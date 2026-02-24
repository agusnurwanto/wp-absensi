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

class Wp_Absen_Public_Ijin {

	use CustomTraitAbsen;

	private $plugin_name;
	private $version;
	private $functions;

	public function __construct( $plugin_name, $version, $functions ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->functions = $functions;
	}

	public function management_data_ijin($atts) {
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
		require 'partials/wp-absen-management-data-ijin.php';
		return ob_get_clean();
	}

	public function get_datatable_ijin() {
		global $wpdb;
		
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
			
			$start = isset($params['start']) ? intval($params['start']) : 0;
			$length = isset($params['length']) ? intval($params['length']) : 10;
			$search_value = isset($params['search']['value']) ? $params['search']['value'] : '';
			$draw = isset($params['draw']) ? intval($params['draw']) : 0;

			$sql_base = "SELECT i.*, p.nama as nama_pegawai
						 FROM absensi_ijin i
						 LEFT JOIN absensi_data_pegawai p ON i.id_pegawai = p.id
						 WHERE i.deleted_at IS NULL";

			// Filter Tahun
			$sql_base .= $wpdb->prepare(" AND i.tahun = %s", $tahun);
			if (!empty($bulan)) {
				$sql_base .= $wpdb->prepare(" AND MONTH(i.tanggal_mulai) = %d", intval($bulan));
			}


			$current_user = wp_get_current_user();
			$user_roles = (array) $current_user->roles;
			$is_admin = in_array('administrator', $user_roles) || in_array('admin_instansi', $user_roles);

			if (in_array('admin_instansi', $user_roles) && !in_array('administrator', $user_roles)) {
				$sql_base .= $wpdb->prepare(" AND i.id_instansi = %d", $current_user->ID);
			} elseif (in_array('pegawai', $user_roles) && !in_array('administrator', $user_roles) && !in_array('admin_instansi', $user_roles)) {
				$pegawai_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM absensi_data_pegawai WHERE id_user = %d", $current_user->ID));
				if ($pegawai_id) {
					$sql_base .= $wpdb->prepare(" AND i.id_pegawai = %d", $pegawai_id);
				} else {
					$sql_base .= " AND 1=0"; 
				}
			}

			if( !empty($search_value) ) {
				$sql_base .= " AND (i.tipe_ijin LIKE '%$search_value%' OR i.alasan LIKE '%$search_value%' OR p.nama LIKE '%$search_value%')";
			}

			$queryTot = $wpdb->get_var("SELECT COUNT(*) FROM ($sql_base) as total_table");
			$totalRecords = $queryTot ? intval($queryTot) : 0;

			$sql_base .=  " ORDER BY i.created_at DESC LIMIT $start, $length";
			$queryRecords = $wpdb->get_results($sql_base, ARRAY_A);

			$data = array();
			$no = $start + 1;
			
			if ($queryRecords) {
				foreach ($queryRecords as $row) {
					$tanggal = date_i18n( 'd M Y', strtotime( $row['tanggal_mulai'] ) );
					if ($row['tanggal_mulai'] != $row['tanggal_selesai']) {
						$tanggal .= ' - ' . date_i18n( 'd M Y', strtotime( $row['tanggal_selesai'] ) );
					}

					$files = '-';
					if (!empty($row['file_lampiran'])) {
                        // Path: public/img/ijin/
						$file_url = ABSEN_PLUGIN_URL . 'public/img/ijin/' . $row['file_lampiran'];
						$files = '-';
					if (!empty($row['file_lampiran'])) {
                        // Path: public/img/ijin/
						$file_url = ABSEN_PLUGIN_URL . 'public/img/ijin/' . $row['file_lampiran'];
						$files = '
						<a href="'.$file_url.'" target="_blank">
							<img src="'.$file_url.'" style="max-width:80px; display:block; margin:0 auto 5px;">
						</a>
						';
					}
					}

                    // Status Badge
                    $status_badge = '<span class="badge badge-secondary">Pending</span>';
                    if ($row['status'] == 'Approved') $status_badge = '<span class="badge badge-success">Disetujui</span>';
                    if ($row['status'] == 'Rejected') $status_badge = '<span class="badge badge-danger">Ditolak</span>';

					$aksi = '';
					
					// Edit/Delete for Creator or Admin
                    // Approve/Reject for Admin
                    if ($is_admin) {
                        $aksi .= '<a href="javascript:void(0)" class="btn btn-sm btn-warning" onclick="edit_data_ijin('.$row['id'].')" title="Edit"><i class="dashicons dashicons-edit"></i></a> ';
                        $aksi .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="hapus_data_ijin('.$row['id'].')" title="Hapus"><i class="dashicons dashicons-trash"></i></a> ';
                        
                        if ($row['status'] == 'Pending') {
                            $aksi .= '<br><a href="javascript:void(0)" class="btn btn-sm btn-success mt-1" onclick="update_status_ijin('.$row['id'].', \'Approved\')" title="Setujui"><i class="dashicons dashicons-yes"></i></a> ';
                            $aksi .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger mt-1" onclick="update_status_ijin('.$row['id'].', \'Rejected\')" title="Tolak"><i class="dashicons dashicons-no"></i></a>';
                        }
                    } else {
                        // Pegawai can edit/delete only if Pending (usually)
                        if ($row['status'] == 'Pending') {
                            $aksi .= '<a href="javascript:void(0)" class="btn btn-sm btn-warning" onclick="edit_data_ijin('.$row['id'].')"><i class="dashicons dashicons-edit"></i></a> ';
                            $aksi .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="hapus_data_ijin('.$row['id'].')"><i class="dashicons dashicons-trash"></i></a> ';
                        } else {
                            $aksi .= '-';
                        }
                    }

					$nestedData = array(); 
					$nestedData['no'] = $no++;
					$nestedData['nama_pegawai'] = $row['nama_pegawai'];
					$nestedData['tipe_ijin'] = $row['tipe_ijin'];
					$nestedData['tanggal'] = $tanggal;
					$nestedData['alasan'] = $row['alasan'];
					$nestedData['lampiran'] = $files;
					$nestedData['status'] = $status_badge;
					$nestedData['aksi'] = $aksi;
					
					$data[] = $nestedData;
				}
			}

			$json_data = array(
				"draw"            => $draw,
				"recordsTotal"    => $totalRecords,  
				"recordsFiltered" => $totalRecords,
				"data"            => $data
			);
			
			ob_end_clean();
			wp_send_json($json_data);
		} else {
			ob_end_clean();
			wp_send_json($ret);
		}
	}
	public function print_laporan_perijinan() {
		global $wpdb;

		if (!empty($_GET['api_key']) && $_GET['api_key'] == get_option(ABSEN_APIKEY)) {

			$tahun = sanitize_text_field($_GET['tahun']);
			$bulan = sanitize_text_field($_GET['bulan']);

			$sql = "SELECT i.*, p.nama as nama_pegawai
					FROM absensi_ijin i
					LEFT JOIN absensi_data_pegawai p ON i.id_pegawai = p.id
					WHERE i.deleted_at IS NULL";

			$sql .= $wpdb->prepare(" AND i.tahun = %s", $tahun);

			if (!empty($bulan)) {
				$sql .= $wpdb->prepare(" AND MONTH(i.tanggal_mulai) = %d", intval($bulan));
			}

			$current_user = wp_get_current_user();
			$user_roles = (array) $current_user->roles;

			if (in_array('admin_instansi', $user_roles) && !in_array('administrator', $user_roles)) {

				$sql .= $wpdb->prepare(" AND i.id_instansi = %d", $current_user->ID);

			} elseif (in_array('pegawai', $user_roles)
				&& !in_array('administrator', $user_roles)
				&& !in_array('admin_instansi', $user_roles)) {

				$pegawai_id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM absensi_data_pegawai WHERE id_user = %d",
						$current_user->ID
					)
				);

				if ($pegawai_id) {
					$sql .= $wpdb->prepare(" AND i.id_pegawai = %d", $pegawai_id);
				} else {
					$sql .= " AND 1=0";
				}
			}

