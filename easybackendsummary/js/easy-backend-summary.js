jQuery(document).ready(function ($) {


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
