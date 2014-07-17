<?php

/*  Copyright 2014  Captcha Inc. (email : development@captcha.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: BotDetect CAPTCHA
Plugin URI: http://captcha.com/doc/php/wordpress-captcha.html?utm_source=plugin&amp;utm_medium=wp&amp;utm_campaign=3.0.Beta3.3
Description: Adds BotDetect CAPTCHA to WordPress comments, login, registration, and lost password.
Version: 3.0.Beta3.3
Author: BotDetect CAPTCHA
Author URI: http://captcha.com?utm_source=plugin&amp;utm_medium=wp&amp;utm_campaign=3.0.Beta3.3
*/

/**
 * WordPress DB defaults & options
 */
if (!defined('__DIR__')) {define('__DIR__', dirname(__FILE__));}
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'PluginInfo.php' );
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'HttpHelpers.php' );
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'Localization.php' );
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'BackwardCompatibility.php' );

$LBD_WP_Defaults['generator'] = 'library';
$LBD_WP_Defaults['library_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
$LBD_WP_Defaults['library_assets_url'] = plugin_dir_url( __FILE__ ) . 'lib/botdetect/public/';
$LBD_WP_Defaults['service_api_key'] = '';
$LBD_WP_Defaults['on_login'] = true;
$LBD_WP_Defaults['on_comments'] = true;
$LBD_WP_Defaults['on_lost_password'] = true;
$LBD_WP_Defaults['on_registration'] = true;
$LBD_WP_Defaults['audio'] = true;
$LBD_WP_Defaults['image_width'] = 235;
$LBD_WP_Defaults['image_height'] = 50;
$LBD_WP_Defaults['min_code_length'] = 3;
$LBD_WP_Defaults['max_code_length'] = 5;
$LBD_WP_Defaults['helplink'] = 'image';
$LBD_WP_Defaults['remote'] = true;

// Copies data from previous version to latest version
BDWP_BackwardCompatibility::ResolveBackwardCompatibility();

$LBD_WP_Options = get_option('botdetect_options');
if (is_array($LBD_WP_Options)) {
	$LBD_WP_Options = array_merge($LBD_WP_Defaults, $LBD_WP_Options);
} else {
	$LBD_WP_Options = $LBD_WP_Defaults;
}

/**
 * In case of a local library generator, include the required library files and route the request.
 */
if ($LBD_WP_Options['generator'] == 'library' && is_file($LBD_WP_Options['library_path'] . 'botdetect/CaptchaIncludes.php')) {
	define('LBD_INCLUDE_PATH', $LBD_WP_Options['library_path'] . 'botdetect/');
	define('LBD_URL_ROOT', $LBD_WP_Options['library_assets_url']);

	require_once($LBD_WP_Options['library_path'] . 'botdetect/CaptchaIncludes.php');
	require_once($LBD_WP_Options['library_path'] . 'botdetect/CaptchaConfig.php');

	// Configure Botdetect with WP settings
	$LBD_CaptchaConfig = CaptchaConfiguration::GetSettings();
	$LBD_CaptchaConfig->HandlerUrl = home_url( '/' ) . 'index.php?botdetect_request=1'; //handle trough the WP stack
	$LBD_CaptchaConfig->ReloadIconUrl = $LBD_WP_Options['library_assets_url'] . 'lbd_reload_icon.gif';
	$LBD_CaptchaConfig->SoundIconUrl = $LBD_WP_Options['library_assets_url'] . 'lbd_sound_icon.gif';
	$LBD_CaptchaConfig->LayoutStylesheetUrl = $LBD_WP_Options['library_assets_url'] . 'lbd_layout.css';
	$LBD_CaptchaConfig->ScriptIncludeUrl = $LBD_WP_Options['library_assets_url'] . 'lbd_scripts.js';

	$LBD_CaptchaConfig->CodeLength = CaptchaRandomization::GetRandomCodeLength($LBD_WP_Options['min_code_length'], $LBD_WP_Options['max_code_length']);
	$LBD_CaptchaConfig->ImageWidth = $LBD_WP_Options['image_width'];
	$LBD_CaptchaConfig->ImageHeight = $LBD_WP_Options['image_height'];

	$LBD_CaptchaConfig->SoundEnabled = $LBD_WP_Options['audio'];
	$LBD_CaptchaConfig->RemoteScriptEnabled = $LBD_WP_Options['remote'];  

	switch ($LBD_WP_Options['helplink']) {
		case 'image':
			$LBD_CaptchaConfig->HelpLinkMode = HelpLinkMode::Image;
			break;

		case 'text':
			$LBD_CaptchaConfig->HelpLinkMode = HelpLinkMode::Text;
			break;

		case 'off':
			$LBD_CaptchaConfig->HelpLinkEnabled = false;
			break;

		default:
			$LBD_CaptchaConfig->HelpLinkMode = HelpLinkMode::Image;
			break;
	}

	// Route the request
	if (isset($_GET['botdetect_request']) && $_GET['botdetect_request']) {
	  // direct access, proceed as Captcha handler (serving images and sounds), terminates on output.
	  require_once(LBD_INCLUDE_PATH . 'CaptchaHandler.php');
	} else {
	  // included in another file, proceed as Captcha class (form helper)
	  require_once(LBD_INCLUDE_PATH . 'CaptchaClass.php');
	}
}

class WP_Botdetect_Plugin{
	public static $instance;
	var $options = array();
	var $is_solved = false;

	/**
	 * Init & setup hooks
	 */
	public function __construct($options) {
		self::$instance = $this;

		register_activation_hook(__FILE__, array('WP_Botdetect_Plugin', 'add_defaults'));
		register_uninstall_hook(__FILE__, array('WP_Botdetect_Plugin', 'delete_options'));

		$this->hook('admin_init', 'wp_version_requirement');
		$this->hook('init', 'init_sessions');

		// We don't want the captcha to appear for logged in users. -- Mario: changed
		// $this->hook('init', 'solve_if_logged_in');
		$this->hook('wp_logout', 'login_reset');

		// OPTIONS
		$this->options = $options;

		$this->hook('admin_menu', 'add_options_page');
		$this->hook('admin_init', 'register_setting');
		
		if ($this->is_bdwp_settings_page()) {
			$this->hook('admin_print_styles', 'bdwp_install_stylesheet');
			$this->hook('admin_print_scripts', 'jquery_library_scripts');
			$this->hook('admin_footer', 'bdwp_settings_scripts');
			$this->hook('admin_init', 'update_last_plugin_install');
		}

		// $this->hook('admin_init', 'install_library_automatically');
		$this->hook('admin_init', 'bdwp_plugin_redirect');

		// Localized
		BDWP_Localization::Init();

		// Show update message when detect the new version of BDWP plugin
		$this->bdwp_detect_new_version();

		add_filter( 'plugin_action_links', array($this, 'plugin_action_links'), 10, 2 );

		// GENERATOR NOTICES
		if (!$this->library_is_installed()) {
			$this->hook('admin_notices', 'captcha_library_missing_notice');
			return;
		}

		if ($this->options['generator'] == 'service') {
			$this->hook('admin_notices', 'captcha_service_notice');
			return;
		}

		$this->hook('init', 'register_scripts');

		// USE ON
		if ($this->options['on_login']) {
			$this->hook('login_head', 'login_head');
			$this->hook('login_form', 'login_form');
			$this->hook('authenticate', 'login_validate', 1);
		}

		if ($this->options['on_comments']) {
			$this->hook('wp_enqueue_scripts', 'comment_head');
			$this->hook('comment_form_after_fields', 'comment_form');
			$this->hook('comment_form_logged_in_after', 'comment_form'); // Mario 20131004
			$this->hook('pre_comment_on_post', 'comment_validate', 1);
			$this->hook('comment_post', 'comment_reset');
		}

		if ($this->options['on_lost_password']) {
			$this->hook('login_head', 'login_head');
			$this->hook('lostpassword_form', 'lost_password_form');
			$this->hook('lostpassword_post', 'lost_password_validate');
		}

		if ($this->options['on_registration']) {
			$this->hook('login_head', 'login_head');
			$this->hook('register_form', 'register_form');
			$this->hook('registration_errors', 'register_validation');
		}

	}

	public function init_sessions() {
		if (!session_id()) {
			session_start();
		}
	}

    public static function get_wordpress_version() {
        global $wp_version;
        return $wp_version;
    }

	public function solve_if_logged_in(){
		// We don't want the captcha to appear for logged in users.
	
	  // mario: always visible -- removed
	  // $this->is_solved = is_user_logged_in();
	}

	public function register_scripts(){
		wp_register_style( 'botdetect-captcha-style', CaptchaUrls::LayoutStylesheetUrl());
	}

	/**
	 * Show Captcha on login form
	 */
	public function login_form(){
		$this->show_captcha_form('login_captcha', 'login_captcha_field');
	}

	public function login_validate($user){
		if ($_POST){
			$isHuman = $this->validate_captcha('login_captcha', 'login_captcha_field');
			if(!$isHuman){
				if (!is_wp_error($user)) {
					$user = new WP_Error();
				}

				$user->add('captcha_fail', __('<strong>ERROR</strong>: Please retype the letters under the CAPTCHA image.', 'botdetect-wp-captcha'), 'BotDetect');
				remove_action('authenticate', 'wp_authenticate_username_password', 20);
				return $user;
			}
		}
	}

	public function login_reset(){
		$this->reset_captcha('login_captcha', 'login_captcha_field');
	}

	public function login_head(){
		wp_enqueue_style( 'botdetect-captcha-style' );
	}

	/**
	 * Show Captcha on comment form
	 */
	public function comment_form(){
		$this->show_captcha_form('comment_captcha', 'comment_captcha_field', array(
			'label' => __('Retype the characters', 'BotDetect'),
			'prepend' => '<p>',
			'append' => '</p>'
			));
	}
	public function comment_validate(){
		if ($_POST){
			$isHuman = $this->validate_captcha('comment_captcha', 'comment_captcha_field');
			if(!$isHuman){
				wp_die( __('<strong>ERROR</strong>: Please browser\'s back button and retype the letters under the CAPTCHA image.', 'botdetect-wp-captcha'), 'BotDetect');
			}
		}

			// Possible alternative to wp_die();
			// $location = empty($_POST['redirect_to']) ? get_comment_link($comment_id) : $_POST['redirect_to'] . '#comment-' . $comment_id;
			// $location = apply_filters('comment_post_redirect', $location, $comment);

			// wp_safe_redirect( $location );
			// exit;
	}

	public function comment_head(){
		wp_enqueue_style( 'botdetect-captcha-style' );
	}

	public function comment_reset(){
		$this->reset_captcha('comment_captcha', 'comment_captcha_field');
	}

	/**
	 * Show Captcha on lost password form
	 */
	public function lost_password_form(){
		$this->show_captcha_form('lost_password_captcha', 'lost_password_captcha_field');
	}

	public function lost_password_validate(){
		if ($_POST){
			$isHuman = $this->validate_captcha('lost_password_captcha', 'lost_password_captcha_field');
			if(!$isHuman){
				wp_die( __('<strong>ERROR</strong>: Please browser\'s back button and retype the letters under the CAPTCHA image.', 'botdetect-wp-captcha'), 'BotDetect');
			}else{
				$this->reset_captcha('lost_password_captcha', 'lost_password_captcha_field');
			}
		}
	}

	/**
	 * Show Captcha on register form
	 */
	public function register_form(){
		$this->show_captcha_form('register_captcha', 'register_captcha_field');
	}

	public function register_validation($error){
		if ($_POST){
			$isHuman = $this->validate_captcha('register_captcha', 'register_captcha_field');
			if(!$isHuman){
				if (!is_wp_error($error)) {
					$error = new WP_Error();
				}

				$error->add('captcha_fail', __('<strong>ERROR</strong>: Please retype the letters under the CAPTCHA image.', 'botdetect-wp-captcha'), 'BotDetect');
				return $error;
			}else{
				$this->reset_captcha('register_captcha', 'register_captcha_field');
				return $error;
			}
		}
	}

	/**
	 * Captcha helpers
	 */
	public function validate_captcha($captcha_ID = 'BotDetectCaptcha', $UserInputId = 'CaptchaCode'){
		$captcha = &$this->init_captcha($captcha_ID, $UserInputId);

	   	// mario: always visible -- new
	    $UserInput = $_POST[$UserInputId];
	    $isHuman = $captcha->Validate($UserInput);
	 
		return $isHuman;
	}

	/**
	 *
	 */
	public function get_captcha_form($captcha_ID = 'BotDetectCaptcha', $UserInputId = 'CaptchaCode'){
		$captcha = &$this->init_captcha($captcha_ID, $UserInputId);
	    
	    // mario: always visible -- new
	    $output = $captcha->Html();
	    $output .= '<input name="' . $UserInputId . '" type="text" id="' . $UserInputId .'" />';

		return $output;
	}

	public function show_captcha_form($captcha_ID = 'BotDetectCaptcha', $UserInputId = 'CaptchaCode', $options = array()){
		$elements = array();
		$elements[] = $this->get_captcha_form($captcha_ID, $UserInputId);
		if(isset($options) && count($options) != 0 && isset($options[0])){
			if (array_key_exists('label', $options)){
				array_unshift($elements, '<label for="' . $UserInputId. '">' . $options['label']. '</label>');
			}
			if (array_key_exists('prepend', $options)){
				array_unshift($elements, $options['prepend']);
			}
			if (array_key_exists('append', $options)){
				$elements[] = $options['append'];
			}
		}
		echo implode('', $elements);
	}

	public function reset_captcha($captcha_ID = 'BotDetectCaptcha', $UserInputId = 'CaptchaCode'){
		$captcha = &$this->init_captcha($captcha_ID, $UserInputId);
		$captcha->Reset();
		$this->is_solved = false;
	}

	public function &init_captcha($captcha_ID = 'BotDetectCaptcha', $UserInputId = 'CaptchaCode'){
		$captcha = new Captcha($captcha_ID);
		$captcha->UserInputId = $UserInputId;

		return $captcha;
	}

	/**
	 * Admin notices
	 */
	function captcha_library_missing_notice(){

        if ($this->is_bdwp_settings_page()) {
            echo '<div class="error" id="notice-captcha-library"><p>'. sprintf(__( '<b>You are almost done!</b> BotDetect WordPress Captcha Plugin requires you to deploy the BotDetect PHP Captcha library to your WordPress server.<br>
            	Please click the "Install BotDetect Captcha library" button to download the latest BotDetect PHP Captcha Library from <a href="%scaptcha.com?utm_source=plugin&amp;utm_medium=wp&amp;utm_campaign=%s" target="_blank">captcha.com</a> site.', 'botdetect-wp-captcha'), BDWP_HttpHelpers::GetProtocol(), BDWP_PluginInfo::GetVersion()) .'</p></div>';
        }
        else {
	  		echo '<div class="error" id="notice-captcha-library"><p>' . sprintf(__( '<b>You are almost done!</b> BotDetect WordPress Captcha Plugin requires you to deploy the BotDetect PHP Captcha library to your WordPress server.<br>
	  			Please go to the <a href="%s">plugin settings</a> to do it.', 'botdetect-wp-captcha'), admin_url('options-general.php?page='.plugin_basename( __FILE__ ))) . '</p></div>';
	  	}
	}
  

	function captcha_service_notice(){
	  	echo '<div class="updated"><p>' . __( 'The BotDetect Captcha service is currently in a closed Alpha testing phase. Please contact us if you wish to participate in testing.', 'botdetect-wp-captcha') . '</p></div>';
	}

	/**
	 * Minimum WP version
	 */
	public function wp_version_requirement() {
		global $wp_version;
		$plugin = plugin_basename( __FILE__ );
		$plugin_data = get_plugin_data( __FILE__, false );

		if ( version_compare($wp_version, "3.3", "<" ) ) {
			if( is_plugin_active($plugin) ) {
				deactivate_plugins( $plugin );
				wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
			}
		}
	}

	/**
	 * Add defaults on plugin activation
	 */
	public static function add_defaults() {
		global $LBD_WP_Defaults;

		$tmp = get_option('botdetect_options');
		if($tmp['chk_default_options_db'] == true || !is_array($tmp)) {
			delete_option('botdetect_options');
			update_option('botdetect_options', $LBD_WP_Defaults);
		}

		// Add bdwp_diagnostics plugin install
		self::add_diagnostics_plugin_install();

		add_option('bdwp_do_activation_redirect', true);
	}


	/**
	 * Delete options on deactivation
	 */
	public static function delete_options() {
		delete_option('botdetect_options');
		delete_option('bdwp_diagnostics');
		delete_option('bdwp_settings');
		delete_option('bdwp_workflow');
	}

	/**
	 * Add options page
	 */
	public function add_options_page() {
		add_options_page('BotDetect CAPTCHA WordPress Plugin Settings', 'BotDetect CAPTCHA', 'manage_options', __FILE__, array($this,'render_options_page'));
	}

	public function plugin_action_links( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			$action_link = '<a href="'.get_admin_url().'options-general.php?page='.plugin_basename( __FILE__ ).'">'.__('Settings').'</a>';
			// make the 'Settings' link appear first
			array_unshift( $links, $action_link );
		}

		return $links;
	}

	public function register_setting() {
		register_setting( 'botdetect_plugin_options', 'botdetect_options', array($this,'validate_options'));
	}

	/**
	 * Sanitize & Validate
	 */
	public function validate_options($input) {
		 // strip html from textboxes
		$input['image_width'] =  absint(wp_filter_nohtml_kses($input['image_width'])) ;
		$input['image_height'] =  absint(wp_filter_nohtml_kses($input['image_height']));
		$input['min_code_length'] =  absint(wp_filter_nohtml_kses($input['min_code_length']));
		$input['max_code_length'] =  absint(wp_filter_nohtml_kses($input['max_code_length']));

		$input['generator'] =  ($input['generator'] == 'library' || $input['generator'] == 'service')? $input['generator']: 'library';
		$input['library_path'] =  trailingslashit($input['library_path']);
		$input['library_assets_url'] =  trailingslashit(wp_filter_nohtml_kses($input['library_assets_url']));
		$input['service_api_key'] =  wp_filter_nohtml_kses($input['service_api_key']);

		$input['on_login'] =  (empty($input['on_login']))? false : true;
		$input['on_comments'] =  (empty($input['on_comments']))? false : true;
		$input['on_lost_password'] =  (empty($input['on_lost_password']))? false : true;
		$input['on_registration'] =  (empty($input['on_registration']))? false : true;
		$input['audio'] =  (empty($input['audio']))? false : true;

		$input['helplink'] =  ($input['helplink'] == 'image' || $input['helplink'] == 'text' || $input['helplink'] == 'off')? $input['helplink'] : 'image';

		$input['chk_default_options_db'] =  (empty($input['chk_default_options_db']))? false : true;

		return $input;
	}
	
	/**
	 *  Current page is BDWP Settings page
	 */
	public function is_bdwp_settings_page() {
		$current_page = (isset($_REQUEST['page']))? str_replace('.php','',$_REQUEST['page']) : '';
		$settings_page = str_replace('.php', '', plugin_basename( __FILE__ ));
        return ($current_page == $settings_page)? true : false;
    }

	/**
	 *  Redirect to BDWP settings after plugin activation
	 */
	public function bdwp_plugin_redirect() {
		if (get_option('bdwp_do_activation_redirect', false)) {
	        delete_option('bdwp_do_activation_redirect');
	        wp_redirect(admin_url('options-general.php?page='.plugin_basename( __FILE__ )));
	    }
	}

	/**
	 *  Init settings
	 */
	public static function init_settings() {
		$bdwp_settings = array(
			'install_lib_automatically_on_plugin_update' => null,
			'customer_email' => '',
			'captcha_provider' => 'bdphplib'
		);
		return $bdwp_settings;
	}

	/**
	 *  Init diagnostics
	 */
	public static function init_diagnostics() {
		$bdwp_diagnostics = array(
			'database_version' => BDWP_PluginInfo::GetVersion(),
			'first_plugin_install' => array(
				'datetime' => '',
				'plugin_version' => '',
				'wp_version' => ''
			),
			'last_plugin_install' => array(
				'datetime' => '',
				'plugin_version' => '',
				'wp_version' => ''
			),
			'first_bdphplib_install' => array(
				'datetime' => '',
				'bdphplib_version' => '',
				'bdphplib_is_free' => true,
				'plugin_version' => '',
				'wp_version' => ''
			),
			'last_bdphplib_install' => array(
				'datetime' => '',
				'bdphplib_version' => '',
				'bdphplib_is_free' => true,
				'plugin_version' => '',
				'wp_version' => ''
			)
		);
		return $bdwp_diagnostics;
	}

	/**
	 *  Add diagnostics plugin install
	 */
	public static function add_diagnostics_plugin_install() {

		$bdwp_diagnostics = get_option('bdwp_diagnostics');
		if (!is_array($bdwp_diagnostics)) {
			$bdwp_diagnostics = self::init_diagnostics();
		}

		$last_plugin_install = array(
			'datetime' => current_time('mysql'),
			'plugin_version' => BDWP_PluginInfo::GetVersion(),
			'wp_version' => self::get_wordpress_version()
		);

		if (empty($bdwp_diagnostics['first_plugin_install']['plugin_version'])) {
			$bdwp_diagnostics['first_plugin_install'] = $last_plugin_install;
		}

		$bdwp_diagnostics['last_plugin_install'] = $last_plugin_install;
		update_option('bdwp_diagnostics', $bdwp_diagnostics);
	}

	/**
	 *  Add diagnostics botdetect php library install
	 */
	public static function add_diagnostics_bdphplib_install() {

		$bdwp_diagnostics = get_option('bdwp_diagnostics');
		if (is_array($bdwp_diagnostics)) {

			$bdphplib_info = Captcha::GetProductInfo();
			
			$last_bdphplib_install = array(
				'datetime' => current_time('mysql'),
				'bdphplib_version' => $bdphplib_info['version'],
				'bdphplib_is_free' => Captcha::IsFree(),
				'plugin_version' => BDWP_PluginInfo::GetVersion(),
				'wp_version' => self::get_wordpress_version()
			);

			if (empty($bdwp_diagnostics['first_bdphplib_install']['bdphplib_version'])) {
				$bdwp_diagnostics['first_bdphplib_install'] = $last_bdphplib_install;
			}

			$bdwp_diagnostics['last_bdphplib_install'] = $last_bdphplib_install;
			update_option('bdwp_diagnostics', $bdwp_diagnostics);
		}
	}

	/**
	 * Update last plugin install
	 */
	public function update_last_plugin_install() {

		$bdwp_diagnostics = get_option('bdwp_diagnostics');
	
		if (is_array($bdwp_diagnostics) &&
				$bdwp_diagnostics['last_plugin_install']['plugin_version'] != BDWP_PluginInfo::GetVersion()) {

			$last_plugin_install = array(
				'datetime' => current_time('mysql'),
				'plugin_version' => BDWP_PluginInfo::GetVersion(),
				'wp_version' => self::get_wordpress_version()
			);

			$bdwp_diagnostics['last_plugin_install'] = $last_plugin_install;
			update_option('bdwp_diagnostics', $bdwp_diagnostics);
		}
	}

	/**
	 * Check the BotDetect Captcha Library is installed
	 */
	public function library_is_installed() {
		return ($this->options['generator'] == 'library' && !class_exists('LBD_CaptchaBase'))? false : true;
	}

	/**
	 * Starting installation BotDetect Captcha Library
	 */
	public function starting_installation_library() {
		$bdwp_workflow = array('bdphplib_is_installing' => true);
		update_option('bdwp_workflow', $bdwp_workflow);
	}

	/**
	 *  Installation ended BotDetect Captcha Library
	 */
	public function installation_ended_library() {
		update_option('bdwp_workflow', '');
	}

	/**
	 * Hidden notice captcha library after installation
	 */
	public function hidden_notice_captcha_library() {
		echo '<script> $(document).ready(function(){  document.getElementById("notice-captcha-library").style.cssText= "display:none!important;"  }); </script>';
	}

	/**
	 * Detect Internet Explorer version [1-7]
	 */
	public function detect_ie_browser() {
		return (preg_match('/(?i)MSIE [1-7]/', $_SERVER['HTTP_USER_AGENT']))? true : false;
	}

    /**
     *  Email store on client's WordPress database
     */
    public function save_customer_email($email) {
        $bdwp_settings = self::init_settings();
        $bdwp_settings['customer_email'] = $email;
        update_option('bdwp_settings', $bdwp_settings);
    }

    /**
     *  Installl library automatically on plugin update 
     */
    public function install_library_automatically() {

    	if (!$this->library_is_installed()) {

    		$bdwp_settings = get_option('bdwp_settings');

    		if ($this->array_key_exists_check('install_lib_automatically_on_plugin_update', $bdwp_settings) && 
    				$bdwp_settings['install_lib_automatically_on_plugin_update'] == true) {

    			$customerEmail = (array_key_exists('customer_email', $bdwp_settings))? $bdwp_settings['customer_email'] : '';

    			if (!empty($customerEmail)) {

    				$installConfig = array(
    					'customer_email' => $customerEmail,
    					'relay_url' => 'http://captcha.com/forms/integration/relay.php',
    					'folder_plugin' => dirname(__FILE__),
    					'plugin_version' => BDWP_PluginInfo::GetVersion()
    				);

                    $install = new BDWP_InstallCaptchaProviderBotDetectLibrary($installConfig);
                    $result = $install->DoInstall();
                    
                    if ($result['status'] == 'SUCESSFULLY_INSTALLED') {
                        $this->delete_file($result['pathFileLibraryOnDisk']);
                    }
				}
    		}
    	}
    }

	/**
	 * Output the options page & form HTML
	 */
	public function render_options_page() {
		?>
		<div class="wrap">

			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php printf(__('BotDetect CAPTCHA WordPress Plugin (%s)', 'botdetect-wp-captcha'), BDWP_PluginInfo::GetVersion());?></h2>
			<p></p>
			
      		<p id="lblMessageStatus"></p>

      		<p class="botdetect-license"><?php _e('The license under which the BotDetect Captcha WordPress Plugin software is released is the <a href="http://www.gnu.org/licenses/gpl-2.0.txt">GPLv2</a> (or later) from the <a href="http://www.fsf.org/">Free Software Foundation</a>. Plugin source code is available for <a href="https://github.com/wp-plugins/botdetect-wp-captcha">download here</a>.', 'botdetect-wp-captcha'); ?></p>   		
			   
      		<!-- START - Install Botdetect Captcha Library -->
      		<?php 
      			// Form install lib automatically on plugin update
                if (isset($_REQUEST['btnSaveAutoInstall'])) {

                	$bdwp_settings = get_option('bdwp_settings');
                	if( isset($_REQUEST['chk_install_auto_after_update']) ) {
                		$bdwp_settings['install_lib_automatically_on_plugin_update'] = true;
                	} else {
                		$bdwp_settings['install_lib_automatically_on_plugin_update'] = false;
                	}
                	// Add settings and diagnostics botdetec php library install
                	update_option('bdwp_settings', $bdwp_settings);
                	self::add_diagnostics_bdphplib_install();

                	$this->installation_ended_library();
                }
      		?>

      		<?php 
      			// Hidden install library form
      			$isHiddenInstallLibraryForm = true;
      			$bdwp_settings = get_option('bdwp_settings');
      			if (!$this->library_is_installed() || $this->array_key_exists_check('install_lib_automatically_on_plugin_update', $bdwp_settings) && $bdwp_settings['install_lib_automatically_on_plugin_update'] === null) {
      				$isHiddenInstallLibraryForm = false;
      			}
      		?>

            <div id="container-install" class="<?php echo ($isHiddenInstallLibraryForm)? 'hidden-install-form' : '';?>">

            	<?php
            		$installStatus = '';
            		$installMessage = '';

	              	if (isset($_REQUEST['btnInstallLBD'])) {
	              		// Starting installation
	              		$this->starting_installation_library();
	              		$customerEmail = $_REQUEST['customerEmail'];

                        $installConfig = array(
	    					'customer_email' => $customerEmail,
	    					'relay_url' => 'http://captcha.com/forms/integration/relay.php',
	    					'folder_plugin' => dirname(__FILE__),
	    					'plugin_version' => BDWP_PluginInfo::GetVersion()
	    				);

                        $install = new BDWP_InstallCaptchaProviderBotDetectLibrary($installConfig);

                        $result = $install->DoInstall();
                        if ($result['status'] == 'SUCESSFULLY_INSTALLED') {

                        	$this->hidden_notice_captcha_library();
                            $this->save_customer_email($customerEmail);
                            
                            $installStatus = $result['status'];

                            $this->delete_file($result['pathFileLibraryOnDisk']);

                        } else if ($result['status'] == 'ERR_INVALIDEMAIL') {
                            $installStatus = $result['status'];
                            $installMessage = $result['message'];
                        } else {
                            $installStatus = $result['status'];
                            $installMessage = $result['message'];
                        }
                    }

                ?>

            	<form action="" method="post">
            		<input type="hidden" id="BDUrlCaptchaImage" value="<?php echo network_site_url('/');?>index.php?botdetect_request=1&get=image&c=login_captcha&t=9da6cb3b0e6dcbec4dfeb03392c61b88" >
            		<input type="hidden" id="BDPluginFolder" value="<?php echo plugin_dir_url( __FILE__ );?>">
            		<input type="hidden" id="BDOptions" value="<?php echo json_encode($this->options);?>">
            		<input type="hidden" id="BDMsgImageRenderError" value="<?php _e('An error occurred while generating a captcha image. Captcha has been disabled in a login form.','botdetect-wp-captcha');?>">
            		<input type="hidden" id="BDMsgLoadingRenderCheck" value="<?php _e('Cheking render captcha image...', 'botdetect-wp-captcha');?>">
            		<input type="hidden" id="BDMsgWorkingInstallLib" value="<?php _e('Working...<br>Installation may take a few minutes, please wait.', 'botdetect-wp-captcha');?>">

                <?php
                	if ($installStatus == 'ERR_OCCURED') {
                		$this->installation_ended_library();
                		printf(__('<p class="error_msg res_msg">%s</p>', 'botdetect-wp-captcha'), $installMessage);
                ?>
                		<a href="<?php echo BDWP_HttpHelpers::GetProtocol();?>captcha.com/contact.html" class="button-primary" target="_blank"><?php _e('Please let us know this -- we\'ll try to help!', 'botdetect-wp-captcha'); ?></a>
                <?php
                	} else if ($this->library_is_installed() || $installStatus == 'SUCESSFULLY_INSTALLED') {

                    	if ($installStatus == 'SUCESSFULLY_INSTALLED' && !$this->detect_ie_browser()) {
                            $this->hook('admin_footer', 'bdwp_captcha_image_render_check_scripts');
                    	} else {
                    		$this->installation_ended_library();
                    	}
                ?>
						<p class="msg-installed-lib"><?php _e('BotDetect Captcha Library is sucessfully installed.', 'botdetect-wp-captcha');?></p>
                       	<p><label><input type="checkbox" name="chk_install_auto_after_update" checked="checked"><?php _e('Automatically install library after future plugin updates.', 'botdetect-wp-captcha');?></label></p>
                       	<input type="submit" class="button-primary" name="btnSaveAutoInstall" id="btnSaveAutoInstall" value="<?php _e('OK, let\'s move forward', 'botdetect-wp-captcha') ?>" />
		        <?php
                    } else {
                    	$bdwp_settings = get_option('bdwp_settings');
                    	$customerEmail = (is_array($bdwp_settings) && array_key_exists('customer_email', $bdwp_settings))? $bdwp_settings['customer_email'] : '';
               	?>
               			<p class="msg-no-install-lib"><?php _e('BotDetect Captcha Library is not installed. Click the <span>Install BotDetect Captcha Library</span> button to install it.', 'botdetect-wp-captcha')?></p>
						<input type="text" size="40" class="input-text" value="<?php echo $customerEmail;?>" placeholder="<?php _e('Enter your email', 'botdetect-wp-captcha');?>" name="customerEmail" id="customerEmail"/>
	                	<p><?php _e('We need your email to reference your deployment in our database. We will use it to inform you about security updates, new features, etc. We will never give your email to third parties, and you can easily unsubscribe (from our rare mailings) at any time.', 'botdetect-wp-captcha')?></p>

	                	<div>
		                    <input type="submit" class="button-primary" name="btnInstallLBD" id="btnInstallLBD" value="<?php _e('Install BotDetect Captcha Library', 'botdetect-wp-captcha') ?>" />
		                    <p class="btn-disable-install-lib" id="btnInstallDisable"><?php _e('Installing...', 'botdetect-wp-captcha')?></p>
		                </div>                       	
                <?php
                    }
                ?>  
                </form>

                <?php
    				if ($installStatus == 'ERR_INVALIDEMAIL') {
    					$this->installation_ended_library();
                		printf(__('<p id="wrong_email" class="error_msg res_msg">%s</p>', 'botdetect-wp-captcha'), $installMessage);
                	}
                ?>

                <p class="res_msg" id="lblWaiting"></p>
            </div>
            
            <!-- END - Install Botdetect Captcha Library -->

			<form method="post" action="options.php">
				<?php settings_fields('botdetect_plugin_options'); ?>
				<?php $options = $this->options; ?>
                <?php
                    $isDisableField = false;
					$bdwp_settings = get_option('bdwp_settings');
					if (!$this->library_is_installed() || $this->array_key_exists_check('install_lib_automatically_on_plugin_update', $bdwp_settings) && $bdwp_settings['install_lib_automatically_on_plugin_update'] === null) {
						$isDisableField = true;
					}
                ?>

				<input type="hidden" name="botdetect_options[library_path]" value="<?php echo (isset($options['library_path']))? $options['library_path'] : ''; ?>" />
				<input type="hidden" name="botdetect_options[library_assets_url]" value="<?php echo (isset($options['library_assets_url']))? $options['library_assets_url'] : ''; ?>" />
        
				<table class="form-table">

			        <tr valign="top" >            
						<th scope="row"><h3><?php _e('Plugin settings', 'botdetect-wp-captcha'); ?></h3></th>
						<td></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Generate Captchas', 'botdetect-wp-captcha'); ?></th>
						<td>
							<div id="botdetect_service_options_wrapper" class="botdetect_options_wrapper">
								<label style="color: #ccc;"><input class="botdetect_generator_select" name="botdetect_options[generator]" type="radio" value="service" disabled="disabled" /> <?php _e('Remotely', 'botdetect-wp-captcha'); ?> <span style="color:#ccc;margin-left:5px;"><?php _e('(using the BotDetect CAPTCHA service -- not available since BotDetect Captcha service is currently in a private pre-Alpha testing phase)', 'botdetect-wp-captcha'); ?></span></label><br />
								<div id="botdetect_service_options_wrapper" class="botdetect_options_wrapper" style="margin-left: 15px; margin-bottom: 20px;">
									<label style="color: #ccc;"><?php _e('Service API key:', 'botdetect-wp-captcha'); ?> <br /><input type="text" size="50" name="botdetect_options[service_api_key]" value="" disabled="disabled" /></label><br />
									<label style="color: #ccc;"><input name="botdetect_options[service_redundancy]" type="checkbox" value="true" checked="checked" disabled="disabled"/> <?php _e('Use a local BotDetect CAPTCHA library as a fallback.', 'botdetect-wp-captcha'); ?> </label><br />
								</div>
								<label><input class="botdetect_generator_select" name="botdetect_options[generator]" type="radio" value="library" checked="checked" disabled="disabled" /> <?php _e('Locally', 'botdetect-wp-captcha'); ?> <span style="color:#666666;margin-left:5px;"><?php _e('(using a local BotDetect PHP CAPTCHA Library)', 'botdetect-wp-captcha'); ?></span></label><br />
							</div>
						</td>
					</tr>

					<tr><td colspan="2"><div style="margin-top:10px; border-top:#dddddd 1px solid;"></div></td></tr>
					<tr valign="top">
						<th scope="row"><?php _e('Use BotDetect CAPTCHA with', 'botdetect-wp-captcha'); ?></th>
						<td>
							<label><input name="botdetect_options[on_login]" type="checkbox" value="true" <?php echo ($isDisableField)? ' disabled ' : '';?> <?php if (isset($options['on_login'])) { checked($options['on_login'], true); } ?> /> <?php _e('Login', 'botdetect-wp-captcha'); ?> </label><br />
							<label><input name="botdetect_options[on_registration]" type="checkbox" value="true" <?php echo ($isDisableField)? ' disabled ' : '';?> <?php if (isset($options['on_registration'])) { checked($options['on_registration'], true); } ?> /> <?php _e('User Registration', 'botdetect-wp-captcha'); ?> </label><br />
							<label><input name="botdetect_options[on_lost_password]" type="checkbox" value="true" <?php echo ($isDisableField)? ' disabled ' : '';?> <?php if (isset($options['on_lost_password'])) { checked($options['on_lost_password'], true); } ?> /> <?php _e('Lost Password', 'botdetect-wp-captcha'); ?> </label><br />
							<label><input name="botdetect_options[on_comments]" type="checkbox" value="true" <?php echo ($isDisableField)? ' disabled ' : '';?> <?php if (isset($options['on_comments'])) { checked($options['on_comments'], true); } ?> /> <?php _e('Wordpress Comments', 'botdetect-wp-captcha'); ?> </label><br />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Captcha image width', 'botdetect-wp-captcha'); ?></th>
						<td>
							<input type="text" size="3" name="botdetect_options[image_width]" value="<?php echo (isset($options['image_width']))? $options['image_width'] : ''; ?>" <?php echo ($isDisableField)? ' disabled ' : '';?> />
							<span style="color:#666666;">px</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Captcha image height', 'botdetect-wp-captcha'); ?></th>
						<td>
							<input type="text" size="3" name="botdetect_options[image_height]" value="<?php echo (isset($options['image_height']))? $options['image_height'] : ''; ?>" <?php echo ($isDisableField)? ' disabled ' : '';?> />
							<span style="color:#666666;">px</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Number of characters', 'botdetect-wp-captcha'); ?></th>
						<td>
							<input type="text" size="3" id="min_code_length" name="botdetect_options[min_code_length]" value="<?php echo (isset($options['min_code_length']))? $options['min_code_length'] : ''; ?>" <?php echo ($isDisableField)? ' disabled ' : '';?> /> &ndash;
                            <input type="text" size="3" id="max_code_length" name="botdetect_options[max_code_length]" value="<?php echo (isset($options['max_code_length']))? $options['max_code_length'] : ''; ?>" <?php echo ($isDisableField)? ' disabled ' : '';?> />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Sound', 'botdetect-wp-captcha'); ?></th>
						<td>
							<label><input name="botdetect_options[audio]" type="checkbox" value="true" <?php if (isset($options['audio'])) { checked($options['audio'], true); } ?> <?php echo ($isDisableField)? ' disabled ' : '';?> /> <?php _e('Enable audio Captcha', 'botdetect-wp-captcha'); ?></label>
						</td>
					</tr>
          
          <?php
            $isFree = false; 
            if (class_exists('Captcha') && Captcha::IsFree()) $isFree = true; 
          ?>
					<tr>
                        <th scope="row"><?php _e('Remote Include', 'botdetect-wp-captcha'); ?></th>
                        <td>
                            <label><input name="botdetect_options[remote]" type="checkbox" value="true" 
							<?php if ((!class_exists('Captcha') && !$isFree) || $isFree) echo "disabled"; ?> <?php if (isset($options['remote'])) { checked($options['remote'], true); } ?> />
								<?php _e('Enable Remote Include -- used for statistics collection and proof-of-work confirmation (still work in progress)','botdetect-wp-captcha'); ?>
                                <br>
                                <?php _e('<i>Switching off is disabled with the Free version of BotDetect.', 'botdetect-wp-captcha'); ?> </label>
                        </td>
                    </tr>
          
					<tr valign="top">
                        <th scope="row"><?php _e('Help link', 'botdetect-wp-captcha'); ?></th>
                        <td>
                            <label><input name="botdetect_options[helplink]" type="radio" value="image" <?php echo ($isDisableField)? ' disabled ' : '';?> <?php checked($options['helplink'], 'image'); ?> /> <?php _e('Image', 'botdetect-wp-captcha'); ?> <span style="color:#666666;margin-left:42px;"><?php _e('Clicking the Captcha image opens the help page in a new browser tab.', 'botdetect-wp-captcha'); ?></span></label><br />
                            <label><input name="botdetect_options[helplink]" type="radio" value="text" <?php echo ($isDisableField)? ' disabled ' : '';?> <?php checked($options['helplink'], 'text'); ?> /> <?php _e('Text', 'botdetect-wp-captcha'); ?> <span style="color:#666666;margin-left:56px;"><?php _e('A text link to the help page is rendered in the bottom 10 px of the Captcha image.', 'botdetect-wp-captcha'); ?></span></label><br />
                            <label><input name="botdetect_options[helplink]"
                                    <?php if ((!class_exists('Captcha') && !$isFree) || $isFree) echo "disabled"; ?>
                                          type="radio" value="off" <?php checked($options['helplink'], 'off'); ?> /> <?php _e('Off', 'botdetect-wp-captcha'); ?> <span style="color:#666666;margin-left:63px;">
              <?php if ($isFree) { ?>
              <?php _e('<i>Not available with the Free version of BotDetect.', 'botdetect-wp-captcha'); ?> </span></label><br />
                            <?php } else { ?>
                                <?php _e('Help link is disabled.', 'botdetect-wp-captcha'); ?></span></label><br />
                            <?php } ?>
                        </td>
                    </tr>

					<tr>

						<td colspan = "2">
							<p><?php printf(__('Additionally: Please note almost everything is customizable by editing BotDetect\'s <a href="%scaptcha.com/doc/php/howto/captcha-configuration.html?utm_source=plugin&amp;utm_medium=wp&amp;utm_campaign=%s">configuration file</a>.', 'botdetect-wp-captcha'), BDWP_HttpHelpers::GetProtocol(), BDWP_PluginInfo::GetVersion()); ?></p>
						</td>
					</tr>

					<tr><td colspan="2"><div style="margin-top:10px; border-top:#dddddd 1px solid;"></div></td></tr>
					<tr valign="top">
						<th scope="row"><?php _e('Misc Options', 'botdetect-wp-captcha'); ?></th>
						<td>
							<label><input name="botdetect_options[chk_default_options_db]" type="checkbox" value="true" <?php if (isset($options['chk_default_options_db'])) { checked($options['chk_default_options_db'], true); } ?> <?php echo ($isDisableField)? ' disabled ' : '';?> /> <?php _e('Restore defaults upon plugin deactivation/reactivation', 'botdetect-wp-captcha'); ?></label>
							<br /><span style="color:#666666;margin-left:2px;"><?php _e('Only check this if you want to reset plugin settings upon Plugin reactivation', 'botdetect-wp-captcha'); ?></span>
						</td>
					</tr>
				</table>
				<p class="submit">
				<input type="submit" class="button-primary" id="btnBDSettingsSaveChanges" value="<?php _e('Save Changes') ?>" <?php echo ($isDisableField)? ' disabled ' : '';?> />
				</p>
			</form>
		</div>
		<?php
	}

	function jquery_library_scripts() {
		wp_enqueue_script( 'bdwp-jquery-library', plugin_dir_url( __FILE__ ) . 'public/js/jquery.min.js' );
	}

	function bdwp_settings_scripts() {
		wp_enqueue_script( 'bdwp-settings-validation', plugin_dir_url( __FILE__ ) . 'public/js/bdwp_settings_validation.js' );
		wp_enqueue_script( 'bdwp-install-progress', plugin_dir_url( __FILE__ ) . 'public/js/captcha_provider_installation_progress.js' );
	}

	function bdwp_install_stylesheet() {
        wp_enqueue_style( 'bdwp-install-progress-stylesheet', plugin_dir_url( __FILE__ ) . 'public/css/style.css' );
    }

    function bdwp_captcha_image_render_check_scripts() {
        wp_enqueue_script( 'bdwp-captcha-image-render-check', plugin_dir_url( __FILE__ ) . 'public/js/captcha_image_render_check.js' );
    }

    public function array_key_exists_check($key, $arr) {
    	if (!is_array($arr)) return false;
    	if (!array_key_exists($key, $arr)) return false;
    	return true;
    }

    public function bdwp_show_update_message($plugin_data, $r) {
		echo '<p style="color: red">After updating please just open plugin settings, and the required changes will be applied automatically.</p>';
	}

    /** 
	 * Detect the new version of BotDetect WP plugin
	 */
	public function bdwp_detect_new_version() {
		global $pagenow;
		if ($pagenow === 'plugins.php') {
		    $file = basename( __FILE__ );
		    $folder = basename( dirname( __FILE__ ) );
		    $hook = "in_plugin_update_message-{$folder}/{$file}";
		    add_action($hook, array($this,'bdwp_show_update_message'), 10, 2);
		}
	}

	/**
     *	Delete a file
     */
    public function delete_file($path_file) {
        if (!empty($path_file)) {
            return @unlink($path_file);
        }
        return false;
    }

	/**
	 * Add action helper
	 */
	public function hook($hook){
		$priority = 10;
		$method = $this->sanitize_method($hook);
		$additional_args = func_get_args();
		unset($additional_args[0]);
		// set priority
		foreach((array)$additional_args as $a){
			if(is_int($a)){
				$priority = $a;
			}else{
				$method = $a;
			}
		}
		return add_action($hook,array($this,$method),$priority,999);
	}

	/**
	 * Sanitize hooks
	 */
	private function sanitize_method($m){
		return str_replace(array('.','-'),array('_DOT_','_DASH_'),$m);
	}
}

new WP_Botdetect_Plugin($LBD_WP_Options);

// Include: BDWP_InstallCaptchaProviderBotDetectLibrary class
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'InstallCaptchaProviderBotDetectLibrary.php' );
?>