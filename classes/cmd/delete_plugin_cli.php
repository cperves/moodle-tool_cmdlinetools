<?php
/**
 * delete a plugin
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/accesslib.php');
class delete_plugin_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'plugintype', 2=>'pluginname');
	}
	function process_options(){
		global $CFG,$DB;
		switch($this->arguments['plugintype']){
			case 'block':
				require_once($CFG->libdir . '/adminlib.php');
				if (!$block = $DB->get_record('block', array('name'=>$this->arguments['pluginname']))) {
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				uninstall_plugin('block', $this->arguments['pluginname']);
				break;
			case 'mod':
				require_once($CFG->libdir . '/adminlib.php');
				if (!$module = $DB->get_record("modules", array("name"=>$this->arguments['pluginname']))) {
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				uninstall_plugin('mod', $this->arguments['pluginname']);
				break;
			case 'assignfeedback':
			case 'assignsubmission':
				require_once($CFG->dirroot.'/mod/assign/adminlib.php');
				$shortsubtype = substr($this->arguments['plugintype'], strlen('assign'));
				//check pluginname
				if(count((array)get_config($this->arguments['plugintype'].'_'.$this->arguments['pluginname']))==0){
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				// Delete any configuration records.
				if (!unset_all_config_for_plugin($this->arguments['plugintype']. '_' . $this->arguments['pluginname'])) {
					cli_error(get_string('errordeletingconfig', 'admin', $this->arguments['plugintype'] . '_' . $this->arguments['pluginname']));
				}
				// Should be covered by the previous function - but just in case
				unset_config('disabled', $this->arguments['plugintype'] . '_' . $this->arguments['pluginname']);
				unset_config('sortorder', $this->arguments['plugintype'] . '_' . $this->arguments['pluginname']);
		
				// delete the plugin specific config settings
				$DB->delete_records('assign_plugin_config', array('plugin'=>$this->arguments['pluginname'], 'subtype'=>$this->arguments['plugintype']));
		
				// Then the tables themselves
				drop_plugin_tables($this->arguments['plugintype'] . '_' . $this->arguments['pluginname'], $CFG->dirroot . '/mod/assign/' . $shortsubtype . '/' .$this->arguments['pluginname']. '/db/install.xml', false);
		
				// Remove event handlers and dequeue pending events
				events_uninstall($this->arguments['plugintype'] . '_' . $this->arguments['pluginname']);
				break;
			case 'qtype':
				require_once($CFG->libdir . '/questionlib.php');
				require_once($CFG->libdir . '/adminlib.php');
				require_once($CFG->libdir . '/pluginlib.php');
				require_once($CFG->libdir . '/tablelib.php');
				//check qtype
				if(!array_key_exists ($this->arguments['pluginname'], get_plugin_list('qtype'))){
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				$needed = array();
				//delete
				// Delete any configuration records.
				$qtypes = question_bank::get_all_qtypes();
				$counts = $DB->get_records_sql("
        SELECT qtype, COUNT(1) as numquestions, SUM(hidden) as numhidden
        FROM {question} GROUP BY qtype", array());
				$pluginmanager = plugin_manager::instance();
				foreach ($qtypes as $qtypename => $qtype) {
					if (!isset($counts[$qtypename])) {
						$counts[$qtypename] = new stdClass;
						$counts[$qtypename]->numquestions = 0;
						$counts[$qtypename]->numhidden = 0;
					}
					$needed[$qtypename] = $counts[$qtypename]->numquestions > 0 ||
					$pluginmanager->other_plugins_that_require($qtype->plugin_name());
					$counts[$qtypename]->numquestions -= $counts[$qtypename]->numhidden;
				}
				//check if removable
				if(!array_key_exists($this->arguments['pluginname'], $needed)){
					cli_error(get_string('delete_plugin_cli_undeletableplugintype','tool_cmdlinetools',array('name'=>$this->arguments['pluginname'],'type'=>$this->arguments['plugintype'])));
					die;
				}
				if (!unset_all_config_for_plugin('qtype_' . $this->arguments['pluginname'])) {
					cli_error($OUTPUT->notification(get_string('errordeletingconfig', 'admin', 'qtype_' . $this->arguments['pluginname'])));
				}
				unset_config($this->arguments['pluginname'] . '_disabled', 'question');
				unset_config($this->arguments['pluginname'] . '_sortorder', 'question');
		
				// Then the tables themselves
				drop_plugin_tables($this->arguments['pluginname'], $qtypes[$this->arguments['pluginname']]->plugin_dir() . '/db/install.xml', false);
		
				// Remove event handlers and dequeue pending events
				events_uninstall('qtype_' . $this->arguments['pluginname']);
				break;
			case 'qbehaviour':
				require_once($CFG->libdir . '/questionlib.php');
				require_once($CFG->libdir . '/adminlib.php');
				require_once($CFG->libdir . '/pluginlib.php');
				require_once($CFG->libdir . '/tablelib.php');
				require_once($CFG->dirroot.'/question/engine/lib.php');
				//check qbehaviour
				if(!array_key_exists ($this->arguments['pluginname'], get_plugin_list('qbehaviour'))){
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				if(question_engine::is_behaviour_archetypal($this->arguments['pluginname'])){
					cli_error(get_string('delete_plugin_cli_undeletablerequiredplugintype','tool_cmdlinetools',array('name'=>$this->arguments['pluginname'],'type'=>$this->arguments['plugintype'])));
				}
				// Work of the correct sort order.
				$behaviours = get_plugin_list('qbehaviour');
				$config = get_config('question');
				$sortedbehaviours = array();
				//needed checks
				$pluginmanager = plugin_manager::instance();
				$needed = array();
				$archetypal = array();
				$counts = $DB->get_records_sql_menu("
        SELECT behaviour, COUNT(1)
        FROM {question_attempts} GROUP BY behaviour");
				foreach ($behaviours as $behaviour => $notused) {
					if (!array_key_exists($behaviour, $counts)) {
						$counts[$behaviour] = 0;
					}
					$needed[$behaviour] = ($counts[$behaviour] > 0) ||
					$pluginmanager->other_plugins_that_require('qbehaviour_' . $behaviour);
					$archetypal[$behaviour] = question_engine::is_behaviour_archetypal($behaviour);
				}
				foreach ($counts as $behaviour => $count) {
					if (!array_key_exists($behaviour, $behaviours)) {
						$counts['missing'] += $count;
					}
				}
				$needed['missing'] = true;
				if(!array_key_exists($this->arguments['pluginname'], $needed)){
					cli_error(get_string('delete_plugin_cli_undeletablerequiredplugintype','tool_cmdlinetools',array('name'=>$this->arguments['pluginname'],'type'=>$this->arguments['plugintype'])));
				}
				foreach ($behaviours as $behaviour => $notused) {
					$sortedbehaviours[$behaviour] = question_engine::get_behaviour_name($behaviour);
				}
				if (!empty($config->behavioursortorder)) {
					$sortedbehaviours = question_engine::sort_behaviours($sortedbehaviours,
							$config->behavioursortorder, '');
				}
		
				if (!empty($config->disabledbehaviours)) {
					$disabledbehaviours = explode(',', $config->disabledbehaviours);
				} else {
					$disabledbehaviours = array();
				}
		
				// Delete any configuration records.
				if (!unset_all_config_for_plugin('qbehaviour_' . $this->arguments['pluginname'])) {
					cli_error(get_string('errordeletingconfig', 'admin', 'qbehaviour_' . $this->arguments['pluginname']));
				}
				if (($key = array_search($this->arguments['pluginname'], $disabledbehaviours)) !== false) {
					unset($disabledbehaviours[$key]);
					set_config('disabledbehaviours', implode(',', $disabledbehaviours), 'question');
				}
				$behaviourorder = array_keys($sortedbehaviours);
				if (($key = array_search($this->arguments['pluginname'], $behaviourorder)) !== false) {
					unset($behaviourorder[$key]);
					set_config('behavioursortorder', implode(',', $behaviourorder), 'question');
				}
		
				// Then the tables themselves
				drop_plugin_tables($this->arguments['pluginname'], get_plugin_directory('qbehaviour', $this->arguments['pluginname']) . '/db/install.xml', false);
		
				// Remove event handlers and dequeue pending events
				events_uninstall('qbehaviour_' . $this->arguments['pluginname']);
				break;
			case 'enrol':
				require_once($CFG->libdir . '/adminlib.php');
				if (!get_string_manager()->string_exists('pluginname', 'enrol_'.$this->arguments['pluginname'])) {
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				set_time_limit(0);
				// Disable plugin to prevent concurrent cron execution.
				$enabled = enrol_get_plugins(true);
				unset($enabled[$this->arguments['pluginname']]);
				set_config('en$textrol_plugins_enabled', implode(',', array_keys($enabled)));
		
				uninstall_plugin('enrol', $this->arguments['pluginname']);
				$systemcontext = context_system::instance();
				$systemcontext->mark_dirty();
				break;
			case 'filter':
				require_once($CFG->libdir.'/filterlib.php');
				//check if filter exists
				if(!array_key_exists("filter/$this->arguments['pluginname']",filter_get_all_installed())){
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				filter_delete_all_for_filter("filter/$this->arguments['pluginname']");
				break;
			case 'editor':
				$editors=editors_get_available();
				if(!array_key_exists($this->arguments['pluginname'],$editors)){
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
					die;
				}
				$active_editors = explode(',', $CFG->texteditors);
				$active_editors_index = array_search($this->arguments['pluginname'],$active_editors);
				$key = array_search($this->arguments['pluginname'], $active_editors);
				unset($active_editors[$key]);
				set_config('texteditors', implode(',', $active_editors));
		
				// Delete everything!!
				uninstall_plugin('editor', $this->arguments['pluginname']);
				break;
			case 'auth':
				cli_error(get_string('delete_plugin_cli_cantremovexplugin','toolcmdlinetools',$this->arguments['plugintype']));
				break;
			case 'license':
				cli_error(get_string('delete_plugin_cli_cantremovexplugin','toolcmdlinetools',$this->arguments['plugintype']));
				break;
			case 'repository':
				require_once($CFG->libdir.'/adminlib.php');
				$pluginman = core_plugin_manager::instance();
				$pluginfo = $pluginman->get_plugin_info($this->arguments['plugintype'].'_'.$this->arguments['pluginname']);
				$pluginname = $pluginman->plugin_name($pluginfo->component);
				if (is_null($pluginfo)) {
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				if (!$pluginman->can_uninstall_plugin($pluginfo->component)) {
					cli_error(get_string('delete_plugin_cli_undeletableplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				$progress = new progress_trace_buffer(new text_progress_trace(), false);
				$pluginman->uninstall_plugin($pluginfo->component, $progress);
				$progress->finished();
				if (function_exists('opcache_reset')) {
					opcache_reset();
				}
				die;
				break;
			case 'courseformat':
				cli_error(get_string('delete_plugin_cli_notyetimplemented','tool_cmdlinetools'));
				break;
			case 'local':
				require_once($CFG->libdir . '/adminlib.php');
				$localplugins = get_plugin_list('local');
				if (!array_key_exists($this->arguments['pluginname'], $localplugins)) {
					cli_error(get_string('delete_plugin_cli_unreconizedplugintype','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'], 'name'=>$this->arguments['pluginname'])));
				}
				uninstall_plugin('local', $this->arguments['pluginname']);
				break;
			case 'tool' :
				require_once($CFG->libdir . '/adminlib.php');
				uninstall_plugin('tool', $this->arguments['pluginname']);
				break;
			default:
				cli_error('delete_plugin_cli_badtype','tool_cmdlinetools');
		}
		return true;
	}
}
