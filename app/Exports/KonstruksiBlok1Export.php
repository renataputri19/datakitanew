<?php

namespace App\Exports;

use App\Models\KonstruksiSurveyResponse;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per Survei Konstruksi submission: the Blok I identity answers, the
 * per-blok completion flags, and the headline figures BPS follows up on.
 *
 * The aggregates (jumlah proyek, nilai kontrak, nilai konstruksi diselesaikan,
 * Blok IX totals) are recomputed from the stored answers rather than read off a
 * column — Blok IX is derived, so there is nothing stored to read. They come
 * out as numbers so the sheet can sum and sort them; every figure is in
 * thousands of rupiah, matching the questionnaire.
 */
class KonstruksiBlok1Export extends SurveyMonitoringExport
{
    public function title(): string
    {
        return 'Data Survei Konstruksi';
    }

    /** Keep No + company name in view while scrolling right. */
    protected function freezeCell(): string
    {
        return 'C2';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Perusahaan',
            'Instrumen',
            'Periode',
            'Tahun',
            'KIP',
            'NUS',
            'Provinsi',
            'Kabupaten/Kota',
            'Kecamatan',
            'Desa/Kelurahan',
            'Alamat Perusahaan',
            'RT',
            'RW',
            'Kode Pos',
            'Telepon',
            'Website',
            'Email Perusahaan',
            'Alamat Operasional',
            'Punya NIB?',
            'NIB',
            'Nama Penanggung Jawab',
            'No. HP Penanggung Jawab',
            'Badan Hukum/Usaha',
            'Status Penanaman Modal',
            'Kualifikasi',
            // Quarterly-only preamble (blank on annual rows)
            'SKTR: Status Perusahaan',
            'SKTR: Kesediaan Responden',
            'SKTR: Keberadaan Proyek',
            'SKTR: Kab/Kota Baru (pindah)',
            // Blok flags. blok1–blok3 mean different blocks in the two
            // instruments, so the header names both readings.
            'Blok I (Keterangan Umum)',
            'Blok II (Tenaga Kerja)',
            'Blok III (Modal / Realisasi Fisik)',
            'Blok IV (Pengeluaran Operasional)',
            'Blok V (Barang Modal)',
            'Blok VI (Pekerjaan Konstruksi)',
            'Blok VII (Bahan/Material)',
            'Blok VIII (Pendapatan Lain)',
            'Blok IX (Ringkasan)',
            'Blok X / Penutup',
            // Headline figures — annual
            'Jumlah Pekerja (orang)',
            'Balas Jasa (000 Rp)',
            'Jumlah Proyek',
            'Nilai Kontrak (000 Rp)',
            'Bahan/Material Proyek (000 Rp)',
            'Upah Pekerja Harian (000 Rp)',
            'Nilai Konstruksi Diselesaikan (000 Rp)',
            'Pengeluaran Operasional (000 Rp)',
            'Pendapatan Lain (000 Rp)',
            'Jumlah Pengeluaran A (000 Rp)',
            'Jumlah Pendapatan B (000 Rp)',
            'Keuntungan thd Pengeluaran (%)',
            // Headline figures — quarterly. The captions roll per quarter, so
            // the header names the slot and the value columns carry the label.
            'SKTR: Triwulan Sebelumnya',
            'SKTR: Triwulan Berjalan',
            'SKTR: Pekerja TW Sebelumnya (orang)',
            'SKTR: Pekerja TW Berjalan (orang)',
            'SKTR: Balas Jasa TW Sebelumnya (000 Rp)',
            'SKTR: Balas Jasa TW Berjalan (000 Rp)',
            'SKTR: Upah Harian TW Berjalan (000 Rp)',
            'SKTR: Nilai Konstruksi TW Berjalan (000 Rp)',
            // Blok X
            'Metode Pendataan Diinginkan',
            'Nama Pemberi Jawaban',
            'Jabatan Pemberi Jawaban',
            'HP Pemberi Jawaban',
            'Email Pemberi Jawaban',
            'Catatan',
            // Meta
            'Kemajuan (%)',
            'Status Survei',
            'Nama Pengguna',
            'Email Pengguna',
            'Tanggal Dibuat',
            'Terakhir Diperbarui',
        ];
    }

    protected function row(Model $record, int $no): array
    {
        /** @var KonstruksiSurveyResponse $record */
        $isTw      = $record->isTriwulanan();
        $proyek    = $record->totalProyek();
        $ringkasan = $record->ringkasan();
        $twProyek  = $record->totalProyekTw();
        $twLabels  = $isTw ? $record->quarterLabels() : [];
        $pekerja   = is_array($record->tw_pekerja) ? $record->tw_pekerja : [];
        $balasJasa = is_array($record->tw_balas_jasa) ? $record->tw_balas_jasa : [];

        /** Annual-only figure: blank on a quarterly row, so blanks stay meaningful. */
        $annual = fn ($v) => $isTw ? '' : $v;
        /** Quarterly-only figure. */
        $quarterly = fn ($v) => $isTw ? $v : '';

        return [
            $no,
            $this->val($record->nama_perusahaan),
            $isTw ? 'SKTR (Triwulanan)' : 'SKTH (Tahunan)',
            \App\Support\KonstruksiBlokPath::periodLabel($record->tahun, $record->triwulan),
            $this->val($record->tahun),
            $this->val($record->kip),
            $this->val($record->nus),
            $this->val($record->provinsi),
            $this->val($record->kabupaten_kota),
            $this->val($record->kecamatan),
            $this->val($record->kelurahan_desa),
            $this->val($record->alamat_perusahaan),
            $this->val($record->rt),
            $this->val($record->rw),
            $this->val($record->kode_pos),
            $this->val($record->nomor_telepon),
            $this->val($record->website),
            $this->val($record->email_perusahaan),
            $this->val($record->alamat_operasional),
            $this->label($record->punya_nib, ['ya' => 'Ya', 'tidak' => 'Tidak']),
            $this->val($record->nib),
            $this->val($record->nama_pengusaha),
            $this->val($record->hp_pengusaha),
            $this->label($record->badan_hukum, KonstruksiSurveyResponse::BADAN_HUKUM),
            $this->label($record->status_penanaman_modal, KonstruksiSurveyResponse::STATUS_MODAL),
            $this->label($record->kualifikasi, KonstruksiSurveyResponse::KUALIFIKASI),

            $quarterly($this->label($record->status_perusahaan, KonstruksiSurveyResponse::STATUS_PERUSAHAAN)),
            $quarterly($this->label($record->kesediaan_responden, KonstruksiSurveyResponse::KESEDIAAN_RESPONDEN)),
            $quarterly($this->label($record->keberadaan_proyek, KonstruksiSurveyResponse::KEBERADAAN_PROYEK)),
            $quarterly($this->val($record->pindah_kabupaten_kota)),

            // Blok flags follow whichever instrument this row belongs to; the
            // blocks that instrument does not have come out blank.
            $this->flag($isTw ? $record->isBlok1TwComplete() : $record->isBlok1Complete()),
            $this->flag($isTw ? $record->isBlok2TwComplete() : $record->isBlok2Complete()),
            $this->flag($isTw ? $record->isBlok3TwComplete() : $record->isBlok3Complete()),
            $annual($this->flag($record->isBlok4Complete())),
            $annual($this->flag($record->isBlok5Complete())),
            $annual($this->flag($record->isBlok6Complete())),
            $annual($this->flag($record->isBlok7Complete())),
            $annual($this->flag($record->isBlok8Complete())),
            $annual($this->flag($record->isBlok9Complete())),
            $this->flag($isTw ? $record->isPenutupComplete() : $record->isBlok10Complete()),

            $annual($record->totalPekerja()),
            $annual($record->totalBalasJasa()),
            // Both instruments report projects, so this column is filled for
            // either — it is the figure BPS chases up most often.
            $isTw ? count($record->proyekTwRows()) : count($record->proyekRows()),
            $isTw ? $twProyek['nilai_kontrak'] : $proyek['nilai_kontrak'],
            $annual($proyek['bahan_perusahaan'] + $proyek['bahan_pemilik']),
            $annual($proyek['upah_harian']),
            $annual($proyek['nilai_konstruksi']),
            $annual($record->totalPengeluaran()),
            $annual($record->totalPendapatanLain()),
            $annual($ringkasan['total_a']),
            $annual($ringkasan['total_b']),
            $annual($ringkasan['margin_pct'] === null ? '' : round($ringkasan['margin_pct'], 2)),

            $quarterly($twLabels['prev']['short'] ?? ''),
            $quarterly($twLabels['cur']['short'] ?? ''),
            $quarterly(KonstruksiSurveyResponse::n($pekerja['prev'] ?? null)),
            $quarterly(KonstruksiSurveyResponse::n($pekerja['cur'] ?? null)),
            $quarterly(KonstruksiSurveyResponse::n($balasJasa['prev'] ?? null)),
            $quarterly(KonstruksiSurveyResponse::n($balasJasa['cur'] ?? null)),
            $quarterly($twProyek['upah_ini']),
            $quarterly($twProyek['nilai_konstruksi']),

            $this->label($record->metode_pendataan, KonstruksiSurveyResponse::METODE_PENDATAAN),
            $this->val($record->pj_nama),
            $this->val($record->pj_jabatan),
            $this->val($record->pj_hp),
            $this->val($record->pj_email),
            $this->val($record->catatan),

            $record->completionPercent(),
            $record->is_completed ? 'Selesai' : 'Dalam Proses',
            $this->val($record->user->name ?? null),
            $this->val($record->user->email ?? null),
            $this->datetime($record->created_at),
            $this->datetime($record->updated_at),
        ];
    }
}
