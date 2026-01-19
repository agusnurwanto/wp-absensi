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

class Wp_Absen_Public_Kode_Kerja
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

    public function manajemen_data_kerja($atts){
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-absen-management-data-kode-kerja.php';
    }

	public function get_datatable_kode_kerja() {
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data'  => array()
        );

        if(!empty($_POST)){
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                $params = $_REQUEST;
                $columns = array( 
                    0 => 'nama_kerja',
                    1 => 'jenis',
                    2 => 'id'
                );
                $where = $sqlTot = $sqlRec = "";

                if( !empty($params['search']['value']) ) {
                    $where .=" AND ( nama_kerja LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
                    $where .=" OR jenis LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%").")";
                }

                $sql_tot = "SELECT count(k.id) as jml FROM `absensi_data_kerja` k";
                $sql = "SELECT k.*, i.username as nama_instansi FROM `absensi_data_kerja` k 
                        LEFT JOIN absensi_data_instansi i ON k.id_instansi = i.id_user"; // User ID join

                $where_first = " WHERE 1=1";

                // Filter for Admin Instansi
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
                if ($is_admin_instansi) {
                    $where_first .= $wpdb->prepare(" AND k.id_instansi = %d", $current_user->ID);
                }

                $sqlTot .= $sql_tot.$where_first;
                $sqlRec .= $sql.$where_first;
                if(isset($where) && $where != '') {
                    $sqlTot .= $where;
                    $sqlRec .= $where;
                }

                $limit = '';
                if($params['length'] != -1){
                    $limit = "  LIMIT ".$wpdb->prepare('%d', $params['start'])." ,".$wpdb->prepare('%d', $params['length']);
                }
                
                $order_clause = " ORDER BY k.id DESC"; 
                if (isset($params['order'][0]['column']) && isset($columns[$params['order'][0]['column']])) {
                    $order_col = $columns[$params['order'][0]['column']];
                    $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'DESC';
                    $order_clause = " ORDER BY $order_col $order_dir";
                }

                $sqlRec .=  $order_clause . $limit;

                $queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
                $totalRecords = $queryTot[0]['jml'];
                $queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

                foreach ($queryRecords as $recKey => $recVal) {
                    $btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
                    $btn .= ' <a class="btn btn-sm btn-danger" onclick="hapus_kode_kerja(\''.$recVal['id'].'\'); return false;" href="#" title="Hapus Data"><i class="dashicons dashicons-trash"></i></a>';

                    if ($recVal['active'] == 1) {
                        $btn .= ' <a class="btn btn-sm btn-secondary" onclick="toggle_status_kode_kerja_js(\''.$recVal['id'].'\', 1); return false;" href="#" title="Nonaktifkan"><i class="dashicons dashicons-hidden"></i></a>';
                        $status_badge = '<span class="badge badge-success" style="background-color:#28a745; color:white; padding:5px 10px; border-radius:10px;">Aktif</span>';
                    } else {
                        $btn .= ' <a class="btn btn-sm btn-success" onclick="toggle_status_kode_kerja_js(\''.$recVal['id'].'\', 0); return false;" href="#" title="Aktifkan"><i class="dashicons dashicons-visibility"></i></a>';
                        $status_badge = '<span class="badge badge-secondary" style="background-color:#6c757d; color:white; padding:5px 10px; border-radius:10px;">Nonaktif</span>';
                    }

                    $queryRecords[$recKey]['status_badge'] = $status_badge;
                    $queryRecords[$recKey]['aksi'] = $btn;
                }

                $json_data = array(
                    "draw"            => intval( $params['draw'] ),   
                    "recordsTotal"    => intval( $totalRecords ),  
                    "recordsFiltered" => intval($totalRecords),
                    "data"            => $queryRecords
                );

                die(json_encode($json_data));
            } else {
                $ret['status'] = 'error'; $ret['message'] = 'Api Key tidak sesuai!';
            }
        }
        die(json_encode($ret));
	}

    public function tambah_data_kode_kerja() {
        global $wpdb;
        $ret = array('status' => 'success', 'message' => 'Berhasil simpan data!', 'data' => array());
        if(!empty($_POST)){
            if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
                $id_instansi = $is_admin_instansi ? $current_user->ID : (!empty($_POST['admin_instansi']) ? $_POST['admin_instansi'] : 0);

                // Validation: Required Fields
                if(empty($_POST['nama_kerja'])) { $ret['status']='error'; $ret['message']='Nama Kerja wajib diisi!'; die(json_encode($ret)); }
                if(empty($id_instansi)) { $ret['status']='error'; $ret['message']='Admin Instansi wajib dipilih!'; die(json_encode($ret)); }
                if(empty($_POST['jenis'])) { $ret['status']='error'; $ret['message']='Jenis Kode Kerja wajib dipilih!'; die(json_encode($ret)); }
                if(empty($_POST['koordinat'])) { $ret['status']='error'; $ret['message']='Koordinat Lokasi wajib diisi!'; die(json_encode($ret)); }
                if(empty($_POST['radius_meter'])) { $ret['status']='error'; $ret['message']='Jarak Maksimal Absen wajib diisi!'; die(json_encode($ret)); }
                if(empty($_POST['hari_kerja'])) { $ret['status']='error'; $ret['message']='Pilih minimal satu hari kerja!'; die(json_encode($ret)); }

                // Validation: One Primary per Instansi
                if($_POST['jenis'] == 'Primary'){
                    $check_sql = $wpdb->prepare("SELECT id FROM absensi_data_kerja WHERE id_instansi = %d AND jenis = 'Primary' AND active = 1", $id_instansi);
                    $existing_primary = $wpdb->get_row($check_sql);
                    
                    if($existing_primary) {
                        // If Create Mode OR (Edit Mode AND ID != Existing Primary ID)
                        if(empty($_POST['id_data']) || ($_POST['id_data'] != $existing_primary->id)) {
                             $ret['status'] = 'error'; 
                             $ret['message'] = 'Hanya boleh ada 1 Jadwal Utama (Primary) per Instansi!'; 
                             die(json_encode($ret));
                        }
                    }
                }

                // JSON Encode Arrays
                $jam_masuk = isset($_POST['jam_masuk']) ? json_encode($_POST['jam_masuk']) : '[]';
                $jam_pulang = isset($_POST['jam_pulang']) ? json_encode($_POST['jam_pulang']) : '[]';
                $hari_kerja = isset($_POST['hari_kerja']) ? json_encode($_POST['hari_kerja']) : '[]';

                $data = array(
                    'id_instansi' => $id_instansi,
                    'jenis' => $_POST['jenis'],
                    'nama_kerja' => $_POST['nama_kerja'],
                    'jam_masuk' => $jam_masuk,
                    'jam_pulang' => $jam_pulang,
                    'hari_kerja' => $hari_kerja,
                    'koordinat' => $_POST['koordinat'],
                    'radius_meter' => $_POST['radius_meter'],
                    'active' => 1,
                    'update_at' => current_time('mysql')
                );

                if(!empty($_POST['id_data'])){
                    // Edit Mode - Permission Check
                    if ($is_admin_instansi) {
                        $existing = $wpdb->get_row($wpdb->prepare("SELECT id_instansi FROM absensi_data_kerja WHERE id=%d", $_POST['id_data']));
                        if(!$existing || $existing->id_instansi != $current_user->ID){
                            $ret['status']='error'; $ret['message']='Akses ditolak!'; die(json_encode($ret));
                        }
                    }
                    $wpdb->update('absensi_data_kerja', $data, array('id' => $_POST['id_data']));
                    $ret['message'] = 'Berhasil update data!';
                }else{
                    $data['created_at'] = current_time('mysql');
                    $wpdb->insert('absensi_data_kerja', $data);
                }
            } else { $ret['status']='error'; $ret['message'] = 'API Key Invalid'; }
        }
        die(json_encode($ret));
    }

    public function get_data_kode_kerja_by_id() {
        global $wpdb;
        $ret = array('status' => 'success', 'message' => 'Detail Data', 'data' => array());
        if(!empty($_POST['id']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )){
             $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_data_kerja WHERE id=%d", $_POST['id']), ARRAY_A);

             // Decode JSON fields
            $data['jam_masuk'] = json_decode($data['jam_masuk']);
            $data['jam_pulang'] = json_decode($data['jam_pulang']);
            $data['hari_kerja'] = json_decode($data['hari_kerja']);

            $ret['data'] = $data;
        }
        die(json_encode($ret));
    }

    public function hapus_data_kode_kerja_by_id() {
        global $wpdb;
        $ret = array('status' => 'success', 'message' => 'Data dihapus');
        if(!empty($_POST['id']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )){
            $current_user = wp_get_current_user();
            $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
            
            if ($is_admin_instansi) {
                $existing = $wpdb->get_row($wpdb->prepare("SELECT id_instansi FROM absensi_data_kerja WHERE id=%d", $_POST['id']));
                if(!$existing || $existing->id_instansi != $current_user->ID){
                    $ret['status']='error'; $ret['message']='Akses ditolak!'; die(json_encode($ret));
                }
            }

            $wpdb->delete('absensi_data_kerja', array('id' => $_POST['id']));
        }
        die(json_encode($ret));
    }
    public function toggle_status_kode_kerja() {
        global $wpdb;
        $ret = array('status' => 'success', 'message' => 'Status berhasil diubah');
        if(!empty($_POST['id']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )){
            $current_user = wp_get_current_user();
            $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
            
            if ($is_admin_instansi) {
                $existing = $wpdb->get_row($wpdb->prepare("SELECT id_instansi FROM absensi_data_kerja WHERE id=%d", $_POST['id']));
                if(!$existing || $existing->id_instansi != $current_user->ID){
                    $ret['status']='error'; $ret['message']='Akses ditolak!'; die(json_encode($ret));
                }
            }
            
            $new_status = ($_POST['current_status'] == 1) ? 0 : 1;
            $wpdb->update('absensi_data_kerja', array('active' => $new_status), array('id' => $_POST['id']));
        }
        die(json_encode($ret));
    }
    public function check_primary_kode_kerja() {
        global $wpdb;
        $ret = array('status' => 'success', 'data' => array('primary_exists' => false));
        
        if(!empty($_POST['id_instansi']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )){
            $id_instansi = intval($_POST['id_instansi']);
            $exclude_id = !empty($_POST['exclude_id']) ? intval($_POST['exclude_id']) : 0;
            
            $sql = $wpdb->prepare("SELECT id FROM absensi_data_kerja WHERE id_instansi = %d AND jenis = 'Primary' AND active = 1 AND id != %d", $id_instansi, $exclude_id);
            $existing = $wpdb->get_var($sql);
            
            if($existing) {
                $ret['data']['primary_exists'] = true;
            }
        }
        die(json_encode($ret));
    }
}