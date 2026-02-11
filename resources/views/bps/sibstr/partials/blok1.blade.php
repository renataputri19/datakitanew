{{-- Blok I: Keterangan Umum - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <!-- Header Information Section -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">Header Information</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">1.</span>
                        <span>KIP (Kode Identitas Perusahaan):</span>
                    </label>
                    <input type="text" name="kip" value="{{ $surveyResponse->kip ?? '' }}"
                           class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">2.</span>
                        <span>IDSBR (ID Statistical Business Register):</span>
                    </label>
                    <input type="text" name="idsbr" value="{{ $surveyResponse->idsbr ?? '' }}"
                           class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        <!-- Section I: KETERANGAN UMUM -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">I. KETERANGAN UMUM (General Information)</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">101.</span>
                        <span>Nama Perusahaan:</span>
                    </label>
                    <input type="text" name="nama_perusahaan" value="{{ $surveyResponse->nama_perusahaan ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">102.</span>
                        <span>Alamat Pabrik/Tempat Usaha:</span>
                    </label>
                    <textarea name="alamat_pabrik" rows="3"
                              class="form-control textarea" readonly disabled>{{ $surveyResponse->alamat_pabrik ?? '' }}</textarea>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">103.</span>
                        <span>Kabupaten/Kota:</span>
                    </label>
                    <input type="text" name="kabupaten_kota" value="{{ $surveyResponse->kabupaten_kota ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">104.</span>
                        <span>Telepon/Fax:</span>
                    </label>
                    <input type="text" name="telepon_fax" value="{{ $surveyResponse->telepon_fax ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">105.</span>
                        <span>Penghubung:</span>
                    </label>
                    <input type="text" name="penghubung" value="{{ $surveyResponse->penghubung ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">106.</span>
                        <span>Email:</span>
                    </label>
                    <input type="email" name="email" value="{{ $surveyResponse->email ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">107.</span>
                        <span>Homepage/Website:</span>
                    </label>
                    <input type="url" name="homepage" value="{{ $surveyResponse->homepage ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">108.</span>
                        <span>Tahun mulai beroperasi secara komersial:</span>
                    </label>
                    <input type="number" name="tahun_mulai_beroperasi" value="{{ $surveyResponse->tahun_mulai_beroperasi ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">107.</span>
                        <span>NIB (Nomor Induk Berusaha):</span>
                    </label>
                    <input type="text" name="nib" value="{{ $surveyResponse->nib ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">108.</span>
                        <span>Jenis Kawasan:</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="jenis_kawasan_view" value="ekonomi_khusus"
                                   {{ ($surveyResponse->jenis_kawasan ?? '') == 'ekonomi_khusus' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">a. Kawasan Ekonomi Khusus</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jenis_kawasan_view" value="industri"
                                   {{ ($surveyResponse->jenis_kawasan ?? '') == 'industri' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">b. Kawasan Industri</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jenis_kawasan_view" value="luar_kawasan"
                                   {{ ($surveyResponse->jenis_kawasan ?? '') == 'luar_kawasan' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">c. Di Luar Kawasan</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">109.</span>
                        <span>Nama Kawasan:</span>
                    </label>
                    <input type="text" name="nama_kawasan" value="{{ $surveyResponse->nama_kawasan ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">112.</span>
                        <span>Nama Perusahaan Pengelola Kawasan:</span>
                    </label>
                    <input type="text" name="nama_pengelola_kawasan" value="{{ $surveyResponse->nama_pengelola_kawasan ?? '' }}"
                           class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        <!-- LEGALISASI PERUSAHAAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">LEGALISASI PERUSAHAAN</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 italic px-6">
                Diketahui oleh yang bertanggung jawab di perusahaan
            </p>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">110.</span>
                        <span>Nama:</span>
                    </label>
                    <input type="text" name="legalisasi_nama" value="{{ $surveyResponse->legalisasi_nama ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">111.</span>
                        <span>Jabatan:</span>
                    </label>
                    <input type="text" name="legalisasi_jabatan" value="{{ $surveyResponse->legalisasi_jabatan ?? '' }}"
                           class="form-control" readonly disabled>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">113.</span>
                        <span>Jenis Kelamin:</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="legalisasi_jenis_kelamin_view" value="laki_laki"
                                   {{ ($surveyResponse->legalisasi_jenis_kelamin ?? '') == 'laki_laki' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">Laki-laki</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="legalisasi_jenis_kelamin_view" value="perempuan"
                                   {{ ($surveyResponse->legalisasi_jenis_kelamin ?? '') == 'perempuan' ? 'checked' : '' }}
                                   class="radio-input" disabled>
                            <label class="radio-label">Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">115.</span>
                        <span>NIK:</span>
                    </label>
                    <input type="text" name="legalisasi_nik" value="{{ $surveyResponse->legalisasi_nik ?? '' }}"
                           class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        <!-- BPS RI (Static Information) -->
        <div class="form-section" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(37, 99, 235, 0.05));">
            <div class="section-header" style="background: rgba(59, 130, 246, 0.1);">
                <h3 class="section-title">BPS RI (Informasi Statis)</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">117.</span>
                        <span>Penghubung:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['penghubung'] }}</span>
                </div>
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">118.</span>
                        <span>Telepon:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['telepon'] }}</span>
                </div>
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">119.</span>
                        <span>Fax:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['fax'] }}</span>
                </div>
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">120.</span>
                        <span>Email:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['email'] }}</span>
                </div>
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">121.</span>
                        <span>Alamat:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['alamat'] }}</span>
                </div>
            </div>
        </div>
    </form>
</div>
