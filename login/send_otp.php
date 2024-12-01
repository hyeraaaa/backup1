<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'dbh.inc.php';
require '../admin/features/config.php';
require '../admin/features/log.php';

// function sendMessage($contact_number, $message)
// {
//     $infobip_url = "https://wg43qy.api.infobip.com/sms/2/text/advanced";
//     $api_key = INFOPB_API_KEY;

//     $data = [
//         "messages" => [
//             [
//                 "from" => "447491163443",
//                 "destinations" => [
//                     ["to" => $contact_number]
//                 ],
//                 "text" => $message
//             ]
//         ]
//     ];

//     $headers = [
//         "Authorization: App $api_key",
//         "Content-Type: application/json",
//         "Accept: application/json"
//     ];

//     $options = [
//         'http' => [
//             'header'  => implode("\r\n", $headers),
//             'method'  => 'POST',
//             'content' => json_encode($data),
//         ],
//     ];

//     $context = stream_context_create($options);
//     $result = file_get_contents($infobip_url, false, $context);
//     if ($result === FALSE) {
//         error_log("Failed to send SMS to $contact_number");
//         return false;
//     }
//     error_log("Sent SMS to $contact_number: $result");
//     return json_decode($result, true);
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $user_found = false;
    $user_type = '';
    $first_name = '';
    $contact_number = '';
    $display_email = $email;

    $stmt_student = $pdo->prepare("SELECT * FROM student WHERE email = :email");
    $stmt_student->execute(['email' => $email]);
    $result_student = $stmt_student->fetch(PDO::FETCH_ASSOC);

    if (isset($_GET['email'])) {
        $display_email = $_GET['email'];
    }

    if ($result_student) {
        $user_found = true;
        $user_type = 'student';
        $contact_number = $result_student['contact_number'];
        $first_name =  $result_student['first_name'];
    }

    $stmt_staff = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
    $stmt_staff->execute(['email' => $email]);
    $result_staff = $stmt_staff->fetch(PDO::FETCH_ASSOC);

    if ($result_staff) {
        $user_found = true;
        $user_type = 'admin';
        $contact_number = $result_staff['contact_number'];
        $first_name =  $result_staff['first_name'];
    }

    if ($user_found) {
        $otp = rand(100000, 999999);
        $otp_expiry = gmdate("Y-m-d H:i:s", strtotime('+10 minutes'));

        if ($user_type == 'student') {
            $update_stmt = $pdo->prepare("UPDATE student SET otp = :otp, otp_expiry = :otp_expiry WHERE email = :email");
            logAction($pdo, $result_student['student_id'], 'Student', 'Password Reset Attempt', 'student', $result_student['student_id'], 'An OTP has been sent');
        } else {
            $update_stmt = $pdo->prepare("UPDATE admin SET otp = :otp, otp_expiry = :otp_expiry WHERE email = :email");
            if($result_staff['role'] === 'superadmin'){
                $role = 'superadmin';
            } else {
                $role = 'admin';
            }
            logAction($pdo, $result_staff['admin_id'], $role, 'Password Reset Attempt', 'admin', $result_staff['admin_id'], 'An OTP has been sent');
        }

        $update_stmt->execute([
            'otp' => $otp,
            'otp_expiry' => $otp_expiry,
            'email' => $email
        ]);



        // Send Email with OTP
        $mail = new PHPMailer(true);

        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ismsbatstateu@gmail.com';
        $mail->Password = 'vkfy htwr ldkd qoav';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('ismsbatstateu@gmail.com', 'ISMS - BSU Announcement Portal');
        $mail->addAddress($email);

        //Content
        $mail->isHTML(true);
        $mail->addEmbeddedImage('pics/brand.png', 'brand_logo');
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Password Reset Request</title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            </head>
            <body style="margin: 0;">
                <div style="width: 600px; max-width: 100%; background-color: #dc3545; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 30px; margin: 15px auto; font-family: \'Poppins\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, sans-serif;">
                    <div style="background-color: #ffffff; border-radius: 6px; padding: 25px;">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <img src="cid:brand_logo" alt="ISMS Logo" style="height: 50px;">
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <h2 style="margin: 0 0 15px; color: #333333; font-size: 22px; font-weight: 600; letter-spacing: -0.5px;">Password Reset Request</h2>
                            <p style="font-size: 14px; line-height: 1.5; color: #555555; margin: 0 0 15px; font-weight: 400;">Dear ' . htmlspecialchars($first_name) . ',</p>
                            <p style="font-size: 14px; line-height: 1.5; color: #555555; margin: 0; font-weight: 400;">We received a request to reset your password. For your security, use the verification code below to complete the password reset process:</p>
                        </div>
            
                        <div style="text-align: center; margin: 20px 0;">
                            <div style="font-family: \'Poppins\', monospace; font-size: 28px; font-weight: 600; letter-spacing: 6px; background-color: #f8f9fa; color: #1a1a1a; padding: 15px 25px; border: 2px solid #dc3545; border-radius: 6px; display: inline-block;">
                                ' . htmlspecialchars($otp) . '
                            </div>
                        </div>
            
                        <div style="background-color: #fff8f8; border-left: 4px solid #dc3545; padding: 12px; margin: 15px 0; border-radius: 4px;">
                            <p style="font-size: 13px; color: #666666; margin: 0; font-weight: 400;">
                                <strong style="font-weight: 600;">Important:</strong> This verification code will expire in 10 minutes for security purposes.
                            </p>
                        </div>
            
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eaeaea;">
                            <p style="font-size: 13px; color: #666666; margin: 0 0 8px; line-height: 1.5; font-weight: 400;">If you didn\'t request this password reset, please ignore this email or contact our support team if you have concerns about your account security.</p>
                        </div>
            
                        <div style="margin-top: 20px; text-align: left;">
                            <p style="font-size: 13px; color: #666666; margin: 0 0 2px; font-weight: 400;">Best regards,</p>
                            <p style="font-size: 13px; font-weight: 600; color: #333333; margin: 0;">The I-SMS Team</p>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 15px;">
                        <p style="font-size: 11px; color: #ffffff; margin: 0; font-weight: 300;">This is an automated message, please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
            ';
        $mail->send();

        // Send SMS with OTP
        // $smsMessage = "Your OTP is: $otp. It is valid for 10 minutes.";
        // sendMessage($contact_number, $smsMessage);

?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Validation OTP</title>
            <?php include '../cdn/head.html'; ?>

            <link rel="stylesheet" href="login.css">
        </head>

        <body class="d-flex flex-column min-vh-100">
            <header class="header_container bg-white">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-3">
                            <a href="#" class="d-block">
                                <img
                                    src="pics/brand.png"
                                    alt="Brand Logo"
                                    class="img-fluid"
                                    width="150"
                                    height="auto">
                            </a>
                        </div>
                        <div class="col-6">
                            <h1 class="text-center fw-bold m-0">ISMS ANNOUNCEMENTS</h1>
                        </div>
                        <div class="col-3 text-end">
                            <a href="#" class="d-block">
                                <img
                                    src="pics/bsu_logo.png"
                                    alt="BSU Logo"
                                    class="img-fluid"
                                    width="150"
                                    height="auto">
                            </a>
                        </div>
                    </div>
                </div>
            </header>


            <section class="login_container flex-grow-1 d-flex justify-content-center align-items-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-6 col-lg-5">
                            <div class="card shadow-lg border-0">
                                <div class="card-body p-4">
                                    <div class="text-center mb-3">
                                        <h2 class="fw-bold">Verify OTP</h2>
                                        <p class="text-muted mb-0">Please check your email for the OTP code</p>
                                    </div>

                                    <form id="otp_form" method="POST" action="validate_otp.php">
                                        <?php if (isset($_GET['message'])): ?>
                                            <div class="alert alert-danger py-2"><?php echo $_GET['message']; ?></div>
                                        <?php endif; ?>

                                        <?php if (isset($_GET['error'])): ?>
                                            <div class="alert alert-danger py-2">
                                                <?php echo htmlspecialchars($_GET['error']); ?>
                                            </div>
                                            <script>
                                                $(document).ready(function() {
                                                    setTimeout(function() {
                                                        $(".alert").fadeOut('slow');
                                                    }, 3000);
                                                });
                                            </script>
                                        <?php endif; ?>

                                        <div class="form-floating mt-3">
                                            <input
                                                type="email"
                                                name="email"
                                                id="email"
                                                class="form-control"
                                                placeholder="name@example.com"
                                                value="<?php echo htmlspecialchars($display_email); ?>"
                                                readonly
                                                required>

                                            <label for="email">Email address</label>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label">Enter OTP Code</label>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <input type="text" class="form-control text-center otp-input" maxlength="1" data-index="1" style="width: 45px; height: 45px; padding: 0;">
                                                <input type="text" class="form-control text-center otp-input" maxlength="1" data-index="2" style="width: 45px; height: 45px; padding: 0;">
                                                <input type="text" class="form-control text-center otp-input" maxlength="1" data-index="3" style="width: 45px; height: 45px; padding: 0;">
                                                <input type="text" class="form-control text-center otp-input" maxlength="1" data-index="4" style="width: 45px; height: 45px; padding: 0;">
                                                <input type="text" class="form-control text-center otp-input" maxlength="1" data-index="5" style="width: 45px; height: 45px; padding: 0;">
                                                <input type="text" class="form-control text-center otp-input" maxlength="1" data-index="6" style="width: 45px; height: 45px; padding: 0;">
                                            </div>
                                            <input type="hidden" name="otp" id="otp">
                                            <div class="invalid-feedback text-center"></div>
                                        </div>

                                        <button type="submit" class="btn btn-danger w-100 py-2 mt-3">
                                            Verify OTP
                                        </button>

                                        <div class="text-center text-danger mt-3">
                                            <button type="button" id="resendOTP" class="btn btn-link text-decoration-none p-0" disabled>
                                                Resend OTP (60s)
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <footer class="bg-black py-3">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h2 class="h5 text-white mb-2">BATANGAS STATE UNIVERSITY</h2>
                            <p class="text-white-50 mb-2">A premier national university that develops leaders in the global knowledge economy</p>
                            <p class="text-white-50 small mb-0">Copyright &copy; <?php echo date('Y'); ?></p>
                        </div>
                        <div class="col-4">
                            <div class="text-end">
                                <img
                                    src="pics/redspartan-logo.png"
                                    alt="Red Spartan Logo"
                                    class="img-fluid"
                                    width="150"
                                    height="auto">
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <?php include '../cdn/body.html'; ?>
            <script src="resend-otp.js"></script>
        </body>

        </html>
<?php

    } else {
        echo '<script>alert("Email does not exist in either student or admin records.")</script>';
        header('Location: login.php?error=Email does not exist in either student or admin records');
    }
}
