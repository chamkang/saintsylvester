<?php
/**
 * Minimal transactional email via the Resend HTTP API (https://resend.com).
 *
 * Chosen because the site runs on Vercel (serverless), where PHP's mail() does
 * not work — this is a plain HTTPS call, like the Fapshi payment integration.
 * No-ops safely when RESEND_API_KEY is empty, so a missing/incorrect key can
 * never break a booking. Returns true on a 2xx response.
 */
function send_email(string $to, string $subject, string $html): bool
{
    if (! defined('RESEND_API_KEY') || RESEND_API_KEY === '') {
        return false; // email not configured yet — silently skip
    }

    $payload = json_encode([
        'from'    => MAIL_FROM,
        'to'      => [$to],
        'subject' => $subject,
        'html'    => $html,
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json',
        ],
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $code >= 200 && $code < 300;
}
