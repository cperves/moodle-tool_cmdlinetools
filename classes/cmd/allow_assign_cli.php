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
require_once($CFG->libdir.'/accesslib.php');
class allow_assign_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'role', 2=>'targetrole');
		$this->longoptions= array(
				'disallow'       => false,
		);
		$this->shortmapping = array(
				'd' => 'disallow',
		);
	}
	public function process_options(){
		global $CFG,$DB;
		//don't create if already exists
		$role = $DB->get_record('role', array('shortname' => $this->arguments['role']));
		if (!$role) {
			cli_error(get_string('allow_assign_cli_rolexnotexists','tool_cmdlinetools',$this->arguments['role']));
		}
		$targetrole = $DB->get_record('role', array('shortname' => $this->arguments['targetrole']));
		if (!$role) {
			cli_error(get_string('allow_assign_cli_rolexnotexists','tool_cmdlinetools',$this->arguments['targetrole']));
		}
		if($this->options['disallow']){
			$DB->delete_records('role_allow_assign',array('roleid'=>$role->id,'allowassign'=>$targetrole->id));
			cli_writeln(get_string('allow_assign_cli_disallowed','tool_cmdlinetools'));
		}else{
			if(!$DB->get_record('role_allow_assign', array('roleid'=>$role->id, 'allowassign'=>$targetrole->id))){
				allow_assign($role->id, $targetrole->id);
			}
			cli_writeln(get_string('allow_assign_cli_allowed','tool_cmdlinetools'));
		}
		return true;
	}
}
