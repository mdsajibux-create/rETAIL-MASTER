<?php

namespace Modules\SystemCore\app\Http\Controllers\Api\V1;

use App\Http\Resources\Translation\AllThemeSettingsTranslationResource;
use App\Http\Resources\Translation\ThemeSettingsTranslationResource;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\SystemCore\app\Models\SettingOption;

class ThemeManageController
{
    public function themes(Request $request)
    {

        $all_themes = SettingOption::with('translations')
            ->whereIn('option_name', ['theme_one', 'theme_two'])
            ->get();

        if ($all_themes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Theme settings not found.'
            ], 404);
        }

        // Transform theme data into a clean list
             $themes = $all_themes->map(function ($theme) {
            $data = json_decode($theme->option_value, true);

            // active theme
            $config_theme = config('themes.active_theme') ?? config('themes.default_theme');

            return [
                "name"        => $data['name'] ?? null,
                "slug"        => $theme->option_name,
                "description" => $data['description'] ?? null,
                "version"     => $data['version'] ?? null,
                "status" => $config_theme === $theme->option_name ? 'active' : 'inactive',
                "translations" => $theme->translations->mapWithKeys(function ($t) {
                    return [$t->language_code => $t->value];
                })
            ];
        });

        return response()->json([
            "themes" => $themes,
        ],200);
    }

    public function themeDataGet(Request $request){

        $theme_settings = SettingOption::with(['translations'])
            ->where('option_name', $request->theme_slug)
            ->first();

        //  translation data for theme settings
      $translations =  Translation::where('translatable_type', 'Modules\SystemCore\app\Models\SettingOption')
            ->where('translatable_id', $theme_settings->id)
            ->where('key', 'theme_data')
            ->get();


        if (!$theme_settings) {
            return response()->json([
                'success' => false,
                'message' => 'Theme settings not found.'
            ], 404);
        }

        // Decode JSON stored in option_value
        $json_decoded = json_decode($theme_settings->option_value, true);
        $theme_data = jsonImageModifierFormatter($json_decoded);
        $theme_settings->option_value = $theme_data;

        // modified translate data with add theme slug
        $translations = $translations->groupBy('language')
            ->map(function ($item) use ($theme_settings) {
                $item->theme_slug = $theme_settings['option_value']['slug'];  // attach slug
                return $item;
            });


        return response()->json([
            "theme_data" => $theme_settings->option_value,
            "translations"  => ThemeSettingsTranslationResource::collection($translations),
        ], 200);
    }

    public function storeTheme(Request $request){
         $theme_slug = $request->theme_data['slug'];

        $validator = Validator::make($request->all(), [
            'theme_data' => 'required|array',
            'theme_data.name' => 'required|string|max:255',
            'theme_data.slug' => [ 'required', 'string', 'max:255'],
            'theme_data.description' => 'nullable|string|max:1000',
            'theme_data.theme_style' => 'required|array',
            'theme_data.theme_header' => 'required|array',
            'theme_data.theme_footer' => 'nullable|array',
            'theme_data.theme_homepage' => 'nullable|array',
            'theme_data.theme_pages' => 'nullable|array',
            'translations' => 'nullable|array',
        ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // check theme system
            if(!in_array($theme_slug,['theme_one', 'theme_two'])){
                return response()->json([
                   'success' => false,
                   'message' => 'Theme settings not found.'
                ]);
            }

            $themeData = $request->input('theme_data', []);
            $translationKeys = ['theme_data'];

            // Store theme configuration
            $theme_settings = SettingOption::updateOrCreate(
                ['option_name' => $theme_slug],
                ['option_value' => json_encode($themeData)]
            );

            // Save translations
            if ($request->has('translations')) {
                createOrUpdateTranslationJson($request, $theme_settings->id, 'Modules\SystemCore\app\Models\SettingOption', $translationKeys);
            }
            return response()->json([
                'message' => __('messages.save_success', ['name' => 'Theme Settings']),
            ],201);
    }

    public function themeActive(Request $request){
        $theme_slug = $request->theme_slug;
        // Validation
        $validator = Validator::make($request->all(), [
            'theme_slug' => 'required|string|max:255',
        ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

        // Check if theme exists in available themes config
        $availableThemes = config('themes.available_themes', []);
        if (!in_array($theme_slug, $availableThemes)) {
            return response()->json([
                'success' => false,
                'message' => __('Theme not found.'),
            ], 404);
        }

        try {
            // Check if theme exists in database
            $theme = SettingOption::where('option_name', $theme_slug)->first();
            if (!$theme) {
                return response()->json([
                    'success' => false,
                    'message' => __('Theme not found.'),
                ], 404);
            }

            // Update config with new active theme
            $this->updateThemeConfig($theme_slug);

            Cache::forget('home_settings_data:theme=theme_one:lang=en:platform=web');
            Cache::forget('home_settings_data:theme=theme_two:lang=en:platform=web');
            Cache::forget('home_settings_data:theme=theme_one:lang=en:platform=android');
            Cache::forget('home_settings_data:theme=theme_two:lang=en:platform=android');
            Cache::forget('home_settings_data:theme=theme_one:lang=en:platform=ios');
            Cache::forget('home_settings_data:theme=theme_two:lang=en:platform=ios');

            return response()->json([
                'message' => __('Theme Successfully Activated'),
            ],200);

        }catch (\Exception $exception){}

    }

    private function updateThemeConfig(string $themeSlug): void
    {
        $configPath = config_path('themes.php');
        $config = include $configPath;

        // Update active theme in config
        $config['active_theme'] = $themeSlug;

        // Write updated config back to file
        $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        file_put_contents($configPath, $configContent);
    }


    public function clearThemeCache()
    {
        try {
            // Clear specific theme caches
            $availableThemes = config('themes.available_themes', []);
            foreach ($availableThemes as $theme) {
                Cache::forget("theme_data_{$theme}");
            }

            // Clear general theme caches
            Cache::forget('active_theme');
            Cache::forget('active_theme_data');
            Cache::forget('active_theme_full_data');
            Cache::forget('all_themes_data');

            // Clear config cache
            Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => __('Theme cache cleared successfully'),
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to clear theme cache'),
            ], 500);
        }
    }


}
