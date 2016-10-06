<?php
/**
 * hide show a plugin
 *
 * @package  
 * @subpackage 
 * @copyright  2016 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @author inspired from moosh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/admin/tool/cmdlinetools/classes/cmdlinecli.php');
require_once($CFG->dirroot.'/user/lib.php');
class create_user_cli extends cmdlinecli{
	public function define_arguments_and_options(){
		$this->waitedarguments = array(1=>'username');
		$this->longoptions= array(
								'auth'       => 'manual',
								'password'              => '',
								'email'         => '',
								'city'              => '',
								'country'		=>'',
								'firstname' => '',
								'lastname' => '',
								'idnumber' => '',
								'maildigest' => 0,
							);
		$this->shortmapping = array(
								'a' => 'auth',
								'p' => 'password',
								'e'=>'email',
								'c'=>'city',
								'C'=>'country',
								'f'=>'firstname',
								'l'=>'lastname',
								'i'=>'idnumber',
								'd'=>'maildigest'
						);
	}
	public function process_options(){
		global $CFG,$DB;
		//don't create if already exists
		$user = $DB->get_record('user', array('username' => $this->arguments['username']));
		if ($user) {
			cli_error(get_string('create_user_cli_userxexists','tool_cmdlinetools',$this->arguments['username']));
		}
		$newuser = (object)$this->options;
		unset($newuser->help);
		$newuser->username = $this->arguments['username'];
		if($this->options['password']=='None'){
			$newuser -> password = '';
		}
		if(is_numeric($this->options['maildigest']) && $this->options['maildigest'] > 0 && $this->options['maildigest'] <= 2){
			$newuser->maildigest = $this->options['maildigest'];
		}else{
			unset($newuser->maildigest);
		}
		$newuser->timecreated = time();
		$newuser->timemodified = $newuser->timecreated;
		$newuser->confirmed = 1;
		$newuser->mnethostid = $CFG->mnet_localhost_id;
		
		if($this->options['auth'] && $this->options['auth'] != "manual" && empty($newuser -> password)){
			$newuserid = $DB->insert_record('user', $newuser);
		}else{
			$newuserid = user_create_user($newuser);
		}
		cli_writeln(get_string('create_user_cli_createuserxid','tool_cmdlinetools',$newuserid));
		return true;
	}
}
