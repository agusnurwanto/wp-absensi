<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Absen
 * @subpackage Wp_Absen/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Absen
 * @subpackage Wp_Absen/includes
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
class Wp_Absen_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $path = ABSEN_PLUGIN_PATH.'/tabel.sql';
        $sql = file_get_contents($path);
        dbDelta($sql);
        update_option('_wp_absen_db_version', WP_ABSEN_VERSION);

        add_role( 'admin_instansi', 'Admin Instansi', array( 'read' => true ) );
        add_role( 'pegawai', 'Pegawai', array( 'read' => true ) );

        // Add default year if table is empty
        $current_year = date('Y');
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM absensi_tahun WHERE tahun = %d AND deleted_at IS NULL",
            $current_year
        ));

        if (!$existing) {
            $wpdb->insert('absensi_tahun', array(
                'tahun' => $current_year,
                'active' => 1,
                'created_at' => current_time('mysql')
            ));
        }
	}

}
