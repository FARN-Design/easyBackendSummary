jQuery(document).ready(function ($) {
    $("#ebsum_set_id").submit(function (e) {
        e.preventDefault();
        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = $(this);
        
        $.ajax({
            type: "POST",
            url: "../wp-content/plugins/easy-backend-summary/db/db-handle.php",
            data: form.serialize(), // serializes the form's elements.
            success: function(data)
            {
              alert(data); // show response from the php script.
            }
        });
    });
});