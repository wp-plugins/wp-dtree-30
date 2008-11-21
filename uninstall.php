<?php
// WordPress 2.7+ will call this file upon plugin Delete
if(!defined('ABSPATH')) exit();	// sanity check
require_once('wp-dtree.php');
require_once('wp-dtree_cache.php');
global $wpdb;
wp_dtree_uninstall_cache();
wp_dtree_unregister_widget();
delete_option('wp_dtree_db_version');
delete_option('wp_dtree_options');
?>