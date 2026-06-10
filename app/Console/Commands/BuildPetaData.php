<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Builds the static data used by the "Peta Wilayah Kerja" (/peta-wilayah) page
 * from the raw SLS GeoJSON export.
 *
 * The raw export (Final_SLS_*.geojson) is ~9 MB with ~30 properties per feature
 * and ~15-decimal coordinates — far too heavy to ship to a phone. This command
 * produces two lighter artefacts under resources/data/peta/ (committed to git, so
 * the page works after deploy with no database import):
 *
 *   index.json                 Wilayah tree (Kecamatan > Kelurahan > SLS) with a
 *                              centroid per SLS. Powers the cascading dropdowns and
 *                              the "navigate to" target. Geometry-free, so small.
 *   sls/{kdkec}_{kddesa}.json  A trimmed GeoJSON FeatureCollection holding only the
 *                              polygons of one kelurahan. Fetched on demand.
 *
 * Coordinates are rounded to 6 decimals (~0.11 m — plenty for SLS boundaries) and
 * properties trimmed to what the map tooltips need.
 *
 * Re-run whenever the SLS export is updated:  php artisan peta:build
 */
class BuildPetaData extends Command
{
    protected $signature = 'peta:build
        {source? : Path to the source GeoJSON (defaults to Final_SLS_*.geojson in the project root)}
        {--precision=6 : Number of decimals to keep for coordinates}';

    protected $description = 'Build the lightweight Peta Wilayah Kerja data files from the raw SLS GeoJSON';

    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');

        $source = $this->argument('source') ?: $this->guessSource();
        if (!$source || !File::exists($source)) {
            $this->error("Source GeoJSON not found. Pass the path explicitly: php artisan peta:build path/to/file.geojson");
            return self::FAILURE;
        }

        $precision = max(4, (int) $this->option('precision'));

        $this->info("Reading: {$source}");
        $raw = json_decode(File::get($source), true);
        if (!isset($raw['features']) || !is_array($raw['features'])) {
            $this->error('Invalid GeoJSON: no "features" array found.');
            return self::FAILURE;
        }

        $features = $raw['features'];
        $this->info('Features: ' . number_format(count($features)));

        $outDir = resource_path('data/peta');
        $slsDir = $outDir . '/sls';
        File::ensureDirectoryExists($slsDir);
        // Clear any previous build so removed kelurahan don't linger.
        foreach (File::glob($slsDir . '/*.json') as $old) {
            File::delete($old);
        }

