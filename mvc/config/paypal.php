<?php

return [

    /**
     * ============================================================
     * PAYPAL MODE
     * ============================================================
     * Define qué entorno usa el sistema:
     * - sandbox → PRUEBAS (no dinero real)
     * - live    → PRODUCCIÓN (dinero real)
     *
     * Se controla con la variable de entorno:
     *   PAYPAL_MODE=sandbox  o  PAYPAL_MODE=live
     *
     * Si no existe o es inválido, por defecto será sandbox.
     */
    'mode' => (function () {
        $m = strtolower(trim((string)(getenv('PAYPAL_MODE') ?: 'sandbox')));
        return in_array($m, ['sandbox', 'live'], true) ? $m : 'sandbox';
    })(),


    /**
     * ============================================================
     * RETURN & CANCEL URLS (COMÚN PARA AMBOS)
     * ============================================================
     * PayPal agregará ?subscription_id=... automáticamente.
     *
     * Variables opcionales en .env:
     *  - PAYPAL_RETURN_URL
     *  - PAYPAL_CANCEL_URL
     */
    'return_url' => getenv('PAYPAL_RETURN_URL') ?: 'https://app.zendrhax.com/subcription/return',
    'cancel_url' => getenv('PAYPAL_CANCEL_URL') ?: 'https://app.zendrhax.com/subcription/cancelled',


    // ======================================================================
    //                                LIVE
    // ======================================================================

    /**
     * ============================================================
     * PAYPAL LIVE (PRODUCCIÓN REAL)
     * ============================================================
     * Credenciales reales (cuando vendas en producción).
     *
     * Variables esperadas en tu .env:
     *  - PAYPAL_LIVE_CLIENT_ID
     *  - PAYPAL_LIVE_CLIENT_SECRET
     *
     * base_url correcto para API:
     *  https://api-m.paypal.com
     */
    'live' => [

        'client_id'     => getenv('PAYPAL_LIVE_CLIENT_ID') ?: '',
        'client_secret' => getenv('PAYPAL_LIVE_CLIENT_SECRET') ?: '',
        'base_url'      => 'https://api-m.paypal.com',

        /**
         * ============================================================
         * WEBHOOK ID (LIVE)
         * ============================================================
         * ID del webhook creado en PayPal LIVE.
         *
         * Variable esperada:
         *  - PAYPAL_WEBHOOK_LIVE_ID
         */
        'webhook_id' => getenv('PAYPAL_WEBHOOK_LIVE_ID') ?: '',

        /**
         * ============================================================
         * SUBSCRIPTION PLANS (LIVE)
         * ============================================================
         * Aquí mapeas tus planes internos (pro / business)
         * a los plan_id reales de PayPal LIVE (P-xxxx).
         *
         * Variables esperadas en tu .env:
         *  - PAYPAL_PLAN_LIVE_PRO
         *  - PAYPAL_PLAN_LIVE_BUSINESS
         */
        'plans' => [

            // PRO (1 device)
            'pro' => [
                'paypal_plan_id' => getenv('PAYPAL_PLAN_LIVE_PRO') ?: '',
                'max_devices'    => 1,
                'label'          => 'Subscription Plans - Pro (Annual)',
                'price'          => '59.99',     // ajusta si aplica
                'currency'       => 'USD',
            ],

            // BUSINESS (2 devices)
            'business' => [
                'paypal_plan_id' => getenv('PAYPAL_PLAN_LIVE_BUSINESS') ?: '',
                'max_devices'    => 2,
                'label'          => 'Subscription Plans - Business (Annual)',
                'price'          => '79.99',    // ajusta si aplica
                'currency'       => 'USD',
            ],
        ],
    ],


    // ======================================================================
    //                              SANDBOX
    // ======================================================================

    /**
     * ============================================================
     * PAYPAL SANDBOX (ENTORNO DE PRUEBAS)
     * ============================================================
     * Credenciales de sandbox (no dinero real).
     *
     * Variables esperadas en tu .env:
     *  - PAYPAL_SANDBOX_CLIENT_ID
     *  - PAYPAL_SANDBOX_CLIENT_SECRET
     *
     * base_url correcto para API:
     *  https://api-m.sandbox.paypal.com
     */
    'sandbox' => [

        'client_id'     => getenv('PAYPAL_SANDBOX_CLIENT_ID') ?: '',
        'client_secret' => getenv('PAYPAL_SANDBOX_CLIENT_SECRET') ?: '',
        'base_url'      => 'https://api-m.sandbox.paypal.com',

        /**
         * ============================================================
         * WEBHOOK ID (SANDBOX)
         * ============================================================
         * ID del webhook creado en PayPal SANDBOX.
         *
         * Variable esperada:
         *  - PAYPAL_WEBHOOK_SANDBOX_ID
         */
        'webhook_id' => getenv('PAYPAL_WEBHOOK_SANDBOX_ID') ?: '',

        /**
         * ============================================================
         * SUBSCRIPTION PLANS (SANDBOX)
         * ============================================================
         * Plan IDs de PayPal SANDBOX (son distintos a LIVE).
         *
         * Variables esperadas en tu .env:
         *  - PAYPAL_PLAN_SANDBOX_PRO
         *  - PAYPAL_PLAN_SANDBOX_BUSINESS
         */
        'plans' => [

            // PRO (1 device)
            'pro' => [
                'paypal_plan_id' => getenv('PAYPAL_PLAN_SANDBOX_PRO') ?: '',
                'max_devices'    => 1,
                'label'          => 'Subscription Plans - Pro (Annual)',
                'price'          => '59.99',
                'currency'       => 'USD',
            ],

            // BUSINESS (2 devices)
            'business' => [
                'paypal_plan_id' => getenv('PAYPAL_PLAN_SANDBOX_BUSINESS') ?: '',
                'max_devices'    => 2,
                'label'          => 'Subscription Plans - Business (Annual)',
                'price'          => '79.99',
                'currency'       => 'USD',
            ],
        ],
    ],
];
