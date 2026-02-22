<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QzTraySigningController extends Controller
{
    public function certificate(): JsonResponse
    {
        $certificate = $this->normalizePem((string) config('services.qz_tray.certificate'));

        if ($certificate === '') {
            return response()->json([
                'message' => 'QZ certificate belum dikonfigurasi.',
            ], 422);
        }

        return response()->json([
            'data' => $certificate,
        ]);
    }

    public function sign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request' => ['required', 'string'],
        ]);

        $privateKeyPem = $this->normalizePem((string) config('services.qz_tray.private_key'));

        if ($privateKeyPem === '') {
            return response()->json([
                'message' => 'QZ private key belum dikonfigurasi.',
            ], 422);
        }

        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if (! $privateKey) {
            return response()->json([
                'message' => 'Format QZ private key tidak valid.',
            ], 422);
        }

        $signature = '';
        $signed = openssl_sign($validated['request'], $signature, $privateKey, OPENSSL_ALGO_SHA512);
        openssl_free_key($privateKey);

        if (! $signed) {
            return response()->json([
                'message' => 'Gagal melakukan signature request QZ.',
            ], 500);
        }

        return response()->json([
            'data' => base64_encode($signature),
        ]);
    }

    protected function normalizePem(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        return str_replace('\n', "\n", $trimmed);
    }
}

