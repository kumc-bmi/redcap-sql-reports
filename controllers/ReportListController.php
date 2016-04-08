<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');

define('AS_OR', 0);
define('AS_AND', 1);

class ReportListController extends PluginController {

    protected function handleGET() {
        require_once(FRAMEWORK_ROOT.'ProjectModel.php');

        $reports = new ProjectModel(
            $this->CONFIG['report_config_pid'],
            $this->CONN
        );

        $report_info = $reports->get_records_by(
            'project_id',
            $this->GET['pid']
        );

        $user = $this->get_user_info($this->USER);
        
        // Limit by access constraints
        $restricted_report_info = array();
        foreach($report_info as $report) {
            if($this->is_accessable_by($report, $user)) {
                $restricted_report_info[] = $report;
            }
        }

        return $this->render('report_list.html', array(
            'reports' => $restricted_report_info,
            'PID' => $this->GET['pid']
        ));
    }

    private function get_user_info($username) {
        $user = array('name'=>$username);
        $user_rights = REDCap::getUserRights($username);
        $user['role'] = $user_rights[$username]['role_name'];
        if ($user_rights[$username]['group_id']) {
            $user['group'] = REDCap::getGroupNames(
                False,
                $user_rights[$username]['group_id']
            );
        } else {
            $user['group'] = False;
        }
        return $user;
    }

    private function is_accessable_by($report, $user) {
        if(isset($report['handle_as']) and $report['handle_as'] == AS_OR) {
            $muc = $this->meets_user_constraint($user, $report);
            $mrc = $this->meets_role_constraint($user, $report);
            $mgc = $this->meets_group_constraint($user, $report);
//            echo bool2str($muc).' OR '.bool2str($mrc).' OR '.bool2str($mgc).' = '.bool2str($muc or $mrc or $mgc);
            return ($muc or $mrc or $mgc);
        } else {
            $muc = $this->meets_user_constraint($user, $report);
            $mrc = $this->meets_role_constraint($user, $report);
            $mgc = $this->meets_group_constraint($user, $report);
//            echo bool2str($muc).' AND '.bool2str($mrc).' AND '.bool2str($mgc).' = '.bool2str($muc and $mrc and $mgc);
            return ($muc and $mrc and $mgc);
        }
    }

    // TODO: DRY up the three method below.
    private function meets_user_constraint($user, $report) {
        if(isset($report['user_access'])) { // User constraint exists
            $users = explode("\n", $report['user_access']);
            $meets_constraint =  in_array($user['name'], $users);
        } elseif(isset($report['handle_as']) and $report['handle_as'] == AS_OR) {
            $meets_constraint = False;

        } else {
            $meets_constraint = True;
        } 
        return $meets_constraint;
    }

    private function meets_role_constraint($user, $report) {
        if(isset($report['role_access'])) { // User constraint exists
            $roles = explode("\n", $report['role_access']);
            $meets_constraint = in_array($user['role'], $roles);
        } elseif(isset($report['handle_as']) and $report['handle_as'] == AS_OR) {
            $meets_constraint = False;
        } else {
            $meets_constraint = True;
        } 
        return $meets_constraint;
    }

    private function meets_group_constraint($user, $report) {
        if(isset($report['dag_access'])) { // User constraint exists
            $groups = explode("\n", $report['dag_access']);
            $meets_constraint = in_array($user['group'], $groups);
        } elseif(isset($report['handle_as']) and $report['handle_as'] == AS_OR) {
            $meets_constraint = False;
        } else {
            $meets_constraint = True;
        } 
        return $meets_constraint;
    }
}

// DELETE: Used in debugging
function bool2str($bool) {
    if($bool === True) {
        return 'TRUE';
    } elseif($bool === False) {
        return 'FALSE';
    } else {
        return 'N/A';
    }
}
?>
