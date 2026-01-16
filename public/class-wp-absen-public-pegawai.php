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
 * @package    Wp_Absen
 * @subpackage Wp_Absen/public
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
require_once ABSEN_PLUGIN_PATH . "/public/trait/CustomTrait.php";

class Wp_Absen_Public_Pegawai {

	use CustomTraitAbsen;

	private $plugin_name;
	private $version;
	private $functions;

	public function __construct( $plugin_name, $version, $functions ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->functions = $functions;

	}

    public function management_data_pegawai_absensi($atts){
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-absen-management-data-pegawai.php';
    }

    function copy_data_pegawai()
	{
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil copy data kuesioner menpan!',
			'data'  => array()
		);

		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
				if ($ret['status'] != 'error' && empty($_POST['tahun_sumber'])) {
					$ret['status'] = 'error';
					$ret['message'] = 'Tahun Sumber Tidak Boleh Kosong!';
				} else if ($ret['status'] != 'error' && empty($_POST['tahun_tujuan'])) {
					$ret['status'] = 'error';
					$ret['message'] = 'Tahun Halaman Ini Tidak Boleh Kosong!';
				}

				if ($ret['status'] != 'error') {
					$this_tahun = $_POST['tahun_tujuan'];
					$tahun_sumber = $_POST['tahun_sumber'];

					/** Kosongkan tabel data yang akan disii data baru hasil copy */
					$wpdb->update(
						'siakar_data_pegawai',
						array(
							'active' => 0
						),
						array(
							'tahun' => $this_tahun
						)
					);

					$data_sumber = $wpdb->get_results($wpdb->prepare('
						SELECT
							*
						FROM
							siakar_data_pegawai
						WHERE tahun=%d
							AND active=%d
							AND status_kerja=1
						', $tahun_sumber, 1), ARRAY_A);
					if (!empty($data_sumber)) {
						foreach ($data_sumber as $k => $sumber) {
							$data_pegawai = array(
								'nik' => $sumber['nik'],
		                        'nama' => $sumber['nama'],
		                        'tempat_lahir' => $sumber['tempat_lahir'],
		                        'tanggal_lahir' => $sumber['tanggal_lahir'],
		                        'jenis_kelamin' => $sumber['jenis_kelamin'],
		                        'status' => $sumber['status'],
		                        'status_teks' => $sumber['status_teks'],
		                        'jabatan' => $sumber['jabatan'],
		                        'agama' => $sumber['agama'],
		                        'no_hp' => $sumber['no_hp'],
		                        'alamat' => $sumber['alamat'],
		                        'pendidikan_terakhir' => $sumber['pendidikan_terakhir'],
		                        'pendidikan_sekarang' => $sumber['pendidikan_sekarang'],
		                        'nama_sekolah' => $sumber['nama_sekolah'],
		                        'lulus' => $sumber['lulus'],
		                        'email' => $sumber['email'],
		                        'karpeg' => $sumber['karpeg'],
		                        'tanggal_mulai' => $sumber['tanggal_mulai'],
		                        'tanggal_selesai' => $sumber['tanggal_selesai'],
		                        'gaji' => $sumber['gaji'],
		                        'user_role' => $sumber['user_role'],
		                        'status_kerja' => $sumber['status_kerja'],
								'active' => 1,
								'tahun' => $this_tahun
							);

							$wpdb->insert(
								'siakar_data_pegawai',
								$data_pegawai
							);
						}
					}
				}
			} else {
				$ret = array(
					'status' => 'error',
					'message'   => 'Api Key tidak sesuai!'
				);
			}
		} else {
			$ret = array(
				'status' => 'error',
				'message'   => 'Format tidak sesuai!'
			);
		}
		die(json_encode($ret));
	}

    public function get_master_data(){
	    $ret = array(
	        'status' => 'success',
	        'message' => 'Berhasil get master data!',
	        'data' => array()
	    );
	    
	    if(!empty($_POST)){
	        if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
	            $ret['data'] = array(
	                'jenis_kelamin' => $this->get_master_jenis_kelamin(),
	                'agama' => $this->get_master_agama(),
	                'pendidikan' => $this->get_master_pendidikan(),
	                'status_pegawai' => $this->get_master_status_pegawai(),
	                'user_role' => $this->get_master_user_role(),
	                'hari' => $this->get_master_hari(),
	                'bulan' => $this->get_master_bulan(),
	                'jenis_absensi' => $this->get_master_jenis_absensi()
	            );
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

    public function get_master_jenis_kelamin(){
        return array(
            array('value' => 'L', 'label' => 'Laki-laki'),
            array('value' => 'P', 'label' => 'Perempuan')
        );
    }

    public function get_master_agama(){
        return array(
            array('value' => 'Islam', 'label' => 'Islam'),
            array('value' => 'Kristen', 'label' => 'Kristen'),
            array('value' => 'Katolik', 'label' => 'Katolik'),
            array('value' => 'Hindu', 'label' => 'Hindu'),
            array('value' => 'Buddha', 'label' => 'Buddha'),
            array('value' => 'Konghucu', 'label' => 'Konghucu')
        );
    }

    public function get_master_pendidikan(){
        return array(
            array('value' => 'SD', 'label' => 'SD'),
            array('value' => 'SMP', 'label' => 'SMP'),
            array('value' => 'SMA/SMK', 'label' => 'SMA/SMK'),
            array('value' => 'D3', 'label' => 'D3'),
            array('value' => 'S1', 'label' => 'S1'),
            array('value' => 'S2', 'label' => 'S2'),
            array('value' => 'S3', 'label' => 'S3'),
            array('value' => 'Tidak Sedang Menempuh', 'label' => 'Tidak Sedang Menempuh')
        );
    }

    public function get_master_status_pegawai(){
        return array(
            array('value' => '1', 'label' => 'Pegawai Tetap'),
            array('value' => '2', 'label' => 'Pegawai Kontrak'),
            array('value' => '3', 'label' => 'Pegawai Magang'),
            array('value' => '4', 'label' => 'Pegawai Probation'),
            array('value' => '5', 'label' => 'Lainnya')
        );
    }

    public function get_master_user_role(){
        return array(
            array('value' => 'admin_instansi', 'label' => 'Admin Instansi'),
            array('value' => 'pegawai', 'label' => 'Pegawai')
        );
    }

    public function get_status_pegawai_text($status, $status_teks = null){
        $master = $this->get_master_status_pegawai();
        foreach($master as $item){
            if($item['value'] == $status){
                if($status == '5' && !empty($status_teks)){
                    return $status_teks;
                }
                return $item['label'];
            }
        }
        return '-';
    }

    public function get_master_hari() {
        return array(
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        );
    }

    public function get_master_bulan() {
        return array(
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        );
    }

    public function get_master_jenis_absensi() {
        return array(
            1 => array('label' => 'Masuk', 'class' => 'shift-masuk'),
            2 => array('label' => 'Ijin', 'class' => 'shift-izin'),
            3 => array('label' => 'Sakit', 'class' => 'shift-sakit'),
            4 => array('label' => 'Ganti Hari', 'class' => 'shift-ganti-hari'),
            5 => array('label' => 'Alasan', 'class' => 'shift-alasan'),
            6 => array('label' => 'Cuti', 'class' => 'shift-cuti'),
            7 => array('label' => 'Lembur', 'class' => 'shift-lembur')
        );
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
	            if (empty($_POST['tahun'])) {
	                $ret['status'] = 'error';
	                $ret['message'] = 'Tahun kosong!';
	            }
	            $params = $columns = $totalRecords = $data = array();
	            $params = $_REQUEST;
	            $columns = array( 
	               0 => 'nik',
	               1 => 'nama',
	               2 => 'tempat_lahir',
	               3 => 'tanggal_lahir',
	               4 => 'jenis_kelamin',
	               5 => 'jabatan',
	               6 => 'agama',
	               7 => 'no_hp',
	               8 => 'alamat',
	               9 => 'pendidikan_terakhir',
	               10 => 'pendidikan_sekarang',
	               11 => 'nama_sekolah',
	               12 => 'lulus',
	               13 => 'email',
	               14 => 'karpeg',
	               15 => 'tanggal_mulai',
	               16 => 'tanggal_selesai',
	               17 => 'gaji',
	               18 => 'user_role',
	               19 => 'status',
	               20 => 'id',
                   21 => 'nama_instansi'
	            );
	            $where = $sqlTot = $sqlRec = "";

	            if( !empty($params['search']['value']) ) {
	                $where .=" AND ( p.nama LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");  
	                $where .=" OR p.nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
                    $where .=" OR i.nama_instansi LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%").")";
	            }

	            $status_kerja_filter = isset($_POST['status_kerja_filter']) ? $_POST['status_kerja_filter'] : '';
	            if($status_kerja_filter !== ''){
	                $where .= $wpdb->prepare(" AND p.status_kerja = %d", $status_kerja_filter);
	            }
                
                // Permission Check
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
                
                if ($is_admin_instansi) {
                    // Check user's assigned instansi
                    $my_instansi = $wpdb->get_var($wpdb->prepare("SELECT id FROM absensi_data_instansi WHERE id_user = %d AND active=1 LIMIT 1", $current_user->ID));
                    if ($my_instansi) {
                         $where .= $wpdb->prepare(" AND p.id_instansi = %d", $my_instansi);
                    } else {
                         // User has role but no assigned instansi -> look at nothing
                         $where .= " AND 1=0"; 
                    }
                }

	            $sql_tot = "SELECT count(p.id) as jml FROM `absensi_data_pegawai` p JOIN `absensi_data_instansi` i ON p.id_instansi = i.id";
	            $sql = "SELECT p.*, p.status_teks, p.status_kerja, i.nama_instansi FROM `absensi_data_pegawai` p JOIN `absensi_data_instansi` i ON p.id_instansi = i.id";
	            $where_first = $wpdb->prepare(" WHERE 1=1 AND p.active = 1 AND p.tahun = %d", $_POST['tahun']);
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
	                $status_kerja = isset($recVal['status_kerja']) ? $recVal['status_kerja'] : 1;
	                
	                $btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
	                $btn .= ' <a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\', '.$status_kerja.'); return false;" href="#" title="Hapus Data"><i class="dashicons dashicons-trash"></i></a>';
	                $queryRecords[$recKey]['aksi'] = $btn;
	                
	                $status_text = $this->get_status_pegawai_text($recVal['status'], $recVal['status_teks']);
	                $status_kerja = isset($recVal['status_kerja']) ? $recVal['status_kerja'] : 1;
	                
	                if($status_kerja == 1){
	                    $badge = '<span class="status-badge status-active">Active</span>';
	                } else {
	                    $badge = '<span class="status-badge status-inactive">Non Active</span>';
	                }
	                
	                
                    $queryRecords[$recKey]['nama_instansi'] = $recVal['nama_instansi'];
	                $queryRecords[$recKey]['status_display'] = $status_text . '<br>' . $badge;
	                
	                if(!empty($recVal['gaji'])){
	                    $queryRecords[$recKey]['gaji'] = 'Rp ' . number_format($recVal['gaji'], 0, ',', '.');
	                } else {
	                    $queryRecords[$recKey]['gaji'] = '-';
	                }
	                
	                if(empty($recVal['tanggal_selesai']) || $recVal['tanggal_selesai'] == '0000-00-00'){
	                    $queryRecords[$recKey]['tanggal_selesai'] = '-';
	                }
	            }

	            $json_data = array(
	                "draw"            => intval( $params['draw'] ),   
	                "recordsTotal"    => intval( $totalRecords ),  
	                "recordsFiltered" => intval($totalRecords),
	                "data"            => $queryRecords
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
                $tipe = isset($_POST['tipe']) ? $_POST['tipe'] : 'hapus';
                
                if($tipe == 'hapus'){
                    // Hapus data (set active = 0)
                    $ret['data'] = $wpdb->update('absensi_data_pegawai', 
                        array('active' => 0), 
                        array('id' => $_POST['id'])
                    );
                    $ret['message'] = 'Data pegawai berhasil dihapus!';
                } else if($tipe == 'nonaktif'){
                    // Nonaktifkan pegawai (set status_kerja = 0, active tetap 1)
                    $ret['data'] = $wpdb->update('absensi_data_pegawai', 
                        array('status_kerja' => 0), 
                        array('id' => $_POST['id'])
                    );
                    $ret['message'] = 'Pegawai berhasil dinonaktifkan!';
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
                if (empty($_POST['nik'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data NIK tidak boleh kosong!';
                } else if (empty($_POST['nama'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data Nama tidak boleh kosong!';
                } else if (empty($_POST['jenis_kelamin'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Jenis Kelamin tidak boleh kosong!';
                } else if (empty($_POST['status'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Status Pegawai tidak boleh kosong!';
                } else if ($_POST['status'] == '5' && empty($_POST['status_teks'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Status Pegawai Lainnya tidak boleh kosong!';
                } else if (empty($_POST['jabatan'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Jabatan tidak boleh kosong!';
                } else if (empty($_POST['tanggal_mulai'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Tanggal Mulai tidak boleh kosong!';
                } else if ($_POST['status'] != '1' && empty($_POST['tanggal_selesai'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Tanggal Selesai tidak boleh kosong!';
                } else if (empty($_POST['id_instansi'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Instansi tidak boleh kosong!';
                } else {
                    $nik = $_POST['nik'];
                    $nama = $_POST['nama'];
                    $tempat_lahir = !empty($_POST['tempat_lahir']) ? $_POST['tempat_lahir'] : null;
                    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
                    $jenis_kelamin = $_POST['jenis_kelamin'];
                    $status = $_POST['status'];
                    $status_teks = !empty($_POST['status_teks']) ? $_POST['status_teks'] : null;
                    $jabatan = $_POST['jabatan'];
                    $agama = !empty($_POST['agama']) ? $_POST['agama'] : null;
                    $no_hp = !empty($_POST['no_hp']) ? $_POST['no_hp'] : null;
                    $alamat = !empty($_POST['alamat']) ? $_POST['alamat'] : null;
                    $pendidikan_terakhir = !empty($_POST['pendidikan_terakhir']) ? $_POST['pendidikan_terakhir'] : null;
                    $pendidikan_sekarang = !empty($_POST['pendidikan_sekarang']) ? $_POST['pendidikan_sekarang'] : null;
                    $nama_sekolah = !empty($_POST['nama_sekolah']) ? $_POST['nama_sekolah'] : null;
                    $lulus = !empty($_POST['lulus']) ? $_POST['lulus'] : null;
                    $email = !empty($_POST['email']) ? $_POST['email'] : null;
                    $karpeg = !empty($_POST['karpeg']) ? $_POST['karpeg'] : null;
                    $tanggal_mulai = $_POST['tanggal_mulai'];
                    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
                    $gaji = !empty($_POST['gaji']) ? $_POST['gaji'] : null;
                    $id_instansi = $_POST['id_instansi'];
                    $user_role = 'pegawai'; // Default role
                    $tahun = !empty($_POST['tahun']) ? $_POST['tahun'] : date('Y');

                    $data = array(
                        'nik' => $nik,
                        'nama' => $nama,
                        'tempat_lahir' => $tempat_lahir,
                        'tanggal_lahir' => $tanggal_lahir,
                        'jenis_kelamin' => $jenis_kelamin,
                        'status' => $status,
                        'status_teks' => $status_teks,
                        'jabatan' => $jabatan,
                        'agama' => $agama,
                        'no_hp' => $no_hp,
                        'alamat' => $alamat,
                        'pendidikan_terakhir' => $pendidikan_terakhir,
                        'pendidikan_sekarang' => $pendidikan_sekarang,
                        'nama_sekolah' => $nama_sekolah,
                        'lulus' => $lulus,
                        'email' => $email,
                        'karpeg' => $karpeg,
                        'tanggal_mulai' => $tanggal_mulai,
                        'tanggal_selesai' => $tanggal_selesai,
                        'gaji' => $gaji,
                        'id_instansi' => $id_instansi,
                        'user_role' => $user_role,
                        'tahun' => $tahun,
                        'status_kerja' => 1,
                        'active' => 1,
                        'update_at' => current_time('mysql')
                    );
                    
                    if(!empty($_POST['id_data'])){
                        $wpdb->update('absensi_data_pegawai', $data, array(
                            'id' => $_POST['id_data']
                        ));
                        $ret['message'] = 'Berhasil update data!';
                    }else{
                        $cek_nik = $wpdb->get_row($wpdb->prepare('
                            SELECT
                                id,
                                active
                            FROM absensi_data_pegawai
                            WHERE nik=%s
                        ', $nik), ARRAY_A);
                        
                        if(empty($cek_nik)){
                            $wpdb->insert('absensi_data_pegawai', $data);
                        }else{
                            if($cek_nik['active'] == 0){
                                $wpdb->update('absensi_data_pegawai', $data, array(
                                    'id' => $cek_nik['id']
                                ));
                            }else{
                                $ret['status'] = 'error';
                                $ret['message'] = 'Gagal disimpan. Data pegawai dengan NIK="'.$nik.'" sudah ada!';
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
