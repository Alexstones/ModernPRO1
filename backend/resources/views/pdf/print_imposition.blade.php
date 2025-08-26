@php
  $cols = $grid['cols']; $rows = $grid['rows']; $gap = $grid['gap'] ?? 4;
  $perPage = $cols * $rows;
  $pages = array_chunk($items, $perPage);
  $margin = $margin ?? 10;  // mm
  $bleed  = $bleed  ?? 3;   // mm
  $crop   = !empty($crop);
  $pageCrop = !empty($pageCrop);
  $fit    = $fit ?? 'contain';
  $rotate = !empty($rotate);
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $title ?? 'Imposición' }}</title>
<style>
  @page { size: {{ $sheet ?? 'A4' }}; margin: {{ $margin }}mm; }
  body { font-family: DejaVu Sans, sans-serif; color:#111827; margin: 0; }

  .page { position: relative; break-after: page; }
  .grid { display: grid; grid-template-columns: repeat({{ $cols }}, 1fr); gap: {{ $gap }}mm; }

  .cell {
    position: relative;
    border: 0.25mm solid #ddd;
    padding: {{ max(0,$bleed) }}mm; /* sangrado */
    min-height: 90mm;
    overflow: hidden;
  }

  .nombre { font-size: 16pt; font-weight:600; }
  .numero {
    font-size: 48pt; font-weight:900; color:#fff; line-height:1;
    -webkit-text-stroke: 2pt #000;
    display:inline-block;
    {{ $rotate ? 'transform: rotate(90deg); transform-origin:left top;' : '' }}
  }
  .meta { font-size: 10pt; margin-top: 2mm; }

  /* marcas de corte por celda */
  @if($crop)
  .cell:before, .cell:after { content:''; position:absolute; width:10mm; height:10mm; pointer-events:none; }
  .cell:before { left:-{{ $bleed }}mm; top:-{{ $bleed }}mm;
                 border-left:0.3mm solid #000; border-top:0.3mm solid #000; }
  .cell:after  { right:-{{ $bleed }}mm; bottom:-{{ $bleed }}mm;
                 border-right:0.3mm solid #000; border-bottom:0.3mm solid #000; }
  @endif

  /* marcas de corte de página */
  @if($pageCrop)
  .crop-page { position: fixed; inset: 0; pointer-events:none; }
  .crop-page:before, .crop-page:after { content:''; position:absolute; width:14mm; height:14mm; }
  .crop-page:before { left:0; top:0; border-left:0.3mm solid #000; border-top:0.3mm solid #000; }
  .crop-page:after  { right:0; bottom:0; border-right:0.3mm solid #000; border-bottom:0.3mm solid #000; }
  @endif
</style>
</head>
<body>
@foreach($pages as $pIndex=>$page)
  <div class="page">
    <div class="grid">
      @foreach($page as $it)
        <div class="cell">
          <div class="nombre">{{ $it['NOMBRE'] }}</div>
          <div class="numero">{{ $it['NUMERO'] }}</div>
          <div class="meta">
            Talle: <strong>{{ $it['TALLE'] }}</strong>
            @if(!empty($it['CATEGORIA'])) · Cat: <strong>{{ $it['CATEGORIA'] }}</strong>@endif
          </div>
        </div>
      @endforeach
    </div>
    @if($pageCrop)<div class="crop-page"></div>@endif
  </div>
@endforeach
</body>
</html>
