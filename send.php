<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if(isset($_POST["send"])){
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "testemail";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Escape user inputs for security
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    // File upload directory
    $target_dir = "uploads/";

    // Get the file name
    $target_file = $target_dir . basename($_FILES["file"]["name"]);

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
    }

    // Upload file
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["file"]["name"])). " has been uploaded.";

        // Insert data into database with file path
        $sql = "INSERT INTO users (name, email, subject, message, file_path) VALUES ('$name', '$email', '$subject', '$message', '$target_file')";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";

            // PHPMailer configuration for admin email
            $admin_mail = new PHPMailer(true);
            $admin_mail->isSMTP();
            $admin_mail->Host = 'smtp.gmail.com';
            $admin_mail->SMTPAuth = true;
            $admin_mail->Username = 'inaniyakapil2000@gmail.com';
            $admin_mail->Password = 'bpvpurfrnavrould';
            $admin_mail->SMTPSecure = 'ssl';
            $admin_mail->Port = 465;
            $admin_mail->setFrom('inaniyakapil2000@gmail.com');
            $admin_mail->addAddress('inaniyakapil2000@gmail.com'); // Admin email
            $admin_mail->isHTML(true);
            $admin_mail->Subject = 'New user query';
            $admin_mail->Body = '<h2>New User Query</h2>
                                <p>Hello Admin,</p>
                                <p>You have received a new query from a user:</p>
                                <p>Name: ' . $_POST["name"] . '</p>
                                <p>Email: ' . $_POST["email"] . '</p>
                                <p>Subject: ' . $_POST["subject"] . '</p>
                                <p>Message: ' . $_POST["message"] . '</p>';
            // Attach file
            $admin_mail->addAttachment($target_file); // Attach file to user email

            // Send email to admin
            try {
                $admin_mail->send();
                echo "<script>alert('Email to admin sent');</script>";
            } catch (Exception $e) {
                echo "<script>alert('Message could not be sent. Mailer Error: {$e->getMessage()}');</script>";
            }

            // PHPMailer configuration for user email
            $user_mail = new PHPMailer(true);
            $user_mail->isSMTP();
            $user_mail->Host = 'smtp.gmail.com';
            $user_mail->SMTPAuth = true;
            $user_mail->Username = 'inaniyakapil2000@gmail.com';
            $user_mail->Password = 'bpvpurfrnavrould';
            $user_mail->SMTPSecure = 'ssl';
            $user_mail->Port = 465;
            $user_mail->setFrom('inaniyakapil2000@gmail.com');
            $user_mail->addAddress($_POST["email"]); // User email
            $user_mail->isHTML(true);
            $user_mail->Subject = 'Thank you for submitting';
            $user_mail->Body = '<h2>Thank You for Your Query</h2>
                                <p>Dear ' . $_POST["name"] . ',</p>
                                <p>Thank you for submitting your query. We will get back to you shortly.</p>
                                <p>Best Regards,</p>
                                <p>Admin</p>';

            // Attach file
            $user_mail->addAttachment($target_file); // Attach file to user email

            // Send email to user
            try {
                $user_mail->send();
                echo "<script>alert('Email to user sent');</script>";
            } catch (Exception $e) {
                echo "<script>alert('Message could not be sent. Mailer Error: {$e->getMessage()}');</script>";
            }
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }

    // Close database connection
    $conn->close();
}
?>
