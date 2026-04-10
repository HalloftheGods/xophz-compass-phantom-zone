<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/includes
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Phantom_Zone_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	    if ( !class_exists( 'Xophz_Compass' ) ) {  
	    	die('This plugin requires COMPASS to be active.</a></div>');
	    }
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-phantom-zone-db.php';
		Xophz_Compass_Phantom_Zone_DB::create_tables();
	}

}
