{{-- Blok II: Pendahuluan - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <!-- Section II: PENDAHULUAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK II. PENDAHULUAN</h3>
            </div>
            <div class="form-grid">
                <!-- Question 201 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">201.</span>
                        <span>Kondisi Perusahaan:</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan_view" value="masih_aktif"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'masih_aktif' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">a. Masih Aktif</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan_view" value="belum_beroperasi"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'belum_beroperasi' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">b. Belum Beroperasi</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan_view" value="tutup"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'tutup' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">c. Tutup</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan_view" value="pindah"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'pindah' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">d. Pindah</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan_view" value="tidak_ditemukan"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'tidak_ditemukan' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">e. Tidak Ditemukan</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan_view" value="double_ganda_duplikat"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'double_ganda_duplikat' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">f. Double / Ganda / Duplikat</label>
                        </div>
                    </div>
                </div>

                @if(!empty($blok2Visibility['showAfterQ201']))
                <!-- Question 202 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">202.</span>
                        <span>Jaringan atau unit kegiatan perusahaan:</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan_view" value="tunggal"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'tunggal' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">a. Tunggal</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan_view" value="pabrik_unit_produksi"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'pabrik_unit_produksi' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">b. Pabrik/Unit produksi, Cabang atau Perwakilan</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan_view" value="pusat_ada_kegiatan_produksi"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'pusat_ada_kegiatan_produksi' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">c. Pusat ada kegiatan produksi</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan_view" value="kantor_pusat_administrasi_perwakilan"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'kantor_pusat_administrasi_perwakilan' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">d. Kantor Pusat / Kantor Administrasi / Kantor Perwakilan</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan_view" value="unit_pembantu_penunjang"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'unit_pembantu_penunjang' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">e. Unit Pembantu / Penunjang</label>
                        </div>
                    </div>
                </div>

                @if(($surveyResponse->triwulan ?? 0) == 0)
                <!-- Question 203 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">203.</span>
                        <span>Berapa jumlah seluruh kantor cabang dan unit usaha yang berada di bawah kantor pusat?</span>
                    </label>
                    <input type="number" name="jumlah_cabang_dan_unit_usaha" value="{{ $surveyResponse->jumlah_cabang_dan_unit_usaha ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <!-- Question 204 -->
                @if(!empty($blok2Visibility['showQ203']))
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">204.</span>
                        <span>Informasi kantor pusat (Diisi jika R203 berkode b):</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Nama Kantor Pusat:</label>
                            <input type="text" name="info_kantor_pusat_nama" value="{{ $surveyResponse->info_kantor_pusat_nama ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Alamat Kantor Pusat:</label>
                            <textarea name="info_kantor_pusat_alamat" rows="3"
                                      class="form-control textarea" readonly disabled>{{ $surveyResponse->info_kantor_pusat_alamat ?? '' }}</textarea>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Email Kantor Pusat:</label>
                            <input type="email" name="info_kantor_pusat_email" value="{{ $surveyResponse->info_kantor_pusat_email ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Negara:</label>
                            <input type="text" name="info_kantor_pusat_negara" value="{{ $surveyResponse->info_kantor_pusat_negara ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">e. Provinsi:</label>
                            <input type="text" name="info_kantor_pusat_provinsi" value="{{ $surveyResponse->info_kantor_pusat_provinsi ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">f. Kabupaten / Kota:</label>
                            <input type="text" name="info_kantor_pusat_kabkota" value="{{ $surveyResponse->info_kantor_pusat_kabkota ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>
                @endif
                @endif {{-- tahunan-only Q203/Q204 --}}

                @if(!empty($blok2Visibility['showQ205to211']))
                @if(($surveyResponse->triwulan ?? 0) == 0)
                <!-- Question 205 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">205.</span>
                        <span>Jumlah bulan perusahaan aktif berproduksi selama tahun 2025:</span>
                    </label>
                    <input type="number" name="jumlah_bulan_aktif_2025" value="{{ $surveyResponse->jumlah_bulan_aktif_2025 ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <!-- Question 206 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">206.</span>
                        <span>Rata-rata waktu kerja selama tahun 2025:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Rata-rata hari kerja per bulan selama tahun 2025:</label>
                            <input type="number" name="rata_hari_kerja_bulanan_2025" value="{{ $surveyResponse->rata_hari_kerja_bulanan_2025 ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Rata-rata jam kerja per hari selama tahun 2025:</label>
                            <input type="number" name="rata_jam_kerja_per_hari_2025" value="{{ $surveyResponse->rata_jam_kerja_per_hari_2025 ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Rata-rata jumlah shift per hari selama tahun 2025:</label>
                            <input type="number" name="rata_shift_per_hari_2025" value="{{ $surveyResponse->rata_shift_per_hari_2025 ?? '' }}"
                                   class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>
                @endif {{-- tahunan-only Q205/Q206 --}}

                @if(($surveyResponse->triwulan ?? 0) == 0)
                {{-- Q207: Tahunan — detailed worker breakdown --}}
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">207.</span>
                        <span>Jumlah pekerja selama tahun 2025:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Jumlah seluruh pekerja:</label>
                            <input type="number" value="{{ $surveyResponse->jumlah_seluruh_pekerja ?? '' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Menurut jenis kelamin:</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">b.1. Pekerja laki-laki:</label>
                                    <input type="number" value="{{ $surveyResponse->tenaga_kerja_laki_laki ?? '' }}" class="form-control" readonly disabled>
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">b.2. Pekerja perempuan:</label>
                                    <input type="number" value="{{ $surveyResponse->tenaga_kerja_perempuan ?? '' }}" class="form-control" readonly disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Bukan outsourcing:</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">c.1. Produksi:</label>
                                    <input type="number" value="{{ $surveyResponse->pekerja_bukan_outsourcing_produksi ?? '' }}" class="form-control" readonly disabled>
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">c.2. Lainnya:</label>
                                    <input type="number" value="{{ $surveyResponse->pekerja_bukan_outsourcing_lainnya ?? '' }}" class="form-control" readonly disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Outsourcing:</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">d.1. Produksi:</label>
                                    <input type="number" value="{{ $surveyResponse->pekerja_outsourcing_produksi ?? '' }}" class="form-control" readonly disabled>
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">d.2. Lainnya:</label>
                                    <input type="number" value="{{ $surveyResponse->pekerja_outsourcing_lainnya ?? '' }}" class="form-control" readonly disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">e. Pekerja asing:</label>
                            <input type="number" value="{{ $surveyResponse->tenaga_kerja_asing ?? '' }}" class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>
                @else
                {{-- Q207: Triwulanan — simplified --}}
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">207.</span>
                        <span>Rata-rata tenaga kerja di perusahaan pada triwulan ini:</span>
                    </label>
                    <input type="number" value="{{ $surveyResponse->rata_rata_tenaga_kerja ?? '' }}" class="form-control" readonly disabled>
                </div>
                @endif


                <!-- Question 208 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">208.</span>
                        <span>Tuliskan kegiatan utama perusahaan beserta produk utama dan KBLI utama:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Uraian kegiatan utama perusahaan:</label>
                            <textarea name="kegiatan_utama_perusahaan" class="form-control" readonly disabled>{{ $surveyResponse->kegiatan_utama_perusahaan ?? '' }}</textarea>
                        </div>
                        @if(($surveyResponse->triwulan ?? 0) == 0)
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Produk utama tahun 2025:</label>
                            <textarea name="produk_utama_perusahaan" class="form-control" readonly disabled>{{ $surveyResponse->produk_utama_perusahaan ?? '' }}</textarea>
                        </div>
                        @endif
                        <div class="form-subrow">
                            <label class="form-sublabel">c. KBLI utama (5 digit):</label>
                            <input type="text" name="kbli_utama" value="{{ $surveyResponse->kbli_utama ?? '' }}" class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>

                @if(($surveyResponse->triwulan ?? 0) == 0)
                <!-- Question 209 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">209.</span>
                        <span>Pilih yang paling sesuai dengan kegiatan utama usaha/perusahaan ini:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah memproduksi barang sendiri?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="memproduksi_barang_sendiri_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->memproduksi_barang_sendiri ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="memproduksi_barang_sendiri_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->memproduksi_barang_sendiri ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah menyediakan layanan makan minum?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="menyediakan_layanan_makan_minum_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->menyediakan_layanan_makan_minum ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="menyediakan_layanan_makan_minum_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->menyediakan_layanan_makan_minum ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah melakukan penjualan barang dari pihak lain?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="penjualan_barang_pihak_lain_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->penjualan_barang_pihak_lain ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="penjualan_barang_pihak_lain_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->penjualan_barang_pihak_lain ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah melakukan aktivitas jasa?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="aktivitas_jasa_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->aktivitas_jasa ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="aktivitas_jasa_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->aktivitas_jasa ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @endif {{-- tahunan-only Q209 --}}

                @if(($surveyResponse->triwulan ?? 0) == 0)
                <!-- Question 210 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">210.</span>
                        <span>Sertifikasi produk yang dimiliki perusahaan:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Sertifikasi Keamanan Produk (mis. SNI, CPSP, HACCP, GMP/SKP, dll.)</label>
                            <div class="form-control-static">{{ $surveyResponse->sertifikasi_keamanan_produk ?? '-' }}</div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Sertifikasi Kesehatan dan Keberlanjutan (mis. OEKO-TEX, Leather Working Group, dll.)</label>
                            <div class="form-control-static">{{ $surveyResponse->sertifikasi_kesehatan_keberlanjutan ?? '-' }}</div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Sertifikasi Kualitas Manajemen (mis. ISO 9001, ISO 22000, ISO 14001, dll.)</label>
                            <div class="form-control-static">{{ $surveyResponse->sertifikasi_kualitas_manajemen ?? '-' }}</div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Tidak memiliki / tidak tahu</label>
                            <div class="form-control-static">{{ $surveyResponse->sertifikasi_tidak_ada ?? '-' }}</div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">e. Lainnya</label>
                            <div class="form-control-static">{{ $surveyResponse->sertifikasi_lainnya ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Question 211 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">211.</span>
                        <span>Model industri manufaktur yang diterapkan di perusahaan (pilihan boleh lebih dari 1):</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option"><input type="checkbox" class="radio-input" {{ ($surveyResponse->model_industri_oem ?? 0) ? 'checked' : '' }} disabled><label class="radio-label">a. OEM (Original Equipment Manufacturer)</label></div>
                        <div class="radio-option"><input type="checkbox" class="radio-input" {{ ($surveyResponse->model_industri_odm ?? 0) ? 'checked' : '' }} disabled><label class="radio-label">b. ODM (Original Design Manufacturer)</label></div>
                        <div class="radio-option"><input type="checkbox" class="radio-input" {{ ($surveyResponse->model_industri_obm ?? 0) ? 'checked' : '' }} disabled><label class="radio-label">c. OBM (Original Brand Manufacturer)</label></div>
                        <div class="radio-option"><input type="checkbox" class="radio-input" {{ ($surveyResponse->model_industri_tidak_ada ?? 0) ? 'checked' : '' }} disabled><label class="radio-label">d. Tidak ada / tidak tahu</label></div>
                        <div class="radio-option"><input type="checkbox" class="radio-input" {{ ($surveyResponse->model_industri_lainnya ?? '') ? 'checked' : '' }} disabled><label class="radio-label">e. Lainnya{{ $surveyResponse->model_industri_lainnya ? ': '.$surveyResponse->model_industri_lainnya : '' }}</label></div>
                    </div>
                </div>
                @endif

                @if(($surveyResponse->triwulan ?? 0) == 0)
                <!-- Question 212 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">212.</span>
                        <span>Apakah perusahaan ini menggunakan internet dalam menjalankan usaha?</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="penggunaan_internet_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->penggunaan_internet ?? '') == 'ya' ? 'checked' : '' }}>
                            <label class="radio-label">Ya</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="penggunaan_internet_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->penggunaan_internet ?? '') == 'tidak' ? 'checked' : '' }}>
                            <label class="radio-label">Tidak</label>
                        </div>
                    </div>
                </div>

                <!-- 212a: Tujuan penggunaan internet -->
                @if(!empty($blok2Visibility['showQ210a']))
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">212a.</span>
                        <span>Tujuan penggunaan internet:</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'internet_a1_menerima_pesanan' => 'a1. Menerima pesanan barang/jasa',
                            'internet_a2_produksi' => 'a2. Produksi barang/jasa',
                            'internet_a3_distribusi' => 'a3. Distribusi barang/jasa',
                            'internet_a4_beli_bahan_baku' => 'a4. Membeli bahan baku online',
                            'internet_a5_promosi' => 'a5. Promosi',
                            'internet_a6_lainnya' => 'a6. Lainnya',
                        ] as $field => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="{{ $field }}_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->$field ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="{{ $field }}_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->$field ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 212b: Teknologi digital -->
                @if(!empty($blok2Visibility['showQ210b']))
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">212b.</span>
                        <span>Apakah perusahaan memanfaatkan teknologi digital (AI, IoT, big data, printer 3D, blockchain, cloud)?</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="pemanfaatan_teknologi_digital_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->pemanfaatan_teknologi_digital ?? '') == 'ya' ? 'checked' : '' }}>
                            <label class="radio-label">Ya</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="pemanfaatan_teknologi_digital_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->pemanfaatan_teknologi_digital ?? '') == 'tidak' ? 'checked' : '' }}>
                            <label class="radio-label">Tidak</label>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Question 213 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">213.</span>
                        <span>Praktik ramah lingkungan:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Apakah perusahaan memproduksi barang/jasa yang ramah lingkungan?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="produksi_ramah_lingkungan_view" value="ya_seluruh" class="radio-input" disabled {{ ($surveyResponse->produksi_ramah_lingkungan ?? '') == 'ya_seluruh' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya, seluruhnya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="produksi_ramah_lingkungan_view" value="ya_sebagian" class="radio-input" disabled {{ ($surveyResponse->produksi_ramah_lingkungan ?? '') == 'ya_sebagian' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya, sebagian</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="produksi_ramah_lingkungan_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->produksi_ramah_lingkungan ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label class="radio-label">Tidak sama sekali</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Apakah usaha/perusahaan menggunakan input untuk tujuan perlindungan lingkungan?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="penggunaan_input_ramah_lingkungan_view" value="ya" class="radio-input" disabled {{ ($surveyResponse->penggunaan_input_ramah_lingkungan ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="penggunaan_input_ramah_lingkungan_view" value="tidak" class="radio-input" disabled {{ ($surveyResponse->penggunaan_input_ramah_lingkungan ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif {{-- tahunan-only Q212/212a/212b/213 --}}
                @endif {{-- end showQ205to211 --}}
                @endif {{-- end showAfterQ201 --}}
            </div>
        </div>
    </form>
</div>
