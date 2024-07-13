jQuery(document).ready(function($){
    $('#upload_logo_button').click(function(e) {
        alert('Logo uploaded successfully!');
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function(e){
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#adas_user_logo').val(image_url);
        });
    });
});