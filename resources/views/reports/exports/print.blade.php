<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body {
            color: #0f172a;
            font-family: Arial, sans-serif;
            margin: 32px;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 6px;
        }

        p {
            color: #64748b;
            margin: 0 0 24px;
        }

        table {
            border-collapse: collapse;
            font-size: 12px;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
            font-weight: 700;
        }

        @media print {
            body {
                margin: 16px;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $title }}</h1>
    <p>Dicetak pada {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
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
