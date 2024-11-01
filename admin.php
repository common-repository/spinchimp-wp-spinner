<?php
/**
 * Functions related to the admin panel.
 */

wp_enqueue_script( 'post' );
add_action ('admin_menu', 'spinchimp_admin_menu');
add_action ( 'save_post', 'spinchimp_update_post_meta' );

function spinchimp_admin_menu () {
	$spinchimp_options = spinchimp_current_options();
	
	if ( empty($spinchimp_options['email'])) {
		add_menu_page( 'Spin Chimp', 'SpinChimp', 'activate_plugins', 'spinchimp_manager', 'spinchimp_api_settings' );
		add_submenu_page( 'spinchimp_manager', 'Settings', 'Settings', 'activate_plugins', 'spinchimp_manager', 'spinchimp_api_settings' );
	} else {
		add_menu_page( 'Spin Chimp', 'SpinChimp', 'activate_plugins', 'spinchimp_manager', 'spinchimp_api_settings' );
		add_submenu_page( 'spinchimp_manager', 'Settings', 'Settings', 'activate_plugins', 'spinchimp_manager', 'spinchimp_api_settings' );
		add_submenu_page( 'spinchimp_manager', 'SpinChimp Credentials', 'Credentials', 'activate_plugins', 'spinchimp_user_credentials', 'spinchimp_user_credentials' );
	}
}

add_action( 'add_meta_boxes', 'spinchimp_add_post_box' );
function spinchimp_add_post_box () {
	add_meta_box( 'spinchimp_sectionid', 'SpinChimp', 'spinchimp_inner_custom_box', 'post', 'side');
	add_meta_box( 'spinchimp_sectionid', 'SpinChimp', 'spinchimp_inner_custom_box', 'page', 'side');
}

add_action ( 'post_submitbox_misc_actions', 'spinchimp_add_option_to_post' );
function spinchimp_add_option_to_post () {
	$spinchimp_options = spinchimp_current_options();
	?><div style="margin: 10px">
		<input type="hidden" name="sent_by_editor" value="1" />
		<p><input type="checkbox" name="spin_content_on_publish" id="spin_content_on_publish" <?php if ( (int)$spinchimp_options['spin_content_on_publish']==1 ) echo 'checked="checked"'; ?> />&nbsp;Spin Article on Publish</p>
		<p><input type="checkbox" name="spin_title_on_publish" id="spin_title_on_publish" <?php if ( (int)$spinchimp_options['spin_title_on_publish']==1 ) echo 'checked="checked"'; ?> />&nbsp;Spin Title on Publish</p>
	</div><?php
}

add_filter ( 'wp_insert_post_data', 'spinchimp_pre_insert_post', 99 );
function spinchimp_pre_insert_post($data, $postarr=array()){
	
	$options = spinchimp_current_options();
	$content = "";
	$spin = FALSE;
	
	//$data['post_content'] = htmlentities(print_r ($data, TRUE));
	
	//This will happen if the post is sent by the editor...
	if ( isset( $_POST['sent_by_editor'] ) && !empty($data['post_content']) && $_POST['sent_by_editor'] == 1){
		if ( !empty( $_POST['spin_title_on_publish'] ) && $_POST['spin_title_on_publish']=="on" ) {
			$spin = TRUE;
			$content .= "[title]{$data['post_title']}[/title]\n";
		}
		if ( !empty( $_POST['spin_content_on_publish'] ) && $_POST['spin_content_on_publish']=="on" ) {
			$spin = TRUE;
			$content .= "[content]{$data['post_content']}[/content]";
		}
	} else {
		//This is not sent by the editor so it must be sent via wp_insert_post
		if ($options['use_this_on_other_plugins']==1 && !empty($data['post_content'])){
			if ($options['spin_title_on_publish'] == 1)
				$content .= "[title]{$data['post_title']}[/title]\n";
			if ($options['spin_content_on_publish'] == 1)
				$content .= "[content]{$data['post_content']}[/content]";
			$spin = TRUE;
		}
	}
	
	if ($spin && !empty($content)) {
		$content_arr = spinchimp_title_and_article_from_content ( spinchimp_spin_article ($content) );
		$data['post_title'] =  ( empty($content_arr['title']) ? $data['post_title'] : $content_arr['title'] );
		$data['post_content'] = ( empty($content_arr['content']) ? $data['post_content'] : $content_arr['content']);
	}
	return $data;
}

