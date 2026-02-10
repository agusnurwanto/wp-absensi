<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Absen
 * @subpackage Wp_Absen/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Absen
 * @subpackage Wp_Absen/admin
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Wp_Absen_Admin
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $functions)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->functions = $functions;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-absen-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

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

		wp_enqueue_script($this->plugin_name . 'jszip', plugin_dir_url(__FILE__) . 'js/jszip.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'xlsx', plugin_dir_url(__FILE__) . 'js/xlsx.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-absen-admin.js', array('jquery'), $this->version, false);

	}

	public function crb_absen_options()
	{
		global $wpdb;

		$laporan_bulanan_absensi = $this->functions->generatePage(array(
			'nama_page' => 'Laporan Bulanan Absensi',
			'content' => '[laporan_bulanan_absensi]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));

		$ubah_password_page = $this->functions->generatePage(array(
			'nama_page' => 'Ubah Password Absen',
			'content' => '[ubah_password_absen]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'publish'
		));

		$api_key = get_option(ABSEN_APIKEY);
		if (empty($api_key)) {
			$api_key = $this->functions->generateRandomString();
			update_option(ABSEN_APIKEY, $api_key);
		}

		// Check if absensi_tahun table exists
		$table_tahun = 'absensi_tahun';
		$tahun_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_tahun'") === $table_tahun;

		$get_data = '';
		$get_data_instansi = '';
		$get_absensi_pegawai = '';
		$get_data_kegiatan = '';
		$get_data_ijin = '';
		$get_list_tahun = '';

		if ($tahun_exists) {
			$get_tahun = $wpdb->get_results('SELECT tahun FROM absensi_tahun WHERE deleted_at IS NULL AND active = 1 ORDER BY tahun ASC', ARRAY_A);

			// Build list of years for display
			if (!empty($get_tahun)) {
				foreach ($get_tahun as $t) {
					$get_list_tahun .= '<li>' . esc_html($t['tahun']) . ' <a href="#" onclick="hapus_tahun(' . $t['tahun'] . '); return false;" class="button button-small" style="color:red;">Hapus</a></li>';
				}
			} else {
				$get_list_tahun = '<li style="color: orange;">Belum ada tahun yang ditambahkan.</li>';
			}

			if (!empty($get_tahun) && is_array($get_tahun)) {
				foreach ($get_tahun as $k => $v) {
					$management_data_pegawai = $this->functions->generatePage(array(
						'nama_page' => 'Management Data Pegawai | ' . $v['tahun'],
						'content' => '[management_data_pegawai_absensi tahun_anggaran="' . $v["tahun"] . '"]',
						'show_header' => 1,
						'no_key' => 1,
						'post_status' => 'private'
					));

					$get_data .= '<li><a target="_blank" href="' . $management_data_pegawai['url'] . '">' . esc_html($management_data_pegawai['title']) . '</a></li>';
					$management_data_instansi = $this->functions->generatePage(array(
						'nama_page' => 'Management Data Instansi | ' . $v['tahun'],
						'content' => '[management_data_instansi tahun_anggaran="' . $v["tahun"] . '"]',
						'show_header' => 1,
						'no_key' => 1,
						'post_status' => 'published'
					));

					$get_data_instansi .= '<li><a target="_blank" href="' . $management_data_instansi['url'] . '">' . esc_html($management_data_instansi['title']) . '</a></li>';
					$management_data_absensi = $this->functions->generatePage(array(
						'nama_page' => 'Data Absensi Pegawai | ' . $v['tahun'],
						'content' => '[management_data_absensi tahun_anggaran="' . $v["tahun"] . '"]',
						'show_header' => 1,
						'no_key' => 1,
						'post_status' => 'private'
					));
					$get_absensi_pegawai .= '<li><a target="_blank" href="' . $management_data_absensi['url'] . '">' . esc_html($management_data_absensi['title']) . '</a></li>';

					$management_data_kegiatan = $this->functions->generatePage(array(
						'nama_page' => 'Data Kegiatan Pegawai | ' . $v['tahun'],
						'content' => '[management_data_kegiatan tahun_anggaran="' . $v["tahun"] . '"]',
						'show_header' => 1,
						'no_key' => 1,
						'post_status' => 'private'
					));
					$get_data_kegiatan .= '<li><a target="_blank" href="' . $management_data_kegiatan['url'] . '">' . esc_html($management_data_kegiatan['title']) . '</a></li>';

					$management_data_ijin = $this->functions->generatePage(array(
						'nama_page' => 'Data Ijin Pegawai | ' . $v['tahun'],
						'content' => '[management_data_ijin tahun_anggaran="' . $v["tahun"] . '"]',
						'show_header' => 1,
						'no_key' => 1,
						'post_status' => 'private'
					));
					$get_data_ijin .= '<li><a target="_blank" href="' . $management_data_ijin['url'] . '">' . esc_html($management_data_ijin['title']) . '</a></li>';
				}
			}
		} else {
			$get_data = '<li style="color: red; font-weight: bold;">Tabel belum dibuat. Silakan jalankan SQL Migrate terlebih dahulu.</li>';
			$get_list_tahun = '<li style="color: red; font-weight: bold;">Tabel belum dibuat. Silakan jalankan SQL Migrate terlebih dahulu.</li>';
		}

		$basic_options_container = Container::make('theme_options', 'Absensi Options')
			->set_page_menu_position(3)
			->add_tab('⚙️ Konfigurasi Umum', $this->generate_fields_options_konfigurasi_umum())
			->add_tab('📅 Manajemen Tahun', $this->generate_fields_options_manajemen_tahun($get_list_tahun))
			->add_tab('🔌 API WP SIPD', $this->generate_fields_options_api_wpsipd());

		Container::make('theme_options', __('Menu Instansi'))
			->set_page_parent($basic_options_container)
			->add_tab('⚙️ Data Instansi', $this->generate_fields_options_data_instansi($get_data_instansi));

		Container::make('theme_options', __('Menu Pegawai'))
			->set_page_parent($basic_options_container)
			->add_tab('⚙️ Data Pegawai', $this->generate_fields_options_konfigurasi_umum_pegawai($get_data))
			->add_tab('📋 Absensi Pegawai', $this->generate_fields_options_absensi_pegawai($get_absensi_pegawai))
			->add_tab('📅 Data Kegiatan Pegawai', $this->generate_fields_options_kegiatan_pegawai($get_data_kegiatan))
			->add_tab('📝 Data Ijin Pegawai', $this->generate_fields_options_ijin_pegawai($get_data_ijin));

		Container::make('theme_options', __('Menu Data Kerja'))
			->set_page_parent($basic_options_container)
			->add_tab('⚙️ Data Kerja', $this->generate_fields_options_data_kerja());
	}

	public function generate_fields_options_kegiatan_pegawai($get_data_kegiatan)
	{
		return [
			Field::make('html', 'crb_options_kegiatan_pegawai')
				->set_html('
					<h5>HALAMAN TERKAIT</h5>
					<ol>
						' . $get_data_kegiatan . '
					</ol>
				')
		];
	}

	public function generate_fields_options_ijin_pegawai($get_data_ijin)
	{
		return [
			Field::make('html', 'crb_options_ijin_pegawai')
				->set_html('
					<h5>HALAMAN TERKAIT</h5>
					<ol>
						' . $get_data_ijin . '
					</ol>
				')
		];
	}

	public function generate_fields_options_data_kerja()
	{
		$management_data_kerja = $this->functions->generatePage(array(
			'nama_page' => 'Data Kode Kerja',
			'content' => '[manajemen_data_kerja]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'publish'
		));

		return [
			Field::make('html', 'crb_absen_halaman_data_kerja')
				->set_html('
					<h5>HALAMAN TERKAIT</h5>
					<ol>
						<li><a target="_blank" href="' . $management_data_kerja['url'] . '">' . esc_html($management_data_kerja['title']) . '</a></li>
					</ol>
				'),
		];
	}

	public function generate_fields_options_data_instansi($get_data_instansi)
	{
		return [
			Field::make('html', 'crb_absen_halaman_terkait_instansi')
				->set_html('
					<h5>HALAMAN TERKAIT</h5>
					<ol>
						' . $get_data_instansi . '
					</ol>
				'),
		];
	}

	public function import_excel_absen_pegawai()
	{
		global $wpdb;

		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil import excel!'
		);

		if (!empty($_POST)) {
			$ret['data'] = array(
				'insert' => array(),
				'update' => array(),
				'error' => array()
			);

			foreach ($_POST['data'] as $k => $data) {
				$newData = array();

				foreach ($data as $kk => $vv) {
					$cleanKey = trim(strtolower(preg_replace('/\s+/', '_', $kk)));
					$newData[$cleanKey] = trim(preg_replace('/\s+/', ' ', $vv));
				}

				$data_db = array(
					'id_skpd' => $newData['id_skpd'],
					'nip' => $newData['nip'],
					'nik' => $newData['nik'],
					'gelar_depan' => $newData['gelar_depan'],
					'nama' => $newData['nama'],
					'gelar_belakang' => $newData['gelar_belakang'],
					'nama_lengkap' => $newData['nama_lengkap'],
					'tempat_lahir' => $newData['tempat_lahir'],
					'tanggal_lahir' => $newData['tanggal_lahir'],
					'kode_jenis_kelamin' => $newData['kode_jenis_kelamin'],
					'status' => $newData['status'],
					'gol_ruang' => $newData['gol_ruang'],
					'kode_gol' => $newData['kode_gol'],
					'tmt_pangkat' => $newData['tmt_pangkat'],
					'eselon' => $newData['eselon'],
					'jabatan' => $newData['jabatan'],
					'tipe_pegawai' => $newData['tipe_pegawai'],
					'tmt_jabatan' => $newData['tmt_jabatan'],
					'agama' => $newData['agama'],
					'no_hp' => $newData['no_hp'],
					'alamat' => $newData['alamat'],
					'satuan_kerja' => $newData['satuan_kerja'],
					'unit_kerja_induk' => $newData['unit_kerja_induk'],
					'tmt_pensiun' => $newData['tmt_pensiun'],
					'pendidikan' => $newData['pendidikan'],
					'kode_pendidikan' => $newData['kode_pendidikan'],
					'nama_sekolah' => $newData['nama_sekolah'],
					'nama_pendidikan' => $newData['nama_pendidikan'],
					'lulus' => $newData['lulus'],
					'karpeg' => $newData['karpeg'],
					'karis_karsu' => $newData['karis_karsu'],
					'nilai_prestasi' => $newData['nilai_prestasi'],
					'email' => $newData['email'],
					'tahun' => $newData['tahun'],
					'user_role' => $newData['user_role'],
				);

				$wpdb->last_error = "";
				$cek_id = $wpdb->get_var($wpdb->prepare("
                    SELECT id 
                    FROM absensi_data_pegawai 
                    WHERE nip = %s 
                    AND nik = %s 
                    AND tahun = %s
                ", $newData['nip'], $newData['nik'], $newData['tahun']));

				if (empty($cek_id)) {
					$wpdb->insert("absensi_data_pegawai", $data_db);
					$ret['data']['insert'][] = $data_db;
				} else {
					$wpdb->update("absensi_data_pegawai", $data_db, array(
						"id" => $cek_id
					));
					$ret['data']['update'][] = $data_db;
				}

				if (!empty($wpdb->last_error)) {
					$ret['data']['error'][] = array($wpdb->last_error, $data_db);
				}
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}

		die(json_encode($ret));
	}

	function sql_migrate_absen()
	{
		global $wpdb;

		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil menjalankan SQL migrate!'
		);

		$file = 'tabel.sql';
		$path = ABSEN_PLUGIN_PATH . '/' . $file;

		if (!file_exists($path)) {
			$ret['status'] = 'error';
			$ret['message'] = 'File ' . $path . ' tidak ditemukan!';
			die(json_encode($ret));
		}

		$sql = file_get_contents($path);
		if (empty($sql)) {
			$ret['status'] = 'error';
			$ret['message'] = 'File SQL kosong atau tidak dapat dibaca!';
			die(json_encode($ret));
		}

		$ret['value'] = $file . ' (tgl: ' . date('Y-m-d H:i:s') . ')';
		$ret['sql'] = $sql;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$wpdb->hide_errors();

		try {
			$rows_affected = dbDelta($sql);

			if (empty($rows_affected)) {
				$ret['status'] = 'error';
				$ret['message'] = !empty($wpdb->last_error) ? $wpdb->last_error : 'Tidak ada perubahan pada database atau query gagal dieksekusi.';
			} else {
				$ret['message'] = 'Berhasil menjalankan SQL migrate: ' . implode(' | ', $rows_affected);
				$ret['rows_affected'] = $rows_affected;
				$ret['version'] = $this->version;

				update_option('_last_update_sql_migrate_absen', $ret['value']);
				update_option('_wp_absen_db_version', $this->version);
			}
		} catch (Exception $e) {
			$ret['status'] = 'error';
			$ret['message'] = 'Error: ' . $e->getMessage();
		}

		die(json_encode($ret));
	}

	function generate_user_absen($user = array())
	{
		global $wpdb;

		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil generate user',
			'data' => array(
				'insert' => array(),
				'update' => array()
			)
		);

		$user_all = $wpdb->get_results("
			SELECT
				p.*,
				u.nama_instansi
			from absensi_data_pegawai p
			inner join absensi_data_instansi u on u.id=p.id_instansi
				and u.active=p.active
				and u.tahun=p.tahun
			where p.active=1
		", ARRAY_A);

		foreach ($user_all as $user) {
			$username = $user['nip'];
			if (empty($username)) {
				$username = $user['nik'];
			}

			$email = $user['email'];
			if (empty($email)) {
				$email = $username . '@absenlocal.com';
			}

			if (empty($user['user_role'])) {
				continue;
			}

			$all_roles = explode('|', $user['user_role']);
			foreach ($all_roles as $user_role) {
				$role = get_role($user_role);
				if (empty($role)) {
					add_role($user_role, $user_role, array(
						'read' => true,
						'edit_posts' => false,
						'delete_posts' => false
					));
				}
			}

			$id_user = username_exists($username);
			$prefix = carbon_get_theme_option('crb_default_password_prefix');
			$password = $prefix . $_POST['pass'];

			$options = array(
				'user_login' => $username,
				'user_pass' => $password,
				'user_email' => $email,
				'first_name' => $user['nama'],
				'display_name' => $user['nama'],
				'role' => $all_roles[0]
			);

			if (empty($id_user)) {
				$id_user = wp_insert_user($options);
				$ret['data']['insert'][] = $options;
			} else {
				$options['ID'] = $id_user;
				// wp_update_user($options);
				$ret['data']['update'][] = $options;
			}

			$user_meta = get_userdata($id_user);
			foreach ($all_roles as $user_role) {
				if (
					empty($user_meta->roles)
					|| !in_array($user_role, $user_meta->roles)
				) {
					$theUser = new WP_User($id_user);
					$theUser->add_role($user_role);
				}
			}

			$skpd = $wpdb->get_var("
				SELECT 
					nama_skpd 
				from absensi_data_unit 
				where id_skpd=" . $user['id_skpd'] . " 
					AND active=1
			");

			$meta = array(
				'_crb_nama_skpd' => $skpd,
				'_id_sub_skpd' => $user['id_skpd'],
				'_nip' => $user['nip'],
				'id_pegawai' => $user['id'],
				'description' => 'User dibuat dari autogenerate sistem'
			);

			foreach ($meta as $key => $val) {
				update_user_meta($id_user, $key, $val);
			}
		}

		die(json_encode($ret));
	}

	function get_user_roles_by_user_id($user_id)
	{
		$user = get_userdata($user_id);
		return empty($user) ? array() : $user->roles;
	}

	function is_user_in_role($user_id, $role)
	{
		return in_array($role, get_user_roles_by_user_id($user_id));
	}

	public function generate_fields_options_konfigurasi_umum()
	{
		return [
			Field::make('text', 'crb_apikey_absen', 'API KEY')
				->set_default_value($this->functions->generateRandomString())
				->set_help_text('Wajib diisi. API KEY digunakan untuk integrasi data.'),

			Field::make('checkbox', 'crb_enable_pwa', 'Aktifkan Progressive Web App (PWA)')
				->set_option_value('1')
				->set_help_text('Centang untuk mengaktifkan fitur PWA. PWA memungkinkan aplikasi diinstall di home screen HP dan berfungsi offline dengan game sederhana.'),

			Field::make('text', 'crb_default_password_prefix', 'Prefix Password Default')
				->set_help_text('Prefix ini akan ditambahkan di depan password default saat generate user pegawai/instansi.'),

			Field::make('html', 'crb_sql_fte_absen_buttons')
				->set_html(<<<HTML
                    <div>
                        <a onclick="confirm('Apakah anda yakin ingin menjalankan SQL Migrate?') ? sql_migrate_absen() : false;" href="#" class="button button-primary button-large">SQL Migrate</a>
                    </div>
                HTML)
				->set_width(33.33)
				->set_help_text('Tombol untuk menjalankan database migration.'),

			Field::make('html', 'crb_gen_user_absen')
				->set_html('<a target="_blank" onclick="generate_user_absen(); return false;" href="#" class="button button-primary button-large">Generate User Pegawai</a>')
				->set_help_text('Generate user dari tabel <b>data_pegawai</b>.')
		];
	}

	public function generate_fields_options_manajemen_tahun($get_list_tahun)
	{
		return [
			Field::make('html', 'crb_tahun_list')
				->set_html('
					<h5>DAFTAR TAHUN</h5>
					<ol id="list-tahun">
						' . $get_list_tahun . '
					</ol>
				'),

			Field::make('html', 'crb_tambah_tahun')
				->set_html('
					<h5>TAMBAH TAHUN BARU</h5>
					<div style="display: flex; gap: 10px; align-items: center;">
						<input type="number" id="input_tahun_baru" placeholder="Contoh: ' . date('Y') . '" min="2000" max="2099" style="width: 120px;" value="' . date('Y') . '">
						<a href="#" onclick="tambah_tahun(); return false;" class="button button-primary">Tambah Tahun</a>
					</div>
				')
				->set_help_text('Masukkan tahun baru untuk ditambahkan ke sistem. Halaman manajemen data akan otomatis dibuat untuk tahun tersebut.')
		];
	}

	public function tambah_tahun_absen()
	{
		global $wpdb;

		$ret = array(
			'status' => 'success',
			'message' => 'Tahun berhasil ditambahkan!'
		);

		if (!empty($_POST['tahun'])) {
			$tahun = intval($_POST['tahun']);

			if ($tahun < 2000 || $tahun > 2099) {
				$ret['status'] = 'error';
				$ret['message'] = 'Tahun harus antara 2000-2099!';
			} else {
				// Check if already exists
				$existing = $wpdb->get_var($wpdb->prepare(
					"SELECT id FROM absensi_tahun WHERE tahun = %d AND deleted_at IS NULL",
					$tahun
				));

				if ($existing) {
					$ret['status'] = 'error';
					$ret['message'] = 'Tahun ' . $tahun . ' sudah ada!';
				} else {
					$wpdb->insert('absensi_tahun', array(
						'tahun' => $tahun,
						'active' => 1,
						'created_at' => current_time('mysql')
					));
					$ret['message'] = 'Tahun ' . $tahun . ' berhasil ditambahkan! Silakan refresh halaman.';
				}
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Tahun tidak boleh kosong!';
		}

		die(json_encode($ret));
	}

	public function hapus_tahun_absen()
	{
		global $wpdb;

		$ret = array(
			'status' => 'success',
			'message' => 'Tahun berhasil dihapus!'
		);

		if (!empty($_POST['tahun'])) {
			$tahun = intval($_POST['tahun']);

			// Soft delete
			$wpdb->update(
				'absensi_tahun',
				array('deleted_at' => current_time('mysql')),
				array('tahun' => $tahun)
			);

			$ret['message'] = 'Tahun ' . $tahun . ' berhasil dihapus! Silakan refresh halaman.';
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Tahun tidak valid!';
		}

		die(json_encode($ret));
	}

	public function generate_fields_options_api_wpsipd()
	{
		return [
			Field::make('text', 'crb_url_server_wpsipd', 'URL Server WP-SIPD')
				->set_default_value(admin_url('admin-ajax.php'))
				->set_required(true),

			Field::make('text', 'crb_apikey_wpsipd', 'API KEY WP-SIPD')
				->set_default_value($this->functions->generateRandomString())
				->set_help_text('Wajib diisi. API KEY digunakan untuk integrasi data.'),

			Field::make('text', 'crb_tahun_wpsipd', 'Tahun Anggaran WP-SIPD')
				->set_default_value(date('Y'))
				->set_help_text('Wajib diisi.'),

			Field::make('html', 'crb_html_data_unit')
				->set_html('<a href="#" class="button button-primary" onclick="get_data_unit_wpsipd(); return false;">Tarik Data Unit dari WP SIPD</a>')
				->set_help_text('Tombol untuk menarik data Unit dari WP SIPD.')
		];
	}

	public function generate_fields_options_konfigurasi_umum_pegawai($get_data)
	{
		return [
			Field::make('html', 'crb_absen_pegawai_hide_sidebar')
				->set_html('
					<h5>HALAMAN TERKAIT</h5>
					<ol>
						' . $get_data . '
					</ol>
				'),

			Field::make('html', 'crb_absen_pegawai_field_visibility_header')
				->set_html('<h5>PENGATURAN FIELD FORM PEGAWAI</h5><p>Centang field yang ingin disembunyikan pada form tambah/edit data pegawai:</p>'),

			Field::make('checkbox', 'crb_hide_tempat_lahir', 'Sembunyikan Tempat Lahir')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_tanggal_lahir', 'Sembunyikan Tanggal Lahir')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_jenis_kelamin', 'Sembunyikan Jenis Kelamin')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_agama', 'Sembunyikan Agama')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_pendidikan_terakhir', 'Sembunyikan Pendidikan Terakhir')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_pendidikan_sekarang', 'Sembunyikan Pendidikan Sekarang')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_nama_sekolah', 'Sembunyikan Nama Sekolah')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_lulus', 'Sembunyikan Lulus (Tahun)')
				->set_default_value(true)
				->set_option_value('yes'),

			Field::make('checkbox', 'crb_hide_alamat', 'Sembunyikan Alamat')
				->set_default_value(true)
				->set_option_value('yes'),

			// Field::make('html', 'crb_absen_pegawai_upload_html')
			//     ->set_html('<h3>Import EXCEL data Pegawai</h3>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePickedAbsen(event);"><br>Contoh format file excel bisa <a target="_blank" href="' . ABSEN_PLUGIN_URL . 'public/media/absen/contoh_data_pegawai.xlsx' . '">download di sini</a>. Sheet file excel yang akan diimport harus diberi nama <b>data</b>. Untuk kolom nilai angka ditulis tanpa tanda titik.'),
			// Field::make('html', 'crb_absen_pegawai')
			//     ->set_html('Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea>'),
			// Field::make('html', 'crb_absen_pegawai_save_button')
			//     ->set_html('<a onclick="import_excel_absen_pegawai(); return false" href="javascript:void(0);" class="button button-primary">Import WP</a>')
		];
	}

	public function generate_fields_options_absensi_pegawai($get_absensi_pegawai)
	{
		return [
			Field::make('html', 'crb_options_absen_pegawai')
				->set_html('
					<h5>HALAMAN TERKAIT</h5>
					<ol>
						' . $get_absensi_pegawai . '
					</ol>
				')
		];
	}

	function get_data_unit_wpsipd()
	{
		global $wpdb;

		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil Get Data Unit WP-SIPD!'
		);

		if (empty($_POST['server'])) {
			$ret['status'] = 'error';
			$ret['message'] = 'URL Server Tidak Boleh Kosong';
			die(json_encode($ret));
		} else if (empty($_POST['tahun_anggaran'])) {
			$ret['status'] = 'error';
			$ret['message'] = 'Tahun Anggaran Tidak Boleh Kosong';
			die(json_encode($ret));
		} else if (empty($_POST['api_key'])) {
			$ret['status'] = 'error';
			$ret['message'] = 'API Key Tidak Boleh Kosong';
			die(json_encode($ret));
		}

		// data to send in API request
		$api_params_get_skpd = array(
			'action' => 'get_skpd',
			'api_key' => $_POST['api_key'],
			'tahun_anggaran' => $_POST['tahun_anggaran']
		);

		$api_params_get_rekening = array(
			'action' => 'get_rekening_akun',
			'api_key' => $_POST['api_key'],
			'tahun_anggaran' => $_POST['tahun_anggaran']
		);

		$api_params_get_satuan = array(
			'action' => 'get_data_satuan_ssh',
			'api_key' => $_POST['api_key'],
			'tahun_anggaran' => $_POST['tahun_anggaran'],
			'no_option' => true
		);

		$response_get_skpd = wp_remote_post(
			$_POST['server'],
			array(
				'timeout' => 1000,
				'sslverify' => false,
				'body' => $api_params_get_skpd
			)
		);

		$response_get_rekening = wp_remote_post(
			$_POST['server'],
			array(
				'timeout' => 1000,
				'sslverify' => false,
				'body' => $api_params_get_rekening
			)
		);

		$response_get_satuan = wp_remote_post(
			$_POST['server'],
			array(
				'timeout' => 1000,
				'sslverify' => false,
				'body' => $api_params_get_satuan
			)
		);

		$response_get_skpd = wp_remote_retrieve_body($response_get_skpd);
		$response_get_rekening = wp_remote_retrieve_body($response_get_rekening);
		$response_get_satuan = wp_remote_retrieve_body($response_get_satuan);

		$data_get_skpd = json_decode($response_get_skpd);
		$data_get_rekening = json_decode($response_get_rekening);
		$data_get_satuan = json_decode($response_get_satuan);

		$absensi_data_unit = $data_get_skpd->data;
		$absensi_data_rekening_akun = $data_get_rekening->items;
		$absensi_data_satuan = $data_get_satuan->data;
		
		if ($data_get_skpd->status == 'success' && !empty($absensi_data_unit)) {
			$wpdb->update(
				'absensi_data_unit',
				array('active' => 0),
				array('tahun_anggaran' => $api_params_get_skpd['tahun_anggaran'])
			);

			foreach ($absensi_data_unit as $vdata) {
				$cek = $wpdb->get_var(
					$wpdb->prepare('
						SELECT id 
						FROM absensi_data_unit 
						WHERE id_skpd = %d
						AND tahun_anggaran = %d
					', $vdata->id_skpd, $vdata->tahun_anggaran)
				);

				$data = array(
					'id_setup_unit' => $vdata->id_setup_unit,
					'id_unit' => $vdata->id_unit,
					'is_skpd' => $vdata->is_skpd,
					'kode_skpd' => $vdata->kode_skpd,
					'kunci_skpd' => $vdata->kunci_skpd,
					'nama_skpd' => $vdata->nama_skpd,
					'posisi' => $vdata->posisi,
					'status' => $vdata->status,
					'id_skpd' => $vdata->id_skpd,
					'bidur_1' => $vdata->bidur_1,
					'bidur_2' => $vdata->bidur_2,
					'bidur_3' => $vdata->bidur_3,
					'idinduk' => $vdata->idinduk,
					'ispendapatan' => $vdata->ispendapatan,
					'isskpd' => $vdata->isskpd,
					'kode_skpd_1' => $vdata->kode_skpd_1,
					'kode_skpd_2' => $vdata->kode_skpd_2,
					'kodeunit' => $vdata->kodeunit,
					'komisi' => $vdata->komisi,
					'namabendahara' => $vdata->namabendahara,
					'namakepala' => $vdata->namakepala,
					'namaunit' => $vdata->namaunit,
					'nipbendahara' => $vdata->nipbendahara,
					'nipkepala' => $vdata->nipkepala,
					'pangkatkepala' => $vdata->pangkatkepala,
					'setupunit' => $vdata->setupunit,
					'statuskepala' => $vdata->statuskepala,
					'update_at' => $vdata->update_at,
					'tahun_anggaran' => $vdata->tahun_anggaran,
					'active' => $vdata->active
				);

				if (empty($cek)) {
					$wpdb->insert(
						'absensi_data_unit',
						$data
					);
				} else {
					$wpdb->update(
						'absensi_data_unit',
						$data,
						array('id' => $cek)
					);
				}
			}
		} else {
			$ret['status'] = 'error';
			if ($data_get_skpd->status == 'error') {
				$ret['message'] = $data_get_skpd->message;
			} else {
				$ret['message'] = 'Data Unit kosong untuk tahun ' . $_POST['tahun_anggaran'] . '!';
			}
			die(json_encode($ret));
		}

		if ($data_get_rekening->status == true && !empty($absensi_data_rekening_akun)) {
			$wpdb->update(
				'absensi_data_rekening_akun',
				array('active' => 0),
				array('tahun_anggaran' => $api_params_get_rekening['tahun_anggaran'])
			);

			foreach ($absensi_data_rekening_akun as $vdata) {
				$cek = $wpdb->get_var(
					$wpdb->prepare('
						SELECT id 
						FROM absensi_data_rekening_akun 
						WHERE id_akun = %d
						AND kode_akun = %s
						AND tahun_anggaran = %d
					', $vdata->id_akun, $vdata->kode_akun, $api_params_get_rekening['tahun_anggaran'])
				);

				$data = array(
					'id_akun' => $vdata->id_akun,
					'kode_akun' => $vdata->kode_akun,
					'nama_akun' => $vdata->nama_akun,
					'tahun_anggaran' => $api_params_get_rekening['tahun_anggaran'],
					'active' => 1
				);

				if (empty($cek)) {
					$wpdb->insert(
						'absensi_data_rekening_akun',
						$data
					);
				} else {
					$wpdb->update(
						'absensi_data_rekening_akun',
						$data,
						array('id' => $cek)
					);
				}
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Data Rekening gagal untuk didapatkan!';
			die(json_encode($ret));
		}

		if ($data_get_satuan->status == true && !empty($absensi_data_satuan)) {
			$wpdb->update(
				'absensi_data_satuan',
				array('active' => 0),
				array('tahun_anggaran' => $api_params_get_rekening['tahun_anggaran'])
			);

			foreach ($absensi_data_satuan as $vdata) {
				$cek = $wpdb->get_var(
					$wpdb->prepare('
						SELECT id 
						FROM absensi_data_satuan 
						WHERE tahun_anggaran = %d
						AND id_satuan = %d
					',
						$vdata->id_satuan,
						$vdata->tahun_anggaran
					)
				);

				$data = array(
					'id_satuan' => $vdata->id_satuan,
					'nama_satuan' => $vdata->nama_satuan,
					'tahun_anggaran' => $api_params_get_rekening['tahun_anggaran'],
					'active' => 1
				);

				if (empty($cek)) {
					$wpdb->insert(
						'absensi_data_satuan',
						$data
					);
				} else {
					$wpdb->update(
						'absensi_data_satuan',
						$data,
						array('id' => $cek)
					);
				}
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Data Satuan gagal untuk didapatkan!';
			die(json_encode($ret));
		}

		die(json_encode($ret));
	}

	/**
	 * Copy PWA files to WordPress root directory
	 *
	 * @since    1.0.0
	 */
	public function copy_pwa_files_to_root()
	{
		$wp_root = ABSPATH;
		$plugin_path = ABSEN_PLUGIN_PATH;

		// Files to copy
		$files = array(
			'manifest.json' => 'manifest.json',
			'service-worker.js' => 'service-worker.js'
		);

		foreach ($files as $source => $dest) {
			$source_file = $plugin_path . $source;
			$dest_file = $wp_root . $dest;

			if (file_exists($source_file)) {
				copy($source_file, $dest_file);
			}
		}
	}

	/**
	 * Delete PWA files from WordPress root directory
	 *
	 * @since    1.0.0
	 */
	public function delete_pwa_files_from_root()
	{
		$wp_root = ABSPATH;

		// Files to delete
		$files = array(
			'manifest.json',
			'service-worker.js'
		);

		foreach ($files as $file) {
			$file_path = $wp_root . $file;

			if (file_exists($file_path)) {
				unlink($file_path);
			}
		}
	}

	/**
	 * AJAX Handler for PWA File Management
	 *
	 * @since    1.0.0
	 */
	public function manage_pwa_files()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Permission denied'));
		}

		$enable = isset($_POST['enable']) && $_POST['enable'] === 'true';

		if ($enable) {
			// Enable PWA
			$this->copy_pwa_files_to_root();
			update_option('_crb_enable_pwa', '1');
			$message = 'PWA berhasil diaktifkan. File manifest dan service worker telah dicopy.';
		} else {
			// Disable PWA
			$this->delete_pwa_files_from_root();
			update_option('_crb_enable_pwa', '');
			$message = 'PWA berhasil dinonaktifkan. File manifest dan service worker telah dihapus.';
		}

		wp_send_json_success(array('message' => $message));
	}

}