<?php

function spinchimp_default_options () {
	$spinchimp_default = array(
				"spinchimp_version"			=>		WP_SPINCHIMP_PLUGIN_VERSION,
				"spin_content_on_publish"	=>		1,
				"spin_title_on_publish"		=>		0,
				"use_this_on_other_plugins" =>		0,
				"email"						=>		"",
				"apikey"					=>		"",
				"quality"					=>		4,
				"posmatch"					=>		3,
				"protectedterms"			=>		"",
				"rewrite"					=>		1,
				"phraseignorequality"		=>		0,
				"spinwithinspin"			=>		0,
				"spinwithinhtml"			=>		1,
				"applyinstantunique"		=>		0,
				"fullcharset"				=>		0,
				"spintidy"					=>		0,
				"tagprotect"				=>		"",
				"maxspindepth"				=>		0,
				//added on version 1.0.1
				"number_of_posts_per_call"	=>		5,
				"cron_recurrence"			=>		'hourly',
				"pseudo_cron_status"		=>		'inactive',
				);
	return $spinchimp_default;
}

function spinchimp_current_options () {
	$options = get_option ( "spinchimp_options", spinchimp_default_options() );
	return $options;
}

function spinchimp_update_options ($options) {
	return update_option ( "spinchimp_options", $options ); 
}

function spinchimp_update_email_api ($email, $apikey){
	GLOBAL $api_failure_codes;
	
	if ( !wp_verify_nonce( $_POST['spinchimp_nonce'], "spinchimp_nonce" ) )
       return array("result"=>false, "error"=>"NONCE ERROR");
	
	$spinner = new SpinChimp ( $email, $apikey );
	$auth = $spinner->QueryStats ();
	
	if ( !$auth["success"] ) {
		if ( count($auth["error"]) > 1)
			$error = $api_failure_codes[$auth["error"][0]]; else
			$error = $api_failure_codes[$auth["error"][0]];
		return array( "result"=>false, "error"=>$error );
	}
	
	$spinchimp_options = get_option ( "spinchimp_options" );
	$spinchimp_options['email'] = $email;
	$spinchimp_options['apikey'] = $apikey;
	if (spinchimp_update_options ( $spinchimp_options ))
		return array("result"=>true); else
		return array("result"=>false, "error"=>"Email and API key not changed.");
}

function spinchimp_update_spinner_options ($post){
	GLOBAL $other_options;
	
	if ( !wp_verify_nonce( $_POST['spinchimp_nonce'], "spinchimp_nonce" ) )
       return array("result"=>false, "error"=>"NONCE ERROR");
    
    $spinchimp_options = get_option ( "spinchimp_options" );
    
    $spinchimp_options['quality'] = (int)$post['quality'];
    $spinchimp_options['posmatch'] = (int)$post['posmatch'];
    $spinchimp_options['maxspindepth'] = (int)$post['maxspindepth'];
    $spinchimp_options['protectedterms'] = trim(str_replace(", ", ",", $post['protectedterms']));
    $spinchimp_options['tagprotect'] = trim($post['tagprotect']);
    
    foreach ($other_options as $key => $val) {
		$spinchimp_options[$key] = 0;
	}
	
    foreach ($post['other_options'] as $val){
		$spinchimp_options[$val] = 1;
	}
	
	if (spinchimp_update_options ( $spinchimp_options ))
		return array("result"=>true); else
		return array("result"=>false, "error"=>"Error updating options");
}



function spinchimp_update_post_options ($options){
	
    $spinchimp_options = get_option ( "spinchimp_options" );
    
    $spinchimp_options['spin_content_on_publish'] = (int)$options['spin_content_on_publish'];
    $spinchimp_options['spin_title_on_publish'] = (int)$options['spin_title_on_publish'];
    $spinchimp_options['use_this_on_other_plugins'] = (int)$options['use_this_on_other_plugins'];
    
	if (spinchimp_update_options ( $spinchimp_options ))
		return array("result"=>true); else
		return array("result"=>false, "error"=>"Error updating publish options");
}

function spinchimp_install (){
	$spinchimp_default = spinchimp_default_options();
	$spinchimp_options = get_option ( "spinchimp_options", $spinchimp_default );
	
	if ( $spinchimp_options['spinchimp_version'] != WP_SPINCHIMP_PLUGIN_VERSION ) {
		if ( $spinchimp_options['spinchimp_version'] == "1.0.0" ) {
			//new on version 1.0.1...
			$spinchimp_options['use_this_on_other_plugins'] = $spinchimp_default['use_this_on_other_plugins'];
			$spinchimp_options['number_of_posts_per_call']	= 5;
			$spinchimp_options['cron_recurrence']			= 'hourly';
			$spinchimp_options['pseudo_cron_status']		= 'inactive';
			//end
		}
		$spinchimp_options['spinchimp_version'] = WP_SPINCHIMP_PLUGIN_VERSION;
	}
	update_option ( "spinchimp_options", $spinchimp_options);
}

