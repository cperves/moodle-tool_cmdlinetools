<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/clilib.php');

class cmdlinecli{
	protected $options = array();
	protected $longoptions = array();
	protected $shortmapping= array();
	protected $waitedarguments = array();
	protected $arguments = array();
	protected $unrecognized = array();
	protected $help = '';
	protected $help_parameters = array();
	protected $ondie = false;
	
	public function __construct(){
		$this->set_help_parameters();
		$this->define_arguments_and_options();
		//add help option anyway
		if(!array_key_exists('help',$this->longoptions)){
			$this->longoptions['help']=false;
		}
		if(!array_key_exists('h',$this->shortmapping)){
			$this->shortmapping['h']='help';
		}
		//add ondie
		if(!array_key_exists('ondie',$this->longoptions)){
			$this->longoptions['help']=false;
		}
		if(!array_key_exists('d',$this->shortmapping)){
			$this->shortmapping['d']='ondie';
		}
		$result = $this->init();
		cli_heading(get_string('clititle_'.get_class($this),'tool_cmdlinetools'));
		if(!$result){
			cli_error(get_string('clifailed_'.get_class($this),'tool_cmdlinetools'));
		}
		if($this->ondie){
			ob_end_clean();
		}
	}

	protected function define_arguments_and_options(){}
	protected function set_help_parameters(){
		
	}
	 
	protected function get_cmd_arguments(){
		//no arguments at all but maybe help option
		if (array_key_exists('help', $this->options) && $this->options['help']) {
			cli_writeln($this->help);
			die;
		}
		if(count($this->unrecognized)==0){
			//process general help
			$this->process_general_help();
			return true;
		}else if(count($this->unrecognized)>0 && count($this->waitedarguments) == (count($this->unrecognized) -1) ){
			foreach($this->unrecognized as $index=>$argv){
				if($index > 0){
					if(array_key_exists($index, $this->waitedarguments)){
						$this->arguments[$this->waitedarguments[$index]]=$argv;
						//remove it from unrecognized
						unset($this->unrecognized[$index]);
					}
				}
			}
			return true;
		}else{
			cli_error(get_string('badnumberarguments','tool_cmdlinetools',$this->waitedarguments));
		}
	}
	
	protected function get_cmd_options(){
		list($this->options, $this->unrecognized) = cli_get_params( $this->longoptions, $this->shortmapping);
		return true;		
	}
	public static function on_die(){
		$message = ob_get_contents();
		ob_end_clean();
		//TODO
	}
	protected function init(){
		if(get_class($this) == 'cmdlinecli'){
			$this->help = self::get_general_help_string();
		} else {
		$this->help = get_string(get_class($this).'_help', 'tool_cmdlinetools', $this->help_parameters);
		}
		$result = $this->get_cmd_options();
		//need to detect ondie mod fefore first cli_write, cli_writeln or cli_error 
		$this->ondie = (array_key_exists('ondie', $this->options) && $this->options['ondie'])?true:false;
		if($this->ondie){
			register_shutdown_function(array('cmdlinecli','on_die'));
			ob_start();
		}
		if($result){
			$result = $this->get_cmd_arguments();
		}
		if(!$result){
			return false;
		}
		//remove cmd_cli name
		unset($this->unrecognized[0]);
		if ($this->unrecognized) {
			$this->unrecognized = implode("\n  ", $this->unrecognized);
			cli_error(get_string('cliunknowoption', 'admin', $this->unrecognized));
		}
		return true;
	}

	private function process_basic_options(){
		if (array_key_exists('help', $this->options) && $this->options['help']) {
			cli_writeln($this->help);
			die;
		}
		return true;
	}
	
	protected function process_options(){}
	protected function process_output(){
		cli_writeln(get_string('clisuccessfull_'.get_class($this),'tool_cmdlinetools'));
	}
    private static function get_cli_list(){
    	global $CFG;
    	$clilist = array();
    	$fulldir = realpath($CFG->dirroot . DIRECTORY_SEPARATOR . 'admin'. DIRECTORY_SEPARATOR . 'tool'. DIRECTORY_SEPARATOR . 'cmdlinetools'. DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR . 'cmd');
    	$items = new \DirectoryIterator($fulldir);
    	foreach ($items as $item) {
    		if ($item->isDot() || $item->isDir()) {
    			continue;
    		}
	    	$filename = $item->getFilename();
	    	$classname = preg_replace('/\.php$/', '', $filename);
	    
	    	if ($filename === $classname) {
	    		// Not a php file.
	    		continue;
	    	}
	    	$classdescription = preg_replace('/_cli$/', '', $classname);
	    	$clilist[$classname] = $classdescription;
    	}
    	return $clilist;
    }
    private static function get_general_help_string(){
    	$clilist = array();
    	$clilist = self::get_cli_list();
    	return get_string('cmdlinecli_help', 'tool_cmdlinetools', implode(PHP_EOL, $clilist));
    }
	private function process_general_help(){
		global $CFG;
		//retrieve all cmdlinecli class
		$clilist = array();
		$clilist = self::get_cli_list();
		//construct help
		cli_write(get_string('cmdlinecli_help', 'tool_cmdlinetools', implode(PHP_EOL, $clilist)));
	}
	
	public function process(){
		if($this->ondie){
			ob_start();
		}
		$result = $this->process_basic_options();
		if(!$result){
			cli_error(get_string('clifailed_'.get_class($this),'tool_cmdlinetools'));
			die;
		}
		$result = $this->process_options();
		if(!$result){
			cli_error(get_string('clifailed_'.get_class($this),'tool_cmdlinetools'));
			die;
		}
		$this->process_output();
		die;
		if($this->ondie){
			ob_end_clean();
		}
	}

	
	
}