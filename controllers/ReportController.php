<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');

/**
 * Provides other SQL Report plugin controllers with shared access control 
 * methods.
 */
class ReportController extends PluginController {

    private $AS_OR = 0;
    private $AS_AND = 1;

    /**
     * Aggregates user information from different sources.
     *
     * @param string $username The username of the user.  Generally retrieved 
     *        using $this->USER, but could be any user.
     *
     * @return @mixed An associative array containing user, role, and DAG 
     *         information.
     */
    protected function get_user_info($username) {
        $user = array('user'=>$username);
        $user_rights = REDCap::getUserRights($username);
        $user['role'] = $user_rights[$username]['role_name'];
        $user['group_id'] = $user_rights[$username]['group_id'];
        if ($user_rights[$username]['group_id']) {
            $user['group'] = REDCap::getGroupNames(
                True,
                $user_rights[$username]['group_id']
            );
        } else {
            $user['group'] = '';
        }
        return $user;
    }

    /**
     * Determines whether the current user has access to the given report.
     *
     * @param mixed[] $report Report data from the SQL Report configuration 
     *        project (i.e. result row returned by ProjectModel::get_record_by).
     * @param mixed[] $user The aggregated user info array returned by 
     *        get_user_info.
     *
     * @return bool True if user meets report access constraints, false if not.
     */
    protected function is_accessable_by($report, $user) {
        $muc = $this->meets_constraint($user, $report, 'user');
        $mrc = $this->meets_constraint($user, $report, 'role');
        $mgc = $this->meets_constraint($user, $report, 'group');

        if(isset($report['handle_as']) 
            and $report['handle_as'] == $this->AS_OR) 
        {
            return ($muc or $mrc or $mgc);
        } else {
            return ($muc and $mrc and $mgc);
        }
    }

    /**
     * Given user and report information, as well as what type of access
     * constraint to check against, determine if the user meets that constraint
     * for the given report.
     *
     * @param mixed[] $report Report data from the SQL Report configuration 
     *        project (i.e. result row returned by ProjectModel::get_record_by).
     * @param mixed[] $user The aggregated user info array returned by 
     *        get_user_info.
     * @param string $type user, role, group 
     */
    protected function meets_constraint($user, $report, $type) {
        if(isset($report[$type.'_access'])) { // User constraint exists
            $users = explode("\n", $report[$type.'_access']);
            $users = array_map(trim, $users);
            $meets_constraint =  in_array($user[$type], $users);
        } elseif(isset($report['handle_as']) 
            and $report['handle_as'] == $this->AS_OR)
        {
            $meets_constraint = False;
        } else {
            $meets_constraint = True;
        } 
        return $meets_constraint;
    }
}
?>
