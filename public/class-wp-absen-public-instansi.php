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

class Wp_Absen_Public_Instansi
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    private $functions;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version, $functions)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->functions = $functions;
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

    public function management_data_instansi($atts)
    {
        if (!empty($_GET) && !empty($_GET["post"])) {
            return "";
        }
        require_once plugin_dir_path(dirname(__FILE__)) .
            "public/partials/wp-absen-management-data-instansi.php";
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
                    4 => "id",
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
                    " FROM `absensi_data_instansi`";

                $where_first = " WHERE 1=1 AND active=1";
                
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
                    $btn .=
                        '<a class="btn btn-sm btn-info" style="margin:2px;" onclick="mutakhirkan_user(\'' .
                        $recVal["id"] . '\', \'' . $recVal["username"] .
                        '\'); return false;" href="#" title="Mutakhirkan User"><i class="dashicons dashicons-admin-users"></i></a>';
                    $btn .=
                        '<a class="btn btn-sm btn-danger" style="margin:2px;" onclick="hapus_data(\'' .
                        $recVal["id"] .
                        '\'); return false;" href="#" title="Hapus Data"><i class="dashicons dashicons-trash"></i></a>';
                    $queryRecords[$recKey]["aksi"] = $btn;
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
                $ret["data"] = $wpdb->update(
                    "absensi_data_instansi",
                    ["active" => 0],
                    [
                        "id" => $_POST["id"],
                    ],
                );
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
                        '
                    SELECT 
                        *
                    FROM absensi_data_instansi
                    WHERE id=%d
                ',
                        $_POST["id"],
                    ),
                    ARRAY_A,
                );
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
                    $data = [
                        "nama_instansi" => $nama_instansi,
                        "alamat_instansi" => $alamat_instansi,
                        "koordinat" => $koordinat,
                        "radius_meter" => $radius_meter,
                        "username" => $username,
                        "email_instansi" => $email_instansi,
                        "id_user" => $id_user,
                        "tahun_anggaran" => $tahun,
                        "active" => 1,
                        "update_at" => current_time("mysql"),
                    ];

                    if (!empty($_POST["id_data"])) {
                        $wpdb->update("absensi_data_instansi", $data, [
                            "id" => $_POST["id_data"],
                        ]);
                        $ret["message"] = "Berhasil update data!";
                    } else {
                        $cek_id = $wpdb->get_row(
                            $wpdb->prepare(
                                '
                            SELECT
                                id,
                                active
                            FROM absensi_data_instansi
                            WHERE id=%s
                            AND tahun_anggaran=%d
                        ',
                                $_POST["id_data"],
                                $tahun,
                            ),
                            ARRAY_A,
                        );

                        if (empty($cek_id)) {
                            $wpdb->insert("absensi_data_instansi", $data);
                            $new_instansi_id = $wpdb->insert_id;
                        } else {
                             $new_instansi_id = $cek_id["id"];
                            if ($cek_id["active"] == 0) {
                                $wpdb->update("absensi_data_instansi", $data, [
                                    "id" => $cek_id["id"],
                                ]);
                            } else {
                                $ret["status"] = "error";
                                $ret["message"] =
                                    'Gagal disimpan. Data Instansi dengan id Instansi="' .
                                    $_POST["id_data"] .
                                    '" sudah ada!';
                            }
                        }

                        // Create WordPress user automatically if username is provided
                        if (!empty($username) && $ret["status"] !== "error") {
                            // Check if role exists, if not create it
                            if (empty(get_role('admin_instansi'))) {
                                add_role('admin_instansi', 'Admin Instansi', array('read' => true));
                            }

                            // Check if user already exists
                            $existing_user = get_user_by('login', $username);

                            if ($existing_user) {
                                // User already exists, just assign role and update id_user
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

                                // Create new user with password = username
                                $user_id = wp_create_user($username, $username, $email_instansi);

                                if (is_wp_error($user_id)) {
                                    $ret['status'] = 'error';
                                    $ret['message'] = 'Gagal membuat user: ' . $user_id->get_error_message();
                                    die(json_encode($ret));
                                }

                                // Remove default subscriber role and add admin_instansi
                                $u = new WP_User($user_id);
                                $u->remove_role('subscriber');
                                $u->add_role('admin_instansi');

                                // Set meta to force password change on first login
                                update_user_meta($user_id, 'absen_force_password_change', 1);
                            }

                            // Update instansi with user id
                            if (isset($new_instansi_id) && $user_id) {
                                $wpdb->update(
                                    'absensi_data_instansi',
                                    array('id_user' => $user_id),
                                    array('id' => $new_instansi_id)
                                );
                            }
                        } elseif ($id_user > 0) {
                            // Legacy: Assign role if id_user was manually provided
                            $user = new WP_User($id_user);
                            if (empty(get_role('admin_instansi'))) {
                                add_role('admin_instansi', 'Admin Instansi', array('read' => true));
                            }
                            $user->add_role('admin_instansi');
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
    public function mutakhirkan_user_instansi(){
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'User berhasil dimutakhirkan!',
            'data' => array()
        );

        if(!empty($_POST)){
            if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                
                $id_instansi = $_POST['id_instansi'];
                $password = !empty($_POST['password']) ? $_POST['password'] : '';
                
                if(empty($id_instansi)){
                     $ret['status'] = 'error';
                     $ret['message'] = 'ID Instansi tidak valid!';
                } else {
                    $instansi = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_data_instansi WHERE id = %d", $id_instansi));
                    if(!$instansi){
                         $ret['status'] = 'error';
                         $ret['message'] = 'Instansi tidak ditemukan!';
                    } else if(empty($instansi->username)) {
                         $ret['status'] = 'error';
                         $ret['message'] = 'Username Instansi belum diset!';
                    } else {
                        $username = $instansi->username;
                        $email = $instansi->email_instansi;
                        $default_pass = $username; 
                        $new_pass = !empty($password) ? $password : $default_pass;

                        $user = get_user_by('login', $username);
                        $user_id = 0;

                        if($user){
                            // User exists, update password
                            $user_id = $user->ID;
                            if(!empty($password)){ // Only update password if provided
                                wp_set_password($new_pass, $user_id);
                            }
                            
                            // Ensure role is admin_instansi
                            $u = new WP_User( $user_id );
                            if ( !in_array( 'admin_instansi', (array) $u->roles ) ) {
                                $u->add_role( 'admin_instansi' );
                            }

                        } else {
                            // Create new user
                            if(email_exists($email)){
                                 // Email exists but username distinct? Or handle if email empty?
                                 // For now if email collision, create user might fail or we skip email.
                                 // Let's rely on username primarily.
                                 $ret['status'] = 'error';
                                 $ret['message'] = 'Email sudah digunakan user lain!';
                                 die(json_encode($ret));
                            }
                            
                            $user_id = wp_create_user( $username, $new_pass, $email );
                            if ( is_wp_error( $user_id ) ) {
                                $ret['status'] = 'error';
                                $ret['message'] = 'Gagal membuat user: ' . $user_id->get_error_message();
                                die(json_encode($ret));
                            }
                            
                            $u = new WP_User( $user_id );
                            $u->remove_role( 'subscriber' );
                            $u->add_role( 'admin_instansi' );
                        }

                        // Update instansi with user id
                        if($user_id){
                            $wpdb->update(
                                'absensi_data_instansi',
                                array('id_user' => $user_id),
                                array('id' => $id_instansi)
                            );
                        }
                    }
                }
                
            } else {
                $ret['status'] = 'error';
                $ret['message'] = 'Api Key tidak sesuai!';
            }
        } else {
            $ret['status'] = 'error';
            $ret['message'] = 'Format tidak sesuai!';
        }
        die(json_encode($ret));
    }

    public function get_master_instansi(){
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data' => array()
        );
        
        if(!empty($_POST)){
            if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                // Check if current user is admin instansi
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
                
                $where = " WHERE active=1";
                if ($is_admin_instansi) {
                    $where .= " AND id_user = " . $current_user->ID;
                }

                $results = $wpdb->get_results("SELECT id, nama_instansi as label FROM absensi_data_instansi $where ORDER BY nama_instansi ASC", ARRAY_A);
                
                $data = array();
                if(!empty($results)){
                    foreach($results as $row){
                         $data[] = array(
                             'value' => $row['id'],
                             'label' => $row['label']
                         );
                    }
                }
                $ret['data'] = $data;
                
            }else{
                $ret['status']  = 'error';
                $ret['message'] = 'Api key tidak ditemukan!';
            }
        }else{
            $ret['status']  = 'error';
            $ret['message'] = 'Format Salah!';
        }
        
        die(json_encode($ret));
    }
}
