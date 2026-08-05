<?php

namespace App\Exports;

use App\Models\UbSurveyResponse;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per UB (SE2026-L.UB) submission.
 *
 * UB's "Blok 1" spans four sub-blocks; only Blok 1-A is identity, so that is
 * what the sheet carries in full. Blok 1-B/1-C/1-D and Blok 2/3 appear as their
 * completion flags — enough to see who is stuck where without a 90-column file.
 *
 * Kode KBLI (Q9g) is the one exception: it lives in Blok 1-B but sits next to
 * the other identity codes here, because BPS classifies respondents by it.
 */
class UbBlok1Export extends SurveyMonitoringExport
{
    /** Coded answers as they are labelled on the questionnaire. */
    private const JENIS_KAWASAN = [
        '1'  => 'Kawasan Ekonomi Khusus (KEK)',
        '2'  => 'Kawasan Industri (KI)',
        '3'  => 'Stasiun',
        '4'  => 'Bandara',
        '5'  => 'Pelabuhan',
        '6'  => 'Terminal',
        '7'  => 'Rest area jalan tol',
        '8'  => 'Kawasan sentra ekonomi perdesaan/kelurahan',
        '9'  => 'Kawasan usaha lainnya',
        '10' => 'Di luar kawasan',
    ];

    private const STATUS_BADAN_USAHA = [
        '1'  => 'Perseroan (PT/NV/Persero/Tbk/Perseroan Daerah/Perorangan)',
        '2'  => 'Yayasan',
        '3'  => 'Koperasi',
        '4'  => 'Dana Pensiun',
        '5'  => 'Perum/Perumda',
        '6'  => 'BUM Desa',
        '7'  => 'Persekutuan Komanditer (CV)',
        '8'  => 'Persekutuan Firma (Fa)',
        '9'  => 'Persekutuan Perdata (Maatschap)',
        '10' => 'Kantor Perwakilan Luar Negeri',
        '11' => 'Badan Usaha Luar Negeri',
        '12' => 'Badan Usaha Lainnya (BLU, PTN-BH dll)',
        '13' => 'Bukan Badan Usaha',
    ];

    private const YA_TIDAK = ['1' => 'Ya', '2' => 'Tidak'];

    private const JENIS_KELAMIN = ['1' => 'Laki-laki', '2' => 'Perempuan'];

    public function title(): string
    {
        return 'Data Survei UB';
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
            'Nama Komersial',
            'Tahun',
            'Provinsi',
            'Kabupaten/Kota',
            'Kecamatan',
            'Kelurahan/Desa',
            'Alamat',
            'RT',
            'RW',
            'Kode Pos',
            'Telepon',
            'Nomor HP',
            'Email Perusahaan',
            'Homepage',
            'Jenis Kawasan',
            'Nama Kawasan',
            'Memiliki NIB',
            'NIB',
            'Kode KBLI',
            'Status Badan Usaha',
            'Nama Pengusaha',
            'Jenis Kelamin',
            'Umur',
            'NIK',
            'Blok 1-A',
            'Blok 1-B',
            'Blok 1-C',
            'Blok 1-D',
            'Blok 2',
            'Blok 3',
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
        /** @var UbSurveyResponse $record */
        return [
            $no,
            $this->val($record->nama_perusahaan),
            $this->val($record->nama_komersial),
            $this->val($record->tahun),
            $this->val($record->provinsi),
            $this->val($record->kabupaten_kota),
            $this->val($record->kecamatan),
            $this->val($record->kelurahan_desa),
            $this->val($record->alamat_perusahaan),
            $this->val($record->rt),
            $this->val($record->rw),
            $this->val($record->kode_pos),
            $this->val($record->nomor_telepon),
            $this->val($record->nomor_hp),
            $this->val($record->email_perusahaan),
            $this->val($record->homepage),
            $this->label($record->jenis_kawasan, self::JENIS_KAWASAN),
            $this->val($record->nama_kawasan),
            $this->label($record->has_nib, self::YA_TIDAK),
            $this->val($record->nib),
            $this->val($record->kode_kbli),
            $this->label($record->status_badan_usaha, self::STATUS_BADAN_USAHA),
            $this->val($record->nama_pengusaha),
            $this->label($record->jenis_kelamin, self::JENIS_KELAMIN),
            $this->val($record->umur),
            $this->val($record->nik),
            $this->flag($record->blok1a_completed),
            $this->flag($record->blok1b_completed),
            $this->flag($record->blok1c_completed),
            $this->flag($record->blok1d_completed),
            $this->flag($record->blok2_completed),
            $this->flag($record->blok3_completed),
            $record->completionPercent(),
            $record->is_completed ? 'Selesai' : 'Dalam Proses',
            $this->val($record->user->name ?? null),
            $this->val($record->user->email ?? null),
            $this->datetime($record->created_at),
            $this->datetime($record->updated_at),
        ];
    }
}