        $kec = [];            // kdkec => ['k','n','desa'=>[kddesa=>['k','n','file','sls'=>[]]]]
        $kelFeatures = [];    // "{kdkec}_{kddesa}" => [trimmed feature, ...]
        $kabName = null;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($features));
        $bar->start();

        foreach ($features as $f) {
            $p = $f['properties'] ?? [];
            $geom = $f['geometry'] ?? null;

            $kdkec = $this->code($p['kdkec'] ?? null);
            $kddesa = $this->code($p['kddesa'] ?? null);
            $idsls = (string) ($p['idsls'] ?? '');

            if ($kdkec === '' || $kddesa === '' || !$geom || empty($geom['coordinates'])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $kabName ??= $this->cleanName($p['nmkab'] ?? '');
            $nmkec = $this->cleanName($p['nmkec'] ?? '');
            $nmdesa = $this->cleanName($p['nmdesa'] ?? '');
            $nmsls = trim((string) ($p['nmsls'] ?? '')) ?: ('SLS ' . ($p['kdsls'] ?? ''));
            $kelKey = $kdkec . '_' . $kddesa;

            // Compute centroid (bbox centre) before rounding the geometry.
            $bbox = ['minLng' => INF, 'minLat' => INF, 'maxLng' => -INF, 'maxLat' => -INF];
            $this->accumulateBbox($geom['coordinates'], $bbox);
            $lat = round(($bbox['minLat'] + $bbox['maxLat']) / 2, $precision);
            $lng = round(($bbox['minLng'] + $bbox['maxLng']) / 2, $precision);

            // Index tree
            if (!isset($kec[$kdkec])) {
                $kec[$kdkec] = ['k' => $kdkec, 'n' => $nmkec, 'desa' => []];
            }
            if (!isset($kec[$kdkec]['desa'][$kddesa])) {
                $kec[$kdkec]['desa'][$kddesa] = ['k' => $kddesa, 'n' => $nmdesa, 'file' => $kelKey, 'sls' => []];
            }
            $kec[$kdkec]['desa'][$kddesa]['sls'][] = [
                'id'  => $idsls,
                'k'   => (string) ($p['kdsls'] ?? ''),
                'n'   => $nmsls,
                'lat' => $lat,
                'lng' => $lng,
            ];

            // Per-kelurahan trimmed feature
            $kelFeatures[$kelKey][] = [
                'type' => 'Feature',
                'properties' => [
                    'id'     => $idsls,
                    'nmsls'  => $nmsls,
                    'nmdesa' => $nmdesa,
                    'nmkec'  => $nmkec,
                    'lat'    => $lat,
                    'lng'    => $lng,
                ],
                'geometry' => [
                    'type' => $geom['type'],
                    'coordinates' => $this->roundCoords($geom['coordinates'], $precision),
                ],
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Sort: kecamatan & kelurahan by name, SLS naturally by label.
        $kecList = array_values($kec);
        usort($kecList, fn ($a, $b) => strcmp($a['n'], $b['n']));
        foreach ($kecList as &$k) {
            $desa = array_values($k['desa']);
            usort($desa, fn ($a, $b) => strcmp($a['n'], $b['n']));
            foreach ($desa as &$d) {
                usort($d['sls'], fn ($a, $b) => strnatcasecmp($a['n'], $b['n']));
            }
            unset($d);
            $k['desa'] = $desa;
        }
        unset($k);

        $totalSls = array_sum(array_map(
            fn ($k) => array_sum(array_map(fn ($d) => count($d['sls']), $k['desa'])),
            $kecList
        ));

        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        File::put($outDir . '/index.json', json_encode([
            'generated_at' => now()->toIso8601String(),
            'source'       => basename($source),
            'kab'          => $kabName,
            'total_sls'    => $totalSls,
            'kec'          => $kecList,
        ], $jsonFlags));

        // Per-kelurahan geometry files.
        foreach ($kelFeatures as $key => $feats) {
            File::put($slsDir . '/' . $key . '.json', json_encode([
                'type' => 'FeatureCollection',
                'features' => $feats,
            ], $jsonFlags));
        }

        $indexKb = round(File::size($outDir . '/index.json') / 1024, 1);
        $totalKb = round(collect(File::glob($slsDir . '/*.json'))->sum(fn ($f) => File::size($f)) / 1024, 1);

        $this->info('Done.');
        $this->line("  Kecamatan : " . count($kecList));
        $this->line("  Kelurahan : " . count($kelFeatures));
        $this->line("  SLS       : " . number_format($totalSls) . ($skipped ? " (skipped {$skipped})" : ''));
        $this->line("  index.json: {$indexKb} KB");
        $this->line("  sls/*.json: {$totalKb} KB across " . count($kelFeatures) . ' files');
        $this->line("  Output    : " . $outDir);

        return self::SUCCESS;
    }

    /** Best-effort location of the raw export in the project root. */
    private function guessSource(): ?string
    {
        $matches = File::glob(base_path('Final_SLS_*.geojson'));
        return $matches[0] ?? null;
    }

    /** Normalise a wilayah code to a trimmed string ("051", "003", ...). */
    private function code($v): string
    {
        return trim((string) ($v ?? ''));
    }

    /** Collapse the spaced-out BPS names ("B A T A M" -> "Batam") to Title Case. */
    private function cleanName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return $name;
        }
        // Single-spaced single letters => glued ("B A T A M" -> "BATAM").
        if (preg_match('/^(?:[A-Za-z] )+[A-Za-z]$/', $name)) {
            $name = str_replace(' ', '', $name);
        }
        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /** Recursively round a coordinates structure to N decimals. */
    private function roundCoords($coords, int $precision)
    {
        if ($this->isPair($coords)) {
            return [round((float) $coords[0], $precision), round((float) $coords[1], $precision)];
        }
        $out = [];
        foreach ($coords as $c) {
            $out[] = $this->roundCoords($c, $precision);
        }
        return $out;
    }

    /** Recursively expand a bbox over every coordinate pair. */
    private function accumulateBbox($coords, array &$bbox): void
    {
        if ($this->isPair($coords)) {
            $lng = (float) $coords[0];
            $lat = (float) $coords[1];
            if ($lng < $bbox['minLng']) $bbox['minLng'] = $lng;
            if ($lng > $bbox['maxLng']) $bbox['maxLng'] = $lng;
            if ($lat < $bbox['minLat']) $bbox['minLat'] = $lat;
            if ($lat > $bbox['maxLat']) $bbox['maxLat'] = $lat;
            return;
        }
        foreach ($coords as $c) {
            $this->accumulateBbox($c, $bbox);
        }
    }

    /** True when $v is a [lng, lat] coordinate pair (numbers, not nested arrays). */
    private function isPair($v): bool
    {
        return is_array($v) && isset($v[0]) && is_numeric($v[0]) && !is_array($v[0]);
    }
}
