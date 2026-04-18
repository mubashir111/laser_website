<?php
header('Content-Type: application/json');

$phpMailerReady = false;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    $phpMailerReady = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
} elseif (file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    require __DIR__ . '/PHPMailer/src/Exception.php';
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
    $phpMailerReady = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"] ?? ''));
    $company = strip_tags(trim($_POST["company"] ?? ''));
    $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $service = strip_tags(trim($_POST["service"] ?? ''));
    $message = strip_tags(trim($_POST["message"] ?? ''));

    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Please complete all fields correctly."]);
        exit;
    }

    $recipient = 'muba4shir@gmail.com';
    $subject = "New Project Inquiry: $service (from $name)";
    $bodyContent = "
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Company:</strong> {$company}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Service of Interest:</strong> {$service}</p>
        <p><strong>Message:</strong><br>{$message}</p>
    ";
    $plainBody = strip_tags(str_replace('<br>', PHP_EOL, $bodyContent));

    if ($phpMailerReady) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // --- SMTP CONFIGURATION ---
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com';
            $mail->Password = 'your-app-password';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // --- RECIPIENTS ---
            $mail->setFrom('no-reply@laser.sa', 'Laser Tech Contact Form');
            $mail->addReplyTo($email, $name);
            $mail->addAddress($recipient);

            // --- CONTENT ---
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $bodyContent;
            $mail->AltBody = $plainBody;

            $mail->send();
            echo json_encode(["status" => "success", "message" => "Message has been sent!"]);
            exit;
        } catch (Throwable $e) {
            // Fall through to native mail() if PHPMailer is present but not configured correctly.
        }
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Laser Tech Contact Form <no-reply@laser.sa>',
        'Reply-To: ' . $name . ' <' . $email . '>',
    ];

    if (mail($recipient, $subject, $bodyContent, implode("\r\n", $headers))) {
        echo json_encode(["status" => "success", "message" => "Message has been sent!"]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Mail service is not configured. Install PHPMailer or configure PHP mail()."
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request."]);
}
?>
