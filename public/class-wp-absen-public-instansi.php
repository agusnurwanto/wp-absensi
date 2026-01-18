<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Absen
 * @subpackage Wp_Absen/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Absen
 * @subpackage Wp_Absen/public
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */

require_once ABSEN_PLUGIN_PATH . "/public/trait/CustomTrait.php";

class Wp_Absen_Public_Instansi
{
    use CustomTraitAbsen;

    private $plugin_name;
	private $version;
	private $functions;

    public function __construct($plugin_name, $version, $functions)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->functions = $functions;
    }

    public function management_data_instansi($atts)
    {
        if (!empty($_GET) && !empty($_GET["post"])) {
            return "";
        }

        require_once plugin_dir_path(dirname(__FILE__)) .
            "public/partials/wp-absen-management-data-instansi.php";
    }

    public function get_users_for_instansi() {
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data user!',
            'data' => array()
        );

        if (!empty($_POST)) {
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
                $args = array(
                    'fields' => array( 'ID', 'display_name', 'user_login' ),
                );
                $users = get_users( $args );
                $ret['data'] = $users;
            } else {
                $ret['status'] = 'error';
                $ret['message'] = 'Api key tidak ditemukan!';
            }
        } else {
            $ret['status'] = 'error';
            $ret['message'] = 'Format Salah!';
        }
        die(json_encode($ret));
    }

    public function get_datatable_instansi()
    {
        global $wpdb;
        $ret = [
            "status" => "success",
            "message" => "Berhasil get data!",
            "data" => [],
        ];

        if (!empty($_POST)) {
            if (
                !empty($_POST["api_key"]) &&
                $_POST["api_key"] == get_option(ABSEN_APIKEY)
            ) {
                $user_id = um_user("ID");
                $user_meta = get_userdata($user_id);

                $params = $columns = $totalRecords = $data = [];
                $params = $_REQUEST;

                $columns = [
                    0 => "nama_instansi",
                    1 => "alamat_instansi",
                    2 => "username",
                    3 => "email_instansi",
                    4 => "active", // Status column
                    5 => "id",
                ];
                $where = $sqlTot = $sqlRec = "";

                // check search value exist
                if (!empty($params["search"]["value"])) {
                    $where .=
                        " AND ( id LIKE " .
                        $wpdb->prepare(
                            "%s",
                            "%" . $params["search"]["value"] . "%",
                        );
                }

                // getting total number records without any search
                $sql_tot =
                    "SELECT count(id) as jml FROM `absensi_data_instansi`";
                $sql =
                    "SELECT " .
                    implode(", ", $columns) .
                    ", id_user FROM `absensi_data_instansi`";

                $where_first = " WHERE 1=1";
                
                if (!empty($params['tahun'])) {
                    $where_first .= $wpdb->prepare(" AND tahun = %d", $params['tahun']);
                } else {
                    $active_year = $wpdb->get_var("SELECT tahun_anggaran FROM absensi_data_unit WHERE active=1 ORDER BY id DESC LIMIT 1");
                    if($active_year) {
                        $where_first .= $wpdb->prepare(" AND tahun = %d", $active_year);
                    }
                }

                
                // Get current user and check roles
                $current_user = wp_get_current_user();
                $is_admin = in_array( 'administrator', (array) $current_user->roles );
                
                // If NOT administrator, filter by id_user
                if ( ! $is_admin ) {
                    $where_first .= " AND id_user = " . $current_user->ID;
                }

                $sqlTot .= $sql_tot . $where_first;

                $sqlRec .= $sql . $where_first;
                if (isset($where) && $where != "") {
                    $sqlTot .= $where;
                    $sqlRec .= $where;
                }

                $limit = "";
                if ($params["length"] != -1) {
                    $limit =
                        "  LIMIT " .
                        $wpdb->prepare("%d", $params["start"]) .
                        " ," .
                        $wpdb->prepare("%d", $params["length"]);
                }

                $sqlRec .=
                    " ORDER BY " .
                    $columns[$params["order"][0]["column"]] .
                    "   " .
                    $params["order"][0]["dir"] .
                    $limit;

                $queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
                $totalRecords = $queryTot[0]["jml"];
                $queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

                foreach ($queryRecords as $recKey => $recVal) {
                    $btn =
                        '<a class="btn btn-sm btn-warning" style="margin:2px;" onclick="edit_data(\'' .
                        $recVal["id"] .
                        '\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';

                    // Toggle Status Button
                    if ($recVal["active"] == 1) {
                         $status_badge = '<span class="badge badge-success" style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 4px;">Aktif</span>';
                         $btn .= '<a class="btn btn-sm btn-secondary" style="margin:2px;" onclick="toggle_status_instansi(\'' . $recVal["id"] . '\', 0); return false;" href="#" title="Nonaktifkan"><i class="dashicons dashicons-hidden"></i></a>';
                    } else {
                         $status_badge = '<span class="badge badge-secondary" style="background-color: #6c757d; color: white; padding: 5px 10px; border-radius: 4px;">Tidak Aktif</span>';
                         $btn .= '<a class="btn btn-sm btn-success" style="margin:2px;" onclick="toggle_status_instansi(\'' . $recVal["id"] . '\', 1); return false;" href="#" title="Aktifkan"><i class="dashicons dashicons-visibility"></i></a>';
                    }
                    
                    $queryRecords[$recKey]["status_badge"] = $status_badge;


                    $btn .=
                        '<a class="btn btn-sm btn-danger" style="margin:2px;" onclick="hapus_data(\'' .
                        $recVal["id"] .
                        '\'); return false;" href="#" title="Hapus Data"><i class="dashicons dashicons-trash"></i></a>';
                    $queryRecords[$recKey]["aksi"] = $btn;

                     // Override email from WP User if available
                    if (!empty($recVal['id_user'])) {
                        $user_check = get_user_by('id', $recVal['id_user']);
                        
                        if ($user_check) {
                            $need_update = false;
                            $update_data = array();

                            // Check Name mismatch
                            if ($recVal['nama_instansi'] !== $user_check->first_name) {
                                $update_data['nama_instansi'] = $user_check->first_name;
                                $queryRecords[$recKey]['nama_instansi'] = $user_check->first_name;
                                $need_update = true;
                            }

                            // Check Email mismatch
                            if ($recVal['email_instansi'] !== $user_check->user_email) {
                                $update_data['email_instansi'] = $user_check->user_email;
                                $queryRecords[$recKey]['email_instansi'] = $user_check->user_email;
                                $need_update = true;
                            }
                            
                            // Perform DB Update if needed
                            if ($need_update && !empty($update_data)) {
                                $wpdb->update('absensi_data_instansi', $update_data, array('id' => $recVal['id']));
                            }
                        }
                    }
                }

                $json_data = [
                    "draw" => intval($params["draw"]),
                    "recordsTotal" => intval($totalRecords),
                    "recordsFiltered" => intval($totalRecords),
                    "data" => $queryRecords,
                    "sql" => $sqlRec,
                ];

                die(json_encode($json_data));
            } else {
                $return = [
                    "status" => "error",
                    "message" => "Api Key tidak sesuai!",
                ];
            }
        } else {
            $return = [
                "status" => "error",
                "message" => "Format tidak sesuai!",
            ];
        }

        die(json_encode($return));
    }

    public function hapus_data_instansi_by_id()
    {
        global $wpdb;
        $ret = [
            "status" => "success",
            "message" => "Berhasil hapus data!",
            "data" => [],
        ];

        if (!empty($_POST)) {
            if (
                !empty($_POST["api_key"]) &&
                $_POST["api_key"] == get_option(ABSEN_APIKEY)
            ) {
                // Check if current user is admin instansi
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );

                if ($is_admin_instansi) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Akses Ditolak! Anda tidak memiliki izin untuk menghapus data Instansi.';
                    die(json_encode($ret));
                }

                // Get Data First
                $instansi = $wpdb->get_row($wpdb->prepare("SELECT id_user FROM absensi_data_instansi WHERE id = %d", $_POST['id']));
                
                if ($instansi) {
                    // Hapus Data WordPress User if exists
                    if (!empty($instansi->id_user)) {
                        require_once(ABSPATH.'wp-admin/includes/user.php');
                        wp_delete_user($instansi->id_user);
                        
                        // Delete related Data Kerja (Work Codes) linked by id_user
                        $wpdb->delete('absensi_data_kerja', array('id_instansi' => $instansi->id_user));
                    }
                    
                    // Hard Delete Instansi
                    $ret['data'] = $wpdb->delete('absensi_data_instansi', array('id' => $_POST['id']));
                    $ret['message'] = 'Data Instansi dan akun User berhasil dihapus permanent!';
                } else {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tidak ditemukan!';
                }
            } else {
                $ret["status"] = "error";
                $ret["message"] = "Api key tidak ditemukan!";
            }
        } else {
            $ret["status"] = "error";
            $ret["message"] = "Format Salah!";
        }

        die(json_encode($ret));
    }

    public function get_data_instansi_by_id()
    {
        global $wpdb;
        $ret = [
            "status" => "success",
            "message" => "Berhasil get data!",
            "data" => [],
        ];

        if (!empty($_POST)) {
            if (
                !empty($_POST["api_key"]) &&
                $_POST["api_key"] == get_option(ABSEN_APIKEY)
            ) {
                $ret["data"] = $wpdb->get_row(
                    $wpdb->prepare(
                        'SELECT * FROM absensi_data_instansi WHERE id=%d',
                        $_POST["id"]
                    ),
                    ARRAY_A
                );

                if ($ret["data"]) {
                    // Fetch Primary Work Code Info (Strictly by Year)
                    $tahun = $ret["data"]["tahun"];

                    
                    if (!empty($ret["data"]["id_user"])) {
                        $work_code = $wpdb->get_row($wpdb->prepare(
                            "SELECT nama_kerja, jam_masuk, jam_pulang, hari_kerja, koordinat, radius_meter FROM absensi_data_kerja WHERE id_instansi = %d AND jenis = 'Primary' AND active = 1 ORDER BY id DESC LIMIT 1",
                            $ret["data"]["id_user"]
                        ), ARRAY_A);
                    } else {
                        $work_code = null;
                    }

                    if ($work_code) {
                        $ret["data"] = array_merge($ret["data"], $work_code);
                    }

                    // Override with WP User Data if id_user exists
                    if (!empty($ret['data']['id_user'])) {
                        $user_check = get_user_by('id', $ret['data']['id_user']);
                        
                        if ($user_check) {
                            $need_update = false;
                            $update_data = array();

                            // Sync Name
                            if ($ret['data']['nama_instansi'] !== $user_check->first_name) {
                                $update_data['nama_instansi'] = $user_check->first_name;
                                $ret['data']['nama_instansi'] = $user_check->first_name;
                                $need_update = true;
                            }
                            
                            // Sync Email
                            if ($ret['data']['email_instansi'] !== $user_check->user_email) {
                                $update_data['email_instansi'] = $user_check->user_email;
                                $ret['data']['email_instansi'] = $user_check->user_email;
                                $need_update = true;
                            }

                            // Sync Username (just display, no db update usually needed for this column in custom tables unless strict)
                            // But let's check if we store username in our table? Yes we do: 'username' column.
                            if ($ret['data']['username'] !== $user_check->user_login) {
                                $update_data['username'] = $user_check->user_login;
                                $ret['data']['username'] = $user_check->user_login;
                                $need_update = true;
                            }

                            if ($need_update && !empty($update_data)) {
                                $wpdb->update('absensi_data_instansi', $update_data, array('id' => $ret['data']['id']));
                            }
                        }
                    }
                }

            } else {
                $ret["status"] = "error";
                $ret["message"] = "Api key tidak ditemukan!";
            }
        } else {
            $ret["status"] = "error";
            $ret["message"] = "Format Salah!";
        }

        die(json_encode($ret));
    }

    public function tambah_data_instansi()
    {
        global $wpdb;
        $ret = [
            "status" => "success",
            "message" => "Berhasil simpan data!",
            "data" => [],
        ];

        if (!empty($_POST)) {
            if (
                !empty($_POST["api_key"]) &&
                $_POST["api_key"] == get_option(ABSEN_APIKEY)
            ) {
                // Check permissions: Admin Instansi cannot create NEW data
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array('admin_instansi', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles);
                
                if (empty($_POST["id_data"]) && $is_admin_instansi) {
                    $ret["status"] = "error";
                    $ret["message"] = "Akses Ditolak! Anda tidak memiliki izin untuk menambah data Instansi baru.";
                    die(json_encode($ret));
                }

                if (empty($_POST["nama_instansi"])) {
                    $ret["status"] = "error";
                    $ret["message"] = "Data nama Instansi tidak boleh kosong!";
                } elseif (empty($_POST["alamat_instansi"])) {
                    $ret["status"] = "error";
                    $ret["message"] = "Data alamat Instansi tidak boleh kosong!";
                } elseif (empty($_POST["tahun"])) {
                    $ret["status"] = "error";
                    $ret["message"] = "Data tahun tidak boleh kosong!";
                } else {
                    $nama_instansi = $_POST["nama_instansi"];
                    $alamat_instansi = $_POST["alamat_instansi"];
                    $id_user = isset($_POST["id_user"]) ? $_POST["id_user"] : 0;

                    $koordinat = isset($_POST["koordinat"])
                        ? $_POST["koordinat"]
                        : "";
                    $radius_meter = isset($_POST["radius_meter"])
                        ? $_POST["radius_meter"]
                        : 100;
                    $username = isset($_POST["username"])
                        ? sanitize_user($_POST["username"])
                        : "";
                    $email_instansi = isset($_POST["email_instansi"])
                        ? sanitize_email($_POST["email_instansi"])
                        : "";

                    $tahun = $_POST["tahun"];
                    $id_user = isset($_POST["id_user"]) ? $_POST["id_user"] : 0;
                    
                    // --- HANDLE WP USER CREATION / RETRIEVAL FIRST ---
                    if (!empty($username)) {
                        $user_id = 0;
                        
                        // Check if role exists
                        if (empty(get_role('admin_instansi'))) {
                            add_role('admin_instansi', 'Admin Instansi', array('read' => true));
                        }

                        $existing_user = get_user_by('login', $username);
                        if ($existing_user) {
                            $user_id = $existing_user->ID;
                            $u = new WP_User($user_id);
                            if (!in_array('admin_instansi', (array) $u->roles)) {
                                $u->add_role('admin_instansi');
                            }
                        } else {
                             // Validate email
                            if (!empty($email_instansi) && email_exists($email_instansi)) {
                                $ret['status'] = 'error';
                                $ret['message'] = 'Email sudah digunakan user lain!';
                                die(json_encode($ret));
                            }
                            
                            $user_id = wp_create_user($username, $username, $email_instansi);
                            if (is_wp_error($user_id)) {
                                $ret['status'] = 'error';
                                $ret['message'] = 'Gagal membuat user: ' . $user_id->get_error_message();
                                die(json_encode($ret));
                            }
                            
                            $u = new WP_User($user_id);
                            $u->remove_role('subscriber');
                            $u->add_role('admin_instansi');
                            update_user_meta($user_id, 'absen_force_password_change', 1);
                        }

                        if ($user_id) {
                            // Update Profile Name
                            wp_update_user(array(
                                'ID' => $user_id,
                                'first_name' => $nama_instansi,
                                'display_name' => $nama_instansi
                            ));
                            
                            // Set id_user for Instansi Data
                            $id_user = $user_id;
                        }
                    }

                    $data = [
                        "nama_instansi" => $nama_instansi,
                        "alamat_instansi" => $alamat_instansi,
                        "username" => $username,
                        "email_instansi" => $email_instansi,
                        "id_user" => $id_user,
                        "tahun" => $tahun,
                        "active" => 1,
                        "update_at" => current_time("mysql"),
                    ];

                    if (!empty($_POST["id_data"])) {
                        // UPDATE LOGIC
                        $wpdb->update("absensi_data_instansi", $data, [
                            "id" => $_POST["id_data"],
                        ]);
                        $new_instansi_id = $_POST["id_data"];
                        
                        // Sync Email Check (retained from previous logic but simplified)
                        if ($id_user) {
                            $user_data = get_userdata($id_user);
                            if ($user_data && $user_data->user_email !== $email_instansi) {
                                if (email_exists($email_instansi)) {
                                    $ret['message'] .= ' (Email User WP gagal update: Email sudah dipakai)';
                                } else {
                                    wp_update_user(array('ID' => $id_user, 'user_email' => $email_instansi));
                                }
                            }
                        }

                        $ret["message"] = "Berhasil update data!";
                    } else {
                        // INSERT LOGIC
                        $cek_id = $wpdb->get_row(
                            $wpdb->prepare(
                                'SELECT id, active FROM absensi_data_instansi WHERE id=%s AND tahun=%d',
                                $_POST["id_data"], $tahun
                            ), ARRAY_A
                        );

                        if (empty($cek_id)) {
                            $wpdb->insert("absensi_data_instansi", $data);
                            $new_instansi_id = $wpdb->insert_id;
                        } else {
                             // This block handles a weird edge case where id_data is sent but logic falls here? 
                             // Usually if id_data is present, we hit the first if. 
                             // But checking for 'id_data' in POST vs empty check above.
                             // Actually, line 432 checked !empty($_POST["id_data"]).
                             // So this else block is for empty($_POST["id_data"]).
                             // Thus the $cek_id query using $_POST['id_data'] (empty) is likely wrong or for safety?
                             // Wait, looking at original code... Line 471 used $_POST["id_data"] which was empty?
                             // Ah, original code had `if (!empty($_POST["id_data"]))` ... `else` ...
                             // Inside else: `$cek_id = ... WHERE id=%s ... $_POST['id_data']`
                             // If $_POST['id_data'] is empty, the query WHERE id='' usually returns nothing.
                             // So it proceeds to insert.
                            
                             // I will simplify: Just Insert.
                            $wpdb->insert("absensi_data_instansi", $data);
                            $new_instansi_id = $wpdb->insert_id;
                        }
                    }

                     // --- SAVE PRIMARY WORK CODE ---
                        if ($ret['status'] !== 'error' && isset($new_instansi_id)) {
                            // Check if inputs are arrays (from new frontend) or strings (fallback)
                            // We expect arrays from the new frontend form for per-day validation
                            $jam_masuk_input = isset($_POST['jam_masuk']) ? $_POST['jam_masuk'] : '08:00';
                            $jam_pulang_input = isset($_POST['jam_pulang']) ? $_POST['jam_pulang'] : '16:00';
                            $hari_kerja_input = isset($_POST['hari_kerja']) ? $_POST['hari_kerja'] : [];

                            // Process into JSON Strings
                            $jam_masuk = is_array($jam_masuk_input) ? json_encode($jam_masuk_input) : $jam_masuk_input;
                            $jam_pulang = is_array($jam_pulang_input) ? json_encode($jam_pulang_input) : $jam_pulang_input;
                            $hari_kerja = is_array($hari_kerja_input) ? json_encode($hari_kerja_input) : $hari_kerja_input;
                            
                            $nama_kerja = !empty($_POST['nama_kerja']) ? sanitize_text_field($_POST['nama_kerja']) : 'Lokasi Utama';
                            
                            // Check if Primary code exists
                            $existing_code = null;
                            if ($id_user) {
                                $existing_code = $wpdb->get_row($wpdb->prepare(
                                    "SELECT id FROM absensi_data_kerja WHERE id_instansi = %d AND jenis = 'Primary'",
                                    $id_user
                                ));
                            }

                            $code_data = array(
                                'id_instansi' => $id_user,
                                'jenis' => 'Primary',
                                'nama_kerja' => $nama_kerja,
                                'jam_masuk' => $jam_masuk,
                                'jam_pulang' => $jam_pulang,
                                'hari_kerja' => $hari_kerja,
                                'koordinat' => $koordinat,
                                'radius_meter' => $radius_meter,
                                'active' => 1
                            );

                            if ($id_user) {
                                if ($existing_code) {
                                    $wpdb->update('absensi_data_kerja', $code_data, array('id' => $existing_code->id));
                                } else {
                                    $wpdb->insert('absensi_data_kerja', $code_data);
                                }
                            }
                        }
                }
            } else {
                $ret["status"] = "error";
                $ret["message"] = "Api key tidak ditemukan!";
            }
        } else {
            $ret["status"] = "error";
            $ret["message"] = "Format Salah!";
        }

        die(json_encode($ret));
    }

    public function get_master_instansi() {
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data' => array()
        );

        if (!empty($_POST)) {
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                // Check if current user is admin instansi
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
                
                $where = " WHERE active=1";
                if ($is_admin_instansi) {
                    $where .= " AND id_user = " . $current_user->ID;
                }

                $results = $wpdb->get_results("SELECT id, nama_instansi as label FROM absensi_data_instansi $where ORDER BY nama_instansi ASC", ARRAY_A);
                
                $data = array();
                if (!empty($results)) {
                    foreach($results as $row){
                        $data[] = array(
                            'value' => $row['id'],
                            'label' => $row['label']
                        );
                    }
                }
                $ret['data'] = $data;
                
            } else {
                $ret['status']  = 'error';
                $ret['message'] = 'Api key tidak ditemukan!';
            }
        } else {
            $ret['status']  = 'error';
            $ret['message'] = 'Format Salah!';
        }
        
        die(json_encode($ret));
    }

    public function toggle_status_instansi() {
        global $wpdb;
        $ret = [
            "status" => "success",
            "message" => "Status berhasil diubah!",
        ];

        if (!empty($_POST)) {
            if (
                !empty($_POST["api_key"]) &&
                $_POST["api_key"] == get_option(ABSEN_APIKEY)
            ) {
                // Check capability
                 $current_user = wp_get_current_user();
                 $is_admin = in_array('administrator', (array) $current_user->roles);
                 $is_admin_instansi = in_array('admin_instansi', (array) $current_user->roles);

                 if (!$is_admin && !$is_admin_instansi) {
                      $ret['status'] = 'error';
                      $ret['message'] = 'Akses Ditolak!';
                      die(json_encode($ret));
                 }

                $id = $_POST['id'];
                
                // If not admin, verify ownership
                if (!$is_admin) {
                     $instansi = $wpdb->get_row($wpdb->prepare("SELECT id_user FROM absensi_data_instansi WHERE id = %d", $id));
                     if (!$instansi || $instansi->id_user != $current_user->ID) {
                          $ret['status'] = 'error';
                          $ret['message'] = 'Akses Ditolak! Data ini bukan milik anda.';
                          die(json_encode($ret));
                     }
                }
                $new_status = $_POST['status']; 
                
                $wpdb->update(
                    'absensi_data_instansi',
                    array('active' => $new_status),
                    array('id' => $id)
                );
                
                // Toggle User status as well? Usually active=0 instansi means user cannot login or similar.
                // But for now just the instansi record logic.

                $ret['message'] = ($new_status == 1) ? 'Instansi berhasil Diaktifkan!' : 'Instansi berhasil Dinonaktifkan!';

            } else {
                $ret["status"] = "error";
                $ret["message"] = "Api Key tidak sesuai!";
            }
        }
        die(json_encode($ret));
    }
}
