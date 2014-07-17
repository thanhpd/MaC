<?php

class BDWP_Localization {

    public static function Init() {
    	load_plugin_textdomain('botdetect-wp-captcha', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
}
?>