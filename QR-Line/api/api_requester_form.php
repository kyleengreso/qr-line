<?php
include_once "./../base.php";
include_once "./../includes/db_conn.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require "./../../vendor/autoload.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the POST data
    session_start();
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->name) || !isset($data->email) || !isset($data->payment) || !isset($data->website)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please fill up the information."
        ));
        exit;
    }

    $name = $data->name;
    $email = $data->email;
    $payment = $data->payment;
    $website = $data->website;

    $conn->begin_transaction();

    try {
        // Commit the request transaction
        $sql_cmd = "INSERT INTO requesters (name, email, payment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sss", $name, $email, $payment);
        $stmt->execute();
        $requester_id = $stmt->insert_id;
        $stmt->close();

        // Get queue_count_int value from setup
        $sql_cmd = "SELECT setup_value_int FROM setup_system WHERE setup_key = 'queue_count'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $setup_row = $result->fetch_assoc();
        $queue_count_int = $setup_row['setup_value_int'];
        // Then increment the queue_count_int value by 1
        $queue_count_int++;
        $stmt->close();
        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'queue_count'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $queue_count_int);
        $stmt->execute();
        $stmt->close();
    
        // Generate a random token number
        $token_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // Commit the transaction after the requester is inserted
        $sql_cmd = "INSERT INTO transactions (idrequester, token_number, queue_number) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sss", $requester_id, $token_number, $queue_count_int);
        $stmt->execute();
        $transaction_id = $stmt->insert_id;
        $stmt->close();

        $conn->commit();

        $_SESSION['requester_token'] = $token_number;
        echo json_encode(array(
            "status" => "success",
            "message" => "Queue number generated successfully",
            "queue_number" => $queue_count_int,
            "token_number" => $token_number,
        ));

        // how to hide email reponse below this code
        // After the transaction was posted then use email feature to
        // send the transaction data to the requester using his/her email
        global $project_name, $project_name_full;
        global $email_feature;
        global $smtp_host, $smtp_port, $smtp_email, $smtp_password;

        $website_check = $website . '?requester_token=' . $token_number;
        $website_cancel = $website . '?requester_token=' . $token_number . '&cancel=true';

        include "./email_content.php";
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
            $mail->Body    = email_content($name, $email, $payment, $transaction_id, $website_check, $website_cancel);
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(array(
            "status" => "error",
            "message" => "An error occurred. Please try again.",
            "error" => $e->getMessage()
        ));
        exit;
    }
}
?>