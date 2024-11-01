<?php
/*
Plugin Name: WP-SpinChimp
Plugin URI: http://spinchimp.com/
Description:  Generate unique content with automatic spinning with the SpinChimp API.
Version: 1.1.0
Author: AkturaTech.com
Author URI: http://akturatech.com/
*/

include_once ( dirname(__FILE__) . "/config.php" );

if ( is_admin() )
	include_once ( dirname(__FILE__) . "/admin.php" );

register_activation_hook( __FILE__,  'spinchimp_install' );	
register_deactivation_hook( __FILE__, 'spinchimp_deactivate_plugin' );
