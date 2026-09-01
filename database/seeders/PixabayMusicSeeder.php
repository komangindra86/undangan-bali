<?php

namespace Database\Seeders;

use App\Models\Music;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PixabayMusicSeeder extends Seeder
{
    private const LICENSE_CODE = 'PIXABAY-CONTENT-LICENSE';

    private const LICENSE_URL = 'https://pixabay.com/service/license-summary/';

    public function run(): void
    {
        $manifestPath = resource_path('music/pixabay-catalog.json');
        $manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $catalogKeys = array_map(fn (array $entry) => 'pixabay/'.$entry['asset_id'], $manifest);

        // Existing tracks remain stored for already-published invitations, but disappear from the picker.
        Music::query()
            ->where(fn ($query) => $query->whereNull('catalog_key')->orWhereNotIn('catalog_key', $catalogKeys))
            ->update(['is_active' => false]);

        foreach ($manifest as $entry) {
            $this->seedTrack($entry, $manifestPath);
        }
    }

    private function seedTrack(array $entry, string $manifestPath): void
    {
        $id = (int) $entry['asset_id'];
        $catalogKey = 'pixabay/'.$id;
        $fullPath = 'musics/pixabay/'.$id.'.mp3';
        $previewPath = 'musics/pixabay/previews/'.$id.'.mp3';
        // These curated assets are deployed with the application, so tests using Storage::fake still validate the real catalog.
        $full = storage_path('app/public/'.$fullPath);
        $preview = storage_path('app/public/'.$previewPath);

        foreach ([$full, $preview] as $audio) {
            if (! is_file($audio) || filesize($audio) < 20_000) {
                throw new RuntimeException('Audio Pixabay belum tersedia atau rusak: '.$audio);
            }
        }

        if (array_diff($entry['categories'], ['pernikahan', 'ulang-tahun'])) {
            throw new RuntimeException('Kategori musik Pixabay tidak valid: '.$id);
        }

        $audioHash = hash_file('sha256', $full);
        $previewHash = hash_file('sha256', $preview);
        $evidencePath = 'music-licenses/pixabay/'.$id.'/evidence.json';
        $attribution = $entry['title'].' oleh '.$entry['artist'].' (Pixabay Content ID '.$id.').';
        $changes = 'Audio sumber dikompresi ke MP3 96 kbps. Preview 30 detik dikompresi ke 80 kbps dengan fade-out.';

        Storage::disk('local')->put($evidencePath, json_encode([
            'asset_id' => $id,
            'title' => $entry['title'],
            'artist' => $entry['artist'],
            'original_filename' => $entry['original_filename'],
            'source_url' => $entry['source_url'],
            'license_code' => self::LICENSE_CODE,
            'license_url' => self::LICENSE_URL,
            'audio_sha256' => $audioHash,
            'preview_sha256' => $previewHash,
            'manifest_sha256' => hash_file('sha256', $manifestPath),
            'reviewed_at' => '2026-09-01',
            'modifications' => $changes,
            'usage' => 'Audio accompanies invitations; preview is only loaded on demand. Files are not offered as standalone downloads.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        $existing = Music::where('catalog_key', $catalogKey)->first();
        Music::updateOrCreate(['catalog_key' => $catalogKey], [
            'title' => $entry['title'],
            'artist' => $entry['artist'],
            'artist_url' => $entry['source_url'],
            'categories' => $entry['categories'],
            'source_url' => $entry['source_url'],
            'license_code' => self::LICENSE_CODE,
            'license_url' => self::LICENSE_URL,
            'attribution' => $attribution,
            'modifications' => $changes,
            'file_path' => $fullPath,
            'preview_file_path' => $previewPath,
            'duration_seconds' => $entry['duration_seconds'],
            'file_bytes' => filesize($full),
            'audio_sha256' => $audioHash,
            'preview_sha256' => $previewHash,
            'license_evidence_path' => $evidencePath,
            'license_verified_at' => '2026-09-01 00:00:00',
            'is_active' => $existing ? $existing->is_active : true,
        ]);
    }
}
