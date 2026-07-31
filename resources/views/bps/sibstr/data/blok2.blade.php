{{--
  Blok II — Keterangan Perusahaan, read-only.

  Question visibility follows $blok2Visibility, which the controllers compute
  from the same rules survey-blok2.js applies on the form, so BPS sees exactly
  the questions the responden was asked.

  Expects: $surveyResponse, $blok2Visibility, $kbliPrefix
--}}
@php
    use App\Support\SibstrFormat as F;

    $b2Tahunan = ((int) ($surveyResponse->triwulan ?? 0)) === 0;
    $b2Tahun   = (int) ($surveyResponse->tahun ?? 2025);
@endphp

<table class="kv">
    <tr>
        <td class="k">201. Kondisi Perusahaan</td>
        <td class="v">{{ F::kondisiPerusahaan($surveyResponse->kondisi_perusahaan) }}</td>
    </tr>

    @if(!empty($blok2Visibility['showAfterQ201']))
        <tr>
            <td class="k">202. Jaringan/Unit Kegiatan</td>
            <td class="v">{{ F::jaringanUnit($surveyResponse->jaringan_unit_kegiatan) }}</td>
        </tr>

        @if(!empty($blok2Visibility['showQ203']) && $b2Tahunan)
            <tr>
                <td class="k">203. Jumlah Cabang &amp; Unit Usaha</td>
                <td class="v">{{ F::plain($surveyResponse->jumlah_cabang_dan_unit_usaha) }}</td>
            </tr>
        @endif

        @if(!empty($blok2Visibility['showQ204']) && $b2Tahunan)
            <tr><td class="k">204a. Nama Kantor Pusat</td><td class="v">{{ F::plain($surveyResponse->info_kantor_pusat_nama) }}</td></tr>
            <tr><td class="k">204b. Alamat Kantor Pusat</td><td class="v">{{ F::plain($surveyResponse->info_kantor_pusat_alamat) }}</td></tr>
            <tr><td class="k">204c. Email Kantor Pusat</td><td class="v">{{ F::plain($surveyResponse->info_kantor_pusat_email) }}</td></tr>
            <tr><td class="k">204d. Negara</td><td class="v">{{ F::plain($surveyResponse->info_kantor_pusat_negara) }}</td></tr>
            <tr><td class="k">204e. Provinsi</td><td class="v">{{ F::plain($surveyResponse->info_kantor_pusat_provinsi) }}</td></tr>
            <tr><td class="k">204f. Kabupaten/Kota</td><td class="v">{{ F::plain($surveyResponse->info_kantor_pusat_kabkota) }}</td></tr>
        @endif
    @endif
</table>

