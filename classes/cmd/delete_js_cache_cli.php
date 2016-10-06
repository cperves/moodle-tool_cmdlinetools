<?php
/**
 * delete js cache
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/outputrequirementslib.php');
class delete_js_cache_cli extends cmdlinecli{
	public function process_options(){
		js_reset_all_caches();
		return true;
	}
}