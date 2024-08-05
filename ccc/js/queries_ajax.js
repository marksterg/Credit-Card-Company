/**
 * queries_ajax.js
 * Fills dropdown lists with data using ajax calls
 */
'use strict';

$(document).ready(function() {

    /**
     * Event listener for selecting a company id in dropdown menu
     */
    $("#client_id").change((e) => {
        var client_id = $("#client_id option:selected").val();

        // send an ajax to fetch html code for employees of the selected company
        $.ajax({
            url: "queries_ajax.php",
            type: "POST",
            dataType: "html",
            data: { company_selection: client_id },
            success: function(result) {
                // clear the dropdown menu list
                $("#employee_id").empty();
                // draw the dropdown menu list with the new company employees data
                $("#employee_id").html(result);
            }
        });

    });

});