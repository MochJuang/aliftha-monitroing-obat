<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="{{ count($headings) }}">{{ $title }}</th>
            </tr>
            <tr>
                <th colspan="{{ count($headings) }}">Dicetak pada {{ now()->format('d/m/Y H:i') }}</th>
            </tr>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">Belum ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
