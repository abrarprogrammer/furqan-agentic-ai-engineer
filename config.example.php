<?php
/**
 * SMTP + recipient configuration — template.
 * Copy this file to config.php (gitignored) and fill in real values.
 * submit.php loads config.php.
 */
return [
    'smtp' => [
        'host'     => 'smtp.example.com',   // e.g. smtp.gmail.com, smtp.mailgun.org
        'port'     => 587,                   // 587 for STARTTLS, 465 for implicit SSL, 25 for plain
        'secure'   => 'tls',                 // 'tls' | 'ssl' | '' (none)
        'username' => 'postmaster@example.com',
        'password' => 'CHANGE_ME',
    ],

    // Envelope / "From" identity used for both emails.
    'from_email' => 'noreply@example.com',
    'from_name'  => 'Furqan Dev',

    // Where the "new lead" notification is delivered.
    'admin_email' => 'furqan@example.com',
    'admin_name'  => 'Furqan',

    // Shown in email footers / used as a fallback EHLO hostname.
    'site_name' => 'furqan.dev',
    'site_url'  => 'https://furqan.dev',

    // reCAPTCHA v3 — get keys at https://www.google.com/recaptcha/admin/create
    // Site key is public and lives in index.html / script.js, not here.
    'recaptcha' => [
        'secret_key' => 'YOUR_RECAPTCHA_V3_SECRET_KEY',
        'action'     => 'contact_form', // must match the action passed in script.js
        'min_score'  => 0.5,            // 0.0 (likely bot) – 1.0 (likely human)
    ],
];
