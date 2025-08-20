@php
  $pageW = $sizes['pageW'];   // mm
  $pageH = $sizes['pageH'];   // mm
  $thumbW = $sizes['thumbW']; // mm
  $thumbGap = $sizes['thumbGap']; // mm
  $mainW = $pageW - $thumbW - 10; // 10mm de padding
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 0; }
    body { margin: 0; font-family: {{ $fontFamily }}; background: #ffffff; }
    .wrap { width: {{ $pageW }}mm; height: {{ $pageH }}mm; display: flex; flex-direction: row; }

    .thumbs {
      width: {{ $thumbW }}mm;
      padding: 5mm 2mm;
      box-sizing: border-box;
      border-right: 0.4mm solid #ddd;
    }
    .thumb { width: 100%; margin-bottom: {{ $thumbGap }}mm; border: 0.3mm solid #bbb; border-radius: 1.5mm; overflow: hidden; }
    .thumb img { width: 100%; height: auto; display: block; }

    .main { width: {{ $mainW }}mm; padding: 6mm; box-sizing: border-box; position: relative; }
    .svgText { width: 100%; margin-bottom: 6mm; }
    .main-img { width: 100%; height: auto; border: 0.4mm solid #bbb; border-radius: 2mm; }

    .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4mm; }
    .grid img { width: 100%; height: auto; border: 0.3mm solid #bbb; border-radius: 1.5mm; }
  </style>
</head>
<body>
  <div class="wrap">
    {{-- Columna de miniaturas --}}
    <div class="thumbs">
      @foreach($images as $img)
        <div class="thumb">
          <img src="{{ $img['dataUrl'] }}" alt="{{ $img['name'] ?? 'img' }}">
        </div>
      @endforeach
    </div>

    {{-- Área principal --}}
    <div class="main">
      {{-- Texto con contorno (SVG soporta stroke nativo en PDF) --}}
      @if(!empty($text))
        @php
          $strokeEnabled = $stroke['enabled'] ?? true;
          $strokeWidth = $stroke['width'] ?? 2;
          $strokeColor = $stroke['color'] ?? '#ffffff';
          $fill = $fillColor ?? '#111827';
          $fs = $fontSize ?? 42;
        @endphp
        <svg class="svgText" xmlns="http://www.w3.org/2000/svg" width="100%" height="{{ $fs * 1.8 }}">
          <text x="0" y="{{ $fs * 1.2 }}"
                font-family="{{ $fontFamily }}"
                font-size="{{ $fs }}"
                fill="{{ $fill }}"
                @if($strokeEnabled) stroke="{{ $strokeColor }}" stroke-width="{{ $strokeWidth }}" @endif
                style="font-weight:700;">
            {{ $text }}
          </text>
        </svg>
      @endif

      {{-- Imagen principal (si hay 1) o grilla (si hay varias) --}}
      @if(count($images) === 1)
        <img class="main-img" src="{{ $images[0]['dataUrl'] }}" alt="{{ $images[0]['name'] ?? 'principal' }}">
      @elseif(count($images) > 1)
        <div class="grid">
          @foreach($images as $img)
            <img src="{{ $img['dataUrl'] }}" alt="{{ $img['name'] ?? 'img' }}">
          @endforeach
        </div>
      @endif
    </div>
  </div>
</body>
</html>
