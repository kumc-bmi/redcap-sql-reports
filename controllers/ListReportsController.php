<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');
require_once('ReportAccessController.php');

define('REPORT_COMPLETE', 2);


/**
 * Retrieve a list of SQL Reports available to this user.
 */
class ListReportsController extends ReportACCessController {

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
        
        $available_reports = array();
        foreach($report_info as $report) {
            if($this->is_accessable_by($report, $user) 
               and $report['report_config_complete'] == REPORT_COMPLETE)
            {
                $available_reports[] = $report;
            }
        }

        return $this->render('report_list.html', array(
            'reports' => $available_reports,
            'PID' => $this->GET['pid']
        ));
    }
}
?>
