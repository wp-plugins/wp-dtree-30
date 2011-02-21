<?php
// WordPress 2.7+ will call this file upon plugin Delete
if(!defined('ABSPATH')) exit();	// sanity check
require_once('wp-dtree.php');
require_once('wp-dtree-cache.php');
global $wpdb;
wpdt_uninstall_cache();
delete_option('wp_dtree_db_version');
delete_option('wpdt_options');
?>