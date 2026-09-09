<?php
/**
 * Minimal SMTP mailer — no dependencies.
 * Supports STARTTLS ("tls"), implicit SSL ("ssl") or plain ("").
 * Sends a single HTML message per call.
 */
class SmtpMailer
{
    /** @var array */
    private $cfg;

    /**
     * @param array $cfg host, port, secure, username, password, from_email, from_name, ehlo (optional)
     */
    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * @throws Exception on any protocol / connection error
     */
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        $host   = $this->cfg['host'];
        $port   = (int) $this->cfg['port'];
        $secure = strtolower((string) ($this->cfg['secure'] ?? ''));
        $user   = (string) ($this->cfg['username'] ?? '');
        $pass   = (string) ($this->cfg['password'] ?? '');
        $ehlo   = $this->cfg['ehlo'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');

        $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;

        $ctx = stream_context_create([
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true],
        ]);

        $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            throw new Exception("SMTP connection failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($fp, 20);

        try {
            $this->expect($fp, 220);
            $this->cmd($fp, "EHLO {$ehlo}", 250);

            if ($secure === 'tls') {
                $this->cmd($fp, 'STARTTLS', 220);
                $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                    $crypto |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                }
                if (!stream_socket_enable_crypto($fp, true, $crypto)) {
                    throw new Exception('STARTTLS negotiation failed');
                }
                $this->cmd($fp, "EHLO {$ehlo}", 250);
            }

            if ($user !== '') {
                $this->cmd($fp, 'AUTH LOGIN', 334);
                $this->cmd($fp, base64_encode($user), 334);
                $this->cmd($fp, base64_encode($pass), 235);
            }

            $from = $this->cfg['from_email'];
            $this->cmd($fp, "MAIL FROM:<{$from}>", 250);
            $this->cmd($fp, "RCPT TO:<{$toEmail}>", [250, 251]);
            $this->cmd($fp, 'DATA', 354);

            $message = $this->buildMessage($toEmail, $toName, $subject, $htmlBody, $replyToEmail, $replyToName);
            $this->cmd($fp, $message . "\r\n.", 250);

            $this->cmd($fp, 'QUIT', 221);
        } finally {
            fclose($fp);
        }

        return true;
    }

    private function buildMessage(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $replyToEmail,
        ?string $replyToName
    ): string {
        $fromEmail = $this->cfg['from_email'];
        $fromName  = $this->cfg['from_name'] ?? $fromEmail;

        $headers = [
            'Date: ' . date('r'),
            'From: ' . $this->formatAddress($fromEmail, $fromName),
            'To: ' . $this->formatAddress($toEmail, $toName),
            'Subject: ' . $this->encodeHeader($subject),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . ($this->cfg['ehlo'] ?? 'localhost') . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        if ($replyToEmail) {
            $headers[] = 'Reply-To: ' . $this->formatAddress($replyToEmail, $replyToName ?: $replyToEmail);
        }

        // RFC 5321 dot-stuffing
        $body = preg_replace('/^\./m', '..', str_replace("\n", "\r\n", str_replace("\r\n", "\n", $htmlBody)));

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function formatAddress(string $email, string $name): string
    {
        $name = trim($name);
        if ($name === '' || $name === $email) {
            return "<{$email}>";
        }
        return $this->encodeHeader($name, true) . " <{$email}>";
    }

    private function encodeHeader(string $value, bool $isPhrase = false): string
    {
        if (preg_match('/[^\x20-\x7E]/', $value)) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }
        if ($isPhrase && preg_match('/[()<>@,;:\\".\[\]]/', $value)) {
            return '"' . addcslashes($value, '"\\') . '"';
        }
        return $value;
    }

    /** Send a command and assert the reply code. */
    private function cmd($fp, string $cmd, $expected): string
    {
        fwrite($fp, $cmd . "\r\n");
        return $this->expect($fp, $expected, $cmd);
    }

    /** Read a (possibly multiline) reply and assert its code. */
    private function expect($fp, $expected, string $cmd = ''): string
    {
        $response = '';
        while (($line = fgets($fp, 515)) !== false) {
            $response .= $line;
            // Continuation lines look like "250-...", the final line "250 ..."
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        $ok   = array_map('intval', (array) $expected);

        if (!in_array($code, $ok, true)) {
            $label = $cmd === '' ? 'greeting' : "'" . strtok($cmd, "\r\n") . "'";
            throw new Exception("Unexpected SMTP reply to {$label}: " . trim($response));
        }

        return $response;
    }
}
