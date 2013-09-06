<?php

include_once 'header.html';
if(isset($_POST['message'])) {
    $email_to = "info@lifesymb.com";
    $email_subject = "LifeSymb: mail from website contact form";

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $headers = 'From: '.$email."\r\n".
        'Reply-To: '.$email."\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $sent = mail($email_to, $email_subject, $message, $headers);
    echo "SENT: " . $sent;
}
include_once 'contact_thanks.html';
include_once 'footer.html';
?>