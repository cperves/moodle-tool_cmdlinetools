<?php
/**
 * apply_defaults_settings_to_plugin
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/adminlib.php');
class apply_defaults_settings_to_all_plugins_cli extends cmdlinecli{
	public function process_options(){
		global $CFG,$DB;
		$admin = get_admin();//need to be admin in order to retrieve all admin tree
		\core\session\manager::init_empty_session();
		
		\core\session\manager::set_user($admin);
		$adminroot = admin_get_root(); // need all settings
		$newsettings = apply_defaults_settings_to_all_plugins_cli::admin_new_settings_by_page($adminroot);
		foreach($newsettings as $plugin => $newsettingnode){
			foreach($newsettingnode as $key => $value){
				set_config($key, $value==null?'':$value,$plugin);
				cli_writeln(get_string('apply_defaults_settings_to_plugin_cli_settingset','tool_cmdlinetools',array('plugin'=>$plugin, 'key'=>$key, 'value'=>$value)));
			}
		}
		return true;
	}
	public static function admin_new_settings_by_page($node) {
	    $return = array();
	    if ($node instanceof admin_category) {
	        $entries = array_keys($node->children);
	        foreach ($entries as $entry) {
	            $return += apply_defaults_settings_to_all_plugins_cli::admin_new_settings_by_page($node->children[$entry]);
	        }
	
	    } else if ($node instanceof admin_settingpage) {
	            $newsettings = array();
	            foreach ($node->settings as $setting) {
                	if (is_null($setting->get_setting())) {
                    	$newsettings[] = $setting;
                	}
            	}
            	if (count($newsettings) > 0) {
            		$adminroot = admin_get_root();
            		$newsettingsobj = array();
            		foreach ($newsettings as $setting){
            			if (is_null($setting->get_setting())) {
 		                	if(!array_key_exists($setting->plugin, $return)){
		                    	$return[$setting->plugin] = array();
		                    }
		                    $return[$setting->plugin][$setting->name]=$setting->get_defaultsetting();
		                }
		                
		            }	            
            	}
	        
	    }
		return $return;
	}
}
