<?php
/*
Plugin Name: Hide My WP (Share on Theme123.Net)
Plugin URI: http://hide-my-wp.wpwave.com/
Description: With Hide My WP nobody can know you use WordPress! This not only greatly increases your security against hackers, bad written plugins, robots, spammers, etc. but it also allows you to have more beautiful URLs and better control over your WordPress. 
Author: Hassan Jahangiri 
Author URI: http://wpwave.com 
Version: 3.00
Text Domain: hide_my_wp
Domain Path: /lang
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Network: True
*/
    
// todo: new admin path in nginx / long sdq injec / paths in different domain by wpml
/**
 *   ++ Credits ++
 *   Copyright 2014 Hassan Jahangiri
 *   Some code from dxplugin base by mpeshev, plugin base v2 by Brad Vincent, weDevs Settings API by Tareq Hasan, rootstheme by Ben Word and Minify by Stephen Clay
 */
     
define( 'HMW_TITLE', 'Hide My WP'); 
define( 'HMW_VERSION', '3.00' );
define( 'HMW_SLUG', 'hide_my_wp'); //use _
define( 'HMW_PATH', dirname( __FILE__ ) );
define( 'HMW_DIR', basename( HMW_PATH ));
define( 'HMW_URL', plugins_url() . '/' . HMW_DIR );
define( 'HMW_FILE', plugin_basename( __FILE__ ) );

if (is_ssl()){
    define( 'HMW_WP_CONTENT_URL', str_replace ('http:','https:', WP_CONTENT_URL) );
    define( 'HMW_WP_PLUGIN_URL', str_replace ('http:','https:', WP_PLUGIN_URL) );    
}else {
    define( 'HMW_WP_CONTENT_URL', WP_CONTENT_URL );
    define( 'HMW_WP_PLUGIN_URL',  WP_PLUGIN_URL );    
}


 
class HideMyWP {
    const title = HMW_TITLE;
    const ver = HMW_VERSION;
    const slug = HMW_SLUG; 
    const path = HMW_PATH;
    const dir = HMW_DIR;
    const url= HMW_URL;
    const main_file= HMW_FILE;
    
    private $s;
    private $sub_folder;
    private $is_subdir_mu;  
    private $blog_path; 

    private $post_replace_old=array();
    private $post_replace_new=array();
    
    private $post_preg_replace_new=array();
    private $post_preg_replace_old=array();
    
    private $partial_replace_old=array();
    private $partial_replace_new=array();
    
    private $top_replace_old=array();
    private $top_replace_new=array();
    
    private $partial_preg_replace_new=array();
    private $partial_preg_replace_old=array();

    private $replace_old=array();
    private $replace_new=array();
    
    private $preg_replace_old=array();
    private $preg_replace_new=array();

    private $admin_replace_old=array();
    private $admin_replace_new=array();
    
