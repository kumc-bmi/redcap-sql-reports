<?php
/**
 * PLUGIN NAME: RE-POWER Plugin
 * DESCRIPTION: Developed by the University of Kansas Medical Center's Medical
 *              Informatics Department for Dr. Christie Befort's RE-POWER study
 *              to extend REDCap functionality to meet study needs.
 * VERSION: 1.0
 * AUTHOR: Michael Prittie
 */

// Retrieve REDCap global MYSQLi database connection object (REDCapism)
global $conn;

// Include the REDCap Connect file in the main "redcap" directory (REDCapism)
require_once('../../redcap_connect.php');

// Set path constants for REDCap and MI REDCap plugin framework
define('REDCAP_ROOT', realpath(dirname(__FILE__).'/../../').'/');
define('FRAMEWORK_ROOT', REDCAP_ROOT.'plugins/framework/');

// Create plugin configuration object
require_once(FRAMEWORK_ROOT.'PluginConfig.php');
$CONFIG = new PluginConfig('config.ini');

// Limit access to plugin using REDCap helper functions
//REDCap::allowProjects($CONFIG['report_config_pid']);
//REDCap::allowUsers(REDCap::getUsers());

// Include and configure Twig template engine (http://twig.sensiolabs.org/)
require_once(
    FRAMEWORK_ROOT.'lib/twig/'.$CONFIG['versions']['twig']
    .'/lib/Twig/Autoloader.php'
);
Twig_Autoloader::register();
$twig_loader = new Twig_Loader_Filesystem(array(
    './templates',
    FRAMEWORK_ROOT.'templates'
));
$twig = new Twig_Environment($twig_loader, array());

// Query router for relavent controller based on plugin $_REQUEST vars
require_once('routes.php');
$ControllerClass = route($_REQUEST);

// Include and instantiate controller class
if(file_exists('controllers/'.$ControllerClass.'.php')) {
    require_once('controllers/'.$ControllerClass.'.php');
} else {
    require_once(FRAMEWORK_ROOT.'controllers/'.$ControllerClass.'.php');
}
$controller = new $ControllerClass(
    $_GET,
    $_POST,
    $twig,
    $conn,
    USERID,
    $CONFIG
);
$page_html = $controller->process_request();

// OPTIONAL: Display the project header (REDCapism)
require_once(APP_PATH_DOCROOT.'ProjectGeneral/header.php');

// Your HTML page content goes here (REDCapism)
echo $page_html;

// OPTIONAL: Display the project footer (REDCapism)
require_once(APP_PATH_DOCROOT.'ProjectGeneral/footer.php');

?>
