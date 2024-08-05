/**
 * transactions_ajax.js
 * Fills dropdown lists with data using ajax calls
 */
'use strict';

$(document).ready(function() {

    /**
     * Event listener for selecting a client type in the dropdown menu list (individual or company employee)
     */
    $("#select_type_client_to_buy").change((e) => {
        var selection = $("#select_type_client_to_buy option:selected").val();

        // clear alert message (error, success)
        $(".alert_form_div").empty();

        // send an ajax to fetch html code for all the clients
        $.ajax({
            url: "transactions_ajax.php",
            type: "POST",
            dataType: "html",
            data: {
                trans_procedure: "buy",
                select_type_client_to_buy: selection
            },
            success: function(result) {
                // clear the dropdown menu list
                $("#client_buy").empty();
                // draw the new clients
                $("#client_buy").html(result);
            }
        });
    });

});