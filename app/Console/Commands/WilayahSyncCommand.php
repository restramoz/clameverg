<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Kecamatan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature('wilayah:sync {--file= : Path to BPS master wilayah JSON file} {--dry-run : Run without persisting changes}')]
#[Description('Sync official BPS master wilayah data from master_wilayah_bps.json into database')]
class WilayahSyncCommand extends Command
{
    private const HUB_SLUGS = [
        'malang', 'surabaya', 'bandung', 'semarang', 'medan', 'makassar',
        'balikpapan', 'palembang', 'batam', 'denpasar', 'samarinda',
        'pekanbaru', 'yogyakarta', 'banjarmasin', 'padang',
        'bandar-lampung', 'pontianak', 'manado', 'kab-bekasi', 'kab-karawang',
        'kab-bogor', 'kab-tangerang', 'kab-sidoarjo', 'kab-gresik',
        'jakarta-pusat', 'jakarta-selatan', 'jakarta-timur', 'jakarta-barat', 'jakarta-utara',
        'kota-jakarta-pusat', 'kota-jakarta-selatan', 'kota-jakarta-timur', 'kota-jakarta-barat', 'kota-jakarta-utara',
        'kota-surabaya', 'kota-bandung', 'kota-semarang', 'kota-medan', 'kota-makassar',
        'kota-balikpapan', 'kota-palembang', 'kota-batam', 'kota-denpasar', 'kota-samarinda',
        'kota-pekanbaru', 'kota-yogyakarta', 'kota-malang', 'kota-banjarmasin', 'kota-padang',
        'kota-bandar-lampung', 'kota-pontianak', 'kota-manado', 'kabupaten-bekasi', 'kabupaten-karawang',
        'kabupaten-bogor', 'kabupaten-tangerang', 'kabupaten-sidoarjo', 'kabupaten-gresik',
    ];

    public function handle(): int
    {
        $filePath = $this->option('file') ?: base_path('master_wilayah_bps.json');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in dry-run mode (no DB changes will be persisted)');
        }

        if (! file_exists($filePath)) {
            $this->error("BPS JSON file not found at: {$filePath}");

            return Command::FAILURE;
        }

        $this->info("Reading BPS data from: {$filePath}");
        $rawContent = file_get_contents($filePath);
        $records = json_decode($rawContent, true);

        if (! $records || ! is_array($records)) {
            $this->error('Failed to parse JSON file');

            return Command::FAILURE;
        }

        $this->info('Found '.count($records).' records in master_wilayah_bps.json.');

        // Collect official BPS codes from source data
        $officialCityBps = [];
        $officialKecBps = [];

        foreach ($records as $r) {
            $cCode = trim((string) ($r['Kode Kab/Kota'] ?? ''));
            $kCode = trim((string) ($r['Kode Kecamatan'] ?? ''));
            if ($cCode !== '') {
                $officialCityBps[$cCode] = true;
            }
            if ($kCode !== '') {
                $officialKecBps[$kCode] = true;
            }
        }

        $this->info('Official BPS cities: '.count($officialCityBps).', districts: '.count($officialKecBps));

        // Deactivate legacy records not in official BPS data
        $deactivatedCities = 0;
        $deactivatedKecs = 0;

        if (! $dryRun) {
            $deactivatedCities = City::where(function ($query) use ($officialCityBps) {
                $query->whereNotIn('bps_code', array_keys($officialCityBps))
                    ->orWhereNull('bps_code');
            })->update(['status' => 'inactive']);

            $deactivatedKecs = Kecamatan::where(function ($query) use ($officialKecBps) {
                $query->whereNotIn('bps_code', array_keys($officialKecBps))
                    ->orWhereNull('bps_code');
            })->update(['status' => 'inactive']);

            $this->info("Deactivated {$deactivatedCities} non-BPS cities and {$deactivatedKecs} non-BPS kecamatans.");
        } else {
            $deactivatedCities = City::where(function ($query) use ($officialCityBps) {
                $query->whereNotIn('bps_code', array_keys($officialCityBps))
                    ->orWhereNull('bps_code');
            })->count();

            $deactivatedKecs = Kecamatan::where(function ($query) use ($officialKecBps) {
                $query->whereNotIn('bps_code', array_keys($officialKecBps))
                    ->orWhereNull('bps_code');
            })->count();

            $this->info("[Dry-run] Would deactivate {$deactivatedCities} non-BPS cities and {$deactivatedKecs} non-BPS kecamatans.");
        }

        // Build city data from records
        $cityMap = [];
        $now = now()->toDateTimeString();

        foreach ($records as $r) {
            $cityBps = trim((string) ($r['Kode Kab/Kota'] ?? ''));
            if ($cityBps === '' || isset($cityMap[$cityBps])) {
                continue;
            }

            $type = trim($r['Jenis Wilayah'] ?? '');
            $rawName = trim($r['Kabupaten/Kota'] ?? '');
            $fullName = ($type === 'Kota') ? 'Kota '.$rawName : 'Kabupaten '.$rawName;
            $slug = ($type === 'Kota') ? Str::slug($rawName) : 'kab-'.Str::slug($rawName);
            $provName = trim($r['Provinsi'] ?? '');
            $provBps = trim((string) ($r['Kode Provinsi'] ?? ''));
            $island = $this->getIsland($provBps);

            $isHub = in_array($slug, self::HUB_SLUGS)
                || in_array("kota-{$slug}", self::HUB_SLUGS)
                || in_array('kab-'.Str::slug($rawName), self::HUB_SLUGS)
                || in_array('kabupaten-'.Str::slug($rawName), self::HUB_SLUGS);

            $cityMap[$cityBps] = [
                'name' => $fullName,
                'slug' => $slug,
                'bps_code' => $cityBps,
                'bps_province_code' => $provBps,
                'province' => $provName,
                'island' => $island,
                'classification' => $type,
                'is_hub' => $isHub ? 1 : 0,
                'status' => 'active',
                'updated_at' => $now,
            ];
        }

