<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $title ?? 'Personalización' }}</title>
<style>
  @page { size: A4; margin: {{ $margin ?? 10 }}mm; }
  body  { font-family: DejaVu Sans, sans-serif; color: #111827; }
  .wrap { position: relative; width: 100%; min-height: 240mm; }
  .nombre { font-size: 32pt; font-weight:700; }
  .numero { font-size: 82pt; font-weight:900; -webkit-text-stroke: 3pt #000; color:#fff; line-height: 1; }
  .meta   { margin-top: 6mm; font-size: 12pt; }
  .tag    { display:inline-block; background:#111827; color:#fff; padding:2px 6px; border-radius:4px; font-size:9pt; margin-right:4px; }
  /* marcas de corte simples (opcionales) */
  @if(!empty($crop))
    .crop { position: fixed; inset: 0; pointer-events:none; }
    .crop:before, .crop:after { content:""; position:absolute; border:0; }
  @endif
</style>
</head>
<body>
  <div class="wrap">
    <div class="tag">NOMBRE</div> <span class="nombre">{{ $item['NOMBRE'] }}</span><br>
    <div class="tag">NÚMERO</div> <div class="numero">{{ $item['NUMERO'] }}</div>
    <div class="meta">Talle: <strong>{{ $item['TALLE'] }}</strong></div>
  </div>
  @if(!empty($crop))<div class="crop"></div>@endif
</body>
</html>
