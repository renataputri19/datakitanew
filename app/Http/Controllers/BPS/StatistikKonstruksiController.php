<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\KonstruksiSurveyResponse;
use App\Support\KonstruksiBlokPath;
use Illuminate\Http\Request;

/**
 * Statistik Konstruksi — BPS-only dashboard over Survei Konstruksi data.
 *
 * Same architecture as the SIBSTR / UB / Listrik statistik dashboards: the
 * controller flattens every submission into one embedded JSON payload and the
 * page filters and aggregates client-side.
 *
 * Two things are deliberately computed here rather than in the browser:
 * the id-ID figure parsing (SibstrFormat::num(), which JS would have to
 * reimplement exactly) and the Blok VI column (17) formula, so the dashboard
 * and the questionnaire can never disagree about nilai konstruksi.
 */
class StatistikKonstruksiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    public function index(Request $request)
    {
        // Respondent accounts only: staff entries are tests, not companies.
        $rows = KonstruksiSurveyResponse::with('user:id,name,email')
            ->whereHas('user', fn ($q) => $q->respondents())
            ->orderBy('updated_at', 'desc')
            ->get();

        $periods = $rows
            ->map(fn ($r) => [
                'key'   => $r->tahun . '-' . ($r->triwulan === 0 ? 'tahunan' : $r->triwulan),
                'label' => KonstruksiBlokPath::periodLabel($r->tahun, $r->triwulan),
                'tahun' => (int) $r->tahun,
            ])
            ->unique('key')
            ->sortByDesc('tahun')
            ->values()
            ->all();

        $payload = [
            'periods'     => $periods,
            'kualifikasi' => KonstruksiSurveyResponse::KUALIFIKASI,
            'badanHukum'  => KonstruksiSurveyResponse::BADAN_HUKUM,
            'sumberDana'  => KonstruksiSurveyResponse::SUMBER_DANA,
            'pendidikan'  => KonstruksiSurveyResponse::PENDIDIKAN,
            'pengeluaran' => KonstruksiSurveyResponse::PENGELUARAN,
            'generatedAt' => now()->locale('id')->translatedFormat('j F Y H.i'),
            'rows'        => $rows->map(fn ($r) => $this->buildRow($r))->all(),
        ];

        return view('bps.statistik.konstruksi', [
            'user'    => $request->user(),
            'payload' => $payload,
        ]);
    }

    private function buildRow(KonstruksiSurveyResponse $r): array
    {
        return $r->isTriwulanan() ? $this->buildTwRow($r) : $this->buildAnnualRow($r);
    }

    /**
     * A quarterly submission, flattened into the same shape as an annual one.
     *
     * SKTR answers far fewer questions than SKTH, so the annual-only fields are
     * emitted as zeroes/nulls rather than omitted — the dashboard filters to one
     * period at a time, so a card never mixes the two, and keeping one row shape
     * means the table and modal code does not fork.
     */
    private function buildTwRow(KonstruksiSurveyResponse $r): array
    {
        $n         = fn ($v) => KonstruksiSurveyResponse::n($v);
        $proyek    = $r->totalProyekTw();
        $pekerja   = is_array($r->tw_pekerja) ? $r->tw_pekerja : [];
        $balasJasa = is_array($r->tw_balas_jasa) ? $r->tw_balas_jasa : [];
        $labels    = $r->quarterLabels();

        // The quarterly form counts workers as one figure per quarter, not by
        // pendidikan, so the education card has nothing to show for SKTR — the
        // current quarter's headcount goes into a single bucket instead.
        $tenaga = [];
        foreach (array_keys(KonstruksiSurveyResponse::PENDIDIKAN) as $jenjang) {
            $tenaga[$jenjang] = ['l' => 0.0, 'p' => 0.0];
        }

        $proyekList = [];
        foreach ($r->proyekTwRows() as $row) {
            $proyekList[] = [
                'nama'       => $row['nama_pekerjaan'] ?? '',
                'kbli'       => $row['kbli'] ?? '',
                'kabkota'    => trim((string) ($row['kabkota_lokasi'] ?? '')) ?: null,
                'provinsi'   => trim((string) ($row['provinsi_lokasi'] ?? '')) ?: null,
                'sumberDana' => (string) ($row['sumber_dana'] ?? ''),
                'kontrak'    => $n($row['nilai_kontrak'] ?? null),
                'subkontrak' => $n($row['nilai_kontrak'] ?? null) * ($n($row['pct_subkontrak'] ?? null) / 100),
                'bahan'      => 0.0,   // SKTR does not collect material cost per project
                'upah'       => $n($row['upah_ini'] ?? null),
                'upahRata'   => $n($row['upah_rata_ini'] ?? null),
                'realisasi'  => $n($row['realisasi_ini'] ?? null),
                'nilai'      => KonstruksiSurveyResponse::nilaiKonstruksiTw($row),
                'mulai'      => $row['mulai'] ?? '',
                'selesai'    => $row['selesai'] ?? '',
            ];
        }

        $pengeluaran = [];
        foreach (array_keys(KonstruksiSurveyResponse::PENGELUARAN) as $key) {
            $pengeluaran[$key] = 0.0;
        }

        return [
            'id'          => $r->id,
            'uid'         => $r->user_id,
            'perusahaan'  => $r->nama_perusahaan ?: ($r->user->name ?? 'Tanpa nama'),
            'periode'     => $r->tahun . '-' . $r->triwulan,
            'tahun'       => (int) $r->tahun,
            'kabupaten'   => $r->kabupaten_kota,
            'kualifikasi' => (string) $r->kualifikasi,
            'badanHukum'  => (string) $r->badan_hukum,
            'selesai'     => (bool) $r->is_completed,
            'progress'    => $r->completionPercent(),
            'updatedAt'   => optional($r->updated_at)->locale('id')->translatedFormat('j M Y'),
            'updatedTs'   => optional($r->updated_at)->getTimestamp(),

            'tenaga'      => $tenaga,
            'pekerjaTw'   => $n($pekerja['cur'] ?? null),
            'balasJasa'   => $n($balasJasa['cur'] ?? null),
            'pengeluaran' => $pengeluaran,
            'barangModal' => ['tambah' => 0.0, 'kurang' => 0.0],

            'jumlahProyek'    => count($proyekList),
            'nilaiKontrak'    => $proyek['nilai_kontrak'],
            'nilaiKonstruksi' => $proyek['nilai_konstruksi'],
            'bahanProyek'     => 0.0,
            'upahHarian'      => $proyek['upah_ini'],
            'pendapatanLain'  => 0.0,

            // Blok IX does not exist on SKTR; nothing to summarise.
            'totalA'    => 0.0,
            'totalB'    => 0.0,
            'marginPct' => null,

            // Quarterly-only extras the dashboard shows in place of the annual cards.
            'isTw'        => true,
            'twLabels'    => ['prev' => $labels['prev']['short'], 'cur' => $labels['cur']['short']],
            'pekerjaPrev' => $n($pekerja['prev'] ?? null),
            'balasPrev'   => $n($balasJasa['prev'] ?? null),
            'statusLabel' => KonstruksiSurveyResponse::STATUS_PERUSAHAAN[$r->status_perusahaan] ?? '—',
            'adaProyek'   => $r->hasProyek(),

            'proyek' => $proyekList,
            'bahan'  => [],
            'modal'  => ['sendiri' => 0.0, 'pinjaman' => 0.0, 'penggunaJasa' => 0.0],
        ];
    }

    private function buildAnnualRow(KonstruksiSurveyResponse $r): array
    {
        $n         = fn ($v) => KonstruksiSurveyResponse::n($v);
        $proyek    = $r->totalProyek();
        $ringkasan = $r->ringkasan();
        $pekerja   = is_array($r->blok2_pekerja) ? $r->blok2_pekerja : [];

        // Per-jenjang worker counts, kept split by sex so the dashboard can
        // show the education profile and the sex ratio from one payload.
        $tenaga = [];
        foreach (array_keys(KonstruksiSurveyResponse::PENDIDIKAN) as $jenjang) {
            $tenaga[$jenjang] = [
                'l' => $n($pekerja[$jenjang]['l'] ?? null),
                'p' => $n($pekerja[$jenjang]['p'] ?? null),
            ];
        }

        $pengeluaran = [];
        foreach (array_keys(KonstruksiSurveyResponse::PENGELUARAN) as $key) {
            $pengeluaran[$key] = $n(($r->blok4_pengeluaran ?? [])[$key] ?? null);
        }

        $barangModal = ['tambah' => 0.0, 'kurang' => 0.0];
        foreach (array_keys(KonstruksiSurveyResponse::BARANG_MODAL) as $jenis) {
            $barangModal['tambah'] += $n(($r->blok5_barang_modal ?? [])[$jenis]['tambah'] ?? null);
            $barangModal['kurang'] += $n(($r->blok5_barang_modal ?? [])[$jenis]['kurang'] ?? null);
        }

        // One entry per project, already carrying its derived column (17), so
        // the per-proyek and per-wilayah cards read straight off it.
        $proyekList = [];
        foreach ($r->proyekRows() as $row) {
            $proyekList[] = [
                'nama'       => $row['nama_pekerjaan'] ?? '',
                'kbli'       => $row['kbli'] ?? '',
                'kabkota'    => trim((string) ($row['kabkota_lokasi'] ?? '')) ?: null,
                'provinsi'   => trim((string) ($row['provinsi_lokasi'] ?? '')) ?: null,
                'sumberDana' => (string) ($row['sumber_dana'] ?? ''),
                'kontrak'    => $n($row['nilai_kontrak'] ?? null),
                'subkontrak' => $n($row['nilai_subkontrak'] ?? null),
                'bahan'      => $n($row['bahan_perusahaan'] ?? null) + $n($row['bahan_pemilik'] ?? null),
                'upah'       => $n($row['upah_harian'] ?? null),
                'upahRata'   => $n($row['upah_rata_rata'] ?? null),
                'realisasi'  => $n($row['realisasi_akhir'] ?? null),
                'nilai'      => KonstruksiSurveyResponse::nilaiKonstruksi($row),
                'mulai'      => $row['mulai'] ?? '',
                'selesai'    => $row['selesai'] ?? '',
            ];
        }

        $bahanList = [];
        foreach ($r->bahanRows() as $row) {
            $bahanList[] = ['nama' => $row['nama'] ?? '', 'nilai' => $n($row['nilai'] ?? null)];
        }

        return [
            'id'          => $r->id,
            'uid'         => $r->user_id,
            'perusahaan'  => $r->nama_perusahaan ?: ($r->user->name ?? 'Tanpa nama'),
            'periode'     => $r->tahun . '-' . ($r->triwulan === 0 ? 'tahunan' : $r->triwulan),
            'tahun'       => (int) $r->tahun,
            'kabupaten'   => $r->kabupaten_kota,
            'kualifikasi' => (string) $r->kualifikasi,
            'badanHukum'  => (string) $r->badan_hukum,
            'selesai'     => (bool) $r->is_completed,
            'progress'    => $r->completionPercent(),
            'updatedAt'   => optional($r->updated_at)->locale('id')->translatedFormat('j M Y'),
            // sortable twin of updatedAt — the formatted string sorts wrong
            'updatedTs'   => optional($r->updated_at)->getTimestamp(),

            'tenaga'      => $tenaga,
            'balasJasa'   => $r->totalBalasJasa(),
            'pengeluaran' => $pengeluaran,
            'barangModal' => $barangModal,

            'jumlahProyek'    => count($proyekList),
            'nilaiKontrak'    => $proyek['nilai_kontrak'],
            'nilaiKonstruksi' => $proyek['nilai_konstruksi'],
            'bahanProyek'     => $proyek['bahan_perusahaan'] + $proyek['bahan_pemilik'],
            'upahHarian'      => $proyek['upah_harian'],
            'pendapatanLain'  => $r->totalPendapatanLain(),

            'totalA'    => $ringkasan['total_a'],
            'totalB'    => $ringkasan['total_b'],
            'marginPct' => $ringkasan['margin_pct'],

            'isTw'   => false,
            'proyek' => $proyekList,
            'bahan'  => $bahanList,
            'modal'  => [
                'sendiri'     => $n(($r->blok6_sumber_modal ?? [])['modal_sendiri'] ?? null),
                'pinjaman'    => $n(($r->blok6_sumber_modal ?? [])['pinjaman'] ?? null),
                'penggunaJasa' => $n(($r->blok6_sumber_modal ?? [])['pengguna_jasa'] ?? null),
            ],
        ];
    }
}
