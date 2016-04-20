<?php
// Route actions to controllers based on request parameters.
function route($REQUEST) {
    switch($REQUEST['action']) {
        case 'list':
            return 'ListReportsController';
        case 'view':
            return 'ViewReportController';
        default:
            return 'NotFoundController';
    }
}
?>
