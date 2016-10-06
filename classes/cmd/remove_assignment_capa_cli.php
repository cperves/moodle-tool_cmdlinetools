<?php
/**
 * remove assignment capas admin cli
 * remove the capability to take in charge visibility control of feedback or assignment plugins on contexts 
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 */

require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');

class 	remove_assignment_capa_cli extends cmdlinecli{

	function process_options(){
		global $DB;
		//list of assignment and feedback plugins
		$capaslist = array();
		$plugins = core_component::get_plugin_list('assignfeedback');
		$index = 0;
		foreach ($plugins as $plugin=>$path){
			if(get_capability_info('local/assignment_capas:assign_feedback_'.$plugin.'_addinstance')){
				$index++;
				$capaslist[$index]='local/assignment_capas:assign_feedback_'.$plugin.'_addinstance';
				echo "$index : $plugin (feedback)\n";
			}
		}
		$plugins = core_component::get_plugin_list('assignsubmission');
		foreach ($plugins as $plugin=>$path){
			if(get_capability_info('local/assignment_capas:assign_submission_'.$plugin.'_addinstance')){
				$index++;
				$capaslist[$index]='local/assignment_capas:assign_submission_'.$plugin.'_addinstance';
				echo "$index : $plugin (submission)\n";
			}
		}

		if($index == 0){
			cli_writeln(get_string('remove_assignmentcapa_cli_noassignplugin','tool_cmdlinetools'));
			die;
		}
		$promptmsg = get_string('remove_assignmentcapa_cli_choose_plugin', 'tool_cmdlinetools');
		$var_index = remove_assignment_capa_cli::int_values_check_and_prompt(range(1,$index),$promptmsg);
		$errors = 0;
		if ($roles = get_roles_with_capability($capaslist[$var_index])) {
			foreach($roles as $role) {
				if (!unassign_capability($capaslist[$var_index], $role->id)) {
					$errors++;
					cli_writeln(get_string('remove_assignmentcapa_cli_cannotunassignrolex','tool_cmdlinetools',$role->shortname));
				}
			}
		}
		if($errors == 0){
			$DB->delete_records('capabilities', array('name'=>$capaslist[$var_index]));
		}else{
			cli_writeln(get_string('remove_assignmentcapa_cli_capaxnotremoved','tool_cmdlinetools',$capaslist[$var_index]));
			die;
		}
		cache::make('core', 'capabilities')->delete('core_capabilities');
		cli_writeln(get_string('remove_assignmentcapa_cli_capxremoved', 'tool_cmdlinetools', $capaslist[$var_index]));
		return true;

	}

	function int_values_check_and_prompt($possiblevalues,$promptmsg){
		$var = trim(cli_input($promptmsg));
		if(!is_numeric($var)){
			cli_writeln(get_string('remove_assignmentcapa_cli_mustbeint','tool_cmdlinetools'));
			return remove_assignment_capa_cli::int_values_check_and_prompt($possiblevalues,$promptmsg);
		}
		if(!in_array($var, $possiblevalues)){
			cli_writeln(get_string('remove_assignmentcapa_cli_mustbeint','tool_cmdlinetools'));
			return remove_assignment_capa_cli::int_values_check_and_prompt($possiblevalues,$promptmsg);
		}
		return $var;
	}
}
