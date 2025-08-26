<?php

namespace App\Support;

class Placeholder
{
  // Reemplaza {PLACEHOLDER} y {IDX:n}
  public static function apply(string $tpl, array $ctx, int $idx = 1): string
  {
    $base = [
      '{NOMBRE}' => (string)($ctx['NOMBRE'] ?? ''),
      '{NUMERO}' => (string)($ctx['NUMERO'] ?? ''),
      '{TALLE}'  => (string)($ctx['TALLE']  ?? ''),
      '{CATEGORIA}' => (string)($ctx['CATEGORIA'] ?? ''),
      '{YYYY}'   => date('Y'),
      '{MM}'     => date('m'),
      '{DD}'     => date('d'),
    ];
    $s = strtr($tpl, $base);
    $s = preg_replace_callback('/\{IDX(?::(\d+))?\}/', function($m) use ($idx){
      $pad = isset($m[1]) ? (int)$m[1] : 1;
      return str_pad((string)$idx, $pad, '0', STR_PAD_LEFT);
    }, $s);
    return $s;
  }
}
