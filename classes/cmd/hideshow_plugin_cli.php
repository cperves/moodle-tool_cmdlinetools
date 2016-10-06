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
class hideshow_plugin_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'plugintype', 2=>'pluginname', 3=>'hideshow');
	}
	public function process_options(){
		global $CFG,$DB;

		if(!is_numeric($this->arguments['hideshow'])){
			echo "hideshow must be an integer\n";
			die;	
		}
		$this->arguments['hideshow']=(int)$this->arguments['hideshow'];
		
		switch($this->arguments['plugintype']){
			case 'block':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(0,1));
				if (!$block = $DB->get_record('block', array('name'=>$this->arguments['pluginname']))) {
					cli_error(get_string('hideshow_plugin_cli_pluginnotexists','tool_cmdlinetools',array('name'=>$this->arguments['pluginname'], 'type'=> $this->arguments['plugintype'])));
				}
				$DB->set_field('block', 'visible', (int)$this->arguments['hideshow'], array('id'=>$block->id));
				break;
			case 'mod':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(0,1));
				if (!$module = $DB->get_record("modules", array("name"=>$this->arguments['pluginname']))) {
					cli_error(get_string('hideshow_plugin_cli_pluginnotexists','tool_cmdlinetools',array('name'=>$this->arguments['pluginname'], 'type'=> $this->arguments['plugintype'])));
				}
				$DB->set_field("modules", "visible", $this->arguments['hideshow'], array("id"=>$module->id)); // Hide main module
				if($this->arguments['hideshow']==0){
					// Remember the visibility status in visibleold
					// and hide...
					$sql = "UPDATE {course_modules}
			                   SET visibleold=visible, visible=0
			                 WHERE module=?";
					$DB->execute($sql, array($module->id));
					// Increment course.cacherev for courses where we just made something invisible.
			        // This will force cache rebuilding on the next request.
			        increment_revision_number('course', 'cacherev',
			                "id IN (SELECT DISTINCT course
			                                FROM {course_modules}
			                               WHERE visibleold=1 AND module=?)",
			                array($module->id));
			        core_plugin_manager::reset_caches();
				}else{
					$DB->set_field('course_modules', 'visible', '1', array('visibleold'=>1, 'module'=>$module->id)); // Get the previous saved visible state for the course module.
					// Increment course.cacherev for courses where we just made something visible.
			        // This will force cache rebuilding on the next request.
			        increment_revision_number('course', 'cacherev',
			                "id IN (SELECT DISTINCT course
			                                FROM {course_modules}
			                               WHERE visible=1 AND module=?)",
			                array($module->id));
			        core_plugin_manager::reset_caches();
				}
				break;
			case 'assignfeedback':
			case 'assignsubmission':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(0,1));
				//check pluginname
				if(count((array)get_config($this->arguments['plugintype'].'_'.$this->arguments['pluginname']))==0){
					cli_error(get_string('hideshow_plugin_cli_pluginnotexists','tool_cmdlinetools',array('name'=>$this->arguments['pluginname'], 'type'=> $this->arguments['plugintype'])));		
				}
				set_config('disabled', $this->arguments['hideshow']==0?1:0, $this->arguments['plugintype'] . '_' . $this->arguments['pluginname']);
				break;
			case 'qtype':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(0,1));
				//check qtype
				if(!array_key_exists ($this->arguments['pluginname'], core_component::get_plugin_list('qtype'))){
					cli_error(get_string('hideshow_plugin_cli_qtypenotexits','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				set_config($this->arguments['pluginname'] . '_disabled', $this->arguments['hideshow']==0?1:0, 'question');
				break;
			case 'qbehaviour':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(0,1));
				require_once($CFG->dirroot.'/question/engine/lib.php');
				//check qbehaviour
				if(!array_key_exists ($this->arguments['pluginname'], core_component::get_plugin_list('qbehaviour'))){
					cli_error(get_string('hideshow_plugin_cli_qbehaviournotexits','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				if(!question_engine::is_behaviour_archetypal($this->arguments['pluginname'])){
					cli_error(get_string('hideshow_plugin_cli_qbehaviourcantenabledisable','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				$config = get_config('question');
				if (!empty($config->disabledbehaviours)) {
					$disabledbehaviours = explode(',', $config->disabledbehaviours);
				} else {
					$disabledbehaviours = array();
				}
				$disabledbehaviours_index = array_search($this->arguments['pluginname'],$disabledbehaviours);
				if($disabledbehaviours_index!==false && $this->arguments['hideshow']==1){
					unset($disabledbehaviours[$disabledbehaviours_index]);
					set_config('disabledbehaviours', implode(',', $disabledbehaviours), 'question');
				}else if ($disabledbehaviours_index === false && $this->arguments['hideshow'] ==0){
						$disabledbehaviours[] = $this->arguments['pluginname'];
						set_config('disabledbehaviours', implode(',', $disabledbehaviours), 'question');
				}
				break;
			case 'enrol':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(0,1));
				$syscontext = context_system::instance();
				$enabled = enrol_get_plugins(true);
				$all     = enrol_get_plugins(false);
				if(!array_key_exists($this->arguments['pluginname'],$all)){
					cli_error(get_string('hideshow_plugin_cli_enrolmethodnotexists','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				if($this->arguments['hideshow']==0 && array_key_exists($this->arguments['pluginname'],$enabled)){
					unset($enabled[$this->arguments['pluginname']]);
					set_config('enrol_plugins_enabled', implode(',', array_keys($enabled)));
					$syscontext->mark_dirty(); // resets all enrol caches
				}else if($this->arguments['hideshow'] == 1  && !array_key_exists($this->arguments['pluginname'], $enabled)){
					$enabled = array_keys($enabled);
					$enabled[] = $this->arguments['pluginname'];
					set_config('enrol_plugins_enabled', implode(',', $enabled));
					$syscontext->mark_dirty(); // resets all enrol caches
				}
				break;
			case 'filter':
				require_once($CFG->libdir.'/filterlib.php');
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(1,-1,-9999));
				//check if filter exists
				if(!array_key_exists($this->arguments['pluginname'],filter_get_all_installed())){
					cli_error(get_string('hideshow_plugin_cli_filterpluginnotexists','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				filter_set_global_state('filter/'.$this->arguments['pluginname'], $this->arguments['hideshow']);
				if ($this->arguments['hideshow'] == TEXTFILTER_DISABLED) {
					filter_set_applies_to_strings("filter/$this->arguments['pluginname']", false);
				}
				reset_text_filters_cache();
				break;
			case 'editor':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'],array(0,1));
				$editors=editors_get_available();
				if(!array_key_exists($this->arguments['pluginname'],$editors)){
					cli_error(get_string('hideshow_plugin_cli_editorpluginnotexists','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				$active_editors = explode(',', $CFG->texteditors);
				$active_editors_index = array_search($this->arguments['pluginname'],$active_editors);
				if($this->arguments['hideshow']==0 && $active_editors_index !== false){
					unset($active_editors[$active_editors_index]);
					set_config('texteditors', implode(',', $active_editors));
					
				}else if($this->arguments['hideshow'] == 1  && $active_editors_index === false){
					$active_editors[]=$this->arguments['pluginname'];
					set_config('texteditors', implode(',', $active_editors));
				}
				break;
			case 'auth':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'], array(0,1));
				if(!exists_auth_plugin($this->arguments['pluginname'])){
					cli_error(get_string('hideshow_plugin_cli_authpluginnotexists','tool_cmdlinetools',array('type'=>$this->arguments['plugintype'],'name' => $this->arguments['pluginname'])));
				}
				get_enabled_auth_plugins(true); // fix the list of enabled auths
				if (empty($CFG->auth)) {
					$authsenabled = array();
				} else {
					$authsenabled = explode(',', $CFG->auth);
				}
				if($this->arguments['hideshow']==1){
					if (!in_array($this->arguments['pluginname'], $authsenabled)) {
						$authsenabled[] = $this->arguments['pluginname'];
						$authsenabled = array_unique($authsenabled);
						set_config('auth', implode(',', $authsenabled));
					}
					\core\session\manager::gc(); // Remove stale sessions.
					core_plugin_manager::reset_caches();
				}else{
					$key = array_search($this->arguments['pluginname'], $authsenabled);
					if ($key !== false) {
						unset($authsenabled[$key]);
						set_config('auth', implode(',', $authsenabled));
					}
					
					if ($this->arguments['pluginname'] == $CFG->registerauth) {
						set_config('registerauth', '');
					}
					\core\session\manager::gc(); // Remove stale sessions.
					core_plugin_manager::reset_caches();
				}
				break;
			case 'license':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'], array(0,1));
				require_once($CFG->libdir.'/licenselib.php');
				if(license_manager::get_license_by_shortname($this->arguments['pluginname'])==null){
					cli_error(get_string('hideshow_plugin_cli_licensepluginnotexists','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				if($this->arguments['pluginname'] == $CFG->sitedefaultlicense){
					cli_error(get_string('hideshow_plugin_cli_licensecantenabledisable','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				if($this->arguments['hideshow']==1){
					license_manager::enable($this->arguments['pluginname']);
				}else{
					license_manager::disable($this->arguments['pluginname']);
				}
				break;
			case 'repository':
				cli_error(get_string('hideshow_plugin_cli_repositorynotimplemented','tool_cmdlinetools'));
				break;
			case 'courseformat':
				hideshow_plugin_cli::hideshow_values_check($this->arguments['hideshow'], array(0,1));
				require_once($CFG->libdir.'/classes/plugin_manager.php');
				$formatplugins = core_plugin_manager::instance()->get_plugins_of_type('format');
				if (!isset($formatplugins[$this->arguments['pluginname']])) {
					cli_error(get_string('hideshow_plugin_cli_courseformatnotexists','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				if (get_config('moodlecourse', 'format') === $this->arguments['pluginname']){
					cli_error(get_string('hideshow_plugin_cli_courseformatcantenabledisable','tool_cmdlinetools',$this->arguments['pluginname']));
				}
				if($this->arguments['hideshow'] == 0){
					set_config('disabled', 1, 'format_'. $this->arguments['pluginname']);
					core_plugin_manager::reset_caches();
				}else{
					unset_config('disabled', 'format_'. $this->arguments['pluginname']);
					core_plugin_manager::reset_caches();
				}
				break;
			default:
				cli_error(get_string('hideshow_plugin_cli_badplugintype','tool_cmdlinetools'));
		}
		
		return true;
	}
	private static function hideshow_values_check($hideshow,$possiblevalues){
		if(!in_array($hideshow, $possiblevalues)){
			cli_error(get_string('hideshow_plugin_cli_parametervalues','tool_cmdlinetools',implode(',', $possiblevalues)));
		}
	}
}
