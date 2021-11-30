<?php

class MIGUD {

  public function __construct(){
    //
    add_action( 'add_meta_boxes' , array ( $this, 'MIGUD_meta_box'));
    add_action( 'admin_enqueue_scripts' , array ( $this, 'MIGUD_scripts_styles') );
    add_action( 'wp_ajax_migud_save_images' , array( $this, 'MIGUD_save_images' ) );
    add_action( 'wp_ajax_migud_delete_images' , array( $this, 'MIGUD_delete_images' ) );
    add_action( 'wp_ajax_migud_sort_images' , array( $this, 'MIGUD_sort_images' ) );

    // Shortcode, styles, and scripts for Gallery front-end display
    add_action( 'wp_enqueue_scripts' , array ( $this, 'MIGUD_scripts_styles_render'), 100 );
    add_shortcode( 'migud_display' , array( $this, 'MIGUD_shortcode') );
    //
  }

  public function MIGUD_scripts_styles() {
    // WP Media
    wp_enqueue_media();

    // MIGUD Backend Styles and Scripts / JQuery UI
	  wp_enqueue_style( 'jquery-ui', MIGUD_PLUGIN_DIR . 'migud/css/jquery-ui.min.css', array(), '0.0.0', 'all' );
    wp_enqueue_script( 'jquery-ui', MIGUD_PLUGIN_DIR . 'migud/js/jquery-ui.min.js', array('jquery'), null, true );
    wp_enqueue_script( 'MIGUD', MIGUD_PLUGIN_DIR .  'migud/js/multiple-image-gallery-uploader-display.js', array('jquery'), null, true );
    wp_enqueue_style( 'multiple-image-gallery-uploader-display', MIGUD_PLUGIN_DIR .  'migud/css/multiple-image-gallery-uploader-display.css', array(), '0.0.0', 'all' );

    // Localization for ajax
    wp_localize_script( 'MIGUD', 'migud_save_images', array( 'ajax_url' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('MIGUD')) );
    wp_localize_script( 'MIGUD', 'migud_delete_images', array( 'ajax_url' => admin_url('admin-ajax.php')) );
    wp_localize_script( 'MIGUD', 'migud_sort_images', array( 'ajax_url' => admin_url('admin-ajax.php')) );
    //
  }

  public function MIGUD_scripts_styles_render() {
    //
    wp_enqueue_style('multiple-image-gallery-uploader-display-render', MIGUD_PLUGIN_DIR .  'css/multiple-image-gallery-uploader-display-render.css', array(), '0.0.0', 'all');
    wp_enqueue_style('fancybox_css', 'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css', array(), '0.0.0', 'all');
    wp_enqueue_script('fancybox_js', 'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js', array(), null, true);
    //
  }

  /*
  * Display Metabox
  *
  */
  public function MIGUD_meta_box() {

    $pt_s = get_option('MIGUD_post_types');

    if(!empty($pt_s)) {
      $display = explode(',', get_option('MIGUD_post_types'));

      add_meta_box(
        'multiple-image-gallery-uploader-display', // id
        'Multiple Image Gallery Uploader Display (MIGUD)', // title
        array ( $this, 'MIGUD_cb'), // callback
        $display, // content-type
        'normal', // display
        'default' // priority
      );
    }

  }

  /*
  * Metabox Callback
  *
  */
  public function MIGUD_cb( $post ){

    // Call to admin_display_images to grab all thumbnails
    $images = $this->admin_display_images($post->ID);

    wp_nonce_field('MIGUD_meta_box_nonce', 'MIGUD_meta_box_nonce');
    ?>
    <div id="image-uploader-display">
      <div id="miud_instructions">
        <p class="header">MIGUD Instructions ( Show )</p>
        <section class="clearfix">
          <?php require "miud-instructions.php"; ?>
        </section>
      </div>
      <p>Copy/Paste into Content Window: <b style="font-size: 1.2rem;">[migud_display id="<?php echo $post->ID; ?>" title=""]</b></p>
      <div id="gallery_load"><?php if($images) : echo $images; endif; ?></div>
      <input type="button" id="select_images" class="" value="Select Images" data-id="<?php echo $post->ID ?>">
      <input type="button" id="delete_images" class="" value="Delete Images" data-id="<?php echo $post->ID ?>"/>
    </div>
    <?php
    //

  }

  /*
  * Display Images
  *
  * Internal function to grab image file names from database.
  * Used in the function 'MIGUD_cb( $post )' above
  */

  private function admin_display_images($postID) {

    $datacall = get_post_meta($postID, 'migud_post_image_set');

    $_list_images = '<ul id="image_sortable">'; // Start list
    $produce_list_items = '';
    // Check to see if the value returned in $datacall_to_explode is an empty array
    // if not cycle through the array and assign id and source to images.
    if (!empty($datacall[0])) :
      $images = explode(',', $datacall[0]);
      foreach ($images as $image) :
        //Cycle through ids to create thumbnail images
        $produce_list_items .= '<li class="ui-state-default"><img id="' . $image . '" src="' . wp_get_attachment_image_src($image)[0] . '" /></li>';
      endforeach;
    endif;
    if ($produce_list_items) {
      $_list_images .= $produce_list_items;
      $_list_images .= "</ul>";
    } else {
      $_list_images = "";
    }

    return $_list_images;
  }

