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
                    2 => "koordinat",
                    3 => "radius_meter",
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
                        '<a class="btn btn-sm btn-warning" onclick="edit_data(\'' .
                        $recVal["id"] .
                        '\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
                    $btn .=
                        '<a class="btn btn-sm btn-danger" onclick="hapus_data(\'' .
                        $recVal["id"] .
                        '\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-trash"></i></a>';
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

                    $tahun = $_POST["tahun"];
                    $data = [
                        "nama_instansi" => $nama_instansi,
                        "alamat_instansi" => $alamat_instansi,
                        "koordinat" => $koordinat,
                        "radius_meter" => $radius_meter,
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

                        // Assign Role
                        if($id_user > 0){
                             $user = new WP_User( $id_user );
                             // Check if role exists, if not create it (safe failover)
                             if ( empty( get_role( 'um_admin_instansi' ) ) ) {
                                add_role( 'um_admin_instansi', 'Admin Instansi', array( 'read' => true ) );
                             }
                             $user->add_role( 'um_admin_instansi' );
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
}
