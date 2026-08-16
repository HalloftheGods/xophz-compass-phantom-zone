<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/public
 */

class Xophz_Compass_Phantom_Zone_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {}

	public function enqueue_scripts() {}

	/**
	 * Lightweight error interceptor on template_redirect
	 */
	public function intercept_errors() {
		$is_404 = is_404();
		$http_status = http_response_code();

		$error_code = null;
		if ( $is_404 ) {
			$error_code = 404;
		} elseif ( $http_status === 403 ) {
			$error_code = 403;
		} elseif ( $http_status >= 500 ) {
			$error_code = $http_status;
		}

		if ( ! $error_code ) {
			return;
		}

		$settings = get_option('_compass_phantom_zone_settings', []);

		// Default tracking rules
		$track_key = 'track_' . $error_code;
		$should_track = isset($settings[$track_key]) ? filter_var($settings[$track_key], FILTER_VALIDATE_BOOLEAN) : true;

		if ( $should_track ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'xophz_phantom_errors';
			
			$current_user_id = is_user_logged_in() ? get_current_user_id() : null;
			$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip_address = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip_address = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
			}

			$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
			$url = $_SERVER['REQUEST_URI'] ?? '';

			// Fast indexed insert
			$wpdb->insert(
				$table_name,
				[
					'url' => substr($url, 0, 2048),
					'error_code' => $error_code,
					'user_id' => $current_user_id,
					'ip_address' => substr(sanitize_text_field($ip_address), 0, 45),
					'user_agent' => substr(sanitize_text_field($user_agent), 0, 512)
				]
			);

			// Periodic non-blocking pruning (1 in 100 chance)
			if ( wp_rand( 1, 100 ) === 1 ) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-phantom-zone-db.php';
				Xophz_Compass_Phantom_Zone_DB::prune_old_logs( 30 );
			}

			// If 404, handle custom redirect if configured
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
