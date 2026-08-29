<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Materi Pembelajaran - {{ now()->translatedFormat('d F Y') }}</title>
    <style>
        body {
            font-family: 'Nunito', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .student-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-weight: bold;
        }
        .card-body {
            padding: 15px;
        }
        .card-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px;
            color: #333;
        }
        .card-text {
            color: #666;
            margin: 0 0 15px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .bg-info {
            background-color: #17a2b8;
            color: white;
        }
        .bg-secondary {
            background-color: #6c757d;
            color: white;
        }
        .bg-primary {
            background-color: #007bff;
            color: white;
        }
        .meta {
            font-size: 12px;
            color: #888;
            margin: 10px 0;
        }
        .actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
        .new-badge {
            background-color: #007bff;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daftar Materi Pembelajaran</h1>
            <p>SMK Kesehatan Trimurti Husada</p>
            <p>Diunduh pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>

        <div class="student-info">
            <p><strong>Nama Siswa:</strong> {{ auth()->user()->name }}</strong></p>
            <p><strong>Kelas:</strong> {{ auth()->user()->class ?? '-' }}</p>
        </div>

        @if($materials->count() > 0)
            @foreach($materials as $material)
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>{{ $material->title }}</span>
                            @if($material->created_at->diffInDays(now()) <= 7)
                                <span class="new-badge">BARU</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $material->description }}</p>
                        
                        <div style="margin: 10px 0;">
                            <span class="badge bg-info">{{ $material->subject ?? 'Tidak tersedia' }}</span>
                            <span class="badge bg-secondary">{{ $material->class_level ?? 'Tidak tersedia' }}</span>
                        </div>
                        
                        <div class="meta">
                            <div><strong>Oleh:</strong> {{ optional($material->teacher)->name ?? 'Tidak tersedia' }}</div>
                            <div><strong>Tanggal:</strong> {{ $material->created_at->format('d M Y') }}</div>
                            <div><strong>Unduhan:</strong> {{ $material->downloads_count }} kali</div>
                        </div>
                        
                        <div class="actions">
                            @if($material->file_path)
                                <div><strong>File tersedia untuk diunduh</strong></div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;">
                <p style="color: #666; font-size: 16px;">Belum ada materi yang tersedia</p>
            </div>
        @endif

        <div class="footer">
            <p>&copy; {{ date('Y') }} LMS Trimurti Husada. Dokumen ini dibuat secara otomatis.</p>
        </div>
    </div>
</body>
</html>