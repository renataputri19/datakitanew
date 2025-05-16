@extends('layouts.app')

@section('title', 'FAQ - DataKita')
@section('description', 'Frequently Asked Questions (FAQ) tentang DataKita')

@push('styles')
<style>
    .faq-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .faq-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .faq-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e3a8a; /* Blue-900 */
        margin-bottom: 0.5rem;
    }

    .dark .faq-header h1 {
        color: #3b82f6; /* Blue-500 for dark mode */
    }

    .faq-header p {
        font-size: 1.1rem;
        color: #4b5563; /* Gray-600 */
    }

    .dark .faq-header p {
        color: #9ca3af; /* Gray-400 for dark mode */
    }

    .search-bar {
        display: flex;
        max-width: 600px;
        margin: 0 auto 2rem;
    }

    .search-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb; /* Gray-200 */
        border-radius: 0.375rem 0 0 0.375rem;
        font-size: 1rem;
    }

    .dark .search-input {
        background-color: #1f2937; /* Gray-800 */
        border-color: #374151; /* Gray-700 */
        color: #e5e7eb; /* Gray-200 */
    }

    .search-button {
        padding: 0.75rem 1.5rem;
        background-color: #2563eb; /* Blue-600 */
        color: white;
        border: none;
        border-radius: 0 0.375rem 0.375rem 0;
        cursor: pointer;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    .search-button:hover {
        background-color: #1d4ed8; /* Blue-700 */
    }

    .dark .search-button {
        background-color: #3b82f6; /* Blue-500 */
    }

    .dark .search-button:hover {
        background-color: #2563eb; /* Blue-600 */
    }

    .faq-item {
        margin-bottom: 1rem;
        border: 1px solid #e5e7eb; /* Gray-200 */
        border-radius: 0.5rem;
        overflow: hidden;
        background-color: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .dark .faq-item {
        background-color: #1f2937; /* Gray-800 */
        border-color: #374151; /* Gray-700 */
    }

    .faq-question {
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 600;
        color: #111827; /* Gray-900 */
        background-color: #f9fafb; /* Gray-50 */
        transition: background-color 0.2s;
    }

    .dark .faq-question {
        background-color: #111827; /* Gray-900 */
        color: #f9fafb; /* Gray-50 */
    }

    .faq-question:hover {
        background-color: #f3f4f6; /* Gray-100 */
    }

    .dark .faq-question:hover {
        background-color: #1e293b; /* Slate-800 */
    }

    .faq-question svg {
        width: 20px;
        height: 20px;
        transition: transform 0.3s ease;
        color: #6b7280; /* Gray-500 */
    }

    .dark .faq-question svg {
        color: #9ca3af; /* Gray-400 */
    }

    .faq-answer {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s ease;
        color: #4b5563; /* Gray-600 */
        border-top: 0px solid #e5e7eb; /* Gray-200 */
    }

    .dark .faq-answer {
        color: #d1d5db; /* Gray-300 */
        border-color: #374151; /* Gray-700 */
    }

    .faq-answer.active {
        padding: 1.25rem;
        max-height: 1000px;
        border-top-width: 1px;
    }

    .faq-answer p, .faq-answer ul, .faq-answer ol {
        margin-bottom: 1rem;
    }

    .faq-answer ul, .faq-answer ol {
        padding-left: 1.5rem;
    }

    .faq-answer li {
        margin-bottom: 0.5rem;
    }

    .faq-answer a {
        color: #2563eb; /* Blue-600 */
        text-decoration: none;
    }

    .dark .faq-answer a {
        color: #3b82f6; /* Blue-500 */
    }

    .faq-answer a:hover {
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        .faq-header h1 {
            font-size: 2rem;
        }

        .faq-header p {
            font-size: 1rem;
        }

        .search-bar {
            flex-direction: column;
        }

        .search-input {
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }

        .search-button {
            border-radius: 0.375rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="faq-container">
        <div class="faq-header" data-aos="fade-up">
            <h1>FAQ DataKita</h1>
            <p>Pertanyaan yang sering diajukan tentang DataKita</p>
        </div>
        <div class="search-bar" data-aos="fade-up" data-aos-delay="100">
            <input type="text" id="faq-search" placeholder="Cari pertanyaan..." class="search-input">
            <button id="search-button" class="search-button">Cari</button>
        </div>
        <div data-aos="fade-up" data-aos-delay="200">
            <div class="faq-item">
                <div class="faq-question">
                    <span>Apa itu DataKita?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>DataKita adalah aplikasi super yang dikembangkan oleh BPS Kota Batam untuk menyediakan akses terpusat
                        ke data statistik, pembaruan berita, dan sistem terintegrasi seperti MONALISA. Aplikasi ini
                        dirancang untuk memudahkan dinas, instansi, dan masyarakat dalam mengakses informasi statistik
                        secara transparan dan mendukung pengambilan keputusan berbasis data sesuai dengan prinsip Satu Data
                        Indonesia.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Siapa yang dapat menggunakan DataKita?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>DataKita ditujukan untuk:</p>
                    <ul>
                        <li><strong>Dinas</strong>: Untuk perencanaan kebijakan dan tata kelola berbasis data.</li>
                        <li><strong>Instansi</strong>: Untuk penelitian, analisis, dan kepatuhan terhadap standar Satu Data
                            Indonesia.</li>
                        <li><strong>Masyarakat</strong>: Untuk meningkatkan kesadaran dan edukasi mengenai data statistik.
                        </li>
                    </ul>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Apa saja fitur utama DataKita?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <ol>
                        <li><strong>Data Statistik Terkategori</strong>:
                            <ul>
                                <li><strong>Kategori</strong>: Ekonomi (inflasi, kemiskinan), Sosial (IPM, pendidikan),
                                    Demografi (populasi, migrasi), Lingkungan (iklim, biodiversitas), Publikasi (buku
                                    digital, laporan), dan Tabel Dinamis.</li>
                                <li><strong>Fitur</strong>: Deskripsi singkat dan tautan langsung ke situs BPS Batam (<a
                                        href="https://batamkota.bps.go.id">batamkota.bps.go.id</a>).</li>
                                <li><strong>Tujuan</strong>: Memudahkan navigasi data teknis agar lebih mudah dipahami.</li>
                            </ul>
                        </li>
                        <li><strong>Berita & Pembaruan YouTube</strong>:
                            <ul>
                                <li><strong>Konten</strong>: Video Rilis Berita Resmi Statistik bulanan dan pengumuman
                                    publikasi dari kanal YouTube BPS.</li>
                                <li><strong>Fitur</strong>: Bagian “Berita & Pembaruan” dengan tautan ke kanal YouTube dan
                                    kemungkinan daftar putar terbaru.</li>
                                <li><strong>Tujuan</strong>: Menyediakan informasi multimedia terkini yang tidak tersedia di
                                    situs BPS Batam.</li>
                            </ul>
                        </li>
                        <li><strong>Sistem Terintegrasi</strong>:
                            <ul>
                                <li><strong>Integrasi Saat Ini</strong>: MONALISA untuk pemantauan dan evaluasi statistik
                                    sektoral.</li>
                                <li><strong>Integrasi Masa Depan</strong>: Sistem lain seperti Romantik, Simbatik, atau
                                    INDAH.</li>
                                <li><strong>Fitur</strong>: Akses sistem melalui modul dengan tautan langsung (misalnya, <a
                                        href="https://monalisa.bpsbatam.com">monalisa.bpsbatam.com</a>).</li>
                                <li><strong>Tujuan</strong>: Menyediakan antarmuka terpadu untuk berbagai sistem BPS.</li>
                            </ul>
                        </li>
                        <li><strong>Sistem Login Terpadu</strong>:
                            <ul>
                                <li><strong>Fitur</strong>: Satu set kredensial untuk mengakses semua fitur DataKita.</li>
                                <li><strong>Tujuan</strong>: Mempermudah akses dan memastikan keamanan berbasis peran
                                    pengguna.</li>
                            </ul>
                        </li>
                    </ol>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Bagaimana cara mengakses DataKita?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>Pengguna dapat mengakses DataKita melalui portal login terpusat dengan satu set kredensial. Platform
                        ini dioptimalkan untuk perangkat desktop dan mobile dengan antarmuka yang responsif dan navigasi
                        yang jelas.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Apa itu MONALISA?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>MONALISA adalah sistem terintegrasi dalam DataKita untuk memantau dan mengevaluasi statistik
                        sektoral. Sistem ini memungkinkan dinas dan instansi untuk mengunggah dokumen, mengevaluasi
                        indikator berdasarkan standar Evaluasi Penyelenggaraan Statistik Sektoral (EPSS), dan memantau
                        kemajuan secara real-time.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Bagaimana DataKita mendukung Satu Data Indonesia?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>DataKita mendukung Satu Data Indonesia dengan menyediakan platform terpusat yang meningkatkan
                        aksesibilitas, transparansi, dan kolaborasi antar pemangku kepentingan. Data disajikan dengan tautan
                        langsung ke sumber asli, memastikan keakuratan dan kepatuhan terhadap standar nasional.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Apakah DataKita ramah pengguna?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>Ya, DataKita dirancang dengan antarmuka yang bersih, responsif, dan mudah dinavigasi, baik di desktop
                        maupun perangkat mobile. Semua konten disajikan secara transparan dengan deskripsi jelas dan tautan
                        langsung ke sumber asli.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Bagaimana cara mendapatkan pembaruan berita statistik?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>Bagian “Berita & Pembaruan” di DataKita menyediakan akses ke video Rilis Berita Resmi Statistik
                        bulanan dan konten lainnya dari kanal YouTube BPS. Bagian ini diperbarui setiap bulan untuk
                        menampilkan informasi terbaru.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Apakah diperlukan login terpisah untuk setiap sistem?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>Tidak, DataKita menggunakan sistem login terpadu (single sign-on) sehingga pengguna hanya perlu satu
                        set kredensial untuk mengakses semua fitur, termasuk sistem seperti MONALISA.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Bagaimana cara menghubungi dukungan jika ada masalah?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>Untuk bantuan teknis atau pertanyaan lebih lanjut, silakan hubungi tim dukungan BPS Kota Batam
                        melalui situs resmi (<a href="https://batamkota.bps.go.id">batamkota.bps.go.id</a>), kontak resmi
                        yang tersedia, atau hubungi Encik Bot melalui WhatsApp di <a
                            href="http://wa.me/6281319992171">http://wa.me/6281319992171</a>.</p>
                </div>
            </div>
        </div>
    </div>



    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                mirror: false,
            });

            const questions = document.querySelectorAll('.faq-question');
            questions.forEach(question => {
                question.addEventListener('click', () => {
                    const answer = question.nextElementSibling;
                    const isActive = answer.classList.contains('active');
                    const allAnswers = document.querySelectorAll('.faq-answer');
                    const allIcons = document.querySelectorAll('.faq-question svg');

                    allAnswers.forEach(a => a.classList.remove('active'));
                    allIcons.forEach(icon => icon.style.transform = 'rotate(0deg)');

                    if (!isActive) {
                        answer.classList.add('active');
                        question.querySelector('svg').style.transform = 'rotate(180deg)';
                    }
                });
            });

            const searchButton = document.getElementById('search-button');
            const searchInput = document.getElementById('faq-search');
            searchButton.addEventListener('click', () => {
                const searchTerm = searchInput.value.toLowerCase();
                const faqItems = document.querySelectorAll('.faq-item');
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question span').textContent
                        .toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    if (searchTerm === '' || question.includes(searchTerm) || answer.includes(
                            searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    searchButton.click();
                }
            });

            questions[0].click(); // Open the first FAQ by default
        });
    </script>
@endsection
