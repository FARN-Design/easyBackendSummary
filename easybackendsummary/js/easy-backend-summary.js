jQuery(document).ready(function ($) {


    // functio to show or hide the Settings and scroll to elemt if shown
    $('#ebsum_setting_button').click(function () {
        $('.ebsum_setting_wrapper').toggle();
        $([document.documentElement, document.body]).animate({
            scrollTop: $('.ebsum_setting_wrapper').offset().top
        }, 500);

    });

    
    // functio to show or hide the posttype-settings and scroll to elemt if shown
    $('.ebsum_setting_categories_wrapper').click(function () {
        $('.ebsum_setting_posttypes').toggle();

    });


    // function to display alert if load limit is smaler then quantity and disable the submit function
    $("#ebsum_main_settings").on('submit',function(e) {
        e.preventDefault();
        var quantity = parseInt($('#ebsum_quantitys').val());
        
        var load_limit = parseInt($('#ebsum_loadlimits').val());
        
        if(load_limit < quantity){
            $(".ebsum_load_warning").css('display', 'block');
        }else{
            this.submit();
        }
    });
    

    // functtion to show more or less list objects
    $('.ebsum_showmoreposts').click(function(){
        $(this).addClass('ebsum_hidebutton');
        $(this).closest('ul').find('.ebsum_hiddenposts').addClass('ebsum_showmore');
        $(this).parent().find('.ebsum_showlessposts').addClass('ebsum_showbutton');
        $(this).parent().find('.ebsum_showlessposts').removeClass('ebsum_hidebutton'); 
        
    });
    $('.ebsum_showlessposts').click(function(){
        $(this).addClass('ebsum_hidebutton');
        $(this).parent().find('.ebsum_showmoreposts').removeClass('ebsum_hidebutton');    
        $(this).parent().find('.ebsum_showmoreposts').addClass('ebsum_showbutton');        
        $(this).closest('ul').find('.ebsum_hiddenposts').removeClass('ebsum_showmore');
    });
    
   

    

});
