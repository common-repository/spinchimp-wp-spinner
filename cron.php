<?php

error_reporting ( FALSE );
//lets bootstrap WP to be able to use its functions
if(!function_exists('get_option'))  require_once('../../../wp-config.php');
	require_once ( dirname(__FILE__) . "/config.php" );

$spinchimp_default = array( 'spinchimp_options' => spinchimp_default_options());
$spinchimp_options = get_option ( "spinchimp_options", $spinchimp_default );

spinchimp_cron_procedure();
