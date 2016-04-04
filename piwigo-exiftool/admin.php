<?php
// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

load_language('plugin.lang', EXIFTOOL_PATH);

// +-----------------------------------------------------------------------+
// | Check Access and exit when user status is not ok                      |
// +-----------------------------------------------------------------------+
check_status(ACCESS_ADMINISTRATOR);

// Fetch the template.
global $template, $conf;

// pass config parameters to template
$template->assign(array(
  'exiftool_path' => $conf['exiftool_conf']['exiftool_path'],
  'exiftool_flags' => $conf['exiftool_conf']['exiftool_flags'],
));

// Update conf if submitted in admin site
if (isset($_POST['submit']))
{
	$conf['exiftool_conf'] = array(
		'exiftool_path'		=> $_POST['exiftool_path'],
		'exiftool_flags'	=> $_POST['exiftool_flags'],
	);

	// Update config to DB
	conf_update_param('exiftool_conf', serialize($conf['exiftool_conf']));
}

// Add our template to the global template
$template->set_filenames(
 array(
   'plugin_admin_content' => dirname(__FILE__).'/admin.tpl'
 )
);
 
// Assign the template contents to ADMIN_CONTENT
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
?>
