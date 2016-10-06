<?php
/**
 * schedule_task admin cli
 *
 * @package  
 * @subpackage 
 * @copyright  2015 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');

class schedule_task_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->longoptions= array(
								'minute'			=> '*',
								'hour'			=> '*',
								'day'			=> '*',
								'month'			=> '*',
								'dayofweek'		=> '*',
								'disabled'      => false,
								'resettodefaults' => false,
								'list' => null
							);
		$this->shortmapping = array(
								'M' => 'minute',
								'H'=> 'hour',
								'd' => 'day',
								'm' => 'month',
								'w' => 'dayofweek',
								'x' => 'disabled',
								'r' => 'resettodefaults',
								'l'=> 'list'
								
								
						);
		$this->waitedarguments = array(
								1 => 'taskname'
							);
		
	}		 
	function process_options(){
		if(isset($this->options['list'])){
			$tasks = core\task\manager::get_all_scheduled_tasks();
			$tasknames = array();
			foreach($tasks as $currenttask){
				$tasknames[] = get_class($currenttask);
			}
			cli_writeln(get_string('schedule_task_cli_tasklist', 'tool_cmdlinetools', implode(PHP_EOL, $tasknames)));
			die;
		}
		$task = \core\task\manager::get_scheduled_task($this->arguments['taskname']);
		if (!$task) {
			cli_error(get_string('schedule_task_cli_badtaskname','tool_cmdlinetools',$this->arguments['taskname']));
		}
		if ($this->options['resettodefaults']) {
			$defaulttask = \core\task\manager::get_default_scheduled_task($this->arguments['taskname']);
			$task->set_minute($defaulttask->get_minute());
			$task->set_hour($defaulttask->get_hour());
			$task->set_month($defaulttask->get_month());
			$task->set_day_of_week($defaulttask->get_day_of_week());
			$task->set_day($defaulttask->get_day());
			$task->set_disabled($defaulttask->get_disabled());
			$task->set_customised(false);
		} else {
			$task->set_minute($this->options['minute']);
			$task->set_hour($this->options['hour']);
			$task->set_month($this->options['month']);
			$task->set_day_of_week($this->options['dayofweek']);
			$task->set_day($this->options['day']);
			$task->set_disabled($this->options['disabled']);
			$task->set_customised(true);
		}
		try {
			\core\task\manager::configure_scheduled_task($task);
		} catch (Exception $e) {
			return false;
		}
		return true;
		
	}
	
	
}