   /**
   * HideMyWP::__construct() 
   * 
   * @return
   */
   function __construct() {
        
        //Let's start, Bismillah!
        register_activation_hook( __FILE__, array (&$this, 'on_activate_callback') );
        register_deactivation_hook( __FILE__, array (&$this, 'on_deactivate_callback') );

        $can_deactive= false;
        if (isset($_COOKIE['hmwp_can_deactivate']) && substr(NONCE_SALT,0,8) == $_COOKIE['hmwp_can_deactivate'])
            $can_deactive= true;

        //Fix a WP problem caused by filters order for deactivation
        if (isset($_GET['action']) && $_GET['action']=='deactivate' && isset($_GET['plugin']) && $_GET['plugin']==self::main_file && is_admin() && $can_deactive){
            //delete_option(self::slug);
        }

        if  ( (isset($_POST['action']) && $_POST['action']=='deactivate-selected') || (isset($_POST['action2']) && $_POST['action2']=='deactivate-selected') && is_admin() && $can_deactive){
            $plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
            foreach ($plugins as $plugin)
                if ($plugin==self::main_file)
                    delete_option(self::slug);
        }

        include_once('lib/class.helper.php') ;
        $this->h= new PP_Helper(self::slug, self::ver);
        $this->h->check_versions('5.0', '3.4');
        $this->h->register_messages();
        $sub_installation= trim(str_replace (home_url(),'',site_url()),' /');

        //include_once('lib/mute-screamer/mute-screamer.php') ;

        if ($sub_installation && substr($sub_installation, 0, 4)!='http')
            $this->sub_folder= $sub_installation . '/' ;

        $this->is_subdir_mu= false;
        if (is_multisite())
            $this->is_subdir_mu= true;
                if ((defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL) || (defined('VHOST') && VHOST == 'yes'))
                    $this->is_subdir_mu= false;

        if (is_multisite() && !$this->sub_folder && $this->is_subdir_mu)
             $this->sub_folder = ltrim(parse_url( trim( get_blog_option(BLOG_ID_CURRENT_SITE, 'home' ),'/').'/', PHP_URL_PATH ), '/');

        if (is_multisite() && !$this->blog_path && $this->is_subdir_mu) {
             global $current_blog;
             $this->blog_path = str_replace($this->sub_folder , '', $current_blog->path); //has /
        }
  
        if (is_admin())  {
            include_once('lib/class.settings-api.php') ;
            add_action( 'init', array( &$this, 'register_settings' ), 5 );
        }
                                   
        if (is_multisite())
            $this->options = get_blog_option(BLOG_ID_CURRENT_SITE, self::slug);
        else
            $this->options = get_option(self::slug);
     
        add_filter( 'pp_settings_api_filter', array( &$this, 'pp_settings_api_filter'), 100, 1);
        add_action( 'pp_settings_api_reset', array( &$this, 'pp_settings_api_reset'), 100, 1);
        add_action( 'init', array( &$this, 'init' ), 1);
        add_action( 'wp', array( &$this, 'wp' ) );
        add_action( 'generate_rewrite_rules', array( &$this, 'add_rewrite_rules'));
        add_filter( '404_template', array (&$this, 'custom_404_page'), 10, 1);    
        add_filter( 'the_content', array(&$this, 'post_filter'));
        add_action( 'admin_notices', array(&$this, 'admin_notices'));

        if (is_multisite())
	       add_action( 'network_admin_notices', array(&$this, 'admin_notices'));

        if ($this->opt('antispam'))
            add_action('pre_comment_on_post', array(&$this, 'spam_blocker'), 1);


        if ($this->opt('replace_mode')=='quick' && !is_admin()){  
            //root             
            add_filter('plugins_url', array(&$this, 'partial_filter'), 1000, 1);
            add_filter('bloginfo', array(&$this, 'partial_filter'), 1000, 1); 
            add_filter('stylesheet_directory_uri', array(&$this, 'partial_filter'), 1000, 1); 
            add_filter('template_directory_uri', array(&$this, 'partial_filter'), 1000, 1); 
            add_filter('script_loader_src', array(&$this, 'partial_filter'), 1000, 1); 
            add_filter('style_loader_src', array(&$this, 'partial_filter'), 1000, 1); 
            
            add_filter('stylesheet_uri', array(&$this, 'partial_filter'), 1000, 1);        
            add_filter('includes_url', array(&$this, 'partial_filter'), 1000, 1); 
            add_filter('bloginfo_url', array(&$this, 'partial_filter'), 1000, 1);
            
            if (!$this->is_permalink()){  
                add_filter('author_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('post_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('page_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('attachment_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('post_type_link', array(&$this, 'partial_filter'), 1000, 1);
                add_filter('get_pagenum_link', array(&$this, 'partial_filter'), 1000, 1);
                              
                add_filter('category_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('tag_link', array(&$this, 'partial_filter'), 1000, 1); 
                
                add_filter('feed_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('category_feed_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('tag_feed_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('taxonomy_feed_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('author_feed_link', array(&$this, 'partial_filter'), 1000, 1); 
                add_filter('the_feed_link', array(&$this, 'partial_filter'), 1000, 1);
  
            }
        }
                        
        if ($this->opt('email_from_name') )
			add_filter('wp_mail_from_name', array( &$this, 'email_from_name' ));
	  	
         
	  	if ($this->opt('email_from_address') )
            add_filter('wp_mail_from', array( &$this, 'email_from_address' ));
	  	
	  
        if ($this->opt('hide_wp_login')){
            add_action( 'site_url', array( &$this, 'add_login_key_to_action_from' ), 101, 4 );
            remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000);  
            add_filter('login_url', array( &$this,'add_key_login_to_url'), 101, 2);
            add_filter('logout_url', array( &$this,'add_key_login_to_url'), 101, 2);
            add_filter('lostpassword_url', array( &$this,'add_key_login_to_url'), 101, 2);  
            add_filter('register', array( &$this,'add_key_login_to_url'), 101, 2);

            add_filter('wp_logout', array( &$this,'correct_logout_redirect'), 101, 2);
        }    
            
       // if (!is_admin()){
            add_action('after_setup_theme',array(&$this, 'ob_starter') , 0);
           // add_action('shutdown', create_function('', 'return ob_end_flush();'));
      //  }

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_css_js' ) );
        // add_action( 'wp_enqueue_scripts', array( $this, 'css_js' ) );
        
        if (function_exists('bp_is_current_component'))
            add_action( 'bp_uri', array( $this, 'bp_uri' ) );

        if ($this->opt('replace_wpnonce')) {
            if (isset($_GET['_nonce']))
                $_GET['_wpnonce'] = $_GET['_nonce'];

            if (isset($_POST['_nonce']))
                $_POST['_wpnonce'] = $_POST['_nonce'];

            $this->preg_replace_old[]= "/('|\")_wpnonce('|\")/";
            $this->preg_replace_new[]= "'_nonce'";
        }

    }
    
    /**
     * HideMyWP::bp_uri()
     * Fix buddypress pages URL when page_base is enabled
     * 
     * @return
     */
    function bp_uri($uri){
        if(trim($this->opt('page_base') ,' /'))
            return str_replace(trim($this->opt('page_base') ,' /').'/','', $uri);
        else
            return $uri;
    }


    /**
     * HideMyWP::replace_admin_url()
     * Filter to replace old and new admin URL
     *
     * @return
     */
    function replace_admin_url($url, $path = '', $scheme='admin'){
        if (trim( $this->opt('new_admin_path') ,'/ ') && trim( $this->opt('new_admin_path') ,'/ ') != 'wp-admin' )
            $url = str_replace( 'wp-admin/', trim( $this->opt('new_admin_path') ,'/ ').'/', $url);
        return $url;
    }


    /**
     * HideMyWP::admin_notices() 
     * Displays necessary information in admin panel
     * 
     * @return
     */
    function admin_notices(){
        global $current_user;
		
        $this->h->update_pp_important_messages();
        $options_file = (is_multisite()) ? 'network/settings.php' : 'options-general.php';
        $page_url = admin_url(add_query_arg('page', self::slug, $options_file));

        //Update hmw_all_plugins list whenever a theme or plugin activate
        if ( (isset($_GET['page']) && ($_GET['page']==self::slug)) || isset($_GET['deactivate']) || isset($_GET['activate']) || isset($_GET['activated']) || isset($_GET['activate-multi']))
            update_option('hmw_all_plugins', array_keys(get_plugins()));    
        
        if (isset($_GET['page']) && $_GET['page']==self::slug && function_exists('bulletproof_security_load_plugin_textdomain')) {
            echo __('<div class="error"><p>You use BulletProof security plugin. To make it work correctly you need to configure Hide My WP manually. Click on <strong>"Manual Configuration"</strong> in Start tab. (If you did that ignore this message).', self::slug ) . '</p></div>';
        }

        if (isset($_GET['page']) && $_GET['page']==self::slug && isset($_GET['new_admin_action']) && $_GET['new_admin_action']=='configured'){

            if (is_multisite()) {
                $opts = get_blog_option(BLOG_ID_CURRENT_SITE, self::slug);
                $opts['new_admin_path']= get_option('hmwp_temp_admin_path');
                update_blog_option(BLOG_ID_CURRENT_SITE, self::slug, $opts);
            }else{
                $opts = get_option(self::slug);
                $opts['new_admin_path']= get_option('hmwp_temp_admin_path');
                update_option(self::slug, $opts);
            }
            wp_redirect(add_query_arg('new_admin_action', 'redirect_to_new', $page_url));
        }

        if (isset($_GET['page']) && $_GET['page']==self::slug && isset($_GET['new_admin_action']) && $_GET['new_admin_action']=='redirect_to_new'){
            wp_logout();
            wp_redirect(wp_login_url());
        }

        if (isset($_GET['page']) && $_GET['page']==self::slug && isset($_GET['new_admin_action']) && $_GET['new_admin_action']=="abort"){
            update_option('hmwp_temp_admin_path', $this->opt('new_admin_path'));
            wp_redirect(add_query_arg('new_admin_action', 'aborted_msg', $page_url));
        }

        $current_cookie = str_replace(SITECOOKIEPATH, '', ADMIN_COOKIE_PATH);

        //For non-sudomain and with pathes mu:
        if (!$current_cookie)
            $current_cookie ='wp-admin';

        if (!trim(get_option('hmwp_temp_admin_path'),' /') || trim(get_option('hmwp_temp_admin_path'),' /')=='wp-admin')
            $new_admin_path = 'wp-admin';
        else
            $new_admin_path = trim(get_option('hmwp_temp_admin_path'),' /');

        $admin_rule='';
        if ($new_admin_path && $new_admin_path!='wp-admin')
            $admin_rule ='RewriteRule ^'.$new_admin_path. '/(.*) /'. $this->sub_folder . 'wp-admin/$1 [QSA,L]'."\n";

        $multi_site_rule='';
        if (is_multisite())
            $multi_site_rule= "You also need to update your .htaccess file by adding following line  before 'RewriteCond REQUEST_FILENAME} !-f': <br><code>$admin_rule</code><br><br>";
//echo $current_cookie . ' sss '.$new_admin_path;

        if ($current_cookie != $new_admin_path && is_super_admin())
            if ($new_admin_path=='wp-admin')
                echo sprintf(__('<div class="error"><p><strong><span style="color: #ee0000">You MUST edit /wp-config.php to retun default admin path: </span></strong><br>  Open wp-config.php using FTP and <strong>DELETE or comment (//)</strong> line which start with: <br><code>define("ADMIN_COOKIE_PATH",  "...</code><br><br> <a class="button button-primary" href="%3$s">I don\'t know (Abort)</a> | <a class="button" href="%2$s">I did it! Login to new dashboard</a> </strong></p></div>', self::slug ) , '',  add_query_arg( array('new_admin_action'=>'configured'),  $page_url),    add_query_arg( array('new_admin_action'=>'abort'),  $page_url) ) ;
            else
                echo sprintf(__('<div class="error"><p><strong><span style="color: #ee0000">You MUST edit /wp-config.php to make new admin path work: </span><br>  Open wp-config.php using FTP and add following line somewhere before require_once(...) (or update it with new value): <br><code>define("ADMIN_COOKIE_PATH",  "%1$s");</code><br><br>%4$s<a class="button button-primary" href="%3$s">I don\'t know (Abort)</a> | <a class="button" href="%2$s">I did it! Login to new dashboard</a> </strong></p></div>', self::slug ) , preg_replace('|https?://[^/]+|i', '', get_option('siteurl') . '/' ) . $new_admin_path,  add_query_arg( array('new_admin_action'=>'configured'),  $page_url),    add_query_arg( array('new_admin_action'=>'abort'),  $page_url), $multi_site_rule) ;

        //Good place to flush! We really need this. 
        if (is_super_admin() && !function_exists('bulletproof_security_load_plugin_textdomain') && !$this->opt('customized_htaccess'))
            flush_rewrite_rules(true);

        if (is_multisite() && is_network_admin()) {
    		global $wpdb;
            $sites = $wpdb->get_results("SELECT blog_id, domain FROM {$wpdb->blogs} WHERE archived = '0' AND spam = '0' AND deleted = '0' ORDER BY blog_id");

    		//Loop through them
    		foreach ($sites as $site) {
			     global $wp_rewrite;
    			//switch_to_blog($site->blog_id);
			     delete_blog_option( $site->blog_id, 'rewrite_rules' );
    			//$wp_rewrite->init();
    			//$wp_rewrite->flush_rules();
    		}
    		//restore_current_blog();
            //flush_rewrite_rules(true);
    		//$wp_rewrite->init();
    	}

        $home_path = get_home_path();
       	if ( ( ! file_exists($home_path . '.htaccess') && is_writable($home_path) ) || is_writable($home_path . '.htaccess') )
            $writable = true;
        else
            $writable = false;
          
        if (isset($_GET['page']) && $_GET['page']==self::slug && !$this->is_permalink())
            if (!is_multisite())
                echo '<div class="error"><p>' . __('Your <a href="options-permalink.php">permalink structure</a> is off. In order to get all features of this plugin please enable it.', self::slug ) . '</p></div>';
            else
                echo '<div class="error"><p>' . __('Please enable WP permalink structure (Settings -> Permalink ) in your sites.', self::slug ) . '</p></div>';    
        
        if (isset($_GET['page']) && $_GET['page']==self::slug && (isset($_GET['settings-updated']) || isset($_GET['settings-imported'])) && is_multisite() )
            echo '<div class="error"><p>' . __('You enabled Multisite! You need to (re)configure Hide My WP almost after each change. Go to start tab, click on Multisite Configuration and follow documentation', self::slug ) . '</p></div>';
            
        
        if (isset($_GET['page']) && $_GET['page']==self::slug && isset($_GET['settings-updated']) && (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') || stristr($_SERVER['SERVER_SOFTWARE'], 'wpengine')) )
            echo '<div class="error"><p>' . __('Your web server is Nginx! You need to (re)configure Hide My WP almost after each change. Go to start tab, click on Nginx Configuration and follow documentation', self::slug ) . '</p></div>';
            
        if (isset($_GET['page']) && $_GET['page']==self::slug && stristr($_SERVER['SERVER_SOFTWARE'], 'iis') || stristr($_SERVER['SERVER_SOFTWARE'], 'Windows') )
            echo '<div class="error"><p>' . __('It seems you use Windows web server! Hide My WP doesn\'t offically support IIS but you may still make it work! Read the <a target="_blank" href="http://codecanyon.net/item/hide-my-wp-no-one-can-know-you-use-wordpress/4177158/faqs/16072">FAQ</a>', self::slug ) . '</p></div>';
            
            
        if (isset($_GET['page']) && $_GET['page']==self::slug  && !$writable && !function_exists('bulletproof_security_load_plugin_textdomain'))
            echo '<div class="error"><p>' . __('It seems there is no writable htaccess file in your WP directory. In order to get all features of this plugin please change permission of your htaccess file.', self::slug ) . '</p></div>';
                
                                                      
        if (basename($_SERVER['PHP_SELF']) == 'options-permalink.php' && $this->is_permalink() && isset($_POST['permalink_structure']))
            echo '<div class="updated"><p>' . sprintf(__('We are refreshing this page in order to fully implement changes. %s', self::slug ), '<a href="options-permalink.php">Manual Refresh</a>' ). '<script type="text/JavaScript"><!--  setTimeout("window.location = \"options-permalink.php\";", 5000);   --></script></p> </div>';
            
            
        if (isset($_GET['page']) && $_GET['page']=="w3tc_minify")
            echo '<div class="error"><p>' . __('In order to enable minify beside Hide My WP you need a small change in W3 Total Cache. If you already did it ignore this message. <a target="_blank" href="http://codecanyon.net/item/hide-my-wp-no-one-can-know-you-use-wordpress/4177158/faqs/17774">Read more</a>', self::slug ) . '</p></div>'; 
            
                                           
           
    }
  
    /**
     * HideMyWP::email_from_name()
     * 
     * Change mail name
     * @return
     */
  	function email_from_name(){
		return $this->opt('email_from_name');
  	}
  
     /**
     * HideMyWP::email_from_address()
     * 
     * Change mail address
     * @return
     */
  	function email_from_address(){
		return $this->opt('email_from_address');
  	}

   /**
     * HideMyWP::wp()
     * 
     * Disable WP components when permalink is enabled
     * @return
     */
    function wp(){
        
        if ((is_feed() || is_comment_feed())&& !isset($_GET['feed']) && !$this->opt('feed_enable'))
            $this->block_access();
        if (is_author() && !isset($_GET['author']) && !isset($_GET['author']) && !$this->opt('author_enable'))
            $this->block_access();
        if (is_search() && !isset($_GET['s']) && !$this->opt('search_enable'))
            $this->block_access();
        if (is_paged() && !isset($_GET['paged']) && !$this->opt('paginate_enable'))
            $this->block_access(); 
        if (is_page() && !isset($_GET['page_id']) && !isset($_GET['pagename']) && !$this->opt('page_enable'))
            $this->block_access();
        if (is_single() && !isset($_GET['p']) && !$this->opt('post_enable'))
            $this->block_access(); 
        if (is_category() && !isset($_GET['cat']) && !$this->opt('category_enable'))
            $this->block_access();
        if (is_tag() && !isset($_GET['tag']) && !$this->opt('tag_enable'))
            $this->block_access();
        if ((is_date() || is_time()) && !isset($_GET['monthnum']) && !isset($_GET['m'])  && !isset($_GET['w']) && !isset($_GET['second']) && !isset($_GET['year']) && !isset($_GET['day']) && !isset($_GET['hour']) && !isset($_GET['second']) && !isset($_GET['minute']) && !isset($_GET['calendar']) && $this->opt('disable_archive'))
            $this->block_access(); 
        if ((is_tax() || is_post_type_archive() || is_trackback() || is_comments_popup() || is_attachment()) && !isset($_GET['post_type']) && !isset($_GET['taxonamy']) && !isset($_GET['attachment']) && !isset($_GET['attachment_id']) && !isset($_GET['preview']) && $this->opt('disable_other_wp'))
            $this->block_access();
            
        if (!is_404() && !is_home() && (stristr($_SERVER['HTTP_USER_AGENT'], 'BuiltWith') || stristr($_SERVER['HTTP_USER_AGENT'], '2ip.ru')) )
            wp_redirect(home_url());


               
    }
    /**
     * HideMyWP::admin_css_js()
     * 
     * Adds admin.js to options page
     * @return
     */
    function admin_css_js(){
        
        if (isset($_GET['page']) && $_GET['page']==self::slug){
            wp_enqueue_script( 'jquery' );
    		wp_register_script( self::slug.'_admin_js', self::url. '/js/admin.js' , array('jquery'), self::ver, false );
            wp_enqueue_script(  self::slug.'_admin_js');
	    }
        
       //wp_register_style( self::slug.'_admin_css', self::url. '/css/admin.css', array(), self::ver, 'all' );
	   //wp_enqueue_style( self::slug.'_admin_css' );
    }

    /**
     * HideMyWP::pp_settings_api_reset()
     * Filter after reseting Options
     * @return
     */
    function pp_settings_api_reset(){
        flush_rewrite_rules();
        delete_option('hmw_all_plugins');
        delete_option('pp_important_messages');
        delete_option('hmwp_temp_admin_path');
    }

    /**
     * HideMyWP::pp_settings_api_filter()
     * Filter after updateing Options
     * @param mixed $post
     * @return
     */
    function pp_settings_api_filter($post){
        global $wp_rewrite;  
        if ((isset($post[self::slug]['admin_key']) && $this->opt('admin_key')!=$post[self::slug]['admin_key']) || (isset($post[self::slug]['login_query']) && $this->opt('login_query')!=$post[self::slug]['login_query']) ) {
          $body = "Hi-\nThis is %s plugin. Here is your new WordPress login address:\nURL: %s\n\nBest Regards,\n%s";
           
            if (isset($post[self::slug]['login_query']) && $post[self::slug]['login_query'])
                $login_query=  $post[self::slug]['login_query'];
            else
                $login_query = 'hide_my_wp';
            
            $new_url= site_url('wp-login.php');
            if ($this->h->str_contains($new_url, 'wp-login.php'))
       		   $new_url = add_query_arg($login_query, $post[self::slug]['admin_key'], $new_url);
            
            $body = sprintf(__($body, self::slug), self::title, $new_url, self::title );
            $subject = sprintf(__('[%s] Your New WP Login!', self::slug), self::title);
            wp_mail(get_option('admin_email'), $subject, $body);
        }

        if (!trim($this->opt('new_admin_path'), ' /') || trim($this->opt('new_admin_path'),' /') == 'wp-admin')
            $current_admin_path ='wp-admin';
        else
            $current_admin_path = trim($this->opt('new_admin_path'), ' /');

        if (isset($post['import_field']) && $post['import_field']) {
            $import_field = stripslashes($post['import_field']);
            $import_field = json_decode($import_field, true);
            $new_admin_path_input = (isset($import_field['new_admin_path'])) ? $import_field['new_admin_path'] : '';
        }else{
            $new_admin_path_input = (isset($post[self::slug]['new_admin_path'])) ? $post[self::slug]['new_admin_path'] : '';
        }

        if (!trim($new_admin_path_input, ' /') || trim($new_admin_path_input,' /') == 'wp-admin')
            $new_admin_path ='wp-admin';
        else
            $new_admin_path = trim($new_admin_path_input, ' /');

        if ($new_admin_path != $current_admin_path ) {
            //save temp value and return everything back whether it was enter by user or import fields
            if (isset($post['import_field']) && $post['import_field'])
                $post['import_field']=str_replace('\"new_admin_path\":\"'.$new_admin_path.'\"','\"new_admin_path\":\"'.$current_admin_path.'\"');
            else
                $post[self::slug]['new_admin_path'] = $current_admin_path;

            update_option('hmwp_temp_admin_path', $new_admin_path);
        }


        if (!is_multisite()) {
            $wp_rewrite->set_permalink_structure(trim($post[self::slug]['post_base'], ' ')); 
            $wp_rewrite->set_category_base(trim($post[self::slug]['category_base'], '/ ')); 
            $wp_rewrite->set_tag_base(trim($post[self::slug]['tag_base'], '/ '));
        }
        
        if (isset ($post[self::slug]['li']) && (strlen($post[self::slug]['li']) > 35 || strlen($post[self::slug]['li']) < 40))
            delete_option('pp_important_messages');
        
        flush_rewrite_rules();
         
        return $post;   
    }
    
    /**
     * HideMyWP::add_login_key_to_action_from()
     * Add admin key to links in wp-login.php
     * @param string $url
     * @param string $path
     * @param string $scheme
     * @param int $blog_id
     * @return
     */
    function add_login_key_to_action_from($url, $path, $scheme, $blog_id ){
        if ($this->opt('login_query'))
            $login_query = $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';
            
	  	if ($url && $this->h->str_contains($url, 'wp-login.php'))
        	if ($scheme=='login' || $scheme=='login_post' )
            	return add_query_arg($login_query, $this->opt('admin_key'), $url);
            
        return $url; 
    }

    /**
     * HideMyWP::add_key_login_to_url()
     * Add admin key to wp-login url
     * @param mixed $url
     * @param string $redirect
     * @return
     */
    function add_key_login_to_url($url, $redirect='0'){
        if ($this->opt('login_query'))
            $login_query = $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';
                
	  	if ($url && $this->h->str_contains($url, 'wp-login.php') && !$this->h->str_contains($url, $login_query))
       		return add_query_arg($login_query, $this->opt('admin_key'), $url);
            
        return $url;    
    }
    
    
    function correct_logout_redirect(){
        $url =  $_SERVER['PHP_SELF'];
        
        if ($this->opt('login_query'))
            $login_query = $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';
            
        if ($this->h->ends_with($url, 'wp-login.php') && isset($_REQUEST['action']) && $_REQUEST['action']=='logout') {
            $redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'wp-login.php?loggedout=true&'.$login_query.'='.$this->opt('admin_key');
        	wp_safe_redirect( $redirect_to );
        	exit();
        } 
    }
    
    /**
     * HideMyWP::ob_starter()
     * 
     * @return
     */
    function ob_starter(){
        return ob_start(array(&$this, "global_html_filter")) ;
    }
    
    /**
     * HideMyWP::custom_404_page()
     * 
     * @param mixed $templates
     * @return
     */
    function custom_404_page($templates){
        global $current_user;
        $visitor=esc_attr((is_user_logged_in()) ? $current_user->user_login : $_SERVER["REMOTE_ADDR"]);
        
        if (is_multisite())     
            $permalink = get_blog_permalink(BLOG_ID_CURRENT_SITE, $this->opt('custom_404_page')) ;
        else
            $permalink = get_permalink($this->opt('custom_404_page'));
            
        if ($this->opt('custom_404') && $this->opt('custom_404_page'))
            wp_redirect(add_query_arg( array('by_user'=>$visitor, 'ref_url'=> urldecode($_SERVER["REQUEST_URI"])), $permalink )) ;    
        else
            return $templates;
            
        die();            
        
    }
    
    /**
     * HideMyWP::do_feed_base()
     * 
     * @param boolean $for_comments
     * @return
     */
    function do_feed_base( $for_comments ) {
    	if ( $for_comments )
   		   load_template( ABSPATH . WPINC . '/feed-rss2-comments.php' );
    	else
	       load_template( ABSPATH . WPINC . '/feed-rss2.php' );
    }
    /**
     * HideMyWP::is_permalink()
     * Is permalink enabled?
     * @return
     */
    function is_permalink(){
        global $wp_rewrite;
        if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks())
            return false;
        return true;     
    }
    
    /**
     * HideMyWP::block_access()
     * 
     * @return
     */
    function block_access(){
        global $wp_query, $current_user;
        $visitor = esc_attr((is_user_logged_in()) ? $current_user->user_login : $_SERVER["REMOTE_ADDR"]);
        
        $url=esc_url('http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI']);
        // $wp_query->set('page_id', 2);
        // $wp_query->query($wp_query->query_vars);
       
        if ($this->opt('spy_notifier')) {
            $body = "Hi-\nThis is %s plugin. We guess someone is researching about your WordPress site.\n\nHere is some more details:\nVisitor: %s\nURL: %s\nUser Agent: %s\n\nBest Regards,\n%s";
            $body = sprintf(__($body, self::slug), self::title, $visitor, $url, $_SERVER['HTTP_USER_AGENT'], self::title);
            $subject = sprintf(__('[%s] Someone is mousing!', self::slug), self::title);
            wp_mail(get_option('admin_email'), $subject, $body);   
        }
                            
        status_header( 404 );
        nocache_headers();
        
        $headers = array('X-Pingback' => get_bloginfo('pingback_url'));
        $headers['Content-Type'] = get_option('html_type') . '; charset=' . get_option('blog_charset');
        foreach( (array) $headers as $name => $field_value )
			@header("{$name}: {$field_value}");

		//if ( isset( $headers['Last-Modified'] ) && empty( $headers['Last-Modified'] ) && function_exists( 'header_remove' ) )
		//	@header_remove( 'Last-Modified' );
            
            
        //wp-login.php wp-admin and direct .php access can not be implemented using 'wp' hook block_access can't work correctly with init hook so we use wp_remote_get to fix the problem 
        if ( $this->h->str_contains($_SERVER['PHP_SELF'], '/wp-admin/') || $this->h->ends_with($_SERVER['PHP_SELF'], '.php')) {  	
            
            $visitor=esc_attr((is_user_logged_in()) ? $current_user->user_login : $_SERVER["REMOTE_ADDR"]);
        
            if ($this->opt('custom_404') && $this->opt('custom_404_page') )   {
                wp_redirect(add_query_arg( array('by_user'=>$visitor, 'ref_url'=> urldecode($_SERVER["REQUEST_URI"])), get_permalink($this->opt('custom_404_page')))) ; 
            }else{
                $response = @wp_remote_get( home_url('/nothing_404_404') );
                
                if ( ! is_wp_error($response) )
                    echo $response['body'];
                else
                    wp_redirect( home_url('/404_Not_Found')) ;
            }

        }else{
            require_once( get_404_template() );      
        }  
                   
        die();
    }
    
    /**
     * HideMyWP::nice_search_redirect()
     * 
     * @return
     */
    function nice_search_redirect() {
        global $wp_rewrite;
        if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks()) 
            return;
        
        if ($this->opt('nice_search_redirect') && $this->is_permalink()){
            $search_base = $wp_rewrite->search_base;
                
            if (is_search() && strpos($_SERVER['REQUEST_URI'], "/{$search_base}/") === false) {
                if (isset($_GET['s']))
                    $keyword= get_query_var('s');
                
                if (isset($_GET[$this->opt('search_query')]))
                    $keyword= get_query_var($this->opt('search_query'));
                
                wp_redirect(home_url("/{$search_base}/" . urlencode($keyword)));
                exit();
            }
        }
    }


    /**
     * HideMyWP::remove_menu_class()
     * 
     * @param array $classes
     * @return
     */
    function remove_menu_class($classes) {
	  	$new_classes=array();
        if (is_array($classes)) {
             foreach($classes as $class){
                if ($this->h->starts_with( $class, 'current_'))
				  $new_classes[]=$class;
				 
             }
        }else{
            $new_classes='';
        }                   
        
        return $new_classes;   
	
    }


    /**
     * HideMyWP::partial_filter()
     * Filter partial HTML
     * @param mixed $content
     * @return
     */
    function partial_filter($content){

        if ($this->top_replace_old)
            $content = str_replace($this->top_replace_old, $this->top_replace_new, $content);
            
        if ($this->partial_replace_old)
            $content = str_replace($this->partial_replace_old, $this->partial_replace_new, $content);

        if ($this->partial_preg_replace_old)
            $content = preg_replace($this->partial_preg_replace_old, $this->partial_preg_replace_new, $content); 
            
        return $content;     
    }

    /**
     * HideMyWP::reverse_partial_filter()
     * Reverse partial Replace to fix W3 TotalCache Minification
     * @param mixed $content
     * @return
     */
    function reverse_partial_filter($content){
        
        if ($this->top_replace_old)
            $content = str_replace($this->top_replace_new, $this->top_replace_old, $content);
            
        if ($this->partial_replace_old)
            $content = str_replace($this->partial_replace_new, $this->partial_replace_old, $content);
            
        return $content;     
    }
        
    /**
     * HideMyWP::post_filter()
     * Filter post HTML
     * @param mixed $content
     * @return
     */
    function post_filter($content){
        if ($this->post_replace_old)
            $content = str_replace($this->post_replace_old, $this->post_replace_new, $content);
        
        if ($this->post_preg_replace_old)
            $content = preg_replace($this->post_preg_replace_old, $this->post_preg_replace_new, $content); 
            
        return $content;     
    }
    
    /**
     * HideMyWP::global_html_filter()
     * Filter output HTML
     * @param mixed $buffer
     * @return
     */
    function global_html_filter( $buffer){

        if (is_admin() && $this->admin_replace_old ) {
            $buffer = str_replace($this->admin_replace_old, $this->admin_replace_new, $buffer);
            return $buffer;
        }

        if (is_admin())
            return $buffer;

        if ($this->opt('remove_html_comments'))  {
            if ( $this->opt('remove_html_comments')=='simple')  {
                $this->preg_replace_old[]='/<!--(.*?)-->/';
                $this->preg_replace_new[]= ' ';
                $this->preg_replace_old[]="%(\n){2,}%";
                $this->preg_replace_new[]= "\n";

            }elseif ($this->opt('remove_html_comments')=='quick') {
                //comments and more than 2 space or line break will be remove. Simple & quick but not perfect! 
                $this->preg_replace_old[]='!/\*.*?\*/!s';
                $this->preg_replace_new[]=' ';
                $this->preg_replace_old[]='/\n\s*\n/';
                $this->preg_replace_new[]=' ';
                $this->preg_replace_old[]='/<!--(.*?)-->/';
                $this->preg_replace_new[]= ' ';
                $this->preg_replace_old[]="%(\s){3,}%";
                $this->preg_replace_new[]= ' ';
            }elseif ( $this->opt('remove_html_comments')=='safe')  {
                require_once('lib/class.HTML-minify.php');
                $min = new Minify_HTML($buffer, array('xhtml'=>true));
                $buffer = $min->process();
            }
        }
          
        if ($this->top_replace_old)
            $buffer = str_replace($this->top_replace_old, $this->top_replace_new, $buffer);
        
        
        if ($this->opt('replace_in_html')){
            $replace_in_html=$this->h->replace_newline(trim($this->opt('replace_in_html'),' '),'|');
            $replace_lines=explode('|', $replace_in_html);
            if ($replace_lines) {
                foreach ($replace_lines as $line)  {
                    $replace_word=explode('=', $line);
                    
                    if (isset($replace_word[0]) && isset($replace_word[1])) {
                        $replace_word[0]=str_replace('[equal]', '=', $replace_word[0]);
                        $replace_word[1]=str_replace('[equal]', '=', $replace_word[1]);
                        
                        $this->replace_old[]=trim($replace_word[0], ' ');
                        $this->replace_new[]=trim($replace_word[1], ' ');
                    }
                }                                                 
            }
        }
        
        
        if ($this->opt('replace_mode')=='safe' && $this->partial_replace_old)
            $buffer = str_replace($this->partial_replace_old, $this->partial_replace_new, $buffer);
        
        if ($this->opt('replace_mode')=='safe' && $this->partial_preg_replace_old)
            $buffer = preg_replace($this->partial_preg_replace_old, $this->partial_preg_replace_new, $buffer); 
            
        
        if ($this->replace_old)
            $buffer = str_replace($this->replace_old, $this->replace_new, $buffer);
        
        if ($this->preg_replace_old)
            $buffer = preg_replace($this->preg_replace_old, $this->preg_replace_new, $buffer); 
            
        return $buffer;        
        
    }
    /**
     * HideMyWP::remove_ver_scripts()
     * 
     * @param string $src
     * @return
     */
    function remove_ver_scripts($src){
        if ( strpos( $src, 'ver=' ) )
            $src = remove_query_arg( 'ver', $src );
        return $src;
    }


    /**
     * HideMyWP::spam_blocker()
     * Check queries before saving comment
     * @param string $src
     * @return
     */
    function spam_blocker($post_id){

        (array) $counter = get_option('hmwp_spam_counter');

        if ($this->opt('login_query'))
            $login_query = $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';

        $spam= false;
        if ($this->is_permalink() && $this->opt('replace_comments_post') && (!isset($_GET[$login_query]) || $_GET[$login_query]!=$this->opt('admin_key'))) {
            $counter['1']++;
            $spam = true;
        }

        if (!isset($_POST['authar'])){
            $counter['2']++;
            $spam = true;
        }

        if ($spam){
            update_option('hmwp_spam_counter', $counter);
            die('You\re spam! Isn\'t you?');
        }

        if (isset($_POST['authar']) && $_POST['authar'])
            $_POST['author'] = $_POST['authar'];

    }


    /**
     * HideMyWP::global_css_filter()
     * Generate new style from main file 
     * @return
     */
    function global_css_filter(){
        global $wp_query;
       
        $new_style_path=trim($this->opt('new_style_name'),' /'); 
        //$this->h->ends_with($_SERVER["REQUEST_URI"], 'main.css') ||   <- For multisite
        if ( (isset($wp_query->query_vars['style_wrapper']) && $wp_query->query_vars['style_wrapper'] && $this->is_permalink() )){
            
            if (is_multisite() && isset($wp_query->query_vars['template_wrapper']))      
                $css_file = str_replace(get_stylesheet(), $wp_query->query_vars['template_wrapper'], get_stylesheet_directory()).'/style.css'; 
            else
                $css_file = get_stylesheet_directory().'/style.css';    
            
          
            status_header( 200 );
            //$expires = 60*60*24; // 1 day
            $expires = 60*60*24*3; //3 day
            header("Pragma: public");
            header("Cache-Control: maxage=".$expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
            header('Content-type: text/css; charset=UTF-8');
            
            $css = file_get_contents($css_file);
            
            if ($this->opt('minify_new_style') )  {
                if ($this->opt('minify_new_style')=='quick' )  {
                    $to_remove=array ('%\n\r%','!/\*.*?\*/!s', '/\n\s*\n/',"%(\s){1,}%");
                    $css = preg_replace($to_remove, ' ', $css);
                }elseif ($this->opt('minify_new_style')=='safe') {
                    require_once('lib/class.CSS-minify.php');
                    $css = Minify_CSS_Compressor::process($css, array());    
                }
                                 
                
            }
              
            if ($this->opt('clean_new_style') )  {
                if (strpos($css, 'alignright')===false ){  //Disable it if it uses import or so on
                    if (is_multisite()) {
                        $opts = get_blog_option(BLOG_ID_CURRENT_SITE, self::slug);
                        $opts['clean_new_style']='';
                        update_blog_option(BLOG_ID_CURRENT_SITE, self::slug, $opts);
                    }else{
                        $opts = get_option(self::slug);
                        $opts['clean_new_style']='';
                        update_option(self::slug, $opts);
                    }        
                }else{
                    $old = array ('wp-caption', 'alignright', 'alignleft','alignnone', 'aligncenter');
                    $new = array ('x-caption', 'x-right', 'x-left','x-none', 'x-center');
                    $css = str_replace($old, $new, $css);
                }
			    //We replace HTML, too 
            }
            
           // if (is_child_theme())
           //     $css = str_replace('/thematic/', '/parent/', $css);
                 
            echo $css;
            
            //  if(extension_loaded('zlib'))
            //     ob_end_flush();
               
            exit;    
        }
               
    }
    
    /**
     * HideMyWP::init()
     * 
     * @return
     */
    function init(){                  
        global $wp_rewrite,$wp,$wp_roles,$wp_query, $current_user;
        load_plugin_textdomain(self::slug, FALSE, self::dir.'/lang/');
         

/*
if (!is_admin()) {
	echo 'ffff blog_path: '.  $this->blog_path .' sub_folder:'. $this->sub_folder ;
	echo "\n".'testcontent:'. get_option('test');
 } */



  
        if ($wp_roles){
            $wp_roles->add_cap( 'administrator', self::slug . '_trusted');  
            if ( $this->opt('trusted_user_roles') )  {
                foreach ($this->opt('trusted_user_roles') as $trusted_role) 
                    $wp_roles->add_cap( $trusted_role, self::slug . '_trusted');    
            }    
        }                          
                
        if ($this->opt('login_query'))
            $login_query = $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';
        
        $is_trusted=false;   
        if (current_user_can(self::slug . '_trusted') || (isset($_GET[$login_query]) && $_GET[$login_query]==$this->opt('admin_key')) )
            $is_trusted=true;


        $new_admin_path = (trim($this->opt('new_admin_path'), ' /')) ? trim($this->opt('new_admin_path'), ' /') : 'wp-admin';

        if (trim($this->opt('new_admin_path'), ' /') && trim($this->opt('new_admin_path'),' /') != 'wp-admin') {
            $_SERVER['REQUEST_URI'] = $this->replace_admin_url($_SERVER['REQUEST_URI']);
            add_filter( 'admin_url', array (&$this, 'replace_admin_url'), 100, 3);
        }


        if (current_user_can( 'activate_plugins' ))
            setcookie("hmwp_can_deactivate", substr(NONCE_SALT, 0, 8), time()+3600);  
        
        if ($this->opt('remove_ver_scripts')) {
            add_filter( 'style_loader_src', array( &$this, 'remove_ver_scripts'), 9999 );
            add_filter( 'script_loader_src', array( &$this, 'remove_ver_scripts'), 9999 ); 
        }
        
        
        if ($this->opt('remove_default_description') )       
            add_filter('get_bloginfo_rss',  array( &$this, 'remove_default_description'));
        
        
        if ($this->opt('nice_search_redirect') && $this->is_permalink()) 
            add_action('template_redirect', array( &$this, 'nice_search_redirect'));
        

        //prioty 1 let other plugin add something to it. or delte it entirely. 
        if ($this->opt('remove_menu_class') )  {
            add_filter('nav_menu_css_class', array( &$this, 'remove_menu_class'), 9);
            add_filter('nav_menu_item_id', array( &$this,'remove_menu_class'), 9);
            add_filter('page_css_class', array( &$this,'remove_menu_class'), 9);    
        }
            
                           
        if ($this->opt('remove_body_class') )
            add_filter('body_class', array(&$this, 'body_class_filter'), 9);
            
        if ($this->opt('clean_post_class') )
            add_filter('post_class', array(&$this, 'post_class_filter') , 9);
            
   
        if ($this->opt('hide_admin_bar') && !$is_trusted)  
            add_filter( 'show_admin_bar', '__return_false' );

        $feed_base = trim($this->opt('feed_base'), '/ ');
        if ($this->opt('disable_canonical_redirect') || ($feed_base && $this->h->str_contains($_SERVER['REQUEST_URI'], $feed_base, false)))
            add_filter('redirect_canonical', create_function('','return false;'), 101 , 2);
        
        
        //Fix W3 Total Cache Minification without rewrite rule
        if (defined('W3TC'))
            add_filter('plugins_url', array(&$this, 'partial_filter'), 1000, 1);
        
        
        //Remove W3 Total Cache Comments for untrusteds
        if (defined('W3TC')) 
            if ($this->opt('remove_html_comments') || !$is_trusted)
                add_filter('w3tc_can_print_comment',  create_function('','return false;'));
        
              
        $feed_enable=$this->opt('feed_enable');
         
        if (!$feed_enable && !is_admin()) {
            unset($_GET['feed']);
            unset($_GET[$this->opt('feed_query')]);	
            add_action('do_feed', array( &$this, 'block_access'), 1);
            add_action('do_feed_rdf', array( &$this, 'block_access'), 1);
            add_action('do_feed_rss',array( &$this, 'block_access'), 1);
            add_action('do_feed_rss2', array( &$this, 'block_access'), 1);
            add_action('do_feed_atom', array( &$this, 'block_access'), 1);
            
            //...and our own feed type!
            $new_feed_base= trim($this->opt('feed_base'), '/ ');
            if ($new_feed_base) {
                add_action('do_feed_'.$new_feed_base, array( &$this, 'block_access'), 1);
            }
        }        
        if (!$feed_enable || $this->opt('remove_feed_meta')){
            remove_action('wp_head', 'feed_links', 2);
            //Remove automatic the links to the extra feeds such as category feeds.
            remove_action('wp_head', 'feed_links_extra', 3);
        }
         
        $new_feed_query= $this->opt('feed_query');
        if ($new_feed_query && $new_feed_query!='feed' && !is_admin()) {
            if (isset($_GET['feed']))
                unset($_GET['feed']);
             
            $wp->add_query_var($new_feed_query);
            if (isset($_GET[$new_feed_query]))
                $_GET['feed']=$_GET[$new_feed_query];

            if (!$this->is_permalink()){
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)(feed=)#';  //;&amp;
                $this->partial_preg_replace_new[]='$1'.$new_feed_query.'=' ;
            }
        }
         
        $new_feed_base= trim($this->opt('feed_base'), '/ ');	
         
		if ( $new_feed_base && 'feed' != $new_feed_base && $this->is_permalink() ) {
		    $wp_rewrite->feed_base = $new_feed_base;
            add_feed($new_feed_base, array(&$this, 'do_feed_base'));
            
            
            $this->partial_preg_replace_old[]= '#('.home_url().'/[0-9a-z_\-/.]*)(/feed)#';
            $this->partial_preg_replace_new[]= '$1/'.$new_feed_base ;
                
            //Remove default 'feed' type
            $feeds=$wp_rewrite->feeds;
            unset($feeds[0]);
            $wp_rewrite->feeds=$feeds;
		}
        
        $author_enable=$this->opt('author_enable');


        if (!$author_enable && !is_admin()) {
            unset($_GET['author']);
            unset($_GET['author_name']);
            unset($_GET[$this->opt('author_query')]);
        }  
              
        $new_author_query= $this->opt('author_query');
        if ($new_author_query && $new_author_query!='author' && !is_admin()) {
            if (isset($_GET['author']))
                unset($_GET['author']);
            
            if (isset($_GET['author_name']))
                unset($_GET['author_name']);
             
            $wp->add_query_var($new_author_query);
            
            if (isset($_GET[$new_author_query]) && is_numeric($_GET[$new_author_query]) )
                $_GET['author']=$_GET[$new_author_query];
            
            if (isset($_GET[$new_author_query]) && !is_numeric($_GET[$new_author_query]) )
                $_GET['author_name']=$_GET[$new_author_query];
            
            if (!$this->is_permalink()){
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)((author|author_name)=)#';
                $this->partial_preg_replace_new[]='$1'.$new_author_query.'=' ;
            }
        }

        if ($this->opt('antispam')) {
            if (isset($_GET['authar']) && $_GET['authar']){
                $_GET['author'] = $_GET['authar'];
            }
        }

        $new_author_base= trim($this->opt('author_base'), '/ ');	

		if ( $new_author_base && 'author' != $new_author_base && $this->is_permalink()) {
		    $wp_rewrite->author_base = $new_author_base;
            
            //Not require in most cases! 
            //$this->preg_replace_old[]= '#('.home_url().'/)(author/)([0-9a-z_\-/.]+)#';
            //$this->preg_replace_new[]= '$1'.$new_author_base.'/'.'$3' ; 
		}
        
        
        if ($this->opt('author_without_base') && $this->is_permalink())  {
            $wp_rewrite->author_structure = $wp_rewrite->root  . '/%author%' ;
        
        }
        
        $search_enable=$this->opt('search_enable');
       
        if (!$search_enable && !is_admin()) {
            unset($_GET['s']);
            unset($_GET[$this->opt('search_query')]);	
        }  
              
        $new_search_query= $this->opt('search_query');
 
        if ($new_search_query && $new_search_query!='s' && !is_admin()) {
            if (isset($_GET['s']))
                unset($_GET['s']);
             
            $wp->add_query_var($new_search_query);
            
            if (isset($_GET[$new_search_query]) )
                $_GET['s']=$_GET[$new_search_query];
            
           
                //Not require in most cases!
                //$this->preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)(s=)#';
                //$this->preg_replace_new[]='$1'.$new_search_query.'=' ;
                //echo $new_search_query;
                  
                $this->preg_replace_old[]= "/name=('|\")s('|\")/";
                $this->preg_replace_new[]= "name='".$new_search_query."'";
                
            
            
            
        }
     
        $new_search_base= trim($this->opt('search_base'), '/ ');	

		if ( $new_search_base && 'search' != $new_search_base && $this->is_permalink()) {
		    $wp_rewrite->search_base = $new_search_base; 
		}
        
        
        
        $paginate_enable=$this->opt('paginate_enable');
       
        if (!$paginate_enable && !is_admin()) {
            unset($_GET['paged']);  
            unset($_GET[$this->opt('paginate_query')]);	
        }  
              
        $new_paginate_query= $this->opt('paginate_query');
        
        if ($new_paginate_query && $new_paginate_query!='paged' && !is_admin()) {
            if (isset($_GET['paged']))
                unset($_GET['paged']);
             
            $wp->add_query_var($new_paginate_query);
            
            if (isset($_GET[$new_paginate_query]) )
                $_GET['paged']=$_GET[$new_paginate_query];
            
            if (!$this->is_permalink()){
                //Fixed the bug. Here we delete new query that assume as current URL by WP
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)('.$new_paginate_query.'=[0-9&]+)#';
                $this->partial_preg_replace_new[]='$1';
                
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)(paged=)#';
                $this->partial_preg_replace_new[]='$1'.$new_paginate_query.'=' ;                
            }
        }
     
        $new_paginate_base= trim($this->opt('paginate_base'), '/ ');	

		if ( $new_paginate_base && 'page' != $new_paginate_base && $this->is_permalink()) {
		    $wp_rewrite->pagination_base = $new_paginate_base; 
		}
        
        
        
        $page_enable=$this->opt('page_enable');
       
        if (!$page_enable && !is_admin()) {
            unset($_GET['pagename']);
            unset($_GET['page_id']);
            unset($_GET[$this->opt('page_query')]);	
        }  
              
        $new_page_query= $this->opt('page_query');
        
        if ($new_page_query && $new_page_query!='page_id' && !is_admin()) {
            if (isset($_GET['page_id']))
                unset($_GET['page_id']);
            
            if (isset($_GET['pagename']))
                unset($_GET['pagename']);
                 
            $wp->add_query_var($new_page_query);
            
            if (isset($_GET[$new_page_query]) && is_numeric($_GET[$new_page_query]) )
                $_GET['page_id']=$_GET[$new_page_query];
            
            if (isset($_GET[$new_page_query]) && !is_numeric($_GET[$new_page_query]) )
                $_GET['pagename']=$_GET[$new_page_query];
            
            if (!$this->is_permalink()){
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)((page_id|pagename)=)#';
                $this->partial_preg_replace_new[]='$1'.$new_page_query.'=' ;                
            }
        }
     
