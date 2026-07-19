<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UbSurveyResponse extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'ub_survey_responses';

    protected $fillable = [
        'user_id', 'tahun', 'survey_section', 'is_completed', 'last_saved_at',
        // Blok 1-A
        'provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa',
        'nama_perusahaan', 'nama_komersial', 'alamat_perusahaan', 'rt', 'rw',
        'nomor_telepon', 'kode_pos', 'email_perusahaan', 'homepage', 'nomor_hp',
        'jenis_kawasan', 'nama_kawasan',
        'has_nib', 'nib', 'alasan_tidak_nib',
        'status_badan_usaha', 'is_koperasi_kdkmp', 'jenis_koperasi', 'has_laporan_keuangan',
        'nama_pengusaha', 'jenis_kelamin', 'umur', 'nik',
        'blok1a_completed',
        // Blok 1-B
        'kegiatan_utama',
        'produksi_di_lokasi', 'layanan_makan_minum', 'penjualan_barang', 'aktivitas_jasa_pertanian',
        'lokasi_usaha', 'input_produksi', 'proses_produksi', 'produk_utama',
        'kode_kbli', 'kategori_lapangan_usaha', 'klasifikasi_akomodasi',
        'jaringan_usaha', 'jumlah_cabang',
        'kp_nama', 'kp_alamat', 'kp_email', 'kp_negara', 'kp_provinsi', 'kp_kabkota',
        'uses_internet',
        'internet_pesanan', 'internet_produksi', 'internet_distribusi',
        'internet_beli_bahan_baku', 'internet_promosi', 'internet_lainnya',
        'uses_teknologi_digital',
        'produk_ramah_lingkungan', 'uses_input_lingkungan',
        'uses_karya_seni',
        'blok1b_completed',
        // Blok 1-C
        'sertifikat_halal', 'jumlah_produk_halal_bpjph', 'jumlah_produk_belum_halal_bpjph',
        'izin_edar', 'jumlah_produk_izin_edar_bpom', 'jumlah_produk_tanpa_izin_edar_bpom',
        'bermitra_kdkmp', 'terlibat_mbg',
        'ekspor_impor_barang', 'ekspor_impor_jasa',
        'blok1c_completed',
        // Blok 1-D
        'pekerja_laki', 'pekerja_perempuan', 'total_pekerja',
        'tahun_beroperasi',
        'pengeluaran_upah_gaji', 'pengeluaran_biaya_produksi', 'pengeluaran_pembelian_barang',
        'pengeluaran_operasional', 'pengeluaran_nonoperasional', 'total_pengeluaran',
        'nilai_produksi_barang_jasa', 'pendapatan_lainnya', 'total_nilai_produksi',
        'persen_pendapatan_online',
        'nilai_aset_tanah_bangunan', 'nilai_aset_lainnya', 'nilai_total_aset',
        'range_total_aset', 'luas_tanah',
        'modal_pribadi', 'modal_nonprofit', 'modal_korporasi_publik',
        'modal_korporasi_nonpublik', 'modal_pemerintah', 'modal_asing',
        'blok1d_completed',
        // Blok 2
        'catatan', 'blok2_completed',
        // Blok 3
        'ppl_nama', 'ppl_nip', 'ppl_telepon', 'ppl_email', 'ppl_tanggal',
        'pml_nama', 'pml_nip', 'pml_telepon', 'pml_email', 'pml_tanggal',
        'resp_nama', 'resp_nip', 'resp_telepon', 'resp_email', 'resp_tanggal',
        'blok3_completed',
    ];

    protected $casts = [
        'is_completed'       => 'boolean',
        'last_saved_at'      => 'datetime',
        'blok1a_completed'   => 'boolean',
        'blok1b_completed'   => 'boolean',
        'blok1c_completed'   => 'boolean',
        'blok1d_completed'   => 'boolean',
        'blok2_completed'    => 'boolean',
        'blok3_completed'    => 'boolean',
        'ppl_tanggal'        => 'date',
        'pml_tanggal'        => 'date',
        'resp_tanggal'       => 'date',
    ];

    public function updateWithAutoSave(array $data): static
    {
        $this->fill($data);
        $this->last_saved_at = now();
        $this->save();
        return $this;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getOrCreateForUser(string $userId, int $tahun, string $section): static
    {
        $response = static::firstOrCreate(
            ['user_id' => $userId, 'tahun' => $tahun],
            ['survey_section' => $section]
        );
        $response->update(['survey_section' => $section]);
        return $response;
    }

    public function isBlok1aComplete(): bool
    {
        return (bool) $this->blok1a_completed;
    }

    public function isBlok1bComplete(): bool
    {
        return (bool) $this->blok1b_completed;
    }

    public function isBlok1cComplete(): bool
    {
        return (bool) $this->blok1c_completed;
    }

    public function isBlok1dComplete(): bool
    {
        return (bool) $this->blok1d_completed;
    }

    public function isBlok2Complete(): bool
    {
        return (bool) $this->blok2_completed;
    }

    public function completionPercent(): int
    {
        $blocks = [
            $this->blok1a_completed,
            $this->blok1b_completed,
            $this->blok1c_completed,
            $this->blok1d_completed,
            $this->blok2_completed,
            $this->blok3_completed,
        ];
        $done = count(array_filter($blocks));
        return (int) round(($done / count($blocks)) * 100);
    }
}
