<?php
	header("Content-Type: image/jpeg");
	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	require_once( $parse_uri[0] . 'wp-load.php' );

	// Request data from Jquery Ajax
	$requestOptions = (isset($_REQUEST['BDOptions']))? $_REQUEST['BDOptions'] : '';
	$requestInstallationEnded = (isset($_REQUEST['InstallationEnded']))? $_REQUEST['InstallationEnded'] : '';

	// Installation status
	$bdwp_workflow = get_option('bdwp_workflow');

	// Disable login form
	if (!empty($requestOptions) && ArrayKeyExistsCheck('bdphplib_is_installing', $bdwp_workflow) && $bdwp_workflow['bdphplib_is_installing'] == true) {

		$botdetect_options = (array)json_decode(stripslashes($requestOptions));
		DisableCaptchaForm($botdetect_options, 'on_login', false);
		InstallationEndedLibrary();
		echo json_encode(array('status' => 'OK'));
	}

	// Installation BotDetect Captcha Library ended
	if (!empty($requestInstallationEnded) && $requestInstallationEnded == 'ended') {
		InstallationEndedLibrary();
	}

	function InstallationEndedLibrary() {
		update_option('bdwp_workflow', '');
	}

	function DisableCaptchaForm($p_Options, $p_Key, $p_Value) {
		$p_Options[$p_Key] = $p_Value;
		update_option('botdetect_options', $p_Options);
	}

	function ArrayKeyExistsCheck($p_Key, $p_Args) {
    	if (!is_array($p_Args)) return false;
    	if (!array_key_exists($p_Key, $p_Args)) return false;
    	return true;
    }
?>