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

require_once ABSEN_PLUGIN_PATH . "/public/trait/CustomTrait.php";

class Wp_Absen_Public_Absensi
{
    use CustomTraitAbsen;

    private $plugin_name;
    private $version;
    private $functions;

    public function __construct( $plugin_name, $version, $functions ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->functions = $functions;
    }

    /**
     * Get Server Time for Frontend Clock
     */
    public function get_server_time() {
        // Return current server time in Format: Y-m-d H:i:s
        // Also return user specific timezone info if needed, but for now server time is master.
        $ret = array(
            'status' => 'success',
            'time' => current_time('mysql'),
            'timestamp' => current_time('timestamp'),
            'date_formatted' => date_i18n('l, d F Y', current_time('timestamp'))
        );
        die(json_encode($ret));
    }

    /**
     * Get Valid Work Codes for Current Instansi
     */
    public function get_valid_kode_kerja() {
        global $wpdb;
        $ret = array('status' => 'success', 'data' => array());
        
        if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
            $current_user = wp_get_current_user();
            $id_instansi = 0;

            // Check if Admin requesting for specific Instansi/Pegawai
            if ((in_array('administrator', (array)$current_user->roles) || in_array('admin_instansi', (array)$current_user->roles)) && !empty($_POST['id_instansi'])) {
                $id_instansi = intval($_POST['id_instansi']);
            } 
            // Else derive from current user (Employee context)
            else {
                $id_instansi = get_user_meta($current_user->ID, 'id_admin_instansi', true);
                if (!$id_instansi && in_array('admin_instansi', (array) $current_user->roles)) {
                    $id_instansi = $current_user->ID;
                }
            }

            if ($id_instansi) {
                $results = $wpdb->get_results($wpdb->prepare("
                    SELECT id, nama_kerja, jam_masuk, jam_pulang, jenis
                    FROM absensi_data_kerja
                    WHERE id_instansi = %d AND active = 1 AND deleted_at IS NULL
                ", $id_instansi), ARRAY_A);
                
                $ret['data'] = $results;
            }
        }
        die(json_encode($ret)); 
    }

    /**
     * Submit Absensi (Clock In / Out)
     */
    public function submit_absensi_pegawai() {
        global $wpdb;
        $ret = array('status' => 'error', 'message' => 'Terjadi Kesalahan!');

        if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
            $current_user = wp_get_current_user();
            $id_pegawai_user = $current_user->ID;

            // Retrieve Pegawai ID from absensi_data_pegawai using id_user
            $pegawai = $wpdb->get_row($wpdb->prepare("SELECT id, id_instansi FROM absensi_data_pegawai WHERE id_user = %d", $id_pegawai_user));

            if (!$pegawai) {
                $ret['message'] = 'Data Pegawai tidak ditemukan!';
                die(json_encode($ret));
            }

            $id_pegawai = $pegawai->id;
            $id_instansi = $pegawai->id_instansi;
            $id_kode_kerja = isset($_POST['id_kode_kerja']) ? intval($_POST['id_kode_kerja']) : 0;
            $koordinat = isset($_POST['koordinat']) ? sanitize_text_field($_POST['koordinat']) : '';
            $tipe_absen = isset($_POST['tipe_absen']) ? sanitize_text_field($_POST['tipe_absen']) : ''; // 'masuk' or 'pulang'
            $tanggal = current_time('Y-m-d');
            $waktu_sekarang = current_time('mysql');
            $tahun = current_time('Y');

            if (empty($id_kode_kerja)) {
                $ret['message'] = 'Kode Kerja harus dipilih!';
                die(json_encode($ret));
            }

            // Handle Foto Upload
            $foto_lampiran = '';
            if (!empty($_FILES['foto_lampiran']['name'])) {
                $target_path = ABSEN_PLUGIN_PATH . 'public/img/absensi/';
                $ext = ['jpg', 'jpeg', 'png'];

                // Ensure directory exists
                if (!file_exists($target_path)) {
                    mkdir($target_path, 0755, true);
                }

                $prefix = 'absensi_' . $tipe_absen . '_' . $id_pegawai . '_' . time();
                $upload = self::uploadFileAbsen(
                    get_option(ABSEN_APIKEY),
                    $target_path,
                    $_FILES['foto_lampiran'],
                    $ext,
                    2000000, // 2MB max
                    $prefix
                );

                if ($upload['status']) {
                    $foto_lampiran = $upload['filename'];
                } else {
                    $ret['message'] = 'Gagal upload foto: ' . $upload['message'];
                    die(json_encode($ret));
                }
            }

            // Check existing attendance for today & code
            $existing = $wpdb->get_row($wpdb->prepare("
                SELECT id, waktu_masuk, waktu_pulang
                FROM absensi_harian
                WHERE id_pegawai = %d AND id_kode_kerja = %d AND tanggal = %s
            ", $id_pegawai, $id_kode_kerja, $tanggal));

            if ($tipe_absen == 'masuk') {
                if ($existing && !empty($existing->waktu_masuk)) {
                    $ret['message'] = 'Anda sudah absen masuk untuk jadwal ini!';
                } else {
                    // Simpan Absen Masuk
                    $data = array(
                        'id_pegawai' => $id_pegawai,
                        'id_instansi' => $id_instansi,
                        'id_kode_kerja' => $id_kode_kerja,
                        'tanggal' => $tanggal,
                        'waktu_masuk' => $waktu_sekarang,
                        'koordinat_masuk' => $koordinat,
                        'status' => 'Hadir', // Default Hadir, nanti bisa logic telat
                        'tahun' => $tahun
                    );

                    // Add foto masuk if uploaded
                    if (!empty($foto_lampiran)) {
                        $data['foto_masuk'] = $foto_lampiran;
                    }

                    if ($existing) {
                        $wpdb->update('absensi_harian', $data, array('id' => $existing->id));
                    } else {
                        $wpdb->insert('absensi_harian', $data);
                    }
                    $ret['status'] = 'success';
                    $ret['message'] = 'Berhasil Absen Masuk pada ' . date_i18n('H:i', strtotime($waktu_sekarang));
                }
            } elseif ($tipe_absen == 'pulang') {
                if (!$existing || empty($existing->waktu_masuk)) {
                    $ret['message'] = 'Anda belum absen masuk!';
                } elseif (!empty($existing->waktu_pulang)) {
                    // Update Pulang (Allow overwrite or block? Assume block for now, or allow update)
                    // Let's allow update for now as "Update Pulang"
                    $data = array(
                        'waktu_pulang' => $waktu_sekarang,
                        'koordinat_pulang' => $koordinat
                    );

                    // Add foto pulang if uploaded
                    if (!empty($foto_lampiran)) {
                        $data['foto_pulang'] = $foto_lampiran;
                    }

                    $wpdb->update('absensi_harian', $data, array('id' => $existing->id));
                    $ret['status'] = 'success';
                    $ret['message'] = 'Berhasil Update Absen Pulang pada ' . date_i18n('H:i', strtotime($waktu_sekarang));
                } else {
                    // First time pulang
                    $data = array(
                        'waktu_pulang' => $waktu_sekarang,
                        'koordinat_pulang' => $koordinat
                    );

                    // Add foto pulang if uploaded
                    if (!empty($foto_lampiran)) {
                        $data['foto_pulang'] = $foto_lampiran;
                    }

                    $wpdb->update('absensi_harian', $data, array('id' => $existing->id));
                    $ret['status'] = 'success';
                    $ret['message'] = 'Berhasil Absen Pulang pada ' . date_i18n('H:i', strtotime($waktu_sekarang));
                }
            } else {
                $ret['message'] = 'Tipe Absen tidak valid!';
            }

        } else {
            $ret['message'] = 'Invalid API Key';
        }

        die(json_encode($ret));
    }
    
    // Check Status for Today (Helper for Frontend UI)
    public function check_status_absensi() {
        global $wpdb;
        $ret = array('status' => 'success', 'waktu_masuk' => null, 'waktu_pulang' => null);
        
        if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
            $current_user = wp_get_current_user();
            $id_pegawai_user = $current_user->ID;
            $id_kode_kerja = isset($_POST['id_kode_kerja']) ? intval($_POST['id_kode_kerja']) : 0;
            $tanggal = current_time('Y-m-d');

             // Get Pegawai ID
            $pegawai_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM absensi_data_pegawai WHERE id_user = %d", $id_pegawai_user));
            
            if (!$pegawai_id) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data Pegawai tidak ditemukan untuk User ini!';
                die(json_encode($ret));
            }

            if ($id_kode_kerja) {
                $data = $wpdb->get_row($wpdb->prepare("
                    SELECT waktu_masuk, waktu_pulang 
                    FROM absensi_harian 
                    WHERE id_pegawai = %d AND id_kode_kerja = %d AND tanggal = %s
                ", $pegawai_id, $id_kode_kerja, $tanggal));
                
                if ($data) {
                    $ret['waktu_masuk'] = $data->waktu_masuk;
                    $ret['waktu_pulang'] = $data->waktu_pulang;
                }
            }
        }
        die(json_encode($ret));
    }



    /**
     * Get Data Absensi By ID for Edit
     */
    public function get_data_absensi_by_id() {
        global $wpdb;
        $ret = array('status' => 'error', 'message' => 'Data tidak ditemukan!');
        
        if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if ($id) {
                // Fetch data join with Pegawai for name/instansi check if needed
                $data = $wpdb->get_row($wpdb->prepare("
                    SELECT ah.*, p.nama as nama_pegawai, p.nik, p.id_instansi 
                    FROM absensi_harian ah
                    JOIN absensi_data_pegawai p ON ah.id_pegawai = p.id
                    WHERE ah.id = %d
                ", $id), ARRAY_A);
                
                if ($data) {
                    // Check ownership (Admin Instansi)
                    $current_user = wp_get_current_user();
                    
                    if (!in_array('administrator', (array) $current_user->roles) && !in_array('admin_instansi', (array) $current_user->roles)) {
                        $ret['message'] = "Akses Ditolak!";
                        die(json_encode($ret));
                    }
                    if (in_array('admin_instansi', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles)) {
                        if ($data['id_instansi'] != $current_user->ID) {
                            $ret['message'] = "Akses Ditolak!";
                            die(json_encode($ret));
                        }
                    }

                    // Format Times
                    $data['jam_masuk'] = $data['waktu_masuk'] ? date('H:i', strtotime($data['waktu_masuk'])) : '';
                    $data['jam_pulang'] = $data['waktu_pulang'] ? date('H:i', strtotime($data['waktu_pulang'])) : '';
                    
                    // Format for Select2 pre-population
                    $data['pegawai_text'] = $data['nama_pegawai'] . ' (' . $data['nik'] . ')';

                    $ret['status'] = 'success';
                    $ret['data'] = $data;
                    $ret['message'] = 'Berhasil ambil data';
                }
            }
        }
        die(json_encode($ret));
    }

    /**
     * Manual Add Attendance (Admin)
     */
    public function tambah_data_absensi_manual() {
        global $wpdb;
        $ret = array('status' => 'error', 'message' => 'Terjadi Kesalahan!');

        if (!empty($_POST) && !empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
            $current_user = wp_get_current_user();
            // Capability Check
            if (!in_array('administrator', (array)$current_user->roles) && !in_array('admin_instansi', (array)$current_user->roles)) {
                $ret['message'] = 'Akses Ditolak!';
                die(json_encode($ret));
            }

            $id_pegawai = isset($_POST['id_pegawai']) ? intval($_POST['id_pegawai']) : 0;
            $id_kode_kerja = isset($_POST['id_kode_kerja']) ? intval($_POST['id_kode_kerja']) : 0;
            $tanggal = isset($_POST['tanggal']) ? sanitize_text_field($_POST['tanggal']) : '';
            $jam_masuk = isset($_POST['jam_masuk']) ? sanitize_text_field($_POST['jam_masuk']) : '';
            $jam_pulang = isset($_POST['jam_pulang']) ? sanitize_text_field($_POST['jam_pulang']) : '';
            $jam_pulang = isset($_POST['jam_pulang']) ? sanitize_text_field($_POST['jam_pulang']) : '';
            // Default Status always Hadir for manual entry unless specified otherwise by logic (but user asked to remove input)
            $status = 'Hadir';
            $tahun = current_time('Y');

            if (!$id_pegawai || !$id_kode_kerja || !$tanggal) {
                $ret['message'] = 'Data Pegawai, Kode Kerja, dan Tanggal wajib diisi!';
                die(json_encode($ret));
            }

            // Get Instansi from Pegawai (Safety Check)
            $pegawai = $wpdb->get_row($wpdb->prepare("SELECT id_instansi FROM absensi_data_pegawai WHERE id = %d", $id_pegawai));
            if (!$pegawai) {
                $ret['message'] = 'Pegawai tidak ditemukan!';
                die(json_encode($ret));
            }
            
            // Instansi Check for Admin Instansi
            if (in_array('admin_instansi', (array)$current_user->roles) && !in_array('administrator', (array)$current_user->roles)) {
                if ($pegawai->id_instansi != $current_user->ID) {
                    $ret['message'] = 'Akses Ditolak (Pegawai bukan milik Instansi Anda)!';
                    die(json_encode($ret));
                }
            }

            // Format Datetime
            $waktu_masuk = $jam_masuk ? $tanggal . ' ' . $jam_masuk . ':00' : null;
            $waktu_pulang = $jam_pulang ? $tanggal . ' ' . $jam_pulang . ':00' : null;

            // Check if exist
            $existing = $wpdb->get_row($wpdb->prepare("
                SELECT id FROM absensi_harian 
                WHERE id_pegawai = %d AND id_kode_kerja = %d AND tanggal = %s
            ", $id_pegawai, $id_kode_kerja, $tanggal));

            $data = array(
                'id_pegawai' => $id_pegawai,
                'id_instansi' => $pegawai->id_instansi,
                'id_kode_kerja' => $id_kode_kerja,
                'tanggal' => $tanggal,
                'waktu_masuk' => $waktu_masuk,
                'waktu_pulang' => $waktu_pulang,
                'status' => $status,
                'tahun' => $tahun,
                // Manual entry doesn't have coordinates usually, or could set default
                'koordinat_masuk' => 'Manual by Admin', 
                'koordinat_pulang' => 'Manual by Admin'
            );

            if ($existing) {
                $wpdb->update('absensi_harian', $data, array('id' => $existing->id));
                $ret['message'] = 'Data Absensi Berhasil Diupdate!';
            } else {
                $wpdb->insert('absensi_harian', $data);
                $ret['message'] = 'Data Absensi Berhasil Ditambahkan!';
            }
            $ret['status'] = 'success';

        }
        die(json_encode($ret));
    }

    /**
     * Get DataTable for Admin (Manual & Monitoring)
     */
    public function get_datatable_absensi() {
        global $wpdb;
        // Basic Logic for DataTable
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data'  => array()
        );

        if (!empty($_POST) && !empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
            $params = $_REQUEST;
            // Columns mapping
            $columns = array( 
                0 => 'id',
                1 => 'nama_pegawai', 
                2 => 'tanggal',
                3 => 'status',
                4 => 'waktu_masuk',
                5 => 'nama_kerja', // From Join
                6 => 'id'
            );
            
            $sql_base = "
                SELECT 
                    ah.*, 
                    p.nama as nama_pegawai, 
                    k.nama_kerja 
                FROM absensi_harian ah
                JOIN absensi_data_pegawai p ON ah.id_pegawai = p.id
                JOIN absensi_data_kerja k ON ah.id_kode_kerja = k.id
                WHERE ah.deleted_at IS NULL
            ";
            
            // Filter Instansi or Pegawai
            $current_user = wp_get_current_user();
            $is_pegawai_only = in_array('pegawai', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles) && !in_array('admin_instansi', (array) $current_user->roles);

            if (in_array('admin_instansi', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles)) {
                $sql_base .= $wpdb->prepare(" AND ah.id_instansi = %d", $current_user->ID);
            } else if ($is_pegawai_only) {
                // Get Pegawai ID associated with this user
                $pegawai_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM absensi_data_pegawai WHERE id_user = %d", $current_user->ID));
                if ($pegawai_id) {
                    $sql_base .= $wpdb->prepare(" AND ah.id_pegawai = %d", $pegawai_id);
                } else {
                    // Fallback: If no pegawai linked, show nothing or error? 
                    // Showing nothing is safe.
                    $sql_base .= " AND 1=0"; 
                }
            }

             // Search
            if( !empty($params['search']['value']) ) {
                $search = $params['search']['value'];
                $sql_base .= " AND (p.nama LIKE '%$search%' OR k.nama_kerja LIKE '%$search%' OR ah.status LIKE '%$search%')";
            }

             // Total
            $queryTot = $wpdb->get_var("SELECT COUNT(*) FROM ($sql_base) as total_table");
            $totalRecords = $queryTot;

             // Order & Limit
            $sql_base .=  " ORDER BY ah.tanggal DESC, ah.waktu_masuk DESC LIMIT ".$params['start']." ,".$params['length'];
            
            $queryRecords = $wpdb->get_results($sql_base, ARRAY_A);
            
            $data = array();
            foreach ($queryRecords as $row) {
                $aksi = '';
                if (!$is_pegawai_only) {
                    $aksi = '<button class="btn btn-sm btn-danger" onclick="hapus_data_absensi('.$row['id'].')">Hapus</button>';
                }

                $nestedData = array(); 
                $nestedData['no'] = $params['start']++;
                $nestedData['nama_pegawai'] = $row['nama_pegawai'];
                $nestedData['tanggal'] = date_i18n('d-m-Y', strtotime($row['tanggal']));
                $nestedData['status'] = $row['status'];
                $nestedData['waktu'] = date_i18n('H:i', strtotime($row['waktu_masuk'])) . ' - ' . ($row['waktu_pulang'] ? date_i18n('H:i', strtotime($row['waktu_pulang'])) : '-');
                $nestedData['nama_kerja'] = $row['nama_kerja'];
                $nestedData['aksi'] = $aksi;
                
                 $data[] = $nestedData; // Or mapped to columns if serverSide
            }
            
             // Remap for keys
             // Note: Frontend JS expects specific keys or array indices. 
             // Let's use array of arrays or array of objects matching columns "data" field?
             // Checking JS: columns[0].data = ''. Means array of arrays is safest or map properly.
             // Actually JS columns valid data source is empty string in provided example.
             // Let's return objects but DataTable needs "data" property.

            $json_data = array(
                "draw"            => intval( $params['draw'] ),   
                "recordsTotal"    => intval( $totalRecords ),  
                "recordsFiltered" => intval( $totalRecords ),
                "data"            => $queryRecords // Sending Raw for custom formatting in JS or mapped above?
            );

             // Use Mapped Data
            $formatted_data = array();
            $no = $params['start'] + 1;
            foreach($queryRecords as $row){
                $waktu_masuk = $row['waktu_masuk'] ? date('H:i', strtotime($row['waktu_masuk'])) : '-';
                $waktu_pulang = $row['waktu_pulang'] ? date('H:i', strtotime($row['waktu_pulang'])) : '-';

                // Foto Masuk
                $foto_masuk_html = '-';
                if (!empty($row['foto_masuk'])) {
                    $foto_masuk_url = ABSEN_PLUGIN_URL . 'public/img/absensi/' . $row['foto_masuk'];
                    $foto_masuk_html = '<a href="'.$foto_masuk_url.'" target="_blank"><img src="'.$foto_masuk_url.'" style="max-width:50px;max-height:50px;border-radius:5px;" title="Lihat Foto Masuk"></a>';
                }

                // Foto Pulang
                $foto_pulang_html = '-';
                if (!empty($row['foto_pulang'])) {
                    $foto_pulang_url = ABSEN_PLUGIN_URL . 'public/img/absensi/' . $row['foto_pulang'];
                    $foto_pulang_html = '<a href="'.$foto_pulang_url.'" target="_blank"><img src="'.$foto_pulang_url.'" style="max-width:50px;max-height:50px;border-radius:5px;" title="Lihat Foto Pulang"></a>';
                }

                $btn_aksi = '';
                if (!$is_pegawai_only) {
                    $btn_aksi = '
                        <a class="btn btn-sm btn-warning" style="margin-right:5px;" onclick="edit_data_absensi('.$row['id'].')"><i class="dashicons dashicons-edit"></i></a>
                        <a class="btn btn-sm btn-danger" onclick="hapus_data_absensi('.$row['id'].')"><i class="dashicons dashicons-trash"></i></a>
                    ';
                }

                $formatted_data[] = array(
                    // 'no' => $no++, // Removed as per request
                    'nama_pegawai' => $row['nama_pegawai'],
                    'tanggal' => date_i18n('d F Y', strtotime($row['tanggal'])),
                    'status' => $row['status'], // e.g. Hadir, Telat
                    'waktu' => $waktu_masuk . ' - ' . $waktu_pulang,
                    'foto_masuk' => $foto_masuk_html,
                    'foto_pulang' => $foto_pulang_html,
                    'nama_kerja' => $row['nama_kerja'],
                    'aksi' => $btn_aksi
                );
            }

            $json_data['data'] = $formatted_data;
            die(json_encode($json_data));
        }

        die(json_encode($ret));
    }

    public function hapus_data_absensi() {
        global $wpdb;
        $ret = array('status' => 'error', 'message' => 'Gagal menghapus data!');

        if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY) && !empty($_POST['id'])) {
            // Check permissions (Admin Instansi only delete own, Admin can delete all)
            $current_user = wp_get_current_user();
            $id_delete = intval($_POST['id']);
            
            // Get Data to verify ownership
            $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_harian WHERE id = %d", $id_delete));

            if ($data) {
                $allow = false;
                if (in_array('administrator', (array) $current_user->roles)) {
                    $allow = true;
                } else if (in_array('admin_instansi', (array) $current_user->roles)) {
                    if ($data->id_instansi == $current_user->ID) {
                        $allow = true;
                    }
                }

                if ($allow) {
                    // Soft Delete: Set deleted_at timestamp
                    $wpdb->delete(
                        'absensi_harian',
                        array('id' => $id_delete),
                        array('%d')
                    );
                    $ret['status'] = 'success';
                    $ret['message'] = 'Data Absensi berhasil dihapus!';
                } else {
                    $ret['message'] = 'Anda tidak memiliki hak akses untuk menghapus data ini!';
                }
            } else {
                $ret['message'] = 'Data tidak ditemukan!';
            }
        }
        die(json_encode($ret));
    }
}
