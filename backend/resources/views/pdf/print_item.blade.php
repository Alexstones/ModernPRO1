<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $title ?? 'Personalización' }}</title>
@php
  $mm    = fn($x)=> $x.'mm';
  $margin= $margin ?? 10;   // margen de página (mm)
  $bleed = $bleed  ?? 3;    // sangrado alrededor de la pieza (mm)
  $crop  = !empty($crop);
  $pageCrop = !empty($pageCrop);
  $rotate = !empty($rotate);
@endphp
<style>
  @page { size: A4; margin: {{ $mm($margin) }}; }
  body  { font-family: DejaVu Sans, sans-serif; color: #111827; margin: 0; }

  .page {
    position: relative; width: 100%; min-height: calc(297mm - {{ $mm($margin*2) }});
  }

  /* Área de pieza con sangrado */
  .piece {
    position: relative;
    border: 0.25mm solid #ddd;
    padding: {{ $mm($bleed) }};
    /* ejemplo: alto mínimo para mostrar número grande */
    min-height: 120mm;
  }

  .nombre { font-size: 28pt; font-weight:700; }
  .numero {
    font-size: 80pt; font-weight:900; color:#fff; line-height:1;
    -webkit-text-stroke: 3pt #000; /* contorno legible */
  }
  .meta   { margin-top: 6mm; font-size: 12pt; }

  /* Marcas de corte por pieza (esquinas) */
  @if($crop)
  .piece:before, .piece:after { content:''; position:absolute; width:12mm; height:12mm; pointer-events:none; }
  .piece:before { left:-{{ $mm($bleed) }}; top:-{{ $mm($bleed) }};
                  border-left:0.3mm solid #000; border-top:0.3mm solid #000; }
  .piece:after  { right:-{{ $mm($bleed) }}; bottom:-{{ $mm($bleed) }};
                  border-right:0.3mm solid #000; border-bottom:0.3mm solid #000; }
  @endif

  /* Marcas de corte de página (esquinas) */
  @if($pageCrop)
  .crop-page { position: fixed; inset: 0; pointer-events:none; }
  .crop-page:before, .crop-page:after { content:''; position:absolute; width:14mm; height:14mm; }
  .crop-page:before { left:0; top:0; border-left:0.3mm solid #000; border-top:0.3mm solid #000; }
  .crop-page:after  { right:0; bottom:0; border-right:0.3mm solid #000; border-bottom:0.3mm solid #000; }
  @endif
</style>
</head>
<body>
  <div class="page">
    <div class="piece">
      <div class="nombre">{{ $item['NOMBRE'] }}</div>
      <div class="numero" style="{{ $rotate ? 'transform: rotate(90deg); transform-origin:left top;' : '' }}">{{ $item['NUMERO'] }}</div>
      <div class="meta">Talle: <strong>{{ $item['TALLE'] }}</strong></div>
      @if(!empty($item['CATEGORIA']))
        <div class="meta">Categoría: <strong>{{ $item['CATEGORIA'] }}</strong></div>
      @endif
    </div>
    @if($pageCrop)<div class="crop-page"></div>@endif
  </div>
</body>
</html>
