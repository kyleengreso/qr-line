<?php
include_once './../base.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require "./../../vendor/autoload.php";


function forgot_passwd_email($request_data) {
    global $project_name, $project_name_full;
    global $project_email, $project_phone;

    $username = $request_data['username'];
    $password = $request_data['password'];
    $email = $request_data['email'];

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
                <p>Your request has been proceed. Please check the details below.</p>
            </div>
            <div>
                <p>Username: $username</p>
                <p>Password: $password</p>
            </div>
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

function email_request_submit($request_data) {
    global $project_name, $project_name_full;
    global $project_email, $project_phone;

    $name = $request_data['name'];
    $email = $request_data['email'];
    $payment = $request_data['payment'];
    $transaction_id = $request_data['transaction_id'];
    $check_url = $request_data['website_check'];
    $cancel_url = $request_data['website_cancel'];
    $queue_count_int = $request_data['queue_count_int'];

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
                <p>Your request has been proceed. Please check the details below.</p>
            </div>
            <div>
                <h3>Your queue # is <strong>$queue_count_int</strong></h3>
            </div>
            <div>
                <table>
                    <tr><td>Name</td><td>$name</td></tr>
                    <tr><td>Email</td><td>$email</td></tr>
                    <tr><td>Payment</td><td>$payment</td></tr>
                </table>
            </div>
            <br>
            <div>
                If you want to check or cancel your request, click the link below.
            </div>
            <div style="display:flex; flex-direction:column">
                <a href="$check_url" style="text-decoration:none">View your request</a>
            </div>
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

function send_forgot_passwd($request_data) {
    global $project_name, $project_name_full;
    global $email_feature;
    global $smtp_host, $smtp_port, $smtp_email, $smtp_password;

    // echo json_encode($request_data);
    // exit;

    $username = $request_data['username'];
    $password = $request_data['password'];
    $email = $request_data['email'];
    // echo $email;
    try {
        //Server settings
        $mail = new PHPMailer($email_feature);
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $smtp_host;                            //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $smtp_email;                          //SMTP username
        $mail->Password   = $smtp_password;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = $smtp_port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom($smtp_email, $project_name);
        // $mail->addAddress('joe@example.net', 'The Requester');     //Add a recipient
        $mail->addAddress($email);               //Name is optional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'QR-LINE: Your change password request: ' . $username;
        $mail->Body    = forgot_passwd_email($request_data);
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        $mail->smtpClose();
        // echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function send_email_request_submit($request_data) {
    global $project_name, $project_name_full;
    global $email_feature;
    global $smtp_host, $smtp_port, $smtp_email, $smtp_password;

    $name = $request_data['name'];
    $email = $request_data['email'];
    $payment = $request_data['payment'];
    $transaction_id = $request_data['transaction_id'];
    $website_check = $request_data['website_check'];
    $website_cancel = $request_data['website_cancel'];
    $queue_count_int = $request_data['queue_count_int'];

    // echo json_encode(array(
    //     "name" => $name,
    //     "email" => $email,
    //     "payment" => $payment,
    //     "transaction_id" => $transaction_id,
    //     "website_check" => $website_check,
    //     "website_cancel" => $website_cancel,
    //     "queue_count_int" => $queue_count_int
    // ));
    // exit;
    try {
        //Server settings
        $mail = new PHPMailer($email_feature);
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $smtp_host;                            //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $smtp_email;                          //SMTP username
        $mail->Password   = $smtp_password;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = $smtp_port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom($smtp_email, $project_name);
        // $mail->addAddress('joe@example.net', 'The Requester');     //Add a recipient
        $mail->addAddress($email);               //Name is optional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'QR-LINE: Your Request #' . $queue_count_int;
        $mail->Body    = email_request_submit($request_data);
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        $mail->smtpClose();
        // echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function send_email_notify_before_5($request_data) {
    global $project_name, $project_name_full;
    global $email_feature;
    global $smtp_host, $smtp_port, $smtp_email, $smtp_password;

    // echo json_encode($request_data);
    // exit;
    $name = $request_data['name'];
    $email = $request_data['email'];
    $payment = $request_data['payment'];
    $transaction_id = $request_data['transaction_id'];
    $queue_count_int = $request_data['queue_count_int'];
    try {
        //Server settings
        $mail = new PHPMailer($email_feature);
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $smtp_host;                            //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $smtp_email;                          //SMTP username
        $mail->Password   = $smtp_password;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = $smtp_port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom($smtp_email, $project_name);
        // $mail->addAddress('joe@example.net', 'The Requester');     //Add a recipient
        $mail->addAddress($email);               //Name is optional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'QR-LINE: Your Request #' . $queue_count_int . " is coming";
        $mail->Body    = email_nofify_before_5($request_data);
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        $mail->smtpClose();
        // echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Sample usage
// echo email_content("Marc Buday", "webmaster@gmail.com", "assessment", "2","", "", "./../asset/images/favicon.png");
?>