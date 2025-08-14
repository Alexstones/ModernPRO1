<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    /**
     * Busca el user.id por email usando:
     * 1) GoTrue admin (/auth/v1/admin/users?email=...)
     * 2) Fallback PostgREST a auth.users
     */
    private function fetchUserIdByEmail(string $email): ?string
    {
        $base = rtrim(env('SUPABASE_URL'), '/');

        // 1) GoTrue Admin
        $admin = Http::withHeaders([
            'apikey'        => env('SUPABASE_KEY_SECRET'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY_SECRET'),
        ])->get($base . '/auth/v1/admin/users', ['email' => $email]);

        Log::debug('admin/users status', ['status' => $admin->status()]);
        if ($admin->failed()) {
            Log::warning('admin/users body', ['body' => $admin->body()]);
        }

        if ($admin->successful()) {
            $payload = $admin->json();

            // Puede venir como { users: [...] } o como lista plana [...]
            $list = null;
            if (is_array($payload) && isset($payload['users']) && is_array($payload['users'])) {
                $list = $payload['users'];
            } elseif (is_array($payload) && array_keys($payload) === range(0, count($payload) - 1)) {
                // es un array con índices 0..n (lista)
                $list = $payload;
            }

            if (is_array($list) && count($list) > 0) {
                return data_get($list[0], 'id');
            }
        }

        // 2) Fallback: PostgREST a auth.users
        $rest = Http::withHeaders([
            'apikey'        => env('SUPABASE_KEY_SECRET'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY_SECRET'),
            'Content-Type'  => 'application/json',
        ])->get($base . '/rest/v1/auth.users', [
            'select' => 'id,email',
            'email'  => 'eq.' . $email,
            'limit'  => 1
        ]);

        Log::debug('rest auth.users status', ['status' => $rest->status()]);
        if ($rest->failed()) {
            Log::warning('rest auth.users body', ['body' => $rest->body()]);
        }

        if ($rest->successful()) {
            $rows = $rest->json();
            if (is_array($rows) && count($rows) > 0) {
                return data_get($rows[0], 'id');
            }
        }

        return null;
    }

    /**
     * Registra un usuario en Supabase Auth y hace upsert en la tabla 'users'.
     */
    public function register(Request $request)
    {
        try {
            // 1) Validación
            $validated = $request->validate([
                'nombre'    => 'required|string|max:255',
                'email'     => 'required|email',
                'telefono'  => 'nullable|string|max:20',
                'password'  => 'required|string|min:8',
                'device_id' => 'nullable|string',
            ]);

            $email  = strtolower($validated['email']);
            $userId = null;

            // 2) SignUp en Supabase (con confirmación por email)
            $authUrl = rtrim(env('SUPABASE_URL'), '/') . '/auth/v1/signup';
            $authResponse = Http::withHeaders([
                'apikey'        => env('SUPABASE_KEY_PUBLIC'),
                'Authorization' => 'Bearer ' . env('SUPABASE_KEY_PUBLIC'),
                'Content-Type'  => 'application/json',
            ])->post($authUrl, [
                'email'       => $email,
                'password'    => $validated['password'],
                'data'        => [
                    'name'      => $validated['nombre'],
                    'phone'     => $validated['telefono'] ?? null,
                    'device_id' => $validated['device_id'] ?? null,
                ],
                'redirect_to' => config('app.url') . '/auth/callback',
            ]);

            Log::debug('signup status', ['status' => $authResponse->status()]);
            if ($authResponse->failed()) {
                Log::warning('signup body', ['body' => $authResponse->body()]);
            }

            if ($authResponse->successful()) {
                $userId = data_get($authResponse->json(), 'user.id') ?: $this->fetchUserIdByEmail($email);
            }
            // 429 -> tratar como éxito idempotente (correo ya enviado hace poco)
            else if ($authResponse->status() === 429) {
                $errc = data_get($authResponse->json(), 'error.error_code');
                if ($errc === 'over_email_send_rate_limit') {
                    $userId = $this->fetchUserIdByEmail($email);
                    if (!$userId) {
                        return response()->json([
                            'ok'      => true,
                            'message' => 'Ya enviamos un correo recientemente. Revisa tu bandeja.'
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'where'  => 'auth.signup',
                        'status' => 429,
                        'error'  => $authResponse->json(),
                    ], 429);
                }
            }
            // 400 -> “already registered” u otros
            else if ($authResponse->status() === 400) {
                $msg = strtolower((string)(
                    data_get($authResponse->json(), 'error_description')
                    ?? data_get($authResponse->json(), 'msg')
                    ?? data_get($authResponse->json(), 'message')
                    ?? ''
                ));

                if (str_contains($msg, 'already registered') || str_contains($msg, 'user already exists')) {
                    $userId = $this->fetchUserIdByEmail($email);
                    if (!$userId) {
                        return response()->json([
                            'ok'      => true,
                            'message' => 'Tu correo ya está registrado. Si no ves el correo, revisa spam o inténtalo más tarde.'
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'where'  => 'auth.signup',
                        'status' => 400,
                        'error'  => $authResponse->json(),
                    ], 400);
                }
            }
            // Otros errores
            else {
                return response()->json([
                    'where'  => 'auth.signup',
                    'status' => $authResponse->status(),
                    'error'  => $authResponse->json(),
                ], $authResponse->status());
            }

            // Si a estas alturas no hay userId, algo externo falló
            if (!$userId) {
                Log::error('No se obtuvo userId después de signup y búsquedas', ['email' => $email]);
                return response()->json(['message' => 'Error: no se obtuvo ID de usuario.'], 500);
            }

            // 3) Upsert en tu tabla pública 'users' (via PostgREST)
            $dbUrl = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/users?on_conflict=email';
            $dbResponse = Http::withHeaders([
                'apikey'        => env('SUPABASE_KEY_SECRET'),
                'Authorization' => 'Bearer ' . env('SUPABASE_KEY_SECRET'),
                'Content-Type'  => 'application/json',
                'Prefer'        => 'resolution=merge-duplicates,return=minimal',
            ])->post($dbUrl, [[
                'id'        => $userId,
                'email'     => $email,
                'nombre'    => $validated['nombre'],
                'telefono'  => $validated['telefono'] ?? null,
                'device_id' => $validated['device_id'] ?? null,
            ]]);

            Log::debug('users upsert status', ['status' => $dbResponse->status()]);
            if ($dbResponse->failed()) {
                Log::warning('users upsert body', ['body' => $dbResponse->body()]);
                return response()->json([
                    'where'  => 'db.insert',
                    'status' => $dbResponse->status(),
                    'error'  => $dbResponse->json(),
                ], $dbResponse->status());
            }

            // 4) Éxito (normalizado)
            return response()->json([
                'ok'      => true,
                'message' => 'Te enviamos un correo de confirmación. Revisa tu bandeja.'
            ], 200);

        } catch (Throwable $e) {
            Log::error('Excepción en register', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'message'   => 'Server Error',
                    'exception' => get_class($e),
                    'error'     => $e->getMessage(),
                ], 500);
            }

            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}
