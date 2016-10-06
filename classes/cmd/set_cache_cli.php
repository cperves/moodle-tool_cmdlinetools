<?php
/**
 * set config vars admin cli
 *
 * @package  
 * @subpackage 
 * @copyright  2015 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->dirroot.'/cache/locallib.php');

class set_cache_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'definition', 2=>'mappings');
	
	}

	function process_options(){
		$var_definition=$this->arguments['definition'];
		$var_mappings=$this->arguments['mappings'];
		if(!isset($var_definition) || !isset($var_mappings) ){
			cli_writeln(get_string('definitionmappingsrequired_set_cache_cli','tool_cmdlinetools'));
			cli_writeln($help);
			return false;
		}
		$factory = cache_factory::instance();
		list($component, $area) = explode('/', $var_definition, 2);
		$config = cache_config::instance();
		$writer = cache_config_writer::instance();
		$writer->update_definitions();
		$definition_check = $writer->get_definition_by_id($var_definition);
		if (!$definition_check) {
			cli_writeln(get_string('notexistingdefinition_set_cache_cli','tool_cmdlinetools',$var_definition));
			return false;
		}
		
		
		$definition = $factory->create_definition($component, $area);
		$currentstores = $config->get_stores_for_definition($definition);
		$possiblestores = $config->get_stores($definition->get_mode(), $definition->get_requirements_bin());
		
		$var_mappings = explode(',',$var_mappings);
		$mappings = array();
		foreach ($var_mappings as $index => $var_mapping){
			//check mapping is available
			if (array_key_exists($var_mapping, $possiblestores)){
				$mappings[$index]= $var_mapping;
			}else if(!empty($var_mapping)){
				cli_writeln(get_string('badstoreinstancename_set_cache_cli','tool_cmdlinetools',$var_mapping));
				return false;
			}
		}
		
		$writer->set_definition_mappings($var_definition, $mappings);
		return true;
		
	}
	
	
}