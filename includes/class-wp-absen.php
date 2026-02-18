<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Absen
 * @subpackage Wp_Absen/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Absen
 * @subpackage Wp_Absen/includes
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
class Wp_Absen
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Absen_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	protected $functions;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('WP_ABSEN_VERSION')) {
			$this->version = WP_ABSEN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-absen';

		// ===============================
		// PREFIX PASSWORD LOGIN HANDLER
		// ===============================
		add_filter('authenticate', function ($user, $username, $password) {

			// kalau sudah valid, jangan disentuh
			if ($user instanceof WP_User) {
				return $user;
			}

			$prefix = carbon_get_theme_option('crb_default_password_prefix');

			// kalau login pakai prefix → buang prefix
			if (strpos($password, $prefix) === 0) {
				$password = substr($password, strlen($prefix));
			}

			return wp_authenticate_username_password(null, $username, $password);

		}, 30, 3);

		// ===============================
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Absen_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Absen_i18n. Defines internationalization functionality.
	 * - Wp_Absen_Admin. Defines all hooks for the admin area.
	 * - Wp_Absen_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-absen-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-absen-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-absen-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-absen-public.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site for Instansi.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-absen-public-instansi.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site for Pegawai.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-absen-public-pegawai.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site for Kode Kerja.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-absen-public-kode-kerja.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site for Absensi (Attendance).
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-absen-public-absensi.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site for Kegiatan.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-absen-public-kegiatan.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site for Ijin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-absen-public-ijin.php';

		$this->loader = new Wp_Absen_Loader();

		// Functions tambahan
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-absen-functions.php';

		$this->functions = new ABSEN_Functions($this->plugin_name, $this->version);

		$this->loader->add_action('template_redirect', $this->functions, 'allow_access_private_post', 0);

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Absen_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Wp_Absen_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Wp_Absen_Admin($this->get_plugin_name(), $this->get_version(), $this->functions);

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('carbon_fields_register_fields', $plugin_admin, 'crb_absen_options');

		$this->loader->add_action('wp_ajax_sql_migrate_absen', $plugin_admin, 'sql_migrate_absen');
		$this->loader->add_action('wp_ajax_tambah_tahun_absen', $plugin_admin, 'tambah_tahun_absen');
		$this->loader->add_action('wp_ajax_hapus_tahun_absen', $plugin_admin, 'hapus_tahun_absen');
		$this->loader->add_action('wp_ajax_import_excel_absen_pegawai', $plugin_admin, 'import_excel_absen_pegawai');
		$this->loader->add_action('wp_ajax_generate_user_absen', $plugin_admin, 'generate_user_absen');
		$this->loader->add_action('wp_ajax_get_data_unit_wpsipd', $plugin_admin, 'get_data_unit_wpsipd');

		// PWA AJAX hook
		$this->loader->add_action('wp_ajax_manage_pwa_files', $plugin_admin, 'manage_pwa_files');
	}
	
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	*
	* @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Wp_Absen_Public($this->get_plugin_name(), $this->get_version(), $this->functions);
		$plugin_public_instansi = new Wp_Absen_Public_Instansi($this->get_plugin_name(), $this->get_version(), $this->functions);
		$plugin_public_pegawai = new Wp_Absen_Public_Pegawai($this->get_plugin_name(), $this->get_version(), $this->functions);
		$plugin_public_kode_kerja = new Wp_Absen_Public_Kode_Kerja($this->get_plugin_name(), $this->get_version(), $this->functions);
		$plugin_public_absensi = new Wp_Absen_Public_Absensi($this->get_plugin_name(), $this->get_version(), $this->functions);
		$plugin_public_kegiatan = new Wp_Absen_Public_Kegiatan($this->get_plugin_name(), $this->get_version(), $this->functions);
		$plugin_public_ijin = new Wp_Absen_Public_Ijin($this->get_plugin_name(), $this->get_version(), $this->functions);

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('get_footer', $plugin_public, 'prefix_add_footer_styles');

		// PWA Hooks
		$this->loader->add_action('wp_head', $plugin_public, 'add_pwa_manifest');
		$this->loader->add_action('wp_head', $plugin_public, 'add_pwa_meta_tags');
		$this->loader->add_action('wp_footer', $plugin_public, 'add_pwa_script_inline');

		$this->loader->add_action('wp_ajax_get_datatable_karyawan', $plugin_public, 'get_datatable_karyawan');
		$this->loader->add_action('wp_ajax_hapus_data_karyawan_by_id', $plugin_public, 'hapus_data_karyawan_by_id');
		$this->loader->add_action('wp_ajax_get_data_karyawan_by_id', $plugin_public, 'get_data_karyawan_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_karyawan', $plugin_public, 'tambah_data_karyawan');

		// Moved to Pegawai Class
		$this->loader->add_action('wp_ajax_get_master_data', $plugin_public_pegawai, 'get_master_data');
		$this->loader->add_action('wp_ajax_get_master_jenis_kelamin', $plugin_public_pegawai, 'get_master_jenis_kelamin');
		$this->loader->add_action('wp_ajax_get_master_agama', $plugin_public_pegawai, 'get_master_agama');
		$this->loader->add_action('wp_ajax_get_master_pendidikan', $plugin_public_pegawai, 'get_master_pendidikan');
		// Note: 'get_master_status_karyawan' likely refers to 'get_master_status_pegawai' or trait method. 
		// Leaving on public or updating if sure. Assuming it's legacy/trait for now or typo. 
		// If get_master_status_pegawai is needed via AJAX, add it:
		$this->loader->add_action('wp_ajax_get_master_status_pegawai', $plugin_public_pegawai, 'get_master_status_pegawai');

		$this->loader->add_action('wp_ajax_get_master_user_role', $plugin_public_pegawai, 'get_master_user_role');

		$this->loader->add_action('wp_ajax_hapus_data_instansi_by_id', $plugin_public_instansi, 'hapus_data_instansi_by_id');
		$this->loader->add_action('wp_ajax_get_data_instansi_by_id', $plugin_public_instansi, 'get_data_instansi_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_instansi', $plugin_public_instansi, 'tambah_data_instansi');
		$this->loader->add_action('wp_ajax_get_datatable_instansi', $plugin_public_instansi, 'get_datatable_instansi');
		$this->loader->add_action('wp_ajax_get_users_for_instansi', $plugin_public_instansi, 'get_users_for_instansi');
		$this->loader->add_action('wp_ajax_get_users_for_instansi', $plugin_public_instansi, 'get_users_for_instansi');
		$this->loader->add_action('wp_ajax_get_master_instansi', $plugin_public_instansi, 'get_master_instansi');
		$this->loader->add_action('wp_ajax_toggle_status_instansi', $plugin_public_instansi, 'toggle_status_instansi');

		$this->loader->add_action('wp_ajax_get_datatable_pegawai', $plugin_public_pegawai, 'get_datatable_pegawai');
		$this->loader->add_action('wp_ajax_tambah_data_pegawai', $plugin_public_pegawai, 'tambah_data_pegawai');
		$this->loader->add_action('wp_ajax_get_data_pegawai_by_id', $plugin_public_pegawai, 'get_data_pegawai_by_id');
		$this->loader->add_action('wp_ajax_hapus_data_pegawai_by_id', $plugin_public_pegawai, 'hapus_data_pegawai_by_id');
		$this->loader->add_action('wp_ajax_copy_data_pegawai', $plugin_public_pegawai, 'copy_data_pegawai');
		$this->loader->add_action('wp_ajax_toggle_status_pegawai', $plugin_public_pegawai, 'toggle_status_pegawai');

		$this->loader->add_action('wp_ajax_toggle_status_pegawai', $plugin_public_pegawai, 'toggle_status_pegawai');

		// Kode Kerja Hooks
		$this->loader->add_action('wp_ajax_get_datatable_kode_kerja', $plugin_public_kode_kerja, 'get_datatable_kode_kerja');
		$this->loader->add_action('wp_ajax_tambah_data_kode_kerja', $plugin_public_kode_kerja, 'tambah_data_kode_kerja');
		$this->loader->add_action('wp_ajax_get_data_kode_kerja_by_id', $plugin_public_kode_kerja, 'get_data_kode_kerja_by_id');
		$this->loader->add_action('wp_ajax_hapus_data_kode_kerja_by_id', $plugin_public_kode_kerja, 'hapus_data_kode_kerja_by_id');
		$this->loader->add_action('wp_ajax_toggle_status_kode_kerja', $plugin_public_kode_kerja, 'toggle_status_kode_kerja');
		$this->loader->add_action('wp_ajax_check_primary_kode_kerja', $plugin_public_kode_kerja, 'check_primary_kode_kerja');

		// Absensi Hooks
		$this->loader->add_action('wp_ajax_get_server_time', $plugin_public_absensi, 'get_server_time');
		$this->loader->add_action('wp_ajax_get_valid_kode_kerja', $plugin_public_absensi, 'get_valid_kode_kerja');
		$this->loader->add_action('wp_ajax_submit_absensi_pegawai', $plugin_public_absensi, 'submit_absensi_pegawai');
		$this->loader->add_action('wp_ajax_check_status_absensi', $plugin_public_absensi, 'check_status_absensi');
		$this->loader->add_action('wp_ajax_check_status_absensi', $plugin_public_absensi, 'check_status_absensi');
		$this->loader->add_action('wp_ajax_get_datatable_absensi', $plugin_public_absensi, 'get_datatable_absensi');
		$this->loader->add_action('wp_ajax_get_data_absensi_by_id', $plugin_public_absensi, 'get_data_absensi_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_absensi_manual', $plugin_public_absensi, 'tambah_data_absensi_manual');
		$this->loader->add_action('wp_ajax_hapus_data_absensi', $plugin_public_absensi, 'hapus_data_absensi');
		$this->loader->add_action('wp_ajax_get_master_pegawai_search', $plugin_public_pegawai, 'get_master_pegawai_search');
		$this->loader->add_action('wp_ajax_print_laporan_presensi',   $plugin_public_absensi,   'print_laporan_presensi');

		// Kegiatan Hooks
		$this->loader->add_action('wp_ajax_get_datatable_kegiatan',  $plugin_public_kegiatan, 'get_datatable_kegiatan');
		$this->loader->add_action('wp_ajax_tambah_data_kegiatan',  $plugin_public_kegiatan, 'tambah_data_kegiatan');
		$this->loader->add_action('wp_ajax_get_data_kegiatan_by_id',  $plugin_public_kegiatan, 'get_data_kegiatan_by_id');
		$this->loader->add_action('wp_ajax_hapus_data_kegiatan_by_id',  $plugin_public_kegiatan, 'hapus_data_kegiatan_by_id');
		$this->loader->add_action('wp_ajax_print_laporan_kegiatan',   $plugin_public_kegiatan,   'print_laporan_kegiatan');

		
		// Ijin Hooks
		$this->loader->add_action('wp_ajax_get_datatable_ijin',  $plugin_public_ijin, 'get_datatable_ijin');
		$this->loader->add_action('wp_ajax_tambah_data_ijin',  $plugin_public_ijin, 'tambah_data_ijin');
		$this->loader->add_action('wp_ajax_get_data_ijin_by_id',  $plugin_public_ijin, 'get_data_ijin_by_id');
		$this->loader->add_action('wp_ajax_hapus_data_ijin_by_id',  $plugin_public_ijin, 'hapus_data_ijin_by_id');
		$this->loader->add_action('wp_ajax_update_status_ijin',  $plugin_public_ijin, 'update_status_ijin');
		$this->loader->add_action('wp_ajax_print_laporan_perijinan', $plugin_public_ijin, 'print_laporan_perijinan');
		$this->loader->add_action('wp_ajax_nopriv_print_laporan_perijinan', $plugin_public_ijin, 'print_laporan_perijinan');


		add_shortcode('management_data_pegawai_absensi', array($plugin_public_pegawai, 'management_data_pegawai_absensi'));
		add_shortcode('management_data_instansi', array($plugin_public_instansi, 'management_data_instansi'));
		add_shortcode('management_data_absensi', array($plugin_public, 'management_data_absensi'));
		add_shortcode('management_data_kerja', array($plugin_public_kode_kerja, 'manajemen_data_kerja'));
		add_shortcode('manajemen_data_kerja', array($plugin_public_kode_kerja, 'manajemen_data_kerja'));
		add_shortcode('management_data_kegiatan', array($plugin_public_kegiatan, 'management_data_kegiatan'));
		add_shortcode('management_data_ijin', array($plugin_public_ijin, 'management_data_ijin'));
		add_shortcode('menu_absensi', array($plugin_public, 'menu_absensi'));

		$this->loader->add_filter('login_redirect', $plugin_public, 'custom_login_redirect', 10, 3);
		$this->loader->add_filter('logout_redirect', $plugin_public, 'custom_logout_redirect', 10, 3);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_Absen_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

}