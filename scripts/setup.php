<?php
# Prepare Reporting
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

# Load
if ( empty($Application) ) {
	# Bootstrap
	$run = $bootstrap = false;
	require_once(dirname(__FILE__).'/../index.php');
}

# Bootstrap
$Application->bootstrap('balphp');
$Application->bootstrap('balcms'); // required for messages

# App
Bal_App::getInstance($Application)->setup();
die;
