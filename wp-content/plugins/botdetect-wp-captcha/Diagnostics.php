<?php 
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class BDWP_Diagnostics {

	/**
	 * Get all themes in client's wordpress and activated status
	 */
	public static function GetThemes() {
		$themes = array();
		$all_theme = wp_get_themes();
		$current_theme = get_stylesheet();

		foreach ($all_theme as $theme => $info) {
			$temp_theme = array(
				'Name'      => $info->display('Name'),
				'Version'   => $info->display('Version'),
				'Activated' => $theme == $current_theme
			);
			array_push($themes, $temp_theme);
		}
		return $themes;	
	}

	/**
	 * Get all plugins that have been installed and activated status
	 */
	public static function GetPlugins() {
		$plugins = array();
		$all_plugins = get_plugins();
		$activeted_plugins = get_option('active_plugins');
		
		foreach ($all_plugins as $plugin => $info) {
			$temp_plugin = array(
				'Name'      => $info['Name'],
				'Version'   => $info['Version'],
				'Activated' => in_array($plugin, $activeted_plugins)
			);
			array_push($plugins, $temp_plugin);
		}
		return $plugins;
	}

	/**
	 * Get WordPress version
	 */
	public static function GetWordPressVersion() {
		global $wp_version;
		return $wp_version;
	}

	/**
	 * Check Session is enabled
	 */
	public static function IsSessionEnabled() {

	    if (function_exists('session_status')) {
	    	return (session_status() === PHP_SESSION_ACTIVE); 
		} else {
			$setting = 'session.use_trans_sid';
    		$current_init = @ini_get($setting);

			if ($current_init !== false) {
				$result = @ini_set($setting, $current_init); 
    			return $result !== $current_init;
			} else {
				return false;
			}
		}
	}

	/**
	 * Check Multisite is enabled
	 */
	public static function IsWordPressConfiguredAsMultisite() {
		return is_multisite();
	}
}
?>