function spinchimp_user_credentials () {
	
	if ( isset( $_POST['submit']) ) {
		switch ($_POST['submit']) {
			case	"Update Spinchimp Settings"		:	
								$result = spinchimp_update_email_api ($_POST['email'], $_POST['apikey']);
								if ( !$result['result'] )
									$report = spinchimp_show_result ( "Error Found: " . $result['error'], "error" ); else
									$report = spinchimp_show_result ( "Updated email and apikey", "ok" );
								break;
			default: 
								break;
		}
	}
	spinchimp_menu_header("SpinChimp Credentials", "icon-user-edit"); ?>
	<?php if ( !empty( $report )) echo $report; ?>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
			<?php //spinchimp_show_stats (); ?>
			<div id="post-body">
				<div id="post-body-content">
					<?php
					spinchimp_api_form(TRUE);
					?>
				</div>
			</div>
	</div>
	<?php
}

function spinchimp_inner_custom_box () {
	?><script>
		jQuery(document).ready(function() {

			window.send_to_editor2 = function(html) {
				jQuery('#content').val(html);
				tb_remove();
			};
		});
		</script>
	<p><a href="<?php echo plugins_url( 'popup.php', __FILE__); ?>?TB_iframe=1&width=100&height=100" class="thickbox" title="SpinChimp Spinner" onclick="jQuery('#spin_content_on_publish').attr('checked', false);return false;">SpinChimp Article Spinner</a></p>
	<?php
}

function spinchimp_api_settings(){
	
	if ( isset( $_POST['submit']) ) {
		switch ($_POST['submit']) {
			case	"Update Spinchimp Settings"		:	
								
								$result = spinchimp_update_email_api ($_POST['email'], $_POST['apikey']);
								
								if ( !$result['result'] )
									$report = spinchimp_show_result ( "Error Found: " . $result['error'], "error" ); else
									$report = spinchimp_show_result ( "Updated email and apikey", "ok" );
								
								break;
								
			case	"Update Spinner Setting"		:
								
								$result = spinchimp_update_spinner_options ($_POST);
								
								if ( !$result['result'] )
									$report = spinchimp_show_result ( "Error Found: " . $result['error'], "error" ); else
									$report = spinchimp_show_result ( "Updated SpinChimp Spinner Settings", "ok" );
								
								break;
								
			case	"Update Publishing Options"			:
								
								if ( isset($_POST['spin_content_on_publish']) && $_POST['spin_content_on_publish'] == 'on' )
									$options['spin_content_on_publish'] = 1; else
									$options['spin_content_on_publish'] = 0;
								
								if ( isset($_POST['spin_title_on_publish']) && $_POST['spin_title_on_publish'] == 'on' )
									$options['spin_title_on_publish'] = 1; else
									$options['spin_title_on_publish'] = 0;
								
								if ( isset($_POST['use_this_on_other_plugins']) && $_POST['use_this_on_other_plugins'] == 'on' )
									$options['use_this_on_other_plugins'] = 1; else
									$options['use_this_on_other_plugins'] = 0;
									
								$result = spinchimp_update_post_options ($options);
								
								if ( !$result['result'] )
									$report = spinchimp_show_result ( "Error Found: " . $result['error'], "error" ); else
									$report = spinchimp_show_result ( "Updated SpinChimp Publish Settings", "ok" );
								
								break;
								
			case	"Import to Protected Terms"		:
								$arr = array();
								
								if ( !empty( $_POST['categories'] )) {
									foreach ( $_POST['categories'] as $category )
										$arr[] = $category;
								}
								
								if ( !empty( $_POST['tags'] )) {
									foreach ( $_POST['tags'] as $tag )
										$arr[] = $tag;
								}
								$result = spinchimp_save_protected_terms (spinchimp_parse_array_to_csv($arr));
								
								if ( !$result['result'] )
									$report = spinchimp_show_result ( "Error Found: " . $result['error'], "error" ); else
									$report = spinchimp_show_result ( "Protected Terms added to option", "ok" );
								
								break;
			
			case "Activate Automatic Rewrite" :
								$number_of_posts_per_call = (int)$_POST['number_of_posts_per_call'];
								$cron_recurrence = $_POST['cron_recurrence'];
								$result = spinchimp_activate_automatic_rewrite($number_of_posts_per_call, $cron_recurrence);
			
								if ( !$result )
									$report = spinchimp_show_result ( "Error Activating Automatic Rewrite", "error" ); else
									$report = spinchimp_show_result ( "Automatic Rewrite Activated", "ok" );
			
								break;
			
			case 'De-activate Automatic Rewrite' :
								$result = spinchimp_deactivate_automatic_rewrite();
								
								if ( !$result )
									$report = spinchimp_show_result ( "Error Activating Automatic Rewrite", "error" ); else
									$report = spinchimp_show_result ( "Automatic Rewrite Deactivated", "ok" );
			
								break;
			
			case "Rewrite Now"			:
								$result = spinchimp_pseudo_cron_procedure();
								
								if ( !$result )
									$report = spinchimp_show_result ( "All written articles are already spinned. No articles processed.", "error" ); else
									$report = spinchimp_show_result ( "Cron operation processed successfully", "ok" );
								break;
			default:
								break;
		}
	}
	$spinchimp_options = spinchimp_current_options();
	spinchimp_menu_header("SpinChimp Settings"); ?>
	<?php if ( !empty( $report )) echo $report; ?>
	
	<div id="poststuff" class="metabox-holder has-right-sidebar">
			<div id="side-info-column" class="inner-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
			<?php 
				
			if ( !empty($spinchimp_options['email']) ) {
				spinchimp_show_stats ();
				spinchimp_show_post_options_form ();
				spinchimp_import_tags_form ();
			}
			?>
				</div>
			</div>
			<div id="post-body">
				<div id="post-body-content">
					<?php
						if ( empty( $spinchimp_options['email'] ) ){
							spinchimp_api_form();
						} else {
							spinchimp_options_form();
						}
					?>
				</div>
			</div>
	</div>
	<?php
}

