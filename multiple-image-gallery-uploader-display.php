<?php
/**
* Plugin Name: Multiple Image Gallery Uploader Display (MIGUD)
* Description: Image uploader for image galleries (per post type). Allows update free uploading, sortiing, and deleting in real time. Uses JQuery UI utilities<.
* Author: Daniel Hines (dlhines.net)
**/

define('MIGUD_PLUGIN_FOLDER', dirname(__FILE__) );
define('MIGUD_PLUGIN_BASE_FILENAME', plugin_basename(__FILE__));

// Grab plugin directory name from the plugin main file name
define('MIGUD_PLUGIN_DIR', plugins_url() . "/" . str_replace(".php","", substr(MIGUD_PLUGIN_BASE_FILENAME, strpos(MIGUD_PLUGIN_BASE_FILENAME, "/") + 1)) . "/");

class MIGUD_initiate {

    public function __construct() {

      // Include Administration Page
      require ( MIGUD_PLUGIN_FOLDER . '/administration/administration.php' );

      // Include IUD post_type_display and render frontend
      require ( MIGUD_PLUGIN_FOLDER . '/migud/migud.php' );

    }

}

$initiate = new MIGUD_initiate();
?>
