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

    public function management_data_pegawai_absensi($atts) {
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-absen-management-data-pegawai.php';
    }

    function copy_data_pegawai() {
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
                                'agama' => $sumber['agama'],
                                'no_hp' => $sumber['no_hp'],
                                'alamat' => $sumber['alamat'],
                                'pendidikan_terakhir' => $sumber['pendidikan_terakhir'],
                                'pendidikan_sekarang' => $sumber['pendidikan_sekarang'],
                                'nama_sekolah' => $sumber['nama_sekolah'],
                                'lulus' => $sumber['lulus'],
                                'email' => $sumber['email'],
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

    public function get_master_data() {
        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get master data!',
            'data' => array()
        );

        if (!empty($_POST)) {
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                $ret['data'] = array(
                    'jenis_kelamin' => $this->get_master_jenis_kelamin(),
                    'agama' => $this->get_master_agama(),
                    'pendidikan' => $this->get_master_pendidikan(),
                    'admin_instansi' => $this->get_master_admin_instansi(),
                    'user_role' => $this->get_master_user_role(),
                    'hari' => $this->get_master_hari(),
                    'bulan' => $this->get_master_bulan(),
                    'jenis_absensi' => $this->get_master_jenis_absensi()
                );
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

    public function get_master_jenis_kelamin() {
        return array(
            array('value' => 'L', 'label' => 'Laki-laki'),
            array('value' => 'P', 'label' => 'Perempuan')
        );
    }

    public function get_master_agama() {
        return array(
            array('value' => 'Islam', 'label' => 'Islam'),
            array('value' => 'Kristen', 'label' => 'Kristen'),
            array('value' => 'Katolik', 'label' => 'Katolik'),
            array('value' => 'Hindu', 'label' => 'Hindu'),
            array('value' => 'Buddha', 'label' => 'Buddha'),
            array('value' => 'Konghucu', 'label' => 'Konghucu')
        );
    }

    public function get_master_pendidikan() {
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

    public function get_master_user_role() {
        return array(
            array('value' => 'admin_instansi', 'label' => 'Admin Instansi'),
            array('value' => 'pegawai', 'label' => 'Pegawai')
        );
    }

    public function get_master_admin_instansi() {
        $current_user = wp_get_current_user();
        $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );

        if ($is_admin_instansi) {
            return array(array(
                'value' => $current_user->ID,
                'label' => $current_user->display_name
            ));
        }

        $users = get_users(array('role' => 'admin_instansi'));
        $data = array();

        foreach ($users as $user) {
            $data[] = array(
                'value' => $user->ID,
                'label' => $user->display_name
            );
        }

        return $data;
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

    public function get_datatable_pegawai() {
        global $wpdb;

        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data'  => array()
        );

        if (!empty($_POST)) {
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
                    2 => 'jabatan', // Insert Jabatan here
                    3 => 'no_hp',
                    4 => 'email',
                    5 => 'id_instansi', // Corrected: Use DB column for SQL select/sort
                    6 => 'active',
                    7 => 'id'
                );

                $where = $sqlTot = $sqlRec = "";

                if (!empty($params['search']['value'])) {
                    $where .= " AND ( nama LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%");  
                    $where .= " OR nik LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%").")";
                }

                $sql_tot = "SELECT count(id) as jml FROM `absensi_data_pegawai`";
                $sql = "SELECT ".implode(', ', $columns)." FROM `absensi_data_pegawai`";
                $where_first = $wpdb->prepare(" WHERE 1=1 AND tahun = %d", $_POST['tahun']);

                // Filter for Admin Instansi
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );
                if ($is_admin_instansi) {
                    $where_first .= $wpdb->prepare(" AND id_instansi = %d", $current_user->ID);
                }

                $sqlTot .= $sql_tot.$where_first;
                $sqlRec .= $sql.$where_first;
                if (isset($where) && $where != '') {
                    $sqlTot .= $where;
                    $sqlRec .= $where;
                }

                $limit = '';
                if ($params['length'] != -1) {
                    $limit = "  LIMIT ".$wpdb->prepare('%d', $params['start'])." ,".$wpdb->prepare('%d', $params['length']);
                }

                // ORDER BY Logic (Fix for missing order param)
                $order_clause = " ORDER BY id DESC"; // Default
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
                    // SYNC DATA WITH WORDPRESS USER
                    $wp_user = false;
                    $user_data_changed = false;
                    $update_data = array();

                    // 1. Try to get user by id_user
                    if (!empty($recVal['id_user'])) {
                        $wp_user = get_user_by('id', $recVal['id_user']);
                    }

                    // 2. If not found, try by NIK (username)
                    if (!$wp_user && !empty($recVal['nik'])) {
                        $wp_user = get_user_by('login', $recVal['nik']);
                        if ($wp_user) {
                            $update_data['id_user'] = $wp_user->ID;
                            $user_data_changed = true;
                        }
                    }

                    // 3. If still not found, try by Email
                    if (!$wp_user && !empty($recVal['email'])) {
                        $wp_user = get_user_by('email', $recVal['email']);
                        if ($wp_user) {
                            $update_data['id_user'] = $wp_user->ID;
                            $user_data_changed = true;
                        }
                    }

                    // 4. If User Found, Compare and Sync
                    if ($wp_user) {
                        // Check NIK (Username)
                        if ($recVal['nik'] !== $wp_user->user_login) {
                            $update_data['nik'] = $wp_user->user_login;
                            $recVal['nik'] = $wp_user->user_login; // Update local var for display
                            $user_data_changed = true;
                        }

                        // Check Nama (First Name)
                        // Note: WP first_name might be empty, if so keep DB name? 
                        // Prompt says "Nama (first name user wordpress)". We assume WP is master.
                        if (!empty($wp_user->first_name) && $recVal['nama'] !== $wp_user->first_name) {
                            $update_data['nama'] = $wp_user->first_name;
                            $recVal['nama'] = $wp_user->first_name;
                            $user_data_changed = true;
                        }

                        // Check Email
                        if ($recVal['email'] !== $wp_user->user_email) {
                            $update_data['email'] = $wp_user->user_email;
                            $recVal['email'] = $wp_user->user_email;
                            $user_data_changed = true;
                        }

                        // Execute Update if needed
                        if ($user_data_changed && !empty($update_data)) {
                            $wpdb->update('absensi_data_pegawai', $update_data, array('id' => $recVal['id']));
                        }
                    }

                    // Get Admin Instansi Name
                    $instansi_name = '-';
                    if (!empty($recVal['id_instansi'])) {
                        $instansi_user = get_userdata($recVal['id_instansi']);
                        if ($instansi_user) {
                            $instansi_name = $instansi_user->display_name;
                        }
                    }

                    $queryRecords[$recKey]['admin_instansi_name'] = $instansi_name;
                    foreach ($queryRecords as $recKey => $recVal) {
                        $btn = '';
                        $btn .= '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
                        $btn .= ' <a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Hapus Data"><i class="dashicons dashicons-trash"></i></a>';

                        if ($recVal["active"] == 1) {
                            $btn .= ' <a class="btn btn-sm btn-secondary" onclick="toggle_status_pegawai(\'' . $recVal["id"] . '\', 0); return false;" href="#" title="Nonaktifkan"><i class="dashicons dashicons-hidden"></i></a>';
                            $status_badge = '<span class="badge badge-success" style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 4px;">Aktif</span>';
                        } else {
                            $btn .= ' <a class="btn btn-sm btn-success" onclick="toggle_status_pegawai(\'' . $recVal["id"] . '\', 1); return false;" href="#" title="Aktifkan"><i class="dashicons dashicons-visibility"></i></a>';
                            $status_badge = '<span class="badge badge-secondary" style="background-color: #6c757d; color: white; padding: 5px 10px; border-radius: 4px;">Tidak Aktif</span>';
                        }

                        $queryRecords[$recKey]["status_badge"] = $status_badge;
                        $queryRecords[$recKey]['aksi'] = $btn;
                    }
                }

                $json_data = array(
                    "draw"            => intval( $params['draw'] ),   
                    "recordsTotal"    => intval( $totalRecords ),  
                    "recordsFiltered" => intval($totalRecords),
                    "data"            => $queryRecords
                );

                die(json_encode($json_data));
            } else {
                $return = array(
                    'status' => 'error',
                    'message'   => 'Api Key tidak sesuai!'
                );
            }
        } else {
            $return = array(
                'status' => 'error',
                'message'   => 'Format tidak sesuai!'
            );
        }
        die(json_encode($return));
	}

    public function hapus_data_pegawai_by_id() {
        global $wpdb;

        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil hapus data!',
            'data' => array()
        );

        if (!empty($_POST)) {
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                // Check Ownership for Admin Instansi
                $current_user = wp_get_current_user();
                $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );

                $existing_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_data_pegawai WHERE id = %d", $_POST['id']));

                if (!$existing_data) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tidak ditemukan!';
                    die(json_encode($ret));
                }

                if ($is_admin_instansi) {
                    if ($existing_data->id_instansi != $current_user->ID) {
                        $ret['status'] = 'error';
                        $ret['message'] = 'Anda tidak memiliki hak akses untuk menghapus data ini!';
                        die(json_encode($ret)); // Stop execution
                    }
                }

                // Hapus Data WordPress User if exists
                if (!empty($existing_data->id_user)) {
                    require_once(ABSPATH.'wp-admin/includes/user.php');
                    wp_delete_user($existing_data->id_user);
                }

                // Hapus data hard delete
                $ret['data'] = $wpdb->delete('absensi_data_pegawai', array('id' => $_POST['id']));
                $ret['message'] = 'Data pegawai dan akun user berhasil dihapus secara permanen!';
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

    public function get_data_pegawai_by_id(){
        global $wpdb;

        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil get data!',
            'data' => array()
        );

        if (!empty($_POST)) {
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                $ret['data'] = $wpdb->get_row($wpdb->prepare('
                    SELECT *
                    FROM absensi_data_pegawai
                    WHERE id=%d
                ', $_POST['id']), ARRAY_A);
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

    public function tambah_data_pegawai() {
        global $wpdb;

        $ret = array(
            'status' => 'success',
            'message' => 'Berhasil simpan data!',
            'data' => array()
        );

        if (!empty($_POST)) {
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
                if (empty($_POST['admin_instansi'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Admin Instansi (Parent Role) tidak boleh kosong!';
                } else if (empty($_POST['nik'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data NIK tidak boleh kosong!';
                } else if (empty($_POST['nama'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data Nama tidak boleh kosong!';
                } else if (empty($_POST['email'])) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Email tidak boleh kosong!';
                } else if (empty($_POST['jenis_kelamin']) && !carbon_get_theme_option('crb_hide_jenis_kelamin')) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Jenis Kelamin tidak boleh kosong!';
                } else {
                    $nik = $_POST['nik'];
                    $nama = $_POST['nama'];
                    $email = !empty($_POST['email']) ? $_POST['email'] : null;

                    $tempat_lahir = !empty($_POST['tempat_lahir']) ? $_POST['tempat_lahir'] : null;
                    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;

                    $jenis_kelamin = $_POST['jenis_kelamin'];
                    $agama = !empty($_POST['agama']) ? $_POST['agama'] : null;

                    $no_hp = !empty($_POST['no_hp']) ? $_POST['no_hp'] : null;
                    $alamat = !empty($_POST['alamat']) ? $_POST['alamat'] : null;

                    $pendidikan_terakhir = !empty($_POST['pendidikan_terakhir']) ? $_POST['pendidikan_terakhir'] : null;
                    $pendidikan_sekarang = !empty($_POST['pendidikan_sekarang']) ? $_POST['pendidikan_sekarang'] : null;

                    $nama_sekolah = !empty($_POST['nama_sekolah']) ? $_POST['nama_sekolah'] : null;
                    $lulus = !empty($_POST['lulus']) ? $_POST['lulus'] : null;

                    $current_user = wp_get_current_user();
                    if (in_array('admin_instansi', (array) $current_user->roles)) {
                        $id_instansi = $current_user->ID;
                    } else {
                        $id_instansi = !empty($_POST['admin_instansi']) ? $_POST['admin_instansi'] : 0;
                    }

                    $user_role = 'pegawai';

                    $tahun = !empty($_POST['tahun']) ? $_POST['tahun'] : date('Y');

                    $data = array(
                        'id_instansi' => $id_instansi,
                        'nik' => $nik,

                        'nama' => $nama,
                        'tempat_lahir' => $tempat_lahir,
                        'tanggal_lahir' => $tanggal_lahir,
                        'jenis_kelamin' => $jenis_kelamin,
                        'agama' => $agama,
                        'no_hp' => $no_hp,

                        'alamat' => $alamat,
                        'jabatan' => !empty($_POST['jabatan']) ? $_POST['jabatan'] : null,
                        'pendidikan_terakhir' => $pendidikan_terakhir,
                        'pendidikan_sekarang' => $pendidikan_sekarang,
                        'nama_sekolah' => $nama_sekolah,
                        'lulus' => $lulus,
                        'email' => $email,
                        'user_role' => $user_role,

                        'tahun' => $tahun,
                        'status_kerja' => 1,
                        'active' => 1,
                        'update_at' => current_time('mysql')
                    );

                    if (!empty($_POST['id_data'])) {
                        // Check Ownership for Admin Instansi
                        $existing_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_data_pegawai WHERE id = %d", $_POST['id_data']));

                        $current_user = wp_get_current_user();
                        $is_admin_instansi = in_array( 'admin_instansi', (array) $current_user->roles ) && !in_array( 'administrator', (array) $current_user->roles );

                        if ($is_admin_instansi) {
                            if ($existing_data->id_instansi != $current_user->ID) {
                                $ret['status'] = 'error';
                                $ret['message'] = 'Anda tidak memiliki hak akses untuk mengedit data ini!';
                                die(json_encode($ret));
                            }
                        }

                        // SYNC TO WORDPRESS USER
                        if (!empty($existing_data->id_user)) {
                            // Update Email and Name
                            wp_update_user(array(
                                'ID' => $existing_data->id_user,
                                'user_email' => $email,
                                'first_name' => $nama
                            ));

                            // Update Username (NIK) if changed
                            if ($existing_data->nik !== $nik) {
                                $wpdb->update($wpdb->users, array('user_login' => $nik), array('ID' => $existing_data->id_user));
                            }
                        }

                        $wpdb->update('absensi_data_pegawai', $data, array(
                            'id' => $_POST['id_data']
                        ));
                        $ret['message'] = 'Berhasil update data!';
                    } else {
                        $cek_nik = $wpdb->get_row($wpdb->prepare('
                            SELECT
                                id,
                                active
                            FROM absensi_data_pegawai
                            WHERE nik=%s
                        ', $nik), ARRAY_A);

                        if (empty($cek_nik)) {
                            // Create WordPress User
                            if (username_exists($nik)) {
                                $ret['status'] = 'error';
                                $ret['message'] = 'Username (NIK) sudah terdaftar sebagai User WordPress!';
                            } elseif (email_exists($email)) {
                                $ret['status'] = 'error';
                                $ret['message'] = 'Email sudah terdaftar sebagai User WordPress!';
                            } else {
                                $userdata = array(
                                    'user_login'    => $nik,
                                    'user_pass'     => $nik, // Password same as Username
                                    'user_email'    => $email,
                                    'first_name'    => $nama,
                                    'role'          => 'pegawai'
                                );

                                $user_id = wp_insert_user($userdata);
                                if (is_wp_error($user_id)) {
                                    $ret['status'] = 'error';
                                    $ret['message'] = 'Gagal membuat User WordPress: ' . $user_id->get_error_message();
                                } else {
                                    // Add User Meta for Admin Instansi (Parent Role)
                                    // Make sure we have the correct ID. 
                                    // If currentUser is admin_instansi, use their ID.
                                    // If currentUser is administrator, use the posted admin_instansi ID.
                                    
                                    // Note: The variable $id_instansi logic above handles this ID selection correctly.
                                    // However, $id_instansi is the WP User ID of the Admin Instansi.
                                    update_user_meta($user_id, 'id_admin_instansi', $id_instansi);

                                    // Proceed to insert into custom table
                                    $data['id_user'] = $user_id;
                                    $wpdb->insert('absensi_data_pegawai', $data);
                                }
                            }
                        } else {
                            if ($cek_nik['active'] == 0) {
                                $wpdb->update('absensi_data_pegawai', $data, array(
                                    'id' => $cek_nik['id']
                                ));
                            } else {
                                $ret['status'] = 'error';
                                $ret['message'] = 'Gagal disimpan. Data pegawai dengan NIK="'.$nik.'" sudah ada!';
                            }
                        }
                    }
                }
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


    public function toggle_status_pegawai() {
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

                // Verify Ownership
                $existing_data = $wpdb->get_row($wpdb->prepare("SELECT id_instansi FROM absensi_data_pegawai WHERE id = %d", $id));

                if (!$existing_data) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tidak ditemukan!';

                    die(json_encode($ret));
                }

                if (!$is_admin && $is_admin_instansi) {
                    if ($existing_data->id_instansi != $current_user->ID) {
                        $ret['status'] = 'error';
                        $ret['message'] = 'Akses Ditolak! Data ini bukan milik anda.';

                        die(json_encode($ret));
                    }
                }

                $new_status = $_POST['status'];

                $wpdb->update(
                    'absensi_data_pegawai',
                    array('active' => $new_status),
                    array('id' => $id)
                );

                $ret['message'] = ($new_status == 1) ? 'Pegawai berhasil Diaktifkan!' : 'Pegawai berhasil Dinonaktifkan!';
            } else {
                $ret["status"] = "error";
                $ret["message"] = "Api Key tidak sesuai!";
            }
        }

        die(json_encode($ret));
    }

    /**
     * Get Master Pegawai for Search (Select2)
     */
    public function get_master_pegawai_search() {
        global $wpdb;
        $ret = array('items' => array());
        
        if (!empty($_GET['api_key']) && $_GET['api_key'] == get_option(ABSEN_APIKEY)) {
            $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
            
            $query = "SELECT id, nama, nik, id_instansi FROM absensi_data_pegawai WHERE active = 1";
            
            // Instansi Filter
            $current_user = wp_get_current_user();
            if (in_array('admin_instansi', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles)) {
                 $query .= $wpdb->prepare(" AND id_instansi = %d", $current_user->ID);
            }
            
            if ($search) {
                $query .= " AND (nama LIKE '%$search%' OR nik LIKE '%$search%')";
            }
            
            $query .= " LIMIT 20";
            
            $results = $wpdb->get_results($query);
            foreach ($results as $row) {
                $ret['items'][] = array(
                    'id' => $row->id,
                    'text' => $row->nama . ' (' . $row->nik . ')',
                    'id_instansi' => $row->id_instansi // Pass this for frontend Use
                );
            }
        }
        die(json_encode($ret));
    }

}
