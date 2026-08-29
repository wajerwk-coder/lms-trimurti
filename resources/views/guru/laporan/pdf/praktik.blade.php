<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
    body { margin: 20px; color: #1e293b; }
    h1 { font-size: 16px; color: #7c3aed; margin-bottom: 4px; }
    .subtitle { color: #64748b; font-size: 11px; margin-bottom: 16px; }
    .meta td { padding: 2px 8px 2px 0; }
    table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.data th { background: #7c3aed; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
    table.data td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
    table.data tr:nth-child(even) td { background: #faf5ff; }
    .grade { font-weight: bold; }
    .a { color: #16a34a; } .b { color: #3b82f6; } .c { color: #f59e0b; } .d { color: #ef4444; } .e { color: #6b7280; }
    .section-title { background: #f3e8ff; padding: 6px 10px; font-weight: bold; color: #6d28d9; border-left: 3px solid #7c3aed; margin: 12px 0 6px; font-size: 12px; }
    .footer { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    .stat-row td { padding: 4px; }
</style>
</head>
<body>
    <h1>Laporan Penilaian Praktikum</h1>
    <p class="subtitle">SMK Kesehatan Trimurti Husada — Dicetak {{ now()->format('d M Y, H:i') }}</p>

    <table style="margin-bottom:12px;">
        <tr class="stat-row">
            <td><strong>Periode</strong> : {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Total Praktikum</strong> : {{ $stats['total_practicals'] }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Rata-rata Nilai</strong> : {{ $stats['average_score'] }}</td>
        </tr>
    </table>

    @foreach($practicals as $p)
    <div class="section-title">{{ $p->title }} — {{ $p->subject?->name ?? '—' }}</div>
    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Siswa</th>
                <th>Nilai</th>
                <th>Grade</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $scores = $p->scores->whereNull('criteria_id');
            @endphp
            @forelse($scores as $j => $sc)
            @php
                $val   = (float) $sc->score;
                $grade = $val >= 90 ? 'A' : ($val >= 80 ? 'B' : ($val >= 70 ? 'C' : ($val >= 60 ? 'D' : 'E')));
                $gc    = strtolower($grade);
            @endphp
            <tr>
                <td>{{ $j + 1 }}</td>
                <td>{{ $sc->siswa?->name ?? '—' }}</td>
                <td>{{ number_format($val, 1) }}</td>
                <td class="grade {{ $gc }}">{{ $grade }}</td>
                <td>{{ $sc->feedback ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;">Belum ada penilaian</td></tr>
            @endforelse
        </tbody>
    </table>
    @endforeach

    <div class="footer">SMK Kesehatan Trimurti Husada — Laporan dibuat otomatis oleh sistem LMS</div>
</body>
</html>
