<?php

declare(strict_types=1);

final class View
{
    public static function render(string $template, array $data = []): void
    {
        if (!isset($data['settings']) && isset($GLOBALS['settings'])) {
            $data['settings'] = $GLOBALS['settings'];
        }
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/' . $template . '.php';
    }
}
