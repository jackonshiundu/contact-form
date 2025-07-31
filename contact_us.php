<?php
//composer require phpmailer/phpmailer

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'john_doe';

// Initialize variables
$name = $email = $subject = $message = '';
$errors = [];
$success = false;

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure this path is correct

// Process form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // Validate inputs
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if (empty($subject)) {
        $errors['subject'] = 'Subject is required';
    }

    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }

    if (empty($errors)) {
        // Save to database
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO contact (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            // Send email via Gmail SMTP
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'jackonshiundu2019@gmail.com';     // Your Gmail address
                $mail->Password   = 'depeunrrwvzuaeic';                 // Your app password WITHOUT spaces
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('jackonshiundu2019@gmail.com', 'Website Contact Form');
                $mail->addAddress('jackonshiundu2019@gmail.com', 'Jackon Shiundu');

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = "<p><strong>Name:</strong> {$name}</p>
                                  <p><strong>Email:</strong> {$email}</p>
                                  <p><strong>Email:</strong> {$subject}</p>
                                  <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
                $mail->AltBody = "Name: $name\nEmail: $email\nMessage:\n$message";

                $mail->send();
                $success = true;
            } catch (Exception $e) {
                $errors['email_send'] = "Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $errors['database'] = 'Error saving to database: ' . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
}

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact Us</title>
    <style>
        .error { color: red; }
        .success { color: green; }
        form { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; }
        textarea { height: 150px; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Contact Us</h1>

    <?php if ($success): ?>
        <p class="success">Thank you for your message! We'll get back to you soon.</p>
    <?php endif; ?>

    <?php if (isset($errors['database'])): ?>
        <p class="error"><?php echo $errors['database']; ?></p>
    <?php endif; ?>

    <?php if (isset($errors['email_send'])): ?>
        <p class="error"><?php echo $errors['email_send']; ?></p>
    <?php endif; ?>

    <form action="contact_us.php" method="post">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" />
            <?php if (isset($errors['name'])): ?>
                <span class="error"><?php echo $errors['name']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" />
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?php echo $errors['email']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" />
            <?php if (isset($errors['subject'])): ?>
                <span class="error"><?php echo $errors['subject']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="message">Message:</label>
            <textarea id="message" name="message"><?php echo htmlspecialchars($message); ?></textarea>
            <?php if (isset($errors['message'])): ?>
                <span class="error"><?php echo $errors['message']; ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Send Message</button>
    </form>
</body>
</html>
