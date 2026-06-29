<?php

namespace App\Http\Controllers\Api\V1\Com;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\FooterSettingsPublicResource;
use Modules\SystemCore\app\Models\SettingOption;


class  HeaderFooterController extends Controller
{
    public function siteFooterInfo()
    {
        $footer_settings = SettingOption::with('translations')
            ->where('option_name', 'footer_settings')
            ->first();
        if (!$footer_settings) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
        $json_decoded = json_decode($footer_settings->option_value, true);
        $content = jsonImageModifierFormatter($json_decoded);
        $footer_settings->option_value = $content;

        return response()->json([
            'data' => new FooterSettingsPublicResource($footer_settings)
        ]);

    }
}
