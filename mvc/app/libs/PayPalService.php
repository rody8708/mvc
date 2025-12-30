<?php
// app/lib/PayPalService.php

namespace App\Libs;

use Exception;

class PayPalService
{
    /**
     * Full config array (paypal.php)
     * Includes: mode, return_url, cancel_url, and env blocks (live/sandbox)
     */
    private array $cfg;

    /**
     * Active environment block (either $cfg['sandbox'] or $cfg['live'])
     * This block contains: client_id, client_secret, base_url, webhook_id, plans
     */
    private array $env;

    public function __construct()
    {
        $this->cfg = require __DIR__ . '/../../config/paypal.php';

        // Determine mode safely
        $mode = $this->cfg['mode'] ?? 'sandbox';
        $mode = is_string($mode) ? strtolower(trim($mode)) : 'sandbox';

        if (!in_array($mode, ['sandbox', 'live'], true)) {
            $mode = 'sandbox';
        }

        // Load env block (sandbox/live)
        if (!isset($this->cfg[$mode]) || !is_array($this->cfg[$mode])) {
            throw new Exception("PayPal config missing environment block: {$mode}");
        }

        $this->env = $this->cfg[$mode];

        // Basic validation (optional but helpful)
        if (empty($this->env['client_id']) || empty($this->env['client_secret']) || empty($this->env['base_url'])) {
            // Do NOT throw if you want to allow boot even without paypal configured.
            // But for payment endpoints, this should be configured.
            // Throwing here makes misconfig obvious early.
            // Comment this out if you prefer silent behavior.
            // throw new Exception("PayPal {$mode} credentials are not configured.");
        }
    }

    public function getMode(): string
    {
        $mode = $this->cfg['mode'] ?? 'sandbox';
        $mode = is_string($mode) ? strtolower(trim($mode)) : 'sandbox';
        return in_array($mode, ['sandbox', 'live'], true) ? $mode : 'sandbox';
    }

    /**
     * Returns plan configuration for current mode (sandbox/live).
     *
     * IMPORTANT: Plans are stored under the environment block now:
     *   $this->env['plans']['pro']
     *   $this->env['plans']['business']
     */
    public function getPlanConfig(string $slug): array
    {
        $slug = strtolower(trim($slug));

        if (empty($this->env['plans']) || !is_array($this->env['plans'])) {
            throw new Exception('PayPal plans are not configured for current mode.');
        }

        if (!isset($this->env['plans'][$slug]) || !is_array($this->env['plans'][$slug])) {
            throw new Exception('Invalid plan slug.');
        }

        return $this->env['plans'][$slug];
    }

    public function getAccessToken(): string
    {
        $url = $this->env['base_url'] . '/v1/oauth2/token';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: en_US',
            ],
            CURLOPT_USERPWD => ($this->env['client_id'] ?? '') . ':' . ($this->env['client_secret'] ?? ''),
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('PayPal token request failed: ' . $err);
        }
        curl_close($ch);

        $json = json_decode($raw, true);
        if ($code < 200 || $code >= 300 || empty($json['access_token'])) {
            throw new Exception('PayPal token error: ' . $raw);
        }

        return (string)$json['access_token'];
    }

    public function createSubscription(string $planId, string $customId): array
    {
        $token = $this->getAccessToken();
        $url   = $this->env['base_url'] . '/v1/billing/subscriptions';

        $payload = [
            'plan_id' => $planId,
            'application_context' => [
                'brand_name' => 'Zendrhax Invoices',
                'locale' => 'en-US',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',

                // These are common (not per env)
                // PayPal will append ?subscription_id=... automatically on return
                'return_url' => $this->cfg['return_url'] ?? '',
                'cancel_url' => $this->cfg['cancel_url'] ?? '',
            ],
            'custom_id' => $customId,
        ];

        return $this->postJson($url, $token, $payload);
    }

    public function getSubscription(string $subscriptionId): array
    {
        $token = $this->getAccessToken();
        $url   = $this->env['base_url'] . '/v1/billing/subscriptions/' . urlencode($subscriptionId);

        return $this->getJson($url, $token);
    }

    /**
     * Verifies the PayPal webhook signature for the current mode.
     *
     * IMPORTANT: webhook_id is now per environment:
     *   $this->env['webhook_id']
     */
    public function verifyWebhookSignature(array $headers, string $rawBody): bool
    {
        $webhookId = (string)($this->env['webhook_id'] ?? '');
        if (trim($webhookId) === '') return false;

        $token = $this->getAccessToken();
        $url   = $this->env['base_url'] . '/v1/notifications/verify-webhook-signature';

        $payload = [
            'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? '',
            'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? '',
            'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
            'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
            'webhook_id'        => $webhookId,
            'webhook_event'     => json_decode($rawBody, true),
        ];

        $resp = $this->postJson($url, $token, $payload);

        return isset($resp['verification_status']) && $resp['verification_status'] === 'SUCCESS';
    }

    private function postJson(string $url, string $token, array $payload): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('PayPal request failed: ' . $err);
        }

        curl_close($ch);

        // PayPal cancel can return 204 No Content
        if ($code === 204) {
            return [
                'success'    => true,
                'http_code'  => 204,
                'no_content' => true,
            ];
        }

        $json = null;
        if (is_string($raw) && trim($raw) !== '') {
            $json = json_decode($raw, true);
        }

        if ($code < 200 || $code >= 300) {
            throw new Exception('PayPal API error: ' . $raw);
        }

        if (is_array($json)) {
            return $json;
        }

        return [
            'success'   => true,
            'http_code' => $code,
            'raw'       => $raw,
        ];
    }

    private function getJson(string $url, string $token): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('PayPal request failed: ' . $err);
        }
        curl_close($ch);

        $json = json_decode($raw, true);
        if ($code < 200 || $code >= 300 || !is_array($json)) {
            throw new Exception('PayPal API error: ' . $raw);
        }

        return $json;
    }

    public function cancelSubscription(string $subscriptionId, string $reason = 'User requested cancellation'): array
    {
        $token = $this->getAccessToken();

        $url = $this->env['base_url']
            . '/v1/billing/subscriptions/'
            . urlencode($subscriptionId)
            . '/cancel';

        $payload = [];
        if ($reason !== '') {
            $payload['reason'] = $reason;
        }

        return $this->postJson($url, $token, $payload);
    }
}
