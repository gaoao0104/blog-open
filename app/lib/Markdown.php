<?php

declare(strict_types=1);

final class Markdown
{
    public static function render(string $markdown): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown);
        $html = '';
        $inList = false;
        $listType = '';
        $inCode = false;
        $inBlockquote = false;
        $blockquoteBuffer = [];

        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trim = trim($line);

            if (str_starts_with($trim, '```')) {
                if ($inList) {
                    $html .= $listType === 'ol' ? '</ol>' : '</ul>';
                    $inList = false;
                }
                if ($inBlockquote) {
                    $html .= '<blockquote>' . self::render(implode("\n", $blockquoteBuffer)) . '</blockquote>';
                    $inBlockquote = false;
                    $blockquoteBuffer = [];
                }
                if ($inCode) {
                    $html .= "</code></pre></div>";
                    $inCode = false;
                } else {
                    $html .= "<div class=\"code-wrapper\"><pre><code>";
                    $inCode = true;
                }
                continue;
            }

            if ($inCode) {
                $html .= htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n";
                continue;
            }

            if ($trim === '') {
                if ($inList) {
                    $html .= $listType === 'ol' ? '</ol>' : '</ul>';
                    $inList = false;
                }
                if ($inBlockquote) {
                    $html .= '<blockquote>' . self::render(implode("\n", $blockquoteBuffer)) . '</blockquote>';
                    $inBlockquote = false;
                    $blockquoteBuffer = [];
                }
                continue;
            }

            if (preg_match('/^\s*>/', $line)) {
                $inBlockquote = true;
                if ($inList) {
                     $html .= $listType === 'ol' ? '</ol>' : '</ul>';
                     $inList = false;
                }
                $blockquoteBuffer[] = ltrim(preg_replace('/^\s*> ?/', '', $line));
                continue;
            }

            if ($inBlockquote) {
                $html .= '<blockquote>' . self::render(implode("\n", $blockquoteBuffer)) . '</blockquote>';
                $inBlockquote = false;
                $blockquoteBuffer = [];
                // Fallthrough to process this line as normal text
            }

            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trim)) {
                if ($inList) {
                    $html .= $listType === 'ol' ? '</ol>' : '</ul>';
                    $inList = false;
                }
                $html .= '<hr>';
                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.*)$/', $trim, $matches)) {
                if ($inList) {
                    $html .= $listType === 'ol' ? '</ol>' : '</ul>';
                    $inList = false;
                }
                $level = strlen($matches[1]);
                $content = self::inline($matches[2]);
                $html .= sprintf('<h%d>%s</h%d>', $level, $content, $level);
                continue;
            }

            if (self::isTableHeader($trim, $lines[$i + 1] ?? null)) {
                $html .= self::renderTable($trim, $lines, $i);
                continue;
            }

            if (preg_match('/^\d+\.\s+(.*)$/', $trim, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) {
                        $html .= '</ul>';
                    }
                    $html .= '<ol>';
                    $inList = true;
                    $listType = 'ol';
                }
                $html .= '<li>' . self::inline($matches[1]) . '</li>';
                continue;
            }

            if (preg_match('/^[-*+]\s+(.*)$/', $trim, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) {
                        $html .= '</ol>';
                    }
                    $html .= '<ul>';
                    $inList = true;
                    $listType = 'ul';
                }
                $html .= '<li>' . self::inline($matches[1]) . '</li>';
                continue;
            }

            if ($inList) {
                $html .= $listType === 'ol' ? '</ol>' : '</ul>';
                $inList = false;
            }

            $html .= '<p>' . self::inline($trim) . '</p>';
        }

        if ($inList) {
            $html .= $listType === 'ol' ? '</ol>' : '</ul>';
        }

        if ($inBlockquote) {
            $html .= '<blockquote>' . self::render(implode("\n", $blockquoteBuffer)) . '</blockquote>';
        }

        if ($inCode) {
            $html .= '</code></pre></div>';
        }

        return $html;
    }

    private static function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = preg_replace('/!\[([^\]]*)\]\(([^)\s]+)\)/', '<img src="$2" alt="$1">', $text);
        $text = preg_replace('/\[(.+?)\]\((https?:\/\/[^\s]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text);
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/~~(.+?)~~/', '<del>$1</del>', $text);
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        return $text;
    }

    private static function isTableHeader(string $line, ?string $next): bool
    {
        if ($next === null) {
            return false;
        }
        if (substr_count($line, '|') < 1) {
            return false;
        }
        $trimNext = trim($next);
        return (bool)preg_match('/^\|?\s*:?-{3,}:?\s*(\|\s*:?-{3,}:?\s*)+\|?$/', $trimNext);
    }

    private static function renderTable(string $headerLine, array $lines, int &$index): string
    {
        $headerCells = self::splitTableLine($headerLine);
        $index += 1;
        $bodyLines = [];
        $count = count($lines);
        for ($i = $index + 1; $i < $count; $i++) {
            $line = $lines[$i];
            if (trim($line) === '' || substr_count($line, '|') < 1) {
                $index = $i - 1;
                break;
            }
            $bodyLines[] = $line;
            $index = $i;
        }

        $html = '<table><thead><tr>';
        foreach ($headerCells as $cell) {
            $html .= '<th>' . self::inline($cell) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($bodyLines as $line) {
            $cells = self::splitTableLine($line);
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= '<td>' . self::inline($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    private static function splitTableLine(string $line): array
    {
        $line = trim($line);
        $line = trim($line, '|');
        $cells = array_map('trim', explode('|', $line));
        return $cells;
    }
}