function spinchimp_api_form ($showvalues=FALSE) {
	if ($showvalues)
		$spinchimp_options = spinchimp_current_options();
	
	?><div id="linkform" class="stuffbox">
		<h3><label for="linkform">SpinChimp Settings</label></h3>
		<div class="inside">
			<table class='form-table'>
				<form name='settings' action='' method='post' >
					<?php
					wp_nonce_field( 'spinchimp_nonce', 'spinchimp_nonce');
					?>
					<tr><td style='text-align: center;' colspan='2'>To be able to use your WP-SpinChimp you need to enter your SpinChimp email and API key. If you don't have one yet, you can always get one <a href='http://spinchimp.com/' title='Get your SpinChimp account' target="_blank">here</a></td></tr>
					<tr><td style='text-align: right;'>Email Address:</td><td><input type='text' class='smalltext' name='email' value='<?php if ( $showvalues ) echo $spinchimp_options['email']; ?>' />&nbsp;</td></tr>
					<tr><td style='text-align: right;'>SpinChimp API key :</td><td><input type='text' class='smalltext' name='apikey' value='<?php if ( $showvalues ) echo $spinchimp_options['apikey']; ?>' />&nbsp;</td></tr>
					<tr><td style='text-align: right;'>&nbsp;</td><td><?php submit_button( "Update Spinchimp Settings", "primary", "submit"); ?></td></tr>	
				</form>
			</table>
		</div>
	</div>
	
	
	<?php
}

