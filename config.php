<?php
/* SpinChimp Constants */
define ( "WP_SPINCHIMP_PLUGIN_VERSION", "1.0.1" );
define ( "WP_SPINCHIMP_APP_ID", "wp_spinchimp" );
define ( "WP_SPINCHIMP_NO_EMAIL_WARNING", 'To be able to use your WP-SpinChimp you need to enter your SpinChimp email and API key. Click <a href="admin.php?page=spinchimp_manager">here</a> to enter your API credentials. If you don\'t have credentials yet, you can get one <a href="http://spinchimp.com/" title="Get your SpinChimp account" target="_blank">here</a>.' );
define ( "WP_SPINCHIMP_ADMIN_INCLUDE", ABSPATH . 'wp-admin/includes/' );

/* SpinChimp API values */
$qualities = array (
				"Best"		=> 	5,
				"Better" 	=>	4,
				"Good"		=>	3,
				"Average"	=>	2,
				"All"		=>	1,
				);

$posmatches = array (
				"Full Spin"	=>	4,
				"Full"		=>	3,
				"Loose"		=>	2,
				"Extremely Loose"	=>	1,
				"None"		=>	0,
				);

$other_options = array (
				"phraseignorequality"		=>	"Ignore Quality of Phrases",
				"spintidy"					=>	"Run Spin Tidy",
				"spinwithinspin"			=>	"Spin Within Spin",
				"spinwithinhtml"			=>	"Spin Within HTML",
				"applyinstantunique"		=>	"Apply Instant Unique",
				"fullcharset"				=>	"Full Charset",
				);

$api_option_description = array (
				"spin_content_on_publish"	=>	"When checked will spin article before publishing to wordpress.",
				"spin_title_on_publish"		=>	"When checked will include title for spinning.",
				"quality"					=>	"Spin Quality",
				"posmatch"					=>	"Required Part of Speech (POS) match for a spin",
				"protectedterms"			=>	"Comma separated list of words or phrases to protect from spin i.e. ‘my main keyword,my second keyword’. Alternately, you can import your Categories or Tags by using the 'Import to Protected Terms' section on the right.",
				"tagprotect"				=>	"Protects anything between any syntax you define. Separate start and end syntax with a pipe ‘|’ and separate multiple tags with a comma ‘,’. For example, you could protect anything in square brackets by setting tagprotect=[|]. You could also protect anything between “begin” and “end” by setting tagprotect=[|],begin|end",
				"phraseignorequality"		=> 	"If checked, quality is ignored when finding phrase replacements for phrases. This results in a huge amount of spin, but quality can vary.",
				"spintidy"					=>	"(Extra quota cost) Runs a spin tidy pass over the result article. This fixes any common a/an type grammar mistakes and repeated words due to phrase spinning. Generally increases the quality of the article. Costs one extra query.",
				"spinwithinspin"			=>	"If checked and there is existing spin syntax in the content you send up, the API will spin any relevant content inside this syntax. If unchecked, the API will skip over this content and only spin outside of existing syntax.",
				"spinwithinhtml"			=>	"Spin inside HTML tags. This includes <p> tags, for example if you send up “<p>Here is a paragraph</p>”, nothing would be spun unless this is checked.",
				"applyinstantunique"		=>	"(Extra quota cost) Runs an instant unique pass over the article once spun. This replaces letters with characters that look like the original letter but have a different UTF8 value, passing copyscape 100% but garbling content to the search engines. It it recommended to protect keywords while using instant unique. Costs one extra query.",
				"fullcharset"				=>	"Only used if applyinstantunique is checked. This causes IU to use the full character set which has a broader range of replacements.",
				"maxspindepth"				=>	"Define a maximum spin level depth in returned article. If set to 1, no nested spin will appear in the spun result. This paramater only matters if rewrite is false. Set to 0 or ignore for no limit on spin depth.",
				);
				
$api_failure_codes = array (
				"InvalidEmail" 			=> "Invalid Email",
				"SubscriptionExpired" 	=> "Subscription Expired",
				"InvalidAPIKey" 		=> "Invalid API Key",
				"NotAPIRegistered" 		=> "Not API Registered",
				"MaxQueriesReached" 	=> "Maximum Queries Reached",
				"DatabaseFailure" 		=> "Database Failure",
				);

/* default pseudo cron recurrences and their definitions */
$cron_recurrences = array (
				'hourly'	 => 'Every Hour',
				'twicedaily' => 'Twice Daily',
				'daily'		=> 'Daily',
				);
				
require_once ( dirname ( __FILE__ ) . "/functions.php" );
require_once ( dirname ( __FILE__ ) . "/spinchimp.class.php" );
