<?php
/*
	Version: 0.1
	Plugin Name: Exiftool
	Plugin URI: // Here comes a link to the Piwigo extension gallery, after
			   // publication of your plugin. For auto-updates of the plugin.
	Author: Thomas Feuster
	Description:
	Add support of the exiftool from http://www.sno.phy.queensu.ca/~phil/exiftool/.
	In two places changes are made:
	include/functions_metadata.inc.php, get_exif_data : call exiftool instead of read_exif_data
	plugins/exif_view/main.inc.php , exif_key_translation (if installed) : return without doing any translations
*/

// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
 
// Define the path to our plugin.
define('EXIFTOOL_PATH', PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)).'/');

global $conf;

// Prepare configuration
$conf['exiftool_conf'] = unserialize($conf['exiftool_conf']);

// Hook on to an event to show the administration page.
add_event_handler('get_admin_plugin_menu_links', 'exiftool_admin_menu');

// Add an entry to the 'Plugins' menu.
function exiftool_admin_menu($menu) {
 array_push(
   $menu,
   array(
     'NAME'  => 'Exiftool',
     'URL'   => get_admin_plugin_menu_link(dirname(__FILE__)).'/admin.php'
   )
 );
 return $menu;
}
?>
