<?php
/**
 * @package Hashtag
 */
/*
Plugin Name: Hashtag
Plugin URI: http://takien.com/plugins/hashtag
Description: Converts hashtag strings into clickable link in WordPress. If clicked it will search contents contain same hashtag.
Version: 0.5
Author: takien
Author URI: http://takien.com
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

defined('ABSPATH') or die();

require_once(dirname(__FILE__).'/options/easy-options.php');

if(!class_exists('HashtagPluginOption')) {
	class HashtagPluginOption extends EasyOptions {
		function init() {
			add_filter( 'the_content', array(&$this,'hashtag_regex_replace'),200,1 );
			add_action( 'admin_enqueue_scripts', array(&$this,'hastag_plugin_enqueue_script' ) );
			add_action( 'wp_head', array(&$this,'hashtag_plugin_script'));
		}
		
		function hashtag_regex_replace( $content ) {
			$regular   = easy_options('regular_search','hashtag-plugin-option');
			$class     = easy_options('link_class','hashtag-plugin-option');
			$hash      = $regular ? '' : '%23';
			$class     = $class ? $class : 'hashtag';
			$content = str_replace('>#','> #',$content);
			$content = preg_replace('/(?<!:|\s|&|"|\')(\s)#([^\s<]+)/i', '<span class="'.$class.'">\1#<a href="'.site_url().'?s='.$hash.'\2">\2</a></span>', $content);
			return $content;
		}
		
		function hastag_plugin_enqueue_script( $hook_suffix ) {
			if(!preg_match('/(hashtag-plugin)/i',$hook_suffix)) return;
			wp_enqueue_script( 'hashtag-jscolor', plugins_url( 'options/jscolor/jscolor.js' ,__FILE__ ),Array(), 0.5, false );
		}
		
		function hashtag_plugin_script() {
			$class = easy_options('link_class','hashtag-plugin-option');
			$color = easy_options('link_color','hashtag-plugin-option');
			$bgcolor = easy_options('bg_color','hashtag-plugin-option');
			$add   = easy_options('additional_css','hashtag-plugin-option');
			
			$class = $class ? $class : 'hashtag';
			$color = $color ? $color : '#0084B4';
			$bgcolor = $bgcolor ? $bgcolor : 'transparent';
			?>
		<style type="text/css">
		<?php
			echo ".$class,
				.$class a {
					color: $color !important;
					background-color: $bgcolor !important;
					text-decoration:none;
					$add
				}
				.$class a:hover {
					text-decoration:underline;
				}
			";
		?>
		</style>
			<?php
		}
		function form($fields){
			$output ='<table class="form-table">';
				foreach($fields as $field){
					$field['rowclass'] = isset($field['rowclass']) ? $field['rowclass'] : false;
					$value = $this->option($field['name']) ? $this->option($field['name']) : (isset($field['value']) ? $field['value'] : null);
					
					if($this->option($field['name']) === '') {
						$value = '';
					}
					$field['attr']  = isset($field['attr']) ? $field['attr'] : '';
					$field['class'] = isset($field['class']) ? $field['class'] : '';
					
					if ( $field['type']=='checkbox' ) {
						$field['attr'] = $field['attr']. ' '.(($value) ? 'checked="checked"' : '');
					}
					
					$field['name'] = $this->group.'['.$field['name'].']';
			
					if($field['type']=='text'){
						$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
						$output .= '<td><input class="'.$field['class'].' regular-text" type="text" id="'.$field['name'].'" name="'.$field['name'].'" value="'.$value.'" />';
						$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
					}
					if($field['type']=='checkbox'){
						$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
						$output .= '<td><input type="hidden" name="'.$field['name'].'" value="" /><input type="checkbox" id="'.$field['name'].'" name="'.$field['name'].'" value="1" '.$field['attr'].' />';
						$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
					}
					if($field['type']=='textarea'){
						$output .= '<tr><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
						$output .= '<td style="vertical-align:top"><textarea style="width:400px;height:150px" id="'.$field['name'].'" name="'.$field['name'].'">'.esc_textarea($value).'</textarea>';
						$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
					}
				}
			$output .= '</table>';
			return $output;
		}		
	}
}
$hashtag_plugin['icon_large'] = plugins_url( 'images/icon-large.png' , __FILE__  );
$hashtag_plugin['icon_small'] = plugins_url( 'images/icon-small.png' , __FILE__  );


$hashtag_plugin_config = new HashtagPluginOption(Array(
    'group'             => 'hashtag-plugin-option', 
    'menu_name'         => '#Hashtag', 
    'page_title'        => '#Hashtag Option', 
    'menu_slug'         => 'hashtag-plugin-option', 
    'menu_location'     => 'add_options_page', 
    'icon_big'          => $hashtag_plugin['icon_large'],
    'icon_small'        => $hashtag_plugin['icon_small']

));
$hashtag_plugin_config->fields = Array(
	Array(
		'name'         => 'link_class',
		'label'        => 'Link class',
		'type'         => 'text',
		'value'        => 'hashtag',
		'description'  => 'Class for hashtag link'),
	Array(
		'name'         => 'link_color',
		'label'        => 'Link color',
		'type'         => 'text',
		'class'         => 'color {hash:true}',
		'value'        => '#0084b4',
		'description'  => 'Color for hashtag link'),
	Array(
		'name'         => 'bg_color',
		'label'        => 'Background color',
		'type'         => 'text',
		'class'         => 'color {hash:true}',
		'value'        => '#FFFFFF',
		'description'  => 'Bacckground color for hashtag link'),
	Array(
		'name'         => 'additional_css',
		'label'        => 'Additional CSS properties',
		'type'         => 'textarea',
		'value'        => "font-weight:bold;",
		'description'  => 'Additional CSS properties for hashtag link, separate each properties with semicolon ( ; ) and new line, separate key and value with colon ( : )'),
	Array(
		'name'         => 'regular_search',
		'label'        => 'Perform regular search',
		'type'         => 'checkbox',
		'description'  => 'If checked, hashtag will search for "keyword" instead of "#keyword"')
);