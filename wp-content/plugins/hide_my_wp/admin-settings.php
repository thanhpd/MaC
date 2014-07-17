<?php

/**
 * @author Hassan Jahangiri
 * @copyright 2013
 */

(array) $counter = get_option('hmwp_spam_counter');
$spam_counter=  $counter['1'] + $counter['2'];
$spam_counter = ($spam_counter>1) ? ' [Spam Counter: <strong>'.$spam_counter.'</strong>]' : '';


$sections = array(
            array(
                'id' => 'start',
                'title' => __( 'Start', self::slug )
            ),
            array(
                'id' => 'general',
                'title' => __( 'General Settings', self::slug )
            ),
            array(
                'id' => 'permalink',
                'title' => __( 'Permalinks & URLs', self::slug )
            )
        );    
        
        $fields['start'] = 
            array(
                array(
                    'name' => 'li',
                    'label' => __( 'Purchase Code', self::slug ),
                    'desc' => __( 'Enter your Purchase Code to make sure everything work as expected. <a target="_blank" href="http://wpwave.com/envato/purchase_code_1200.png">Get it.</a>', self::slug ),
                    'type' => 'text',
                    'default' => '',
                    'class' =>''
                    
                ),
                array(
                    'name' => 'import_options',
                    'label' => __( 'Import Options', self::slug ),
                    'desc' => __( 'Paste your settings code below or choose a pre-made settings scheme.', self::slug ),
                    'type' => 'import',
                    'default' => '',
                    'class' =>'',
                    'options' => array(
                             'Medium Privacy - More Compatibilty (Recommended)' => '{"replace_javascript_path":"3","disable_directory_listing":"on","exclude_plugins_access":"","exclude_theme_access":"","replace_wpnonce":"","replace_mode":"safe","custom_404":"0","custom_404_page":"","login_query":"'.self::slug.'","admin_key":"1234","remove_feed_meta":"on","hide_admin_bar":"","remove_other_meta":"on","clean_post_class":"","remove_menu_class":"","remove_default_description":"on","remove_ver_scripts":"","direct_access_except":"index.php, wp-content/repair.php, wp-comments-post.php, wp-includes/js/tinymce/wp-tinymce.php, xmlrpc.php, wp-cron.php","disable_canonical_redirect":"","email_from_name":"'.get_bloginfo('blogname').'","email_from_address":"noreply@testmail.com","replace_in_html":"","new_theme_path":"/skin","new_style_name":"main.css","new_include_path":"/other","new_plugin_path":"/ext","new_upload_path":"/file","replace_comments_post":"","replace_admin_ajax":"ajax","author_enable":"1","author_base":"profile","author_query":"profile","feed_enable":"1","feed_base":"rss","feed_query":"rss","post_enable":"1","post_base":"'.get_option('permalink_structure').'","post_query":"entry_id","page_enable":"1","page_base":"","page_query":"","paginate_enable":"1","paginate_base":"","paginate_query":"","category_enable":"1","category_base":"'.get_option('category_base').'","category_query":"","tag_enable":"1","tag_base":"'.get_option('tag_base').'","tag_query":"","search_enable":"1","search_base":"","search_query":"","nice_search_redirect":"on","import_options":"","export_options":"","debug_report":"","minify_new_style":"safe","clean_new_style":"","rename_plugins":"","separator2":"","author_without_base":"","disable_archive":"","disable_other_wp":"","trusted_user_roles":"","hide_wp_login":"on","hide_wp_admin":"on","spy_notifier":"","separator":"","remove_body_class":"","remove_html_comments":"","antispam":"on","avoid_direct_access":"","hide_other_wp_files":"on"}'
                             ,                                   
                                          
                            'Medium Privacy - Quick (Recommended)' => '{"replace_javascript_path":"1","disable_directory_listing":"on","exclude_plugins_access":"on","exclude_theme_access":"on","replace_wpnonce":"","replace_mode":"quick","custom_404":"0","custom_404_page":"","hide_wp_login":"on","login_query":"'.self::slug.'","admin_key":"1234","hide_wp_admin":"on","remove_feed_meta":"on","hide_admin_bar":"on","remove_other_meta":"on","remove_body_class":"","clean_post_class":"","remove_menu_class":"","remove_default_description":"on","remove_html_comments":"","remove_ver_scripts":"","avoid_direct_access":"","direct_access_except":"index.php, wp-content/repair.php, wp-comments-post.php, wp-includes/js/tinymce/wp-tinymce.php, xmlrpc.php, wp-cron.php","hide_other_wp_files":"on","disable_canonical_redirect":"","email_from_name":"'.get_bloginfo('blogname').'","email_from_address":"noreply@testmail.com","replace_in_html":"","new_theme_path":"/template","new_style_name":"main.css","minify_new_style":"quick","clean_new_style":"","new_include_path":"/lib","new_plugin_path":"/modules","rename_plugins":"all","new_upload_path":"/file","replace_comments_post":"/user_submit.php","replace_admin_ajax":"do_ajax.php","author_enable":"1","author_base":"profile","author_query":"user","author_without_base":"","feed_enable":"1","feed_base":"index.xml","feed_query":"sitefeed","post_enable":"1","post_base":"'.get_option('permalink_structure').'","post_query":"article_id","page_enable":"1","page_base":"","page_query":"page_num","paginate_enable":"1","paginate_base":"/page/","paginate_query":"go","category_enable":"1","category_base":"'.get_option('category_base').'","category_query":"topic","tag_enable":"1","tag_base":"'.get_option('tag_base').'","tag_query":"keyword","search_enable":"1","search_base":"search","search_query":"","nice_search_redirect":"on","disable_archive":"","disable_other_wp":"","import_options":"","export_options":"","debug_report":"","separator2":"","trusted_user_roles":"","antispam":"on","spy_notifier":"","separator":""}'
                          ,
                          'High Privacy - Less Compatibility *' => '{"replace_javascript_path":"3","disable_directory_listing":"on","exclude_plugins_access":"","exclude_theme_access":"","replace_wpnonce":"","replace_mode":"safe","custom_404":"0","custom_404_page":"","hide_wp_login":"on","login_query":"'.self::slug.'","admin_key":"1234","hide_wp_admin":"on","remove_feed_meta":"on","hide_admin_bar":"on","remove_other_meta":"on","remove_body_class":"on","clean_post_class":"on","remove_menu_class":"on","remove_default_description":"on","remove_html_comments":"simple","remove_ver_scripts":"on","avoid_direct_access":"on","direct_access_except":"index.php, wp-content/repair.php, wp-comments-post.php, wp-includes/js/tinymce/wp-tinymce.php, xmlrpc.php, wp-cron.php","hide_other_wp_files":"on","disable_canonical_redirect":"on","email_from_name":"'.get_bloginfo('blogname').'","email_from_address":"noreply@test-mail.com","replace_in_html":"","new_theme_path":"/template","new_style_name":"main.css","minify_new_style":"quick","clean_new_style":"on","new_include_path":"/template/lib","new_plugin_path":"/template/ext","rename_plugins":"all","new_upload_path":"/storage","replace_comments_post":"submit_comment.php","replace_admin_ajax":"ajax","author_enable":"1","author_base":"profile","author_query":"profile","author_without_base":"on","feed_enable":"1","feed_base":"rss.xml","feed_query":"rss","post_enable":"1","post_base":"'.get_option('permalink_structure').'","post_query":"entry","page_enable":"1","page_base":"/page","page_query":"page_num","paginate_enable":"1","paginate_base":"list","paginate_query":"list","category_enable":"1","category_base":"'.get_option('category_base').'","category_query":"category","tag_enable":"1","'.get_option('tag_base').'":"keyword","tag_query":"keyword","search_enable":"1","search_base":"find","search_query":"find","nice_search_redirect":"on","disable_archive":"on","disable_other_wp":"","import_options":"","export_options":"","debug_report":"","separator2":"","trusted_user_roles":"","antispam":"on","spy_notifier":"","separator":""}',
                            
                    
                    )
                )
                ,
                array(
                    'name' => 'export_options',
                    'label' => __( 'Export Options', self::slug ),
                    'desc' => __( 'Copy your export code and save it somewhere for later use.', self::slug ),
                    'type' => 'export',
                    'default' => '',
                    'class' =>''
                    
                )
                ,
                array(
                    'name' => 'debug_report',
                    'label' => __( 'Debug Report', self::slug ),
                    'desc' => __( 'Provide above report to support team to get better and faster service.', self::slug ),
                    'type' => 'debug_report',
                    'default' => '',
                    'class' =>''
                    
                )
                
            );
            
             
        if (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') || stristr($_SERVER['SERVER_SOFTWARE'], 'wpengine') || isset($_GET['nginx_config']) ){    
            $fields['start'][]  = array(
                    'name' => 'nginx_config',
                    'label' => __( 'Nginx Configuration', self::slug ),
                    'desc' => $this->nginx_config(),
                    'type' => 'custom',
                    'default' => '',
                    'class' =>''
                    
                );             
        } 
        
        if (function_exists('bulletproof_security_load_plugin_textdomain') || isset($_GET['single_config']) ){   
            $fields['start'][]  = array(
                    'name' => 'single_config',
                    'label' => __( 'Manual Configuration', self::slug ),
                    'desc' => $this->single_config(),
                    'type' => 'custom',
                    'default' => '',
                    'class' =>''
                                    
                );             
        }
        
        if (is_multisite() || (isset($_GET['multisite_config']) && $_GET['multisite_config'])){
            $fields['start'][]  = array(
                    'name' => 'multisite_config',
                    'label' => __( 'Multisite Configuration', self::slug ),
                    'desc' => $this->multisite_config(),
                    'type' => 'custom',
                    'default' => '',
                    'class' =>''
                                    
                );             
        } 
                
        $fields['start'][]  = array(
                    'name' => 'vote_us',
                    'label' => __( 'Spread it (please!)', self::slug ),
                    'desc' =>  '<a target="_blank" href="http://codecanyon.net/item/hide-my-wp-no-one-can-know-you-use-wordpress/4177158" class="button">'.__(' ★ Vote it', self::slug).'</a> ' . '  <a target="_blank" href="http://twitter.com/home?status=Hide+My+WP+::+No+one+can+know+you+use+WordPress!+http://codecanyon.net/item/hide-my-wp-no-one-can-know-you-use-wordpress/4177158?rate_it=true" class="button">'.__(' ♥  Tweet it', self::slug).'</a>',
                    'type' => 'custom',
                    'default' => '',
                    'class' =>''
                    
                );
                
        $fields['start'][]  = array(
                    'name' => 'help',
                    'label' => __( 'Quick Fix Guide', self::slug ),
                    'desc' =>  '<ol><li>Make sure you have a <strong>writable htaccess</strong> file (if you use Apache) or configured your web server manually (if you use Nginx or enabled multi-site). Follow installation guide for details.</li><li><strong>Disable features that have an asterisk(*) in their names</strong> or use a more compatible settings scheme.</li><li>Read <a href="http://codecanyon.net/item/hide-my-wp-no-one-can-know-you-use-wordpress/4177158/support"> <strong>our helpful FAQs</strong></a> for common issues.</li><li>If you still see WP footprints in source code use <strong>Replace in HTML</strong> to remove them.</li><!--<li>For better support provide debug report or login details (if possible) via a message (and not comment) using author\'s profile page in Codecanyon.</li>--></ol>',
                    'type' => 'custom',
                    'default' => '',
                    'class' =>''
                    
                );
                
            
                         
        $fields['permalink'] = array(
                    array(
                        'name' => 'new_theme_path',
                        'label' => __( 'New theme path', self::slug ),
                        'desc' => __( 'e.g. "/template"', self::slug ),
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'
                        ),
                        
                     array(
                        'name' => 'new_style_name',
                        'label' => __( 'New style name', self::slug ),
                        'desc' => __( 'e.g. "main.css" (Require New theme path)', self::slug ),
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'
                        ),
                     
                     array(
                        'name' => 'minify_new_style',
                        'label' => __( 'Minify style', self::slug ),
                        'desc' => __( 'Remove theme info and other comments from stylesheet. (Require new style path)(Use with cache!).', self::slug ),
                        'type' => 'select',
                        'default' => '',
                        'class' =>'permalink_req' ,
                        'options' => array(
                            '' => 'Disable Minify',
                            'quick' => __('Quick Minify', self::slug),
                            'safe' =>  __('Safe Minify', self::slug)
                            )
                        ),
                     array(
                        'name' => 'clean_new_style',
                        'label' => __( 'Clean style', self::slug ),
                        'desc' => __( 'Replace WP classes (wp-caption, etc.) with their "x-" version e.g x-caption (Require new style path).', self::slug ),
                        'type' => 'checkbox',
                        'default' => '',
                        'class' =>'permalink_req'
                        ),
                           
                     array(
                        'name' => 'new_include_path',
                        'label' => __( 'New wp-includes path', self::slug ),
                        'desc' => __( 'e.g. "/lib"', self::slug ),
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'
                        )
                     ,
                     array(
                        'name' => 'new_plugin_path',
                        'label' => __( 'New plugin path', self::slug ),
                        'desc' => __( 'e.g. "/modules"', self::slug ),
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'
                        ),
                     
                     
                     array(
                        'name' => 'rename_plugins',
                        'label' => __( 'Rename Plugins', self::slug ),
                        'desc' => (is_multisite()) ? __( 'Change plugins folder both in sitewide and main blog) with a codename (Require new plugin path).', self::slug ) : __( 'Change each plugin folder name with a codename (Require new plugin path).', self::slug ),
                        'type' => 'select',
                        'default' => '',
                        'class' =>'permalink_req',
                        'options' => array(
                            '' => __( 'Disable Plugin Rename', self::slug ),
                            'on' => __( 'Only Active Plugins (Quick) *', self::slug ),
                            'all' => __( 'All Plugins', self::slug )
                        )
                        
                       
                        )
                     ,
                     array(
                        'name' => 'new_upload_path',
                        'label' => __( 'New upload path', self::slug ),
                        'desc' => __( 'e.g. "/file". <br>If your theme or your image plugins uses <strong>TimThumb</strong> (check source code) <a href="http://codecanyon.net/item/hide-my-wp-no-one-can-know-you-use-wordpress/4177158/faqs/16224">read here</a>.', self::slug ),
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'replace_comments_post',
                        'label' => __( 'Post Comment', self::slug ),
                        'desc' => __( 'Change "wp_comments_post.php" URL (e.g. "/user_submit" or "/folder/user_submit.php").', self::slug ),
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'
                        )
                     ,
                    array(
                        'name' => 'antispam',
                        'label' => __( 'Anti-Spam', self::slug ),
                        'desc' => __( 'Enable HMWP anti-spam system.', self::slug ) . $spam_counter,
                        'type' => 'checkbox',
                        'default' => '',
                        'class' =>''
                    )
                    ,
                     array(
                        'name' => 'replace_admin_ajax',
                        'label' => __( 'AJAX URL', self::slug ),
                        'desc' => __( 'Change wp-admin/admin_ajax.php URL (e.g. "/ajax" or "ajax.php").', self::slug ),
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'
                        )
                        ,
                        
                    array(
                        'name' => 'replace_javascript_path',
                        'label' => __( 'Replace \/ URLs', self::slug ),
                        'desc' => 'Choose if you see Javascript URLs (e.g. \/wp-conetet\/themes)',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '0' => __( 'Disable JS URLs', self::slug ),
                            '1' => __( 'Only for theme', self::slug ),
                            '2' => __( 'For theme and plugins', self::slug ),
                            '3' => __( 'For theme, plugins and uploads', self::slug ),
                            )
                        ),
                        
                    array(
                        'name' =>'separator2',
                        'label' =>'',
                        'desc' => '<div style="border-top:1px solid #ccc;"></div><br/>',
                        'type' => 'html',
                        'class'=>'permalink_req'
                        )
                    ,
                    array(
                        'name' => 'new_admin_path',
                        'label' => __( 'New wp-admin Path*', self::slug ),
                        'desc' => __( '<br>(EXPERIMENTAL) Change "/wp-admin" (e.g. panel, cp). <br> <strong>Require</strong> to update wp-config.php manually' , self::slug) ,
                        'type' => 'text',
                        'default' => '',
                        'class' =>'permalink_req'

                    ),



                    array(
                        'name' =>'separator3',
                        'label' =>'',
                        'desc' => '<div style="border-top:1px solid #ccc;"></div><br/>',
                        'type' => 'html',
                        'class'=>'permalink_req'
                    )
                    ,
            array(
                        'name' => 'author_enable',
                        'label' => __( 'Author', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __( 'Enable Authors URL', self::slug ),
                            '0' => __( 'Disable Authors URL', self::slug )
                            )
                        ),
                    array(
                        'name' => 'author_base',
                        'label' => __( 'Author Base', self::slug ),
                        'desc' => __( 'Change "/author/username" (e.g. user, profile, members/editrs/).', self::slug ),
                        'type' => 'text',
                        'default' => '/author',
                        'class' =>' open_by_author_enable_1 permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'author_query',
                        'label' => __( 'Author Query', self::slug ),
                        'desc' => __( 'Change /?author=1 and /?author_name=username (e.g. u, user, member).', self::slug ),
                        'type' => 'text',
                        'default' => 'author',
                        'class' =>' open_by_author_enable_1'
                        )
                    ,
                    array(
                        'name' => 'author_without_base',
                        'label' => __( 'Author without base', self::slug ),
                        'desc' => __( 'Use username directly and without base (e.g. domain.com/admin) *', self::slug ),
                        'type' => 'checkbox',
                        'default' => '',
                        'class' =>'open_by_author_enable_1 permalink_req'
                        )
                    ,
                    array(
                        'name' => 'feed_enable',
                        'label' => __( 'Feeds', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __( 'Enable Feeds URL', self::slug ),
                            '0' => __( 'Disable All Feeds URL' , self::slug )
                            )
                        ),
                    array(
                        'name' => 'feed_base',
                        'label' => __( 'Feed Base', self::slug ),
                        'desc' => __( 'Change /feed (e.g. xml, rss, index.xml).', self::slug ),
                        'type' => 'text',
                        'default' => '/feed',
                        'class' =>' open_by_feed_enable_1 permalink_req'
                        ) 
                    ,
                      
                    array(
                        'name' => 'feed_query',
                        'label' => __( 'Feed Query', self::slug ),
                        'desc' => __( 'Change /?feed=rss2 (e.g. xml, rss, sitefeed).', self::slug ),
                        'type' => 'text',
                        'default' => 'feed',
                        'class' =>' open_by_feed_enable_1'
                        )  
                    ,
                     
                     array(
                        'name' => 'post_enable',
                        'label' => __( 'Post', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __('Enable Posts URL', self::slug ),
                            '0' => __('Disable Posts URL', self::slug )
                            )
                     ),
                    array(
                        'name' => 'post_base',
                        'label' => __( 'Post Permalink', self::slug ),
                        'desc' => (is_multisite()) ? __( 'Use default WP permalink page to change post permalink.', self::slug) : __( 'Change default WP post permalink. <a href="http://codex.wordpress.org/Using_Permalinks">[Get Tags]</a>.', self::slug ),
                        'type' => (is_multisite()) ? 'custom' : 'text',
                        'default' => get_option('permalink_structure'),
                        'class' =>' open_by_post_enable_1 permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'post_query',
                        'label' => __( 'Post Query', self::slug ),
                        'desc' => __( 'Change /?p=1 (e.g. article_id, news_id or pid).', self::slug ),
                        'type' => 'text',
                        'default' => 'p',
                        'class' =>' open_by_post_enable_1'
                        )
                    ,
                    array(
                        'name' => 'page_enable',
                        'label' => __( 'Page', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __('Enable Pages URL', self::slug ),
                            '0' => __('Disable Pages URL', self::slug )
                            )
                     ),
                    array(
                        'name' => 'page_base',
                        'label' => __( 'Page Base', self::slug ),
                        'desc' => __( 'Change /sample-page to /X/sample-page (e.g. pages, static).', self::slug ),
                        'type' => 'text',
                        'default' => '/',
                        'class' =>' open_by_page_enable_1 permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'page_query',
                        'label' => __( 'Page Query', self::slug ),
                        'desc' => __( 'Change /?page_id=1 or /?page_name=about (e.g. pages).', self::slug ),
                        'type' => 'text',
                        'default' => 'page_id',
                        'class' =>' open_by_page_enable_1'
                        )
                    ,
                    
                    array(
                        'name' => 'paginate_enable',
                        'label' => __( 'Paginate', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __( 'Enable Paginates URL', self::slug ),
                            '0' => __( 'Disable Paginates URL', self::slug )
                            )
                        ),
                    array(
                        'name' => 'paginate_base',
                        'label' => __( 'Paginate Base', self::slug ),
                        'desc' => __( 'Change /page/2 (e.g. pages, go).', self::slug ),
                        'type' => 'text',
                        'default' => '/page',
                        'class' =>' open_by_paginate_enable_1 permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'paginate_query',
                        'label' => __( 'Paginate Query', self::slug ),
                        'desc' => __( 'Change /?paged=2.', self::slug ),
                        'type' => 'text',
                        'default' => 'paged',
                        'class' =>' open_by_paginate_enable_1'
                        )
                    ,
                    array(
                        'name' => 'category_enable',
                        'label' => __( 'Category', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __('Enable Categories URL', self::slug ),
                            '0' => __('Disable Categories URL', self::slug )
                            )
                     ),
                    array(
                        'name' => 'category_base',
                        'label' => __( 'Category Base', self::slug ),
                        'desc' => (is_multisite()) ? __( 'Use default WP permalink page to change category base.', self::slug) : __( 'Change /category/uncategorized. (e.g. topic, all).', self::slug ),
                        'type' => (is_multisite()) ? 'custom' : 'text',
                        'default' => get_option('category_base'),
                        'class' =>' open_by_category_enable_1 permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'category_query',
                        'label' => __( 'Category Query', self::slug ),
                        'desc' => __( 'Change /?cat=1 or /?category_name=uncategorized (e.g. topic).', self::slug ),
                        'type' => 'text',
                        'default' => 'cat',
                        'class' =>' open_by_category_enable_1'
                        )
                    ,
                    
                    array(
                        'name' => 'tag_enable',
                        'label' => __( 'Tag', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __('Enable Tags URL', self::slug ),
                            '0' => __('Disable Tags URL', self::slug )
                            )
                     ),
                    array(
                        'name' => 'tag_base',
                        'label' => __( 'Tag Base', self::slug ),
                        'desc' => (is_multisite()) ? __( 'Use default WP permalink page to change tag base.', self::slug) : __( 'Change /tag/tag1 (e.g. keyword, find).', self::slug ),
                        'type' => (is_multisite()) ? 'custom' :'text',
                        'default' => get_option('tag_base'),
                        'class' =>' open_by_tag_enable_1 permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'tag_query',
                        'label' => __( 'Tag Query', self::slug ),
                        'desc' => __( 'Change /?tag=tag1 (e.g. keyword, find).', self::slug ),
                        'type' => 'text',
                        'default' => 'tag',
                        'class' =>' open_by_tag_enable_1'
                        )
                    ,

                    array(
                        'name' => 'search_enable',
                        'label' => __( 'Search', self::slug ),
                        'desc' => '',
                        'type' => 'select',
                        'default' => '1',
                        'class' =>'opener',
                        'options' => array(
                            '1' => __('Enable Search', self::slug ),
                            '0' => __('Disable Search' , self::slug )
                            )
                        ),
                    array(
                        'name' => 'search_base',
                        'label' => __( 'Search Base', self::slug ),
                        'desc' => __( 'Change /search/keyword (e.g. find, s, dl).', self::slug ),
                        'type' => 'text',
                        'default' => '/search',
                        'class' =>' open_by_search_enable_1 permalink_req'
                        ) 
                    ,
                    array(
                        'name' => 'search_query',
                        'label' => __( 'Search Query', self::slug ),
                        'desc' => __( 'Change /?s=keyword (e.g. find, s, dl).', self::slug ),
                        'type' => 'text',
                        'default' => 's',
                        'class' =>' open_by_search_enable_1'
                        )
                    , 
                    array(
                        'name' => 'nice_search_redirect',
                        'label' => __( 'Search base redirect', self::slug ),
                        'desc' => __( 'Redirect all search queries to permalink (e.g. /search/test instead /?s=test).', self::slug ),
                        'type' => 'checkbox',
                        'default' => '',
                        'class' =>'open_by_search_enable_1 permalink_req'
                        )
                     , 
                    
                    array(
                        'name' => 'disable_archive',
                        'label' => __( 'Disable Archive', self::slug ),
                        'desc' => __( 'Disable archive queries (yearly, monthly or daily archives).', self::slug ),
                        'type' => 'checkbox',
                        'default' => '',
                        'class' =>''
                        )
                    , 
                    array(
                        'name' => 'replace_wpnonce',
                        'label' => __( 'Change Nonce', self::slug ),
                        'desc' => __( 'Replace _wpnonce in URLs with _nonce.', self::slug ),
                        'type' => 'checkbox',
                        'default' => '',
                        'class' =>''
                        )
                    ,
                    array(
                        'name' => 'disable_other_wp',
                        'label' => __( 'Disable Other WP', self::slug ),
                        'desc' => __( 'Disable other WordPress queries like post type, taxonamy, attachments, comment page etc. Post types may be used by themes or plugins. *', self::slug ),
                        'type' => 'checkbox',
                        'default' => '',
                        'class' =>''
                        )
        
            );
                         
        $fields['general'] = 
                array(
                         array(
                            'name' => 'custom_404',
                            'label' => __( '404 Page Template', self::slug ),
                            'desc' => __( '', self::slug ),
                            'type' => 'radio',
                            'default' => '0',
                            'class' =>'opener',
                            'options' => array(
                                '0' => 'Use default 404 page from theme',
                                '1' => 'Choose a custom page'
                                )
                            )
                        ,      
                        array(
                            'name' => 'custom_404_page',
                            'label' => __( 'Custom 404 Page', self::slug ),
                            'desc' => __( 'We use this as 404 page.', self::slug ),
                            'type' => 'pagelist',
                            'default' => '',
                            'class' =>'open_by_custom_404_1'
                            )
                        ,
                        array(
                            'name' => 'trusted_user_roles',
                            'label' => __( 'Trusted User Roles', self::slug ),
                            'desc' => __( 'Choose trusted user roles. (Administrator are trusted by default)', self::slug ),
                            'type' => 'rolelist',
                            'options' => array(),
                            'class' =>''
                            
                        )  
                        ,
                        array(
                            'name' => 'replace_mode',
                            'label' => __( 'Replace Mode', self::slug ),
                            'desc' => __( 'How should we replace old URLs? (Use Full mode with cache)', self::slug ),
                            'type' => 'select',
                            'default' => 'quick',
                            'class' =>'',
                            'options' => array(
                                'quick' => __('Partial (Quick) *', self::slug),
                                'safe' =>  __('Full Page', self::slug)
                                )   
                            )
                        ,
                        array(
                            'name' => 'hide_wp_login',
                            'label' => __( 'Hide Login Page', self::slug ),
                            'desc' => __( 'Hide wp-login.php. [<b>Important:</b> You need to remember new address to login!]', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>'opener'
                            )
                        ,
                        array(
                            'name' => 'login_query',
                            'label' => __( 'Login Query', self::slug ),
                            'desc' => __( 'Login parameter for protected login address (default: hide_my_wp) e.g. wp-login.php?hide_my_wp=ADMIN_KEY', self::slug ),
                            'type' => 'text',
                            'default' => self::slug,
                            'class' =>'open_by_hide_wp_login' 
                            )
                        ,
                       
                        array(
                            'name' => 'admin_key',
                            'label' => __( 'Admin Login Key', self::slug ),
                            'desc' => sprintf(__( '<br>Current Login url: %s <a title="New WP Login" href="%s">[Link]</a> (Save changes to update)<br>Need to change login URL to something like /login? Try <a href="http://wordpress.org/extend/plugins/theme-my-login/">Theme My Login</a>', self::slug ), '<b>/wp-login.php?'.$this->opt('login_query').'='.$this->opt('admin_key').'</b>', site_url('wp-login.php?'.$this->opt('login_query').'='.$this->opt('admin_key')) ),
                            'type' => 'text',
                            'default' => '1234',
                            'class' =>'open_by_hide_wp_login'
                            )
                        ,
                        array(
                            'name' => 'hide_wp_admin',
                            'label' => __( 'Hide Admin', self::slug ),
                            'desc' => __( 'Hide wp-admin folder and its files for untrusted users.', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            )
                        ,
                        array(
                            'name' => 'spy_notifier',
                            'label' => __( 'Spy Notify', self::slug ),
                            'desc' => __( 'Send an email to site admin whenever someone visits 404 page!', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            )
                        ,
                        array(
                            'name' => 'customized_htaccess',
                            'label' => __( 'Customized htaccess', self::slug ),
                            'desc' => __( 'Choose this option only if you have a customized htaccess and don\'t want to allow HMWP update it frequently. You need to configure HMWP manually. To do that <a href="?page='.self::slug.'&single_config=1">Click here</a>  and go to Start tab.', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            )
                        ,
                        array(
                            'name'=>'separator',
                            'label' => '',
                            'desc' => '<div style="border-top: 1px solid #ccc;"></div><br/>',
                            'type' => 'html',
                            'class' =>''
                            )
                        ,
                        array(
                            'name' => 'remove_feed_meta',
                            'label' => __( 'Feed Meta', self::slug ),
                            'desc' => __( 'Remove auto-generated feeds from header.', self::slug ),
                            'type' => 'checkbox',
                            'default' => 'on',
                            'class' =>'open_by_feed_enable_1'
                            )
                        ,    
                         array(
                            'name' => 'remove_other_meta',
                            'label' => __( 'Other Meta', self::slug ),
                            'desc' => __( 'Remove other header metas like short link, previous and next links, etc.', self::slug ),
                            'type' => 'checkbox',
                            'default' => 'on',
                            'class' =>''
                            )
                         ,
                        array(
                            'name' => 'hide_admin_bar',
                            'label' => __( 'Hide Admin Bar', self::slug ),
                            'desc' => __( 'Hide admin bar for untrusted users.', self::slug ),
                            'type' => 'checkbox',
                            'default' => 'on',
                            'class' =>''
                            )

                         ,
                         array(
                            'name' => 'remove_default_description',
                            'label' => __( 'Default Tagline', self::slug ),
                            'desc' => __( 'Remove \'Just another WordPress blog\' from your feed.', self::slug ),
                            'type' => 'checkbox',
                            'default' => 'on',
                            'class' =>''
                            )
                         ,
                        
                         
                         array(
                            'name' => 'remove_ver_scripts',
                            'label' => __( 'Remove Version', self::slug ),
                            'desc' => __( 'Remove version number (?ver=) from styles and scripts URLs.', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            )
                         ,
                         array(
                            'name' => 'hide_other_wp_files',
                            'label' => __( 'Hide Other Files', self::slug ),
                            'desc' => __( 'Hide license.txt, wp-includes, wp-content/debug.log, etc.', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>'permalink_req'
                            )     
                          ,
                          array(
                                'name' => 'disable_directory_listing',
                                'label' => __( 'Directory List', self::slug ),
                                'desc' => __( 'Disable directory listing and hide all .txt files.', self::slug ),
                                'type' => 'checkbox',
                                'default' => '',
                                'class' =>'permalink_req'
                            )
                          ,
                          array(
                            'name' => 'disable_canonical_redirect',
                            'label' => __( 'Canonical Redirect', self::slug ),
                            'desc' => __( 'Disable canonical redirect. This is requiring when you want to use URL queries.', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            
                         ),
                         array(
                            'name' => 'remove_body_class',
                            'label' => __( 'Body Classes', self::slug ),
                            'desc' => __( 'Clean up body classes *', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            ) 
                            ,
                         array(
                            'name' => 'clean_post_class',
                            'label' => __( 'Post Classes', self::slug ),
                            'desc' => __( 'Clean up post classes *', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            ) ,
                         array(
                            'name' => 'remove_menu_class',
                            'label' => __( 'Menu Classes', self::slug ),
                            'desc' => __( 'Clean up menu classes *', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>''
                            )
                         ,
                         array(
                            'name' => 'avoid_direct_access',
                            'label' => __( 'Hide PHP Files', self::slug ),
                            'desc' => __( 'Avoid direct access to php files (except wp-admin) *', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>'opener'
                            ),
                         array(
                            'name' => 'direct_access_except',
                            'label' => __( 'Exclude Files', self::slug ),
                            'desc' => __( 'Except these PHP files (or folders). Separate with ,', self::slug ),
                            'type' => 'textarea',
                            'default' => 'index.php, wp-content/repair.php, wp-comments-post.php, wp-includes/js/tinymce/wp-tinymce.php, xmlrpc.php, wp-cron.php, wp-admin/upgrade.php',
                            'class' =>'open_by_avoid_direct_access'
                            )
                         ,
                         array(
                            'name' => 'exclude_theme_access',
                            'label' => __( 'Exclude Theme', self::slug ),
                            'desc' => __( 'Add theme files to above list. Use this if you experience incompatibility with your theme.', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>'open_by_avoid_direct_access'
                            )
                         ,
                         array(
                            'name' => 'exclude_plugins_access',
                            'label' => __( 'Exclude Plugins', self::slug ),
                            'desc' => __( 'Add plugins files to above list. Use this if you experience incompatibility with plugins.', self::slug ),
                            'type' => 'checkbox',
                            'default' => '',
                            'class' =>'open_by_avoid_direct_access'
                            )
                         ,
                         
                         array(
                            'name' => 'remove_html_comments',
                            'label' => __( 'Compress Page', self::slug ),
                            'desc' => __( 'Remove comments and whitespaces from HTML (Use with cache).', self::slug ),
                            'type' => 'select',
                            'default' => '',
                            'class' =>'',
                            'options' => array(
                                '' => 'Disable Minify',
                                'simple' => __('Simple Minify', self::slug),
                                'quick' => __('Quick Minify *', self::slug),
                                'safe' =>  __('Safe Minify *', self::slug)
                                )   
                            )
                        ,
                         array(
                            'name' => 'email_from_name',
                            'label' => __( 'Email sender name', self::slug ),
                            'desc' => __( 'e.g. John Smith', self::slug ),
                            'type' => 'text',
                            'default' => '',
                            'class' =>''
                            )
                         ,
                         array(
                            'name' => 'email_from_address',
                            'label' => __( 'Email sender address', self::slug ),
                            'desc' => __( 'e.g. info@domain.com', self::slug ),
                            'type' => 'text',
                            'default' => '',
                            'class' =>''
                            )
                         ,  
                         array(
                            'name' => 'replace_in_html',
                            'label' => __( 'Replace in HTML', self::slug ),
                            'desc' => __( 'Replace words in HTML output. Case-sensitive. Order is important. One per line. e.g. old=new <br/> Use [equal] when \'=\' is in keywords. Do not use this to change URLs. <br/>', self::slug ),
                            'type' => 'textarea',
                            'default' => '',
                            'class' =>''
                            )
                         ,   
                         array(
                            'name' => 'replace_urls',
                            'label' => __( 'Replace URLs', self::slug ),
                            'desc' => __( 'Replace or rename URLs in HTML output. Case-sensitive. Order is important. One per line.<br> Use \'orginal\' path and \'==\' e.g wp-content/plugins/woocommerce/assets/css/woocommerce.css==ec.css<br>Use \'nothing_404_404\' as second part to make it unavailable.<br>Add \'/\' to the end of first part to change all files in that folder<br>Use \'Full Page\' replace mode if you experience conflict with other replaces', self::slug ),
                            'type' => 'textarea',
                            'default' => '',
                            'class' =>''
                            ) 

                        
                        
                );
          
                                              
                                              
        $menu=array(
                    'name' => self::slug,
                    'title' => self::title,
                    'icon_path' => '',
                    'role' => '',
                    'template_file' =>'',
                    'display_metabox' => '1',
                    'plugin_file' => self::main_file ,
                    'action_link' => '<b>Settings</b>',
                    'multisite_only' => (is_multisite()) ? true : false
                   );

        
                
        foreach ($fields as $tab=>$field){
            $i=0;
            foreach ($field as $option) {
                if ($this->h->str_contains($option['class'], 'permalink_req') && !get_option('permalink_structure'))
                    unset($fields[$tab][$i]) ;
                $i++;
            }
        } 
        
        $this->s = new PP_Settings_API($fields, $sections, $menu);
    

?>