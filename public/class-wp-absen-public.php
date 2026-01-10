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
class Wp_Absen_Public {

	use CustomTraitAbsen;

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
	public function __construct( $plugin_name, $version, $functions ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->functions = $functions;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Absen_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Absen_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name . 'select2', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . 'datatables', plugin_dir_url(__FILE__) . 'css/datatables.min.css', array(), $this->version, 'all');

		wp_enqueue_style( 'dashicons' );

	}

	public function prefix_add_footer_styles() {
		wp_enqueue_style($this->plugin_name . 'bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-absen-public.css', array(), $this->version, 'all' );
	}


	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Absen_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Absen_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		wp_enqueue_script($this->plugin_name . 'bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'select2', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'datatables', plugin_dir_url(__FILE__) . 'js/datatables.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'chart', plugin_dir_url(__FILE__) . 'js/chart.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-absen-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name, 'ajax', array(
			'api_key' => get_option(ABSEN_APIKEY),
			'url' => admin_url('admin-ajax.php')
		));
	}

	public function management_data_pegawai_absensi($atts){
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-absen-management-data-pegawai.php';
    }
    public function management_data_pasar($atts){
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-absen-management-data-pasar.php';
    }
    public function management_data_absensi($atts){
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-absen-management-data-absensi.php';
    }

    public function get_datatable_pegawai(){
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data'  => array()
        );

        if(!empty($_POST)){
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                $user_id = um_user( 'ID' );
                $user_meta = get_userdata($user_id);
                $params = $columns = $totalRecords = $data = array();
                $params = $_REQUEST;
                $columns = array( 
				   0 => 'id_skpd',
				   1 => 'nik',
				   2 => 'nip',
				   3 => 'gelar_depan',
				   4 => 'nama',
				   5 => 'gelar_belakang',
				   6 => 'nama_lengkap',
				   7 => 'tempat_lahir',
				   8 => 'tanggal_lahir',
				   9 => 'jenis_kelamin',
				   10 => 'kode_jenis_kelamin',
				   11 => 'status',
				   12 => 'gol_ruang',
				   13 => 'kode_gol',
				   14 => 'tmt_pangkat',
				   15 => 'eselon',
				   16 => 'jabatan',
				   17 => 'tipe_pegawai',
				   18 => 'tmt_jabatan',
				   19 => 'agama',
				   20 => 'alamat',
				   21 => 'no_hp',
				   22 => 'satuan_kerja',
				   23 => 'unit_kerja_induk',
				   24 => 'tmt_pensiun',
				   25 => 'pendidikan',
				   26 => 'kode_pendidikan',
				   27 => 'nama_sekolah',
				   28 => 'nama_pendidikan',
				   29 => 'lulus',
				   30 => 'karpeg',
				   31 => 'karis_karsu',
				   32 => 'nilai_prestasi',
				   33 => 'email',
				   34 => 'tahun',
                   35 => 'user_role',
                   36 => 'id'
                );
                $where = $sqlTot = $sqlRec = "";

                // check search value exist
                if( !empty($params['search']['value']) ) {
                    $where .=" AND ( id_pegawai LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");  
                    $where .=" OR total LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
                }

                // getting total number records without any search
                $sql_tot = "SELECT count(id) as jml FROM `absensi_data_pegawai`";
                $sql = "SELECT ".implode(', ', $columns)." FROM `absensi_data_pegawai`";
                $where_first = " WHERE 1=1 AND active=1";
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
                $sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir'].$limit;

                $queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
                $totalRecords = $queryTot[0]['jml'];
                $queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

                foreach($queryRecords as $recKey => $recVal){
                    $btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
                    $btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-trash"></i></a>';
                    $queryRecords[$recKey]['aksi'] = $btn;
                }

                $json_data = array(
                    "draw"            => intval( $params['draw'] ),   
                    "recordsTotal"    => intval( $totalRecords ),  
                    "recordsFiltered" => intval($totalRecords),
                    "data"            => $queryRecords,
                    "sql"             => $sqlRec
                );

                die(json_encode($json_data));
            }else{
                $return = array(
                    'status' => 'error',
                    'message'   => 'Api Key tidak sesuai!'
                );
            }
        }else{
            $return = array(
                'status' => 'error',
                'message'   => 'Format tidak sesuai!'
            );
        }
        die(json_encode($return));
    }

    public function hapus_data_pegawai_by_id(){
	    global $wpdb;
	    $ret = array(
	        'status' => 'success',
	        'message' => 'Berhasil hapus data!',
	        'data' => array()
	    );
	    if(!empty($_POST)){
	        if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
	            $ret['data'] = $wpdb->update('absensi_data_pegawai', array('active' => 0), array(
	                'id' => $_POST['id']
	            ));
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

	public function get_data_pegawai_by_id(){
	    global $wpdb;
	    $ret = array(
	        'status' => 'success',
	        'message' => 'Berhasil get data!',
	        'data' => array()
	    );
	    if(!empty($_POST)){
	        if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
	            $ret['data'] = $wpdb->get_row($wpdb->prepare('
	                SELECT 
	                    *
	                FROM absensi_data_pegawai
	                WHERE id=%d
	            ', $_POST['id']), ARRAY_A);
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

	public function tambah_data_pegawai(){
	    global $wpdb;
	    $ret = array(
	        'status' => 'success',
	        'message' => 'Berhasil simpan data!',
	        'data' => array()
	    );
	    if(!empty($_POST)){
	        if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
				if (empty($_POST['gelar_depan'])) {
				    $ret['status'] = 'error';
				    $ret['message'] = 'Data gelar_depan tidak boleh kosong!';
				} else if (empty($_POST['gelar_belakang'])) {
				    $ret['status'] = 'error';
				    $ret['message'] = 'Data gelar_belakang tidak boleh kosong!';
				} else if (empty($_POST['nama_lengkap'])) {
				    $ret['status'] = 'error';
				    $ret['message'] = 'Data nama_lengkap tidak boleh kosong!';
				} else if (empty($_POST['jenis_kelamin'])) {
				    $ret['status'] = 'error';
				    $ret['message'] = 'Data jenis_kelamin tidak boleh kosong!';
				} else if (empty($_POST['kode_jenis_kelamin'])) {
				    $ret['status'] = 'error';
				    $ret['message'] = 'Data kode_jenis_kelamin tidak boleh kosong!';
				} else if (empty($_POST['kode_gol'])) {
				    $ret['status'] = 'error';
				    $ret['message'] = 'Data kode_gol tidak boleh kosong!';
				} else if (empty($_POST['id_skpd'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data id_skpd tidak boleh kosong!';
                } else if (empty($_POST['nik'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data nik tidak boleh kosong!';
                } else if (empty($_POST['nip'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data nip tidak boleh kosong!';
                } else if (empty($_POST['nama'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data nama tidak boleh kosong!';
                } else if (empty($_POST['tempat_lahir'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tempat_lahir tidak boleh kosong!';
                } else if (empty($_POST['tanggal_lahir'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tanggal_lahir tidak boleh kosong!';
                } else if (empty($_POST['status'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data status tidak boleh kosong!';
                } else if (empty($_POST['gol_ruang'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data gol_ruang tidak boleh kosong!';
                } else if (empty($_POST['tmt_pangkat'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tmt_pangkat tidak boleh kosong!';
                } else if (empty($_POST['eselon'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data eselon tidak boleh kosong!';
                } else if (empty($_POST['jabatan'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data jabatan tidak boleh kosong!';
                } else if (empty($_POST['tipe_pegawai'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tipe_pegawai tidak boleh kosong!';
                } else if (empty($_POST['tmt_jabatan'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tmt_jabatan tidak boleh kosong!';
                } else if (empty($_POST['agama'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data agama tidak boleh kosong!';
                } else if (empty($_POST['alamat'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data alamat tidak boleh kosong!';
                } else if (empty($_POST['no_hp'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data no_hp tidak boleh kosong!';
                } else if (empty($_POST['satuan_kerja'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data satuan_kerja tidak boleh kosong!';
                } else if (empty($_POST['unit_kerja_induk'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data unit_kerja_induk tidak boleh kosong!';
                } else if (empty($_POST['tmt_pensiun'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tmt_pensiun tidak boleh kosong!';
                } else if (empty($_POST['pendidikan'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data pendidikan tidak boleh kosong!';
                } else if (empty($_POST['kode_pendidikan'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data kode_pendidikan tidak boleh kosong!';
                } else if (empty($_POST['nama_sekolah'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data nama_sekolah tidak boleh kosong!';
                } else if (empty($_POST['nama_pendidikan'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data nama_pendidikan tidak boleh kosong!';
                } else if (empty($_POST['lulus'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data lulus tidak boleh kosong!';
                } else if (empty($_POST['karpeg'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data karpeg tidak boleh kosong!';
                } else if (empty($_POST['karis_karsu'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data karis_karsu tidak boleh kosong!';
                } else if (empty($_POST['nilai_prestasi'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data nilai_prestasi tidak boleh kosong!';
                } else if (empty($_POST['email'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data email tidak boleh kosong!';
                } else if (empty($_POST['tahun'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tahun tidak boleh kosong!';
                } else if (empty($_POST['user_role'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data user_role tidak boleh kosong!';
                } else {
                	$id_skpd = $_POST['id_skpd'];
		            $nik = $_POST['nik'];
		            $nip = $_POST['nip'];
		            $nama = $_POST['nama'];
		            $tempat_lahir = $_POST['tempat_lahir'];
		            $tanggal_lahir = $_POST['tanggal_lahir'];
		            $status = $_POST['status'];
		            $gol_ruang = $_POST['gol_ruang'];
		            $tmt_pangkat = $_POST['tmt_pangkat'];
		            $eselon = $_POST['eselon'];
		            $jabatan = $_POST['jabatan'];
		            $tipe_pegawai = $_POST['tipe_pegawai'];
		            $tmt_jabatan = $_POST['tmt_jabatan'];
		            $agama = $_POST['agama'];
		            $alamat = $_POST['alamat'];
		            $no_hp = $_POST['no_hp'];
		            $satuan_kerja = $_POST['satuan_kerja'];
		            $unit_kerja_induk = $_POST['unit_kerja_induk'];
		            $tmt_pensiun = $_POST['tmt_pensiun'];
		            $pendidikan = $_POST['pendidikan'];
		            $kode_pendidikan = $_POST['kode_pendidikan'];
		            $nama_sekolah = $_POST['nama_sekolah'];
		            $nama_pendidikan = $_POST['nama_pendidikan'];
		            $lulus = $_POST['lulus'];
		            $karpeg = $_POST['karpeg'];
		            $karis_karsu = $_POST['karis_karsu'];
		            $nilai_prestasi = $_POST['nilai_prestasi'];
		            $email = $_POST['email'];
		            $tahun = $_POST['tahun'];
		            $user_role = $_POST['user_role'];
					$gelar_depan = $_POST['gelar_depan'];
					$gelar_belakang = $_POST['gelar_belakang'];
					$nama_lengkap = $_POST['nama_lengkap'];
					$jenis_kelamin = $_POST['jenis_kelamin'];
					$kode_jenis_kelamin = $_POST['kode_jenis_kelamin'];
					$kode_gol = $_POST['kode_gol'];
	                $data = array(
	                    'id_skpd' => $id_skpd,
	                    'nik' => $nik,
	                    'nip' => $nip,
	                    'nama' => $nama,
	                    'tempat_lahir' => $tempat_lahir,
	                    'tanggal_lahir' => $tanggal_lahir,
	                    'status' => $status,
	                    'gol_ruang' => $gol_ruang,
	                    'tmt_pangkat' => $tmt_pangkat,
	                    'eselon' => $eselon,
	                    'jabatan' => $jabatan,
	                    'tipe_pegawai' => $tipe_pegawai,
	                    'tmt_jabatan' => $tmt_jabatan,
	                    'agama' => $agama,
	                    'alamat' => $alamat,
	                    'no_hp' => $no_hp,
	                    'satuan_kerja' => $satuan_kerja,
	                    'unit_kerja_induk' => $unit_kerja_induk,
	                    'tmt_pensiun' => $tmt_pensiun,
	                    'pendidikan' => $pendidikan,
	                    'kode_pendidikan' => $kode_pendidikan,
	                    'nama_sekolah' => $nama_sekolah,
	                    'nama_pendidikan' => $nama_pendidikan,
	                    'lulus' => $lulus,
	                    'karpeg' => $karpeg,
	                    'karis_karsu' => $karis_karsu,
	                    'nilai_prestasi' => $nilai_prestasi,
	                    'email' => $email,
	                    'tahun' => $tahun,
	                    'user_role' => $user_role,
	                    'gelar_depan' => $gelar_depan,
						'gelar_belakang' => $gelar_belakang,
						'nama_lengkap' => $nama_lengkap,
						'jenis_kelamin' => $jenis_kelamin,
						'kode_jenis_kelamin' => $kode_jenis_kelamin,
						'kode_gol' => $kode_gol,
	                    'active' => 1,
	                    'update_at' => current_time('mysql')
	                );
	                if(!empty($_POST['id_data'])){
	                    $wpdb->update('absensi_data_pegawai', $data, array(
	                        'id' => $_POST['id_data']
	                    ));
	                    $ret['message'] = 'Berhasil update data!';
	                }else{
	                    $cek_id = $wpdb->get_row($wpdb->prepare('
	                        SELECT
	                            id,
	                            active
	                        FROM absensi_data_pegawai
	                        WHERE id_pegawai=%s
	                    ', $id_pegawai), ARRAY_A);
	                    if(empty($cek_id)){
	                        $wpdb->insert('absensi_data_pegawai', $data);
	                    }else{
	                        if($cek_id['active'] == 0){
	                            $wpdb->update('absensi_data_pegawai', $data, array(
	                                'id' => $cek_id['id']
	                            ));
	                        }else{
	                            $ret['status'] = 'error';
	                            $ret['message'] = 'Gagal disimpan. Data pegawai_lembur dengan id_pegawai="'.$id_pegawai.'" sudah ada!';
	                        }
	                    }
	                }
	            }
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

	public function get_datatable_pasar(){
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data'  => array()
        );

        if(!empty($_POST)){
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                $user_id = um_user( 'ID' );
                $user_meta = get_userdata($user_id);
                $params = $columns = $totalRecords = $data = array();
                $params = $_REQUEST;
                $columns = array( 
				   0 => 'nama_pasar',
				   1 => 'alamat_pasar',
                   2 => 'id'
                );
                $where = $sqlTot = $sqlRec = "";

                // check search value exist
                if( !empty($params['search']['value']) ) {
                    $where .=" AND ( id LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
                }

                // getting total number records without any search
                $sql_tot = "SELECT count(id) as jml FROM `absensi_data_pasar`";
                $sql = "SELECT ".implode(', ', $columns)." FROM `absensi_data_pasar`";
                $where_first = " WHERE 1=1 AND active=1";
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
                $sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir'].$limit;

                $queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
                $totalRecords = $queryTot[0]['jml'];
                $queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

                foreach($queryRecords as $recKey => $recVal){
                    $btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
                    $btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-trash"></i></a>';
                    $queryRecords[$recKey]['aksi'] = $btn;
                }

                $json_data = array(
                    "draw"            => intval( $params['draw'] ),   
                    "recordsTotal"    => intval( $totalRecords ),  
                    "recordsFiltered" => intval($totalRecords),
                    "data"            => $queryRecords,
                    "sql"             => $sqlRec
                );

                die(json_encode($json_data));
            }else{
                $return = array(
                    'status' => 'error',
                    'message'   => 'Api Key tidak sesuai!'
                );
            }
        }else{
            $return = array(
                'status' => 'error',
                'message'   => 'Format tidak sesuai!'
            );
        }
        die(json_encode($return));
    }

    public function hapus_data_pasar_by_id(){
	    global $wpdb;
	    $ret = array(
	        'status' => 'success',
	        'message' => 'Berhasil hapus data!',
	        'data' => array()
	    );
	    if(!empty($_POST)){
	        if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
	            $ret['data'] = $wpdb->update('absensi_data_pasar', array('active' => 0), array(
	                'id' => $_POST['id']
	            ));
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

	public function get_data_pasar_by_id(){
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data' => array()
        );
        if(!empty($_POST)){
            if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                $ret['data'] = $wpdb->get_row($wpdb->prepare('
                    SELECT 
                        *
                    FROM absensi_data_pasar
                    WHERE id=%d
                ', $_POST['id']), ARRAY_A);
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

    public function tambah_data_pasar(){
        global $wpdb;
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil simpan data!',
            'data' => array()
        );
        if(!empty($_POST)){
            if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                if (empty($_POST['nama_pasar'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data nama pasar tidak boleh kosong!';
                } else if (empty($_POST['alamat_pasar'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data alamat pasar tidak boleh kosong!';
                } else if (empty($_POST['tahun'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tahun tidak boleh kosong!';
                } else {
                    $nama_pasar = $_POST['nama_pasar'];
                    $alamat_pasar = $_POST['alamat_pasar'];
                    $tahun = $_POST['tahun'];
                    $data = array(
                        'nama_pasar' => $nama_pasar,
                        'alamat_pasar' => $alamat_pasar,
                        'tahun_anggaran' => $tahun,
                        'active' => 1,
                        'update_at' => current_time('mysql')
                    );
                    if(!empty($_POST['id_data'])){
                        $wpdb->update('absensi_data_pasar', $data, array(
                            'id' => $_POST['id_data']
                        ));
                        $ret['message'] = 'Berhasil update data!';
                    }else{
                        $cek_id = $wpdb->get_row($wpdb->prepare('
                            SELECT
                                id,
                                active
                            FROM absensi_data_pasar
                            WHERE id=%s
                            AND tahun_anggaran=%d
                        ', $_POST['id_data'], $tahun), ARRAY_A);
                        if(empty($cek_id)){
                            $wpdb->insert('absensi_data_pasar', $data);
                        }else{
                            if($cek_id['active'] == 0){
                                $wpdb->update('absensi_data_pasar', $data, array(
                                    'id' => $cek_id['id']
                                ));
                            }else{
                                $ret['status'] = 'error';
                                $ret['message'] = 'Gagal disimpan. Data pasar dengan id pasar="'.$_POST['id_data'].'" sudah ada!';
                            }
                        }
                    }
                }
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