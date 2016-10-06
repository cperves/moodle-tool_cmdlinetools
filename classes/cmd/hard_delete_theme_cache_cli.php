<?php
/**
 * hardly delete theme cache
 *
 * @package  
 * @subpackage 
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */



require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->libdir.'/filelib.php');
class hard_delete_theme_cache_cli extends cmdlinecli{
	public function process_options(){
		global $CFG;
		fulldelete("$CFG->localcachedir/theme");
		return true;
	}
}



