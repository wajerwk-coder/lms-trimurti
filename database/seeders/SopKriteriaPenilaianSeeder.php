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
            self::sopPemeriksaanGolonganDarah()
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
}
