<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppAdminConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle Meta's webhook verification challenge (GET).
     * Meta sends hub.mode, hub.verify_token, hub.challenge when you
     * save the webhook URL in the developer dashboard.
     */
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $config = WhatsAppAdminConfig::getConfig();

        if ($mode === 'subscribe' && $token === $config?->verify_token) {
            Log::info('WhatsApp webhook verified successfully.');

            // Return the raw challenge exactly as Meta sent it —
            // plain text, no charset suffix, no JSON wrapping.
            return response()->make($challenge, 200, [
                'Content-Type' => 'text/plain',
            ]);
        }

        Log::warning('WhatsApp webhook verification failed.', [
            'mode'           => $mode,
            'received_token' => $token,
            'expected_token' => $config?->verify_token,
        ]);

        return response()->make('Forbidden', 403, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Receive incoming WhatsApp messages / status updates (POST).
     */
    public function handle(Request $request)
    {
        Log::info('WhatsApp webhook received', ['payload' => $request->all()]);

        // Verify request signature if app_secret is configured
        $config = WhatsAppAdminConfig::getConfig();

        if ($config?->app_secret) {
            $signature = $request->header('X-Hub-Signature-256');
            $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $config->app_secret);

            if (! hash_equals($expected, (string) $signature)) {
                Log::warning('WhatsApp webhook signature mismatch.');
                return response('Unauthorized', 401);
            }
        }

        // Process entries
        $entries = $request->input('entry', []);

        foreach ($entries as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                // Incoming messages
                foreach ($value['messages'] ?? [] as $message) {
                    Log::info('WhatsApp incoming message', [
                        'from' => $message['from'] ?? null,
                        'type' => $message['type'] ?? null,
                        'text' => $message['text']['body'] ?? null,
                    ]);
                    // Future: dispatch a job to handle inbound messages
                }

                // Status updates (sent, delivered, read, failed)
                foreach ($value['statuses'] ?? [] as $status) {
                    Log::info('WhatsApp message status update', [
                        'id'     => $status['id'] ?? null,
                        'status' => $status['status'] ?? null,
                        'to'     => $status['recipient_id'] ?? null,
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
