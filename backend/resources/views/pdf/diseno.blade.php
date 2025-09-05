@php
  $pageW = $sizes['pageW'] ?? 210;   // mm
  $pageH = $sizes['pageH'] ?? 297;   // mm
  $thumbW = $sizes['thumbW'] ?? 80;  // mm
  $thumbGap = $sizes['thumbGap'] ?? 4; // mm
  $mainW = $pageW - $thumbW - 10; // 10mm de separación
  // Utilidades
  $mm = function($v){ return $v . 'mm'; };
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 10mm; } /* deja margen para verificar que hay página */
    body { margin: 0; font-family: {!! json_encode($fontFamily) !!}; color: {{$fillColor ?? '#111827'}}; }

    table.layout { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; }

    .thumbs { width: {{ $thumbW }}mm; border-right: 0.4mm solid #ddd; padding-right: 2mm; }
    .thumb  { width: 100%; border: 0.3mm solid #bbb; border-radius: 1mm; margin-bottom: {{ $thumbGap }}mm; }
    .thumb img { display:block; width:100%; height:auto; }

    .main { padding-left: 4mm; }
    .title { font-weight: 700; margin: 0 0 6mm 0; }

    /* simulación de contorno sin SVG (doble texto superpuesto) */
    .stroke-wrap { position: relative; display: inline-block; }
    .stroke-outline {
      position: absolute; left:0; top:0; z-index:0;
      color: {{ $stroke['color'] ?? '#000000' }};
      text-shadow:
        -1px 0 {{$stroke['color'] ?? '#000'}},
         1px 0 {{$stroke['color'] ?? '#000'}},
         0 1px {{$stroke['color'] ?? '#000'}},
         0 -1px {{$stroke['color'] ?? '#000'}};
    }
    .stroke-fill { position: relative; z-index:1; color: {{ $fillColor ?? '#111827' }}; }

    .grid { width: 100%; border-collapse: separate; border-spacing: 4mm; }
    .grid img { display:block; width:100%; height:auto; border:0.3mm solid #bbb; border-radius: 1mm; }
  </style>
</head>
<body>

  <table class="layout">
    <tr>
      <td class="thumbs">
        @forelse($images ?? [] as $img)
          <div class="thumb">
            <img src="{{ $img['dataUrl'] ?? '' }}" alt="{{ $img['name'] ?? 'img' }}">
          </div>
        @empty
          <div style="font-size:10pt;color:#888;">Sin imágenes</div>
        @endforelse
      </td>

      <td class="main" style="width: {{ $mainW }}mm;">
        @php
          $text = $text ?? '';
          $fs   = (float)($fontSize ?? 42);
          $strokeEnabled = data_get($stroke ?? [], 'enabled', true);
        @endphp

        @if($text !== '')
          @if($strokeEnabled)
            <div class="stroke-wrap">
              <div class="title stroke-outline" style="font-size: {{ $fs }}pt;">{{ $text }}</div>
              <div class="title stroke-fill"    style="font-size: {{ $fs }}pt;">{{ $text }}</div>
            </div>
          @else
            <div class="title" style="font-size: {{ $fs }}pt;">{{ $text }}</div>
          @endif
        @endif

        @if(!empty($images))
          @if(count($images) === 1)
            <img src="{{ $images[0]['dataUrl'] ?? '' }}" style="max-width:100%; height:auto; border:0.4mm solid #bbb; border-radius: 2mm;">
          @else
            <table class="grid">
              @foreach($images as $i => $img)
                @if($i % 2 === 0)<tr>@endif
                  <td style="width:50%;"><img src="{{ $img['dataUrl'] ?? '' }}" alt="{{ $img['name'] ?? 'img' }}"></td>
                @if($i % 2 === 1)</tr>@endif
              @endforeach
              @if(count($images) % 2 === 1)</tr>@endif
            </table>
          @endif
        @endif
      </td>
    </tr>
  </table>

</body>
</html>