//spin article based on the stored spinchimp options...
function spinchimp_spin_article ($content) {
	GLOBAL $other_options;
	GLOBAL $api_failure_codes;
	
	$original_content = $content;
	$spinchimp_options = spinchimp_current_options();
	
	if ( !empty($content) ){
		$spinner = new SpinChimp($spinchimp_options['email'], $spinchimp_options['apikey']);
		$spinner->setSpinQuality((int)$spinchimp_options['quality']);
		$spinner->setPOSMatch((int)$spinchimp_options['posmatch']);
		
		if ( !empty($spinchimp_options['protectedterms']) )
			$spinner->setProtectedTerms ( $spinchimp_options['protectedterms'] );
		
		if ( !empty($spinchimp_options['tagprotect']) )
			$spinner->setTagProtect ( $spinchimp_options['tagprotect'] );
		
		if ( !empty($spinchimp_options['maxspindepth']) )
			$spinner->setMaxSpinDepth ( $spinchimp_options['maxspindepth'] );
			
		foreach ( $spinchimp_options as $key => $val){
			
			if ( array_key_exists( $key, $other_options ) && ( int )$val==1)
				$spinner->setParam($key, 1);
				
		}
		$result = $spinner->GlobalSpin($content, 1);
		
		if ( $result["success"] ) {
			$content = $result["result"]; 
		} else {
			$content = "Errors were encountered while spinning the article. Error(s) found: \n\n" . $original_content;
			foreach ( $result["error"] as $error ) {
				if (array_key_exists($error, $api_failure_codes))
					$content .= " " . $api_failure_codes[$error] . "."; else
					$content .= " {$error}.";
			}
		}	
	}
	return $content;
}

function spinchimp_save_protected_terms ($protectedterms) {
	$spinchimp_options = spinchimp_current_options();
	
	$spinchimp_options['protectedterms'] = ltrim($protectedterms, ",");
	$success = spinchimp_update_options($spinchimp_options);
	
	if ($success)
		return array("result"=>true); else
		return array("result"=>false, "error"=>"Error saving protected terms");
}

function spinchimp_parse_array_to_csv ($arr) {
	$spinchimp_options = spinchimp_current_options ();
	$current_protected_terms = explode (",", $spinchimp_options['protectedterms']);
	
	foreach ($arr as $val) {
		if ( !in_array( $val, $current_protected_terms ))
			$current_protected_terms[] = $val;
	}
	return implode(",", $current_protected_terms);
}

function spinchimp_title_and_article_from_content ($content){
	
	$title = "";
	$article = "";
	if (preg_match ('#\[title\](.*?)\[/title\]#', $content, $result))
		$title = (!empty($result[1]) ? $result[1] : "");
	
	if (preg_match ('#\[content\]([\s\S]+)\[/content\]#', $content, $result2))
		$article = (!empty($result2[1]) ? $result2[1] : "");
		
	return array(
				'title' => $title,
				'content' => $article,
				);
}

function spinchimp_post_is_already_spinned ($postid){
	
	$meta = get_post_meta ($postid, 'spinchimp_spinned');
	return $meta[0];
}

function spinchimp_fetch_articles_not_spinned ($limit=5, $post_type='post'){
	GLOBAL $wpdb;
	//we go directly to the mysql query... for faster queries...
	$sql = "
		SELECT
			ID, post_content, post_title
		FROM
			`" . $wpdb->prefix . "posts`
		WHERE 
			id
		NOT IN
			( 
			SELECT 
				post_id FROM `" . $wpdb->prefix . "postmeta`
			WHERE 
				`meta_key`='spinchimp_spinned'
			AND
				`meta_value`='1'
			)
		AND
			post_status='publish'
		AND
			post_type='{$post_type}'
		ORDER BY
			id DESC
		LIMIT
			{$limit}";
	
	return $wpdb->get_results($sql);
			
}

