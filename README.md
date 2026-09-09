# Furqan — Agentic AI Engineer (landing page)

Static landing page with a PHP contact form that sends two SMTP emails on submit.

## Contact form flow

1. `index.html` — `#contactForm` posts via jQuery AJAX (`script.js`) to `submit.php`.
2. `submit.php` — validates input (name, email, length caps, honeypot, header-injection guard)
   and returns JSON `{ success, message }`.
3. On success it sends two HTML emails over SMTP:
   - **to the submitter** — a confirmation with a copy of their message
   - **to the admin** (`admin_email` in `config.php`) — a new-lead notification
   Reply-To is cross-wired so replying to either email reaches the other party.

## Files

| File | Purpose |
|------|---------|
| `config.php` | SMTP credentials + admin/from addresses — **edit before deploying** |
| `submit.php` | Form endpoint (JSON API) |
| `lib/SmtpMailer.php` | Minimal dependency-free SMTP client (STARTTLS / SSL / plain) |
| `lib/email_templates.php` | Table-based HTML emails styled to match the site |

## Setup

Edit `config.php` with your SMTP host, port, `secure` (`tls` / `ssl` / `''`),
username, password, and the `from_email` / `admin_email` addresses.

Requires PHP 8.0+ with `openssl` (for `tls` / `ssl`). No Composer packages.