  /*
  * Image Save Function
  *
  */
  public function MIGUD_save_images() {

    //WP Ajax nonce check
    check_ajax_referer( 'MIGUD', 'security' );

    $image_ids = $_POST['image_ids']; // Grab incoming ids
    $postID = $_POST['postID']; // Grab Post ID
    $migud_post_image_set = get_post_meta($postID, 'migud_post_image_set'); // Grab existing ids in 'migud_post_image_set' postmeta key
    $return_data_array = [];  // Return array shell

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $postID ) ) {
            return;
        }
    }
    else {
        if ( ! current_user_can( 'edit_post', $postID ) ) {
            return;
        }
    }

    if(count($image_ids) > 0) : // Truly, just a safe guard to stop empty img tags
      if(count($migud_post_image_set) > 0) : // If image ids exist in database prepare to prepend database information with new ids
        $join = array_merge($image_ids, $migud_post_image_set);  // Prepend incoming image ids to the front of the database ids
        $save_data = implode(",", $join); // Convert array to comma separated string value for data storage
      else :  // If database is empty
        $save_data = implode(",", $image_ids);  // Convert array to comma separated string value for data storage
      endif;
      $return_data_array["updated"] = update_post_meta( $postID, 'migud_post_image_set', trim($save_data, ",") );  // Update database and return success or failure. Trim removes the trailing comma from the string
      if($return_data_array["updated"] !== false) : // Check for data saved if true grab all images...
        $return_data_array["images"] = $this->admin_display_images($postID);
      endif;
    endif;

    // Return data to javascript
    echo json_encode($return_data_array);
    //

    wp_die();
  }

  /*
  * Image Deletion Function for front-end display
  *
  */
  public function MIGUD_sort_images() {

    $postID = $_POST['postID'];
    $image_ids = $_POST['image_ids'];
    $return_data_array = [];  // Return array shell

    if(count($image_ids) > 0) : // Truly, just a safe guard to stop empty img tags
      foreach ($image_ids as $key => $value) :
        if(!is_numeric($value)) :
          unset($image_ids[$key]);
        endif;
      endforeach;
      $save_data = implode("," ,$_POST['image_ids']);
      $return_data_array["updated"] = update_post_meta( $postID, 'migud_post_image_set', trim($save_data, ",") );  // Update database and return success or failure. Trim removes the trailing comma from the string
      if($return_data_array["updated"] !== false) : // Check for data saved if true grab all images...
        $return_data_array["images"] = $this->admin_display_images($postID);
      endif;
    endif;

    // Return data to javascript
    echo json_encode($return_data_array);
    //

    wp_die();
  }

  /*
  * Image Deletion Function for front-end display
  *
  */
  public function MIGUD_delete_images() {
    //
    $postID = $_POST['postID'];
    $image_ids = $_POST['image_ids'];
    $migud_post_image_set = explode(",", get_post_meta($postID, 'migud_post_image_set')[0]);
    $remainder_array = array_diff($migud_post_image_set, $image_ids);
    $saved_data = implode(",", $remainder_array);

    // Update database and return success or failure. Trim removes the trailing comma from the string
    $return_data_array["updated"] = update_post_meta( $postID, 'migud_post_image_set', $saved_data  );
    $migud_post_image_set = explode(",", get_post_meta($postID, 'migud_post_image_set')[0]);
    // echo $postID . " | " . print_r($image_ids, true) . " | " . print_r($migud_post_image_set, true) . " | " . print_r($remainder_array, true);
    $return_data_array["images"] = ($this->admin_display_images($postID) === 0) ? "0" : $this->admin_display_images($postID);

    echo json_encode($return_data_array);
    //

    wp_die();
  }

  /*
  * Shortcode Function for front-end display
  *
  */
  function MIGUD_shortcode ($atts, $content = "") {
    //
    $atts = shortcode_atts( array(
      'id' => '',
      'title' => '',
  	), $atts, 'migud_post_image_set' );

    $images = get_post_meta( $atts['id'], 'migud_post_image_set')[0];
    $display = explode(",", $images);

    // Code below is commented out for use in the template image return function
    if($images) {
      $return_data_template['display'] = [];
      $render_display = "\n" . '<!--- Multiple Image Gallery Uploader Display (MIGUD)---!>' . "\n";
      $render_display .= '<div id="migud-render" class="clearfix">' . "\n";

      // Display Title from shortcode
      if($atts['title']) {
        $render_display .= "\t" . '<h5>' . $atts['title'] . '</h5>' . "\n";
      }

      foreach ($display as $ele) :
        $return_data_template['display'][$ele]['title'] = get_the_title($ele);
        $return_data_template['display'][$ele]['alt'] = get_post_meta($ele, '_wp_attachment_image_alt', TRUE);
        $return_data_template['display'][$ele]['src'] = wp_get_attachment_image_src($ele, '')[0];

        $render_display .= "\t" . '<a class="migud-render-link" data-fancybox="gallery" href="';
        $render_display .= $return_data_template['display'][$ele]['src'];
        $render_display .= '">' . "\n\t\t" . '<img class="migud-render-link-image" src="';
        $render_display .= $return_data_template['display'][$ele]['src'];
        $render_display .= '" alt="';
        $render_display .= $return_data_template['display'][$ele]['alt'];
        $render_display .= '" />' . "\n\t" . '</a>';
        $render_display .= "\n";
      endforeach;

      $render_display .= '</div>';
      $render_display .= "\n" . '<!--- End (MIGUD)---!>' . "\n\n";

      return $render_display;
    }
  }

}
$miud = new MIGUD();
