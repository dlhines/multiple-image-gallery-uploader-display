(function($) {

  // On page load to check empty gallery
    if( $("#image-uploader-display #gallery_load").is(":empty")){
      $("#image-uploader-display #gallery_load").html('<h4>Click "Select Images"</h4>');
    }

    // Toggle instructions
    $("#image-uploader-display").on('click', '#miud_instructions p.header', function(){
      // Toggles instructions
      $("#image-uploader-display #miud_instructions section").toggle();

      // Changes contents of p tag
      if($("#miud_instructions section").is(":visible")) {
        $("#image-uploader-display #miud_instructions p.header").html("Multiple Image Upload Instructions ( Hide )")
      } else {
        $("#image-uploader-display #miud_instructions p.header").html("Multiple Image Upload Instructions ( Show )")
      }
    })

    // Toggle instructions
    $("#image-uploader-display").on('click', '#shrtcde p.shortcodes', function(){
      // Toggles shrtcde
      $("#image-uploader-display #shrtcde section").toggle();

      // Changes contents of p tag
      if($("#shrtcde section").is(":visible")) {
        $("#image-uploader-display #shrtcde p.shortcodes").html("Default & Custom Shortcode (Hide)")
      } else {
        $("#image-uploader-display #shrtcde p.shortcodes").html("Default & Custom Shortcode (Show)")
      }
    })


    // var for WP Media Uploader
    var mediaUploader;

    // jQuery UI sortable functions
    // Custom namespace SORT for jQuery Sortable. This needs to be called again after ajax
    // https://jqueryui.com/sortable/
    ;IMAGE_SORT = {
      sort: function () {
        $( "#image_sortable" ).sortable({
          revert: true,
          update: function( event, ui ) {
            var image_ids = new Array();
            var postID = $("#select_images").attr("data-id");
            $("#image-uploader-display #gallery_load ul li img").map(function(index){
              image_ids.push(this.id);
            });
            $.ajax({
              url: migud_sort_images.ajax_url,
              type: 'post',
              data: { action: 'migud_sort_images', image_ids: image_ids, postID: postID },
              success: function(response) {
                image_ids.splice(0, image_ids.length);
                var value = JSON.parse(response);
                if(value.updated !== false && value.images !== "") {
                  $("#image-uploader-display #gallery_load").html(value.images);
                  IMAGE_SORT.sort(); // Re-establish jQuery Sortable after ajax calls
                } else {
                  $("#image-uploader-display #gallery_load").html('<h4>Click "Select Images"</h4>');
                }
              }
            });
          }
        });
      }
    }

    // Call the Sort function
    IMAGE_SORT.sort();


    //
    // Select Images
    //
    $("#image-uploader-display").on('click', '#select_images', function(){

      // Array to save image ids, urls, and get the contents to the gallery load div
      var postID = $(this).attr("data-id");
      var image_ids = new Array();
      var nonce = $("#migud_post_image_set_meta_box_nonce").val()
      var display = $("#image-uploader-display #gallery_load").html();

      if (mediaUploader) {
         mediaUploader.open();
         return;
       }
       mediaUploader = wp.media.frames.file_frame = wp.media({
         title: 'Select Image(s)',
         button: {
         text: 'Select Image(s)'
       }, multiple: true });
       mediaUploader.on('select', function() {
         var selection = mediaUploader.state().get('selection');
         selection.map( function( attachment ) {
             attachment = attachment.toJSON();
             image_ids.push(attachment.id);
         });
        $.ajax({
          url: migud_save_images.ajax_url,
          type: 'post',
          data: { action: 'migud_save_images', security: migud_save_images.ajax_nonce, image_ids: image_ids, postID: postID, nonce: nonce
          },
          success: function(response) {
            image_ids.splice(0, image_ids.length);
            var value = JSON.parse(response);
            if(value.updated !== false && value.images !== "") {
              $("#image-uploader-display #gallery_load").html(value.images);
              IMAGE_SORT.sort();
              window.location.reload(true);
            } else {
              $("#image-uploader-display #gallery_load").html('<h4>Click "Select Images"</h4>');
            }
          }
        });
       });
       mediaUploader.open();
    });

    //
    // Double click images for deletion
    //
    $("#image-uploader-display").on('dblclick', '#gallery_load ul li img',function() {
      if($(this).hasClass("remove")) {
        $(this).removeClass("remove");
      } else {
        $(this).addClass("remove");
      }
    });

    //
    // Delete Images
    //
    $("#image-uploader-display").on('click', '#delete_images', function(e){
      e.preventDefault();
      var postID = $(this).attr("data-id");
      var image_ids = new Array();

      $("#image-uploader-display #gallery_load .remove").map(function(index){
        image_ids.push(this.id);
      });
      if(image_ids.length !== 0) {
        $.ajax({
          url: migud_delete_images.ajax_url,
          type: 'post',
          data: { action: 'migud_delete_images', postID: postID, image_ids: image_ids },
          success: function(response) {
            image_ids.splice(0, image_ids.length);
            var value = JSON.parse(response);
            // Check to make sure value.updated is not false
            if(value.updated !== false) {
              if(value.images.length === 0) {
                $("#image-uploader-display #gallery_load").html('<h4>Click "Select Images"</h4>');
                IMAGE_SORT.sort();
              } else {
                $("#image-uploader-display #gallery_load").html(value.images);
                IMAGE_SORT.sort();
              }
            } else {
              alert("Error: Did not save selected image(s).");
            }
          }
        });
      } else {
        alert("You have not selected any images to delete.");
      }
      // End jQuery
    });
})(jQuery);
