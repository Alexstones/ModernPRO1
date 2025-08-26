@php
  $cols = $grid['cols']; $rows = $grid['rows'];
  $perPage = $cols * $rows;
  $pages = array_chunk($items, $perPage);
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $title ?? 'Imposición' }}</title>
<style>
  @page { size: {{ $sheet ?? 'A4' }}; margin: {{ $margin ?? 10 }}mm; }
  body { font-family: DejaVu Sans, sans-serif; color:#111827; }
  .grid { display: grid; grid-template-columns: repeat({{ $cols }}, 1fr); gap: 8mm; }
  .cell { border: 0.25mm solid #ddd; padding: {{ max(0, ($bleed ?? 0)) }}mm; min-height: 100mm; }
  .nombre { font-size: 18pt; font-weight:600; }
  .numero { font-size: 48pt; font-weight:900; -webkit-text-stroke: 2pt #000; color:#fff; line-height:1; }
  .meta { font-size: 10pt; margin-top: 2mm; }
  .page-break { page-break-after: always; }
  @if(!empty($crop)) .crop { position: fixed; inset: 0; border: 0.3pt dashed #aaa; } @endif
</style>
</head>
<body>
@foreach($pages as $pIndex=>$page)
  <div class="grid">
    @foreach($page as $it)
      <div class="cell">
        <div class="nombre">{{ $it['NOMBRE'] }}</div>
        <div class="numero">{{ $it['NUMERO'] }}</div>
        <div class="meta">Talle: {{ $it['TALLE'] }}</div>
      </div>
    @endforeach
  </div>
  @if(!empty($crop))<div class="crop"></div>@endif
  @if($pIndex < count($pages)-1)<div class="page-break"></div>@endif
@endforeach
</body>
</html>
