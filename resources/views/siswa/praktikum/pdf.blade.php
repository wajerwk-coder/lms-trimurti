<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Praktikum - {{ now()->translatedFormat('d F Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .student-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px; }
        .card { border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px; page-break-inside: avoid; }
        .card-header { background-color: #007bff; color: white; padding: 10px 15px; border-top-left-radius: 8px; border-top-right-radius: 8px; }
        .card-body { padding: 15px; }
        .badge { padding: 3px 8px; border-radius: 4px; font-weight: bold; margin-right: 5px; }
        .bg-info { background-color: #17a2b8; color: white; }
        .bg-success { background-color: #28a745; color: white; }
        .bg-warning text-dark { background-color: #ffc107; color: black; }
        .list-group-item { padding: 8px 15px; border: 1px solid #dee2e6; margin-bottom: 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Jadwal Praktikum</h2>
        <h3>{{ auth()->user()->name }} - {{ auth()->user()->class ?? '-' }}</h3>
        <p>Diunduh pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
    </div>

    <div class="student-info">
        <p><strong>NIS:</strong> {{ auth()->user()->nis ?? '-' }}</strong></p>
        <p><strong>Filter Status:</strong> {{ request('status', 'Semua Praktikum') }}</strong></p>
    </div>

    @if($practicals->count() > 0)
        @foreach($practicals as $practical)
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>{{ $practical->title }}</span>
                        @if($practical->date->isToday())
                            <span class="badge bg-warning text-dark">HARI INI</span>
                        @elseif($practical->date->isFuture())
                            <span class="badge bg-info">AKAN DATANG</span>
                        @else
                            <span class="badge bg-success">SELESAI</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Deskripsi:</strong> {{ $practical->description }}</p>

                    <div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                        <div><strong>Tanggal:</strong> {{ $practical->date->format('d M Y') }}</div>
                        <div><strong>Waktu:</strong> {{ $practical->start_time }} - {{ $practical->end_time }}</div>
                        <div><strong>Lokasi:</strong> {{ $practical->location }}</div>
                        <div><strong>Pengajar:</strong> {{ optional($practical->teacher)->name ?? 'Tidak tersedia' }}</div>
                        <div><strong>Kelas:</strong> {{ $practical->class_level ?? 'Tidak tersedia' }}</div>
                    </div>

                    @if($practical->materials_count > 0)
                        <div style="margin: 15px 0;">
                            <strong>Materi Praktikum:</strong>
                            <div style="margin-top: 5px;">
                                @foreach($practical->materials as $material)
                                    <div style="padding: 5px; background: #e9ecef; margin: 3px 0; border-radius: 3px;">
                                        {{ $material->title }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($practical->scores->where('user_id', auth()->id())->count() > 0)
                        @php
                            $score = $practical->scores->where('user_id', auth()->id())->first();
                        @endphp
                        <div style="margin: 15px 0; padding: 10px; background: #d1ecf1; border-radius: 5px;">
                            <div><strong>Nilai Anda:</strong> {{ $score->score }}/100</div>
                            @if($score->feedback)
                                <div><strong>Feedback:</strong> {{ $score->feedback }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;">
            <p style="color: #666; font-size: 16px;">Belum ada jadwal praktikum yang tersedia</p>
        </div>
    @endif

    <div style="text-align: center; margin-top: 50px; color: #666; font-size: 12px;">
        <p>&copy; {{ date('Y') }} LMS Trimurti Husada. Dokumen ini dibuat secara otomatis.</p>
    </div>
</body>
</html>
