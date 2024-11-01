<?php
/**
 * Thickbox popup so user can spin article before sending to text editor.
 *
 * @package wp-spinchimp 
 */

/** Load WordPress Administration Bootstrap to prevent XSS*/
require_once( '../../../wp-admin/admin.php' );

if ( !current_user_can('publish_posts') )
	wp_die(__('You do not have permission to publish posts.') );

include_once ( "config.php" );

function spinchimp_header (){
	_wp_admin_html_begin();
	?><title><?php bloginfo('name') ?> &rsaquo; <?php _e('SpinChimp Spinner'); ?> &#8212; <?php _e('WordPress'); ?></title>
		<style>
			h3 {
				font-family: Georgia,"Times New Roman",Times,serif;
				font-size: 16px;
				font-weight: bold;
				color: #5A5A5A;
			}
			body {
				background-color: #F6F6F6;
				font-family: sans-serif;
				font-size: 12px;
				line-height: 1.4em;
			}
			#body {
				margin: 20px;
			}
			#container {
				width: 100%;
				height: 150px;
				border: 1px solid #D1D1D1;
				cursor: move;
				-webkit-border-top-left-radius: 3px;
				-webkit-border-top-right-radius: 3px;
				-webkit-border-bottom-left-radius: 3px;
				-webkit-border-bottom-right-radius: 3px;
				border-top-left-radius: 3px;
				border-top-right-radius: 3px;
				border-bottom-left-radius: 3px;
				border-bottom-right-radius: 3px;
			}
			table, th, td {
				border: 0px;
			}
			td {
				width: 200px;
			}
			#advanced_options_link {
				text-decoration: underline;
				color: #21759B;
			}
			#advanced_options_link2 {
				text-decoration: underline;
				color: #21759B;
			}
			.button-primary {
				min-width: 80px;
				text-align: center;
				border-color: #298CBA;
				font-weight: bold;
				color: white;
				background: #21759B;
				text-shadow: rgba(0, 0, 0, 0.3) 0 -1px 0;
				border-radius: 11px;
			}
		</style>
		<?php
		do_action('admin_head');
		?>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script>
			$(document).ready(function(){
				$('#advanced_options_content').hide();
				$('#advanced_options_link2').hide();
				
				$('#advanced_options_link').mouseover(function(){
					$('#advanced_options_link').css("color","#D54E21");
				});
				$('#advanced_options_link').hover(function(){
					$(this).css("cursor", "hand");
				});
				
				$('#advanced_options_link').mouseout(function(){
					$('#advanced_options_link').css("color","#21759B");
				});
				
				$('#advanced_options_link').click(function(){
					$('#advanced_options_link').hide();
					$('#advanced_options_link2').show(200);
					$('#advanced_options_content').show();
				});
				
				$('#advanced_options_link2').click(function(){
					$('#advanced_options_link2').hide();
					$('#advanced_options_content').hide();
					$('#advanced_options_link').show(200);
				});
				
				$('#advanced_options_link2').mouseover(function(){
					$('#advanced_options_link2').css("color","#D54E21");
				});
				$('#advanced_options_link2').hover(function(){
					$(this).css("cursor", "hand");
				});
				
				$('#advanced_options_link2').mouseout(function(){
					$('#advanced_options_link2').css("color","#21759B");
				});
			});
		</script>
		</head>
	<body<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?> class="no-js">
<?php
}

function spinchimp_footer (){
	?>
	</body>
</html>
	<?php
}

