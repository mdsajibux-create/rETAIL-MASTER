<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OpenAiController
{

    public function generateContent(Request $request){

        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if OpenAI is enabled
        $enable = com_option_get('com_openai_enable_disable');
        if (empty($enable) || is_null($enable)) {
            return response()->json([
                'success' => false,
                'message' => 'AI content generation is currently disabled'
            ], 503);
        }

        try {
            $generatedContent = $this->callOpenAI($request->prompt);
            return response()->json([
                'success' => true,
                'generated_content' => $generatedContent
            ]);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            // Customize common OpenAI error messages
            if (str_contains($message, 'billing')) {
                $message = 'AI content generation failed: Your OpenAI quota has been exceeded. Please check your plan or billing.';
            } elseif (str_contains($message, 'invalid_api_key')) {
                $message = 'AI content generation failed: OpenAI API key is invalid.';
            } elseif (str_contains($message, 'The project')) {
                $message = 'AI content generation failed: OpenAI project not found. Please check your API settings.';
            } else {
                $message = 'AI content generation failed. Please try again later.';
            }
            return response()->json([
                'success' => false,
                'message' => $message
            ], 500);
        }

    }

    private function callOpenAI($prompt)
    {
        $apiKey = com_option_get('com_openai_api_key');
        $model = com_option_get('com_openai_model') ?? 'gpt-4o-mini';
        $timeout = (int) (com_option_get('com_openai_timeout') ?? 30);

        $response = Http::timeout($timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->getMaxTokens(500),
                'temperature' => 0.7
            ]);

        $data = $response->json();

        // Check if OpenAI returned an error
        if (isset($data['error'])) {
            throw new \Exception('OpenAI Error: ' . $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid OpenAI API response format');
        }

        return trim($data['choices'][0]['message']['content']);
    }

    private function getPrompts()
    {
        return [
            'description' => 'You are a professional product copywriter.',
        ];
    }

    private function getMaxTokens(int $min = 500): int
    {
        return 500;
    }


}
