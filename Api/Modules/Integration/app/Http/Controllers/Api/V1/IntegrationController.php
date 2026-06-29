<?php

namespace Modules\Integration\app\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\Integration\app\Models\Integration;

class IntegrationController
{

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $integrations = Integration::where('type', $request->type)->get();
        return response()->json([
            'status' => 'success',
            'data' => $integrations,
        ], 200);
    }

    public function getIntegration($id=null)
    {
        $integration = Integration::find($id);

        if (is_null($integration)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Integration not found',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $integration,
        ], 200);
    }

    public function updateIntegration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|exists:integrations,id',
            'name' => 'required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'required|string|in:ai,social_platform,login_provider,api_service,stock_api,wa_api,ai_config',
            'platform' => 'required|string',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $validated = $validator->validated();

            // Update
            if (!empty($validated['id'])) {
                // Update existing integration
                $integration = Integration::findOrFail($validated['id']);

                // check if social_platform tiktok_business file add
                if ($validated['type'] === 'social_platform' && $validated['platform'] === 'tiktok_business') {
                    $file = $validated['config']['verification_file'];
                    $file = Storage::class($file);
                }

                // Handle TikTok Business verification file
                if (
                    $validated['type'] === 'social_platform' &&
                    $validated['platform'] === 'tiktok_business' &&
                    isset($validated['config']['verification_file']) &&
                    $request->hasFile('verification_file')
                ) {
                    $file = $request->file('verification_file');
                    // Save it inside: storage/app/public/uploads/social-ai/
                    $path = $file->store('uploads/social-ai', 'public');


                    // Update config with stored file path
                    $config = $validated['config'];
                    $config['verification_file'] = $path;
                    $validated['config'] = $config;
                }

                $integration->update([
                    'name' => $validated['name'],
                    'url' => $validated['url'] ?? null,
                    'type' => $validated['type'],
                    'platform' => $validated['platform'],
                    'config' => $validated['config'] ?? [],
                ]);
                $message = 'Integration updated successfully';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Integration operation failed',
            ], 500);
        }
    }

    public function statusChange(Request $request)
    {
        try {
            $integration = Integration::findOrFail($request->id);
            if ($integration->status === 'active') {
                $integration->update([
                    'status' => 'inactive',
                ]);
            }else{
                $integration->update([
                    'status' => 'active',
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => "Integration status update successfully",
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to change integration status',
            ], 500);
        }
    }

}