function spinchimp_first_page (){
	GLOBAL $qualities;
	GLOBAL $posmatches;
	GLOBAL $other_options;
	$spinchimp_options = spinchimp_current_options();
	
	spinchimp_header ();
	?><div id="body">
	<h3>Place the Article to spin here...</h3>
	<form method="post" action="">
		<textarea id="container" name="container"></textarea>
		<table>
			<tr>
				<td>Spin Quality:</td>
				<td><select name="quality">
				<?php
					foreach ($qualities as $quality=>$val){
						echo '<option value="' . $val . '"';
						if ( $spinchimp_options['quality'] == $val)
							echo ' selected="selected" ';
						echo '>' . $quality . "</option>\n";
					}
				?></select>
				</td>
			</tr>
			<tr>
				<td>Part of Speech Match:</td>
				<td><select name="posmatch">
				<?php
					foreach ($posmatches as $posmatch=>$val){
						echo '<option value="' . $val . '"';
						if ( $spinchimp_options['posmatch'] == $val)
							echo ' selected="selected" ';
						echo '>' . $posmatch . "</option>\n";
					}
				?></select>
				</td>
			</tr>
			<?php
					$count = 0;
					
					foreach ($other_options as $key=>$val){
						if ($count < 2) {
							echo "<td style='text-align: right;'>" . '&nbsp;</td><td><input type="checkbox" class="smalltext" value="'. $key . '" name="other_options[]" ';
							if ( $spinchimp_options[$key] == 1)
								echo 'checked="checked" ';
							echo '/>&nbsp;' . $val . '</td></tr>' . "\n";
						}
						if ($count == 2) {
							echo '<tr id="advanced_options_link"><td>&nbsp;</td><td><a href="javascript:function()">Show Advanced Options</a></td></tr>' . "\n";
							echo '<tr id="advanced_options_content"><td>&nbsp;</td><td>' . "\n";
						}
						if ($count > 2) {
							echo '<input type="checkbox" class="smalltext" value="'. $key . '" name="other_options[]" ';
							if ( $spinchimp_options[$key] == 1)
								echo 'checked="checked" ';
							echo '/>&nbsp;' . $val . '<br />' . "\n";
						}
						$count++;
					}
					echo "</tr>";
					echo '<tr id="advanced_options_link2"><td>&nbsp;</td><td><a href="javascript:function()" >Hide Advanced Options</a></td></tr>' . "\n";
			?>
			<tr><td>&nbsp;</td><td><?php submit_button( "Spin and Send to Editor", "primary", "submit"); ?></td></tr>
		</table>
	</form>
	</div>
	
	<?php
	spinchimp_footer();
}

function spinchimp_second_page(){
	GLOBAL $api_failure_codes;
	
	$spinchimp_options = spinchimp_current_options ();
	$content = trim($_POST["container"]);
	if ( !empty($content) ){
		$spinner = new SpinChimp($spinchimp_options['email'], $spinchimp_options['apikey']);
		$spinner->setSpinQuality((int)$_POST['quality']);
		$spinner->setPOSMatch((int)$_POST['posmatch']);
		
		if ( !empty($spinchimp_options['protectedterms']) )
			$spinner->setProtectedTerms ( $spinchimp_options['protectedterms'] );
		
		if ( !empty($spinchimp_options['tagprotect']) )
			$spinner->setTagProtect ( $spinchimp_options['tagprotect'] );
			
		$other_options = $_POST['other_options[]'];
		foreach ($other_options as $val){
			$spinner->setParam($val, 1);
		}
		$result = $spinner->GlobalSpin($content, 1);
		
		if ( $result["success"] ) {
			$content = $result["result"]; 
		} else {
			$content = "Errors were encountered while spinning the article. Error(s) found: ";
			foreach ( $result["error"] as $error ) {
				if (array_key_exists($error, $api_failure_codes))
					$content .= " " . $api_failure_codes[$error] . "."; else
					$content .= " {$error}.";
			}
		}	
	}
	spinchimp_send_to_editor (str_replace (array("\r\n", "\n"), '<br />', $content));
}

function spinchimp_send_to_editor ($content) {
?>
<script type="text/javascript">
/*<![CDATA[ */
var html = "<?php echo $content; ?>";

var win = window.dialogArguments || opener || parent || top;
win.send_to_editor(html);
win.send_to_editor2(html);
/* ]]> */
</script>
<?php
};
if ( !empty($_POST) && $_POST['submit']=="Spin and Send to Editor" ){
	add_action ( "show_second_page", "spinchimp_second_page");
	do_action ( "show_second_page" );
} else {
	add_action ( "show_first_page", "spinchimp_first_page");
	do_action("show_first_page");
}
