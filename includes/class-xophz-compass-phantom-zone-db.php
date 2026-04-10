<?php

/**
 * The database functionality of the plugin.
 *
 * @package    Xophz_Compass_Phantom_Zone
 * @subpackage Xophz_Compass_Phantom_Zone/includes
 */

class Xophz_Compass_Phantom_Zone_DB {

    /**
     * Create the database tables needed for the plugin.
     *
     * @since    1.0.0
     */
    public static function create_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'xophz_phantom_errors';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            url varchar(2048) NOT NULL,
            error_code smallint(5) unsigned NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY error_code (error_code)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}
