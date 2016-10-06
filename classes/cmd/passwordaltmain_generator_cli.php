<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2015 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/clilib.php');
class passwordaltmain_generator_cli extends cmdlinecli{
	public function process_options(){
		global $CFG;
		cli_writeln(complex_random_string());
		return true;
	}
}
