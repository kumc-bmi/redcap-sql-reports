<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');

class ViewReportController extends PluginController {

    protected function handleGET() {
        require_once(FRAMEWORK_ROOT.'ProjectModel.php');

        // 1. Retrieve report configuration information
        $reports = new ProjectModel(
            $this->CONFIG['report_config_pid'],
            $this->CONN
        );
        $report_info = $reports->get_record_by('record', $this->GET['rid']);

        // 2. Verify correct project scope
        if($report_info['project_id'] != $this->GET['pid']) {
            return $this->render('not_found.html', array(
                'PID' => $this->GET['pid']
            ));
        }

        // 3. Run preliminary SQL (if present) 

        // 4. Run report SQL
        $results = $this->execute_query($report_info['report_sql']);

        return $this->render('view_report.html', array(
            'report' => $report_info,
            'results' => $results,
            'PID' => $this->GET['pid']
        ));
    }

    /*
     * TODO: This was hijacked directly from framework/ProjectModel.php and
     * probably doesn't belong here in the long run.
     */
    protected function execute_query($query, $bind_params) {

        $fields = $results = array();
        $stmt = $this->CONN->stmt_init();
        if($stmt->prepare($query)) {
            call_user_func_array(array($stmt,"bind_param"), $bind_params);
            $stmt->execute();
            $meta = $stmt->result_metadata();

            while($field = $meta->fetch_field()) {
                $var = $field->name;
                $$var = null;
                $fields[$var] = &$$var;
            }

            call_user_func_array(array($stmt, 'bind_result'), $fields);
            while($stmt->fetch()) {
                $row = array();
                foreach($fields as $field_name => $var) {
                    $row[$field_name] = $var;
                }
                $results[] = $row;
            }
        }
        $stmt->close();

        return $results;
    }
}
?>
