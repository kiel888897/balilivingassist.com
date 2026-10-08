<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a',
        'blockquote',
        'br',
        'code',
        'del',
        'div',
        'em',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'i',
        'li',
        'ol',
        'p',
        'pre',
        'strong',
        'u',
        'ul',
    ];

    private const DROP_CONTENT_TAGS = [
        'audio',
        'button',
        'embed',
        'form',
        'iframe',
        'input',
        'math',
        'object',
        'script',
        'style',
        'svg',
        'textarea',
        'video',
    ];

    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="bla-rich-text-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        if (!$loaded) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $root = (new DOMXPath($document))->query('//*[@id="bla-rich-text-root"]')->item(0);

        if (!$root) {
            return '';
        }

        self::sanitizeChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_CONTENT_TAGS, true)) {
                $parent->removeChild($child);
                continue;
            }

            self::sanitizeChildren($child);

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                if ($tag === 'a' && strtolower($attribute->name) === 'href' && self::isSafeLink($attribute->value)) {
                    continue;
                }

                $child->removeAttribute($attribute->name);
            }
        }
    }

    private static function isSafeLink(string $url): bool
    {
        $url = trim($url);

        if (preg_match('/^(https?:|mailto:)/i', $url)) {
            return true;
        }

        return str_starts_with($url, '/') && !str_starts_with($url, '//')
            || str_starts_with($url, '#');
    }
}