function spinchimp_options_form () {
	GLOBAL $qualities;
	GLOBAL $posmatches;
	GLOBAL $other_options;
	GLOBAL $api_option_description;
	
	$spinchimp_options = spinchimp_current_options();
	?><div id="linkform" class="stuffbox">
		<h3><label for="linkform">SpinChimp Spinner Settings</label></h3>
		<div class="inside">
			<table class='form-table'>
				<form name='settings' action='' method='post' >
					<?php
					wp_nonce_field( 'spinchimp_nonce', 'spinchimp_nonce');
					?>
					<tr><td style='text-align: right;'>Quality:</td><td><select class='smalltext' name='quality'>
					<?php
						foreach ($qualities as $quality=>$val){
							echo '<option value="' . $val . '"';
							if ( $spinchimp_options['quality'] == $val)
								echo ' selected="selected" ';
							echo '>' . $quality . "</option>\n";
						}
					?>
					</select>&nbsp;<a href="javascript:;" style="position: relative; top: 5px;" title="<?php echo $api_option_description["quality"]; ?>"><img src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" alt="help"  /></a></td></tr>
					<tr><td style='text-align: right;'>Part of Speech Match:</td><td>
					<select class='smalltext' name='posmatch'>
					<?php
						foreach ($posmatches as $posmatch=>$val){
							echo '<option value="' . $val . '"';
							if ( $spinchimp_options['posmatch'] == $val)
								echo ' selected="selected" ';
							echo '>' . $posmatch . "</option>\n";
						}
					?>
					</select>&nbsp;<a href="javascript:;" style="position: relative; top: 5px;" title="<?php echo $api_option_description["posmatch"]; ?>"><img src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" alt="help"  /></a>
					</td></tr>
					<tr><td style='text-align: right;'>Protected Terms: </td><td><input type="text" class="smalltext" value="<?php if ( !empty($spinchimp_options['protectedterms']) ) echo $spinchimp_options['protectedterms']; ?>" name="protectedterms" />
						&nbsp;<a href="javascript:;" style="position: relative; top: 5px;" title="<?php echo $api_option_description["protectedterms"]; ?>"><img src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" alt="help"  /></a>
					</td>
					</tr>
					<tr><td style='text-align: right;'>Protect Tags: </td><td><input type="text" class="smalltext" value="<?php if ( !empty($spinchimp_options['tagprotect']) ) echo $spinchimp_options['tagprotect']; ?>" name="tagprotect" />
						&nbsp;<a href="javascript:;" style="position: relative; top: 5px;" title="<?php echo $api_option_description["tagprotect"]; ?>"><img src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" alt="help"  /></a>
					</td>
					</tr>
					</tr>
					<tr><td style='text-align: right;'>Max Spin Depth: </td><td><select name="maxspindepth">
					<?php
						for ($count=0; $count<=5; $count++) {
							echo '<option value="' . $count . '"';
							if ( !empty($spinchimp_options['maxspindepth']) && $count== $spinchimp_options['maxspindepth']) echo ' selected="selected" ';
							echo ">{$count}</option>\n";
						}
					?>
					</select>
						&nbsp;<a href="javascript:;" style="position: relative; top: 5px;" title="<?php echo $api_option_description["maxspindepth"]; ?>"><img src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" alt="help"  /></a>
					</td>
					</tr>		
					<?php
						foreach ($other_options as $key=>$val){
							echo "<tr><td style='text-align: right;'>" . '&nbsp;</td><td><input type="checkbox" class="smalltext" value="'. $key . '" name="other_options[]" ';
							
							if ( $spinchimp_options[$key] == 1)
								echo 'checked="checked" ';
							echo '/>&nbsp;' . $val . '&nbsp;';
							echo '<a href="javascript:;" style="position: relative; top: 3px;" title="' . $api_option_description[$key] . '"><img src="' . plugins_url( 'images/help.png' , __FILE__ ) . '" alt="help"  /></a>';
							echo '</td></tr>' . "\n";	
						}
					?>
					<tr><td style='text-align: right;'>&nbsp;</td><td><?php submit_button( "Update Spinner Setting", "primary", "submit", True); ?></td></tr>	
				</form>
			</table>
		</div>
	</div>
	
	<div id="cronsettings" class="stuffbox">
		<h3><label for="cronsettings">SpinChimp Advanced Settings</label></h3>
		<div class="inside">
			<table class='form-table'>
				<form name='cron' action='' method='post' >
					<?php
					wp_nonce_field( 'spinchimp_nonce', 'spinchimp_nonce');
					if ($spinchimp_options['pseudo_cron_status']=='inactive')
						$status = ''; else
						$status = ' disabled="disabled" ';
					?>
					<tr><td style='text-align: right;'>Automatic Rewrite Posts Status:</td>
						<td><strong><?php echo ucfirst($spinchimp_options['pseudo_cron_status']); ?></strong></td>
					</tr>
					<tr><td style='text-align: right;'>Number of posts to spin per call :</td>
						<td><input type="text" name="number_of_posts_per_call" value="<?php if (isset($spinchimp_options['number_of_posts_per_call'])) echo $spinchimp_options['number_of_posts_per_call']; ?>" <?php echo $status; ?>/></td>
					</tr>
					<tr>
						<td style='text-align: right;'><label for="cron_recurrence">When are we going to process this?: </label></td>
						<td>
							<select name="cron_recurrence" <?php echo $status; ?>>
							<?php
							GLOBAL $cron_recurrences;
							foreach ($cron_recurrences as $key => $recurrence) {
								$val = '';
								if ( $spinchimp_options['cron_recurrence'] == $key)
									$val = ' selected="selected" '; else
									$val = '';
									
								echo "<option value=\"{$key}\" {$val}>{$recurrence}</option>\n";
							}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td style='text-align: right;'>&nbsp;</td>
						<?php 
							switch ($spinchimp_options['pseudo_cron_status']){
								case "inactive"		:
												$button = "Activate Automatic Rewrite";
												break;
								case "active"		:
												$button = "De-activate Automatic Rewrite";
												break;
							}
						?>
						<td><?php submit_button( $button, "primary", "submit", FALSE); ?>&nbsp;<?php submit_button( "Rewrite Now", "secondary", "submit", FALSE); ?></td>
					</tr>
					<tr>
						<td colspan="2">
							<p>You can also make calls to this script either manually by clicking: </p>
							<p><strong><a href="<?php echo plugins_url('spinchimp-wp-spinner/cron.php'); ?>" target="_blank" title="manually call the cron script"><?php echo plugins_url('spinchimp-wp-spinner/cron.php'); ?></a></strong></p>
							<p>or call the script via Cron (applicable only on Unix-like systems)</p>
							<p><strong><?php echo dirname(__FILE__) . '/cron.php'; ?></strong></p>
						</td>
					</tr>
				</form>
			</table>
		</div>
	</div>
	
	<?php
}


