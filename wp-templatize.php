<?php
/*
Plugin Name: WP Templatize
Plugin URI: http://www.github.com/wp-templatize/
Description: Enables the templates of your wordpress site to be extracted automatically.
Version: 0.01
Author: Martin Andrews
Author URI: http://www.platformedia.com
*/

/*  Copyright 2010 Martin Andrews (email : Templatize@-no-spam-platformedia.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// templatize_ prefix used throughout...
register_activation_hook(__FILE__, 'templatize_add_defaults');
register_uninstall_hook(__FILE__, 'templatize_delete_plugin_options');
add_action('admin_init', 'templatize_init' );
add_action('admin_menu', 'templatize_add_options_page');

$templatize_options = get_option('templatize_options');
if(isset($_GET[$templatize_options['action']])) {
 add_action('plugins_loaded', 'templatize_register_hooks' );
}

// ***************************************
// *** START - Create Admin Options    ***
// ***************************************

// delete options table entries ONLY when plugin deactivated AND deleted
function templatize_delete_plugin_options() {
	delete_option('templatize_options');
}

// ***************************************
// http://codex.wordpress.org/Plugin_API/Filter_Reference
// ***************************************

$templetize_sections=array(
 'the_content',
 'the_title',
 'comment_text',
 'get_comment_author',
 'wp_tag_cloud',
 'the_generator',
);
$templetize_sections_special=array(
 'wp_nav_menu_args', # menus will be oblitorated...
);

// Define default option settings
function templatize_add_defaults() {
 global $templetize_sections;
	$tmp = get_option('templatize_options');
 
// if(($tmp['chk_default_options_db']=='1') || (!is_array($tmp))) {
 if(true || (!is_array($tmp))) {
		delete_option('templatize_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(
   "action" => "templatize",
  );
/*  
  foreach($templetize_sections as $item) {
   echo $item.'<br />';
   $arr['start_'.$item]='<template-'.$item.'>';
   $arr['include_'.$item]=1;
   $arr['end_'.$item]='</template-'.$item.'>';
  }
  // Now the special cases
  $arr['surround_'.'wp_nav_menu_args']='template-menu-container';
*/  
		update_option('templatize_options', $arr);
	}
}

//templatize_add_defaults();


// Init plugin options to white list our options
function templatize_init(){
	// put the below into a function and add checks all sections (especially radio buttons) have a valid choice (i.e. no section is blank)
	// this is primarily to check newly added options have correct initial values
	$tmp = get_option('templatize_options');
//	if(!$tmp['rdo_strict_filtering']) {   // check strict filtering option has a starting value
//		$tmp["rdo_strict_filtering"] = "strict_off";
//		update_option('templatize_options', $tmp);
//	}
	register_setting( 'templatize_plugin_options', 'templatize_options', 'templatize_validate_options' );
	templatize_legacy();
}

function templatize_legacy() {
	// delete legacy options - if they don't exist it just returns false
 //	delete_option('templatize_something');
}

// Add menu page
function templatize_add_options_page() {
	add_options_page('WP Templatize Options Page', 
  'WP Templatize', 
  'manage_options', 
  __FILE__,
  'templatize_render_form'
 );
}

// Draw the menu page itself
function templatize_render_form() {
 global $templetize_sections;
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>WP Templatize Options</h2>
		<p>Configure the plugin options below</p>
<style type="text/css">
#templetize-admin input[type="text"] {
 width:400px;
}
</style>
		<form method="post" action="options.php">
			<?php settings_fields('templatize_plugin_options'); ?>
			<?php $options = get_option('templatize_options'); ?>
			<table xclass="form-table" id="templetize-admin">
				<tr>
					<th scope="row" style="text-align:left;">Query String to Activate</th>
					<td>
						<input name="templatize_options[action]" type='text' value="<?php echo $options['action']; ?>" style="width:200px;" />
<!--      
      <br /><span style="color:#666666;margin-left:2px;">Separate keywords with commas</span>
!-->
					</td>
				</tr>
