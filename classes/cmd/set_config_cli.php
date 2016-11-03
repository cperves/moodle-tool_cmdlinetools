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

class set_config_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'name', 2=>'value');
		$this->longoptions= array(
								'help'              => false,
								'check' 			=> false
							);
		$this->shortmapping = array(
								'h' => 'help',
								'c'=>'check'
						);
		
	}		 
	function process_options(){
		$var_name=$this->arguments['name'];
		$var_name = explode('/',$var_name);
		$var_value=$this->arguments['value'];
		if(!isset($var_name) || !isset($var_value) ){
			cli_writeln(get_string('namevaluerequired_set_config_cli','tool_cmdlinetools'));
			cli_writeln($help);
			return false;
		}
		
		$name = array_pop($var_name);
		$plugin = (count($var_name)>0?implode('/',$var_name):null);
		if($this->options['check']){
			if(!property_exists(get_config($plugin), $name)){
				cli_error(get_string('settingsnamenotexists_set_config_cli','tool_cmdlinetools',implode('/',$var_name)));
			}
		}
		$config = get_config($plugin, $name);
		set_config($name, $var_value, $plugin);
		return true;
		
	}
	
	
}