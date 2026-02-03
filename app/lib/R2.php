<?php

declare(strict_types=1);

class R2Client
{
    private string $accessKey;
    private string $secretKey;
    private string $endpoint;
    private string $bucket;
    private string $region;
    private string $publicBaseUrl;
    private string $prefix;
    private string $lastError = '';

    public function __construct(array $config)
    {
        $this->accessKey = trim((string)($config['access_key'] ?? ''));
        $this->secretKey = trim((string)($config['secret_key'] ?? ''));
        $this->endpoint = trim((string)($config['endpoint'] ?? ''));
        $this->bucket = trim((string)($config['bucket'] ?? ''));
        $this->region = trim((string)($config['region'] ?? 'auto'));
        $this->publicBaseUrl = trim((string)($config['public_base_url'] ?? ''));
        $this->prefix = trim((string)($config['prefix'] ?? ''));
        $this->prefix = trim($this->prefix, '/');
    }

    public function isConfigured(): bool
    {
        return $this->accessKey !== ''
            && $this->secretKey !== ''
            && $this->endpoint !== ''
            && $this->bucket !== ''
            && $this->publicBaseUrl !== '';
    }

    public function lastError(): string
    {
        return $this->lastError;
    }

    public function buildObjectKey(string $name): string
    {
        $name = ltrim($name, '/');
        if ($this->prefix === '') {
            return $name;
        }
        return $this->prefix . '/' . $name;
    }

    public function publicUrl(string $key): string
    {
        return rtrim($this->publicBaseUrl, '/') . '/' . ltrim($key, '/');
    }

    public function keyFromUrl(string $url): ?string
    {
        $base = rtrim($this->publicBaseUrl, '/');
        if ($base !== '' && str_starts_with($url, $base . '/')) {
            return ltrim(substr($url, strlen($base)), '/');
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) {
            return null;
        }
        $path = ltrim($path, '/');
        if ($this->bucket !== '' && str_starts_with($path, $this->bucket . '/')) {
            return substr($path, strlen($this->bucket) + 1);
        }
        return $path === '' ? null : $path;
    }

    public function putObject(string $key, string $body, string $contentType): bool
    {
        if (!$this->isConfigured()) {
            $this->lastError = 'missing_config';
            return false;
        }

        $key = ltrim($key, '/');
        $payloadHash = hash('sha256', $body);
        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $host = $this->endpointHost();
        $canonicalUri = '/' . rawurlencode($this->bucket) . '/' . $this->encodeKey($key);

        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = "PUT\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$dateStamp}/{$this->region}/s3/aws4_request\n" . hash('sha256', $canonicalRequest);
        $signature = hash_hmac('sha256', $stringToSign, $this->getSigningKey($dateStamp, $this->region, 's3'));

        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$dateStamp}/{$this->region}/s3/aws4_request, SignedHeaders={$signedHeaders}, Signature={$signature}";
        $url = rtrim($this->endpoint, '/') . '/' . rawurlencode($this->bucket) . '/' . $this->encodeKey($key);

        if (!extension_loaded('curl')) {
            $this->lastError = 'curl_missing';
            return false;
        }

