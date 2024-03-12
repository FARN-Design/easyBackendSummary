jQuery(document).ready(function ($) {

   // Function to save datas from settings to database posttypes and userroles
    $(".ebsum-class").submit(function (e) {
        e.preventDefault(); 
    
        var form = $(this);

        //todo need data object
        /*
        let data = {
            nonce: "",
            action: "NAME of the ajax call",
            formData: form.serialize(),
        }
        */
        
        $.ajax({
            type: "POST",
            url: "../wp-content/plugins/easy-backend-summary/db/db-handle.php", //TODO Error must be to ajax main url
            data: form.serialize(),
            //data: data,
            success: function()
            {
                location.reload();
            }
        });
    });

 // Function to save datas from settings to database period, changes an the limits
    $("#main_settings").on('submit',function(e) {
        e.preventDefault();
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

    // functio to show or hide the Settings
    $('#ebsum_setting_button').click(function () {
        $('.ebsum_setting_wrapper').toggle();

    });

    $('.setting_categories_wrapper').click(function () {
        $('.setting_posttypes').toggle();

    });


    
    // functtion to show more or less list objects
    $('.showmoreposts').click(function(){
        $(this).addClass('hidebutton');
        $(this).closest('ul').find('.hiddenposts').addClass('showmore');
        $(this).parent().find('.showlessposts').addClass('showbutton');
        $(this).parent().find('.showlessposts').removeClass('hidebutton'); 
        
    });
    $('.showlessposts').click(function(){
        $(this).addClass('hidebutton');
        $(this).parent().find('.showmoreposts').removeClass('hidebutton');    
        $(this).parent().find('.showmoreposts').addClass('showbutton');        
        $(this).closest('ul').find('.hiddenposts').removeClass('showmore');
    });
    
   

    

});
