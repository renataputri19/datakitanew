<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * "Peta Wilayah Kerja" — a field guide map for petugas.
 *
 * The page is rendered by index(); the wilayah index and per-kelurahan polygons
 * are served as JSON by indexData()/kelurahan(). The data lives under
 * resources/data/peta/ (outside the public web root) so it is only reachable
 * through these auth-gated routes. Regenerate it with `php artisan peta:build`.
 */
class PetaController extends Controller
{
    private function dataPath(string $relative = ''): string
    {
        return resource_path('data/peta' . ($relative ? '/' . ltrim($relative, '/') : ''));
    }

    /** The map page. */
    public function index()
    {
        return view('peta.index', [
            'dataReady' => File::exists($this->dataPath('index.json')),
        ]);
    }

    /** Wilayah tree (Kecamatan > Kelurahan > SLS) that powers the dropdowns. */
    public function indexData(): BinaryFileResponse
    {
        return $this->jsonFile($this->dataPath('index.json'));
    }

    /** Trimmed GeoJSON polygons for a single kelurahan, keyed "{kdkec}_{kddesa}". */
    public function kelurahan(string $key): BinaryFileResponse
    {
        abort_unless(preg_match('/^\d{3}_\d{3}$/', $key), 404);

        return $this->jsonFile($this->dataPath('sls/' . $key . '.json'));
    }

    private function jsonFile(string $path): BinaryFileResponse
    {
        abort_unless(File::exists($path), 404, 'Data peta belum tersedia. Jalankan: php artisan peta:build');

        return response()->file($path, [
            'Content-Type'  => 'application/json',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
