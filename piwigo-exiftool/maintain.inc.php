<?php
/**
	Version: 0.1
	Plugin Name: Exiftool
	Plugin URI: // Here comes a link to the Piwigo extension gallery, after
			   // publication of your plugin. For auto-updates of the plugin.
	Author: Thomas Feuster
 */

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install()
{
	if (!defined('EXIFTOOL_PATH'))
		define('EXIFTOOL_PATH', PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)).'/');

	$default_config = array(
		'exiftool_path'		=> 'exiftool',
		'exiftool_flags'	=> '',
	);
	/* Add configuration to the config table */
	$conf['exiftool_conf'] = serialize($default_config);
	conf_update_param('exiftool_conf', $conf['exiftool_conf']);

	$q = 'UPDATE '.CONFIG_TABLE.' SET `comment` = "Configuration settings for piwigo-exiftool plugin" WHERE `param` = "exiftool_conf";';
	pwg_query( $q );
}

function plugin_activate()
{
	global $conf;

	if ( (!isset($conf['exiftool_conf']))
		or (count($conf['exiftool_conf'], COUNT_RECURSIVE) != 2))
	{
		plugin_install();
	}
}

function plugin_uninstall()
{
	/* Remove configuration from the config table */
	conf_delete_param('exiftool_conf');
}
?>
