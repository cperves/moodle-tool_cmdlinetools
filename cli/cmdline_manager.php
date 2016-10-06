<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!defined('CLI_SCRIPT')){
	define('CLI_SCRIPT', true);
}
require_once(dirname(dirname(dirname(__FILE__))).'../../../config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
if(count($_SERVER['argv'])<2){
	cli_error(get_string('toofewargs','tool_cmdlinetools'));
}
$cmd = $_SERVER['argv'][1];
if(strpos($cmd, '-')!==0){
	$classname = $cmd.'_cli';
	$filename = $CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmd/'.$classname.'.php';
	if(!file_exists($filename)){
		cli_error(get_string('manager_notexistingfilename','tool_cmdlinetools',$filename),1);
	}
	require_once($filename);
	$cmd_cli = new $classname();
	$cmd_cli->process();
}else{
	require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
	$cmd_cli = new cmdlinecli();
}
