<section class="edit-banner" role="region" aria-labelledby="edit-banner-title" aria-describedby="edit-banner-desc" aria-live="polite">
    <div class="edit-banner__icon" aria-hidden="true">
        ✏️
    </div>
    <div class="edit-banner__content">
        <h2 id="edit-banner-title" class="edit-banner__title">Mode Edit Survei</h2>
        <p id="edit-banner-desc" class="edit-banner__desc">Perubahan yang Anda lakukan akan memperbarui isian survei yang sudah dikirim.</p>
    </div>
    @if(!empty($exitUrl))
        <div class="edit-banner__actions">
            <a href="{{ $exitUrl }}" class="edit-banner__action" aria-label="Keluar dari mode edit dan kembali ke halaman hasil">Keluar Mode Edit</a>
        </div>
    @endif
</section>