//cron procedure...
//echo results with newlines. Is expected to be viewable via email.
function spinchimp_cron_procedure (){
	$spinchimp_options = spinchimp_current_options();
	
	$txt = "Loading Cron Job: WP-Spinchimp V. " . WP_SPINCHIMP_PLUGIN_VERSION . "\n";
	$txt .= "====================================\n";
	$txt .= "Fetching articles to process\n";
	$articles_to_process = spinchimp_fetch_articles_not_spinned( $spinchimp_options['number_of_posts_per_call'] );
	
	if (is_array($articles_to_process) && count( $articles_to_process ) > 0 ){
		$txt .= "Fetched " . count($articles_to_process) . " articles...\n";
		
		foreach ($articles_to_process as $article){
			$txt .= "Processing {$article->post_title}...\n";
			$content = "[title]{$article->post_title}[/title]\n[content]{$article->post_content}[/content]";
			$content_arr = spinchimp_title_and_article_from_content ( spinchimp_spin_article ($content) );
			
			$post_title =  ( empty($content_arr['title']) ? "" : $content_arr['title'] );
			$post_content = ( empty($content_arr['content']) ? "" : $content_arr['content']);
			$txt .= "Article spinned... \n";
			spinchimp_update_post ($article->ID, $post_title, $post_content);
			spinchimp_update_post_meta ($article->ID);
		}
	} else {
		$txt .= "Fetched 0 articles... \n";
	}
	$txt .= "Done Processing... Will resume next call...\n";
	echo $txt;
}

//pseudo cron procedure...
//does not echo results...
function spinchimp_pseudo_cron_procedure(){
	$spinchimp_options = spinchimp_current_options();
	
	$articles_to_process = spinchimp_fetch_articles_not_spinned( $spinchimp_options['number_of_posts_per_call'] );
	
	if (is_array($articles_to_process) && count( $articles_to_process ) > 0 ){
		foreach ($articles_to_process as $article){
			$content = "[title]{$article->post_title}[/title]\n[content]{$article->post_content}[/content]";
			$content_arr = spinchimp_title_and_article_from_content ( spinchimp_spin_article ($content) );
			
			$post_title =  ( empty($content_arr['title']) ? "" : $content_arr['title'] );
			$post_content = ( empty($content_arr['content']) ? "" : $content_arr['content']);
			
			spinchimp_update_post ($article->ID, $post_title, $post_content);
			spinchimp_update_post_meta ($article->ID);
		}
		return TRUE;
	} else {
		return FALSE;
	}
}

function spinchimp_deactivate_plugin (){
	
	$spinchimp_options = spinchimp_current_options ();
	$spinchimp_options['pseudo_cron_status'] = 'inactive';
	spinchimp_update_options ($spinchimp_options);
	
	//we should deactivate the pseudo cron each time we deactivate this plugin
	//else it will continue calling the cron even if not activated...
	wp_clear_scheduled_hook('spinchimp_pseudo_cron');
}

add_action ( 'spinchimp_pseudo_cron', 'spinchimp_pseudo_cron_procedure' );
function spinchimp_activate_automatic_rewrite($number_of_posts_per_call=5, $cron_recurrence='hourly'){
	$spinchimp_options = spinchimp_current_options ();
	$spinchimp_options['pseudo_cron_status'] = 'active';
	$spinchimp_options['number_of_posts_per_call'] = $number_of_posts_per_call;
	$spinchimp_options['cron_recurrence'] = $cron_recurrence;
	spinchimp_update_options ($spinchimp_options);
	
	wp_schedule_event(time(), $spinchimp_options['cron_recurrence'], 'spinchimp_pseudo_cron');
	return TRUE;
}

function spinchimp_deactivate_automatic_rewrite(){
	$spinchimp_options = spinchimp_current_options ();
	$spinchimp_options['pseudo_cron_status'] = 'inactive';
	
	wp_clear_scheduled_hook('spinchimp_pseudo_cron');
	spinchimp_update_options ($spinchimp_options);
	
	return TRUE;
}

function spinchimp_update_post ($id, $title, $content){
	GLOBAL $wpdb;
	//we go directly to the dB else we'll be hooked by the filter...
	$wpdb->update( $wpdb->prefix . "posts", 
					array(
							'post_title'=>$title, 
							'post_content'=>$content
						), 
					array(
							'ID'=>$id
						) 
				); 
}

function spinchimp_update_post_meta ($postid) {
	//We're going to mark a post as either spinned or not...
	$options = spinchimp_current_options();
	$spinned = FALSE;
	if ( isset( $_POST['sent_by_editor'] ) && $_POST['sent_by_editor'] == 1){
		if ( !empty( $_POST['spin_title_on_publish'] ) && $_POST['spin_title_on_publish']=="on" )
			$spinned = TRUE;
		if ( !empty( $_POST['spin_content_on_publish'] ) && $_POST['spin_content_on_publish']=="on" )
			$spinned = TRUE;
	} else {
		if ($options['use_this_on_other_plugins']==1){
			if ($options['spin_title_on_publish'] == 1)
				$spinned=TRUE;
			if ($options['spin_content_on_publish'] == 1)
				$spinned = TRUE;
		}
	}
	update_post_meta ( $postid, 'spinchimp_spinned', $spinned );
}
