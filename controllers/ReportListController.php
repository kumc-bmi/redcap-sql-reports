<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');

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

        return $this->render('report_list.html', array(
            'reports' => $report_info,
            'PID' => $this->GET['pid']
        ));
    }
}
?>
