<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');
//require_once('/srv/www/htdocs-insecure/redcap/plugins/framework/PluginController.php');

class ReportController extends PluginController {

    private $AS_OR = 0;
    private $AS_AND = 1;

    protected function get_user_info($username) {
        $user = array('user'=>$username);
        $user_rights = REDCap::getUserRights($username);
        $user['role'] = $user_rights[$username]['role_name'];
        if ($user_rights[$username]['group_id']) {
            $user['group'] = REDCap::getGroupNames(
                False,
                $user_rights[$username]['group_id']
            );
        } else {
            $user['group'] = '';
        }
        return $user;
    }

    protected function is_accessable_by($report, $user) {
        $muc = $this->meets_constraint($user, $report, 'user');
        $mrc = $this->meets_constraint($user, $report, 'role');
        $mgc = $this->meets_constraint($user, $report, 'group');

        if(isset($report['handle_as']) and $report['handle_as'] == $this->AS_OR) {
            return ($muc or $mrc or $mgc);
        } else {
            return ($muc and $mrc and $mgc);
        }
    }

    protected function meets_constraint($user, $report, $type) {
        if(isset($report[$type.'_access'])) { // User constraint exists
            $users = explode("\n", $report[$type.'_access']);
            $users = array_map(trim, $users);
            $meets_constraint =  in_array($user[$type], $users);
        } elseif(isset($report['handle_as']) and $report['handle_as'] == $this->AS_OR) {
            $meets_constraint = False;
        } else {
            $meets_constraint = True;
        } 
        return $meets_constraint;
    }
}
?>
