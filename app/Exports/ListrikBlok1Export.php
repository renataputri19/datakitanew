<?php

namespace App\Exports;

use App\Models\ListrikSurveyResponse;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per Survei Listrik submission: the Blok I identity answers plus the
 * block flags.
 *
 * Listrik's Blok II is a monthly grid rather than a set of questions, so the
 * sheet reports how many of the available months have been started ("9/18")
 * next to the strict "grid lengkap" flag — a half-filled grid is the normal
 * in-progress state here and the month count is what BPS follows up on.
 */
class ListrikBlok1Export extends SurveyMonitoringExport
{
    private const JENIS_KELAMIN = ['1' => 'Laki-laki', '2' => 'Perempuan'];

    public function title(): string
    {
        return 'Data Survei Listrik';
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
            'Jenis Pembangkit',
            'Daya Terpasang (KW)',
            'Nama Pengusaha',
            'Jenis Kelamin',
            'Umur',
            'NIK',
            'Blok I (Identitas)',
            'Blok II (Grid Bulanan)',
            'Bulan Terisi',
            'Grid Bulanan Lengkap?',
            'Blok III (Catatan)',
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
        /** @var ListrikSurveyResponse $record */
        $totalMonths = count(ListrikSurveyResponse::availableMonthKeys());

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
            $this->val($record->jenis_pembangkit),
            $this->val($record->daya_terpasang_kw),
            $this->val($record->nama_pengusaha),
            $this->label($record->jenis_kelamin, self::JENIS_KELAMIN),
            $this->val($record->umur),
            $this->val($record->nik),
            $this->flag($record->blok1_completed),
            $this->flag($record->blok2_completed),
            $record->filledMonthCount() . '/' . $totalMonths,
            $record->isBlok2GridComplete() ? 'Lengkap' : 'Belum',
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
