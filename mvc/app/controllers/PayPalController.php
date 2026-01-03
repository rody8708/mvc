<?php

namespace App\Controllers;

use App\Libs\PayPalService;
use Exception;

class PayPalController extends Controller
{
    private PayPalService $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalService();
    }

    public function createSubscription()
    {
        try {
            $planId = filter_input(INPUT_POST, 'plan_id', FILTER_SANITIZE_STRING);
            $customId = filter_input(INPUT_POST, 'custom_id', FILTER_SANITIZE_STRING);

            if (empty($planId) || empty($customId)) {
                throw new Exception('Plan ID and Custom ID are required.');
            }

            $subscription = $this->paypal->createSubscription($planId, $customId);

            // Redirect user to PayPal approval URL
            header('Location: ' . $subscription['links'][1]['href']);
            exit;
        } catch (Exception $e) {
            $this->setFlashAlert('error', $e->getMessage());
            $this->loadView('error', ['message' => $e->getMessage()]);
        }
    }

    public function handleReturn()
    {
        $subscriptionId = filter_input(INPUT_GET, 'subscription_id', FILTER_SANITIZE_STRING);

        if (empty($subscriptionId)) {
            $this->setFlashAlert('error', 'Subscription ID is missing.');
            $this->loadView('error', ['message' => 'Subscription ID is missing.']);
            return;
        }

        try {
            $subscription = $this->paypal->getSubscription($subscriptionId);
            $this->loadView('paypal/success', ['subscription' => $subscription]);
        } catch (Exception $e) {
            $this->setFlashAlert('error', $e->getMessage());
            $this->loadView('error', ['message' => $e->getMessage()]);
        }
    }

    public function handleCancel()
    {
        $this->setFlashAlert('info', 'Subscription process was canceled.');
        $this->loadView('paypal/cancel');
    }

    public function handleWebhook()
    {
        try {
            // Read raw POST data
            $rawBody = file_get_contents('php://input');
            $headers = getallheaders();

            // Verify webhook signature
            $isValid = $this->paypal->verifyWebhookSignature($headers, $rawBody);

            if (!$isValid) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid webhook signature.']);
                return;
            }

            // Decode the webhook event
            $event = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
                return;
            }

            // Handle specific event types
            switch ($event['event_type']) {
                case 'BILLING.SUBSCRIPTION.CREATED':
                    $this->handleSubscriptionCreated($event);
                    break;

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    $this->handleSubscriptionCancelled($event);
                    break;

                default:
                    // Log unhandled events
                    Logger::info('Unhandled PayPal webhook event: ' . $event['event_type']);
                    break;
            }

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Webhook handled successfully.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function handleSubscriptionCreated(array $event)
    {
        $subscriptionId = $event['resource']['id'] ?? '';
        $email = $event['resource']['subscriber']['email_address'] ?? '';

        if ($subscriptionId && $email) {
            // Send confirmation email
            Mailer::sendSubscriptionConfirmation($email, $subscriptionId);
            Logger::info("Subscription created: $subscriptionId for $email");
        }
    }

    private function handleSubscriptionCancelled(array $event)
    {
        $subscriptionId = $event['resource']['id'] ?? '';
        $email = $event['resource']['subscriber']['email_address'] ?? '';

        if ($subscriptionId && $email) {
            // Send cancellation email
            Mailer::sendSubscriptionCancellation($email, $subscriptionId);
            Logger::info("Subscription cancelled: $subscriptionId for $email");
        }
    }
}