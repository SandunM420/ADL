<?php
/**
 * Contact Form Handler
 *
 * Validates the submitted contact form and sends an email via SMTP
 * using PHPMailer. Returns a JSON response so the frontend can
 * display inline success / error feedback without a page reload.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

session_start();
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/vendor/phpmailer/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Sanitise and return a plain-text string from user input.
 *
 * @param  string $value
 * @param  int    $max_length
 * @return string
 */
function clean_input($value, $max_length = 255) {
    return substr(trim(strip_tags($value)), 0, $max_length);
}

// ── Validate inputs ──────────────────────────────────────────────────────────

$name    = clean_input($_POST['name']    ?? '');
$email   = clean_input($_POST['email']   ?? '', 150);
$phone   = clean_input($_POST['phone']   ?? '', 50);
$product = clean_input($_POST['product'] ?? '', 255);
$message = clean_input($_POST['message'] ?? '', 2000);

$errors = [];

if ($name === '') {
    $errors[] = 'Name is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if ($message === '') {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['error' => implode(' ', $errors)]);
    exit;
}

// ── Send email ───────────────────────────────────────────────────────────────

try {
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    // Recipients
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO);
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Enquiry from ' . $name;
    $mail->Body    = build_html_email($name, $email, $phone, $product, $message);
    $mail->AltBody = build_plain_email($name, $email, $phone, $product, $message);

    $mail->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log('contact-handler.php — mailer error: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['error' => 'Could not send your message. Please try again or contact us directly by phone.']);
}

/**
 * Build the HTML version of the notification email.
 *
 * @return string
 */
function build_html_email($name, $email, $phone, $product, $message) {
    $esc = fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    $row = fn($label, $value) => $value
        ? "<tr><td style='padding:6px 12px;font-weight:600;color:#6B6560;white-space:nowrap;'>{$label}</td><td style='padding:6px 12px;color:#1A1714;'>" . $esc($value) . "</td></tr>"
        : '';

    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#F5F2EC;font-family:Inter,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:40px 16px;">
<table width="600" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
  <tr><td style="background:#1A1714;padding:28px 32px;">
    <p style="margin:0;font-size:20px;font-weight:700;color:#C9A84C;letter-spacing:0.04em;">ABEYWARDANA DISTRIBUTORS</p>
    <p style="margin:4px 0 0;font-size:13px;color:#A09890;">New website enquiry</p>
  </td></tr>
  <tr><td style="padding:32px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E8E2D9;border-radius:8px;">
      ' . $row('Name', $name) . $row('Email', $email) . $row('Phone', $phone) . $row('Product Interest', $product) . '
    </table>
    <p style="margin:24px 0 8px;font-weight:600;color:#6B6560;font-size:13px;text-transform:uppercase;letter-spacing:0.06em;">Message</p>
    <p style="margin:0;color:#1A1714;line-height:1.7;white-space:pre-wrap;">' . $esc($message) . '</p>
    <hr style="margin:28px 0;border:none;border-top:1px solid #E8E2D9;">
    <p style="margin:0;font-size:12px;color:#A09890;">Sent from the contact form at abeywardanadistributors.com</p>
  </td></tr>
</table>
</td></tr></table></body></html>';
}

/**
 * Build the plain-text fallback version of the notification email.
 *
 * @return string
 */
function build_plain_email($name, $email, $phone, $product, $message) {
    $lines = ["New enquiry from the Abeywardana Distributors website", str_repeat('-', 50), ""];
    $lines[] = "Name:             $name";
    $lines[] = "Email:            $email";
    if ($phone)   $lines[] = "Phone:            $phone";
    if ($product) $lines[] = "Product Interest: $product";
    $lines[] = "";
    $lines[] = "Message:";
    $lines[] = $message;
    $lines[] = "";
    $lines[] = str_repeat('-', 50);
    $lines[] = "Sent from abeywardanadistributors.com";
    return implode("\n", $lines);
}
