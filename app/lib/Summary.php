<?php

declare(strict_types=1);

final class Summary
{
    private static ?string $lastError = null;

    public static function generate(array $config, string $markdown, int $length = 50): string
    {
        self::$lastError = null;
        $plain = markdown_plaintext($markdown);
<<<<<<< HEAD
        $fallback = trim(snippet($plain, $length));
=======
        if ($plain === '') {
            $plain = trim($markdown);
        }
        $plain = self::limitText($plain, 3000);
        $fallback = trim(snippet($plain, $length));
        if ($fallback === '') {
            $fallback = trim(snippet(trim($markdown), $length));
        }
>>>>>>> a3d11b8 (sync: update open-source release)

        if (empty($config['gemini_api_key'])) {
            self::$lastError = 'missing_api_key';
            return $fallback;
        }
        if (!function_exists('curl_init')) {
            self::$lastError = 'curl_missing';
            return $fallback;
        }

<<<<<<< HEAD
=======
        $targetLength = max(10, $length);
        $inputLength = self::textLength($plain);
        $minLength = max(20, (int)ceil($targetLength * 0.7));
        if ($inputLength > 0 && $inputLength < $minLength) {
            $minLength = max(10, min($targetLength, $inputLength));
        }
        $attempts = [$plain, self::limitText($plain, 1000)];
        $models = self::resolveModels($config);
        $lastError = null;

        foreach ($attempts as $index => $attemptText) {
            if ($attemptText === '') {
                continue;
            }
            if ($index > 0 && $attemptText === $plain) {
                continue;
            }

            $prompts = self::buildPrompts($attemptText, $targetLength, $minLength);
            foreach ($models as $model) {
                foreach ($prompts as $prompt) {
                    [$content, $error] = self::requestSummary($config, $prompt, $model, $minLength);
                    if ($content !== '') {
                        return $content;
                    }

                    $lastError = $error ?? 'empty_response';
                    if (!in_array($lastError, ['empty_response', 'too_short'], true) && !str_starts_with($lastError, 'blocked:')) {
                        break 2;
                    }
                }
            }
        }

        self::$lastError = $lastError ?? 'empty_response';
        return $fallback;
    }

    public static function lastError(): ?string
    {
        return self::$lastError;
    }

    private static function limitText(string $text, int $maxChars): string
    {
        if ($maxChars <= 0) {
            return '';
        }
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxChars);
        }
        return substr($text, 0, $maxChars);
    }

    private static function requestSummary(array $config, string $prompt, string $model, int $minLength): array
    {
>>>>>>> a3d11b8 (sync: update open-source release)
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
<<<<<<< HEAD
                            'text' => "请用约200字中文生成文章摘要：\n" . $plain,
=======
                            'text' => $prompt,
>>>>>>> a3d11b8 (sync: update open-source release)
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
<<<<<<< HEAD
            ],
        ];

        $model = $config['gemini_model'] ?? 'gemini-1.5-flash';
=======
                'maxOutputTokens' => 256,
                'responseMimeType' => 'text/plain',
            ],
        ];

>>>>>>> a3d11b8 (sync: update open-source release)
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $config['gemini_api_key'];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
<<<<<<< HEAD
            CURLOPT_TIMEOUT => 20,
=======
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
>>>>>>> a3d11b8 (sync: update open-source release)
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
<<<<<<< HEAD
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
=======
            return ['', $error . ' (http ' . (string)$httpCode . ')'];
        }

        $data = json_decode($response, true);
        $content = self::extractContent($data);
        if ($content !== '') {
            if ($minLength > 0 && self::textLength($content) < $minLength) {
                return ['', 'too_short'];
            }
            return [$content, null];
        }

        $blockReason = $data['promptFeedback']['blockReason'] ?? '';
        if ($blockReason !== '') {
            return ['', 'blocked:' . $blockReason];
        }

        return ['', 'empty_response'];
    }

    private static function extractContent(?array $data): string
    {
        if (!is_array($data)) {
            return '';
        }

        $candidate = $data['candidates'][0] ?? null;
        if (is_array($candidate)) {
            $parts = $candidate['content']['parts'] ?? null;
            if (is_array($parts)) {
                $texts = [];
                foreach ($parts as $part) {
                    if (isset($part['text'])) {
                        $texts[] = $part['text'];
                    }
                }
                $joined = trim(implode('', $texts));
                if ($joined !== '') {
                    return $joined;
                }
            }

            if (!empty($candidate['content']['text'])) {
                return trim((string)$candidate['content']['text']);
            }
            if (!empty($candidate['text'])) {
                return trim((string)$candidate['text']);
            }
        }

        return '';
    }

    private static function buildPrompts(string $plain, int $targetLength, int $minLength): array
    {
        $base = "请用约{$targetLength}字中文总结文章，字数控制在{$targetLength}字左右：\n" . $plain;
        $strict = "请用约{$targetLength}字中文总结文章，字数控制在{$targetLength}字左右，内容不少于{$minLength}字，不要只输出标题或一句话：\n" . $plain;
        return [$base, $strict];
    }

    private static function resolveModels(array $config): array
    {
        $models = [];
        $primary = trim((string)($config['gemini_model'] ?? ''));
        if ($primary !== '') {
            $models[] = $primary;
        }
        $models[] = 'gemini-2.5-flash';
        $models[] = 'gemini-2.0-flash';
        $models[] = 'gemini-flash-latest';
        $models = array_values(array_unique($models));
        return $models;
    }

    private static function textLength(string $text): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($text);
        }
        return strlen($text);
>>>>>>> a3d11b8 (sync: update open-source release)
    }
}
