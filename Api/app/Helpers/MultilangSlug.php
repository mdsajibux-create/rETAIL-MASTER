<?php
namespace App\Helpers;

use Illuminate\Support\Str;

class MultilangSlug
{

    public static function makeSlug(string $model, $slugText, string $field = '', string $divider = null): string
    {
        $slugText = match (true) {
            !empty($slugText)  => $slugText,
            empty($slugText)   => 'auto-generated-string',
        };
        return MultilangSlug::globalSlugify(model: $model, slugText: $slugText,  field: $field, divider: $divider);
    }


    public static function globalSlugify(string $model, string $slugText,  string $field = '', string $divider = null): string
    {
        try {

            $id = 0;
            $divider = empty($divider) ? config('multilang-slug.separator') : $divider;
            $query = $model::query();

            $cleanString = preg_replace("/[~`{}.'\"\!\@\#\$\%\^\&\*\(\)\_\=\+\/\?\>\<\,\[\]\:\;\|\\\]/", "", $slugText);
            $cleanString = preg_replace("/[\/_|+ -]+/", '-', $cleanString);
            $slug = strtolower($cleanString);

            if ($field) {
                $slugCount = $query->where($field, $slug)->get();
            } else {
                $field = 'slug';
                $slugCount = $query->where('slug', $slug)->get();
            }
            $uniqueSlug = Str::random(config('multilang-slug.random_text'));


            if (empty($slugCount->count())) {
                $slug = is_numeric($slug) ? "{$slug}{$divider}{$uniqueSlug}" : $slug;
                return $slug;
            }


            if (config('multilang-slug.unique_slug')) {
                return "{$slug}{$divider}{$uniqueSlug}";
            } else {
                $allSlugs = MultilangSlug::getRelatedSlugs($slug, $id, $model, $field);
                for ($i = 1; $i <= config('multilang-slug.max_count'); $i++) {
                    $newSlug = $slug . $divider . $i;
                    if (!$allSlugs->contains("$field", $newSlug)) {
                        return $newSlug;
                    }
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private static function getRelatedSlugs($slug, $id, $model, $field)
    {
        if (empty($id)) {
            $id = 0;
        }
        return $model::select("$field")
            ->where("$field", 'like', $slug . '%')
            ->where('id', '<>', $id)
            ->get();
    }
}