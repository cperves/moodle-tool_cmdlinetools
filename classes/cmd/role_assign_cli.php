<?php
/**
 * hide show a plugin
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/testing/generator/data_generator.php');
class role_assign_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'username', 2=>'shortname', 3=>'contextid');
		
	}
	public function process_options(){
		global $CFG,$DB;
		$user = $DB->get_record('user', array('username' => $this->arguments['username']));
		if (!$user) {
			cli_error(get_string('role_assign_cli_userxnotexists','tool_cmdlinetools',$this->arguments['username']));
		}
		$role = $DB->get_record('role', array('shortname' => $this->arguments['shortname']));
		if (!$role) {
			cli_error(get_string('role_assign_cli_rolexnotexists','tool_cmdlinetools',$this->arguments['shortname']));
		}
		$context = context::instance_by_id($this->arguments['contextid']);
		if (!$context) {
			cli_error(get_string('role_assign_cli_contextxnotexists','tool_cmdlinetools',$this->arguments['contextid']));
		}
		role_assign($role->id, $user->id, $context->id);
		return true;
	}
}
