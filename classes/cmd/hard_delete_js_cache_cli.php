<?php
/**
 * hardly delete js cache
 *
 * @package  
 * @subpackage 
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/filelib.php');
class hard_delete_js_cache_cli extends cmdlinecli{

	public function process_options(){
		global $CFG;
		fulldelete("$CFG->localcachedir/requirejs");
		fulldelete("$CFG->localcachedir/js");
		return true;
	}
}
