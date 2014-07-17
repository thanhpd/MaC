<?php

/*
* Add your own functions here. You can also copy some of the theme functions into this file. 
* Wordpress will use those functions instead of the original functions then.
*/
add_filter( 'avf_google_heading_font',  'avia_add_heading_font');
function avia_add_heading_font($fonts)
{
$fonts['RobotoVN'] = 'Roboto:400,600,700,900&subset=latin,vietnamese';
return $fonts;
}

add_filter( 'avf_google_content_font',  'avia_add_content_font');
function avia_add_content_font($fonts)
{
$fonts['RobotoVN'] = 'Roboto:400,600,700,900&subset=latin,vietnamese';
return $fonts;
}

add_filter('avf_title_args', 'change_post_title', 10, 2);
function change_post_title($args, $id){

	if ( $args['title'] == 'Blog - Latest News' )
	{
		$post_categories = wp_get_post_categories( $id );
		$cats = array();
		foreach($post_categories as $c){
			$cat = get_category( $c );
			$cats[] = array( 'name' => $cat->name, 'slug' => $cat->slug );
		}
		$args['title'] = get_the_category_by_ID($post_categories[0]);
		$args['link'] = get_category_link( $post_categories[0] );
	}
	return $args;
}

$avia_config['layout']['fullsize'] 		= array('content' => 'twelve alpha', 'sidebar' => 'hidden', 	 'meta' => 'two alpha', 'entry' => 'eleven');
$avia_config['layout']['sidebar_left'] 	= array('content' => 'eight', 		 'sidebar' => 'four alpha' ,'meta' => 'two alpha', 'entry' => 'eight');
$avia_config['layout']['sidebar_right'] = array('content' => 'eight alpha',   'sidebar' => 'four alpha', 'meta' => 'two alpha', 'entry' => 'eight alpha');

?>