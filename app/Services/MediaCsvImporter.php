<?php

namespace App\Services;

use App\Models\MediaOutlet;
use App\Models\Package;
use App\Support\CatalogCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use SplFileObject;

/**
 * Bulk-imports media outlets from CSV (columns: name, website_url, logo_url, category, is_active).
 * Every row is validated; results report imported, updated, skipped and failed rows with reasons.
 */
class MediaCsvImporter
{
    public const MAX_ROWS = 5000;

    public const COLUMNS = ['name', 'website_url', 'logo_url', 'category', 'is_active'];

    /**
     * @return array{imported: int, updated: int, skipped: int, failed: int, errors: array<int, array{row: int, name: string, message: string}>}
     */
    public function import(string $path, bool $updateExisting = false, ?Package $attachTo = null): array
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE);

        $header = null;
        $result = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];
        $seen = [];
        $attachIds = [];
        $categories = collect(config('vmnewswire.media_categories'))->mapWithKeys(fn ($c) => [mb_strtolower($c) => $c]);

        foreach ($file as $index => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($header === null) {
                $header = array_map(fn ($h) => mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $row);
                if (! in_array('name', $header, true) || ! in_array('category', $header, true)) {
                    throw new RuntimeException('The CSV header must include at least "name" and "category" columns.');
                }

                continue;
            }

            $line = $index + 1;
            if ($line - 1 > self::MAX_ROWS) {
                throw new RuntimeException('The CSV has more than '.self::MAX_ROWS.' rows. Split it into smaller files.');
            }

            $data = [];
            foreach (self::COLUMNS as $column) {
                $pos = array_search($column, $header, true);
                $data[$column] = $pos === false ? null : trim((string) ($row[$pos] ?? ''));
            }
            $data = array_map(fn ($v) => $v === '' ? null : $v, $data);
            $data['category'] = $categories[mb_strtolower((string) $data['category'])] ?? $data['category'];

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'website_url' => ['nullable', 'url:http,https', 'max:255'],
                'logo_url' => ['nullable', 'url:https', 'max:255'],
                'category' => ['required', Rule::in($categories->values()->all())],
                'is_active' => ['nullable', Rule::in(['1', '0', 'true', 'false', 'yes', 'no', 'TRUE', 'FALSE', 'Yes', 'No'])],
            ]);

            $name = (string) ($data['name'] ?? '');

            if ($validator->fails()) {
                $result['failed']++;
                $result['errors'][] = ['row' => $line, 'name' => $name, 'message' => implode(' ', $validator->errors()->all())];

                continue;
            }

            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                $result['skipped']++;
                $result['errors'][] = ['row' => $line, 'name' => $name, 'message' => "Duplicate of row {$seen[$key]} in this file."];

                continue;
            }
            $seen[$key] = $line;

            $attributes = [
                'website_url' => $data['website_url'],
                'logo_url' => $data['logo_url'],
                'category' => $data['category'],
                'is_active' => $data['is_active'] === null ? true : filter_var($data['is_active'], FILTER_VALIDATE_BOOL),
            ];

            $existing = MediaOutlet::where('name', $name)->first();

            if ($existing && ! $updateExisting) {
                $result['skipped']++;
                $result['errors'][] = ['row' => $line, 'name' => $name, 'message' => 'Already exists (enable "Update existing outlets" to overwrite).'];
                $attachIds[] = $existing->id;

                continue;
            }

            DB::transaction(function () use ($existing, $name, $attributes, &$result, &$attachIds) {
                if ($existing) {
                    $existing->update($attributes);
                    $result['updated']++;
                    $attachIds[] = $existing->id;
                } else {
                    $attachIds[] = MediaOutlet::create(['name' => $name] + $attributes)->id;
                    $result['imported']++;
                }
            });
        }

        if ($header === null) {
            throw new RuntimeException('The CSV file is empty.');
        }

        if ($attachTo && $attachIds) {
            $already = $attachTo->mediaOutlets()->pluck('media_outlets.id')->all();
            $order = (int) DB::table('package_media')->where('package_id', $attachTo->id)->max('display_order');
            $attachTo->mediaOutlets()->attach(
                collect($attachIds)->unique()->diff($already)
                    ->mapWithKeys(fn ($id) => [$id => ['is_featured' => false, 'display_order' => ++$order]])->all()
            );
        }

        CatalogCache::flush();

        return $result;
    }
}
