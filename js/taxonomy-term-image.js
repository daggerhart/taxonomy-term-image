(function($){

  var taxonomyTermImage = {

    // modal frame
    file_frame: null,

    // modal settings
    settings: {
      title: tax_term_image_vars.modal_title,
      button: {
          text: tax_term_image_vars.modal_attach
      },
      multiple: false,  // Set to true to allow multiple files to be selected
      library: {
        type: 'image'
      }
    },

    init: function(){
      // delegate our click handler so that the image itself is clickable.  because that's cool
      $('body').on('click', '.taxonomy-term-image-attach', taxonomyTermImage.openModal );
      
      $('.taxonomy-term-image-remove').click( function(){
          $('#taxonomy-term-image-container').html('');
          $('#taxonomy-term-image-id').val('');
      });
    },

    /**
     * Open the media modal window
     * - http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
     * - https://gist.github.com/pippinsplugins/29bebb740e09e395dc06
     *
     * @param event
     */
    openModal: function( event ){
      event.preventDefault();

      // If the media frame already exists, reopen it.
      if ( taxonomyTermImage.file_frame ) {
        taxonomyTermImage.file_frame.open();
        return;
      }

      // Create the media frame.
      taxonomyTermImage.file_frame = wp.media.frames.file_frame = wp.media( taxonomyTermImage.settings );

      // When an image is selected, run a callback.
      taxonomyTermImage.file_frame.on( 'select', function() {

        taxonomyTermImage.file_frame.state()
          .get('selection')

          // handle each attachment
          .map( taxonomyTermImage.updateImage);
      });

      // Finally, open the modal
      taxonomyTermImage.file_frame.open();
    },

    /**
     * Handle a single selected image attachment
     * 
     * @param attachment
     */
    updateImage: function( attachment ){
      // the selected image
      var image = attachment.toJSON();

      // get image sizes data
      var sizes = attachment.get('sizes');

      //image.id
      $('#taxonomy-term-image-id').val( image.id );

      //sizes.thumbnail.url
      $('#taxonomy-term-image-container').html("<img class='taxonomy-term-image-attach' src='" + sizes.thumbnail.url + "' />");
    }

  };

  $(document).ready(function(){
    taxonomyTermImage.init();
  });

})(jQuery);
