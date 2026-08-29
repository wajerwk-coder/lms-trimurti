<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
    body { margin: 20px; color: #1e293b; }
    h1 { font-size: 16px; color: #0891b2; margin-bottom: 4px; }
    .subtitle { color: #64748b; font-size: 11px; margin-bottom: 16px; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #0891b2; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
    table.data td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
    table.data tr:nth-child(even) td { background: #f0f9ff; }
    .footer { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
</style>
</head>
<body>
    <h1>Laporan Materi Pembelajaran</h1>
    <p class="subtitle">SMK Kesehatan Trimurti Husada — Dicetak {{ now()->format('d M Y, H:i') }}</p>

    <table style="margin-bottom:12px;">
        <tr>
            <td><strong>Periode</strong> : {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Total Materi</strong> : {{ $stats['total_materials'] }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Total Unduhan</strong> : {{ $stats['total_downloads'] }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Judul Materi</th>
                <th>Mata Pelajaran</th>
                <th>Tanggal Upload</th>
                <th>Unduhan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $i => $mat)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $mat->title }}</td>
                <td>{{ $mat->subject?->name ?? '—' }}</td>
                <td>{{ $mat->created_at?->format('d M Y') ?? '—' }}</td>
                <td>{{ $mat->downloads_count ?? 0 }}</td>
                <td>{{ $mat->published_at ? 'Terbit' : 'Draft' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:12px;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">SMK Kesehatan Trimurti Husada — Laporan dibuat otomatis oleh sistem LMS</div>
</body>
</html>
