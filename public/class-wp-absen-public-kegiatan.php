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

class Wp_Absen_Public_Kegiatan {

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

	public function management_data_kegiatan($atts) {
		if ( ! is_user_logged_in() ) {
			return $this->functions->redirect_login();
		}

		$current_user = wp_get_current_user();
		// Allow Admin Instansi, Administrator, and Pegawai
		$allowed_roles = array('admin_instansi', 'administrator', 'pegawai');
		$user_roles = (array) $current_user->roles;
		
		if (!array_intersect($allowed_roles, $user_roles)) {
			return 'Akses Ditolak';
		}

		$input = shortcode_atts(array(
			'tahun_anggaran' => date('Y'),
		), $atts);

		global $wpdb;

		// Get Pegawai Info for Frontend
		$is_pegawai = in_array('pegawai', $user_roles) && !in_array('administrator', $user_roles) && !in_array('admin_instansi', $user_roles);
		$is_admin = in_array('administrator', $user_roles) || in_array('admin_instansi', $user_roles);
		
		$pegawai_info = array('id' => 0, 'nama' => $current_user->display_name); // Default values
		$list_pegawai = array();

		if ($is_pegawai) {
			$pegawai_data = $wpdb->get_row($wpdb->prepare("SELECT id, nama FROM absensi_data_pegawai WHERE id_user = %d", $current_user->ID));
			if ($pegawai_data) {
				$pegawai_info['id'] = $pegawai_data->id;
				$pegawai_info['nama'] = $pegawai_data->nama;
			}
		} elseif ($is_admin) {
			// Fetch List Pegawai
			$sql_pegawai = "SELECT id, nama, nik FROM absensi_data_pegawai WHERE active = 1 AND deleted_at IS NULL";
			if (in_array('admin_instansi', $user_roles) && !in_array('administrator', $user_roles)) {
				$sql_pegawai .= $wpdb->prepare(" AND id_instansi = %d", $current_user->ID);
			}
			$sql_pegawai .= " ORDER BY nama ASC";
			$list_pegawai = $wpdb->get_results($sql_pegawai, ARRAY_A);
		}

		ob_start();
		require 'partials/wp-absen-management-data-kegiatan.php';
		
		return ob_get_clean();
	}

	public function get_datatable_kegiatan() {
		global $wpdb;
		
		// Start Output Buffering to capture any stray errors/notices
		ob_start();

		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data'  => array()
		);

