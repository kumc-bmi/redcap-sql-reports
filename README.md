# REDCap SQL Reports Plugin

### INTRODUCTION
This custom REDCap plugin was developed to meet the inter-project reporting
needs of the RE-POWER study, but was designed in a generalizable way, so that it
could easily be used in other cases as well.

The plugin allows for reports to be generated using SQL by defining report in a
SQL Report Configuration REDCap project (defined in `data_dictionary.csv`).
Details on configuring reports are contained in REDCap project.

### PLUGIN STRUCTURE
This plugin used the KUMC REDCap Plugin Framework, which is a lightweight MVC 
framework specific to creating REDCap Plugins.

 * `config.ini`: Contains plugin configuration.  Use `config.ini.example` as a 
   template.

 * `controllers/``: This directory holds controller object files which handle
   plugin specific HTTP requests.  Controller objects should inherit from the
   PluginController object found in ``<framework-root>/PluginController.php`.

 * `index.php`: This file contains the "glue" that connects the rest of the
   plugin code with REDCap code.

 * `routes.php`: The file routes HTTP requests to the appropriate controller.

 * `README.md`: This file.

 * `report_config_dd.csv`: A REDCap data-dictionary defining a project by which to
   configure SQL reports.

 * `templates/``: Holds Twig template files, which contain a combination of HTML
   and template display logic.

### REQUIREMENTS
This plugin requires the following to work correctly:

 * The redcap_connect.php file from the REDCap base install is required and
   needs to be present in the root redcap directory (included in as a part of
   the full REDCap install as of version 5.5.0).  Can also be found at:
   https://iwg.devguard.com/trac/redcap/browser/misc/redcap_connect.zip?format=rawthe

 * MI REDCap Plugin Framework : Contains plugin helper objects and functions
   (including RestCallRequest.php which was obtained via the REDCap API PHP
   sample code).

### INSTALLATION
Installation of this plugin consists of the following steps:

 1. Make sure that the `redcap_connect.php` file described above is present in
    the local REDCap installation's root directory.

 2. Clone the KUMC repower-redcap-plugin into the ``<redcap-root>/plugins`
    directory.  Create the plugins directory if necessary.

 3. Create a new REDCap project using the `report_config_dd.csv` data-dictionary 
    file.  This project will allow user with rights to the project to define
    SQL Reports.


### CONFIGURATION
To configure the plugin:

 1. Copy ``<plugin-root>/config.ini.example` to ``<plugin-root>/config.ini`.

 2. Set the r`eport_config_pid value` to the REDCap project id of the project
    created in step 3 of the installation. 

### MAINTAINERS
Current maintainers:
 * Michael Prittie <mprittie@kumc.edu>