function spinchimp_show_stats () {
	GLOBAL $api_failure_codes;
	
	$spinchimp_options = spinchimp_current_options();
	$spinner = new SpinChimp ($spinchimp_options['email'], $spinchimp_options['apikey']);
	$stats = $spinner->QueryStats();
	?>		<div id="linksubmitdiv" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>SpinChimp Query Statistics</span></h3>
				<div class="inside">
					<div id="sidecontent">
						<div id="misc-publishing-actions">
						<?php
						if ( isset($stats["success"]) ) {
							if ( $stats["success"] ){
								foreach ($stats["result"] as $key=>$val) {
									echo '<div class="misc-pub-section">' . $key . ': <strong>' . $val . '</strong></div>' . "\n";
								}
							} else {
								if ( count( $stats["error"] ) > 1 ){
									foreach ($stats["error"] as $val) {
										if (array_key_exists($val, $api_failure_codes))
											echo '<div class="misc-pub-section">Error Found: <strong>' . $api_failure_codes[$val] . '</strong></div>' . "\n"; else
											echo '<div class="misc-pub-section">Error Found: <strong>' . $val . '</strong></div>' . "\n";
									}
								} else {
									echo '<div class="misc-pub-section">Error Found: <strong>' . $api_failure_codes[$stats["error"][0]] . '</strong></div>' . "\n";
								}
							}
						} else {
							echo '<div class="misc-pub-section">Error Found: <strong>Error connecting with Spinchimp API</strong></div>' . "\n";
						}
						?>
						<div id="major-publishing-actions">
							<form method="post" action=""><?php submit_button( "Refresh", "primary", "submit", false); ?></form>
						</div>
						</div>
					</div>
				</div>
			</div>
	<?php
}

function spinchimp_show_post_options_form () {
	$spinchimp_options = spinchimp_current_options();
	
	?><div id="post_options_form" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>Post/Page Publish Settings</span></h3>
				<div class="inside">
					<div id="sidecontent">
							<form method="post" action="">
						<div id="misc-publishing-actions">
							<p>Set your preferences when publishing new post or page. You may include posts or pages made by other plugins.</p>
							<p><input type="checkbox" name="spin_content_on_publish" <?php if ( (int)$spinchimp_options['spin_content_on_publish'] == 1 ) echo ' checked="checked" '; ?>/>&nbsp;Spin Content on Publish</p>
							<p><input type="checkbox" name="spin_title_on_publish" <?php if ( (int)$spinchimp_options['spin_title_on_publish'] == 1 ) echo ' checked="checked" '; ?>/>&nbsp;Spin Title on Publish</p>
							<p><input type="checkbox" name="use_this_on_other_plugins" <?php if ( (int)$spinchimp_options['use_this_on_other_plugins'] == 1 ) echo ' checked="checked" '; ?>/>&nbsp;Use Spinchimp when publishing contents using other plugins.</p>
						<div id="major-publishing-actions">
							<?php submit_button( "Update Publishing Options", "primary", "submit", false); ?></form>
						</div>
						</div>
					</div>
				</div>
			</div>
	
	
	<?php
	
}

