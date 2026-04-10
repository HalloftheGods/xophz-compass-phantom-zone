<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/admin
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Phantom_Zone_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xophz-compass-phantom-zone-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xophz-compass-phantom-zone-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add menu item 
	 *
	 * @since    1.0.0
	 */
	public function addToMenu(){
        Xophz_Compass::add_submenu($this->plugin_name);
	}

	public function register_rest_routes() {
		register_rest_route('xophz-compass/v1', '/phantom-zone/settings', [
			[
				'methods' => 'GET',
				'callback' => [$this, 'get_settings'],
				'permission_callback' => function() { return current_user_can('manage_options'); }
			],
			[
				'methods' => 'PUT',
				'callback' => [$this, 'update_settings'],
				'permission_callback' => function() { return current_user_can('manage_options'); }
			]
		]);

		register_rest_route('xophz-compass/v1', '/phantom-zone/errors', [
			'methods' => 'GET',
			'callback' => [$this, 'get_errors'],
			'permission_callback' => function() { return current_user_can('manage_options'); }
		]);

		register_rest_route('xophz-compass/v1', '/phantom-zone/stats', [
			'methods' => 'GET',
			'callback' => [$this, 'get_stats'],
			'permission_callback' => function() { return current_user_can('manage_options'); }
		]);
	}

	public function get_settings() {
		$settings = get_option('_compass_phantom_zone_settings', []);
		if (empty($settings)) {
			// Default settings
			$settings = [
				'track_404' => true,
				'track_403' => true,
				'track_500' => true,
				'redirect_404' => '',
			];
		}
		return new WP_REST_Response($settings, 200);
	}

	public function update_settings(WP_REST_Request $request) {
		$params = $request->get_json_params();
		if(!is_array($params)) $params = [];
		
		$settings = get_option('_compass_phantom_zone_settings', []);
		if(empty($settings)) $settings = [];

		if (isset($params['track_404'])) $settings['track_404'] = (bool) $params['track_404'];
		if (isset($params['track_403'])) $settings['track_403'] = (bool) $params['track_403'];
		if (isset($params['track_500'])) $settings['track_500'] = (bool) $params['track_500'];
		if (isset($params['redirect_404'])) $settings['redirect_404'] = sanitize_text_field($params['redirect_404']);

		update_option('_compass_phantom_zone_settings', $settings);
		return new WP_REST_Response($settings, 200);
	}

	public function get_errors(WP_REST_Request $request) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'xophz_phantom_errors';

		$page = (int) $request->get_param('page') ?: 1;
		$per_page = (int) $request->get_param('per_page') ?: 20;
		$offset = ($page - 1) * $per_page;
		$search = sanitize_text_field($request->get_param('search'));

		$where = "1=1";
		if ($search) {
			$where .= $wpdb->prepare(" AND url LIKE %s", '%' . $wpdb->esc_like($search) . '%');
		}

		$errors = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_name WHERE $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		));

		$total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");

		// Get IPs associated with usernames to enrich data
		// But let's keep it simple for now, maybe map users
		foreach ($errors as $error) {
			if ($error->user_id) {
				$user = get_userdata($error->user_id);
				$error->username = $user ? $user->user_login : 'Unknown';
			} else {
				$error->username = 'Guest';
			}
		}

		return new WP_REST_Response([
			'items' => $errors,
			'total' => (int) $total,
			'page' => $page,
			'per_page' => $per_page
		], 200);
	}

	public function get_stats() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'xophz_phantom_errors';

		// Get total counts today
		$today_start = date('Y-m-d 00:00:00');
		
		$totals = $wpdb->get_results("
			SELECT error_code, COUNT(*) as count 
			FROM $table_name 
			GROUP BY error_code
		");

		$today_totals = $wpdb->get_results($wpdb->prepare("
			SELECT error_code, COUNT(*) as count 
			FROM $table_name 
			WHERE created_at >= %s
			GROUP BY error_code
		", $today_start));

		// Get trend data (last 7 days)
		// Instead of a complex dynamic pivot, let's just query normally and build arrays via PHP
		$trend_days = 7;
		$trend_start = date('Y-m-d 00:00:00', strtotime("-$trend_days days"));

		$trends = $wpdb->get_results($wpdb->prepare("
			SELECT error_code, DATE(created_at) as date, COUNT(*) as count 
			FROM $table_name 
			WHERE created_at >= %s
			GROUP BY error_code, DATE(created_at)
			ORDER BY date ASC
		", $trend_start));

		$chart_data = [];
		for ($i = $trend_days; $i >= 0; $i--) {
			$date = date('Y-m-d', strtotime("-$i days"));
			$chart_data[$date] = [
				'404' => 0,
				'403' => 0,
				'500' => 0,
			];
		}

		foreach ($trends as $row) {
			if(isset($chart_data[$row->date]) && in_array($row->error_code, [404, 403, 500])) {
				$chart_data[$row->date][$row->error_code] = (int)$row->count;
			}
		}

		$formatted_totals = ['404' => 0, '403' => 0, '500' => 0];
		foreach ($totals as $row) {
			$formatted_totals[$row->error_code] = (int)$row->count;
		}

		$formatted_today = ['404' => 0, '403' => 0, '500' => 0];
		foreach ($today_totals as $row) {
			$formatted_today[$row->error_code] = (int)$row->count;
		}

		return new WP_REST_Response([
			'totals' => $formatted_totals,
			'today' => $formatted_today,
			'chart' => $chart_data
		], 200);
	}

}
