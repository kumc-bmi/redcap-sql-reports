<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');


class ReportListController extends PluginController {

    protected $AS_OR = 0;
    protected $AS_AND = 1;

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

    private function is_accessable_by($report, $user) {
        $muc = $this->meets_constraint($user, $report, 'user');
        $mrc = $this->meets_constraint($user, $report, 'role');
        $mgc = $this->meets_constraint($user, $report, 'group');

        if(isset($report['handle_as']) and $report['handle_as'] == $this->AS_OR) {
//            echo bool2str($muc).' OR '.bool2str($mrc).' OR '.bool2str($mgc).' == '.bool2str($muc or $mrc or $mgc);
            return ($muc or $mrc or $mgc);
        } else {
//            echo bool2str($muc).' AND '.bool2str($mrc).' AND '.bool2str($mgc).' == '.bool2str($muc and $mrc and $mgc);
            return ($muc and $mrc and $mgc);
        }
    }

    private function meets_constraint($user, $report, $type) {
        if(isset($report[$type.'_access'])) { // User constraint exists
            $users = explode("\n", $report[$type.'_access']);
            $meets_constraint =  in_array($user[$type], $users);
        } elseif(isset($report['handle_as']) and $report['handle_as'] == $this->AS_OR) {
            $meets_constraint = False;
        } else {
            $meets_constraint = True;
        } 
        return $meets_constraint;
    }
}
/*
// DELETE: Used in debugging
function bool2str($bool) {
    if($bool === True) {
        return 'TRUE';
    } elseif($bool === False) {
        return 'FALSE';
    } else {
        return 'N/A';
    }
}*/
?>
