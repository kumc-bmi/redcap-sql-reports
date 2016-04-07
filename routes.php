<?php
// Route actions ot controllers based on request parameters.
function route($REQUEST) {
    switch($REQUEST['action']) {
        case 'list':
            return 'ReportListController';
        case 'view':
            return 'ViewReportController';
        default:
            return 'NotFoundController';
    }
}
?>