        $this->info('Processing '.count($cityMap).' official BPS cities...');

        // Upsert cities
        $citiesInserted = 0;
        $citiesUpdated = 0;

        if (! $dryRun) {
            DB::transaction(function () use ($cityMap, $now, &$citiesInserted, &$citiesUpdated) {
                foreach ($cityMap as $bpsCode => $data) {
                    $city = City::where('bps_code', $bpsCode)->first();
                    if ($city) {
                        $city->update($data);
                        $citiesUpdated++;
                    } else {
                        $data['created_at'] = $now;
                        City::create($data);
                        $citiesInserted++;
                    }
                }
            });
        } else {
            foreach ($cityMap as $bpsCode => $data) {
                $city = City::where('bps_code', $bpsCode)->first();
                if ($city) {
                    $citiesUpdated++;
                } else {
                    $citiesInserted++;
                }
            }
        }

        $this->info("Cities: {$citiesInserted} inserted, {$citiesUpdated} updated.");

        // Build city_id lookup by bps_code
        $cityIdLookup = City::pluck('id', 'bps_code')->toArray();

        // Upsert kecamatans
        $kecsInserted = 0;
        $kecsUpdated = 0;
        $kecsSkipped = 0;
        $districtSlugTracker = [];

        if (! $dryRun) {
            DB::transaction(function () use ($records, $cityIdLookup, $now, &$kecsInserted, &$kecsUpdated, &$kecsSkipped, &$districtSlugTracker) {
                foreach ($records as $r) {
                    $cityBps = trim((string) ($r['Kode Kab/Kota'] ?? ''));
                    $cityId = $cityIdLookup[$cityBps] ?? null;

                    if (! $cityId) {
                        $kecsSkipped++;

                        continue;
                    }

                    $kecBps = trim((string) ($r['Kode Kecamatan'] ?? ''));
                    $kecName = trim($r['Kecamatan'] ?? '');

                    if ($kecBps === '' || $kecName === '') {
                        $kecsSkipped++;

                        continue;
                    }

                    $baseSlug = Str::slug($kecName) ?: 'kecamatan-'.$kecBps;
                    $kecSlug = $baseSlug;

                    $counter = 1;
                    while (isset($districtSlugTracker[$cityId][$kecSlug])) {
                        $kecSlug = "{$baseSlug}-{$counter}";
                        $counter++;
                    }
                    $districtSlugTracker[$cityId][$kecSlug] = true;

                    $kecData = [
                        'city_id' => $cityId,
                        'name' => $kecName,
                        'slug' => $kecSlug,
                        'bps_code' => $kecBps,
                        'status' => 'active',
                        'updated_at' => $now,
                    ];

                    $kec = Kecamatan::where('bps_code', $kecBps)
                        ->where('city_id', $cityId)
                        ->first();

                    if ($kec) {
                        $kec->update($kecData);
                        $kecsUpdated++;
                    } else {
                        $kecData['created_at'] = $now;
                        Kecamatan::create($kecData);
                        $kecsInserted++;
                    }
                }
            });
        } else {
            foreach ($records as $r) {
                $cityBps = trim((string) ($r['Kode Kab/Kota'] ?? ''));
                $cityId = $cityIdLookup[$cityBps] ?? null;

                if (! $cityId) {
                    $kecsSkipped++;

                    continue;
                }

                $kecBps = trim((string) ($r['Kode Kecamatan'] ?? ''));
                $kecName = trim($r['Kecamatan'] ?? '');

                if ($kecBps === '' || $kecName === '') {
                    $kecsSkipped++;

                    continue;
                }

                $baseSlug = Str::slug($kecName) ?: 'kecamatan-'.$kecBps;
                $kecSlug = $baseSlug;

                $counter = 1;
                while (isset($districtSlugTracker[$cityId][$kecSlug])) {
                    $kecSlug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $districtSlugTracker[$cityId][$kecSlug] = true;

                $kec = Kecamatan::where('bps_code', $kecBps)
                    ->where('city_id', $cityId)
                    ->first();

                if ($kec) {
                    $kecsUpdated++;
                } else {
                    $kecsInserted++;
                }
            }
        }

        $this->info("Kecamatans: {$kecsInserted} inserted, {$kecsUpdated} updated, {$kecsSkipped} skipped.");

        // Final verification
        $totalCities = City::where('status', 'active')->count();
        $totalKecs = Kecamatan::where('status', 'active')->count();

        $this->info("Verification: {$totalCities} active cities, {$totalKecs} active kecamatans.");
        $this->info('Wilayah sync completed successfully!');

        return Command::SUCCESS;
    }

    private function getIsland(string $provCode): string
    {
        $prefix = substr($provCode, 0, 2);

        return match ($prefix) {
            '11', '12', '13', '14', '15', '16', '17', '18', '19', '21' => 'Sumatera',
            '31', '32', '33', '34', '35', '36' => 'Jawa',
            '51', '52', '53' => 'Bali & Nusa Tenggara',
            '61', '62', '63', '64', '65' => 'Kalimantan',
            '71', '72', '73', '74', '75', '76' => 'Sulawesi',
            '81', '82' => 'Maluku',
            '91', '92', '94', '95', '96', '97' => 'Papua',
            default => 'Indonesia',
        };
    }
}
