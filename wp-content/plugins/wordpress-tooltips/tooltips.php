<?php
/*
Plugin Name: Tooltips
Plugin URI:  http://tomas.zhu.bz/wordpress-plugin-tooltips.html
Description: Wordpress Tooltips,You can add text,image,link,video,radio in tooltips, add tooltips in gallery. More amazing features? Do you want to customize a beautiful style for your tooltips? Get <a href='http://tooltips.org' target='blank'>Wordpress Tooltips Pro</a> now.
Version: 3.4.5
Author: Tomas Zhu: <a href='http://tooltips.org' target='_blank'>Tooltips Pro</a>
Author URI: http://tomas.zhu.bz
License: GPL2
*/

/*  Copyright 2011 Tomas.Zhu (email : expert2wordpress@gmail.com)

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

error_reporting(0);
require_once("tooltipsfunctions.php");
function tooltipsHead()
{
	$m_pluginURL = get_option('siteurl').'/wp-content/plugins';

?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $m_pluginURL; ?>/<?php echo  "/wordpress-tooltips" ?>/js/jdirectory/directory.css" title="green" />


 	<script type="text/javascript">	
	if(typeof jQuery=='undefined')
	{
		document.write('<'+'script src="<?php echo $m_pluginURL; ?>/<?php echo  '/wordpress-tooltips'; ?>/js/qtip/jquery.js" type="text/javascript"></'+'script>');
	}
	</script>
	
	<script type="text/javascript" src="<?php echo $m_pluginURL; ?>/<?php echo  "/wordpress-tooltips" ?>/js/qtip/jquery.qtip-1.0.0-rc3.min.js"></script>
	<script type="text/javascript" src="<?php echo $m_pluginURL; ?>/<?php echo  "/wordpress-tooltips" ?>/js/jdirectory/jquery.directory.js"></script>
	<script type="text/javascript">

	function toolTips(whichID,theTipContent)
	{
			jQuery(whichID).qtip
			(
				{
					content:theTipContent,
   					style:
   					{
      					width: 400,
      					padding: 5,
      					background: '#fcfcec',
      					color: 'black',
      					textAlign: 'left',
      					border:
      					{
         					width: 1,
         					radius: 8,
         					color: '#eee'
      					}
    				},
    				position:
    				{
      					corner:
      					{
         					target: 'rightMiddle',
         					tooltip: 'leftBottom'
      					}
    				},
					show:'mouseover',
					hide: { fixed: true, delay: 200 }
				}
			)
	}
</script>
	
<?php
}

function tooltipsMenu()
{

	add_menu_page(__('Tooltips','Tooltips'), __('Tooltips','Tooltips'), 10, 'tooltipsfunctions.php','editTooltips');
	add_submenu_page('tooltipsfunctions.php',__('Edit Tooltips','Tooltips'), __('Edit Tooltips','Tooltips'),10, 'tooltipsfunctions.php','editTooltips');
}

add_action('admin_menu', 'tooltips_menu');

function tooltips_menu() {
	add_submenu_page('edit.php?post_type=tooltips',__('Global Settings','Tooltips'), __('Global Settings','Tooltips'),10, 'tooltipglobalsettings','tooltipGlobalSettings');
}

function showTooltips($content)
{
	global $table_prefix,$wpdb,$post;

	do_action('action_before_showtooltips', $content);
	$content = apply_filters( 'filter_before_showtooltips',  $content);
	
	//!!! $m_result = get_option('tooltipsarray');
	$curent_post = get_post($post);
	
	$curent_content = $curent_post->post_content;

	
	$m_result = tooltips_get_option('tooltipsarray');
	$m_keyword_result = '';
	if (!(empty($m_result)))
	{
		$m_keyword_id = 0;
		foreach ($m_result as $m_single)
		{
			
					if (stripos($curent_content,$m_single['keyword']) === false)
					{
						
					}
					else 
					{			
			$m_keyword_result .= '<script type="text/javascript">';
			$m_content = $m_single['content'];
			$m_content = str_ireplace('\\','',$m_content);
			$m_content = str_ireplace("'","\'",$m_content);
			$m_content = preg_replace('|\r\n|', '<br/>', $m_content);
			if (!(empty($m_content)))
			{
				$m_keyword_result .= " toolTips('.classtoolTips$m_keyword_id','$m_content'); ";
			}
			$m_keyword_result .= '</script>';
					}
					$m_keyword_id++;
		}


	}
	$content = $content.$m_keyword_result;
	do_action('action_after_showtooltips', $content);
	$content = apply_filters( 'filter_after_showtooltips',  $content);
	return $content;
}

function showTooltipsInTag($content)
{
	global $table_prefix,$wpdb,$post;

	do_action('action_before_showtooltipsintag', $content);
	$content = apply_filters( 'filter_before_showtooltipsintag',  $content);
	//!!! $m_result = get_option('tooltipsarray');
	
	$curent_content = $content;

	
	$m_result = tooltips_get_option('tooltipsarray');
	$m_keyword_result = '';
	if (!(empty($m_result)))
	{
		$m_keyword_id = 0;
		foreach ($m_result as $m_single)
		{
			
					if (stripos($curent_content,$m_single['keyword']) === false)
					{
						
					}
					else 
					{			
			$m_keyword_result .= '<script type="text/javascript">';
			$m_content = $m_single['content'];
			$m_content = str_ireplace('\\','',$m_content);
			$m_content = str_ireplace("'","\'",$m_content);
			$m_content = preg_replace('|\r\n|', '<br/>', $m_content);
			if (!(empty($m_content)))
			{
				$m_keyword_result .= " toolTips('.classtoolTips$m_keyword_id','$m_content'); ";
			}
			$m_keyword_result .= '</script>';
					}
					$m_keyword_id++;
		}


	}
	$content = $content.$m_keyword_result;

	do_action('action_after_showtooltipsintag', $content);
	$content = apply_filters( 'filter_after_showtooltipsintag',  $content);

	return $content;
}


function tooltipsInContent($content)
{
	do_action('action_before_tooltipsincontent', $content);
	$content = apply_filters( 'filter_before_tooltipsincontent',  $content);
		
	$onlyFirstKeyword = get_option("onlyFirstKeyword");
	if 	($onlyFirstKeyword == false)
	{
		$onlyFirstKeyword = 'all';
	}

	$m_result = tooltips_get_option('tooltipsarray');
	if (!(empty($m_result)))
	{
		$m_keyword_id = 0;
		foreach ($m_result as $m_single)
		{
		
			$m_keyword = $m_single['keyword'];
			$m_content = $m_single['content'];
			$m_replace = "<span class='classtoolTips$m_keyword_id' style='border-bottom:2px dotted #888;'>$m_keyword</span>";
	
			if (stripos($content,$m_keyword) === false)
			{
				
			}
			else
			{
				//!!! 3.0.1 $content = preg_replace("/(\W)(".$m_keyword.")(?![^<|^\[]*[>|\]])(\W)/is","\\1"."<span class='classtoolTips$m_keyword_id' style='border-bottom:2px dotted #888;'>"."\\2"."</span>"."\\3",$content);
				if ($onlyFirstKeyword == 'all')
				{
					$content = preg_replace("/(\W)(".$m_keyword.")(?![^<|^\[]*[>|\]])(\W)/is","\\1"."<span class='classtoolTips$m_keyword_id' style='border-bottom:2px dotted #888;'>"."\\2"."</span>"."\\3",$content);
				}
			
				if ($onlyFirstKeyword == 'first')
				{
					$content = preg_replace("/(\W)(".$m_keyword.")(?![^<|^\[]*[>|\]])(\W)/is","\\1"."<span class='classtoolTips$m_keyword_id' style='border-bottom:2px dotted #888;'>"."\\2"."</span>"."\\3",$content,1);
				}
			}
			$m_keyword_id++;
		}
	}
	
	do_action('action_after_tooltipsincontent', $content);
	$content = apply_filters( 'filter_after_tooltipsincontent',  $content);
		
	return $content;
}

function nextgenTooltips()
{
?>
<script type="text/javascript">
	jQuery("img").load(function()
	{
		if ((jQuery(this).parent("a").attr('title') != '' )  && (jQuery(this).parent("a").attr('title') != undefined ))
		{
			toolTips(jQuery(this).parent("a"),jQuery(this).parent("a").attr('title'));
		}
		else
		{
			var tempAlt = jQuery(this).attr('alt');
			tempAlt = tempAlt.replace(' ', '');
			if (tempAlt == '')
			{
				
			}
			else
			{
				toolTips(jQuery(this),jQuery(this).attr('alt'));
			}
		}
	}

	);
</script>
<?php
}

function tooltipsAdminHead()
{
?>	
<style type="text/css">
span.question, span.questionimage, span.questionexcerpt, span.questiontags {
  cursor: pointer;
  display: inline-block;
  line-height: 14px;
  width: 14px;
  height: 14px;
  border-radius: 7px;
  -webkit-border-radius:7px;
  -moz-border-radius:7px;
  background: #5893ae;
  color: #fff;
  text-align: center;
  position: relative;
  font-size: 10px;
  font-weight: bold;
}
span.question:hover { background-color: #21759b; }
span.questionimage:hover { background-color: #21759b; }
span.questiontags:hover { background-color: #21759b; }

div.tooltip {
  text-align: left;
  left: 25px;
  top: -55px;
  background: #21759b;
  color: #fff;
  position: absolute;
  z-index: 1000000;
  width: 400px;
  border-radius: 5px;
  -webkit-border-radius:5px;
  -moz-border-radius:5px;
}
div.tooltip:before {
  border-color: transparent #21759b transparent transparent;
  border-right: 6px solid #21759b;
  border-style: solid;
  border-width: 6px 6px 6px 0px;
  content: "";
  display: block;
  height: 0;
  width: 0;
  line-height: 0;
  position: absolute;
  top: 40%;
  left: -6px;
}
div.tooltip p {
  margin: 10px;
  color: #fff;
}
</style>										
<?php
}										
add_action('the_content','tooltipsInContent');
//add_action('the_excerpt','tooltipsInContent');
//add_action('the_tags','tooltipsInContent');
add_action('wp_head', 'tooltipsHead');
add_action('the_content','showTooltips');
//add_action('the_excerpt','showTooltips');
//add_action('the_tags','showTooltipsInTag');
add_action('admin_head', 'tooltipsAdminHead');

$enableTooltipsForExcerpt = get_option("enableTooltipsForExcerpt");
if ($enableTooltipsForExcerpt =='YES')
{
	add_action('the_excerpt','tooltipsInContent');
	add_action('the_excerpt','showTooltips');	
}

$enableTooltipsForTags = get_option("enableTooltipsForTags");
if ($enableTooltipsForTags =='YES')
{
	add_action('the_tags','tooltipsInContent');
	add_action('the_tags','showTooltipsInTag');
}

$enableTooltipsForImageCheck = get_option("enableTooltipsForImage");
if ($enableTooltipsForImageCheck == false)
{
	update_option("enableTooltipsForImage", "YES");
}
if ($enableTooltipsForImageCheck == 'YES')
{
	add_action('wp_footer','nextgenTooltips');
}


function add_tooltips_post_type() {
  $labels = array(
    'name' => __('Tooltips', 'tooltips'),
    'singular_name' => __('Tooltip', 'tooltips'),
    'add_new' => __('Add New', 'tooltips'),
    'add_new_item' => __('Add New Tooltip', 'tooltips'),
    'edit_item' => __('Edit Tooltip', 'tooltips'),
    'new_item' => __('New Tooltip', 'tooltips'),
    'all_items' => __('All Tooltips', 'tooltips'),
    'view_item' => __('View Tooltip', 'tooltips'),
    'search_items' => __('Search Tooltip', 'tooltips'),
    'not_found' =>  __('No Tooltip found', 'tooltips'),
    'not_found_in_trash' => __('No Tooltip found in Trash', 'tooltips'), 
    'menu_name' => __('Tooltips', 'tooltips')
  );
  
  $args = array(
    'labels' => $labels,
    'public' => false,
    'show_ui' => true, 
    'show_in_menu' => true, 
    '_builtin' =>  false,
    'query_var' => "tooltips",
    'rewrite' => false,
    'capability_type' => 'post',
    'has_archive' => false, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'title', 'editor','author','custom-fields','thumbnail' )
  ); 
  register_post_type('tooltips', $args);
}
add_action( 'init', 'add_tooltips_post_type' );

function upgrade_check()
{
	$currentVersion = get_option('ztooltipversion');

	if (empty($currentVersion))
	{
		$m_result = get_option('tooltipsarray');
		if (!(empty($m_result)))
		{
			$m_keyword_id = 0;
			foreach ($m_result as $m_single)
			{
				$m_keyword = $m_single['keyword'];
				$m_content = $m_single['content'];				
				$my_post = array(
  				//'post_title'    => wp_strip_all_tags( $_POST['post_title'] ),
  				'post_title'    => $m_keyword,
  				'post_content'  => $m_content,
  				'post_status'   => 'publish',
  				'post_type'   => 'tooltips',
  				'post_author'   => 1,
				);
				wp_insert_post( $my_post );
			}
		}
	
	}
	update_option('ztooltipversion','3.0.0');
}
add_action( 'init', 'upgrade_check');

function tooltips_get_option($type)
{
	$tooltipsarray = array();
	$m_single = array();
	if ($type == 'tooltipsarray')
	{
		$type = 'tooltips';
		$args=array(
  		'post_type' => $type,
  		'post_status' => 'publish',
  		'posts_per_page' => -1,
  		'caller_get_posts'=> 1
		);
		$my_query = null;
		$my_query = new WP_Query($args);
		if( $my_query->have_posts() ) 
		{
  			while ($my_query->have_posts()) : $my_query->the_post();
  			$m_single = array();
  			$m_single['keyword'] = get_the_title();
			$m_single['content'] = get_the_content();
			$tooltipsarray[] = $m_single;
  			endwhile;
		}
		wp_reset_query();
	}
	return $tooltipsarray;
}

$enableTooltipsForImageCheck = get_option("enableTooltipsForImage");
if ($enableTooltipsForImageCheck == false)
{
	update_option("enableTooltipsForImage", "YES");
}

// version 3.4.3
function showTooltipsInShorcode($content)
{
	global $table_prefix,$wpdb,$post;

	do_action('action_before_showtooltips', $content);
	$content = apply_filters( 'filter_before_showtooltips',  $content);
	

	$curent_content = $content;

	
	$m_result = tooltips_get_option('tooltipsarray');
	$m_keyword_result = '';
	if (!(empty($m_result)))
	{
		$m_keyword_id = 0;
		foreach ($m_result as $m_single)
		{
			
					if (stripos($curent_content,$m_single['keyword']) === false)
					{
						
					}
					else 
					{			
			$m_keyword_result .= '<script type="text/javascript">';
			$m_content = $m_single['content'];
			$m_content = str_ireplace('\\','',$m_content);
			$m_content = str_ireplace("'","\'",$m_content);
			$m_content = preg_replace('|\r\n|', '<br/>', $m_content);
			if (!(empty($m_content)))
			{
				$m_keyword_result .= " toolTips('.classtoolTips$m_keyword_id','$m_content'); ";
			}
			$m_keyword_result .= '</script>';
					}
					$m_keyword_id++;
		}


	}
	$content = $content.$m_keyword_result;
	do_action('action_after_showtooltips', $content);
	$content = apply_filters( 'filter_after_showtooltips',  $content);
	return $content;
}
// version 3.4.3
function tooltips_list_shortcode($atts)
{
	global $table_prefix,$wpdb,$post;

	$args = array( 'post_type' => 'tooltips', 'post_status' => 'public' );
	$loop = new WP_Query( $args );
	$return_content = '';
	$return_content .= '<div class="tooltips_directory">';
	while ( $loop->have_posts() ) : $loop->the_post();
		$return_content .= '<div class="tooltips_list">'.get_the_title().'</div>';
	endwhile;
	$return_content = tooltipsInContent($return_content);
	$return_content = showTooltipsInShorcode($return_content);

	$return_content .= '</div>';
	
	return $return_content;
}
// version 3.4.3
add_shortcode( 'tooltipslist', 'tooltips_list_shortcode' );

function footernav()
{
	//version 3.4.5
?>
<script type="text/javascript">
jQuery('.tooltips_directory').directory();
</script>
<?php
}
add_action('wp_footer','footernav');
?>