			$sql .= " ORDER BY i.tanggal_mulai DESC";

			$data = $wpdb->get_results($sql);

			?>
			<!DOCTYPE html>
			<html lang="id">
			<head>
				<meta charset="UTF-8">
				<title>Laporan Data Perijinan <?php echo esc_html($tahun); ?></title>
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
						vertical-align: middle;
					}
					th {
						background: #eee;
					}
				</style>
			</head>
			<body onload="window.print()">

			<h2>
				LAPORAN DATA IJIN / CUTI / SAKIT<br>
				TAHUN <?php echo esc_html($tahun); ?>
			</h2>

			<table>
				<thead>
					<tr>
						<th>No</th>
						<th>Nama Pegawai</th>
						<th>Tipe</th>
						<th>Tanggal</th>
						<th>Alasan</th>
						<th>Lampiran</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$no = 1;
				foreach ($data as $row) {

					$tanggal = date_i18n('d F Y', strtotime($row->tanggal_mulai));

					if ($row->tanggal_mulai != $row->tanggal_selesai) {
						$tanggal .= ' - ' . date_i18n('d F Y', strtotime($row->tanggal_selesai));
					}

					echo "<tr>";
					echo "<td>".$no++."</td>";
					echo "<td>".esc_html($row->nama_pegawai)."</td>";
					echo "<td>".esc_html($row->tipe_ijin)."</td>";
					echo "<td>".$tanggal."</td>";
					echo "<td>".esc_html($row->alasan)."</td>";

					echo "<td>";

					if (!empty($row->file_lampiran)) {

						$file_url = plugins_url(
							'public/img/ijin/' . $row->file_lampiran,
							dirname(__FILE__)
						);

						echo '<img src="'.esc_url($file_url).'" style="max-width:80px;">';

					} else {
						echo "-";
					}

					echo "</td>";
					echo "<td>".esc_html($row->status)."</td>";
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

	public function tambah_data_ijin() {
		global $wpdb;
		ob_start();
		$ret = array('status' => 'error', 'message' => 'Gagal simpan data!');

		if (!empty($_POST) && !empty($_POST['api_key']) && $_POST['api_key'] == get_option( ABSEN_APIKEY )) {
			
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$tipe_ijin = isset($_POST['tipe_ijin']) ? sanitize_text_field($_POST['tipe_ijin']) : '';
			$alasan = isset($_POST['alasan']) ? sanitize_textarea_field($_POST['alasan']) : '';
			$tanggal_mulai = isset($_POST['tanggal_mulai']) ? sanitize_text_field($_POST['tanggal_mulai']) : '';
			$tanggal_selesai = isset($_POST['tanggal_selesai']) ? sanitize_text_field($_POST['tanggal_selesai']) : '';
            
            $tahun = '';
			if (!empty($tanggal_mulai)) {
				$tahun = date('Y', strtotime($tanggal_mulai));
			}

			if(empty($tipe_ijin) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
				$ret['message'] = 'Tipe Ijin dan Tanggal wajib diisi!';
				ob_end_clean(); wp_send_json($ret);
			}

			$current_user = wp_get_current_user();
			$user_roles = (array) $current_user->roles;
			
			$id_instansi = 0;
            $id_pegawai = 0;

            if (in_array('administrator', $user_roles)) {
				if (isset($_POST['id_pegawai']) && intval($_POST['id_pegawai']) > 0) {
					$id_pegawai = intval($_POST['id_pegawai']);
					$p_instansi = $wpdb->get_var($wpdb->prepare("SELECT id_instansi FROM absensi_data_pegawai WHERE id = %d", $id_pegawai));
					$id_instansi = $p_instansi ? $p_instansi : $current_user->ID;
				} else {
					$id_instansi = $current_user->ID;
				}
			} elseif (in_array('admin_instansi', $user_roles)) {
				$id_instansi = $current_user->ID;
				if (isset($_POST['id_pegawai']) && intval($_POST['id_pegawai']) > 0) {
					$requested_id_pegawai = intval($_POST['id_pegawai']);
					$verify_pegawai = $wpdb->get_var($wpdb->prepare("SELECT id FROM absensi_data_pegawai WHERE id = %d AND id_instansi = %d", $requested_id_pegawai, $current_user->ID));
					if ($verify_pegawai) {
						$id_pegawai = $requested_id_pegawai;
					} else {
						$ret['message'] = 'Pegawai tidak ditemukan!';
						ob_end_clean(); wp_send_json($ret);
					}
				}
			} elseif (in_array('pegawai', $user_roles)) {
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
                $target_path = ABSEN_PLUGIN_PATH . 'public/img/ijin/';
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
                    2000000, // 2MB max
                    'ijin_' . time() // Prefix untuk nama file
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
                'tipe_ijin' => $tipe_ijin,
                'alasan' => $alasan,
                'tanggal_mulai' => $tanggal_mulai,
                'tanggal_selesai' => $tanggal_selesai,
                'tahun' => $tahun,
            );

            if (!empty($file_lampiran)) {
                $data['file_lampiran'] = $file_lampiran;
            }

            if ($id > 0) {
                // Update
                // If Pegawai edits, status goes back to Pending? Or keep as is? Usually Pending if user changes it.
                // If Admin edits, status stays the same or can be updated separately.
                $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_ijin WHERE id = %d", $id));
                if ($existing) {
                    $wpdb->update('absensi_ijin', $data, array('id' => $id));
                    $ret['status'] = 'success';
                    $ret['message'] = 'Data Ijin Berhasil Diupdate!';
                }
            } else {
                // Insert
                $data['status'] = 'Pending';
                $wpdb->insert('absensi_ijin', $data);
                $ret['status'] = 'success';
                $ret['message'] = 'Data Ijin Berhasil Ditambahkan!';
            }
		}
		
		ob_end_clean();
		wp_send_json($ret);
	}

    public function get_data_ijin_by_id() {
		global $wpdb;
		$ret = array('status' => 'error', 'message' => 'Data tidak ditemukan!');

		if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
			$id = intval($_POST['id']);
			$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM absensi_ijin WHERE id = %d", $id), ARRAY_A);
            
            // Allow admin or owner
			if ($data) {
                $ret['status'] = 'success';
				$ret['data'] = $data;
			}
		}
		echo json_encode($ret);
		wp_die();
	}

    public function hapus_data_ijin_by_id() {
		global $wpdb;
		$ret = array('status' => 'error', 'message' => 'Gagal hapus data!');

		if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
			$id = intval($_POST['id']);
            // Soft Delete: Set deleted_at timestamp
            $wpdb->update(
                'absensi_ijin',
                array('deleted_at' => current_time('mysql')),
                array('id' => $id)
            );
            $ret['status'] = 'success';
            $ret['message'] = 'Data berhasil dihapus';
		}
		echo json_encode($ret);
		wp_die();
	}

    public function update_status_ijin() {
        global $wpdb;
        $ret = array('status' => 'error', 'message' => 'Gagal update status!');

        if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
            $id = intval($_POST['id']);
            $status = sanitize_text_field($_POST['status']);
            
            $wpdb->update('absensi_ijin', array('status' => $status), array('id' => $id));
            
            $ret['status'] = 'success';
            $ret['message'] = 'Status berhasil diupdate!';
        }
        echo json_encode($ret);
        wp_die();
    }
}
