<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    private string $jsonPath = 'comments.json';

    private function load(): array {
        if (!Storage::exists($this->jsonPath)) return [];
        $data = json_decode(Storage::get($this->jsonPath), true);
        return is_array($data) ? $data : [];
    }
    private function save(array $items): void {
        Storage::put($this->jsonPath, json_encode($items, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    // helpers de token (igual que en AuthController)
    private function b64url_decode($s){ return base64_decode(strtr($s, '-_', '+/')); }
    private function sign(string $data): string {
        $secret = config('app.key');
        if (str_starts_with($secret, 'base64:')) $secret = base64_decode(substr($secret, 7));
        return rtrim(strtr(base64_encode(hash_hmac('sha256', $data, $secret, true)), '+/', '-_'), '=');
    }
    private function decodeToken(?string $token): ?array {
        if (!$token || !str_contains($token, '.')) return null;
        [$h,$p,$s] = explode('.', $token, 3);
        if (!hash_equals($this->sign("$h.$p"), $s)) return null;
        $claims = json_decode($this->b64url_decode($p), true);
        if (!is_array($claims)) return null;
        if (isset($claims['exp']) && time() >= $claims['exp']) return null;
        return $claims;
    }
    private function bearer(Request $r): ?string {
        $h = $r->header('Authorization');
        return ($h && str_starts_with($h, 'Bearer ')) ? substr($h, 7) : null;
    }

    public function index() {
        $items = $this->load();
        usort($items, fn($a,$b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return response()->json($items);
    }

    public function store(Request $request) {
        $claims = $this->decodeToken($this->bearer($request));
        if (!$claims) return response()->json(['message'=>'No autenticado'], 401);
        if (empty($claims['paying'])) return response()->json(['message'=>'Solo usuarios premium pueden comentar'], 403);

        $data = $request->validate(['text' => ['required','string','max:2000']]);

        $items = $this->load();
        $item = [
            'id'         => uniqid('', true),
            'user'       => $claims['name'] ?? $claims['sub'],
            'text'       => $data['text'],
            'created_at' => now()->toISOString(),
        ];
        $items[] = $item;
        $this->save($items);

        return response()->json($item, 201);
    }
}