        $headers = [
            'Authorization: ' . $authorization,
            'x-amz-date: ' . $amzDate,
            'x-amz-content-sha256: ' . $payloadHash,
            'Content-Type: ' . $contentType,
            'Content-Length: ' . strlen($body),
            'Expect:',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            $this->lastError = curl_error($ch);
            curl_close($ch);
            return false;
        }
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 200 && $status < 300) {
            return true;
        }

        $this->lastError = 'http_' . $status;
        return false;
    }

    public function deleteObject(string $key): bool
    {
        if (!$this->isConfigured()) {
            $this->lastError = 'missing_config';
            return false;
        }

        $key = ltrim($key, '/');
        $payloadHash = hash('sha256', '');
        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $host = $this->endpointHost();
        $canonicalUri = '/' . rawurlencode($this->bucket) . '/' . $this->encodeKey($key);

        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = "DELETE\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$dateStamp}/{$this->region}/s3/aws4_request\n" . hash('sha256', $canonicalRequest);
        $signature = hash_hmac('sha256', $stringToSign, $this->getSigningKey($dateStamp, $this->region, 's3'));

        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$dateStamp}/{$this->region}/s3/aws4_request, SignedHeaders={$signedHeaders}, Signature={$signature}";
        $url = rtrim($this->endpoint, '/') . '/' . rawurlencode($this->bucket) . '/' . $this->encodeKey($key);

        if (!extension_loaded('curl')) {
            $this->lastError = 'curl_missing';
            return false;
        }

        $headers = [
            'Authorization: ' . $authorization,
            'x-amz-date: ' . $amzDate,
            'x-amz-content-sha256: ' . $payloadHash,
            'Content-Length: 0',
            'Expect:',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            $this->lastError = curl_error($ch);
            curl_close($ch);
            return false;
        }
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 200 && $status < 300) {
            return true;
        }

        $this->lastError = 'http_' . $status;
        return false;
    }

    public function listObjects(string $prefix, ?string $continuationToken = null, int $maxKeys = 1000): array
    {
        if (!$this->isConfigured()) {
            $this->lastError = 'missing_config';
            return ['keys' => [], 'next_token' => null];
        }

        $payloadHash = hash('sha256', '');
        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $host = $this->endpointHost();
        $canonicalUri = '/' . rawurlencode($this->bucket);

        $params = [
            'list-type' => '2',
            'prefix' => ltrim($prefix, '/'),
            'max-keys' => (string)$maxKeys,
        ];
        if ($continuationToken) {
            $params['continuation-token'] = $continuationToken;
        }
        ksort($params);
        $canonicalQuery = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = "GET\n{$canonicalUri}\n{$canonicalQuery}\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$dateStamp}/{$this->region}/s3/aws4_request\n" . hash('sha256', $canonicalRequest);
        $signature = hash_hmac('sha256', $stringToSign, $this->getSigningKey($dateStamp, $this->region, 's3'));

        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$dateStamp}/{$this->region}/s3/aws4_request, SignedHeaders={$signedHeaders}, Signature={$signature}";
        $url = rtrim($this->endpoint, '/') . '/' . rawurlencode($this->bucket) . '?' . $canonicalQuery;

        if (!extension_loaded('curl')) {
            $this->lastError = 'curl_missing';
            return ['keys' => [], 'next_token' => null];
        }

        $headers = [
            'Authorization: ' . $authorization,
            'x-amz-date: ' . $amzDate,
            'x-amz-content-sha256: ' . $payloadHash,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            $this->lastError = curl_error($ch);
            curl_close($ch);
            return ['keys' => [], 'next_token' => null];
        }
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            $this->lastError = 'http_' . $status;
            return ['keys' => [], 'next_token' => null];
        }

        $xml = simplexml_load_string($response);
        if ($xml === false) {
            $this->lastError = 'invalid_xml';
            return ['keys' => [], 'next_token' => null];
        }

        $keys = [];
        foreach ($xml->Contents ?? [] as $content) {
            $key = (string)$content->Key;
            if ($key !== '') {
                $keys[] = $key;
            }
        }

        $nextToken = null;
        $isTruncated = ((string)($xml->IsTruncated ?? 'false')) === 'true';
        if ($isTruncated) {
            $token = (string)($xml->NextContinuationToken ?? '');
            $nextToken = $token !== '' ? $token : null;
        }

        return ['keys' => $keys, 'next_token' => $nextToken];
    }

    private function endpointHost(): string
    {
        $host = parse_url($this->endpoint, PHP_URL_HOST);
        return is_string($host) ? $host : '';
    }

    private function encodeKey(string $key): string
    {
        return str_replace('%2F', '/', rawurlencode($key));
    }

    private function getSigningKey(string $dateStamp, string $region, string $service): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
