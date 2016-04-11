<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');
require_once('ReportController.php');


class ReportListController extends ReportController {

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
}
?>
