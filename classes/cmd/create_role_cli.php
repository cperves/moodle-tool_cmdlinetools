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
require_once($CFG->libdir.'/testing/generator/data_generator.php');
class create_role_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'shortname');
		$this->longoptions= array(
								'description'       => '',
								'name'              => '',
								'archetype'         => '',
								'help'              => false
							);
		$this->shortmapping = array(
								'h' => 'help',
								'd' => 'description',
								'n'=>'name',
								'a'=>'archetype'
						);
	}
	public function process_options(){
		global $CFG,$DB;
		//don't create if already exists
		$role = $DB->get_record('role', array('shortname' => $this->arguments['shortname']));
		if ($role) {
			cli_error(get_string('create_role_cli_rolexexists','tool_cmdlinetools',$this->arguments['shortname']));
		}
		$generator = new \testing_data_generator();
		$record = $this->options;
		//remove help
		unset($record['help']);
		//add shortname
		$record['shortname']=$this->arguments['shortname'];
		$newroleid = $generator->create_role($record);
		cli_writeln(get_string('create_role_cli_createrolexid','tool_cmdlinetools',$newroleid));
		return true;
	}
}
