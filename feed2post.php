<?php
/*
Plugin Name: Feed to Post
Plugin URI: http://miracles.heaven.fr
Description: This plugin allows you to transform items from a feed to wordpress's posts
Author: olivM
Version: 0.2
Author URI: http://heaven.fr
*/

require_once('feed2post_utils.php');
require_once('feed2post_admin.php');

define('FEED2POST_TABLENAME', 'f2p_items');

// Init : create the BDD table
register_activation_hook('wp_feed2post/feed2post.php', 'feed2post_install');


add_action('admin_menu', 'feed2post_panel');

if (get_option('feed2post_auto'))
	add_action('wp_head', 'feed2post_autopublish');

?>