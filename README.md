# Furqan — Agentic AI Engineer (landing page)

Static landing page with a PHP contact form (SMTP email + reCAPTCHA v3) and a
live chat widget backed by an n8n webhook.

## Contact form flow

1. `index.html` — `#contactForm` executes reCAPTCHA v3, then posts via jQuery
   AJAX (`script.js`) to `submit.php`.
2. `submit.php` — loads `config.php`, validates input (name, email, length caps,
   honeypot, header-injection guard), verifies the reCAPTCHA token server-side
   (`lib/recaptcha.php`), and returns JSON `{ success, message }`.
3. On success it sends two HTML emails over SMTP:
   - **to the submitter** — a confirmation with a copy of their message
   - **to the admin** (`admin_email` in `config.php`) — a new-lead notification
   Reply-To is cross-wired so replying to either email reaches the other party.

## Chat widget

`chat-widget.js` posts `{ message, sessionId }` to the n8n webhook configured
in that file and renders `{ reply }` in the "Try it yourself" section. The
session id is a `crypto.randomUUID()` persisted in `localStorage`, reused for
every message so the backend keeps conversation memory per visitor.

## Files

| File | Purpose |
|------|---------|
| `config.example.php` | Template config — copy to `config.php` |
| `config.php` | Real SMTP + reCAPTCHA credentials (gitignored) |
| `submit.php` | Contact form endpoint (JSON API) |
| `lib/SmtpMailer.php` | Minimal dependency-free SMTP client (STARTTLS / SSL / plain) |
| `lib/email_templates.php` | Table-based HTML emails styled to match the site |
| `lib/recaptcha.php` | Server-side reCAPTCHA v3 `siteverify` check |
| `chat-widget.js` | Live chat widget for the demo section |

## Setup

```
cp config.example.php config.php
```

Edit `config.php` with:
- SMTP host, port, `secure` (`tls` / `ssl` / `''`), username, password
- `from_email` / `admin_email` addresses
- `recaptcha.secret_key` — from [google.com/recaptcha/admin](https://www.google.com/recaptcha/admin/create) (reCAPTCHA **v3**, register your domain)

`config.php` is gitignored; `submit.php` loads it.

The reCAPTCHA **site key** is public and lives client-side in two places —
keep them in sync:
- `index.html` — `<script src="https://www.google.com/recaptcha/api.js?render=SITE_KEY">`
- `script.js` — `RECAPTCHA_SITE_KEY` constant

Requires PHP 8.0+ with `openssl` (for SMTP `tls` / `ssl`) and `allow_url_fopen`
(for the reCAPTCHA `siteverify` call). No Composer packages.
