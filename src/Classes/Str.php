<?php

namespace Amicus\FilamentEmployeeManagement\Classes;

use App\Models\Employee;
use DOMDocument;
use DOMXPath;

class Str extends \Illuminate\Support\Str
{
    public static function extractMentionIds(string $htmlBody): array
    {
        $mentionIds = [];
        if (preg_match_all('/<span[^>]*data-type="mention"[^>]*data-id="(\d+)"[^>]*>/', $htmlBody, $matches)) {
            $mentionIds = array_map('intval', $matches[1]);
        }

        return array_unique($mentionIds);
    }

    public static function parseHtmlMentions(string $htmlBody): string
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);

        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$htmlBody, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);
        $spans = $xpath->query('//span[@data-id and contains(@class, "mention")]');
        foreach ($spans as $span) {
            $employee = Employee::find($span->getAttribute('data-id'));
            $span->textContent = $employee ? '@'.$employee->full_name : '@NetEko Zaposlenik';
        }

        // Vrati modificirani HTML
        return $dom->saveHTML();
    }
}
