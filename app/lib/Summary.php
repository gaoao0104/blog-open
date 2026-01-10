<?php

declare(strict_types=1);

final class Summary
{
    private static ?string $lastError = null;

    public static function generate(array $config, string $markdown, int $length = 50): string
    {
        self::$lastError = null;
        $plain = markdown_plaintext($markdown);
        $fallback = trim(snippet($plain, $length));

        if (empty($config['gemini_api_key'])) {
            self::$lastError = 'missing_api_key';
            return $fallback;
        }
        if (!function_exists('curl_init')) {
            self::$lastError = 'curl_missing';
            return $fallback;
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => "请用约200字中文生成文章摘要：\n" . $plain,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
            ],
        ];

        $model = $config['gemini_model'] ?? 'gemini-1.5-flash';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $config['gemini_api_key'];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            $error = '';
            if ($curlError !== '') {
                $error = 'curl_error: ' . $curlError;
            }
            if (is_string($response)) {
                $decoded = json_decode($response, true);
                $error = $decoded['error']['message'] ?? $error;
            }
            if ($error === '') {
                $error = 'api_error';
            }
            self::$lastError = $error . ' (http ' . (string)$httpCode . ')';
            return $fallback;
        }

        $data = json_decode($response, true);
        $content = trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '');

        if ($content === '') {
            self::$lastError = 'empty_response';
            return $fallback;
        }

        return trim(snippet($content, $length));
    }

    public static function lastError(): ?string
    {
        return self::$lastError;
    }
}
