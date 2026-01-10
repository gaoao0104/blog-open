<?php

declare(strict_types=1);

final class Db
{
    private static ?\PDO $pdo = null;

    public static function pdo(array $config): \PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        $db = $config['db'];
        $dsnParts = [
            'mysql:host=' . $db['host'],
            'dbname=' . $db['name'],
            'charset=' . $db['charset'],
        ];
        if (!empty($db['socket'])) {
            $dsnParts[] = 'unix_socket=' . $db['socket'];
        }
        $dsn = implode(';', $dsnParts);

        self::$pdo = new \PDO($dsn, $db['user'], $db['pass'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }
}