@if(!empty($blok2Visibility['showQ205to211']))

    @if($b2Tahunan)
    <div class="sub">Waktu Kerja &amp; Bulan Aktif</div>
    <table class="kv">
        <tr><td class="k">205. Jumlah Bulan Aktif {{ $b2Tahun }}</td><td class="v">{{ F::plain($surveyResponse->jumlah_bulan_aktif_2025) }}</td></tr>
        <tr><td class="k">206a. Rata-rata Hari Kerja per Bulan</td><td class="v">{{ F::plain($surveyResponse->rata_hari_kerja_bulanan_2025) }}</td></tr>
        <tr><td class="k">206b. Rata-rata Jam Kerja per Hari</td><td class="v">{{ F::plain($surveyResponse->rata_jam_kerja_per_hari_2025) }}</td></tr>
        <tr><td class="k">206c. Rata-rata Shift per Hari</td><td class="v">{{ F::plain($surveyResponse->rata_shift_per_hari_2025) }}</td></tr>
    </table>
    @endif

    <div class="sub">Tenaga Kerja</div>
    <table class="kv">
        @if($b2Tahunan)
            <tr><td class="k">207a. Jumlah Seluruh Pekerja</td><td class="v num">{{ F::idr($surveyResponse->jumlah_seluruh_pekerja) }}</td></tr>
            <tr><td class="k">207b.1. Pekerja Laki-laki</td><td class="v num">{{ F::idr($surveyResponse->tenaga_kerja_laki_laki) }}</td></tr>
            <tr><td class="k">207b.2. Pekerja Perempuan</td><td class="v num">{{ F::idr($surveyResponse->tenaga_kerja_perempuan) }}</td></tr>
            <tr><td class="k">207c.1. Bukan Outsourcing — Produksi</td><td class="v num">{{ F::idr($surveyResponse->pekerja_bukan_outsourcing_produksi) }}</td></tr>
            <tr><td class="k">207c.2. Bukan Outsourcing — Lainnya</td><td class="v num">{{ F::idr($surveyResponse->pekerja_bukan_outsourcing_lainnya) }}</td></tr>
            <tr><td class="k">207d.1. Outsourcing — Produksi</td><td class="v num">{{ F::idr($surveyResponse->pekerja_outsourcing_produksi) }}</td></tr>
            <tr><td class="k">207d.2. Outsourcing — Lainnya</td><td class="v num">{{ F::idr($surveyResponse->pekerja_outsourcing_lainnya) }}</td></tr>
            <tr><td class="k">207e. Tenaga Kerja Asing</td><td class="v num">{{ F::idr($surveyResponse->tenaga_kerja_asing) }}</td></tr>
        @else
            <tr><td class="k">207. Rata-rata Tenaga Kerja (triwulan)</td><td class="v num">{{ F::idr($surveyResponse->rata_rata_tenaga_kerja) }}</td></tr>
        @endif
    </table>

    <div class="sub">Kegiatan Utama</div>
    <table class="kv">
        <tr><td class="k">208e. Uraian Kegiatan Utama</td><td class="v">{{ F::plain($surveyResponse->kegiatan_utama_perusahaan) }}</td></tr>
        <tr>
            <td class="k">208f. KBLI Utama</td>
            <td class="v">
                {{ F::plain($surveyResponse->kbli_utama) }}
                @if(!empty($kbliPrefix))
                    <span class="badge">{{ ($kbliPrefix >= 10 && $kbliPrefix <= 33) ? 'Industri' : 'Non-Industri' }}</span>
                @endif
            </td>
        </tr>
        @if($b2Tahunan)
        <tr><td class="k">208b. Produk Utama {{ $b2Tahun }}</td><td class="v">{{ F::plain($surveyResponse->produk_utama_perusahaan) }}</td></tr>
        @endif
    </table>

    @if($b2Tahunan)
    <div class="sub">Produksi &amp; Layanan</div>
    <table class="kv">
        <tr><td class="k">209. Memproduksi Barang Sendiri</td><td class="v">{{ F::yaTidak($surveyResponse->memproduksi_barang_sendiri) }}</td></tr>
        <tr><td class="k">209. Menyediakan Layanan Makan/Minum</td><td class="v">{{ F::yaTidak($surveyResponse->menyediakan_layanan_makan_minum) }}</td></tr>
        <tr><td class="k">209. Penjualan Barang Pihak Lain</td><td class="v">{{ F::yaTidak($surveyResponse->penjualan_barang_pihak_lain) }}</td></tr>
        <tr><td class="k">209. Melakukan Aktivitas Jasa</td><td class="v">{{ F::yaTidak($surveyResponse->aktivitas_jasa) }}</td></tr>
    </table>

    <div class="sub">Sertifikasi</div>
    <table class="kv">
        <tr><td class="k">a. Keamanan Produk <span class="hint">(SNI, CPSP, HACCP, GMP/SKP, dll.)</span></td><td class="v">{{ F::plain($surveyResponse->sertifikasi_keamanan_produk) }}</td></tr>
        <tr><td class="k">b. Kesehatan &amp; Keberlanjutan <span class="hint">(OEKO-TEX, LWG, dll.)</span></td><td class="v">{{ F::plain($surveyResponse->sertifikasi_kesehatan_keberlanjutan) }}</td></tr>
        <tr><td class="k">c. Kualitas Manajemen <span class="hint">(ISO 9001, 22000, 14001, dll.)</span></td><td class="v">{{ F::plain($surveyResponse->sertifikasi_kualitas_manajemen) }}</td></tr>
        <tr><td class="k">d. Tidak Memiliki / Tidak Tahu</td><td class="v">{{ F::plain($surveyResponse->sertifikasi_tidak_ada) }}</td></tr>
        <tr><td class="k">e. Lainnya</td><td class="v">{{ F::plain($surveyResponse->sertifikasi_lainnya) }}</td></tr>
    </table>

    <div class="sub">Model Industri Manufaktur</div>
    <table class="kv">
        <tr><td class="k">a. OEM <span class="hint">(Original Equipment Manufacturer)</span></td><td class="v">{{ F::checkbox($surveyResponse->model_industri_oem) }}</td></tr>
        <tr><td class="k">b. ODM <span class="hint">(Original Design Manufacturer)</span></td><td class="v">{{ F::checkbox($surveyResponse->model_industri_odm) }}</td></tr>
        <tr><td class="k">c. OBM <span class="hint">(Original Brand Manufacturer)</span></td><td class="v">{{ F::checkbox($surveyResponse->model_industri_obm) }}</td></tr>
        <tr><td class="k">d. Tidak Ada / Tidak Tahu</td><td class="v">{{ F::checkbox($surveyResponse->model_industri_tidak_ada) }}</td></tr>
        <tr><td class="k">e. Lainnya</td><td class="v">{{ F::plain($surveyResponse->model_industri_lainnya) }}</td></tr>
    </table>

    <div class="sub">Penggunaan Internet &amp; Teknologi Digital</div>
    <table class="kv">
        <tr><td class="k">210. Menggunakan Internet</td><td class="v">{{ F::yaTidak($surveyResponse->penggunaan_internet) }}</td></tr>
        @if(!empty($blok2Visibility['showQ210a']))
            <tr><td class="k">210a1. Menerima Pesanan Barang/Jasa</td><td class="v">{{ F::yaTidak($surveyResponse->internet_a1_menerima_pesanan) }}</td></tr>
            <tr><td class="k">210a2. Produksi Barang/Jasa</td><td class="v">{{ F::yaTidak($surveyResponse->internet_a2_produksi) }}</td></tr>
            <tr><td class="k">210a3. Distribusi Barang/Jasa</td><td class="v">{{ F::yaTidak($surveyResponse->internet_a3_distribusi) }}</td></tr>
            <tr><td class="k">210a4. Membeli Bahan Baku Online</td><td class="v">{{ F::yaTidak($surveyResponse->internet_a4_beli_bahan_baku) }}</td></tr>
            <tr><td class="k">210a5. Promosi</td><td class="v">{{ F::yaTidak($surveyResponse->internet_a5_promosi) }}</td></tr>
            <tr><td class="k">210a6. Lainnya</td><td class="v">{{ F::yaTidak($surveyResponse->internet_a6_lainnya) }}</td></tr>
        @endif
        @if(!empty($blok2Visibility['showQ210b']))
            <tr><td class="k">210b. Memanfaatkan Teknologi Digital</td><td class="v">{{ F::yaTidak($surveyResponse->pemanfaatan_teknologi_digital) }}</td></tr>
        @endif
    </table>

    <div class="sub">Ramah Lingkungan</div>
    <table class="kv">
        <tr><td class="k">211a. Memproduksi Barang/Jasa Ramah Lingkungan</td><td class="v">{{ F::produksiRamahLingkungan($surveyResponse->produksi_ramah_lingkungan) }}</td></tr>
        <tr><td class="k">211b. Menggunakan Input untuk Perlindungan Lingkungan</td><td class="v">{{ F::yaTidak($surveyResponse->penggunaan_input_ramah_lingkungan) }}</td></tr>
    </table>
    @endif

@elseif(!empty($blok2Visibility['showAfterQ201']))
    <p class="note">
        R202 dijawab <strong>Unit Pembantu/Penunjang</strong> — pertanyaan 205 s.d. 213
        tidak ditanyakan dan survei langsung dilanjutkan ke Blok VI.
    </p>
@else
    <p class="note">
        Perusahaan tidak berstatus <strong>Masih Aktif</strong> — pertanyaan 202 dan
        seterusnya tidak ditanyakan dan survei langsung dilanjutkan ke Blok VI.
    </p>
@endif
