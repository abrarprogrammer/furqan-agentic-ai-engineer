<?php
/**
 * Contact form endpoint.
 * Accepts a POST from the landing page (jQuery AJAX), validates it,
 * and sends two HTML emails over SMTP: one to the submitter, one to the admin.
 * Always responds with JSON.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/** Send a JSON response and stop. */
function respond(int $status, bool $success, string $message): void
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'Method not allowed.');
}

// Honeypot — real users never fill this hidden field.
if (!empty($_POST['_hp'] ?? '')) {
    respond(200, true, 'Thanks, I\'ll be in touch shortly.');
}

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

$errors = [];
if ($name === '' || mb_strlen($name) > 120) {
    $errors[] = 'a valid name';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'a valid email address';
}
if (mb_strlen($company) > 200 || mb_strlen($message) > 4000) {
    $errors[] = 'shorter details';
}
// Basic header-injection guard.
if (preg_match('/[\r\n]/', $name . $email . $company)) {
    respond(400, false, 'Invalid input.');
}

if ($errors) {
    respond(422, false, 'Please provide ' . implode(' and ', $errors) . '.');
}

$config = require __DIR__ . '/config.php';
require __DIR__ . '/lib/SmtpMailer.php';
require __DIR__ . '/lib/email_templates.php';

$data = [
    'name'    => $name,
    'email'   => $email,
    'company' => $company,
    'message' => $message,
];

$smtpCfg = $config['smtp'] + [
    'from_email' => $config['from_email'],
    'from_name'  => $config['from_name'],
    'ehlo'       => $config['site_name'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost'),
];

try {
    $mailer = new SmtpMailer($smtpCfg);

    // Email 1 → submitter (reply goes to the admin).
    $mailer->send(
        $email,
        $name,
        'Thanks for reaching out — ' . ($config['from_name'] ?? 'Furqan Dev'),
        build_submitter_email($data, $config),
        $config['admin_email'],
        $config['admin_name'] ?? null
    );

    // Email 2 → admin (reply goes straight to the lead).
    $mailer->send(
        $config['admin_email'],
        $config['admin_name'] ?? $config['admin_email'],
        'New chatbot enquiry from ' . $name,
        build_admin_email($data, $config),
        $email,
        $name
    );
} catch (Throwable $e) {
    error_log('[submit.php] mail failure: ' . $e->getMessage());
    respond(502, false, 'Sorry — the message could not be sent right now. Please email me directly.');
}

respond(200, true, 'Thanks, ' . $name . ' — your message is in. I\'ll reply within one business day.');
