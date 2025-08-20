<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$file = __DIR__ . '/palette.json';
if (!file_exists($file)) {
  echo json_encode(['colors' => ['#111827','#ffffff','#ef4444','#3b82f6','#10b981','#f59e0b']]);
  exit;
}
echo json_encode(['colors' => json_decode(file_get_contents($file), true)]);
