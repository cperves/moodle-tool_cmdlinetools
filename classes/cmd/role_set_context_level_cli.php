<?php
/**
 * hide show a plugin
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @author inspired from moosh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/accesslib.php');
class role_set_context_level_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'shortname', 2=>'contextlevels');
	}
	public function process_options(){
		global $CFG,$DB;
		//don't create if already exists
		$role = $DB->get_record('role', array('shortname' => $this->arguments['shortname']));
		if (!$role) {
			cli_error(get_string('role_set_context_level_cli_rolexnotexists','tool_cmdlinetools',$this->arguments['shortname']));
		}
		$contextlevels = explode(',',$this->arguments['contextlevels']);
		set_role_contextlevels($role->id, $contextlevels);
		return true;
	}
}
