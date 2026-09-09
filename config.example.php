<?php
/**
 * SMTP + recipient configuration — template.
 * Copy this file to config.local.php and fill in real values.
 * config.local.php is gitignored and is what submit.php actually loads.
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
];
