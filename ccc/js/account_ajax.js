/**
 * account_ajax.js
 * Fills dropdown lists with data using ajax calls
 */
'use strict';

/**
 * Functions and Event Listeners for Account Register procedure.
 */
$(document).ready(function() {

    /**
     * clearing the divs on change in dropdown lists (user selection)
     */
    function clear_divs_user() {
        $("#seller_div").empty();
        $("#individual_div").empty();
        $("#company_div").empty();
        $("#company_employee_div").empty();
        $("#client_div").empty();
    }

    /**
     * clearing the divs on change in dropdown lists (client selection)
     */
    function clear_divs_client() {
        $("#individual_div").empty();
        $("#company_div").empty();
        $("#company_employee_div").empty();
    }

    /**
     * Event listener for selecting an option in user_type selection div
     */
    $("#user_type").change((e) => {
        var user_selection = $("#user_type option:selected").val();

        // clear alert message (error, success)
        $(".alert_form_div").empty();
        // clear results table
        $(".col-md-15").empty();

        if (user_selection == "client") {
            // send an ajax to fetch html code for client as user type
            $.ajax({
                url: "account_ajax.php",
                type: "POST",
                dataType: "html",
                data: { user_type: "client" },
                success: function(result) {
                    // clear all the other possible divs to refresh them with the new content
                    clear_divs_user();
                    // draw the new content
                    $("#client_div").html(result);
                    /**
                     * Event listener for selecting an option in client_type selection div
                     */
                    $("#client_type").change((e) => {
                        var client_selection = $("#client_type option:selected").val();

                        if (client_selection == "individual" || client_selection == "company") {

                            // send an ajax to fetch html code for individual or company as a client type
                            $.ajax({
                                url: "account_ajax.php",
                                type: "POST",
                                dataType: "html",
                                data: { client_type: client_selection },
                                success: function(result) {
                                    // clear all the other possible divs to refresh them with the new content
                                    clear_divs_client();
                                    // draw the new content
                                    $("#individual_div").html(result);
                                    /** 
                                     * event listener for filling the client's credit balance input with the same amount of credit limit
                                     */
                                    $("#credit_limit").change((e) => {
                                        var limit = $("#credit_limit option:selected").val();
                                        $("#credit_balance").val(limit);
                                    });
                                }
                            });
                        } else {
                            // send an ajax to fetch html code for employee as a client type
                            $.ajax({
                                url: "account_ajax.php",
                                type: "POST",
                                dataType: "html",
                                data: { client_type: "company_employee" },
                                success: function(result) {
                                    // clear all the other possible divs to refresh them with the new content
                                    clear_divs_client();
                                    // draw the new content
                                    $("#company_employee_div").html(result);
                                }
                            });
                        }
                    });
                }
            });
        } else {
            // send an ajax to fetch html code for seller as user type
            $.ajax({
                url: "account_ajax.php",
                type: "POST",
                dataType: "html",
                data: { user_type: "seller" },
                success: function(result) {
                    // clear all the other possible divs to refresh them with the new content
                    clear_divs_user();
                    // draw the new content
                    $("#seller_div").html(result);
                }
            });
        }
    });

});