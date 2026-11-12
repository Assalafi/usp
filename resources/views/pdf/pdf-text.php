<?php

/**
 * Normalize text before sending it to the legacy FPDF renderer.
 * FPDF's built-in fonts are not UTF-8 aware, so Unicode Roman numerals
 * otherwise appear as mojibake (for example, "â…").
 */
if (!function_exists('normalizePdfRomanNumerals')) {
    function normalizePdfRomanNumerals($input)
    {
        $romanMap = [
            "\xE2\x85\xA0" => 'I', "\xE2\x85\xA1" => 'II', "\xE2\x85\xA2" => 'III',
            "\xE2\x85\xA3" => 'IV', "\xE2\x85\xA4" => 'V', "\xE2\x85\xA5" => 'VI',
            "\xE2\x85\xA6" => 'VII', "\xE2\x85\xA7" => 'VIII', "\xE2\x85\xA8" => 'IX',
            "\xE2\x85\xA9" => 'X', "\xE2\x85\xB0" => 'i', "\xE2\x85\xB1" => 'ii',
            "\xE2\x85\xB2" => 'iii', "\xE2\x85\xB3" => 'iv', "\xE2\x85\xB4" => 'v',
            "\xE2\x85\xB5" => 'vi', "\xE2\x85\xB6" => 'vii', "\xE2\x85\xB7" => 'viii',
            "\xE2\x85\xB8" => 'ix', "\xE2\x85\xB9" => 'x',
        ];

        return strtr((string) $input, $romanMap);
    }
}

if (!function_exists('pdfTitleCase')) {
    function pdfTitleCase($input)
    {
        $excludeWords = ['or', 'and', 'of', 'the', 'in', 'on', 'at', 'by', 'for', 'to'];
        $words = explode(' ', ucwords(strtolower(normalizePdfRomanNumerals($input))));
        foreach ($words as &$word) {
            if (in_array(strtolower($word), $excludeWords, true)) {
                $word = strtolower($word);
            }
        }

        $formatted = implode(' ', $words);
        $romanWords = [
            'i' => 'I', 'ii' => 'II', 'iii' => 'III', 'iv' => 'IV', 'v' => 'V',
            'vi' => 'VI', 'vii' => 'VII', 'viii' => 'VIII', 'ix' => 'IX', 'x' => 'X',
        ];

        return preg_replace_callback('/\b(i|ii|iii|iv|v|vi|vii|viii|ix|x)\b/i', function ($match) use ($romanWords) {
            return $romanWords[strtolower($match[0])] ?? $match[0];
        }, $formatted);
    }
}

