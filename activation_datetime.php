<?php 
/*
 * Plugin Name: Plugins Activation DateTime
 * Plugin URI: http://webandseoguide.tk
 * Description: This plugin add the date and time when it was last updated or activated
 * Version: 1.0.0
 * Author: Ganesh Veer
 * Author URI: 
 * License: GPL2
*/

defined('ABSPATH') or die('Hey, what are you doing here?');

add_filter( 'plugin_row_meta', 'custom_plugin_row_meta', 10, 2);
function custom_plugin_row_meta( $links, $file ){
	global $wpdb;
	
	$plugin_act_dt = $wpdb->get_var( $wpdb->prepare(  
		"SELECT time 
		 FROM wp_pluginsdata
		 WHERE pluginname = %s",  $file) 
		);

	if(isset($plugin_act_dt)){
		$new_links = array( 'date' => 'Last Update: '.$plugin_act_dt );	
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
//Function to run after plugin activation
function detect_plugin_activation( $plugin, $network_activation ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "pluginsdata"; 

	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ), 
			'pluginname' => $plugin, 
			'pluginurl' => 'url', 
		) 
	);

}
add_action( 'activated_plugin', 'detect_plugin_activation', 10, 2 );

function wp_upe_upgrade_completed( $upgrader_object, $options ) {
 global $wpdb;
 // The path to our plugin's main file
 $our_plugin = plugin_basename( __FILE__ );
 // If an update has taken place and the updated type is plugins and the plugins element exists
 if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
  // Iterate through the plugins being updated and check if ours is there
 foreach( $options['plugins'] as $plugin ) {
  			$table_name = $wpdb->prefix . "pluginsdata"; 
		    $ctime =  current_time( 'mysql' );

  		   // Do stuff before we run the single plugin upgrade
    		$my_part_ID = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM wp_pluginsdata WHERE pluginname = %s LIMIT 1", $plugin ) );

			if ( $my_part_ID > 0 ){  // exists		    		   
			   		$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET  time = %s WHERE pluginname = %s", $ctime, $plugin) );	   		
				}
			else{
				    // does not exist
					$wpdb->insert( 	$table_name, 	array( 	'time' => $ctime, 'pluginname' => $plugin, 'pluginurl' => 'url' ));
				}
  			}
 		}

}
add_action( 'upgrader_process_complete', 'wp_upe_upgrade_completed', 10, 2 );

//Create table to save all plugins updated date
function muplug_create_Table(){
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . "pluginsdata"; 

	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  pluginname tinytext NOT NULL,
	  pluginurl varchar(55) DEFAULT 'url' NOT NULL,
	  PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
add_action( 'init', 'muplug_create_Table', 10, 2 );