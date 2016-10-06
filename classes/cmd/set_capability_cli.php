<?php

/**
 * set capability for context and role admin cli
 *
 * @package  
 * @subpackage 
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */


require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/accesslib.php');

class set_capability_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'capability', 2=>'permission', 3=>'context', 4=>'role');
		
	}
	protected function set_help_parameters(){
		$this->help_parameters =  array('CAP_INHERIT'=>CAP_INHERIT, 'CAP_ALLOW'=>CAP_ALLOW, 'CAP_PREVENT'=>CAP_PREVENT, 'CAP_PROHIBIT'=> CAP_PROHIBIT );
	}

	function process_options(){
		global $DB;
		$system_context=context_system::instance();
		//check capability
		if(get_capability_info($this->arguments['capability'])==null){
			echo get_string('capabilitynotexists_set_capability_cli','tool_cmdlinetools',$this->arguments['capability']);
			return false;
		}
		//check permission
		if(!is_numeric($this->arguments['permission']) || !in_array($this->arguments['permission'],array(CAP_INHERIT,CAP_ALLOW,CAP_PREVENT,CAP_PROHIBIT))){
			echo get_string('permissionvalues_set_capability_cli','tools_cmdlinetools',array('CAP_INHERIT'=>CAP_INHERIT, 'CAP_ALLOW'=>CAP_ALLOW, 'CAP_PREVENT'=>CAP_PREVENT, 'CAP_PROHIBIT'=> CAP_PROHIBIT ));
			return false;
		}
		//check context
		if(!is_numeric($this->arguments['context'])){
			echo "context must be an int\n";
			die;
		}
		$this->arguments['context']= (int) $this->arguments['context'];
		$context=false;
		try{
			$context=context::instance_by_id($this->arguments['context']);
		}catch(Exception $ex){
			$context=false;
		}
		if(!$context){
			echo "entenred context does not exist\n";
			return false;
		}
		//check role
		$role=$DB->get_record('role', array('shortname'=>$this->arguments['role']));
		if(!$role){
			echo "role $this->arguments['role'] does not exist\n";
			return false;
		}	
		assign_capability($this->arguments['capability'], $this->arguments['permission'], $role->id, $this->arguments['context'],true);
		return true;
	}	
}