<?php
 foreach($templetize_sections as $item) {
  $start_text = $options['start_'.$item];
  $checked    = $options['include_'.$item]?' checked':'';
  $end_text   = $options['end_'.$item];
  echo <<<FILTERBLOCK
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row" style="text-align:left;">Filter for '$item'</th>
					<td>
						<input name="templatize_options[start_$item]" type='text' value="$start_text" /><br />
						<label><input name="templatize_options[include_$item]" type="checkbox" value="1"$checked /> Include contents of $item</label><br />
						<input name="templatize_options[end_$item]" type='text' value="$end_text" /><br />
      <br />
					</td>
				</tr>
FILTERBLOCK;
 }
 if(version_compare(get_bloginfo('version'), '3.0') >= 0) {  # Version >=3.00
?>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row">Menu surround</th>
					<td>
						<input name="templatize_options[surround_wp_nav_menu_args]" type='text' value="<?php echo $options['surround_wp_nav_menu_args']; ?>" /><br />
      <br />
					</td>
				</tr>
<?php
 }
?>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row">Load Defaults (careful!)</th>
					<td>
						<select name="templatize_options[defaults]"> 
       <option value="" selected></option>
       <option value="xhtml">XHTML surrounding tags</option>
       <option value="mako">Ready for .mako creation</option>
      </select>
      <br />
					</td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>

	</div>
 
 
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function templatize_validate_options($input) {
 global $templetize_sections;

 // strip html from textboxes
//	$input['templatize_something'] =  wp_filter_nohtml_kses($input['templatize_something']);

 $type=$input['defaults'];
 if($type) {
// 	$arr = get_option('templatize_options');
  if($type=='xhtml') {
   foreach($templetize_sections as $item) {
    $input['start_'.$item]='<template-'.$item.'>';
    $input['include_'.$item]=1;
    $input['end_'.$item]='</template-'.$item.'>';
   }
   // Now the special cases
   $input['surround_'.'wp_nav_menu_args']='template-menu-container';
  }
  else if($type=='mako') {
   foreach($templetize_sections as $item) {
    $input['start_'.$item]='';
    $input['include_'.$item]=0;
    $input['end_'.$item]='${c.wp.'.$item.'}';
   }
   // Now the special cases
   $input['surround_'.'wp_nav_menu_args']='template-menu-container';  // Hmm...
  }
//		update_option('templatize_options', $arr);
 }
	return $input;
}

// ***************************************
// *** END - Create Admin Options    ***
// ***************************************

// ---------------------------------------------------------------------------------

// ***************************************
// *** START - Plugin Core Functions   ***
// ***************************************

function templatize_register_hooks() {
 global $templetize_sections, $templetize_sections_special;
	$tmp = get_option('templatize_options');
 
 foreach($templetize_sections as $item) {
  add_filter($item, 'templatize_filter_'.$item); 
 }
 foreach($templetize_sections_special as $item) { 
  add_filter($item, 'templatize_filter_special_'.$item); 
 }
}

// This should be some sort of lambda fn...
function templatize_filter($section, $content) {
	$tmp = get_option('templatize_options');
 return $tmp['start_'.$section] . ($tmp['include_'.$section]?$content:'') . $tmp['end_'.$section] ;
}


function templatize_filter_the_content($text) {
 return templatize_filter('the_content', $text);
}

function templatize_filter_the_title($text) {
 return templatize_filter('the_title', $text);
}

function templatize_filter_comment_text($text) {
 return templatize_filter('comment_text', $text);
}

function templatize_filter_get_comment_author($text) {
 return templatize_filter('get_comment_author', $text);
}

function templatize_filter_wp_tag_cloud($text) {
 return templatize_filter('wp_tag_cloud', $text);
}


// These are for the strange functions - not standard changes

// This only exists for version>=3.0.0
function templatize_filter_special_wp_nav_menu_args( $args = '' ) {
	$tmp = get_option('templatize_options');
 if($tmp['surround_'.'wp_nav_menu_args']) { # If it's not set, don't change anything
  $args['container'] = $tmp['surround_'.'wp_nav_menu_args'];
 }
	return $args;
}

// ***************************************
// *** END - Plugin Core Functions     ***
// ***************************************
