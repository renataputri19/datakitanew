{{-- Blok VI: Catatan - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK VI - VI. CATATAN</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">601.</span>
                        <span>Catatan tambahan (jika ada):</span>
                    </label>
                    <textarea rows="5" class="form-control textarea" readonly disabled>{{ $surveyResponse->catatan ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
