<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
    body { margin: 20px; color: #1e293b; }
    h1 { font-size: 16px; color: #0f766e; margin-bottom: 4px; }
    .subtitle { color: #64748b; font-size: 11px; margin-bottom: 16px; }
    .meta { background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 6px; margin-bottom: 16px; }
    .meta td { padding: 2px 8px 2px 0; }
    .stats { display: table; width: 100%; margin-bottom: 16px; }
    .stat-box { display: table-cell; width: 25%; padding: 8px; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 6px; text-align: center; margin: 4px; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #0f766e; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
    table.data td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
    table.data tr:nth-child(even) td { background: #f8fafc; }
    .badge { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
    .hadir  { background: #dcfce7; color: #16a34a; }
    .izin   { background: #fef9c3; color: #a16207; }
    .sakit  { background: #e0f2fe; color: #0891b2; }
    .alpha  { background: #fee2e2; color: #dc2626; }
    .footer { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
</style>
</head>
<body>
    <h1>Laporan Absensi Siswa</h1>
    <p class="subtitle">SMK Kesehatan Trimurti Husada — Dicetak {{ now()->format('d M Y, H:i') }}</p>

    <table class="meta">
        <tr>
            <td><strong>Periode</strong></td>
            <td>: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><strong>Kelas</strong></td>
            <td>: {{ $filters['kelas'] ? \App\Models\Kelas::find($filters['kelas'])?->name : 'Semua Kelas' }}</td>
        </tr>
    </table>

    {{-- Stats --}}
    <table style="width:100%;margin-bottom:16px;">
        <tr>
            @foreach($stats as $status => $count)
            <td style="width:25%;padding:4px;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px;text-align:center;">
                    <div style="font-size:18px;font-weight:bold;color:#0f766e;">{{ $count }}</div>
                    <div style="font-size:10px;color:#64748b;text-transform:capitalize;">{{ $status }}</div>
                </div>
            </td>
            @endforeach
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Siswa</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendance as $i => $att)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $att->siswa?->name ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}</td>
                <td>
                    <span class="badge {{ $att->status }}">{{ strtoupper($att->status) }}</span>
                </td>
                <td>{{ $att->note ?? $att->keterangan ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:12px;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">SMK Kesehatan Trimurti Husada — Laporan dibuat otomatis oleh sistem LMS</div>
</body>
</html>
