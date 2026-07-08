<?php

namespace App\Http\Controllers\Concerns;

use App\Models\IdempotencyKey;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

trait HandlesIdempotency
{
    /**
     * Executa a acção uma vez por request_id do mesmo user.
     * Sem request_id, corre normalmente (compatível com clientes antigos/mobile).
     */
    protected function withIdempotency(Request $request, string $action, callable $callback): JsonResponse
    {
        $requestId = $request->input('request_id') ?: $request->header('Idempotency-Key');

        if (!$requestId || !is_string($requestId)) {
            return $callback();
        }

        $requestId = substr(trim($requestId), 0, 64);
        if ($requestId === '') {
            return $callback();
        }

        $userId = Auth::id();

        $existing = IdempotencyKey::where('key', $requestId)
            ->where('user_id', $userId)
            ->where('action', $action)
            ->first();

        if ($existing && (int) $existing->status_code !== 202) {
            return response()->json($existing->response_body, $existing->status_code);
        }

        if ($existing && (int) $existing->status_code === 202) {
            // Outro pedido ainda a processar a mesma key
            return response()->json([
                'message' => 'Pedido em processamento. Aguarde e tente novamente.',
            ], 409);
        }

        try {
            IdempotencyKey::create([
                'key' => $requestId,
                'user_id' => $userId,
                'action' => $action,
                'status_code' => 202,
                'response_body' => ['status' => 'processing'],
            ]);
        } catch (QueryException $e) {
            $existing = IdempotencyKey::where('key', $requestId)
                ->where('user_id', $userId)
                ->where('action', $action)
                ->first();

            if ($existing && (int) $existing->status_code !== 202) {
                return response()->json($existing->response_body, $existing->status_code);
            }

            return response()->json([
                'message' => 'Pedido em processamento. Aguarde e tente novamente.',
            ], 409);
        }

        try {
            /** @var JsonResponse $response */
            $response = $callback();
            $status = $response->getStatusCode();
            $body = $response->getData(true);

            if ($status >= 200 && $status < 300) {
                IdempotencyKey::where('key', $requestId)
                    ->where('user_id', $userId)
                    ->where('action', $action)
                    ->update([
                        'status_code' => $status,
                        'response_body' => $body,
                    ]);
            } else {
                // Erro de negócio: liberta a key para permitir retry
                IdempotencyKey::where('key', $requestId)
                    ->where('user_id', $userId)
                    ->where('action', $action)
                    ->delete();
            }

            return $response;
        } catch (Throwable $e) {
            IdempotencyKey::where('key', $requestId)
                ->where('user_id', $userId)
                ->where('action', $action)
                ->delete();

            throw $e;
        }
    }
}
