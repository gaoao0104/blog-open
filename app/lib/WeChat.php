<?php

declare(strict_types=1);

final class WeChat
{
    public static function signature(array $config, string $url): ?array
    {
        $appId = trim((string)($config['wechat_app_id'] ?? ''));
        $secret = trim((string)($config['wechat_app_secret'] ?? ''));

        if ($appId === '' || $secret === '') {
            return null;
        }

        $ticket = self::getJsApiTicket($config, $appId, $secret);
        if ($ticket === null) {
            return null;
        }

        $nonceStr = bin2hex(random_bytes(8));
        $timestamp = time();
        $base = "jsapi_ticket={$ticket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($base);

        return [
            'appId' => $appId,
            'timestamp' => $timestamp,
            'nonceStr' => $nonceStr,
            'signature' => $signature,
        ];
    }

    private static function getJsApiTicket(array $config, string $appId, string $secret): ?string
    {
        $cachePath = self::cachePath($config, 'wechat_ticket_cache', 'wechat_jsapi_ticket.json');
        $cached = self::readCache($cachePath);
        if ($cached !== null) {
            return $cached['value'] ?? null;
        }

        $token = self::getAccessToken($config, $appId, $secret);
        if ($token === null) {
            return null;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . rawurlencode($token) . '&type=jsapi';
        $response = self::requestJson($url);
        if (!$response || (int)($response['errcode'] ?? 0) !== 0 || empty($response['ticket'])) {
            return null;
        }

        $ticket = (string)$response['ticket'];
        $expiresIn = (int)($response['expires_in'] ?? 7200);
        self::writeCache($cachePath, $ticket, $expiresIn);

        return $ticket;
    }

    private static function getAccessToken(array $config, string $appId, string $secret): ?string
    {
        $cachePath = self::cachePath($config, 'wechat_token_cache', 'wechat_access_token.json');
        $cached = self::readCache($cachePath);
        if ($cached !== null) {
            return $cached['value'] ?? null;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . rawurlencode($appId) . '&secret=' . rawurlencode($secret);
        $response = self::requestJson($url);
        if (!$response || empty($response['access_token'])) {
            return null;
        }

        $token = (string)$response['access_token'];
        $expiresIn = (int)($response['expires_in'] ?? 7200);
        self::writeCache($cachePath, $token, $expiresIn);

        return $token;
    }

    private static function requestJson(string $url): ?array
    {
        $body = null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $body = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 8,
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
        }

        if ($body === false || $body === null) {
            return null;
        }

        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    private static function cachePath(array $config, string $key, string $fallbackName): string
    {
        $path = trim((string)($config[$key] ?? ''));
        if ($path !== '') {
            return $path;
        }

        $dir = rtrim(sys_get_temp_dir(), '/');
        return $dir . '/' . $fallbackName;
    }

    private static function readCache(string $path): ?array
    {
        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        $expiresAt = (int)($data['expires_at'] ?? 0);
        if ($expiresAt <= time()) {
            return null;
        }

        return $data;
    }

    private static function writeCache(string $path, string $value, int $expiresIn): void
    {
        $expiresAt = time() + max(0, $expiresIn - 200);
        $payload = json_encode([
            'value' => $value,
            'expires_at' => $expiresAt,
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return;
        }

        @file_put_contents($path, $payload, LOCK_EX);
    }
}
