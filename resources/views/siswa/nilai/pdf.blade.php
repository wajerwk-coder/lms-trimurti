<!DOCTYPE html>
<html>
<head>
    <title>Rekap Nilai Siswa - {{ now()->translatedFormat('d F Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .student-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .badge { padding: 5px 10px; border-radius: 20px; color: white; font-weight: bold; }
        .bg-success { background-color: #28a745; }
        .bg-primary { background-color: #007bff; }
        .bg-info { background-color: #17a2b8; }
        .bg-warning text-dark { background-color: #ffc107; }
        .bg-danger { background-color: #dc3545; }
        .footer { text-align: center; margin-top: 50px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rekap Nilai Siswa</h2>
        <h3>{{ auth()->user()->name }} - {{ auth()->user()->class ?? 'Kelas tidak tersedia' }}</h3>
        <p>Diunduh pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
    </div>

    <div class="student-info">
        <p><strong>NIS:</strong> {{ auth()->user()->nis ?? '-' }}</strong></p>
        <p><strong>Semester:</strong> {{ request('semester', 'Semua Semester') }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mata Pelajaran</th>
                <th>Semester</th>
                <th>Nilai Tugas</th>
                <th>Nilai Praktikum</th>
                <th>Nilai UTS</th>
                <th>Nilai UAS</th>
                <th>Nilai Akhir</th>
                <th>Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scores as $subject => $scoreData)
            <tr>
                <td>{{ $subject }}</td>
                <td>{{ $scoreData['semester'] }}</td>
                <td>{{ $scoreData['assignment_score'] ?? '-' }}</td>
                <td>{{ $scoreData['practical_score'] ?? '-' }}</td>
                <td>{{ $scoreData['midterm_score'] ?? '-' }}</td>
                <td>{{ $scoreData['final_score'] ?? '-' }}</td>
                <td><strong>{{ $scoreData['final_grade'] ?? '-' }}</strong></td>
                <td>
                    @if(isset($scoreData['final_grade']))
                        @php
                            $grade = $scoreData['final_grade'];
                            $badgeClass = 'bg-danger';
                            $gradeLetter = 'E';
                            if ($grade >= 90) {
                                $badgeClass = 'bg-success';
                                $gradeLetter = 'A';
                            } elseif ($grade >= 80) {
                                $badgeClass = 'bg-primary';
                                $gradeLetter = 'B';
                            } elseif ($grade >= 70) {
                                $badgeClass = 'bg-info';
                                $gradeLetter = 'C';
                            } elseif ($grade >= 60) {
                                $badgeClass = 'bg-warning text-dark';
                                $gradeLetter = 'D';
                            }
                        @endphp
                        <span class="{{ $badgeClass }}">{{ $gradeLetter }}</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>&copy; {{ date('Y') }} LMS Trimurti Husada. Dokumen ini dibuat secara otomatis.</p>
    </div>
</body>
</html>