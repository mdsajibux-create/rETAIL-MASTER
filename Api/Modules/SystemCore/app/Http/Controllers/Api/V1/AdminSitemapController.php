<?php

namespace Modules\SystemCore\app\Http\Controllers\Api\V1;

use App\Services\SitemapService;
use Illuminate\Http\Request;
use Modules\SystemCore\app\Models\Sitemap;

class AdminSitemapController
{
    public function generate(Request $request, SitemapService $sitemapService)
    {
        if ($request->isMethod('POST')) {
            return $sitemapService->generate(); // handles generation, DB insert, and download
        } else {
            $sitemap = Sitemap::orderBy('generated_at', 'desc')->first();
            return response()->json([
                'data' => $sitemap
            ]);
        }
    }

}
