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



    public function management_data_absensi($atts){
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-absen-management-data-absensi.php';
    }



	public function menu_absensi() {
        global $wpdb;
        
		$user_id = um_user('ID');
		$user_meta = get_userdata($user_id);
        
        $table_name = 'absensi_data_unit';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        $get_data = '';
        $get_data_instansi = '';
        $get_absensi_pegawai = '';
        if ($table_exists) {
            $get_tahun = $wpdb->get_results('SELECT tahun_anggaran FROM absensi_data_unit GROUP BY tahun_anggaran ORDER BY tahun_anggaran ASC', ARRAY_A);
            
            if (!empty($get_tahun) && is_array($get_tahun)) {
                foreach ($get_tahun as $k => $v){
                    $management_data_instansi = $this->functions->generatePage(array(
                        'nama_page' => 'Management Data Instansi | ' . $v['tahun_anggaran'],
                        'content' => '[management_data_instansi tahun_anggaran="' . $v["tahun_anggaran"] . '"]',
                        'show_header' => 1,
                        'no_key' => 1,
                        'post_status' => 'published'
                    ));
                    $get_data_instansi .= '<li><a target="_blank" href="' . $management_data_instansi['url'] . '">' . esc_html($management_data_instansi['title']) . '</a></li>';

                    $management_data_pegawai = $this->functions->generatePage(array(
                        'nama_page' => 'Management Data Pegawai | ' . $v['tahun_anggaran'],
                        'content' => '[management_data_pegawai_absensi tahun_anggaran="' . $v["tahun_anggaran"] . '"]',
                        'show_header' => 1,
                        'no_key' => 1,
                        'post_status' => 'published'
                    ));
                    $get_absensi_pegawai .= '<li><a target="_blank" href="' . $management_data_pegawai['url'] . '">' . esc_html($management_data_pegawai['title']) . '</a></li>';
                }
            }
        }

        if (in_array('admin_instansi', $user_meta->roles) || in_array('administrator', $user_meta->roles)) {
            $html = '
            <h3>Menu Admin Instansi</h3>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="data-instansi-tab" data-toggle="tab" href="#data-instansi" role="tab" aria-controls="data-instansi" aria-selected="true">
                        <span class="dashicons dashicons-admin-generic"></span> Manajemen Data Instansi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="absensi-pegawai-tab" data-toggle="tab" href="#absensi-pegawai" role="tab" aria-controls="absensi-pegawai" aria-selected="false">
                        <span class="dashicons dashicons-clipboard"></span> Manajemen Data Pegawai
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent" style="padding: 20px; border: 1px solid #dee2e6; border-top: none; background: #fff;">
                <div class="tab-pane fade show active" id="data-instansi" role="tabpanel" aria-labelledby="data-instansi-tab">
                    <ul>' . $get_data_instansi . '</ul>
                </div>
                <div class="tab-pane fade" id="absensi-pegawai" role="tabpanel" aria-labelledby="absensi-pegawai-tab">
                    <ul>' . $get_absensi_pegawai . '</ul>
                </div>
            </div>';
            return $html;
        }

    }
}