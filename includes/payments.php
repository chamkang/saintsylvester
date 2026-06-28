<?php
require_once __DIR__ . '/db.php';

/**
 * Payment layer for the consultation fee.
 *
 * Two providers behind one interface:
 *   - 'sandbox' : simulated MoMo, no real money — lets the whole flow be tested now.
 *   - 'fapshi'  : live MTN MoMo / Orange Money (Cameroon). Needs FAPSHI_* keys + HTTPS.
 *
 * Flow: booking is created `unpaid` (holding its slot), patient is sent to pay.php,
 * provider->initiate() starts the charge, and on confirmation payment_mark_paid()
 * flips the appointment to paid. Abandoned unpaid bookings are released by
 * sweep_expired_holds() after PAYMENT_HOLD_MINUTES.
 */

interface PaymentProvider {
    /** Begin payment. Return ['mode'=>'inline'] (pay on our page) or ['mode'=>'redirect','url'=>...]. */
    public function initiate(array $appt, string $method): array;
    /** Re-check a payment's state with the provider: 'success' | 'pending' | 'failed'. */
    public function verify(string $providerRef): string;
}

class SandboxProvider implements PaymentProvider {
    public function initiate(array $appt, string $method): array {
        // nothing to call — pay.php renders simulate buttons
        return ['mode' => 'inline'];
    }
    public function verify(string $providerRef): string {
        return 'success';
    }
}

class FapshiProvider implements PaymentProvider {
    public function initiate(array $appt, string $method): array {
        if (!FAPSHI_API_USER || !FAPSHI_API_KEY) {
            throw new RuntimeException('Fapshi keys not configured');
        }
        $payload = [
            'amount'      => (int)$appt['amount'],
            'externalId'  => $appt['reference'],
            'redirectUrl' => base_url('pay.php?ref=' . urlencode($appt['reference']) . '&t=' . urlencode($appt['pay_token'])),
            'message'     => 'Frais de consultation ' . CLINIC_NAME,
        ];
        $res = $this->call('POST', '/initiate-pay', $payload);
        // Fapshi returns { link, transId }
        db()->prepare("UPDATE payments SET provider_ref = ?, raw = ? WHERE appointment_id = ? AND status = 'pending'")
            ->execute([$res['transId'] ?? null, json_encode($res), $appt['id']]);
        return ['mode' => 'redirect', 'url' => $res['link'] ?? base_url('pay.php')];
    }
    public function verify(string $providerRef): string {
        $res = $this->call('GET', '/payment-status/' . rawurlencode($providerRef));
        return match (strtoupper($res['status'] ?? '')) {
            'SUCCESSFUL' => 'success',
            'FAILED', 'EXPIRED' => 'failed',
            default => 'pending',
        };
    }
    private function call(string $method, string $path, array $body = []): array {
        $ch = curl_init(FAPSHI_BASE . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'apiuser: ' . FAPSHI_API_USER, 'apikey: ' . FAPSHI_API_KEY],
            CURLOPT_POSTFIELDS => $body ? json_encode($body) : null,
            CURLOPT_TIMEOUT => 30,
        ]);
        $out = curl_exec($ch);
        curl_close($ch);
        return json_decode((string)$out, true) ?: [];
    }
}

function payment_provider(): PaymentProvider {
    return PAYMENT_PROVIDER === 'fapshi' ? new FapshiProvider() : new SandboxProvider();
}

/** Best-effort absolute URL for redirect/callback URLs. */
function base_url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    // pay/api scripts live one level deep for api/, root for pay.php — normalise to app root
    $dir = preg_replace('#/api$#', '', $dir);
    return $scheme . '://' . $host . $dir . '/' . ltrim($path, '/');
}

/** Insert a pending payment row for an appointment. */
function payment_open(int $appointmentId, int $amount, string $method): int {
    db()->prepare("INSERT INTO payments (appointment_id, provider, amount, currency, status, method)
                   VALUES (?,?,?,?, 'pending', ?)")
        ->execute([$appointmentId, PAYMENT_PROVIDER, $amount, CONSULTATION_CURRENCY, $method]);
    return (int)db()->lastInsertId();
}

/** Mark an appointment (and its open payment) as paid. */
function payment_mark_paid(int $appointmentId, ?string $providerRef = null, ?string $method = null): void {
    $now = date('Y-m-d H:i:s');
    $pdo = db();
    $pdo->prepare("UPDATE payments SET status = 'success', provider_ref = COALESCE(?, provider_ref),
                   method = COALESCE(?, method), updated_at = ? WHERE appointment_id = ? AND status = 'pending'")
        ->execute([$providerRef, $method, $now, $appointmentId]);
    $pdo->prepare("UPDATE appointments SET payment_status = 'paid', paid_at = ?, updated_at = ? WHERE id = ?")
        ->execute([$now, $now, $appointmentId]);
}

/**
 * Release slots held by unpaid bookings that were never paid within the hold window.
 * Keeps the unique slot index consistent with what patients actually see.
 */
function sweep_expired_holds(): void {
    $cutoff = date('Y-m-d H:i:s', time() - PAYMENT_HOLD_MINUTES * 60);
    db()->prepare("UPDATE appointments SET status = 'cancelled', cancel_reason = 'payment_timeout', updated_at = ?
                   WHERE status = 'pending' AND payment_status = 'unpaid' AND created_at < ?")
        ->execute([date('Y-m-d H:i:s'), $cutoff]);
}

function consultation_fee(): int {
    return CONSULTATION_FEE;
}

/** Fee for a specific service (by slug) — falls back to the fixed default. */
function consultation_fee_for(?string $slug): int {
    return CONSULTATION_FEES[$slug] ?? CONSULTATION_FEE;
}

function money(int $amount): string {
    return number_format($amount, 0, ',', ' ') . ' ' . CONSULTATION_CURRENCY;
}
