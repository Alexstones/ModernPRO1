<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 12mm; }
    body { font-family: {!! json_encode($meta['fontFamily'] ?? 'DejaVu Sans') !!}; color: {{ $meta['fillColor'] ?? '#111827' }}; }
    h1 { font-size: {{ ($meta['fontSize'] ?? 16) + 4 }}pt; margin: 0 0 8pt 0; }
    h2 { font-size: {{ ($meta['fontSize'] ?? 16) + 2 }}pt; margin: 12pt 0 4pt 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
    th, td { border: 0.4pt solid #bbb; padding: 4pt 6pt; font-size: 10pt; }
    th { background: #f1f5f9; }
    .sheet { page-break-after: always; }
    .sheet:last-child { page-break-after: auto; }
  </style>
</head>
<body>
  @if(!empty($meta['text']))
    <h1>{{ $meta['text'] }}</h1>
  @endif

  @foreach($sheets as $s)
    <div class="sheet">
      <h2>{{ $s['title'] }}</h2>
      @php $rows = $s['rows'] ?? []; @endphp
      @if(!empty($rows))
        <table>
          @foreach($rows as $ri => $row)
            @if($ri === 0)
              <tr>
                @foreach($row as $cell)
                  <th>{{ $cell }}</th>
                @endforeach
              </tr>
            @else
              <tr>
                @foreach($row as $cell)
                  <td>{{ $cell }}</td>
                @endforeach
              </tr>
            @endif
          @endforeach
        </table>
      @else
        <div style="color:#64748b">Hoja vacía</div>
      @endif
    </div>
  @endforeach
</body>
</html>
