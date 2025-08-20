<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!isset($data['colors']) || !is_array($data['colors'])) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'colors array required']);
  exit;
}

$file = __DIR__ . '/palette.json';
file_put_contents($file, json_encode(array_values($data['colors']), JSON_PRETTY_PRINT));

echo json_encode(['ok' => true]);
