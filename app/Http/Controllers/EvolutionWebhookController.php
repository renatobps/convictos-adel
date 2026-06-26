<?php

namespace App\Http\Controllers;

use App\Services\EnqueteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EvolutionWebhookController extends Controller
{
    public function __construct(
        private readonly EnqueteService $enqueteService,
    ) {}

    public function handle(Request $request, ?string $event = null): JsonResponse
    {
        $payload = $request->all();

        if ($payload === []) {
            Log::info('Evolution webhook vazio ignorado.', [
                'path' => $request->path(),
            ]);

            return response()->json(['ok' => true, 'processed' => false]);
        }

        if ($event !== null && blank($payload['event'] ?? null)) {
            $payload['event'] = Str::upper(str_replace('-', '_', $event));
        }

        $eventoEnquete = $this->isEventoEnqueteRelevante($payload, $event);

        if ($eventoEnquete) {
            Log::info('Evolution webhook recebido.', [
                'event' => $payload['event'] ?? $event,
                'instance' => $payload['instance'] ?? null,
                'path' => $request->path(),
                'message_type' => data_get($payload, 'data.messageType')
                    ?? data_get($payload, 'data.0.messageType'),
                'fromMe' => data_get($payload, 'data.key.fromMe')
                    ?? data_get($payload, 'data.0.key.fromMe'),
                'remoteJid' => data_get($payload, 'data.key.remoteJid')
                    ?? data_get($payload, 'data.0.key.remoteJid'),
                'remoteJidAlt' => data_get($payload, 'data.key.remoteJidAlt')
                    ?? data_get($payload, 'data.0.key.remoteJidAlt'),
                'selected_id' => data_get($payload, 'data.message.templateButtonReplyMessage.selectedId')
                    ?? data_get($payload, 'data.message.buttonsResponseMessage.selectedButtonId')
                    ?? data_get($payload, 'data.templateButtonReplyMessage.selectedId')
                    ?? data_get($payload, 'data.0.message.templateButtonReplyMessage.selectedId'),
            ]);
        }

        $processed = $this->enqueteService->processarWebhookPayload($payload);

        if ($eventoEnquete && ! $processed) {
            Log::info('Evolution webhook recebido mas resposta de enquete nao registrada.', [
                'event' => $payload['event'] ?? $event,
                'path' => $request->path(),
                'message_type' => data_get($payload, 'data.messageType')
                    ?? data_get($payload, 'data.0.messageType'),
            ]);
        }

        return response()->json([
            'ok' => true,
            'processed' => $processed,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function isEventoEnqueteRelevante(array $payload, ?string $event): bool
    {
        $evento = strtolower(str_replace('.', '_', (string) ($payload['event'] ?? $event ?? '')));

        return str_contains($evento, 'messages_upsert')
            || str_contains($evento, 'message_upsert');
    }
}
