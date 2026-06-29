<?php

namespace App\Repositories;

use App\Interfaces\BlogManageInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Blog\app\Models\Blog;
use Modules\Blog\app\Models\BlogCategory;

class BlogManageRepository implements BlogManageInterface
{
    public function __construct(protected Blog $blog, protected BlogCategory $blogCategory, protected Translation $translation)
    {
    }

    public function translationKeysForBlog(): mixed
    {
        return $this->blog->translationKeys;
    }

    public function translationKeysForCategory(): mixed
    {
        return $this->blogCategory->translationKeys;
    }

    public function getPaginatedCategory(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $blogCategory = BlogCategory::leftJoin('translations as name_translations', function ($join) use ($language) {
            $join->on('blog_categories.id', '=', 'name_translations.translatable_id')
                ->where('name_translations.translatable_type', '=', BlogCategory::class)
                ->where('name_translations.language', '=', $language)
                ->where('name_translations.key', '=', 'name');
        })
            ->leftJoin('translations as meta_title_translations', function ($join) use ($language) {
                $join->on('blog_categories.id', '=', 'meta_title_translations.translatable_id')
                    ->where('meta_title_translations.translatable_type', '=', BlogCategory::class)
                    ->where('meta_title_translations.language', '=', $language)
                    ->where('meta_title_translations.key', '=', 'meta_title');
            })
            ->leftJoin('translations as meta_description_translations', function ($join) use ($language) {
                $join->on('blog_categories.id', '=', 'meta_description_translations.translatable_id')
                    ->where('meta_description_translations.translatable_type', '=', BlogCategory::class)
                    ->where('meta_description_translations.language', '=', $language)
                    ->where('meta_description_translations.key', '=', 'meta_description');
            })
            ->select(
                'blog_categories.*',
                DB::raw('COALESCE(name_translations.value, blog_categories.name) as name'),
                DB::raw('COALESCE(meta_title_translations.value, blog_categories.meta_title) as meta_title'),
                DB::raw('COALESCE(meta_description_translations.value, blog_categories.meta_description) as meta_description')
            );
        // Apply search filter if search parameter exists
        if ($search) {
            $blogCategory->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', blog_categories.name, name_translations.value)"), 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT_WS(' ', blog_categories.meta_title, meta_title_translations.value)"), 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT_WS(' ', blog_categories.meta_description, meta_description_translations.value)"), 'like', "%{$search}%");
            });
        }
        // Apply sorting and pagination
        // Return the result
        return $blogCategory
            ->with('related_translations')
            ->orderBy($sortField, $sort)
            ->paginate($limit);
    }

    public function getCategoryById(int|string $id)
    {
        try {
            $blogCategory = BlogCategory::with('related_translations')->find($id);

            if ($blogCategory) {
                return $blogCategory;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function getPaginatedBlog(int|string $per_page, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $blog = Blog::leftJoin('translations as title_translations', function ($join) use ($language) {
            $join->on('blogs.id', '=', 'title_translations.translatable_id')
                ->where('title_translations.translatable_type', '=', Blog::class)
                ->where('title_translations.language', '=', $language)
                ->where('title_translations.key', '=', 'title');
        })
            ->leftJoin('translations as description_translations', function ($join) use ($language) {
                $join->on('blogs.id', '=', 'description_translations.translatable_id')
                    ->where('description_translations.translatable_type', '=', Blog::class)
                    ->where('description_translations.language', '=', $language)
                    ->where('description_translations.key', '=', 'description');
            })
            ->leftJoin('translations as meta_title_translations', function ($join) use ($language) {
                $join->on('blogs.id', '=', 'meta_title_translations.translatable_id')
                    ->where('meta_title_translations.translatable_type', '=', Blog::class)
                    ->where('meta_title_translations.language', '=', $language)
                    ->where('meta_title_translations.key', '=', 'meta_title');
            })
            ->leftJoin('translations as meta_description_translations', function ($join) use ($language) {
                $join->on('blogs.id', '=', 'meta_description_translations.translatable_id')
                    ->where('meta_description_translations.translatable_type', '=', Blog::class)
                    ->where('meta_description_translations.language', '=', $language)
                    ->where('meta_description_translations.key', '=', 'meta_description');
            })
            ->leftJoin('translations as meta_keywords_translations', function ($join) use ($language) {
                $join->on('blogs.id', '=', 'meta_keywords_translations.translatable_id')
                    ->where('meta_keywords_translations.translatable_type', '=', Blog::class)
                    ->where('meta_keywords_translations.language', '=', $language)
                    ->where('meta_keywords_translations.key', '=', 'meta_keywords');
            })
            ->select(
                'blogs.*',
                DB::raw('COALESCE(title_translations.value, blogs.title) as title'),
                DB::raw('COALESCE(description_translations.value, blogs.description) as description'),
                DB::raw('COALESCE(meta_title_translations.value, blogs.meta_title) as meta_title'),
                DB::raw('COALESCE(meta_description_translations.value, blogs.meta_description) as meta_description'),
                DB::raw('COALESCE(meta_keywords_translations.value, blogs.meta_keywords) as meta_keywords')
            );
        // Apply search filter if search parameter exists
        if ($search) {
            $blog->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', blogs.title, blogs.description, blogs.meta_title, blogs.meta_description, blogs.meta_keywords)"), 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT_WS(' ', title_translations.value, description_translations.value, meta_title_translations.value, meta_description_translations.value, meta_keywords_translations.value)"), 'like', "%{$search}%");
            });
        }
        // Apply sorting and pagination
        // Return the result
        return $blog
            ->with(['category.related_translations', 'admin', 'related_translations'])
            ->orderBy($sortField, $sort)
            ->paginate($per_page);

    }

    public function getBlogById(int|string $id)
    {
        try {
            $blog = Blog::with('related_translations')->find($id);

            if (!$blog) {
                return false;
            }

            return $blog;

        } catch (\Throwable $th) {
            return false;
        }
    }

    public function store(array $data, string $modelClass)
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("The provided model class does not exist: $modelClass");
        }
        try {
            $data = Arr::except($data, ['translations']);
            $final = $modelClass::create($data);
            return $final->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data, string $modelClass)
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("The provided model class does not exist: $modelClass");
        }
        try {
            $final = $modelClass::findOrFail($data['id']);
            if ($final) {
                $data = Arr::except($data, ['translations']);
                $final->update($data);
                return $final->id;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete(int|string $id, string $modelClass)
    {
        try {
            $final = $modelClass::findOrFail($id);
            $this->deleteTranslation($final->id, $modelClass);
            $final->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function deleteTranslation(int|string $id, string $translatable_type)
    {
        try {
            $translation = Translation::where('translatable_id', $id)
                ->where('translatable_type', $translatable_type)
                ->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }
                    // Collect translation data
                    $translations[] = [
                        'translatable_type' => $refPath,
                        'translatable_id' => $refid,
                        'language' => $translation['language_code'],
                        'key' => $key,
                        'value' => $translatedValue,
                    ];
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }

                    $trans = $this->translation->where('translatable_type', $refPath)->where('translatable_id', $refid)
                        ->where('language', $translation['language_code'])->where('key', $key)->first();
                    if ($trans != null) {
                        $trans->value = $translatedValue;
                        $trans->save();
                    } else {
                        $translations[] = [
                            'translatable_type' => $refPath,
                            'translatable_id' => $refid,
                            'language' => $translation['language_code'],
                            'key' => $key,
                            'value' => $translatedValue,
                        ];
                    }
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }

}
