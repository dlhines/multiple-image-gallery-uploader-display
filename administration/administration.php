<?php

class MIGUD_administration {

  public function __construct() {
    // Add Administration Page
    add_action( 'admin_menu' , array ( $this , 'plugin_admin_add_menu'));
    add_action( 'wp_ajax_migud_set_post_types', array( $this, 'MIGUD_administration_set_post_types' ) );
  }

  public function plugin_admin_add_menu() {
    // Administration Page creation
    $hook = add_menu_page(
      'Multiple Image Gallery Uploader Display',
      'Multiple Image Gallery Uploader Display',
      'manage_options',
      'multiple-image-gallery-uploader-display-administration',
      array( $this , 'MIGUD_administration_main'), '');

    add_action( 'load-' . $hook , array( $this, 'MIGUD_administration_assets' ) );
  }

  public function MIGUD_administration_main() {
      require_once ( MIGUD_PLUGIN_FOLDER . '/administration/templates/main.php' );
  }

  public function MIGUD_administration_assets() {
    wp_enqueue_style( 'multiple-image-gallery-uploader-display-administration', MIGUD_PLUGIN_DIR . 'administration/css/multiple-image-gallery-uploader-display-administration.css', array(), '0.0.0', 'all');
    wp_enqueue_script( 'multiple-image-gallery-uploader-display-administration', MIGUD_PLUGIN_DIR . 'administration/js/multiple-image-gallery-uploader-display-administration.js', array('jquery'), null, true);
    wp_localize_script( 'multiple-image-gallery-uploader-display-administration', 'migud_set_post_types',
      array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'ajax_nonce' => wp_create_nonce('MIGUD')
      )
    );
  }

  public function MIGUD_administration_set_post_types() {
    check_ajax_referer( 'MIGUD', 'security' );
    $post_types = $_POST['post_types'];
    $post_types = implode(',', $post_types);

    $update = update_option('MIGUD_post_types', $post_types);

    if($update = 1) {
      if(!empty($_POST['post_types'])) :
        echo "\nYou have successfully updated the Content Types\non which MIGUD will be attached.";
      else :
        echo "You have cleared all Post Types.\n\nYou are no longer using MIGUD.";
      endif;
    } else {
      echo "Error: Updating Content Types not Succesfull. Contact Administrator.\n";
    };

    wp_die();
  }

}

$migud_admin = new MIGUD_administration();

?>
