<?php
require_once(FRAMEWORK_ROOT.'PluginController.php');
require_once('ReportController.php');


class ViewReportController extends ReportController {

    protected function handleGET() {
        require_once(FRAMEWORK_ROOT.'ProjectModel.php');

        // 1. Retrieve report configuration information
        $reports = new ProjectModel(
            $this->CONFIG['report_config_pid'],
            $this->CONN
        );
        $report_info = $reports->get_record_by('record', $this->GET['rid']);

        // 2. Verify valid project scope
        if($report_info['project_id'] != $this->GET['pid']) {
            return $this->render('not_found.html', array(
                'PID' => $this->GET['pid']
            ));
        }

        // 3. Verify that user meet access constraints
        $user_info = $this->get_user_info($this->USER);
        if(!$this->is_accessable_by($report_info, $user_info)) {
            return $this->render(
                'not_found.html',
                array('PID' => $this->GET['pid'])
            );
        }

        // 4a. Preliminary SQL exists
        if(isset($report_info['preliminary_sql'])
            and $report_info['preliminary_sql'])
        {
            $prelim_results = $this->execute_query(
                $report_info['preliminary_sql']
            );
            $results = array();
            foreach($prelim_results as $prelim_result) {
                $prelim_result['__GROUPID__'] = $user_info['group_id'];
                $formatted_sql = $this->replace_labels_with_values(
                    $report_info['report_sql'],
                    $prelim_result
                );
                $results[$prelim_result['table_title']] = $this->execute_query(
                    $formatted_sql
                );
            }
            return $this->render('view_sub_reports.html', array(
                'report' => $report_info,
                'results' => $results,
                'PID' => $this->GET['pid']
            ));
        // 4b. Only Report SQL
        } else {
            $formatted_sql = $this->replace_labels_with_values(
                $report_info['report_sql'],
                array('__GROUPID__'=>$user_info['group_id'])
            );
            $results = $this->execute_query($formatted_sql);

            return $this->render('view_report.html', array(
                'report' => $report_info,
                'results' => $results,
                'PID' => $this->GET['pid']
            ));
        }
    }

    private function replace_labels_with_values($text, $fields) {
        $pattern = '\[[0-9a-zA-Z_]*]\[[0-9a-zA-Z_]*]|\[[0-9a-zA-Z_]*]';
        preg_match_all('/'.$pattern.'/U', $text, $matches);
        $matches = array_unique($matches);
        foreach($matches[0] as $match) {
            $text = str_replace(
                $match,
                $fields[substr($match,1,-1)],
                $text
            );
        }
        return $text;
    }

    /*
     * TODO: This was hijacked directly from framework/ProjectModel.php and
     * probably doesn't belong here in the long run, but it's a private method
     * in ProjectModel and probably should remain that way.
     */
    private function execute_query($query, $bind_params) {

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
