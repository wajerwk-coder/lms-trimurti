<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KriteriaPenilaian;

class SopKriteriaPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        $data = array_merge(
            self::sopPemasanganInfus(),
            self::sopPemeriksaanGolonganDarah(),
            self::sopPeracikanObatFarmasi()
        );

        foreach ($data as $item) {
            KriteriaPenilaian::updateOrCreate(
                [
                    'name'          => $item['name'],
                    'mata_praktik'  => $item['mata_praktik'],
                    'tingkat_kelas' => $item['tingkat_kelas'],
                    'kategori'      => $item['kategori'],
                ],
                [
                    'description'   => $item['description'],
                    'weight'        => $item['weight'],
                    'max_score'     => 100,
                    'is_active'     => true,
                    'sop_checklist' => $item['sop_checklist'],
                ]
            );
        }
    }

    // ── SOP Pemasangan Infus (Keperawatan) ────────────────────────────────

    private static function sopPemasanganInfus(): array
    {
        return [
            // A. Tahap Pra Interaksi → kategori: persiapan (bobot 25%)
            [
                'name'          => 'Tahap Pra Interaksi – Pemasangan Infus',
                'kategori'      => KriteriaPenilaian::KATEGORI_PERSIAPAN,
                'weight'        => 25,
                'description'   => 'Persiapan sebelum tindakan: pengecekan catatan medik, cuci tangan, dan kelengkapan alat/bahan pemasangan infus.',
                'mata_praktik'  => 'Pemasangan Infus',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Mengecek catatan medik pasien',
                    'Cuci tangan',
                    'Menyiapkan perlak dan pengalasnya',
                    'Menyiapkan tourniquet',
                    'Menyiapkan kapas alkohol/alcohol swab',
                    'Menyiapkan plester',
                    'Menyiapkan gunting perban',
                    'Menyiapkan kain kasa steril',
                    'Menyiapkan jarum infus (abbocath)',
                    'Menyiapkan cairan infus',
                    'Menyiapkan bengkok',
                    'Menyiapkan bak instrumen steril',
                    'Menyiapkan sarung tangan bersih',
                    'Menyiapkan standar infus',
                ],
            ],

            // B. Tahap Orientasi → kategori: sikap (bobot 15%)
            [
                'name'          => 'Tahap Orientasi – Pemasangan Infus',
                'kategori'      => KriteriaPenilaian::KATEGORI_SIKAP,
                'weight'        => 15,
                'description'   => 'Komunikasi terapeutik dan orientasi kepada pasien sebelum tindakan pemasangan infus.',
                'mata_praktik'  => 'Pemasangan Infus',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Memberikan salam dan menyebutkan nama pasien',
                    'Menjelaskan tujuan dan prosedur yang akan dilakukan',
                    'Memberikan kesempatan kepada pasien untuk bertanya',
                    'Melakukan kontrak waktu',
                ],
            ],

            // C. Tahapan Kerja → kategori: pelaksanaan (bobot 45%)
            [
                'name'          => 'Tahapan Kerja – Pemasangan Infus',
                'kategori'      => KriteriaPenilaian::KATEGORI_PELAKSANAAN,
                'weight'        => 45,
                'description'   => 'Pelaksanaan teknik pemasangan infus secara sistematis sesuai SOP.',
                'mata_praktik'  => 'Pemasangan Infus',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Menjaga privasi klien',
                    'Membawa alat ke dekat pasien',
                    'Mencuci tangan',
                    'Memakai sarung tangan',
                    'Membuka daerah yang akan diinfus',
                    'Memasang alat di bawah anggota badan yang akan diinfus',
                    'Membuka set infus dan meletakkannya pada bak instrumen steril',
                    'Memasukkan jarum set infus ke dalam botol infus, mengalirkan cairan ke selang infus hingga ke bengkok untuk mengeluarkan udara',
                    'Mengisi tempat tetesan infus kurang lebih setengahnya',
                    'Memastikan roller selang infus dalam keadaan menutup (ke arah bawah)',
                    'Menggantungkan selang infus pada standar infus',
                    'Membuka abbocath dari plester',
                    'Memotong 3 lembar plester',
                    'Memilih pembuluh darah (berukuran besar, tidak bercabang, tidak di area persendian)',
                    'Membendung bagian proksimal/atas pembuluh darah dengan tourniquet',
                    'Meminta pasien menegangkan tangan dengan ibu jari di dalam genggaman',
                    'Mendesinfeksi daerah yang akan dipasang infus',
                    'Memasukkan jarum infus ke vena dengan lubang jarum menghadap ke atas hingga darah mengaliri jarum dan abbocath',
                    'Melepas tourniquet setelah darah masuk',
                    'Melepas jarum sambil meninggalkan abbocath di dalam pembuluh darah',
                    'Menekan pangkal abbocath dan memasukkan ujung selang infus set ke abbocath',
                    'Fiksasi cara kupu-kupu: plester dibalik di bawah selang infus kemudian disilangkan',
                    'Menutup jarum dan tempat tusukan dengan kasa steril dan diplester',
                    'Mengatur/menghitung jumlah tetesan infus',
                    'Mengatur posisi anggota tubuh yang diinfus, diberi spalk bila perlu',
                    'Menuliskan tanggal pemasangan infus pada plester terakhir',
                    'Merapikan alat dan pasien',
                    'Melepaskan sarung tangan dan mencuci tangan',
                ],
            ],

            // D. Tahap Terminasi (Evaluasi) → kategori: hasil (bobot 15%)
            [
                'name'          => 'Tahap Terminasi (Evaluasi) – Pemasangan Infus',
                'kategori'      => KriteriaPenilaian::KATEGORI_HASIL,
                'weight'        => 15,
                'description'   => 'Evaluasi hasil pemasangan infus meliputi kelancaran aliran, sterilitas, kenyamanan pasien, dan dokumentasi.',
                'mata_praktik'  => 'Pemasangan Infus',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Aliran dan tetesan infus lancar',
                    'Tidak terjadi hematom',
                    'Sterilitas terjaga',
                    'Infus terpasang rapi',
                    'Pasien nyaman',
                    'Lingkungan bersih',
                    'Menanyakan respon klien',
                    'Mengakhiri pemeriksaan dengan mengucapkan salam penutup',
                    'Dokumentasi',
                ],
            ],
        ];
    }

    // ── SOP Pemeriksaan Golongan Darah (TLM) ─────────────────────────────

    private static function sopPemeriksaanGolonganDarah(): array
    {
        return [
            // A. Tahap Pra Analitik → kategori: persiapan (bobot 30%)
            [
                'name'          => 'Tahap Pra Analitik – Pemeriksaan Golongan Darah',
                'kategori'      => KriteriaPenilaian::KATEGORI_PERSIAPAN,
                'weight'        => 30,
                'description'   => 'Persiapan alat, bahan, dan konfirmasi pemeriksaan pasien sebelum analisis golongan darah.',
                'mata_praktik'  => 'Pemeriksaan Golongan Darah',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Konfirmasi pemeriksaan pasien',
                    'Menyiapkan plate golda/slide golda',
                    'Menyiapkan batang pengaduk',
                    'Menyiapkan tissue',
                    'Memakai handscoon',
                    'Memakai masker',
                    'Menyiapkan pipet',
                    'Menyiapkan darah EDTA',
                    'Menyiapkan reagent golda: Antisera A',
                    'Menyiapkan reagent golda: Antisera B',
                    'Menyiapkan reagent golda: Antisera AB',
                    'Menyiapkan reagent golda: Antisera D (Rhesus)',
                ],
            ],

            // B. Tahap Analitik → kategori: pelaksanaan (bobot 45%)
            [
                'name'          => 'Tahap Analitik – Pemeriksaan Golongan Darah',
                'kategori'      => KriteriaPenilaian::KATEGORI_PELAKSANAAN,
                'weight'        => 45,
                'description'   => 'Pelaksanaan prosedur pemeriksaan golongan darah menggunakan metode slide/plate.',
                'mata_praktik'  => 'Pemeriksaan Golongan Darah',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Meneteskan darah pada plate/slide golda ke masing-masing lingkaran',
                    'Meneteskan 1 tetes reagen ke masing-masing lingkaran sesuai antisera golda (A, B, AB, D)',
                    'Mengaduk dengan batang pengaduk sampai tercampur antara reagen dan darah',
                    'Menggoyangkan plate/slide golda kurang lebih 1 menit',
                    'Memperhatikan aglutinasi yang mungkin terjadi di tiap lingkaran',
                ],
            ],

            // C. Pasca Analitik → kategori: hasil (bobot 25%)
            [
                'name'          => 'Pasca Analitik – Interpretasi Hasil Golongan Darah',
                'kategori'      => KriteriaPenilaian::KATEGORI_HASIL,
                'weight'        => 25,
                'description'   => 'Interpretasi hasil aglutinasi untuk menentukan golongan darah ABO dan status Rhesus.',
                'mata_praktik'  => 'Pemeriksaan Golongan Darah',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Golongan darah A: adanya aglutinasi pada lingkaran A dan AB',
                    'Golongan darah B: adanya aglutinasi pada lingkaran B dan AB',
                    'Golongan darah O: tidak terjadi aglutinasi pada lingkaran A, B, dan AB',
                    'Golongan darah AB: adanya aglutinasi pada lingkaran A, B, dan AB',
                    'Rhesus positif: aglutinasi pada lingkaran D',
                    'Rhesus negatif: tidak terjadi aglutinasi pada lingkaran D',
                ],
            ],
        ];
    }

    // ── SOP Peracikan dan Formulasi Obat (Farmasi) ────────────────────────

    private static function sopPeracikanObatFarmasi(): array
    {
        return [
            // A. Tahap Pra Interaksi (20 item) → kategori: persiapan (bobot 25%)
            [
                'name'          => 'Tahap Pra Interaksi – Peracikan dan Formulasi Obat',
                'kategori'      => KriteriaPenilaian::KATEGORI_PERSIAPAN,
                'weight'        => 25,
                'description'   => 'Persiapan sebelum peracikan: pengecekan resep/formula, verifikasi identitas pasien, persiapan alat, bahan, dan tempat kerja.',
                'mata_praktik'  => 'Peracikan dan Formulasi Obat',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Mengecek resep atau formula obat yang akan diracik',
                    'Memeriksa kelengkapan dan kejelasan resep atau formula',
                    'Memeriksa identitas pasien pada resep apabila tersedia',
                    'Memeriksa nama obat, bentuk sediaan, kekuatan, jumlah, dan aturan pakai',
                    'Memahami tujuan dan prosedur peracikan atau formulasi obat',
                    'Mencuci tangan sesuai prosedur',
                    'Menggunakan pakaian kerja dan alat pelindung diri sesuai kebutuhan',
                    'Menyiapkan tempat kerja yang bersih, rapi, dan aman',
                    'Menyiapkan alat dan bahan yang diperlukan',
                    'Menyiapkan mortir dan stamper',
                    'Menyiapkan sudip atau spatula',
                    'Menyiapkan kertas perkamen atau wadah penampung bahan',
                    'Menyiapkan timbangan sesuai kebutuhan',
                    'Menyiapkan gelas ukur, beaker glass, atau alat ukur lain sesuai formula',
                    'Menyiapkan bahan obat dan bahan tambahan sesuai resep atau formula',
                    'Memeriksa nama, kualitas, kondisi, dan tanggal kedaluwarsa bahan',
                    'Memastikan alat yang digunakan bersih dan layak pakai',
                    'Menyiapkan etiket, wadah obat, dan kemasan yang sesuai',
                    'Menyiapkan alat pembersih dan tempat pembuangan limbah',
                    'Memastikan perhitungan dosis dan jumlah bahan telah diperiksa sebelum peracikan',
                ],
            ],

            // B. Tahap Orientasi (9 item) → kategori: sikap (bobot 15%)
            [
                'name'          => 'Tahap Orientasi – Peracikan dan Formulasi Obat',
                'kategori'      => KriteriaPenilaian::KATEGORI_SIKAP,
                'weight'        => 15,
                'description'   => 'Komunikasi dan orientasi kepada pasien atau penguji sebelum memulai tindakan peracikan dan formulasi obat.',
                'mata_praktik'  => 'Peracikan dan Formulasi Obat',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Memberikan salam dan memperkenalkan diri kepada pasien atau penguji sesuai situasi praktik',
                    'Memastikan identitas pasien sesuai resep apabila simulasi melibatkan pasien',
                    'Menjelaskan tujuan dan prosedur peracikan atau formulasi obat',
                    'Menjelaskan jenis sediaan obat yang akan dibuat',
                    'Menjelaskan aturan penggunaan obat secara umum sesuai resep atau skenario',
                    'Memberikan kesempatan kepada pasien atau penguji untuk bertanya',
                    'Menunjukkan sikap sopan, ramah, dan profesional',
                    'Memastikan resep atau formula telah dipahami sebelum tindakan dimulai',
                    'Memastikan waktu praktik dan tahapan kerja telah disepakati',
                ],
            ],

            // C. Tahap Kerja (32 item) → kategori: pelaksanaan (bobot 45%)
            [
                'name'          => 'Tahap Kerja – Peracikan dan Formulasi Obat',
                'kategori'      => KriteriaPenilaian::KATEGORI_PELAKSANAAN,
                'weight'        => 45,
                'description'   => 'Pelaksanaan teknik peracikan dan formulasi obat secara sistematis sesuai SOP meliputi penimbangan, pencampuran, pengemasan, dan pemberian etiket.',
                'mata_praktik'  => 'Peracikan dan Formulasi Obat',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Menjaga kebersihan dan kerapian tempat kerja selama proses peracikan',
                    'Mencuci tangan sebelum memulai proses peracikan',
                    'Menggunakan alat pelindung diri sesuai jenis bahan dan sediaan',
                    'Memeriksa kembali resep atau formula sebelum mengambil bahan',
                    'Menghitung kebutuhan bahan sesuai formula dengan benar',
                    'Menimbang bahan obat menggunakan timbangan yang sesuai',
                    'Menimbang bahan tambahan sesuai perhitungan formula',
                    'Memastikan setiap bahan yang ditimbang sesuai dengan nama dan jumlah yang diperlukan',
                    'Menggunakan alat peracikan sesuai fungsi dan prosedur',
                    'Memasukkan bahan ke dalam mortir atau wadah pencampuran sesuai urutan yang tepat',
                    'Menghaluskan bahan menggunakan mortir dan stamper apabila diperlukan',
                    'Melakukan pengayakan apabila diperlukan sesuai karakteristik bahan',
                    'Mencampur bahan secara bertahap dan merata',
                    'Menerapkan prinsip pengenceran geometris untuk bahan tertentu apabila diperlukan',
                    'Menghindari kehilangan bahan selama proses peracikan',
                    'Memastikan tidak terjadi kontaminasi silang antarbahan atau sediaan',
                    'Melakukan proses formulasi sesuai jenis sediaan yang dibuat',
                    'Menggunakan pelarut atau bahan pembawa yang sesuai dengan formula',
                    'Melakukan pengadukan atau pencampuran sesuai prosedur sediaan',
                    'Memperhatikan homogenitas campuran atau keseragaman sediaan',
                    'Memeriksa karakteristik fisik sediaan seperti warna, bau, bentuk, dan konsistensi sesuai jenis sediaan',
                    'Memastikan tidak terdapat benda asing atau kontaminan pada sediaan',
                    'Menempatkan hasil racikan atau formulasi ke dalam wadah yang sesuai',
                    'Mengemas sediaan obat dengan benar dan rapi',
                    'Memasang etiket pada wadah obat sesuai resep atau formula',
                    'Menuliskan nama obat, aturan pakai, jumlah, dan informasi lain yang diperlukan pada etiket',
                    'Memeriksa kembali kesesuaian hasil racikan dengan resep atau formula',
                    'Melakukan pemeriksaan akhir terhadap berat, volume, atau jumlah sediaan sesuai jenis obat',
                    'Membersihkan alat yang telah digunakan sesuai prosedur',
                    'Mengembalikan alat dan bahan ke tempat semula',
                    'Membuang limbah sesuai jenis dan ketentuan keselamatan kerja',
                    'Mencuci tangan setelah menyelesaikan proses peracikan',
                ],
            ],

            // D. Tahap Terminasi/Evaluasi (16 item) → kategori: hasil (bobot 15%)
            [
                'name'          => 'Tahap Terminasi (Evaluasi) – Peracikan dan Formulasi Obat',
                'kategori'      => KriteriaPenilaian::KATEGORI_HASIL,
                'weight'        => 15,
                'description'   => 'Evaluasi kesesuaian hasil peracikan, kualitas sediaan, kebersihan tempat kerja, dan dokumentasi hasil praktik.',
                'mata_praktik'  => 'Peracikan dan Formulasi Obat',
                'tingkat_kelas' => KriteriaPenilaian::TINGKAT_XI,
                'sop_checklist' => [
                    'Memastikan hasil racikan atau formulasi sesuai dengan resep atau formula',
                    'Memastikan kesesuaian nama dan jumlah bahan yang digunakan',
                    'Memastikan homogenitas dan kualitas fisik sediaan sesuai jenisnya',
                    'Memastikan tidak terdapat kontaminasi pada hasil racikan',
                    'Memastikan wadah obat dalam keadaan baik dan tertutup',
                    'Memastikan etiket terpasang dengan benar dan mudah dibaca',
                    'Memastikan informasi aturan pakai sesuai dengan resep atau formula',
                    'Memastikan hasil racikan dikemas dengan rapi dan aman',
                    'Memastikan tempat kerja dalam keadaan bersih',
                    'Memastikan seluruh alat telah dibersihkan dan disimpan dengan benar',
                    'Memastikan limbah praktik telah dibuang sesuai prosedur',
                    'Menanyakan atau mengevaluasi kendala yang dialami selama praktik',
                    'Menjelaskan hasil evaluasi praktik kepada penguji',
                    'Melakukan refleksi terhadap ketepatan perhitungan dan teknik peracikan',
                    'Melakukan dokumentasi hasil peracikan atau formulasi obat',
                    'Mengakhiri praktik dengan mengucapkan salam atau berpamitan kepada penguji',
                ],
            ],
        ];
    }
}
