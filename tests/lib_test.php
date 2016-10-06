<?php

/**
 * cmdlinetools tests.
 *
 * @package    tool_cmdlinetools
 * @category   phpunit
 * @copyright  2016 Unistra {@link http://nistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * cmdlinetools tests.
 *
 * @package    tool_cmdlinetools
 * @category   phpunit
 * @copyright  2016 Unistra {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class tool_cmdlinetools_lib_testcase extends advanced_testcase {
	public function test_cmdlinetools_hideshow_process() {
		global $DB, $CFG;
		$localDataGenerator = $this->getDataGenerator()->get_plugin_generator('tool_cmdlinetools');
		$this->resetAfterTest();
		//show hide a block method
		//test settings since o possibility to remove it
		$record = $DB->get_record('block', array('name'=> 'settings'));
		$this->assertEquals(true, $record!==false);
		$visibility = $record->visible;
		//launch a cli
		$output = array();
		$return_var=0;
		//exec('php define(\'PHPUNIT_TEST\', true);');
		//$_SERVER['argv'] = array($CFG->dirroot.'/admin/tool/cmdlinetools/cli/cmdline_manager.php','hideshow_plugin','block','settings',$visibility == 1?'0':'1');
		//include($CFG->dirroot.'/admin/tool/cmdlinetools/cli/cmdline_manager.php');
		exec($CFG->dirroot.'/admin/tool/cmdlinetools/cli/cmdline_manager.php hideshow_plugin block settings '.($visibility == 1?'0':'1'),$output,$return_var);
		$record = $DB->get_record('block', array('name'=> 'settings'));
		$this->assertEquals(!$visibility, $record->visible);
		exec($CFG->dirroot.'/admin/tool/cmdlinetools/cli/cmdline_manager.php hideshow_plugin block settings '.$visibility == 1?'1':'0');
		$record = $DB->get_record('block', array('name'=> 'settings'));
		$this->assertEquals($visibility, $record->visible);
		
	}
}
