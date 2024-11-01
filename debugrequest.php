<?php
include_once ( dirname(__FILE__) . "/spinchimp.class.php" );
$spinner = new SpinChimp('testemail@somedomain.com', 'afakeAPIkey');
$spinner->setSpinQuality(4);
$spinner->setPOSMatch(3);
$spinner->setProtectedTerms ('term1,term2,term3');
$spinner->setTagProtect ('[|]');
$spinner->setMaxSpinDepth (3);
$result = $spinner->PrintRequest();
?>