        $new_page_base= trim($this->opt('page_base'), '/ ');	
        
		if ( $new_page_base && $this->is_permalink()) {
		  
		   $wp_rewrite->page_base = $new_page_base;
           $wp_rewrite->page_structure = $wp_rewrite->root .'/'.$new_page_base.'/'. '%pagename%'; 
           
		}
        
        $post_enable=$this->opt('post_enable');
       
        if (!$post_enable && !is_admin()) {
            unset($_GET['p']);
           
            unset($_GET[$this->opt('post_query')]);	
        }  
              
        $new_post_query= $this->opt('post_query');
        
        if ($new_post_query && $new_post_query!='p' && !is_admin() && !isset($_GET['preview'])) {
            $wp->add_query_var($new_post_query);
            
            if (isset($_GET['p']))
                unset($_GET['p']);
                
            if (isset($_GET[$new_post_query]) && is_numeric($_GET[$new_post_query]) )
                $_GET['p']=$_GET[$new_post_query];
            
            if (!$this->is_permalink()){
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)(p=)#';
                $this->partial_preg_replace_new[]='$1'.$new_post_query.'=' ;                
            } 
        }
        
      //Not work in multisite at all!  
      if (basename($_SERVER['PHP_SELF']) == 'options-permalink.php' && isset($_POST['permalink_structure']) ){
            $this->options['post_base'] = $_POST['permalink_structure'];
                update_option(self::slug, $this->options);
         
      } 
      
        
        $category_enable=$this->opt('category_enable');
       
        if (!$category_enable && !is_admin()) {
            unset($_GET['cat']);
            unset($_GET[$this->opt('category_name')]);	
        }  
              
        $new_category_query= $this->opt('category_query');
        
        if ($new_category_query && $new_category_query!='cat' && !is_admin()) {
            $wp->add_query_var($new_category_query);
            
            unset($_GET['cat']);
            unset($_GET['category_name']);
            if (isset($_GET[$new_category_query]) && is_numeric($_GET[$new_category_query]) )
                $_GET['cat']=$_GET[$new_category_query];
                
            if (isset($_GET[$new_category_query]) && !is_numeric($_GET[$new_category_query]) )
                $_GET['category_name']=$_GET[$new_category_query];
                
            if (!$this->is_permalink()){
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)((cat|category_name)=)#';
                $this->partial_preg_replace_new[]='$1'.$new_category_query.'=' ;                
            }
        }

        if (basename($_SERVER['PHP_SELF']) == 'options-permalink.php' && isset($_POST['category_base']) ){
            $this->options['category_base'] = $_POST['category_base'];
            update_option(self::slug, $this->options); 
        }
        
        $tag_enable=$this->opt('tag_enable');
       
        if (!$tag_enable && !is_admin()) {
            unset($_GET['tag']);	
        }  
              
        $new_tag_query= $this->opt('tag_query');
        
        if ($new_tag_query && $new_tag_query!='tag' && !is_admin()) {
            $wp->add_query_var($new_tag_query);
            
            unset($_GET['tag']);
            if (isset($_GET[$new_tag_query])  )
                $_GET['tag']=$_GET[$new_tag_query];
                
            if (!$this->is_permalink()){
                $this->partial_preg_replace_old[]='#('.home_url().'(/\?)[0-9a-z=_/.&\-;]*)(tag=)#';
                $this->partial_preg_replace_new[]='$1'.$new_tag_query.'=' ;                
            }
        }

        
        if (basename($_SERVER['PHP_SELF']) == 'options-permalink.php' && isset($_POST['tag_base']) ){
            $this->options['tag_base'] = $_POST['tag_base'];
            update_option(self::slug, $this->options); 
        } 
            
        
        if ($this->opt('disable_archive') && !is_admin()) {
            unset($_GET['year']);
            unset($_GET['m']);
            unset($_GET['w']);
            unset($_GET['day']);
            unset($_GET['hour']);
            unset($_GET['minute']);
            unset($_GET['second']);
            
            unset($_GET['calendar']);
            unset($_GET['monthnum']);
        }
        
        
        if ($this->opt('disable_other_wp') && !is_admin()) {
            unset($_GET['post_type']);
            unset($_GET['cpage']);
            unset($_GET['term']);
            unset($_GET['taxonomy']);   
            unset($_GET['robots']);
            
            unset($_GET['attachment_id']);
            unset($_GET['attachment']);
            
            unset($_GET['withcomments']);
            unset($_GET['withoutcomments']);
            
            unset($_GET['orderby']);
            unset($_GET['order']);
            
            //There's still a little more but we ignore them
        }
                	
        
        if ($this->opt('remove_other_meta')){
            //Remove generator name and version from your Website pages and from the RSS feed.
            add_filter('the_generator', create_function('', 'return "";'));
            //Display the XHTML generator that is generated on the wp_head hook, WP version
            remove_action( 'wp_head', 'wp_generator' ); 
            //Remove the link to the Windows Live Writer manifest file.
            remove_action('wp_head', 'wlwmanifest_link'); 
            //Remove EditURI
            remove_action('wp_head', 'rsd_link');
            //Remove index link.
            remove_action('wp_head', 'index_rel_link');
            //Remove previous link.
            remove_action('wp_head', 'parent_post_rel_link', 10, 0);      
            //Remove start link.
            remove_action('wp_head', 'start_post_rel_link', 10, 0);
            //Remove relational links (previous and next) for the posts adjacent to the current post.
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
            //Remove shortlink if it is defined.
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
            
            $this->replace_old[]='<link rel="profile" href="http://gmpg.org/xfn/11" />';
            $this->replace_new[]='';
            
            $this->replace_old[]='<link rel="pingback" href="'. get_bloginfo( 'pingback_url' ).'" />';
            $this->replace_new[]='';
             
            //Added from roots
            if (!class_exists('WPSEO_Frontend')) 
                remove_action('wp_head', 'rel_canonical'); 
        }
           
         if ( $this->opt('new_style_name') && $this->opt('new_style_name')!='style.css' && $this->is_permalink() && !isset($_POST['wp_customize']) )  {
        	    
            $rel_style_path = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri().'/style.css'), '/');
            
            //style should be in theme directory.
            $new_style_path = trim($this->opt('new_theme_path'),' /') . '/' .trim($this->opt('new_style_name'), '/ ') ;
            $new_style_path = str_replace('.', '\.', $new_style_path) ;
            
             

            if (is_multisite()){
    
                $new_style_path = '/'.trim($this->opt('new_theme_path') , '/ ') .'/'.get_stylesheet(). '/' .trim($this->opt('new_style_name'), '/ ');

                $rel_theme_path_with_theme = trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
                $rel_style_path = $this->blog_path . $rel_theme_path_with_theme .'/style.css'; //without theme

                $wp->add_query_var('template_wrapper');
                
                //Fix a little issue with Multisite partial order
                $this->partial_replace_old[] = '/'.get_stylesheet().'/style.css';
                $this->partial_replace_new[] = '/'.get_stylesheet().'/'.str_replace('\.', '.', trim($this->opt('new_style_name'), '/ '));
            }else{
                $this->partial_replace_old[] = '/'.trim($this->opt('new_theme_path'),' /').'/style.css';
                $this->partial_replace_new[] = '/'. str_replace('\.', '.', $new_style_path);
            }
            
            $wp->add_query_var('style_wrapper');

            //This line doesn't work in multisite
     	    $wp_rewrite->add_rule($new_style_path, 'index.php?style_wrapper=true', 'top') ;
             
            $this->partial_replace_old[] = $rel_style_path;
            $this->partial_replace_new[] = str_replace('\.', '.', $new_style_path);
            
            add_action('wp', array( &$this, 'global_css_filter'));
            if ($this->opt('clean_new_style') )  {
                $old = array ('wp-caption', 'alignright', 'alignleft','alignnone', 'aligncenter');
                $new = array ('x-caption', 'x-right', 'x-left','x-none', 'x-center');

                $this->post_replace_old = array_merge($this->post_replace_old, $old);
                $this->post_replace_new = array_merge($this->post_replace_new, $new);
                
                $this->post_preg_replace_old[]='#wp\-(image|att)\-[0-9]*#';
                $this->post_preg_replace_new[]='';
                
            }
        }

            
        //echo '<pre>';
        //print_r($wp_rewrite);
        //echo '</pre>';
        
        
        //These 3 should be after page base so get_permalink in block access should work correctly
        if ($this->opt('hide_wp_admin') && !$is_trusted)  {
            if ( $this->h->str_contains($_SERVER['PHP_SELF'], '/wp-admin/') && trim($this->opt('new_admin_path'),' /')!='wp-admin' && !$this->h->str_contains($_SERVER['REQUEST_URI'], $this->opt('new_admin_path')) ) {
                if (!$this->h->ends_with($_SERVER['PHP_SELF'], '/admin-ajax.php')) {
                    $this->block_access();
                }        
            }
        }                           
        
        //$is_trusted: When user request xmlrpc.php current user will be set to 0 by WP so only admin key works    
        if ($this->opt('avoid_direct_access') && !$is_trusted)  {   
            if ( $this->h->ends_with($_SERVER['PHP_SELF'], '.php') && !$this->h->str_contains($_SERVER['PHP_SELF'], '/wp-admin/')) {
                $white_list= explode(",", $this->opt('direct_access_except'));
                $white_list[]='wp-login.php';
                $white_list[]='index.php';
                $block = true;
                
                foreach ($white_list as $white_file) {
                    if ($this->h->ends_with($_SERVER['PHP_SELF'], trim($white_file,', \r\n'))) 
                        $block= false;
                }
                
                if ($block)
                    $this->block_access();      
            }
        }
             
        if ($this->opt('hide_wp_login') && !$is_trusted)  {
             
            if ($this->h->ends_with($_SERVER['PHP_SELF'], '/wp-login.php') || $this->h->ends_with($_SERVER['PHP_SELF'], '/wp-login.php/')) { 
                $this->block_access();
            }                
        }
        
        //Fix a WooCommerce problem
        if (function_exists('woocommerce_get_page_id') && trim($this->opt('page_base'),' /') )  {
             $this->replace_old []= get_permalink(woocommerce_get_page_id('shop')); 
             $this->replace_new []= str_replace(trim($this->opt('page_base'),' /').'/', '', get_permalink(woocommerce_get_page_id('shop')));
        }
        
        //We only need replaces in this line. htaccess related works don't work here. They need flush and generate_rewrite_rules filter
	    $this->add_rewrite_rules($wp_rewrite);

     
    }
    /**
     * HideMyWP::remove_default_description()
     * 
     * @param mixed $bloginfo
     * @return
     */
    function remove_default_description($bloginfo) {
        return ($bloginfo == 'Just another WordPress site') ? '' : $bloginfo;
    }


        /**
     * HideMyWP::body_class_filter()
     * Only store page class
     * @param mixed $bloginfo
     * @return                                          
     */                                                   
    function body_class_filter($classes){
        $new_classes=array();
        if (is_array($classes)) {
             foreach($classes as $class){
                if ( $class=='home' || $class=='blog' || $class=='category' || $class=='tag' || $class=='rtl' || $class=='author' || $class=='archive' || $class=='single' || $class=='attachment' || $class=='search' || $class=='custom-background')
                    $new_classes[]=$class;
				 
             }
        }else{
            $new_classes='';
        }                   
        
        return $new_classes; 
    }
    
    /**
     * HideMyWP::post_class_filter()
     * Only store post format, post_types and sticky
     * @param mixed $bloginfo
     * @return                                          
     */    
    function post_class_filter($classes){
        $post_types=get_post_types();  
        $new_classes=array();
        if (is_array($classes)) {
             foreach($classes as $class){
                if ( ($class!='format-standard' && $this->h->starts_with( $class, 'format-')) || $class=='sticky')
                    $new_classes[]=$class;
                foreach ($post_types as $post_type)
                    if ($class==$post_type)
				        $new_classes[]=$class;
				 
             }
        }else{
            $new_classes='';
        }                   
        
        return $new_classes; 
    }

    /**
     * HideMyWP::add_rewrite_rules()
     * 
     * @param mixed $wp_rewrite
     * @return
     */
    function add_rewrite_rules( $wp_rewrite ) 
    {  
        global $wp_rewrite, $wp;
                          
        if (is_multisite()){
	    global $current_blog;
            $sitewide_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ));
            $active_plugins= array_merge((array) get_blog_option(BLOG_ID_CURRENT_SITE, 'active_plugins'), $sitewide_plugins);
        }else{
            $active_plugins = get_option('active_plugins');
        }
        
           
        if ($this->opt('rename_plugins')=='all')
             $active_plugins = get_option('hmw_all_plugins');
  
        
        if ($this->opt('replace_urls')){
            $replace_urls=$this->h->replace_newline(trim($this->opt('replace_urls'),' '),'|');
            $replace_lines=explode('|', $replace_urls);
            if ($replace_lines) {
                foreach ($replace_lines as $line)  {
                    
                    $replace_word = explode('==', $line);
                    if (isset($replace_word[0]) && isset($replace_word[1])) {
                        
                        //Check whether last character is / or not to recgnize folders
                        $is_folder= false;
                        if (substr($replace_word[0], strlen($replace_word[0])-1 , strlen($replace_word[0]))=='/')
                            $is_folder= true;

                        $replace_word[0]=trim($replace_word[0], '/ ');
                        $replace_word[1]=trim($replace_word[1], '/ ');
                            
                        $is_block= false;    
                        if ($replace_word[1] == 'nothing_404_404') 
                            $is_block= true;
                        
                        
                        if (!$is_block) {   
                            $this->top_replace_old[]=$replace_word[0];
                            $this->top_replace_new[]=$replace_word[1];                    
                        }
                        
                        if ($is_block){
                            //Swap words to make theme unavailable
                            $temp = $replace_word[0];
                            $replace_word[0] = $replace_word[1];
                            $replace_word[1] = $temp;    
                        }

                        $replace_word[0] = str_replace(array( 'amp;', '%2F','//', '.' ), array('', '/', '/','.'), $replace_word[0]);
                        $replace_word[1] = str_replace(array('.','amp;'), array('\.',''), $replace_word[1]);
                            
                        if ($is_folder){
                            $new_non_wp_rules[$replace_word[1].'/(.*)'] = $this->sub_folder . $replace_word[0].'/$1';
                        }else{
                            $new_non_wp_rules[$replace_word[1]] = $this->sub_folder . $replace_word[0];
                        }
                    }
                }                                                 
            }
        }
     
           
        //Order is important 
        if ($this->opt('rename_plugins') && $this->opt('new_plugin_path') && $this->is_permalink()) {
            foreach ((array) $active_plugins as $active_plugin)  {

                //Ignore itself or a plugin without folder 
                if ( !$this->h->str_contains($active_plugin,'/') || $active_plugin==self::main_file)
                    continue;
                    
                $new_plugin_path = trim($this->opt('new_plugin_path'), '/ ') ;
            
                //This is not just a line of code. I spent around 2 hours for this :|
                $codename_this_plugin=  hash('crc32', $active_plugin );
;
                $rel_this_plugin_path = trim(str_replace(site_url(),'', plugin_dir_url($active_plugin)), '/');
                //Allows space in plugin folder name 
                $rel_this_plugin_path= $this->sub_folder . str_replace(' ','\ ', $rel_this_plugin_path);  
                
                $new_this_plugin_path = $new_plugin_path . '/' . $codename_this_plugin ;
                $new_non_wp_rules[$new_this_plugin_path.'/(.*)'] = $rel_this_plugin_path.'/$1';
                
                if (is_multisite()){
                    $new_this_plugin_path = '/'.$new_this_plugin_path;
                    $rel_this_plugin_path = $this->blog_path . str_replace($this->sub_folder,'',$rel_this_plugin_path);  
                }
                    
                $this->partial_replace_old[]=$rel_this_plugin_path.'/';
                $this->partial_replace_new[]=$new_this_plugin_path.'/';
                
                if ($this->opt('replace_javascript_path')> 1) {
                    $this->replace_old[]= str_replace('/', '\/', $rel_this_plugin_path.'/');
                    $this->replace_new[]= str_replace('/', '\/', $new_this_plugin_path.'/');   
                }
                
    
            }
        }  
        
        if ($this->opt('new_include_path') && $this->is_permalink()){ 
            $rel_include_path = $this->sub_folder . trim(WPINC);
            $new_include_path = trim($this->opt('new_include_path'), '/ ') ;
            
            $new_non_wp_rules[$new_include_path.'/(.*)'] = $rel_include_path.'/$1';
            
            if (is_multisite()){
                $new_include_path = '/'.$new_include_path;
                $rel_include_path = $this->blog_path .str_replace($this->sub_folder,'',$rel_include_path);  
            }
            
            $this->partial_replace_old[]=$rel_include_path;
            $this->partial_replace_new[]=$new_include_path;
        }

        if ($this->opt('new_admin_path') && trim($this->opt('new_admin_path'), '/ ')!='wp-admin' && $this->is_permalink() ){
            $rel_admin_path = $this->sub_folder . 'wp-admin';
            $new_admin_path = trim($this->opt('new_admin_path'), '/ ') ;

            $new_non_wp_rules[$new_admin_path.'/(.*)'] = $rel_admin_path.'/$1';

            if (is_multisite()){
                $new_admin_path = '/'.$new_admin_path;
                $rel_admin_path = $this->blog_path .str_replace($this->sub_folder,'', $rel_admin_path);
            }
            //Add / to fix stylesheet and other 'wp-admin'
            //will break all Replace URLs to wp-admin plus all urls of it
            $this->admin_replace_old[]=$rel_admin_path .'/';
            $this->admin_replace_new[]=$new_admin_path.'/';


            //Fix config code for HMWP nginx / multisite, etc
            if (isset($_GET['page']) && $_GET['page']==self::slug) {
                $this->admin_replace_old[]=$new_admin_path .'/$';
                $this->admin_replace_new[]=$rel_admin_path .'/$';

                $this->admin_replace_old[]=$new_admin_path .'/admin-ajax.php [QSA';
                $this->admin_replace_new[]='wp-admin/admin-ajax.php [QSA';

                $this->admin_replace_old[]=$new_admin_path .'/(?!network';
                $this->admin_replace_new[]='wp-admin/(?!network';

                $this->admin_replace_old[]=$new_admin_path .'/admin-ajax.php last;';
                $this->admin_replace_new[]='wp-admin/admin-ajax.php last;';
            }






        }
        
        
        if ($this->opt('new_upload_path') && $this->is_permalink()){
            $upload_path=wp_upload_dir(); 
             
            if (is_ssl())
                $upload_path['baseurl']= str_replace('http:','https:', $upload_path['baseurl']);
                
            if (is_multisite() && $current_blog->blog_id!=BLOG_ID_CURRENT_SITE){
                 
                $upload_path_array = explode('/', $upload_path['baseurl']);
                array_pop($upload_path_array);
                array_pop($upload_path_array);
                $upload_path['baseurl'] = implode('/', $upload_path_array);
                
            }
              
            $rel_upload_path = $this->sub_folder . trim(str_replace(site_url(),'', $upload_path['baseurl']), '/');;
            $new_upload_path = trim($this->opt('new_upload_path'), '/ ') ; 
            $new_non_wp_rules[$new_upload_path.'/(.*)'] = $rel_upload_path.'/$1';
            
            if (is_multisite()){
		$rel_upload_path = str_replace($this->sub_folder,'',$rel_upload_path); 
                $new_upload_path = str_replace($this->blog_path, '/', home_url($new_upload_path)); 
            }
            
	    
            $this->replace_old[]= home_url($rel_upload_path) ;  //Fix external images problem
	
	    if (is_multisite())
            	$this->replace_new[]= $new_upload_path; //already added home_url!
	    else
		$this->replace_new[]= home_url($new_upload_path);
            
            if ($this->opt('replace_javascript_path')> 2) {
                $this->replace_old[]= str_replace('/', '\/', $rel_upload_path);
                $this->replace_new[]= str_replace('/', '\/', $new_upload_path);   
            }
        }
        
        
        if ($this->opt('new_plugin_path') && $this->is_permalink()){    
            $rel_plugin_path = $this->sub_folder .trim(str_replace(site_url(),'', HMW_WP_PLUGIN_URL), '/');
            
            $new_plugin_path = trim($this->opt('new_plugin_path'), '/ ') ;
            $new_non_wp_rules[$new_plugin_path.'/(.*)'] = $rel_plugin_path.'/$1';
            
            if (is_multisite()){
                $new_plugin_path = '/'.$new_plugin_path;
                $rel_plugin_path = $this->blog_path .str_replace($this->sub_folder,'', $rel_plugin_path);  
            }
            
            $this->partial_replace_old[]=$rel_plugin_path;
            $this->partial_replace_new[]=$new_plugin_path;
            
            if ($this->opt('replace_javascript_path')> 1) {
                $this->replace_old[]= str_replace('/', '\/', $rel_plugin_path);
                $this->replace_new[]= str_replace('/', '\/', $new_plugin_path);   
            }  
        }
        
        if ($this->opt('new_style_name') && $this->opt('new_theme_path')) {
                $new_style_path = trim($this->opt('new_theme_path'),' /') . '/' .trim($this->opt('new_style_name'), '/ ') ;
                $new_style_path = str_replace('.', '\.', $new_style_path) ;
                $rel_theme_path = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
                if ($this->sub_folder)
                    $new_non_wp_rules[$new_style_path] = add_query_arg('style_wrapper', '1', $this->sub_folder);
                else
                    $new_non_wp_rules[$new_style_path] = '/index.php?style_wrapper=1';    
        }
            
        
        if ($this->opt('new_theme_path') && $this->is_permalink() && !isset($_POST['wp_customize'])){
            $rel_theme_path = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
            
            $new_theme_path = trim($this->opt('new_theme_path'), '/ ') ;
            $new_non_wp_rules[$new_theme_path.'/(.*)'] = $rel_theme_path.'/$1';
            
            if (is_multisite()){   
                $new_theme_path = '/'.$new_theme_path;
                $rel_theme_path_with_theme = trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
                $rel_theme_path = $this->blog_path . str_replace('/'.get_stylesheet(), '', $rel_theme_path_with_theme); //without theme  
            }

            $this->partial_replace_old[]=$rel_theme_path;
            $this->partial_replace_new[]=$new_theme_path;
            
            if ($this->opt('replace_javascript_path')> 0) {
                $this->replace_old[]= str_replace('/', '\/', $rel_theme_path);
                $this->replace_new[]= str_replace('/', '\/', $new_theme_path);   
            }
                
            if (is_child_theme()){
                 //remove the end folder so we can replace it with parent theme
                $path_array =  explode('/', $new_theme_path) ; 
                array_pop($path_array);
                $path_string = implode('/', $path_array); 
                
                if ($path_string)
                    $path_string=$path_string.'/' ;
                            
                $parent_theme_new_path = $path_string .get_template() ;
                $rel_parent_theme_path = $this->sub_folder . trim(str_replace(site_url(),'', get_template_directory_uri()), '/');
                $parent_theme_new_path_with_main = $new_theme_path . '_main';
                
                $new_non_wp_rules[$parent_theme_new_path.'/(.*)'] = $rel_parent_theme_path.'/$1';
                $new_non_wp_rules[$parent_theme_new_path_with_main.'/(.*)'] = $rel_parent_theme_path.'/$1';
                
                if (!is_multisite())  {
                    $this->partial_replace_old[]=$rel_parent_theme_path;
                    $this->partial_replace_new[]=$parent_theme_new_path_with_main;
                }
                 
                if ($this->opt('replace_javascript_path')> 0) {
                    $this->replace_old[]= str_replace('/', '\/', $rel_parent_theme_path);
                    $this->replace_new[]= str_replace('/', '\/', $parent_theme_new_path_with_main);   
                }     
            }  
        }
                
       
        if ($this->opt('replace_admin_ajax') && trim($this->opt('replace_admin_ajax'), '/ ')!='admin-ajax.php' && trim($this->opt('replace_admin_ajax') )!='wp-admin/admin-ajax.php' && $this->is_permalink())  {
            $rel_admin_ajax = $this->sub_folder . 'wp-admin/admin-ajax.php';
            $new_admin_ajax = trim($this->opt('replace_admin_ajax'), '/ ');

            $admin_ajax = str_replace('.','\\.', $new_admin_ajax);
           
            $new_non_wp_rules[$admin_ajax] = $rel_admin_ajax;
            
            if (is_multisite()){
            	$rel_admin_ajax =  str_replace($this->sub_folder,'',$rel_admin_ajax);
		        $new_admin_ajax =  $new_admin_ajax;
            }

            $this->replace_old[]= $rel_admin_ajax;
            $this->replace_new[]= $new_admin_ajax; 
            
            $this->replace_old[]= str_replace('/', '\/', $rel_admin_ajax);
            $this->replace_new[]= str_replace('/', '\/', $new_admin_ajax);      
        }
         
        if ($this->opt('replace_comments_post') && trim($this->opt('replace_comments_post'), '/ ')!='wp-comments-post.php' && $this->is_permalink())        {

            $rel_comments_post = $this->sub_folder . 'wp-comments-post.php' ;
            $new_comments_post = trim($this->options['replace_comments_post'], '/ ');
            $comments_post = str_replace('.','\\.', $new_comments_post );

            if ($this->opt('login_query') && $this->opt('login_query'))
                $login_query=  $this->opt('login_query');
            else
                $login_query = 'hide_my_wp';

            if ($this->opt('antispam') && $this->opt('admin_key'))
                $antispam = '?'.$login_query.'='.$this->opt('admin_key');
            else
                $antispam = '';

            $new_non_wp_rules[$comments_post] = $rel_comments_post. $antispam;
            
            if (is_multisite()){
                $new_comments_post = $new_comments_post;
                $rel_comments_post = str_replace($this->sub_folder,'', $rel_comments_post);  
            }
            
            $this->replace_old[]= $rel_comments_post;
            $this->replace_new[]= $new_comments_post;      
        }

        if ($this->opt('antispam') ) {
            $this->preg_replace_old[]= "%name=('|\")author('|\")%";
            $this->preg_replace_new[]= "name='authar'";
        }
         
        if ($this->opt('hide_other_wp_files') && $this->is_permalink()){
            $rel_content_path = $this->sub_folder . trim(str_replace(site_url(),'', HMW_WP_CONTENT_URL), '/');
            $rel_plugin_path = $this->sub_folder . trim(str_replace(site_url(),'', HMW_WP_PLUGIN_URL), '/');
            $rel_include_path = $this->sub_folder .trim(WPINC);
            
            //Fix an anoying strange bug in some webhosts (bright).
            $screenshot='';
            if (! is_multisite()){
                $rel_theme_path_with_theme = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
                $rel_theme_path= str_replace('/'.get_stylesheet(), '', $rel_theme_path_with_theme);
                $screenshot = $rel_theme_path_with_theme.'/screenshot\.png|';
            }

            $style_path_reg='';
          //  if (!is_multisite() && $this->opt('new_style_name') && $this->opt('new_style_name') != 'style.css' && !isset($_POST['wp_customize']))
          //      $style_path_reg = '|'.$rel_theme_path_with_theme.'/style\.css';
                
            //|'.$rel_plugin_path.'/index\.php|'.$rel_theme_path.'/index\.php'    
            $new_non_wp_rules[$screenshot .$this->sub_folder .'readme\.html|'.$this->sub_folder .'license\.txt|'.$rel_content_path.'/debug\.log'.$style_path_reg.'|'.$rel_include_path.'/$'] = 'nothing_404_404';   
        }

        if ($this->opt('disable_directory_listing') && $this->is_permalink()) {
            $rel_content_path = $this->sub_folder . trim(str_replace(site_url(),'', HMW_WP_CONTENT_URL), '/');
            $rel_include_path = $this->sub_folder .trim(WPINC);

            $new_non_wp_rules['((('.$rel_content_path.'|'.$rel_include_path.')/([A-Za-z1-9-_/]*))|(wp-admin/(?!network/)([A-Za-z1-9-_/]+)))(\.txt|/)$'] = 'nothing_404_404';
        }
        
        if ($this->opt('avoid_direct_access') )  {
            $rel_plugin_path = $this->sub_folder . trim(str_replace(site_url(),'', HMW_WP_PLUGIN_URL), '/');
            $rel_theme_path_with_theme = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
    
            $white_list= explode(",", $this->opt('direct_access_except'));
            $white_list[]='wp-login.php';
            $white_list[]='index.php';
            $white_list[]='wp-admin/';
            
            if ($this->opt('exclude_theme_access'))
                $white_list[]= $rel_theme_path_with_theme.'/';
            if ($this->opt('exclude_plugins_access'))
                $white_list[]= $rel_plugin_path.'/';
            
            $block = true;
            $white_regex = '';
            foreach ($white_list as $white_file) {
                 $white_regex.= $this->sub_folder . str_replace(array('.', ' '), array('\.',''), $white_file ).'|';  //make \. remove spaces 
            }
            $white_regex=substr($white_regex, 0 ,strlen($white_regex)-1); //remove last |
            $white_regex = str_replace(array("\n", "\r\n", "\r"), '', $white_regex);
            //ToDo: Maybe this is a better rule. but harder to implement with WP (Because of RewriteCond): 
            //RewriteCond %{REQUEST_URI} !(index\.php|wp-content/repair\.php|wp-includes/js/tinymce/wp-tinymce\.php|wp-comments-post\.php|wp-login\.php|index\.php|wp-admin/)(.*)
            
            $new_non_wp_rules['('.$white_regex.')(.*)'] = '$1$2';
            $new_non_wp_rules[$this->sub_folder . '(.*)\.php$'] = 'nothing_404_404';
            
            add_filter('mod_rewrite_rules', array(&$this, 'mod_rewrite_rules'),10, 1);      
        }
        
        
        if (isset($new_non_wp_rules) && $this->is_permalink())        
            $wp_rewrite->non_wp_rules = array_merge($wp_rewrite->non_wp_rules, $new_non_wp_rules);
            
        return $wp_rewrite;              

    }
    /**
     * HideMyWP::mod_rewrite_rules()
     * Fix WP generated rules
     * @param mixed $key
     * @return
     */                       
    function mod_rewrite_rules($rules){
        $home_root = parse_url(home_url());
		if ( isset( $home_root['path'] ) )
			$home_root = trailingslashit($home_root['path']);
		else
			$home_root = '/';
            
        $rules=str_replace('(.*) '.$home_root.'$1$2 ', '(.*) $1$2 ', $rules);    
        
        return $rules;    
    }


	/**
	 * HideMyWP::on_activate_callback()
	 * 
	 * @return
	 */
	function on_activate_callback() {
        flush_rewrite_rules();
	}
	
	/**
	 * Register deactivation hook
	 * HideMyWP::on_deactivate_callback()
	 * 
	 * @return
	 */
	function on_deactivate_callback() {
        flush_rewrite_rules();
	} 
    
    /**
     * HideMyWP::opt()
     * Get options value
     * @param mixed $key
     * @return
     */
    function opt($key){
        if (isset($this->options[$key]))
            return $this->options[$key];
        return false;  
    }    
    
    function nginx_config(){
        $new_theme_path = trim($this->opt('new_theme_path') ,'/ ') ;
        $new_plugin_path = trim($this->opt('new_plugin_path') ,'/ ') ;
        $new_upload_path = trim($this->opt('new_upload_path') ,'/ ') ;
        $new_include_path = trim($this->opt('new_include_path') ,'/ ') ;
        $new_style_name = trim($this->opt('new_style_name') ,'/ ') ;
        $new_admin_path = trim($this->opt('new_admin_path') ,'/ ') ;

        $replace_admin_ajax = trim($this->opt('replace_admin_ajax'), '/ ') ;
        $replace_admin_ajax_rule = str_replace('.','\\.', $replace_admin_ajax) ;
        $replace_comments_post= trim($this->opt('replace_comments_post'), '/ ') ;
        $replace_comments_post_rule = str_replace('.','\\.', $replace_comments_post) ;
                
        $upload_path=wp_upload_dir();
        
        //not required for nginx
        $sub_install='';
        
        if (is_ssl())
                $upload_path['baseurl']= str_replace('http:','https:', $upload_path['baseurl']);
                        
        $rel_upload_path = $this->sub_folder . trim(str_replace(site_url(),'', $upload_path['baseurl']), '/');
        $rel_include_path = $this->sub_folder . trim(WPINC); 
        $rel_plugin_path = $this->sub_folder .trim(str_replace(site_url(),'', HMW_WP_PLUGIN_URL), '/');
        $rel_theme_path = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
        $rel_comments_post = $this->sub_folder . 'wp-comments-post.php';
        $rel_admin_ajax = $this->sub_folder . 'wp-admin/admin-ajax.php';
        
        
        $rel_content_path = $this->sub_folder . trim(str_replace(site_url(),'', HMW_WP_CONTENT_URL), '/');
        $rel_theme_path_no_template = str_replace('/'.get_stylesheet(), '', $rel_theme_path);
        
        $style_path_reg='';
        //if ($this->opt('new_style_name') && $this->opt('new_style_name') != 'style.css' && !isset($_POST['wp_customize']))
        //    $style_path_reg = '|'.$rel_theme_path.'/style\.css';
            
        //|'.$rel_plugin_path.'/index\.php|'.$rel_theme_path_no_template.'/index\.php'
    
        
	    $hide_other_file_rule = $this->sub_folder .'readme\.html|'.$this->sub_folder .'license\.txt|'.$rel_content_path.'/debug\.log'.$style_path_reg.'|'.$rel_include_path.'/$';

        $disable_directoy_listing = '((('.$rel_content_path.'|'.$rel_include_path.')/([A-Za-z1-9-_/]*))|(wp-admin/(?!network/)([A-Za-z1-9-_/]+)))(\.txt|/)$';

        if ($this->opt('login_query') && $this->opt('login_query'))
            $login_query=  $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';

        if ($this->opt('antispam') && $this->opt('admin_key'))
            $antispam = '?'.$login_query.'='.$this->opt('admin_key');
        else
            $antispam = '';

        if ($this->opt('avoid_direct_access')){
          
            $rel_theme_path_with_theme = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
    
            $white_list= explode(",", $this->opt('direct_access_except'));
            $white_list[]='wp-login.php';
            $white_list[]='index.php';
            $white_list[]='wp-admin/';
            
            if ($this->opt('exclude_theme_access'))
                $white_list[]= $rel_theme_path_with_theme.'/';
            if ($this->opt('exclude_plugins_access'))
                $white_list[]= $rel_plugin_path.'/';
                
            $block = true;
            $white_regex = '';
            foreach ($white_list as $white_file) {
                $white_regex.= $this->sub_folder . str_replace(array('.', ' '), array('\.',''), $white_file ).'|';  //make \. remove spaces 
            }
            $white_regex=substr($white_regex, 0 ,strlen($white_regex)-1); //remove last |
            $white_regex = str_replace(array("\r", "\r\n", "\n"), '', $white_regex);
        } 
          
        
        $output='';

        if ($this->opt('replace_urls')){
            $replace_urls=$this->h->replace_newline(trim($this->opt('replace_urls'),' '),'|');
            $replace_lines=explode('|', $replace_urls);
            if ($replace_lines) {
                foreach ($replace_lines as $line)  {
                    
                    $replace_word = explode('==', $line);
                    if (isset($replace_word[0]) && isset($replace_word[1])) {
                        
                        //Check whether last character is / or not to recgnize folders
                        $is_folder= false;
                        if (substr($replace_word[0], strlen($replace_word[0])-1 , strlen($replace_word[0]))=='/')
                            $is_folder= true;

                        $replace_word[0]=trim($replace_word[0], '/ ');
                        $replace_word[1]=trim($replace_word[1], '/ ');
                            
                        $is_block= false;    
                        if ($replace_word[1] == 'nothing_404_404') 
                            $is_block= true;
                        
                        
                        if ($is_block){
                            //Swap words to make theme unavailable
                            $temp = $replace_word[0];
                            $replace_word[0] = $replace_word[1];
                            $replace_word[1] = $temp;    
                        }

                        $replace_word[0] = str_replace(array( 'amp;', '%2F','//', '.' ), array('', '/', '/','.'), $replace_word[0]);
                        $replace_word[1] = str_replace(array('.','amp;'), array('\.',''), $replace_word[1]);
                            
                        if ($is_folder){
                            $output.='rewrite ^/'.$replace_word[1]. '/(.*) /'. $sub_install . $replace_word[0]. '/$1 last;'."\n";
                        }else{
                            $output.='rewrite ^/'.$replace_word[1]. ' /'. $sub_install . $replace_word[0]. ' last;'."\n";         
                        }
                    }
                }                                                 
            }
        }
        
        
	    if ( is_multisite() ){
            $sitewide_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ));
            $active_plugins= array_merge((array) get_blog_option(BLOG_ID_CURRENT_SITE, 'active_plugins'), $sitewide_plugins);
        }else{
            $active_plugins = get_option('active_plugins');
        }
        
        if ($this->opt('rename_plugins')=='all')
             $active_plugins = get_option('hmw_all_plugins');    
            
	    $pre_plugin_path='';
        if ($this->opt('rename_plugins') && $new_plugin_path) {
            foreach ((array) $active_plugins as $active_plugin)  {
                
                //Ignore itself or a plugin without folder 
                if ( !$this->h->str_contains($active_plugin,'/') || $active_plugin==self::main_file)
                    continue;
                    
                $new_plugin_path = trim($new_plugin_path, '/ ') ;
            
                //This is not just a line of code. I spent around 2 hours for this :|
                $codename_this_plugin=  hash('crc32', $active_plugin );
;
                $rel_this_plugin_path = trim(str_replace(site_url(),'', plugin_dir_url($active_plugin)), '/');
                //Allows space in plugin folder name 
                $rel_this_plugin_path = $this->sub_folder . str_replace(' ','\ ', $rel_this_plugin_path);  
                
                $new_this_plugin_path = $new_plugin_path . '/' . $codename_this_plugin ;
                $pre_plugin_path.= 'rewrite ^/'.$new_this_plugin_path. '/(.*) /'. $rel_this_plugin_path. '/$1 last;'."\n";  
            }
        }
                 
         
        
        
        if (is_child_theme()){
                 //remove the end folder of so we can replace it with parent theme
                $path_array =  explode('/', $new_theme_path) ; 
                array_pop($path_array);
                $path_string = implode('/', $path_array); 
                
                if ($path_string)
                    $path_string=$path_string.'/' ;
                            
                $parent_theme_new_path = $path_string .get_template() ;
                $rel_parent_theme_path = $this->sub_folder . trim(str_replace(site_url(),'', get_template_directory_uri()), '/');
                $output.='rewrite ^/'.$parent_theme_new_path. '/(.*) /'. $rel_parent_theme_path. '/$1 last;'."\n";
                $parent_theme_new_path_with_main = $new_theme_path . '_main';
                $output.='rewrite ^/'.$parent_theme_new_path_with_main. '/(.*) /'. $rel_parent_theme_path. '/$1 last;'."\n";
        }


        if ($new_admin_path && $new_admin_path!='wp-admin')
            $output.='rewrite ^/'.$new_admin_path. '/(.*) /'. $this->sub_folder. 'wp-admin/$1 last;'."\n";

        if ($new_include_path)
            $output.='rewrite ^/'.$new_include_path. '/(.*) /'. $rel_include_path. '/$1 last;'."\n";
                
        if ($new_upload_path)
            $output.='rewrite ^/'.$new_upload_path. '/(.*) /'. $rel_upload_path. '/$1 last;'."\n";
                 
        if ($new_plugin_path && $pre_plugin_path)
            $output.= $pre_plugin_path;
            
        if ($new_plugin_path)
            $output.='rewrite ^/'.$new_plugin_path. '/(.*) /'. $rel_plugin_path. '/$1 last;'."\n";
        
        if ($new_style_name)
            $output.='rewrite ^/'.$new_theme_path. '/'.str_replace('.','\.', $new_style_name).' /'.$rel_theme_path.'/style.css last;'."\n";
    
        if ($new_theme_path)
            $output.='rewrite ^/'.$new_theme_path. '/(.*) /'. $rel_theme_path. '/$1 last;'."\n";

        if ($replace_comments_post && $replace_comments_post != 'wp-comments-post.php')
            $output.='rewrite ^/'.$replace_comments_post_rule. ' /'. $rel_comments_post.$antispam. ' last;'."\n";
            
        if ($replace_admin_ajax_rule && $replace_admin_ajax_rule != 'wp-admin/admin-ajax.php')
            $output.='rewrite ^/'.$replace_admin_ajax_rule. ' /'. $rel_admin_ajax. ' last;'."\n";
            
        if ($this->opt('hide_other_wp_files'))
            $output.='rewrite ^/('.$hide_other_file_rule. ') /nothing_404_404'. ' last;'."\n";

        if ($this->opt('disable_directory_listing') )
            $output.='rewrite ^/'.$disable_directoy_listing. ' /nothing_404_404'. ' last;'."\n";

        if ($this->opt('avoid_direct_access')){
            $output.='rewrite ^/('.$white_regex.')(.*)'. ' /$1$2'. ' last;'."\n";
            $output.='rewrite ^/(.*).php$'. ' /nothing_404_404'. ' last;'."\n";
        }               
        
        if ($output)
            //$output='if (!-e $request_filename) {'. "\n" .  $output . "     break;\n}";
            $output="# BEGIN Hide My WP\n\n" . $output ."\n# END Hide My WP"; 
        else
            $output=__('Nothing to add for current settings.', self::slug);      
        
        $html='';
        $desc = __( 'Add to Nginx config file to get all features of the plugin. <br>', self::slug ) ;
        
        if (isset($_GET['nginx_config']) && $_GET['nginx_config'])  {
            
            $html= sprintf( '%s ', $desc );
            $html.= sprintf( '<span class="description">
        <ol style="color:#ff9900">
        <li>Nginx config file usually located in /etc/nginx/nginx.conf or /etc/nginx/conf/nginx.conf</li>
        <li>You may need to re-configure the server whenever you change settings or activate a new theme or plugin.</li>
        <li>If you use sub-directory for WP block you should add that directory before all of below pathes (e.g. rewrite ^/wordpress/lib/(.*) /wordpress/wp-includes/$1 or rewrite ^/wordpress/(.*).php$ /wordpress/nothing_404_404)</li></ol></span><textarea readonly="readonly" onclick="" rows="5" cols="55" class="regular-text %1$s" id="%2$s" name="%2$s" style="%4$s">%3$s</textarea>', 'nginx_config_class','nginx_config', esc_textarea($output), 'width:95% !important;height:400px !important' );
                                                     
            
        }else{
            $html= '<a href="'.add_query_arg(array('nginx_config'=>true)).'" class="button">'.__('Nginx Configuration', self::slug).'</a>' ;
            $html.= sprintf( '<br><span class="description"> %s</span>', $desc );
        }
        return $html;     
      //rewrite ^/assets/css/(.*)$ /wp-content/themes/roots/assets/css/$1 last;

      
    }
    
    function single_config(){
        $slashed_home      = trailingslashit( get_option( 'home' ) );
        $base = parse_url( $slashed_home, PHP_URL_PATH );     
                
        if (!$this->sub_folder && $base && $base!='/')
            $sub_install= trim($base,' /').'/';
        else
            $sub_install='';
            
        $new_theme_path = trim($this->opt('new_theme_path') ,'/ ') ;
        $new_plugin_path = trim($this->opt('new_plugin_path') ,'/ ') ;
        $new_upload_path = trim($this->opt('new_upload_path') ,'/ ') ;
        $new_include_path = trim($this->opt('new_include_path') ,'/ ') ;
        $new_style_name = trim($this->opt('new_style_name') ,'/ ') ;
        $new_admin_path = trim($this->opt('new_admin_path'), '/ ') ;

        $replace_admin_ajax = trim($this->opt('replace_admin_ajax'), '/ ') ;
        $replace_admin_ajax_rule = str_replace('.','\\.', $replace_admin_ajax) ;
        $replace_comments_post= trim($this->opt('replace_comments_post'), '/ ') ;
        $replace_comments_post_rule = str_replace('.','\\.', $replace_comments_post) ;
                
        $upload_path=wp_upload_dir();
        
        if (is_ssl())
                $upload_path['baseurl']= str_replace('http:','https:', $upload_path['baseurl']);
                
        $rel_upload_path = $sub_install . trim(str_replace(site_url(),'', $upload_path['baseurl']), '/');

        $rel_plugin_path = $sub_install .trim(str_replace(site_url(),'', HMW_WP_PLUGIN_URL), '/');
        $rel_theme_path = $sub_install . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
        $rel_comments_post = $sub_install . 'wp-comments-post.php';
        $rel_admin_ajax = $sub_install . 'wp-admin/admin-ajax.php';
        $rel_include_path2 = $sub_install . trim(WPINC); //To use in second part


        //Only use it if you want subfoler in first part
        $rel_include_path = $this->sub_folder . trim(WPINC);
        $rel_theme_path_with_subfolder= $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
        $rel_content_path = $this->sub_folder . trim(str_replace(site_url(),'', HMW_WP_CONTENT_URL), '/');
        $rel_theme_path_no_template = str_replace('/'.get_stylesheet(), '', $rel_theme_path);
        
        
        $style_path_reg='';
        //if ($new_style_name && $new_style_name != 'style.css' && !isset($_POST['wp_customize']))
         //   $style_path_reg = '|'.$rel_theme_path.'/style\.css';
            
        //|'.$rel_plugin_path.'/index\.php|'.$rel_theme_path_no_template.'/index\.php'    
        $hide_other_file_rule = $this->sub_folder .'readme\.html|'.$this->sub_folder .'license\.txt|'.$rel_content_path.'/debug\.log'.$style_path_reg.'|'.$rel_include_path.'/$';

        $disable_directoy_listing = '((('.$rel_content_path.'|'.$rel_include_path.')/([A-Za-z1-9-_/]*))|(wp-admin/(?!network/)([A-Za-z1-9-_/]+)))(\.txt|/)$';

        if ($this->opt('login_query') && $this->opt('login_query'))
            $login_query=  $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';

        if ($this->opt('antispam') && $this->opt('admin_key'))
            $antispam = '?'.$login_query.'='.$this->opt('admin_key');
        else
            $antispam = '';

        if ($this->opt('avoid_direct_access')){
            $rel_theme_path_with_theme = $sub_install . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
    
            $white_list= explode(",", $this->opt('direct_access_except'));
            $white_list[]='wp-login.php';
            $white_list[]='index.php';
            $white_list[]='wp-admin/';
            
            if ($this->opt('exclude_theme_access'))
                $white_list[]= $rel_theme_path_with_theme.'/';
            if ($this->opt('exclude_plugins_access'))
                $white_list[]= $rel_plugin_path.'/';
                
            $block = true;
            $white_regex = '';
            foreach ($white_list as $white_file) {
                $white_regex.= $sub_install . str_replace(array('.', ' '), array('\.',''), $white_file ).'|';  //make \. remove spaces 
            }
            $white_regex=substr($white_regex, 0 ,strlen($white_regex)-1); //remove last |
            $white_regex = str_replace(array("\n", "\r\n", "\r"), '', $white_regex);
        } 
        
        $output='';
        
        if ($this->opt('replace_urls')){
            $replace_urls=$this->h->replace_newline(trim($this->opt('replace_urls'),' '),'|');
            $replace_lines=explode('|', $replace_urls);
            if ($replace_lines) {
                foreach ($replace_lines as $line)  {
                    
                    $replace_word = explode('==', $line);
                    if (isset($replace_word[0]) && isset($replace_word[1])) {
                        
                        //Check whether last character is / or not to recgnize folders
                        $is_folder= false;
                        if (substr($replace_word[0], strlen($replace_word[0])-1 , strlen($replace_word[0]))=='/')
                            $is_folder= true;

                        $replace_word[0]=trim($replace_word[0], '/ ');
                        $replace_word[1]=trim($replace_word[1], '/ ');
                            
                        $is_block= false;    
                        if ($replace_word[1] == 'nothing_404_404') 
                            $is_block= true;
                        
                        
                        if ($is_block){
                            //Swap words to make theme unavailable
                            $temp = $replace_word[0];
                            $replace_word[0] = $replace_word[1];
                            $replace_word[1] = $temp;    
                        }

                        $replace_word[0] = str_replace(array( 'amp;', '%2F','//', '.' ), array('', '/', '/','.'), $replace_word[0]);
                        $replace_word[1] = str_replace(array('.','amp;'), array('\.',''), $replace_word[1]);
                            
                        if ($is_folder){
                            $output.='RewriteRule ^'.$replace_word[1]. '/(.*) /'. $sub_install . $replace_word[0]. '/$1 [QSA,L]'."\n";
                        }else{
                            $output.='RewriteRule ^'.$replace_word[1]. ' /'. $sub_install . $replace_word[0]. ' [QSA,L]'."\n";
                        }
                    }
                }                                                 
            }
        }
        
          
        $active_plugins = get_option('active_plugins');
        
        if ($this->opt('rename_plugins')=='all')
             $active_plugins = get_option('hmw_all_plugins');
            
	    $pre_plugin_path='';
        if ($this->opt('rename_plugins') && $new_plugin_path) {
            foreach ((array) $active_plugins as $active_plugin)  {
                
                //Ignore itself or a plugin without folder 
                if ( !$this->h->str_contains($active_plugin,'/') || $active_plugin==self::main_file)
                    continue;
                    
                $new_plugin_path = trim($new_plugin_path, '/ ') ;
            
                //This is not just a line of code. I spent around 2 hours for this :|
                $codename_this_plugin=  hash('crc32', $active_plugin );
;
                $rel_this_plugin_path = trim(str_replace(site_url(),'', plugin_dir_url($active_plugin)), '/');
                //Allows space in plugin folder name 
                $rel_this_plugin_path = $sub_install . str_replace(' ','\ ', $rel_this_plugin_path);  
                
                $new_this_plugin_path = $new_plugin_path . '/' . $codename_this_plugin ;
                $pre_plugin_path.= 'RewriteRule ^'.$new_this_plugin_path. '/(.*) /'. $rel_this_plugin_path. '/$1 [QSA,L]'."\n";
            }
        }
                 
        
        
        if (is_child_theme()){
                 //remove the end folder of so we can replace it with parent theme
                $path_array =  explode('/', $new_theme_path) ; 
                array_pop($path_array);
                $path_string = implode('/', $path_array); 
                
                if ($path_string)
                    $path_string=$path_string.'/' ;
                            
                $parent_theme_new_path = $path_string .get_template() ;
                $rel_parent_theme_path = $sub_install . trim(str_replace(site_url(),'', get_template_directory_uri()), '/');
                $output.='RewriteRule ^'.$parent_theme_new_path. '/(.*) /'. $rel_parent_theme_path. '/$1 [QSA,L]'."\n";
                $parent_theme_new_path_with_main = $new_theme_path . '_main';
                $output.='RewriteRule ^'.$parent_theme_new_path_with_main. '/(.*) /'. $rel_parent_theme_path. '/$1 [QSA,L]'."\n";          
        }

        if ($new_admin_path && $new_admin_path!='wp-admin' )
            $output.='RewriteRule ^'.$new_admin_path. '/(.*) /'. $sub_install. 'wp-admin/$1 [QSA,L]'."\n";


        if ($new_include_path)
            $output.='RewriteRule ^'.$new_include_path. '/(.*) /'. $rel_include_path2. '/$1 [QSA,L]'."\n";
                
        if ($new_upload_path)
            $output.='RewriteRule ^'.$new_upload_path. '/(.*) /'. $rel_upload_path. '/$1 [QSA,L]'."\n";
                 
        if ($new_plugin_path && $pre_plugin_path)
            $output.= $pre_plugin_path;
            
        if ($new_plugin_path)
            $output.='RewriteRule ^'.$new_plugin_path. '/(.*) /'. $rel_plugin_path. '/$1 [QSA,L]'."\n";
        
        if ($new_style_name)
            if ($sub_install)
                $output.='RewriteRule ^'.$new_theme_path. '/'.str_replace('.','\.', $new_style_name).' /'.add_query_arg('style_wrapper', '1', $sub_install).' [QSA,L]'."\n";
            else
                $output.='RewriteRule ^'.$new_theme_path. '/'.str_replace('.','\.', $new_style_name).' /index.php?style_wrapper=1'.' [QSA,L]'."\n";
    
        if ($new_theme_path)
            $output.='RewriteRule ^'.$new_theme_path. '/(.*) /'. $rel_theme_path. '/$1 [QSA,L]'."\n";
        
        if ($replace_comments_post && $replace_comments_post != 'wp-comments-post.php')
            $output.='RewriteRule ^'.$replace_comments_post_rule. ' /'. $rel_comments_post.$antispam. ' [QSA,L]'."\n";
            
        if ($replace_admin_ajax_rule && $replace_admin_ajax_rule != 'wp-admin/admin-ajax.php')
            $output.='RewriteRule ^'.$replace_admin_ajax_rule. ' /'. $rel_admin_ajax. ' [QSA,L]'."\n";
            
        if ($this->opt('hide_other_wp_files'))
            $output.='RewriteRule ^('.$hide_other_file_rule. ') /'.$sub_install.'nothing_404_404'. ' [QSA,L]'."\n";

        if ($this->opt('disable_directory_listing'))
            $output.='RewriteRule ^'.$disable_directoy_listing. ' /'.$sub_install.'nothing_404_404'. ' [QSA,L]'."\n";

        if ($this->opt('avoid_direct_access'))  {
            
            //RewriteCond %{REQUEST_URI} !(index\.php|wp-content/repair\.php|wp-includes/js/tinymce/wp-tinymce\.php|wp-comments-post\.php|wp-login\.php|index\.php|wp-admin/)(.*)
            
            $output.='RewriteCond %{REQUEST_URI} !('.$white_regex.')(.*)'."\n";
            $output.='RewriteRule ^(.*).php$'. ' /nothing_404_404'. ' [QSA,L]'."\n";
        }               
        
        if (!$output)
            $output=__('Nothing to add for current settings!', self::slug);
        else
            $output="# BEGIN Hide My WP\n\n" . $output ."\n# END Hide My WP";      
        
        $html='';
        $desc = __( 'In rare cases you need to configure it manually.<br>', self::slug ) ;
        
        if (isset($_GET['single_config']) && $_GET['single_config'])  { 
            $html= sprintf( ' %s ', $desc );
            $html.= sprintf( '<span class="description">
        <ol style="color:#ff9900">
             <li> If you use <strong>BulletProof Security</strong> plugin first secure htaccess file using it  and then add below lines to your htaccess file using FTP. </li>
            <li> You may need to re-configure server whenever you change settings or activate a new theme or plugin. </li>
            <li>Add these lines right before: <strong>RewriteCond %{REQUEST_FILENAME} !-f</strong>. Next you may want to change htaccess permission to read-only (e.g. 666)</li>
        </ol></span><textarea readonly="readonly" onclick="" rows="5" cols="55" class="regular-text %1$s" id="%2$s" name="%2$s" style="%4$s">%3$s</textarea>', 'single_config_class','single_config', esc_textarea($output), 'width:95% !important;height:400px !important' );
                                                     
            
        }else{
            $html= '<a href="'.add_query_arg(array('single_config'=>true)).'" class="button">'.__('Manual Configuration', self::slug).'</a>' ;
            $html.= sprintf( '<br><span class="description"> %s</span>', $desc );
        }
        return $html ;     
      //rewrite ^/assets/css/(.*)$ /wp-content/themes/roots/assets/css/$1 last;

      
    }
    
    
    
    function multisite_config(){
        $slashed_home      = trailingslashit( get_option( 'home' ) );
        $base = parse_url( $slashed_home, PHP_URL_PATH );

        $new_theme_path = trim($this->opt('new_theme_path') ,'/ ') ;
        $new_plugin_path = trim($this->opt('new_plugin_path') ,'/ ') ;
        $new_upload_path = trim($this->opt('new_upload_path') ,'/ ') ;
        $new_include_path = trim($this->opt('new_include_path') ,'/ ') ;
        $new_style_name = trim($this->opt('new_style_name') ,'/ ') ;
        $new_admin_path = trim($this->opt('new_admin_path'), '/ ') ;

        $replace_admin_ajax = trim($this->opt('replace_admin_ajax'), '/ ') ;
        $replace_admin_ajax_rule = str_replace('.','\\.', $replace_admin_ajax) ;
        $replace_comments_post= trim($this->opt('replace_comments_post'), '/ ') ;
        $replace_comments_post_rule = str_replace('.','\\.', $replace_comments_post) ;
                
        $upload_path=wp_upload_dir();
        
        if (is_ssl())
                $upload_path['baseurl']= str_replace('http:','https:', $upload_path['baseurl']);
                
        $rel_upload_path = $this->sub_folder . trim(str_replace(site_url(),'', $upload_path['baseurl']), '/');
        $rel_include_path = $this->sub_folder . trim(WPINC);
        $rel_plugin_path = $this->sub_folder .trim(str_replace(site_url(),'', HMW_WP_PLUGIN_URL), '/');
        $rel_theme_path = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
        $rel_comments_post = $this->sub_folder . 'wp-comments-post.php';
        $rel_admin_ajax = $this->sub_folder . 'wp-admin/admin-ajax.php';
        
        
        $rel_content_path = $this->sub_folder . trim(str_replace(site_url(),'', HMW_WP_CONTENT_URL), '/');
        $rel_theme_path_no_template = str_replace('/'.get_stylesheet(), '', $rel_theme_path);
        
        
        $style_path_reg='';
        //if ($new_style_name && $new_style_name != 'style.css' && !isset($_POST['wp_customize']))
         //   $style_path_reg = '|'.$rel_theme_path.'/style\.css';
            
        //|'.$rel_plugin_path.'/index\.php|'.$rel_theme_path_no_template.'/index\.php' 
		
		if (!$this->sub_folder && $base && $base!='/')
            $sub_install= trim($base,' /').'/';
        else
            $sub_install='';
			

        if ($this->is_subdir_mu)
             $hide_other_file_rule = 'readme\.html|'.'license\.txt|'.str_replace($this->sub_folder,'',$rel_content_path).'/debug\.log'. str_replace($this->sub_folder,'',$style_path_reg) .'|'.str_replace($this->sub_folder,'', $rel_include_path).'/$';
        else
             $hide_other_file_rule = $this->sub_folder .'readme\.html|'.$this->sub_folder .'license\.txt|'.$rel_content_path.'/debug\.log'.$style_path_reg.'|'.$rel_include_path.'/$';

        $disable_directoy_listing = '((('.$rel_content_path.'|'.$rel_include_path.')/([A-Za-z1-9-_/]*))|(wp-admin/(?!network/)([A-Za-z1-9-_/]+)))(\.txt|/)$';

        if ($this->opt('login_query') && $this->opt('login_query'))
            $login_query=  $this->opt('login_query');
        else
            $login_query = 'hide_my_wp';

        if ($this->opt('antispam') && $this->opt('admin_key'))
            $antispam = '?'.$login_query.'='.$this->opt('admin_key');
        else
            $antispam='';

        $output='';
        
        if ($this->opt('avoid_direct_access')){
            $rel_theme_path_with_theme = $this->sub_folder . trim(str_replace(site_url(),'', get_stylesheet_directory_uri()), '/');
    
            $white_list= explode(",", $this->opt('direct_access_except'));
            $white_list[]='wp-login.php';
            $white_list[]='index.php';
            $white_list[]='wp-admin/';
            
            if ($this->opt('exclude_theme_access'))
                $white_list[]= $rel_theme_path_with_theme.'/';
            if ($this->opt('exclude_plugins_access'))
                $white_list[]= $rel_plugin_path.'/';
                
            $block = true;
            $white_regex = '';
            foreach ($white_list as $white_file) {
                $white_regex.= $this->sub_folder . str_replace(array('.', ' '), array('\.',''), $white_file ).'|';  //make \. remove spaces 
            }
            $white_regex=substr($white_regex, 0 ,strlen($white_regex)-1); //remove last |
            $white_regex = str_replace(array("\n", "\r\n", "\r"), '', $white_regex);
        } 
          
        
        if ($this->opt('replace_urls')){
            $replace_urls=$this->h->replace_newline(trim($this->opt('replace_urls'),' '),'|');
            $replace_lines=explode('|', $replace_urls);
            if ($replace_lines) {
                foreach ($replace_lines as $line)  {
                    
                    $replace_word = explode('==', $line);
                    if (isset($replace_word[0]) && isset($replace_word[1])) {
                        
                        //Check whether last character is / or not to recgnize folders
                        $is_folder= false;
                        if (substr($replace_word[0], strlen($replace_word[0])-1 , strlen($replace_word[0]))=='/')
                            $is_folder= true;

                        $replace_word[0]=trim($replace_word[0], '/ ');
                        $replace_word[1]=trim($replace_word[1], '/ ');
                            
                        $is_block= false;    
                        if ($replace_word[1] == 'nothing_404_404') 
                            $is_block= true;
                        
                        
                        if ($is_block){
                            //Swap words to make theme unavailable
                            $temp = $replace_word[0];
                            $replace_word[0] = $replace_word[1];
                            $replace_word[1] = $temp;    
                        }

                        $replace_word[0] = str_replace(array( 'amp;', '%2F','//', '.' ), array('', '/', '/','.'), $replace_word[0]);
                        $replace_word[1] = str_replace(array('.','amp;'), array('\.',''), $replace_word[1]);
                       
						if ($this->is_subdir_mu)   
							 $sub_install2 =  $sub_install .  $this->sub_folder;
						else
							 $sub_install2 =  $sub_install ;
  
                        if ($is_folder){
				
                            $output.='RewriteRule ^'.$replace_word[1]. '/(.*) /'. $sub_install2 . $replace_word[0]. '/$1 [QSA,L]'."\n";
                        }else{
                            $output.='RewriteRule ^'.$replace_word[1]. ' /'. $sub_install2 . $replace_word[0]. ' [QSA,L]'."\n";
                        }
                    }
                }                                                 
            }
        }

	    if ( is_multisite() ){
            $sitewide_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ));
            $active_plugins= array_merge((array) get_blog_option(BLOG_ID_CURRENT_SITE, 'active_plugins'), $sitewide_plugins);
        }else{
            $active_plugins = get_option('active_plugins');
        }

        if ($this->opt('rename_plugins')=='all')
             $active_plugins = get_option('hmw_all_plugins');
            
	$pre_plugin_path='';
        if ($this->opt('rename_plugins') && $new_plugin_path) {
            foreach ((array) $active_plugins as $active_plugin)  {
                
                //Ignore itself or a plugin without folder 
                if ( !$this->h->str_contains($active_plugin,'/') || $active_plugin==self::main_file)
                    continue;
                    
                $new_plugin_path = trim($new_plugin_path, '/ ') ;
            
                //This is not just a line of code. I spent around 2 hours for this :|
                $codename_this_plugin=  hash('crc32', $active_plugin );
;
                $rel_this_plugin_path = trim(str_replace(site_url(),'', plugin_dir_url($active_plugin)), '/');
                //Allows space in plugin folder name 
                $rel_this_plugin_path = $this->sub_folder . str_replace(' ','\ ', $rel_this_plugin_path);  
                
                $new_this_plugin_path = $new_plugin_path . '/' . $codename_this_plugin ;
                $pre_plugin_path.= 'RewriteRule ^'.$new_this_plugin_path. '/(.*) /'. $rel_this_plugin_path. '/$1 [QSA,L]'."\n";
            }
        }

        if ($new_admin_path && $new_admin_path!='wp-admin')
            $output.='RewriteRule ^'.$new_admin_path. '/(.*) /'. $this->sub_folder . 'wp-admin/$1 [QSA,L]'."\n";
            
        if ($new_include_path)
            $output.='RewriteRule ^'.$new_include_path. '/(.*) /'. $rel_include_path. '/$1 [QSA,L]'."\n";
                
        if ($new_upload_path)
            $output.='RewriteRule ^'.$new_upload_path. '/(.*) /'. $rel_upload_path. '/$1 [QSA,L]'."\n";
                 
        if ($new_plugin_path && $pre_plugin_path)
            $output.= $pre_plugin_path;
            
        if ($new_plugin_path)
            $output.='RewriteRule ^'.$new_plugin_path. '/(.*) /'. $rel_plugin_path. '/$1 [QSA,L]'."\n";
        
        if ($new_style_name)
            $output.='RewriteRule ^'.$new_theme_path. '/([_0-9a-zA-Z-]+)/'.$new_style_name.' /'.$this->sub_folder.'index.php?style_wrapper=true&template_wrapper=$1 [QSA,L]'."\n";
                
        if ($new_theme_path)
            $output.='RewriteRule ^'.$new_theme_path. '/(.*) /'. str_replace('/'.get_stylesheet(), '', $rel_theme_path). '/$1 [QSA,L]'."\n";

        if ($replace_comments_post && $replace_comments_post != 'wp-comments-post.php')
            if ($this->is_subdir_mu)
                $output.='RewriteRule ^([_0-9a-zA-Z-]+/)?'.$replace_comments_post_rule. ' /'. $rel_comments_post.$antispam. ' [QSA,L]'."\n";
            else
                $output.='RewriteRule ^'.$replace_comments_post_rule. ' /'. $rel_comments_post.$antispam. ' [QSA,L]'."\n";
           
	
        if ($replace_admin_ajax_rule && $replace_admin_ajax_rule != 'wp-admin/admin-ajax.php') {
 	        if ($this->is_subdir_mu)
            	$output.='RewriteRule ^([_0-9a-zA-Z-]+/)?'.$replace_admin_ajax_rule. ' /'. $rel_admin_ajax. ' [QSA,L]'."\n";
	        else
		        $output.='RewriteRule ^'.$replace_admin_ajax_rule. ' /'. $rel_admin_ajax. ' [QSA,L]'."\n";
        }
    
        if ($this->opt('hide_other_wp_files'))
	        if ($this->is_subdir_mu)
            	$output.='RewriteRule ^('.$hide_other_file_rule. ') /'.$this->sub_folder.'nothing_404_404'. ' [QSA,L]'."\n"; 
            else
		        $output.='RewriteRule ^('.$hide_other_file_rule. ') /nothing_404_404'. ' [QSA,L]'."\n";

        if ($this->opt('disable_directory_listing') )
            if ($this->is_subdir_mu)
                $output.='RewriteRule ^'.$disable_directoy_listing. ' /'.$this->sub_folder.'nothing_404_404'. ' [QSA,L]'."\n";
            else
                $output.='RewriteRule ^'.$disable_directoy_listing. ' /nothing_404_404'. ' [QSA,L]'."\n";

        if ($this->opt('avoid_direct_access'))  {
            
            //RewriteCond %{REQUEST_URI} !(index\.php|wp-content/repair\.php|wp-includes/js/tinymce/wp-tinymce\.php|wp-comments-post\.php|wp-login\.php|index\.php|wp-admin/)(.*)
            
	    if ($this->is_subdir_mu) {
 	        $output.='RewriteCond %{REQUEST_URI} !('. str_replace($this->sub_folder,'',$white_regex) .')(.*)'."\n";
                $output.='RewriteRule ^(.*).php$'. ' /'.$this->sub_folder.'nothing_404_404'. ' [QSA,L]'."\n";
	    }else{  
                $output.='RewriteCond %{REQUEST_URI} !('.$white_regex.')(.*)'."\n";
                $output.='RewriteRule ^(.*).php$'. ' /nothing_404_404'. ' [QSA,L]'."\n";
	    }
        }               
        
        if (!$output)
            $output=__('Nothing to add for current settings!', self::slug);
        else
            $output="# BEGIN Hide My WP\n\n" . $output ."\n# END Hide My WP";       
        
        $html='';
        $desc = __( 'Add following lines to your .htaccess file to get all features of the plugin.<br>', self::slug ) ;
        if (isset($_GET['multisite_config']) && $_GET['multisite_config'])  {
            
            $html= sprintf( '%s ', $desc );
            $html.= sprintf( '<span class="description">
            <ol style="color:#ff9900">
            <li>Add below lines right before <strong>RewriteCond %{REQUEST_FILENAME} !-f</strong> </li>
            <li>You may need to re-configure the server whenever you change settings or activate a new plugin.</li> </ol></span>.
        <textarea readonly="readonly" onclick="" rows="5" cols="55" class="regular-text %1$s" id="%2$s" name="%2$s" style="%4$s">%3$s</textarea>', 'multisite_config_class','multisite_config', esc_textarea($output), 'width:95% !important;height:400px !important' );
                                                     
            
        }else{
            $html= '<a href="'.add_query_arg(array('multisite_config'=>true)).'" class="button">'.__('Multi-site Configuration', self::slug).'</a>' ;
            $html.= sprintf( '<br><span class="description"> %s</span>', $desc );
        }
        return $html;     
      //rewrite ^/assets/css/(.*)$ /wp-content/themes/roots/assets/css/$1 last; 
    }


    /**
	 * Register settings page
	 *
	 */
	/**
	 * HideMyWP::register_settings()
	 * 
	 * @return
	 */
	function register_settings() {
	   require_once('admin-settings.php');	
    }
}

