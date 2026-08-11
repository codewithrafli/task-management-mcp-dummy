<?php

namespace Database\Seeders;

use App\Models\Policy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'title' => 'Aturan Estimasi Task',
                'source' => 'SOP-DEV-001',
                'content' => 'Setiap task wajib memiliki estimasi sebelum masuk sprint. Estimasi menggunakan satuan story point (1, 2, 3, 5, 8). Task dengan estimasi di atas 8 harus dipecah menjadi task yang lebih kecil. Developer wajib memberikan estimasi dalam sesi planning, bukan saat task sudah berjalan.',
            ],
            [
                'title' => 'Batas Maksimum Task Per Developer',
                'source' => 'SOP-DEV-002',
                'content' => 'Setiap developer maksimal memegang 3 task dengan status in_progress secara bersamaan. Jika ingin mengambil task baru, task yang sedang berjalan harus diselesaikan atau di-blok terlebih dahulu. Tim lead berhak memindahkan task jika developer sudah melebihi batas.',
            ],
            [
                'title' => 'Definition of Done',
                'source' => 'SOP-DEV-003',
                'content' => 'Task dianggap selesai (done) jika: (1) kode sudah di-review oleh minimal 1 rekan, (2) semua unit test lulus, (3) sudah di-deploy ke staging dan tidak ada bug kritikal, (4) dokumentasi diperbarui jika diperlukan. Task tidak boleh dipindah ke done sebelum semua kriteria terpenuhi.',
            ],
            [
                'title' => 'Kebijakan Sprint Planning',
                'source' => 'SOP-PM-001',
                'content' => 'Sprint berlangsung 2 minggu. Sprint planning dilakukan setiap Senin di awal sprint mulai pukul 09.00. Backlog harus sudah dipersiapkan Product Owner minimal H-1. Kapasitas tim dihitung berdasarkan jumlah hari kerja dikurangi hari libur. Tidak boleh menambah task baru di tengah sprint tanpa persetujuan PO.',
            ],
            [
                'title' => 'Prosedur Eskalasi Bug Kritikal',
                'source' => 'SOP-QA-001',
                'content' => 'Bug kritikal adalah bug yang menyebabkan sistem tidak bisa diakses atau data hilang/rusak. Jika ditemukan bug kritikal: (1) segera buat task dengan label "critical", (2) assign ke lead developer, (3) notifikasi manajer lewat Slack channel #incidents, (4) wajib diselesaikan dalam 4 jam kerja. Bug non-kritikal diselesaikan sesuai urutan prioritas sprint.',
            ],
        ];

        foreach ($policies as $data) {
            Policy::firstOrCreate(['source' => $data['source']], $data);
        }
    }
}
