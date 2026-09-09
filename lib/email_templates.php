<?php
/**
 * HTML email templates — tabular layout (<table>/<tr>/<td>) with inline styles,
 * mirroring the landing page's dark UI and green accent.
 */

/** Palette approximated from the site's oklch tokens, as email-safe hex. */
const EMAIL_COLORS = [
    'bg'         => '#17181b',
    'bg_raised'  => '#212328',
    'bg_raised2' => '#282a30',
    'border'     => '#3b3d43',
    'text'       => '#f0f0f1',
    'text_dim'   => '#a6a7ab',
    'text_faint' => '#75767a',
    'accent'     => '#3ddc97',
    'accent_ink' => '#0c2a1c',
];

/**
 * Wrap body content in the shared email shell.
 *
 * @param string $preheader Hidden inbox-preview text.
 * @param string $kicker    Mono eyebrow line (e.g. "// new lead").
 * @param string $heading   Main heading.
 * @param string $introHtml One or more <p> paragraphs (already escaped/safe HTML).
 * @param string $tableRows Concatenated <tr>...</tr> markup for the detail table.
 * @param array  $config    App config (for footer site name/url).
 */
function email_shell(string $preheader, string $kicker, string $heading, string $introHtml, string $tableRows, array $config): string
{
    $c = EMAIL_COLORS;
    $siteName = htmlspecialchars($config['site_name'] ?? 'this site', ENT_QUOTES);
    $siteUrl  = htmlspecialchars($config['site_url'] ?? '#', ENT_QUOTES);
    $year     = date('Y');
    $pre      = htmlspecialchars($preheader, ENT_QUOTES);
    $kickerEsc = htmlspecialchars($kicker, ENT_QUOTES);
    $headingEsc = htmlspecialchars($heading, ENT_QUOTES);

    $mono = "font-family:'JetBrains Mono',ui-monospace,SFMono-Regular,Menlo,Consolas,monospace";
    $sans = "font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$headingEsc}</title>
</head>
<body style="margin:0;padding:0;background:{$c['bg']};">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{$pre}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{$c['bg']};padding:32px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="width:560px;max-width:100%;background:{$c['bg_raised']};border:1px solid {$c['border']};border-radius:8px;overflow:hidden;">

        <tr>
          <td style="padding:28px 32px 0 32px;">
            <p style="margin:0;{$mono};font-size:18px;font-weight:700;color:{$c['text']};">furqan<span style="color:{$c['accent']};">.</span></p>
          </td>
        </tr>

        <tr>
          <td style="padding:20px 32px 0 32px;">
            <p style="margin:0 0 10px 0;{$mono};font-size:12px;letter-spacing:0.04em;color:{$c['accent']};">{$kickerEsc}</p>
            <h1 style="margin:0;{$sans};font-size:22px;line-height:1.3;font-weight:700;color:{$c['text']};">{$headingEsc}</h1>
          </td>
        </tr>

        <tr>
          <td style="padding:16px 32px 4px 32px;{$sans};font-size:15px;line-height:1.6;color:{$c['text_dim']};">
            {$introHtml}
          </td>
        </tr>

        <tr>
          <td style="padding:16px 32px 8px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{$c['bg_raised2']};border:1px solid {$c['border']};border-radius:6px;">
              {$tableRows}
            </table>
          </td>
        </tr>

        <tr>
          <td style="padding:20px 32px 30px 32px;border-top:1px solid {$c['border']};">
            <p style="margin:0;{$mono};font-size:12px;line-height:1.6;color:{$c['text_faint']};">
              Sent from <a href="{$siteUrl}" style="color:{$c['text_faint']};text-decoration:underline;">{$siteName}</a> &middot; {$year}
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
}

/** Render a single label / value row for the detail table. */
function email_row(string $label, string $value, bool $first = false): string
{
    $c = EMAIL_COLORS;
    $mono = "font-family:'JetBrains Mono',ui-monospace,SFMono-Regular,Menlo,Consolas,monospace";
    $sans = "font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
    $borderTop = $first ? 'none' : "1px solid {$c['border']}";

    $labelEsc = htmlspecialchars($label, ENT_QUOTES);
    $valueEsc = nl2br(htmlspecialchars($value, ENT_QUOTES));

    return <<<HTML
<tr>
  <td style="padding:13px 16px;border-top:{$borderTop};{$mono};font-size:11px;text-transform:uppercase;letter-spacing:0.04em;color:{$c['accent']};vertical-align:top;white-space:nowrap;">{$labelEsc}</td>
  <td style="padding:13px 16px;border-top:{$borderTop};{$sans};font-size:14px;line-height:1.55;color:{$c['text']};">{$valueEsc}</td>
</tr>
HTML;
}

/**
 * Email #1 — confirmation to the person who submitted the form.
 */
function build_submitter_email(array $data, array $config): string
{
    $c = EMAIL_COLORS;
    $name = htmlspecialchars($data['name'], ENT_QUOTES);

    $intro = "<p style=\"margin:0 0 12px 0;\">Hi {$name},</p>"
        . "<p style=\"margin:0 0 12px 0;\">Thanks for reaching out about a RAG chatbot. I've received your message and will get back to you personally within one business day.</p>"
        . "<p style=\"margin:0 0 4px 0;\">Here's a copy of what you sent:</p>";

    $rows = email_row('Name', $data['name'], true)
        . email_row('Email', $data['email']);
    if ($data['company'] !== '') {
        $rows .= email_row('Company', $data['company']);
    }
    if ($data['message'] !== '') {
        $rows .= email_row('Message', $data['message']);
    }

    return email_shell(
        'Thanks — I\'ve received your message and will reply within one business day.',
        '// message received',
        'Thanks, I\'ll be in touch shortly',
        $intro,
        $rows,
        $config
    );
}

/**
 * Email #2 — new-lead notification to the site admin.
 */
function build_admin_email(array $data, array $config): string
{
    $meta = trim(($_SERVER['REMOTE_ADDR'] ?? '') . ' · ' . ($_SERVER['HTTP_USER_AGENT'] ?? ''), ' ·');

    $intro = "<p style=\"margin:0 0 4px 0;\">A new enquiry just came in through the landing page contact form.</p>";

    $rows = email_row('Name', $data['name'], true)
        . email_row('Email', $data['email'])
        . email_row('Company', $data['company'] !== '' ? $data['company'] : '—')
        . email_row('Message', $data['message'] !== '' ? $data['message'] : '—')
        . email_row('Received', date('r'))
        . email_row('Source', $meta !== '' ? $meta : '—');

    return email_shell(
        'New lead: ' . $data['name'] . ' <' . $data['email'] . '>',
        '// new lead',
        'New chatbot enquiry',
        $intro,
        $rows,
        $config
    );
}
