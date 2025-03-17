<?php
include_once './../base.php';

function email_content($name, $email, $payment, $transaction_id, $check_url, $cancel_url) {
    global $project_name, $project_name_full;
    global $project_email, $project_phone;

    $content = <<<HTML
    <!DOCTYPE html>
    <head>
    </head>
    <body style="font-family: Arial, sans-serif;">
        <div>
            <h2><strong>$project_name_full</strong></h2>
        </div>
        <div>
            <div>
                <p>Your request is 5 request ahead. Go back at the line.</p>
            </div>
            <div>
                <h3>Your queue # is <strong>$transaction_id</strong></h3>
            </div>
            <div>
                <table>
                    <tr><td>Name</td><td>$name</td></tr>
                    <tr><td>Email</td><td>$email</td></tr>
                    <tr><td>Payment</td><td>$payment</td></tr>
                </table>
            </div>
            <br>
            <br>

            <!-- horizontal line -->
            <hr style="border: 1px solid #000;">

            <div>
                <p>Feel free to contact us if you have any question.</p>
                <div>
                    Email: <span><a href="mailto:$project_email">$project_email</a></span>
                </div>
                <div>
                    Phone: <span><a href="tel:$project_phone">$project_phone</a></span>
                </div>
            </div>
        </div>
    </body>
    HTML;

    return $content;
}

// Sample usage
// echo email_content("Marc Buday", "webmaster@gmail.com", "assessment", "2","", "", "./../asset/images/favicon.png");
?>