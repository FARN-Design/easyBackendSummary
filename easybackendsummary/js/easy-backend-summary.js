jQuery(document).ready(function ($) {

   // Function to save datas from settings to database posttypes and userroles
   $(".ebsum-class").submit(function (e) {
    e.preventDefault(); 

        var data = {
            action: "create_post_type_setting",
            formData: $(this).serialize(),
        };

        $.ajax({
            type: "POST",
            url: ebsum_ajax_data.ebsum_url,
            nonce: ebsum_ajax_data.nonce,
            data: data, 
            success:function( data ) {
                location.reload();
            },
            error: function(data){
                console.log(error);
            }
        });
    });


 // Function to save datas from settings to database period, changes an the limits
    $("#main_settings").on('submit',function(e) {
        e.preventDefault();
        let quantity = $('#quantitys').val();
        let load_limit = $('#loadlimits').val();
        if(load_limit < quantity){
            $(".load_warning").css('display', 'block');
        }else{
        var data = {
            action: "main_settings",
            formData: $(this).serialize(),
        };

        $.ajax({
            type: "POST",
            url: ebsum_ajax_data.ebsum_url,
            nonce: ebsum_ajax_data.nonce,
            data: data, 
            success:function( data ) {
                location.reload();
            },
            error: function(data){
                console.log(error);
            }
        });
    }
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
