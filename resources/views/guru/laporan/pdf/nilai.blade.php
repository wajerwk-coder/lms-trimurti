<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Laporan Nilai Siswa</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; }

    /* ── Header ───────────────────────────────────────────────── */
    .header {
        background: linear-gradient(135deg, #0f766e, #1d4ed8);
        color: white;
        padding: 18px 24px;
        margin-bottom: 16px;
        border-radius: 6px;
    }
    .header h1 { font-size: 18px; font-weight: 700; margin-bottom: 3px; }
    .header p  { font-size: 11px; opacity: .8; }

    /* ── Meta info ────────────────────────────────────────────── */
    .meta-row {
        display: flex;
        gap: 12px;
        margin-bottom: 14px;
    }
    .meta-box {
        flex: 1;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 8px 12px;
    }
    .meta-box .lbl { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: .05em; margin-bottom: 2px; }
    .meta-box .val { font-size: 13px; font-weight: 700; color: #0f766e; }

    /* ── Section title ────────────────────────────────────────── */
    .section-title {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        padding: 6px 10px;
        background: #f1f5f9;
        border-left: 4px solid #0891b2;
        margin-bottom: 8px;
        margin-top: 16px;
        border-radius: 0 4px 4px 0;
    }

    /* ── Tables ───────────────────────────────────────────────── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    thead tr { background: #0f766e; }
    thead th {
        color: white;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 7px 10px;
        text-align: left;
    }
    thead th.text-center { text-align: center; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody tr:nth-child(odd)  { background: #ffffff; }
    tbody td {
        padding: 6px 10px;
        font-size: 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    tbody td.text-center { text-align: center; }
    tbody td.num { text-align: center; font-weight: 700; }

    /* ── Grade badges ─────────────────────────────────────────── */
    .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: 700; }
    .badge-A { background: #dcfce7; color: #16a34a; }
    .badge-B { background: #dbeafe; color: #1d4ed8; }
    .badge-C { background: #fef9c3; color: #b45309; }
    .badge-D { background: #fee2e2; color: #dc2626; }
    .badge-E { background: #f1f5f9; color: #64748b; }

    /* ── Score color ──────────────────────────────────────────── */
    .score-hi  { color: #16a34a; font-weight: 700; }
    .score-mid { color: #d97706; font-weight: 700; }
    .score-lo  { color: #dc2626; font-weight: 700; }

    /* ── Empty state ──────────────────────────────────────────── */
    .empty { text-align: center; padding: 16px; color: #94a3b8; font-style: italic; font-size: 10px; }

    /* ── Footer ───────────────────────────────────────────────── */
    .footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
        font-size: 9px;
        color: #94a3b8;
    }
</style>
</head>
<body>

{{-- ── Header ─────────────────────────────────────────────────── --}}
<div class="header">
    <h1>Laporan Nilai Siswa</h1>
    <p>LMS Trimurti Husada &mdash; {{ $guru->name ?? 'Guru' }}</p>
</div>

{{-- ── Meta info: periode & kelas ─────────────────────────────── --}}
<table style="margin-bottom:14px;">
    <tr>
        <td style="width:25%;padding:0 4px 0 0;">
            <div class="meta-box">
                <div class="lbl">Periode</div>
                <div class="val" style="font-size:11px;">
                    {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }}
                    &ndash;
                    {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
                </div>
            </div>
        </td>
        <td style="width:18%;padding:0 4px;">
            <div class="meta-box">
                <div class="lbl">Kelas</div>
                <div class="val">{{ $kelasNama ?? 'Semua Kelas' }}</div>
            </div>
        </td>
        <td style="width:18%;padding:0 4px;">
            <div class="meta-box">
                <div class="lbl">Total Nilai Tugas</div>
                <div class="val">{{ $nilaiTugas->count() }}</div>
            </div>
        </td>
        <td style="width:18%;padding:0 4px;">
            <div class="meta-box">
                <div class="lbl">Total Nilai Praktik</div>
                <div class="val">{{ $nilaiPraktik->count() }}</div>
            </div>
        </td>
        <td style="width:18%;padding:0 0 0 4px;">
            <div class="meta-box">
                <div class="lbl">Dicetak</div>
                <div class="val" style="font-size:10px;">{{ now()->format('d M Y H:i') }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- ══ BAGIAN 1: NILAI TUGAS ═══════════════════════════════════════ --}}
<div class="section-title">&#x1F4CB; Nilai Tugas ({{ $nilaiTugas->count() }} data)</div>

@if($nilaiTugas->isEmpty())
    <div class="empty">Tidak ada data nilai tugas pada periode ini.</div>
@else
<table>
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:22%;">Nama Siswa</th>
            <th style="width:28%;">Judul Tugas</th>
            <th style="width:20%;">Mata Pelajaran</th>
            <th style="width:13%;">Dikumpulkan</th>
            <th class="text-center" style="width:8%;">Nilai</th>
            <th class="text-center" style="width:5%;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($nilaiTugas as $i => $n)
        @php
            $s  = (float)$n->score;
            $g  = $s >= 90 ? 'A' : ($s >= 80 ? 'B' : ($s >= 70 ? 'C' : ($s >= 60 ? 'D' : 'E')));
            $sc = $s >= 80 ? 'score-hi' : ($s >= 60 ? 'score-mid' : 'score-lo');
        @endphp
        <tr>
            <td style="color:#94a3b8;">{{ $i + 1 }}</td>
            <td style="font-weight:600;">{{ $n->siswa?->name ?? '—' }}</td>
            <td>{{ $n->assignment?->title ?? '—' }}</td>
            <td style="color:#64748b;">{{ $n->assignment?->subject?->name ?? '—' }}</td>
            <td style="color:#64748b;">{{ $n->submitted_at?->format('d/m/Y') ?? '—' }}</td>
            <td class="num {{ $sc }}">{{ number_format($s, 0) }}</td>
            <td class="text-center"><span class="badge badge-{{ $g }}">{{ $g }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Ringkasan nilai tugas --}}
@php
    $avgT = round($nilaiTugas->avg('score') ?? 0, 1);
    $maxT = $nilaiTugas->max('score') ?? 0;
    $minT = $nilaiTugas->min('score') ?? 0;
@endphp
<table style="margin-top:4px;margin-bottom:0;">
    <tr>
        <td style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:4px;padding:6px 12px;font-size:9px;color:#0f766e;font-weight:700;width:33%;">
            Rata-rata: {{ $avgT }}
        </td>
        <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;padding:6px 12px;font-size:9px;color:#16a34a;font-weight:700;width:33%;text-align:center;">
            Nilai Tertinggi: {{ number_format($maxT, 0) }}
        </td>
        <td style="background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:6px 12px;font-size:9px;color:#dc2626;font-weight:700;width:33%;text-align:right;">
            Nilai Terendah: {{ number_format($minT, 0) }}
        </td>
    </tr>
</table>
@endif

{{-- ══ BAGIAN 2: NILAI PRAKTIKUM ═══════════════════════════════════ --}}
<div class="section-title">&#x1F9EA; Nilai Praktikum ({{ $nilaiPraktik->count() }} data)</div>

@if($nilaiPraktik->isEmpty())
    <div class="empty">Tidak ada data nilai praktikum pada periode ini.</div>
@else
<table>
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:22%;">Nama Siswa</th>
            <th style="width:28%;">Judul Praktikum</th>
            <th style="width:20%;">Mata Pelajaran</th>
            <th style="width:13%;">Dinilai</th>
            <th class="text-center" style="width:8%;">Nilai</th>
            <th class="text-center" style="width:5%;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($nilaiPraktik as $i => $np)
        @php
            $s2  = (float)$np->score;
            $g2  = $s2 >= 90 ? 'A' : ($s2 >= 80 ? 'B' : ($s2 >= 70 ? 'C' : ($s2 >= 60 ? 'D' : 'E')));
            $sc2 = $s2 >= 80 ? 'score-hi' : ($s2 >= 60 ? 'score-mid' : 'score-lo');
        @endphp
        <tr>
            <td style="color:#94a3b8;">{{ $i + 1 }}</td>
            <td style="font-weight:600;">{{ $np->siswa?->name ?? '—' }}</td>
            <td>{{ $np->practical?->title ?? '—' }}</td>
            <td style="color:#64748b;">{{ $np->practical?->subject?->name ?? '—' }}</td>
            <td style="color:#64748b;">{{ $np->graded_at?->format('d/m/Y') ?? '—' }}</td>
            <td class="num {{ $sc2 }}">{{ number_format($s2, 0) }}</td>
            <td class="text-center"><span class="badge badge-{{ $g2 }}">{{ $g2 }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>

@php
    $avgP = round($nilaiPraktik->avg('score') ?? 0, 1);
    $maxP = $nilaiPraktik->max('score') ?? 0;
    $minP = $nilaiPraktik->min('score') ?? 0;
@endphp
<table style="margin-top:4px;margin-bottom:0;">
    <tr>
        <td style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:4px;padding:6px 12px;font-size:9px;color:#0f766e;font-weight:700;width:33%;">
            Rata-rata: {{ $avgP }}
        </td>
        <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;padding:6px 12px;font-size:9px;color:#16a34a;font-weight:700;width:33%;text-align:center;">
            Nilai Tertinggi: {{ number_format($maxP, 0) }}
        </td>
        <td style="background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:6px 12px;font-size:9px;color:#dc2626;font-weight:700;width:33%;text-align:right;">
            Nilai Terendah: {{ number_format($minP, 0) }}
        </td>
    </tr>
</table>
@endif

{{-- ── Footer ───────────────────────────────────────────────────── --}}
<div class="footer">
    Dicetak dari LMS Trimurti Husada &bull; {{ now()->format('d F Y, H:i') }} WIB &bull; {{ $guru->name ?? '' }}
</div>

</body>
</html>
