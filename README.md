# admin/tool/cmdlinetools : Command line tools for moodle technical administration
cmdlinetools is a command line tools integrated in moodle that enable execute moodle administrative task threw php command lines

## Features
Various command line tools are integrated
* assignment capabilities
* allow assign assignment
* default settings application
* Apply default plugin settings
* role creation
* user creation
* delete js cache + hard delete version
* delete plugin
* delete theme cache
* hide or show a plugin
* passwordaltmain generator
* remove capability to take in charge visibility control of feedback or assignment plugins on contexts -> linked to a patch
* context for role definition
* scheduled task coniguration
* set cache configuration
* set capability
* set config 



## Download

from moodle plugin repository

## Installation

### admin plugin installation
Install plugin on admin/tool/cmdlinetools directory, upgrade moodle

## usage
use each commands as php command by calling it as php command line
/moodle27workspace/src/admin/tool/cmdlinetools/cli/cmdline_manager.php command [options]
command :
set_cache
allow_assign
passwordaltmain_generator
create_user
delete_theme_cache
delete_js_cache
set_capability
delete_plugin
role_set_context_level
set_config
hard_delete_js_cache
role_assign
hard_delete_theme_cache
apply_defaults_settings_to_all_plugins
schedule_task
remove_assignment_capa
create_role
hideshow_plugin
add_assignment_capa

--help, -h help
return list of available commands

/moodle27workspace/src/admin/tool/cmdlinetools/cli/cmdline_manager.php command -h
return help for the given command

## Contributions

Contributions of any form are welcome. Github pull requests are preferred.

Fill any bugs, improvements, or feature requiests in our [issue tracker][issues].

## License
* http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
[admin_tool_cmdlinetools_github]: 
[issues]: 