$HideMyWP = new HideMyWP();
;
/** 
 *  Open wp-content/plugins/w3-total-cache/inc/define.php using FTP, your host file manager or WP plugin editor and rename 'w3_normalize_file_minify' function to something else. Replace:
 * function w3_normalize_file_minify($file) {
 * with:
 * function w3_normalize_file_minify0($file) { 
 */

function fix_w3tc_hmwp(){
    if (defined('W3TC') && !function_exists('w3_normalize_file_minify')) {
        function w3_normalize_file_minify($file){
            global $wp_rewrite;
            
            $hmwp= new HideMyWP();
            $hmwp->init();
            $hmwp->add_rewrite_rules($wp_rewrite);
            $file = $hmwp->reverse_partial_filter($file);
              
            if (w3_is_url($file)) {
                if (strstr($file, '?') === false) {
                    $domain_url_regexp = '~' . w3_get_domain_url_regexp() . '~i';
                    $file = preg_replace($domain_url_regexp, '', $file);
                }
            }
    
            if (!w3_is_url($file)) {
                $file = w3_path($file);
                $file = str_replace(w3_get_document_root(), '', $file);
                $file = ltrim($file, '/');
            }
    
            return $file;   
        }
        
    }
}
add_action('init', 'fix_w3tc_hmwp', 1000);
?>