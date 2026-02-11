{{-- Blok IV: Fenomena dan Catatan - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK IV - Fenomena dan Catatan</h3>
                <p class="section-subtitle">Isi bila ada peningkatan/penurunan signifikan per triwulan.</p>
            </div>
            <div class="form-grid">
                @php
                    $blok4Data = $surveyResponse->blok4_data ?? [];
                    $quarters = [
                        'triwulan1' => ['num' => '401', 'label' => 'Triwulan I (Jan–Mar): Jelaskan fenomena atau catatan'],
                        'triwulan2' => ['num' => '402', 'label' => 'Triwulan II (Apr–Jun): Jelaskan fenomena atau catatan'],
                        'triwulan3' => ['num' => '403', 'label' => 'Triwulan III (Jul–Sep): Jelaskan fenomena atau catatan'],
                        'triwulan4' => ['num' => '404', 'label' => 'Triwulan IV (Okt–Des): Jelaskan fenomena atau catatan'],
                    ];
                @endphp

                @foreach($quarters as $key => $info)
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">{{ $info['num'] }}.</span>
                        <span>{{ $info['label'] }}</span>
                    </label>
                    <textarea rows="4" class="form-control textarea" readonly disabled>{{ $blok4Data[$key] ?? '' }}</textarea>
                </div>
                @endforeach
            </div>
        </div>
    </form>
</div>
