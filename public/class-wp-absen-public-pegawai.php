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
        global $wpdb;

        $current_user = wp_get_current_user();
        $is_admin_instansi = in_array('admin_instansi', (array) $current_user->roles) 
            && !in_array('administrator', (array) $current_user->roles);

        $data = array();

        if ($is_admin_instansi) {

            $row = $wpdb->get_row($wpdb->prepare('
                SELECT id, id_user, nama_instansi
                FROM absensi_data_instansi
                WHERE id_user = %d
                AND active = 1
            ', $current_user->ID), ARRAY_A);

            if (!empty($row)) {
                $data[] = array(
                    'id_instansi'   => $row['id'],
                    'id_user'       => $row['id_user'],
                    'nama_instansi' => $row['nama_instansi']
                );
            }

        } else {

            $rows = $wpdb->get_results('
                SELECT id, id_user, nama_instansi
                FROM absensi_data_instansi
                WHERE active = 1
                ORDER BY nama_instansi ASC
            ', ARRAY_A);

            foreach ($rows as $row) {
                $data[] = array(
                    'id_instansi'   => $row['id'],
                    'id_user'       => $row['id_user'],
                    'nama_instansi' => $row['nama_instansi']
                );
            }
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

        if (empty($_POST)) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Format tidak sesuai!'
            ]));
        }

        if (empty($_POST['api_key']) || $_POST['api_key'] != get_option(ABSEN_APIKEY)) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Api Key tidak sesuai!'
            ]));
        }

        if (empty($_POST['tahun'])) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Tahun kosong!'
            ]));
        }

        $params = $_REQUEST;
        $tahun  = intval($_POST['tahun']);

        // ===============================
        // CEK ROLE
        // ===============================
        $current_user      = wp_get_current_user();
        $current_user_id   = $current_user->ID;
        $is_admin          = in_array('administrator', $current_user->roles);
        $is_admin_instansi = in_array('admin_instansi', $current_user->roles);

        if (!$is_admin && !$is_admin_instansi) {
            die(json_encode([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses ke halaman ini!'
            ]));
        }

        // ===============================
        // FILTER INSTANSI (ADMIN INSTANSI)
        // ===============================
        $join_instansi  = "";
        $where_instansi = "";

        if ($is_admin_instansi && !$is_admin) {

            // ambil instansi milik admin dari tabel instansi
            $id_instansi = $wpdb->get_var(
                $wpdb->prepare("
                    SELECT id
                    FROM absensi_data_instansi
                    WHERE id_user = %d
                    AND tahun = %d
                    AND active = 1
                    AND deleted_at IS NULL
                ", $current_user_id, $tahun)
            );

            if (empty($id_instansi)) {
                die(json_encode([
                    'status'  => 'error',
                    'message' => 'Admin tidak memiliki instansi aktif!'
                ]));
            }

            $join_instansi = "
                INNER JOIN absensi_data_pegawai_instansi api
                    ON api.id_pegawai = p.id
            ";

            $where_instansi = $wpdb->prepare(
                " AND api.id_instansi = %d
                AND api.tahun = %d
                AND api.active = 1
                AND api.deleted_at IS NULL",
                $id_instansi,
                $tahun
            );
        }

        // ===============================
        // COLUMN DATATABLE
        // ===============================
        $columns = array(
            0 => 'p.nik',
            1 => 'p.nama',
            2 => 'p.jabatan',
            3 => 'p.no_hp',
            4 => 'p.email',
            5 => 'p.active',
            6 => 'p.id'
        );

        $where = "";

        if (!empty($params['search']['value'])) {
            $search = '%' . $wpdb->esc_like($params['search']['value']) . '%';
            $where .= $wpdb->prepare(
                " AND (p.nama LIKE %s OR p.nik LIKE %s)",
                $search,
                $search
            );
        }

        // ===============================
        // QUERY TOTAL
        // ===============================
        $sqlTot = "
            SELECT COUNT(DISTINCT p.id) as jml
            FROM absensi_data_pegawai p
            $join_instansi
            WHERE p.deleted_at IS NULL
            AND p.tahun = %d
            $where_instansi
            $where
        ";

        $sqlTot = $wpdb->prepare($sqlTot, $tahun);

        // ===============================
        // QUERY DATA
        // ===============================
        $sqlRec = "
            SELECT DISTINCT
                p.id,
                p.nik,
                p.nama,
                p.jabatan,
                p.no_hp,
                p.email,
                p.active
            FROM absensi_data_pegawai p
            $join_instansi
            WHERE p.deleted_at IS NULL
            AND p.tahun = %d
            $where_instansi
            $where
        ";

        $sqlRec = $wpdb->prepare($sqlRec, $tahun);

        // ORDER
        $order_clause = " ORDER BY p.id DESC";
        if (isset($params['order'][0]['column']) && isset($columns[$params['order'][0]['column']])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
            $order_clause = " ORDER BY $order_col $order_dir";
        }

        // LIMIT
        $limit = '';
        if ($params['length'] != -1) {
            $limit = $wpdb->prepare(
                " LIMIT %d, %d",
                intval($params['start']),
                intval($params['length'])
            );
        }

        $sqlRec .= $order_clause . $limit;

        $queryTot     = $wpdb->get_results($sqlTot, ARRAY_A);
        $totalRecords = !empty($queryTot) ? intval($queryTot[0]['jml']) : 0;
        $queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

        // ===============================
        // FORMAT OUTPUT
        // ===============================
        foreach ($queryRecords as $recKey => $recVal) {

        // default supaya tidak error DataTables
        $queryRecords[$recKey]['instansi_kode'] = '-';
        $queryRecords[$recKey]['status_badge']  = '-';
        $queryRecords[$recKey]['aksi']          = '-';

        // ===============================
        // AMBIL INSTANSI + KODE KERJA
        // ===============================
        $where_instansi_pegawai = "";

        if ($is_admin_instansi && !$is_admin) {
            $where_instansi_pegawai = $wpdb->prepare(
                " AND id_instansi = %d",
                $id_instansi
            );
        }

        $pegawai_instansi = $wpdb->get_results(
            $wpdb->prepare("
                SELECT id_instansi, id_kode_kerja
                FROM absensi_data_pegawai_instansi
                WHERE id_pegawai = %d
                AND tahun = %d
                AND active = 1 
                AND deleted_at IS NULL
                $where_instansi_pegawai
            ", $recVal['id'], $tahun),
            ARRAY_A
        );

        if (!empty($pegawai_instansi)) {

            $list_relasi = array();

            foreach ($pegawai_instansi as $pi) {

                $nama_instansi = $wpdb->get_var(
                    $wpdb->prepare("
                        SELECT nama_instansi
                        FROM absensi_data_instansi
                        WHERE id = %d
                    ", $pi['id_instansi'])
                );

                $nama_kerja = $wpdb->get_var(
                    $wpdb->prepare("
                        SELECT nama_kerja
                        FROM absensi_data_kerja
                        WHERE id = %d
                    ", $pi['id_kode_kerja'])
                );

                if (!empty($nama_instansi) && !empty($nama_kerja)) {
                    $list_relasi[] = $nama_instansi . ' (' . $nama_kerja . ')';
                }
            }

            if (!empty($list_relasi)) {

                $instansi_kode = '<ul style="padding-left:18px;margin:0;">';

                foreach ($list_relasi as $item) {
                    $instansi_kode .= '<li>' . $item . '</li>';
                }

                $instansi_kode .= '</ul>';

                $queryRecords[$recKey]['instansi_kode'] = $instansi_kode;
            }
        }

        // ===============================
        // STATUS BADGE
        // ===============================
        if ($recVal["active"] == 1) {
            $queryRecords[$recKey]["status_badge"] =
                '<span style="background:#28a745;color:#fff;padding:5px 10px;border-radius:4px;">Aktif</span>';
        } else {
            $queryRecords[$recKey]["status_badge"] =
                '<span style="background:#6c757d;color:#fff;padding:5px 10px;border-radius:4px;">Tidak Aktif</span>';
        }

        // ===============================
        // BUTTON
        // ===============================
        $btn  = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#"><i class="dashicons dashicons-edit"></i></a>';
        $btn .= ' <a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#"><i class="dashicons dashicons-trash"></i></a>';

        if ($recVal["active"] == 1) {
            $btn .= ' <a class="btn btn-sm btn-secondary" onclick="toggle_status_pegawai(\''.$recVal["id"].'\',0); return false;" href="#"><i class="dashicons dashicons-hidden"></i></a>';
        } else {
            $btn .= ' <a class="btn btn-sm btn-success" onclick="toggle_status_pegawai(\''.$recVal["id"].'\',1); return false;" href="#"><i class="dashicons dashicons-visibility"></i></a>';
        }

        $queryRecords[$recKey]['aksi'] = $btn;
        }

            die(json_encode([
                "draw"            => intval($params['draw']),
                "recordsTotal"    => $totalRecords,
                "recordsFiltered" => $totalRecords,
                "data"            => $queryRecords
            ]));
    }

    public function hapus_data_pegawai_by_id() {
        global $wpdb;

        $ret = array(
            'status'  => 'success',
            'message' => 'Berhasil hapus data!',
            'data'    => array()
        );

        if (!empty($_POST)) {

            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {

                if (empty($_POST['id'])) {
                    $ret['status']  = 'error';
                    $ret['message'] = 'ID tidak ditemukan!';
                    die(json_encode($ret));
                }

                $id_pegawai = intval($_POST['id']);

                $existing_data = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM absensi_data_pegawai WHERE id = %d",
                        $id_pegawai
                    )
                );

                if (empty($existing_data)) {
                    $ret['status']  = 'error';
                    $ret['message'] = 'Data tidak ditemukan!';
                    die(json_encode($ret));
                }

                // ===============================
                // 1. Soft delete tabel utama
                // ===============================
                $wpdb->update(
                    'absensi_data_pegawai',
                    array(
                        'deleted_at' => current_time('mysql')
                    ),
                    array(
                        'id' => $id_pegawai
                    )
                );

                // ===============================
                // 2. Nonaktifkan semua relasi
                // ===============================
                $wpdb->update(
                    'absensi_data_pegawai_instansi',
                    array(
                        'active' => 0,
                        'update_at' => current_time('mysql')
                    ),
                    array(
                        'id_pegawai' => $id_pegawai
                    )
                );

                $ret['message'] = 'Data pegawai berhasil dinonaktifkan!';
            } else {
                $ret['status']  = 'error';
                $ret['message'] = 'Api key tidak sesuai!';
            }

        } else {
            $ret['status']  = 'error';
            $ret['message'] = 'Format tidak sesuai!';
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
            if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {

                $pegawai = $wpdb->get_row($wpdb->prepare('
                    SELECT *
                    FROM absensi_data_pegawai
                    WHERE id = %d
                ', $_POST['id']), ARRAY_A);

                if (!$pegawai) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Data tidak ditemukan!';
                    die(json_encode($ret));
                }
                
                // Ambil semua instansi dari tabel relasi
                $instansi_rows = $wpdb->get_results($wpdb->prepare('
                    SELECT pi.id_instansi, pi.id_kode_kerja, i.nama_instansi, i.id_user
                    FROM absensi_data_pegawai_instansi pi
                    LEFT JOIN absensi_data_instansi i ON i.id = pi.id_instansi
                    WHERE pi.id_pegawai = %d
                    AND pi.active = 1
                ', $_POST['id']), ARRAY_A);

                $id_instansi = array();
                $id_kode_kerja = array();
                $nama_instansi = array();
                $id_user_instansi = array();

                if (!empty($instansi_rows)) {
                    foreach ($instansi_rows as $row) {
                        $id_instansi[] = $row['id_instansi'];
                        $id_kode_kerja[] = $row['id_kode_kerja'];
                        $nama_instansi[] = $row['nama_instansi']; // tambahkan ini
                        $id_user_instansi[] = $row['id_user'];
                    }
                }

                $pegawai['id_instansi'] = $id_instansi;
                $pegawai['id_kode_kerja'] = $id_kode_kerja;
                $pegawai['nama_instansi'] = $nama_instansi; // return juga nama instansi
                $pegawai['id_user_instansi'] = $id_user_instansi;
                $ret['data'] = $pegawai;

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

        $ret = [
            'status' => 'success',
            'message' => 'Berhasil simpan data!',
            'data' => []
        ];

        if (empty($_POST)) {
            $ret['status'] = 'error';
            $ret['message'] = 'Format Salah!';
            die(json_encode($ret));
        }

        if (empty($_POST['api_key']) || $_POST['api_key'] != get_option(ABSEN_APIKEY)) {
            $ret['status'] = 'error';
            $ret['message'] = 'Api key tidak ditemukan!';
            die(json_encode($ret));
        }

        if (empty($_POST['admin_instansi'])) {
            $ret['status'] = 'error';
            $ret['message'] = 'Admin Instansi (Parent Role) tidak boleh kosong!';
            die(json_encode($ret));
        } elseif (empty($_POST['nik'])) {
            $ret['status'] = 'error';
            $ret['message'] = 'Data NIK tidak boleh kosong!';
            die(json_encode($ret));
        } elseif (empty($_POST['nama'])) {
            $ret['status'] = 'error';
            $ret['message'] = 'Data Nama tidak boleh kosong!';
            die(json_encode($ret));
        } elseif (empty($_POST['email'])) {
            $ret['status'] = 'error';
            $ret['message'] = 'Email tidak boleh kosong!';
            die(json_encode($ret));
        } elseif (empty($_POST['jenis_kelamin']) && !carbon_get_theme_option('crb_hide_jenis_kelamin')) {
            $ret['status'] = 'error';
            $ret['message'] = 'Jenis Kelamin tidak boleh kosong!';
            die(json_encode($ret));
        }

        // Ambil data dari POST
        $nik = $_POST['nik'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $tempat_lahir = $_POST['tempat_lahir'] ?? null;
        $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $agama = $_POST['agama'] ?? null;
        $no_hp = $_POST['no_hp'] ?? null;
        $alamat = $_POST['alamat'] ?? null;
        $pendidikan_terakhir = $_POST['pendidikan_terakhir'] ?? null;
        $pendidikan_sekarang = $_POST['pendidikan_sekarang'] ?? null;
        $nama_sekolah = $_POST['nama_sekolah'] ?? null;
        $lulus = $_POST['lulus'] ?? null;
        $user_role = 'pegawai';
        $tahun = $_POST['tahun'] ?? date('Y');

        $data = [
            'nik' => $nik,
            'nama' => $nama,
            'tempat_lahir' => $tempat_lahir,
            'tanggal_lahir' => $tanggal_lahir,
            'jenis_kelamin' => $jenis_kelamin,
            'agama' => $agama,
            'no_hp' => $no_hp,
            'alamat' => $alamat,
            'jabatan' => $_POST['jabatan'] ?? null,
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
        ];

        $current_user = wp_get_current_user();
        $is_admin_instansi = in_array('admin_instansi', (array)$current_user->roles) && !in_array('administrator', (array)$current_user->roles);

        if (!empty($_POST['id_data'])) {
            // --- EDIT DATA ---
            $id_pegawai = intval($_POST['id_data']);
            $existing_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_data_pegawai WHERE id = %d", $id_pegawai));

            // Check admin_instansi ownership
            if ($is_admin_instansi) {
                $admin_id = get_current_user_id();
                $pegawai_instansi = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM absensi_data_pegawai_instansi
                    WHERE id_pegawai = %d
                    AND id_instansi IN (
                        SELECT id_instansi FROM absensi_data_instansi WHERE id_user = %d
                    )
                    AND active = 1
                ", $id_pegawai, $admin_id));

                if ($pegawai_instansi == 0) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Anda tidak memiliki hak akses untuk mengedit data ini!';
                    die(json_encode($ret));
                }
            }

            // Sync WP user
            if (!empty($existing_data->id_user)) {
                wp_update_user([
                    'ID' => $existing_data->id_user,
                    'user_email' => $email,
                    'first_name' => $nama
                ]);
                if ($existing_data->nik !== $nik) {
                    $wpdb->update($wpdb->users, ['user_login' => $nik], ['ID' => $existing_data->id_user]);
                }
            }

            $wpdb->update('absensi_data_pegawai', $data, ['id' => $id_pegawai]);

        } else {
            // --- INSERT DATA BARU ---
            $cek_nik = $wpdb->get_row($wpdb->prepare("SELECT id, active FROM absensi_data_pegawai WHERE nik=%s", $nik), ARRAY_A);
            $prefix = carbon_get_theme_option('crb_default_password_prefix');
            $password_with_prefix = $prefix . $nik;

            if (empty($cek_nik)) {
                if (username_exists($nik)) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Username (NIK) sudah terdaftar sebagai User WordPress! Harap hubungi admin';
                    die(json_encode($ret));
                } elseif (email_exists($email)) {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Email sudah terdaftar sebagai User WordPress!';
                    die(json_encode($ret));
                } else {
                    $user_id = wp_insert_user([
                        'user_login' => $nik,
                        'user_pass' => $password_with_prefix,
                        'user_email' => $email,
                        'first_name' => $nama,
                        'role' => 'pegawai'
                    ]);
                    if (is_wp_error($user_id)) {
                        $ret['status'] = 'error';
                        $ret['message'] = 'Gagal membuat User WordPress: ' . $user_id->get_error_message();
                        die(json_encode($ret));
                    }
                    update_user_meta($user_id, 'id_admin_instansi', $_POST['admin_instansi']);
                    $data['id_user'] = $user_id;
                    $wpdb->insert('absensi_data_pegawai', $data);
                    $id_pegawai = $wpdb->insert_id;
                }
            } else {
                if ($cek_nik['active'] == 0) {
                    $wpdb->update('absensi_data_pegawai', $data, ['id' => $cek_nik['id']]);
                    $id_pegawai = $cek_nik['id'];
                } else {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Gagal disimpan. Data pegawai dengan NIK="'.$nik.'" sudah ada! Harap hubungi Admin!';
                    die(json_encode($ret));
                }
            }
        }

        // --- HANDLE INSTANSI & KODE KERJA ---
        if (!empty($_POST['instansi'])) {
            foreach ($_POST['instansi'] as $row) {
                $id_instansi = intval($row['id_instansi']);
                $id_kode_kerja = !empty($row['id_kode_kerja']) ? intval($row['id_kode_kerja']) : 0;

                // Nonaktifkan semua kode kerja lama pegawai untuk instansi ini
                $wpdb->update(
                    'absensi_data_pegawai_instansi',
                    ['active' => 0, 'update_at' => current_time('mysql')],
                    ['id_pegawai' => $id_pegawai, 'id_instansi' => $id_instansi],
                    ['%d','%s'],
                    ['%d','%d']
                );

                // Cek apakah relasi sudah ada
                $existing_relasi = $wpdb->get_row($wpdb->prepare("
                    SELECT id FROM absensi_data_pegawai_instansi
                    WHERE id_pegawai = %d
                    AND id_instansi = %d
                    AND id_kode_kerja = %d
                ", $id_pegawai, $id_instansi, $id_kode_kerja));

                if ($existing_relasi) {
                    // Re-activate
                    $wpdb->update(
                        'absensi_data_pegawai_instansi',
                        ['active' => 1, 'update_at' => current_time('mysql')],
                        ['id' => $existing_relasi->id],
                        ['%d','%s'],
                        ['%d']
                    );
                } else {
                    // Insert baru
                    $wpdb->insert(
                        'absensi_data_pegawai_instansi',
                        [
                            'id_pegawai' => $id_pegawai,
                            'id_instansi' => $id_instansi,
                            'id_kode_kerja' => $id_kode_kerja,
                            'tahun' => intval($tahun),
                            'active' => 1,
                            'created_at' => current_time('mysql'),
                            'update_at' => current_time('mysql')
                        ],
                        ['%d','%d','%d','%d','%d','%s','%s']
                    );
                }
            }
        }

        die(json_encode($ret));
    }

    function get_kode_kerja_by_instansi() {
        global $wpdb;

        $ret = array(
            'status' => false,
            'data'   => array()
        );

        if (!isset($_POST['id_instansi'])) {
            wp_send_json($ret);
        }

        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;

        $is_admin_instansi = in_array('admin_instansi', (array) $current_user->roles)
            && !in_array('administrator', (array) $current_user->roles);

        $id_instansi_list = array_map('intval', $_POST['id_instansi']);

        foreach ($id_instansi_list as $id_instansi) {

            // ambil instansi
            $instansi = $wpdb->get_row(
                $wpdb->prepare("
                    SELECT id, nama_instansi, id_user
                    FROM absensi_data_instansi
                    WHERE id = %d
                    AND active = 1
                    AND deleted_at IS NULL
                ", $id_instansi),
                ARRAY_A
            );

            if (empty($instansi)) {
                continue;
            }

            // jika admin instansi hanya boleh instansi miliknya
            if ($is_admin_instansi && $instansi['id_user'] != $current_user_id) {
                continue;
            }

            // ambil kode kerja berdasarkan id_user instansi
            $secondary = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT id, nama_kerja
                    FROM absensi_data_kerja
                    WHERE id_instansi = %d
                    AND active = 1
                    AND deleted_at IS NULL
                    ORDER BY nama_kerja ASC
                ", $instansi['id_user']),
                ARRAY_A
            );

            $ret['data'][] = array(
                'id_instansi'   => $instansi['id'],
                'nama_instansi' => $instansi['nama_instansi'],
                'secondary'     => $secondary
            );
        }

        if (!empty($ret['data'])) {
            $ret['status'] = true;
        }

        wp_send_json($ret);
    }

    public function toggle_status_pegawai() {
        global $wpdb;

        $ret = [
            "status"  => "success",
            "message" => "Status berhasil diubah!",
        ];

        if (!empty($_POST)) {

            if (!empty($_POST["api_key"]) && $_POST["api_key"] == get_option(ABSEN_APIKEY)) {

                $current_user = wp_get_current_user();
                $is_admin = in_array('administrator', (array) $current_user->roles);
                $is_admin_instansi = in_array('admin_instansi', (array) $current_user->roles);

                if (!$is_admin && !$is_admin_instansi) {
                    $ret['status']  = 'error';
                    $ret['message'] = 'Akses Ditolak!';
                    die(json_encode($ret));
                }

                if (empty($_POST['id']) || !isset($_POST['status'])) {
                    $ret['status']  = 'error';
                    $ret['message'] = 'Parameter tidak lengkap!';
                    die(json_encode($ret));
                }

                $id         = intval($_POST['id']);
                $new_status = intval($_POST['status']);

                // ===============================
                // CEK DATA PEGAWAI ADA / TIDAK
                // ===============================
                $existing_data = $wpdb->get_row(
                    $wpdb->prepare("
                        SELECT id 
                        FROM absensi_data_pegawai 
                        WHERE id = %d
                    ", $id)
                );

                if (!$existing_data) {
                    $ret['status']  = 'error';
                    $ret['message'] = 'Data tidak ditemukan!';
                    die(json_encode($ret));
                }

                // ===============================
                // VALIDASI ADMIN INSTANSI
                // ===============================
                if (!$is_admin && $is_admin_instansi) {

                    $cek_relasi = $wpdb->get_var(
                        $wpdb->prepare("
                            SELECT COUNT(id)
                            FROM absensi_data_pegawai_instansi
                            WHERE id_pegawai = %d
                            AND id_instansi = %d
                            AND deleted_at IS NULL
                        ", $id, $current_user->ID)
                    );

                    if ($cek_relasi == 0) {
                        $ret['status']  = 'error';
                        $ret['message'] = 'Akses Ditolak! Data ini bukan milik anda.';
                        die(json_encode($ret));
                    }
                }

                // ===============================
                // UPDATE PEGAWAI
                // ===============================
                $wpdb->update(
                    'absensi_data_pegawai',
                    array('active' => $new_status),
                    array('id' => $id)
                );

                // ===============================
                // SYNC KE RELASI INSTANSI
                // ===============================
                $wpdb->update(
                    'absensi_data_pegawai_instansi',
                    array('active' => $new_status),
                    array('id_pegawai' => $id)
                );

                $ret['message'] = ($new_status == 1)
                    ? 'Pegawai berhasil Diaktifkan!'
                    : 'Pegawai berhasil Dinonaktifkan!';

            } else {
                $ret["status"]  = "error";
                $ret["message"] = "Api Key tidak sesuai!";
            }

        } else {
            $ret["status"]  = "error";
            $ret["message"] = "Format tidak sesuai!";
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
            $query = "SELECT id, nama, nik, id_instansi FROM absensi_data_pegawai WHERE active = 1 AND deleted_at IS NULL";

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
