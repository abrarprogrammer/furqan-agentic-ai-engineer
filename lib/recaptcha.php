<?php
/**
 * Server-side verification for Google reCAPTCHA v3.
 * https://developers.google.com/recaptcha/docs/v3
 */

const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

/**
 * Verify a v3 token against Google's siteverify endpoint.
 *
 * @param string $token    The client-side token (g-recaptcha-response equivalent).
 * @param string $remoteIp Visitor IP, for Google's own abuse signals.
 * @param array  $cfg      config['recaptcha'] — expects 'secret_key', optional 'min_score', 'action'.
 * @return array{ok: bool, score: float, reason: string}
 */
function verify_recaptcha(string $token, string $remoteIp, array $cfg): array
{
    $secret       = $cfg['secret_key'] ?? '';
    $expectAction = $cfg['action'] ?? 'contact_form';
    $minScore     = (float) ($cfg['min_score'] ?? 0.5);

    if ($token === '') {
        return ['ok' => false, 'score' => 0.0, 'reason' => 'missing_token'];
    }
    if ($secret === '' || strpos($secret, 'YOUR_RECAPTCHA') === 0) {
        // Not configured yet — fail closed so a half-set-up deploy doesn't silently
        // accept everything, but log clearly so it's obvious what to fix.
        error_log('[recaptcha] secret_key is not configured in config.local.php / config.php');
        return ['ok' => false, 'score' => 0.0, 'reason' => 'not_configured'];
    }

    $postFields = http_build_query([
        'secret'   => $secret,
        'response' => $token,
        'remoteip' => $remoteIp,
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $postFields,
            'timeout' => 10,
            'ignore_errors' => true,
        ],
    ]);

    $raw = @file_get_contents(RECAPTCHA_VERIFY_URL, false, $ctx);
    if ($raw === false) {
        return ['ok' => false, 'score' => 0.0, 'reason' => 'network_error'];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'score' => 0.0, 'reason' => 'malformed_response'];
    }

    $success = (bool) ($data['success'] ?? false);
    $score   = isset($data['score']) ? (float) $data['score'] : 0.0;
    $action  = (string) ($data['action'] ?? '');

    if (!$success) {
        return ['ok' => false, 'score' => $score, 'reason' => 'google_rejected:' . implode(',', $data['error-codes'] ?? [])];
    }
    if ($expectAction !== '' && $action !== $expectAction) {
        return ['ok' => false, 'score' => $score, 'reason' => "action_mismatch:{$action}"];
    }
    if ($score < $minScore) {
        return ['ok' => false, 'score' => $score, 'reason' => 'low_score'];
    }

    return ['ok' => true, 'score' => $score, 'reason' => 'ok'];
}
