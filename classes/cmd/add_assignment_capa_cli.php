<?php
/**
 * add assignment capas cli
 * add capabilities to take in charge visibility control of feedback or assignment plugins on contexts 
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');

class 	add_assignment_capa_cli extends cmdlinecli{


	function process_options(){
		global $DB;
		//list of assignment and feedback plugins
		$capaslist = array();
		$plugins = core_component::get_plugin_list('assignfeedback');
		$index = 0;
		foreach ($plugins as $plugin=>$path){
			if(!get_capability_info('local/assignment_capas:assign_feedback_'.$plugin.'_addinstance')){
				$index++;
				$capaslist[$index]='local/assignment_capas:assign_feedback_'.$plugin.'_addinstance';
				echo "$index : $plugin (feedback)\n";
			}
		}
		$plugins = core_component::get_plugin_list('assignsubmission');
		foreach ($plugins as $plugin=>$path){
			if(!get_capability_info('local/assignment_capas:assign_submission_'.$plugin.'_addinstance')){
				$index++;
				$capaslist[$index]='local/assignment_capas:assign_submission_'.$plugin.'_addinstance';
				echo "$index : $plugin (submission)\n";
			}
		}
		
		if($index == 0){
			cli_writeln(get_string('add_assignmentcapa_cli_noassignplugin','tool_cmdlinetools'));
			die;
		}
		$promptmsg = get_string('add_assignmentcapa_cli_choose_plugin', 'tool_cmdlinetools');
		//create capability
		$var_index = add_assignment_capa_cli::int_values_check_and_prompt(range(1,$index),$promptmsg);
		$capability = new stdClass();
		$capability->name         = $capaslist[$var_index];
		$capability->captype      = 'read';
		$capability->contextlevel = CONTEXT_MODULE;
		$capability->component='admin_cli'; //unexisting plugin component to prevent cache deletion while updating access.php plugin
		$DB->insert_record('capabilities', $capability, false);
		//apply it to manager
		assign_legacy_capabilities($capaslist[$var_index], array(	'manager' => CAP_ALLOW));
		cache::make('core', 'capabilities')->delete('core_capabilities');
		cli_writeln(get_string('add_assignmentcapa_cli_capxcreated', 'tool_cmdlinetools', $capaslist[$var_index]));
		return true;
		
	}

	function int_values_check_and_prompt($possiblevalues,$promptmsg){
		$var = trim(cli_input($promptmsg));
		if(!is_numeric($var)){
			cli_writeln(get_string('add_assignmentcapa_cli_mustbeint','tool_cmdlinetools'));
			return add_assignment_capa_cli::int_values_check_and_prompt($possiblevalues,$promptmsg);
		}
		if(!in_array($var, $possiblevalues)){
			cli_writeln(get_string('add_assignmentcapa_cli_mustbeint','tool_cmdlinetools'));
			return add_assignment_capa_cli::int_values_check_and_prompt($possiblevalues,$promptmsg);
		}
		return $var;
	}
}