function spinchimp_import_tags_form () {
	
	$categories = get_categories( array("exclude"=>"1") );
	$tags = get_tags();
	
	?><script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#category_pull_down').hide();
			jQuery('#tags_pull_down').hide();
			jQuery('#submit-tags-categories').hide();
			
			jQuery('#category_pull_down_link').click(function(){
				jQuery('#category_pull_down').show(100);
				jQuery('#category_pull_down_link').hide(100);
				jQuery('#submit-tags-categories').show(100);
				jQuery('#tags_pull_down').hide(100);
				jQuery('#tags_pull_down_link').show(100);
			});
			
			jQuery('#tags_pull_down_link').click(function(){
				jQuery('#tags_pull_down').show(100);
				jQuery('#tags_pull_down_link').hide(100);
				jQuery('#submit-tags-categories').show(100);
				jQuery('#category_pull_down').hide(100);
				jQuery('#category_pull_down_link').show(100);
			});
			
			jQuery("#check_all_category").live("click",function(event){
			
				if(jQuery("#check_all_category").hasClass('not_checked')){
					jQuery("#check_all_category").removeClass('not_checked');
					jQuery(".category-box").attr('checked',true);
				} else {
					jQuery("#check_all_category").addClass('not_checked');
					jQuery(".category-box").attr('checked',false);
				}
				
			});
			
			jQuery("#check_all_tags").live("click",function(event){
			
				if(jQuery("#check_all_tags").hasClass('not_checked')){
					jQuery("#check_all_tags").removeClass('not_checked');
					jQuery(".tag-box").attr('checked',true);
				} else {
					jQuery("#check_all_tags").addClass('not_checked');
					jQuery(".tag-box").attr('checked',false);
				}
				
			});
		});
	</script>
	<div id="import_tags_form" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>Import to Protected Terms</span></h3>
				<div class="inside">
					<div id="sidecontent">
							<form method="post" action="">
						<div id="misc-publishing-actions">
							<p>Import to the protected terms your Categories or Tags by clicking on the links below</p>
							<div id="category_pull_down_link"><p><a href="javascript:func();" class="edit-post-status hide-if-no-js" style="display: inline; ">Import Wordpress Categories as Protected Terms</a></p></div>
							<div id="category_pull_down">
								<p><input type="checkbox" class="check-box not_checked" id="check_all_category"/>&nbsp;Select/Deselect All Categories</p>
							<?php
								foreach ($categories as $category) {
									echo '<p><input type="checkbox" name="categories[]" class="category-box" value="'. $category->cat_name . '" />&nbsp;' . $category->cat_name . "</p>\n";
								}
							?>
							</div>
							<div id="tags_pull_down_link"><p><a href="javascript:func();" class="edit-post-status hide-if-no-js" style="display: inline; ">Import Wordpress Tags as Protected Terms</a></p></div>
							<div id="tags_pull_down">
								<p><input type="checkbox" class="check-box-tag not_checked" id="check_all_tags"/>&nbsp;Select/Deselect All Categories</p>
							<?php
								foreach ($tags as $tag) {
									echo '<p><input type="checkbox" name="tags[]" class="tag-box" value="'. $tag->name . '" />&nbsp;' . $tag->name . "</p>\n";
								}
							?>
							</div>
						<div id="submit-tags-categories">
							<?php submit_button( "Import to Protected Terms", "primary", "submit", false); ?></form>
						</div>
						</div>
					</div>
				</div>
			</div>
	
	
	<?php
}

function spinchimp_show_result ($result, $state="ok", $return=TRUE){
		
	if ( $state=="ok" )
		$report = "<div id=\"message\" class=\"updated fade\"><p>{$result}</p></div>"; else
		$report = "<div id=\"message\" class=\"error fade\"><p>{$result}</p></div>";
	
	if ( $return )
		return $report; else
		echo $report;
}

function spinchimp_menu_header ($title, $icon="icon-options-general") {
	?>
	<div class='wrap'>
	<div id="<?php echo $icon; ?>" class="icon32"></div> 
	<h2><?php echo $title; ?></h2>
	<?php
}

add_action( 'admin_notices', 'spinchimp_admin_notification' );
function spinchimp_admin_notification () {
	$spinchimp_options = spinchimp_current_options();
	
	if ( empty( $spinchimp_options['email'] )) {
		spinchimp_show_result ( WP_SPINCHIMP_NO_EMAIL_WARNING, "error", FALSE);
	}
}


