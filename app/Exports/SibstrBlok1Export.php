<?php

namespace App\Exports;

use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per SIBSTR submission (a user × year × period), carrying the full
 * Blok I identity answers plus the period and completion columns BPS monitors.
 *
 * "Selesai" means different columns per period type: a Tahunan record is only
 * finished once Blok VI sets annual_survey_status = FINISH_SURVEY, while a
 * Triwulanan record uses is_completed — the same rule the index page shows.
 */
class SibstrBlok1Export extends SurveyMonitoringExport
{
    public function title(): string
    {
        return 'Data SIBSTR';
    }

    /** Keep No + period + company name in view while scrolling right. */
    protected function freezeCell(): string
    {
        return 'D2';
    }

    public function headings(): array
    {
        return [
            'No',
            'Periode',
            'Nama Perusahaan',
            'Tahun',
            'Triwulan',
            'KIP',
            'IDSBR',
            'Alamat Pabrik',
            'Kabupaten/Kota',
            'Telepon/Fax',
            'Penghubung',
            'Email',
            'Homepage',
            'Tahun Mulai Beroperasi',
            'NIB',
            'Jenis Kawasan',
            'Nama Kawasan',
            'Nama Pengelola Kawasan',
            'Legalisasi — Nama',
            'Legalisasi — Jabatan',
            'Legalisasi — Jenis Kelamin',
            'Legalisasi — NIK',
            'Blok I Terisi',
            'Blok I Lengkap?',
            'Blok Terakhir Diisi',
            'Status Survei',
            'Nama Pengguna',
            'Email Pengguna',
            'Tanggal Dibuat',
            'Terakhir Diperbarui',
        ];
    }

    protected function row(Model $record, int $no): array
    {
        /** @var SurveyResponse $record */
        $triwulan  = (int) ($record->triwulan ?? 0);
        $isTahunan = $triwulan === 0;

        $finished = $isTahunan
            ? $record->annual_survey_status === 'FINISH_SURVEY'
            : (bool) $record->is_completed;

        $required = count(SurveyResponse::blok1RequiredFields());

        return [
            $no,
            $isTahunan ? 'Tahunan ' . $record->tahun : 'Triwulan ' . $triwulan . ' ' . $record->tahun,
            $this->val($record->nama_perusahaan),
            $this->val($record->tahun),
            $isTahunan ? '' : $triwulan,
            $this->val($record->kip),
            $this->val($record->idsbr),
            $this->val($record->alamat_pabrik),
            $this->val($record->kabupaten_kota),
            $this->val($record->telepon_fax),
            $this->val($record->penghubung),
            $this->val($record->email),
            $this->val($record->homepage),
            $this->val($record->tahun_mulai_beroperasi),
            $this->val($record->nib),
            $this->label($record->jenis_kawasan, SurveyResponse::getJenisKawasanOptions()),
            $this->val($record->nama_kawasan),
            $this->val($record->nama_pengelola_kawasan),
            $this->val($record->legalisasi_nama),
            $this->val($record->legalisasi_jabatan),
            $this->label($record->legalisasi_jenis_kelamin, [
                'laki_laki' => 'Laki-laki',
                'perempuan' => 'Perempuan',
            ]),
            $this->val($record->legalisasi_nik),
            $record->blok1FilledCount() . '/' . $required,
            $this->flag($record->isBlok1Complete()),
            $this->val($record->survey_section),
            $finished ? 'Selesai' : 'Dalam Proses',
            $this->val($record->user->name ?? null),
            $this->val($record->user->email ?? null),
            $this->datetime($record->created_at),
            $this->datetime($record->updated_at),
        ];
    }
}
