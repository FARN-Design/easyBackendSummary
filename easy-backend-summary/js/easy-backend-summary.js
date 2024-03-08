jQuery(document).ready(function ($) {

    $(".ebsum-class").submit(function (e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.
    
        var form = $(this);
        
        $.ajax({
            type: "POST",
            url: "../wp-content/plugins/easy-backend-summary/db/db-handle.php",
            data: form.serialize(), // serializes the form's elements.
            success: function()
            {
                location.reload();
            }
        });
    });

    $("#periods").on('change',function() {

        var form = $(this);
        
        $.ajax({
            type: "POST",
            url: "../wp-content/plugins/easy-backend-summary/db/db-handle.php",
            data: form.serialize(),
            success: function()
            {
                location.reload();
            }
        });
    });

    $("#quantitys").on('change',function() {

        var form = $(this);
        
        $.ajax({
            type: "POST",
            url: "../wp-content/plugins/easy-backend-summary/db/db-handle.php",
            data: form.serialize(),
            success: function()
            {
                location.reload();
            }
        });
    });

    $('#ebsum_setting_button').click(function () {
        $('.ebsum_setting_wrapper').toggle();

    });

    $('.setting_posttypes_wrapper').click(function () {
        $('.setting_posttypes').toggle();

    });

    $('.setting_userolles_wrapper').click(function () {
        $('.setting_userroles').toggle();

    });
});
