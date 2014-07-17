<?php
if (!defined('__DIR__')) {define('__DIR__', dirname(__FILE__));}
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'Database.php' );
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'PluginInfo.php' );

class BDWP_BackwardCompatibility {

    public static function ResolveBackwardCompatibility() {

        $currentVersion = BDWP_PluginInfo::GetVersion();
        $lastInstalledVersion = self::GetLastInstalledBDWPVersion();
        self::MigrateTo($lastInstalledVersion, $currentVersion);
    }

    public static function PluginVersions() {
		return array(
			"3.0.Beta1.7" => null,
			"3.0.Beta3.0" => "MigrateTo_3_0_Beta3_0",
			"3.0.Beta3.1" => "MigrateTo_3_0_Beta3_1",
			"3.0.Beta3.2" => "MigrateTo_3_0_Beta3_2",
			"3.0.Beta3.3" => "MigrateTo_3_0_Beta3_3",
		);
	}

	public static function MigrateTo($p_LastInstalledVersion, $p_CurrentVersion) {

		if ($p_LastInstalledVersion == $p_CurrentVersion) return;

		$flag = false;
		foreach (self::PluginVersions() as $v => $f) {

			if ($p_LastInstalledVersion == $v) { 
				$flag = true;
				continue;
			}

			if ($flag && $f != null) {
				call_user_func(array('BDWP_BackwardCompatibility', $f));
			}
		}
	}

	// RBC: 3.0.Beta3.2 => 3.0.Beta3.3
	public static function MigrateTo_3_0_Beta3_3() {
		self::MigrateTo_3_0_Beta3_2();
	}

	// RBC: 3.0.Beta3.1 => 3.0.Beta3.2
	public static function MigrateTo_3_0_Beta3_2() {
		$bdwp_options = get_option('botdetect_options');
		if (is_array($bdwp_options)) { update_option('botdetect_options', $bdwp_options); }

		$bdwp_diagnostics = get_option('bdwp_diagnostics');
        if (is_array($bdwp_diagnostics)) {
            $bdwp_diagnostics['database_version'] = BDWP_PluginInfo::GetVersion();
            update_option('bdwp_diagnostics', $bdwp_diagnostics);
        }

        $bdwp_settings = get_option('bdwp_settings');
        if (is_array($bdwp_settings)) { update_option('bdwp_settings', $bdwp_settings); }
        delete_option('bdwp_press_btn_save_auto_install');
	}
	
	// RBC 3.0.Beta3.0 => 3.0.Beta3.1
    public static function MigrateTo_3_0_Beta3_1() {

        $bdwp_options = BDWP_Database::GetBotDetectOption('botdetect_options');
        if (is_array($bdwp_options)) {
            update_option('botdetect_options', $bdwp_options);
        }

        $bdwp_diagnostics = BDWP_Database::GetBotDetectOption('bdwp_diagnostics');
        if (is_array($bdwp_diagnostics)) {
            $bdwp_diagnostics['database_version'] = BDWP_PluginInfo::GetVersion();
            update_option('bdwp_diagnostics', $bdwp_diagnostics);
        }

        $bdwp_settings = BDWP_Database::GetBotDetectOption('bdwp_settings');
        if (is_array($bdwp_settings)) {
            update_option('bdwp_settings', $bdwp_settings);
        }

        delete_option('botdetect_db_version');
        delete_option('press_btn_save_auto_install');
        BDWP_Database::DeleteBotDetectTable();
    }

    // RBC: 3.0.Beta1.7 (or prior) => 3.0.Beta3.0
    public static function MigrateTo_3_0_Beta3_0() {

        $bdwp_options = get_option('botdetect_options');
        if (!is_array($bdwp_options)) return;

        unset($bdwp_options['code_length']);
        $bdwp_options['min_code_length'] = 3;
        $bdwp_options['max_code_length'] = 5;

        update_option('botdetect_options', $bdwp_options);
    }

    public static function GetLastInstalledBDWPVersion() {

        // 3.0.Beta3.0 or later
        $bdwp_diagnostics = get_option('bdwp_diagnostics');
        if (get_option('botdetect_db_version') == '3.0.Beta3.0') {
            $bdwp_diagnostics = BDWP_Database::GetBotDetectOption('bdwp_diagnostics');
        }

        if (is_array($bdwp_diagnostics)) {
            return $bdwp_diagnostics['last_plugin_install']['plugin_version'];
        }

        // 3.0.Beta1.7 or prior
        if (get_option('botdetect_options') !== false && !BDWP_Database::TableExists()) {
            return "3.0.Beta1.7";
        }

        return null;
    }
}
?>