		if (!empty($_POST) && !empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
			$params = $_REQUEST;
			$tahun = isset($_POST['tahun']) ? sanitize_text_field($_POST['tahun']) : date('Y');
			$bulan = isset($_POST['bulan']) ? sanitize_text_field($_POST['bulan']) : '';

			
			// Default params if missing
			$start = isset($params['start']) ? intval($params['start']) : 0;
			$length = isset($params['length']) ? intval($params['length']) : 10;
			$search_value = isset($params['search']['value']) ? $params['search']['value'] : '';
			$draw = isset($params['draw']) ? intval($params['draw']) : 0;
			
			// Columns mapping: 0=no, 1=nama_pegawai, 2=nama_kegiatan...
			// Update Frontend Columns to match

			$sql_base = "SELECT k.*, p.nama as nama_pegawai
						 FROM absensi_kegiatan k
						 LEFT JOIN absensi_data_pegawai p ON k.id_pegawai = p.id
						 WHERE k.active = 1 AND k.deleted_at IS NULL";
			
			// Filter Tahun
			$sql_base .= $wpdb->prepare(" AND k.tahun = %s", $tahun);
			if (!empty($bulan)) {
				$sql_base .= $wpdb->prepare(" AND MONTH(k.tanggal) = %d", intval($bulan));
			}

			$current_user = wp_get_current_user();
			$user_roles = (array) $current_user->roles;

			if (in_array('admin_instansi', $user_roles) && !in_array('administrator', $user_roles)) {
				$sql_base .= $wpdb->prepare(" AND k.id_instansi = %d", $current_user->ID);
			} elseif (in_array('pegawai', $user_roles) && !in_array('administrator', $user_roles) && !in_array('admin_instansi', $user_roles)) {
				// Filter by Pegawai ID
				$pegawai_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM absensi_data_pegawai WHERE id_user = %d", $current_user->ID));
				if ($pegawai_id) {
					$sql_base .= $wpdb->prepare(" AND k.id_pegawai = %d", $pegawai_id);
				} else {
					$sql_base .= " AND 1=0"; // Hide if not linked
				}
			}

			if( !empty($search_value) ) {
				$sql_base .= " AND (k.nama_kegiatan LIKE '%$search_value%' OR k.tempat LIKE '%$search_value%' OR p.nama LIKE '%$search_value%')";
			}

			// Total matching records
			$queryTot = $wpdb->get_var("SELECT COUNT(*) FROM ($sql_base) as total_table");
			$totalRecords = $queryTot ? intval($queryTot) : 0;

			$sql_base .=  " ORDER BY k.tanggal DESC LIMIT $start, $length";
			$queryRecords = $wpdb->get_results($sql_base, ARRAY_A);

			$data = array();
			$no = $start + 1;
			
			if ($queryRecords) {
				foreach ($queryRecords as $row) {
					$waktu = date('H:i', strtotime($row['jam_mulai'])) . ' - ' . date('H:i', strtotime($row['jam_selesai']));
					$tanggal = date_i18n( 'd F Y', strtotime( $row['tanggal'] ) );
					$nama_pegawai = !empty($row['nama_pegawai']) ? $row['nama_pegawai'] : '-';

					$files = '-';
					if (!empty($row['file_lampiran'])) {
						// Path: public/img/kegiatan/
						$file_url = ABSEN_PLUGIN_URL . 'public/img/kegiatan/' . $row['file_lampiran'];

						$files = '
							<a href="'.$file_url.'" target="_blank">
								<img src="'.$file_url.'" style="max-width:80px; display:block; margin:0 auto 5px;">
							</a>
						';
					}


					$nestedData = array(); 
					$nestedData['no'] = $no++;
					$nestedData['nama_pegawai'] = $nama_pegawai;
					$nestedData['nama_kegiatan'] = $row['nama_kegiatan'];
					$nestedData['tanggal'] = $tanggal;
					$nestedData['waktu'] = $waktu;
					$nestedData['tempat'] = $row['tempat'];
					$nestedData['uraian'] = $row['uraian'];
                    $nestedData['lampiran'] = $files;
					$nestedData['status'] = 'Aktif'; // Placeholder
					$nestedData['aksi'] = '
						<a href="javascript:void(0)" class="btn btn-sm btn-warning" onclick="edit_data_kegiatan('.$row['id'].')"><i class="dashicons dashicons-edit"></i></a>
						<a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="hapus_data_kegiatan('.$row['id'].')"><i class="dashicons dashicons-trash"></i></a>
					';
					
					$data[] = $nestedData;
				}
			}

			$json_data = array(
				"draw"            => $draw,
				"recordsTotal"    => $totalRecords,  
				"recordsFiltered" => $totalRecords,
				"data"            => $data
			);
			
			// Clean buffer before output
			ob_end_clean();
			wp_send_json($json_data);
		} else {
			ob_end_clean();
			wp_send_json($ret);
		}
	}
	public function print_laporan_kegiatan() {
    global $wpdb;

    if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {

        $tahun = sanitize_text_field($_POST['tahun']);
        $bulan = sanitize_text_field($_POST['bulan']);

        $sql = "SELECT k.*, p.nama as nama_pegawai
                FROM absensi_kegiatan k
                LEFT JOIN absensi_data_pegawai p ON k.id_pegawai = p.id
                WHERE k.active = 1 
                AND k.deleted_at IS NULL";

        $sql .= $wpdb->prepare(" AND k.tahun = %s", $tahun);

        if (!empty($bulan)) {
            $sql .= $wpdb->prepare(" AND MONTH(k.tanggal) = %d", intval($bulan));
        }

        $current_user = wp_get_current_user();
        $user_roles = (array) $current_user->roles;

        if (in_array('admin_instansi', $user_roles) && !in_array('administrator', $user_roles)) {

            $sql .= $wpdb->prepare(" AND k.id_instansi = %d", $current_user->ID);

        } elseif (in_array('pegawai', $user_roles) 
            && !in_array('administrator', $user_roles) 
            && !in_array('admin_instansi', $user_roles)) {

            // Ambil ID Pegawai milik user login
            $pegawai_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM absensi_data_pegawai WHERE id_user = %d",
                    $current_user->ID
                )
            );

            if ($pegawai_id) {
                $sql .= $wpdb->prepare(" AND k.id_pegawai = %d", $pegawai_id);
            } else {
                $sql .= " AND 1=0"; 
            }
        }

        $sql .= " ORDER BY k.tanggal DESC";

        $data = $wpdb->get_results($sql);

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Data Kegiatan <?php echo esc_html($tahun); ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                }
                h2 {
                    text-align: center;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid #000;
                }
                th, td {
                    padding: 5px;
                    text-align: left;
                    vertical-align: middle;
                }
                th {
                    background: #eee;
                }
                img {
                    max-width: 100px;
                    height: auto;
                }
				
            </style>
        </head>
        <body onload="window.print()">

        <h2>
            LAPORAN DATA KEGIATAN<br>
            Tahun <?php echo esc_html($tahun); ?>
        </h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pegawai</th>
                    <th>Nama Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Tempat</th>
                    <th>Uraian</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            foreach ($data as $row) {

                echo "<tr>";
                echo "<td>".$no++."</td>";
                echo "<td>".esc_html($row->nama_pegawai)."</td>";
                echo "<td>".esc_html($row->nama_kegiatan)."</td>";
                echo "<td>".date_i18n('d F Y', strtotime($row->tanggal))."</td>";
                echo "<td>".esc_html($row->tempat)."</td>";
                echo "<td>".esc_html($row->uraian)."</td>";

                echo "<td>";

                if (!empty($row->file_lampiran)) {

                    $file_url = plugins_url(
                        'public/img/kegiatan/' . $row->file_lampiran,
                        dirname(__FILE__)
                    );

                    echo '<img src="'.esc_url($file_url).'">';
                } else {
                    echo "-";
                }

                echo "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>

        </body>
        </html>
        <?php
        exit;
    }

    wp_die();
}


	public function tambah_data_kegiatan() {
		global $wpdb;
		
		ob_start();
		$ret = array('status' => 'error', 'message' => 'Gagal simpan data!');

		if (!empty($_POST) && !empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
			
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$nama_kegiatan = isset($_POST['nama_kegiatan']) ? sanitize_text_field($_POST['nama_kegiatan']) : '';
			$tanggal = isset($_POST['tanggal']) ? sanitize_text_field($_POST['tanggal']) : '';
			$jam_mulai = isset($_POST['jam_mulai']) ? sanitize_text_field($_POST['jam_mulai']) : '';
			$jam_selesai = isset($_POST['jam_selesai']) ? sanitize_text_field($_POST['jam_selesai']) : '';
			$tempat = isset($_POST['tempat']) ? sanitize_textarea_field($_POST['tempat']) : '';
			$uraian = isset($_POST['uraian']) ? sanitize_textarea_field($_POST['uraian']) : '';
			
			// Extract Year from Tanggal
			$tahun = '';
			if (!empty($tanggal)) {
				$tahun = date('Y', strtotime($tanggal));
			}

			if(empty($nama_kegiatan) || empty($tanggal)) {
				$ret['message'] = 'Nama Kegiatan dan Tanggal wajib diisi!';
				ob_end_clean(); wp_send_json($ret);
			}

			$current_user = wp_get_current_user();
			$user_roles = (array) $current_user->roles;
			
			$id_instansi = 0;
			$id_pegawai = 0;

			if (in_array('administrator', $user_roles)) {
				// Admin logic: Can set id_pegawai if provided
				if (isset($_POST['id_pegawai']) && intval($_POST['id_pegawai']) > 0) {
					$id_pegawai = intval($_POST['id_pegawai']);
					// Fetch instansi for this pegawai to keep data consistent
					$p_instansi = $wpdb->get_var($wpdb->prepare("SELECT id_instansi FROM absensi_data_pegawai WHERE id = %d", $id_pegawai));
					if ($p_instansi) {
						$id_instansi = $p_instansi;
					} else {
						// Fallback or error? defaulting to current admin id if not found (unlikely)
						$id_instansi = $current_user->ID; 
					}
				} else {
					$id_instansi = $current_user->ID; // Default if no pegawai selected
				}

			} elseif (in_array('admin_instansi', $user_roles)) {
				$id_instansi = $current_user->ID;
				if (isset($_POST['id_pegawai']) && intval($_POST['id_pegawai']) > 0) {
					$requested_id_pegawai = intval($_POST['id_pegawai']);
					// Verify ownership
					$verify_pegawai = $wpdb->get_var($wpdb->prepare("SELECT id FROM absensi_data_pegawai WHERE id = %d AND id_instansi = %d", $requested_id_pegawai, $current_user->ID));
					if ($verify_pegawai) {
						$id_pegawai = $requested_id_pegawai;
					} else {
						$ret['message'] = 'Pegawai tidak ditemukan atau bukan anggota instansi anda!';
						ob_end_clean(); wp_send_json($ret);
					}
				}
			} elseif (in_array('pegawai', $user_roles)) {
				// Fetch Pegawai Data
				$pegawai_data = $wpdb->get_row($wpdb->prepare("SELECT id, id_instansi FROM absensi_data_pegawai WHERE id_user = %d", $current_user->ID));
				if ($pegawai_data) {
					$id_pegawai = $pegawai_data->id;
					$id_instansi = $pegawai_data->id_instansi;
				} else {
					$ret['message'] = 'Data Pegawai tidak ditemukan!';
					ob_end_clean(); wp_send_json($ret);
				}
			}

            // Handle File Upload
            $file_lampiran = '';
            if (!empty($_FILES['file_lampiran']['name'])) {
                $target_path = ABSEN_PLUGIN_PATH . 'public/img/kegiatan/';
                $ext = ['jpg', 'jpeg', 'png', 'pdf'];

                // Ensure directory exists
                if (!file_exists($target_path)) {
                    mkdir($target_path, 0755, true);
                }

                $upload = self::uploadFileAbsen(
                    get_option(ABSEN_APIKEY),
                    $target_path,
                    $_FILES['file_lampiran'],
                    $ext,
                    5000000, // 5MB max
                    'kegiatan_' . time() // Prefix untuk nama file
                );

                if ($upload['status']) {
                    $file_lampiran = $upload['filename'];
                } else {
                    $ret['message'] = 'Gagal upload: ' . $upload['message'];
                    ob_end_clean(); wp_send_json($ret);
                }
            }

			$data = array(
				'id_instansi' => $id_instansi,
				'id_pegawai' => $id_pegawai,
				'nama_kegiatan' => $nama_kegiatan,
				'tanggal' => $tanggal,
				'tahun' => $tahun,
				'jam_mulai' => $jam_mulai,
				'jam_selesai' => $jam_selesai,
				'tempat' => $tempat,
				'uraian' => $uraian,
			);

            if (!empty($file_lampiran)) {
                $data['file_lampiran'] = $file_lampiran;
            }

			if ($id > 0) {
				// Update
				// Check ownership: Admin/Instansi can edit theirs, Pegawai can edit theirs
				$existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_kegiatan WHERE id = %d", $id));
				$allow = false;
				
				if ($existing) {
					if (in_array('administrator', $user_roles)) {
						$allow = true;
					} elseif (in_array('admin_instansi', $user_roles) && $existing->id_instansi == $id_instansi) {
						$allow = true;
					} elseif ($existing->id_pegawai == $id_pegawai && $id_pegawai > 0) {
						$allow = true;
					}
				}

				if ($allow) {
					$wpdb->update('absensi_kegiatan', $data, array('id' => $id));
					$ret['status'] = 'success';
					$ret['message'] = 'Data Kegiatan Berhasil Diupdate!';
				} else {
					$ret['message'] = 'Akses Ditolak/Data Tidak Ditemukan!';
				}
			} else {
				// Insert
				$wpdb->insert('absensi_kegiatan', $data);
				$ret['status'] = 'success';
				$ret['message'] = 'Data Kegiatan Berhasil Ditambahkan!';
			}
		}
		
		ob_end_clean();
		wp_send_json($ret);
	}

	public function get_data_kegiatan_by_id() {
		global $wpdb;
		$ret = array('status' => 'error', 'message' => 'Data tidak ditemukan!');

		if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
			$id = intval($_POST['id']);
			$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_kegiatan WHERE id = %d", $id), ARRAY_A);
			
			if ($data) {
				$current_user = wp_get_current_user();
				// Check Access
				if (in_array('admin_instansi', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles)) {
					if ($data['id_instansi'] != $current_user->ID) {
						echo json_encode($ret); wp_die();
					}
				}

				$ret['status'] = 'success';
				$ret['data'] = $data;
			}
		}
		echo json_encode($ret);
		wp_die();
	}

	public function hapus_data_kegiatan_by_id() {
		global $wpdb;
		$ret = array('status' => 'error', 'message' => 'Gagal hapus data!');
		
		if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
			$id = intval($_POST['id']);
			
			// Permission Check
			$current_user = wp_get_current_user();
			$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_kegiatan WHERE id = %d", $id));
			
			if ($data) {
				$allow = false;
				if (in_array('administrator', (array) $current_user->roles)) $allow = true;
				if (in_array('admin_instansi', (array) $current_user->roles) && $data->id_instansi == $current_user->ID) $allow = true;

				if ($allow) {
					// Soft Delete: Set deleted_at timestamp
					$wpdb->update(
						'absensi_kegiatan',
						array('deleted_at' => current_time('mysql')),
						array('id' => $id)
					);
					$ret['status'] = 'success';
					$ret['message'] = 'Data berhasil dihapus';
				} else {
					$ret['message'] = 'Akses Ditolak!';
				}
			}
		}
		echo json_encode($ret);
		wp_die();
	}

}
