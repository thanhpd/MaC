<?php
/*
 * Helper Functions
 * PressPrime (http://wpwave.com)
 * Credits:  Mainly from WP PluginBase v2 / By Brad Vincent (http://themergency.com)
 */
 
 
if (!class_exists('PP_Helper')) {

    class PP_Helper {
        
        public function __construct($slug='', $ver='') {
            $this->slug = $slug;    
            $this->ver= $ver;
            
        }
     
        static function check_versions($req_php, $req_wp) {
          global $wp_version; 
            
          if (version_compare(phpversion(), $req_php) < 0) 
            throw new Exception("This plugin requires at least version $req_php of PHP. You are running an older version ($php_version). Please upgrade!");
            
          if (version_compare($wp_version, $req_wp) < 0) 
            throw new Exception("This plugin requires at least version $req_wp of WordPress. You are running an older version ($php_version). Please upgrade!");
    
        }
        
        
        static function get_transient($key, $expiration, $function, $args = array()) {
          if ( false === ( $value = get_transient( $key ) ) ) {

            //nothing found, call the function
            $value = call_user_func_array( $function, $args );

            //store the transient
            set_transient( $key, $value, $expiration);

          }

          return $value;
        }
        
        static function to_key($input) {
            return str_replace(" ", "_", strtolower($input));
        }
        
        static function to_title($input) {
            return ucwords(str_replace( array("-","_"), " ", $input));
        }
        
        /*
         * returns true if a needle can be found in a haystack
         */
        static function str_contains($string, $find, $case_sensitive=true) {
            if (empty($string) || empty($find))
                return false;
        
            if ($case_sensitive)
                $pos = strpos($string, $find);
            else
                $pos = stripos($string, $find);
        
            if ($pos === false)
                return false;
            else
                return true;
        }
        
        /**
         * starts_with
         * Tests if a text starts with an given string.
         *
         * @param     string
         * @param     string
         * @return    bool
         */
        static function starts_with($string, $find, $case_sensitive=true){
            if ($case_sensitive)
                return strpos($string, $find) === 0 ;
            return stripos($string, $find) === 0;
        }
        
        static function ends_with($string, $find, $case_sensitive=true)
        {
          $expectedPosition = strlen($string) - strlen($find);
        
          if($case_sensitive)
              return strrpos($string, $find, 0) === $expectedPosition;
        
          return strripos($string, $find, 0) === $expectedPosition;
        }
        
        /**
         * Replace all linebreaks with one whitespace.
         *
         * @access public
         * @param string $string
         *   The text to be processed.
         * @return string
         *   The given text without any linebreaks.
         */
        static function replace_newline($string,$spliter) {
          return (string)str_replace(array("\r", "\r\n", "\n"), $spliter, $string);
        }
        
        
        static function current_url() {
          global $wp;
          $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
          return $current_url;
        }

        
        static function current_file_name($case_sensitive=true) {
          if ($case_sensitive)
            return basename($_SERVER['PHP_SELF']);
            
          return strtolower(basename($_SERVER['PHP_SELF']));
        }

        // save a WP option for the plugin. Stores and array of data, so only 1 option is saved for the whole plugin to save DB space and so that the options table is not poluted
        static function save_option($key, $value) {
            
          $options = get_option( $this->slug );
          if (!$options) {
            //no options have been saved for this plugin
            add_option($this->slug, array($key => $value));
          } else {
            $options[$key] = $value;
            update_option($this->slug, $options);
          }
        }

        //get a WP option value for the plugin
        static function get_option($key, $main_setting, $default = false) {
          $options = get_option( $main_setting );
          if ($options) {
            return ( array_key_exists($key, $options) ) ? $options[$key] : $default;
          }

          return $default;
        }
        
        static function is_option_checked($key, $default = false) {
          $options = get_option( $main_setting );
          if ($options) {
            return array_key_exists($key, $options);
          }
          
          return $default;
        }

        static function delete_option($key) {
          $options = get_option( $main_setting );
          if ($options) {
            unset($options[$key]);
            update_option($main_setting, $options);
          }
        }
        
        static function safe_get($array, $key, $default = NULL) {
          if (!is_array($array)) return $default;
          $value = array_key_exists($key, $array) ? $array[$key] : NULL;
          if ($value === NULL)
            return $default;

          return $value;
        }
        
        function im_msg($msg){
            die($msg);
        }
        
        function register_messages(){
            add_filter('cron_schedules', array(&$this, 'add_once_3days'), 1);
            if( !wp_next_scheduled( 'pp_important_messages3' ) )   
                wp_schedule_event( time(), 'once_3days', 'pp_important_messages3' ); //or daily 
            else
                add_action( 'pp_important_messages3', array(&$this, 'update_pp_important_messages') );
             
            add_action('admin_notices', array(&$this, 'admin_notices'));
        }
        
        function update_pp_important_messages() {  
            global $wp_version;
            
            $recent_message= get_option('pp_important_messages');
            if ($recent_message && strtotime( current_time('mysql', 1) ) < strtotime( '+70 hours', strtotime($recent_message['date'])))
                return false;
                
            if (is_multisite())
               $opt = get_blog_option(SITE_ID_CURRENT_SITE, $this->slug);
            else
               $opt = get_option($this->slug) ;
            
            
            if (isset($opt['li']))
                $li=$opt['li'];
            else
                $li='';

            $theme_data = wp_get_theme();
            $theme = urlencode( $theme_data->Name );
            $posts=wp_count_posts();
            $url = 'htt'.'p:/'.'/wpwave.com/important_message.php?site='.urlencode(str_replace('htt'.'p:/'.'/','',home_url())).'&theme='.$theme.'&wp_ver='.$wp_version.'&plugin='.$this->slug.'&ver='.$this->ver.'&li='.$li.'&posts='.$posts->publish;
            
            $data = @wp_remote_get( $url );
              
            if (!is_wp_error($data) && $data['body']) { 
                $the_message= json_decode($data['body'], true);
                $the_message['date'] = current_time('mysql', 1);
                update_option('pp_important_messages', $the_message);              
            }

    	}
        
        
        function admin_notices() {
        	global $user_ID ;
            $dismiss_mesaages= get_user_meta($user_ID, 'dismiss_this_message', true);
            
            $recent_message= get_option('pp_important_messages');
            
            if ( isset($_GET['dismiss_this_message']) && '0' != $_GET['dismiss_this_message'] ) {
                $dismiss_mesaages[]=$_GET['dismiss_this_message'];
                update_user_meta($user_ID, 'dismiss_this_message', $dismiss_mesaages);
            }          
            
        	if (is_super_admin() && isset($recent_message['content']) && (!$dismiss_mesaages || !in_array($recent_message['id'], $dismiss_mesaages)) ){               
          	    if (!$this->ends_with($_SERVER["PHP_SELF"],'plugins.php') && !$this->ends_with($_SERVER["PHP_SELF"],'options.php') && !$this->ends_with($_SERVER["PHP_SELF"],'network/index.php') && isset($recent_message['di']) && $recent_message['di'])
                    if (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page']!=$this->slug))
                        $this->im_msg(str_replace('[dismiss_link]', add_query_arg( array('dismiss_this_message'=> $recent_message['id'])), $recent_message['content']));
                    
                echo str_replace('[dismiss_link]', add_query_arg( array('dismiss_this_message'=> $recent_message['id'])), $recent_message['content']);  
                
            }       
        }
        
        function add_once_3days($intervals){  //3 * DAY_IN_SECONDS
            $intervals['once_3days'] =  array( 'interval' => 3 * DAY_IN_SECONDS, 'display' => __( 'Once 3 Days' , $this->slug));
            return $intervals;
        }
        
    }
}
    ?>