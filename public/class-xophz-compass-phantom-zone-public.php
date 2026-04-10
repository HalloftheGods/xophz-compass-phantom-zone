<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/public
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Phantom_Zone_Public {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Xophz_Compass_Phantom_Zone_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xophz_Compass_Phantom_Zone_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xophz-compass-phantom-zone-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// ... existing code ...
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xophz-compass-phantom-zone-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Intercept errors and log them to the database.
	 */
	public function intercept_errors() {
		$settings = get_option('_compass_phantom_zone_settings', []);
		
		$is_404 = is_404();
		// In WordPress, 403 or 500 might be harder to catch globally via template_redirect, 
		// but we can try to get the HTTP response code.
		$http_status = http_response_code();

		// Determine if we have an error we care about
		$error_code = null;
		
		if ( $is_404 ) {
			$error_code = 404;
		} elseif ( $http_status === 403 ) {
			$error_code = 403;
		} elseif ( $http_status >= 500 ) {
			$error_code = $http_status;
		}

		if ( $error_code ) {
			// Check settings to see if we should track this
			$track_key = 'track_' . $error_code;
			$should_track = isset($settings[$track_key]) ? filter_var($settings[$track_key], FILTER_VALIDATE_BOOLEAN) : true;
			
			// Default to true if setting is missing but we're here
			if (!isset($settings[$track_key]) && $error_code === 404) $should_track = true;
			if (!isset($settings[$track_key]) && $error_code === 403) $should_track = true;
			if (!isset($settings[$track_key]) && $error_code >= 500) $should_track = true;

			if ( $should_track ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'xophz_phantom_errors';
				
				$current_user_id = is_user_logged_in() ? get_current_user_id() : null;
				// Safely get IP address
				$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$ip_address = $_SERVER['HTTP_CLIENT_IP'];
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$ip_address = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
				}

				$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
				$url = $_SERVER['REQUEST_URI'] ?? '';

				// Make sure the table exists before inserting to prevent errors on freshly activated sites
				// Or assume it exists if the plugin is active.
				$wpdb->insert(
					$table_name,
					[
						'url' => substr($url, 0, 2048),
						'error_code' => $error_code,
						'user_id' => $current_user_id,
						'ip_address' => substr($ip_address, 0, 45),
						'user_agent' => $user_agent
					]
				);

				// If 404, check for custom redirect setting
				if ( $error_code === 404 && !empty($settings['redirect_404']) ) {
					$redirect_url = esc_url_raw($settings['redirect_404']);
					if ( wp_validate_redirect($redirect_url, false) ) {
						wp_safe_redirect( $redirect_url, 301 );
						exit;
					}
				}
			}
		}
	}
}
