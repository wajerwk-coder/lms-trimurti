<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
    body { margin: 20px; color: #1e293b; }
    h1 { font-size: 16px; color: #3b82f6; margin-bottom: 4px; }
    .subtitle { color: #64748b; font-size: 11px; margin-bottom: 16px; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #3b82f6; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
    table.data td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
    table.data tr:nth-child(even) td { background: #eff6ff; }
    .badge { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
    .active   { background: #dcfce7; color: #16a34a; }
    .expired  { background: #fee2e2; color: #dc2626; }
    .draft    { background: #f1f5f9; color: #64748b; }
    .footer { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
</style>
</head>
<body>
    <h1>Laporan Tugas</h1>
    <p class="subtitle">SMK Kesehatan Trimurti Husada — Dicetak {{ now()->format('d M Y, H:i') }}</p>

    <table style="margin-bottom:12px;">
        <tr>
            <td><strong>Periode</strong> : {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Total Tugas</strong> : {{ $stats['total_assignments'] }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Total Pengumpulan</strong> : {{ $stats['total_submissions'] }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Rata-rata</strong> : {{ number_format($stats['average_score'], 1) }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Judul Tugas</th>
                <th>Mata Pelajaran</th>
                <th>Deadline</th>
                <th>Pengumpulan</th>
                <th>Dinilai</th>
                <th>Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $i => $asgn)
            @php
                $avg = $asgn->submissions->whereNotNull('score')->avg('score');
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $asgn->title }}</td>
                <td>{{ $asgn->subject?->name ?? '—' }}</td>
                <td>{{ $asgn->due_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $asgn->submissions_count ?? 0 }}</td>
                <td>{{ $asgn->graded_count ?? 0 }}</td>
                <td>{{ $avg ? number_format($avg, 1) : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:12px;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">SMK Kesehatan Trimurti Husada — Laporan dibuat otomatis oleh sistem LMS</div>
</body>
</html>
