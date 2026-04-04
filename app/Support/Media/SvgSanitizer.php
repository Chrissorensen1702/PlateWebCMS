<?php

namespace App\Support\Media;

use DOMDocument;
use DOMElement;
use DOMNode;
use InvalidArgumentException;

class SvgSanitizer
{
    /**
     * @var array<int, string>
     */
    private array $allowedElements = [
        'svg',
        'g',
        'path',
        'rect',
        'circle',
        'ellipse',
        'line',
        'polyline',
        'polygon',
        'text',
        'tspan',
        'defs',
        'lineargradient',
        'radialgradient',
        'stop',
        'title',
        'desc',
        'clippath',
        'mask',
        'pattern',
        'symbol',
        'use',
    ];

    /**
     * @var array<int, string>
     */
    private array $allowedAttributes = [
        'xmlns',
        'xmlns:xlink',
        'viewbox',
        'width',
        'height',
        'x',
        'y',
        'x1',
        'x2',
        'y1',
        'y2',
        'cx',
        'cy',
        'r',
        'rx',
        'ry',
        'd',
        'points',
        'fill',
        'fill-opacity',
        'fill-rule',
        'stroke',
        'stroke-width',
        'stroke-opacity',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'opacity',
        'transform',
        'id',
        'class',
        'preserveaspectratio',
        'gradientunits',
        'gradienttransform',
        'spreadmethod',
        'offset',
        'stop-color',
        'stop-opacity',
        'clip-path',
        'clip-rule',
        'mask',
        'maskunits',
        'maskcontentunits',
        'patternunits',
        'patterncontentunits',
        'patterntransform',
        'href',
        'xlink:href',
        'font-family',
        'font-size',
        'font-weight',
        'letter-spacing',
        'text-anchor',
        'dominant-baseline',
        'aria-labelledby',
        'role',
    ];

    public function sanitize(string $svg): string
    {
        $svg = trim($svg);

        if ($svg === '') {
            throw new InvalidArgumentException('SVG-filen er tom.');
        }

        if (stripos($svg, '<!DOCTYPE') !== false || stripos($svg, '<!ENTITY') !== false) {
            throw new InvalidArgumentException('SVG-filen indeholder markup der ikke er tilladt.');
        }

        libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        if (! $loaded || ! $document->documentElement || strtolower($document->documentElement->tagName) !== 'svg') {
            throw new InvalidArgumentException('SVG-filen kunne ikke valideres.');
        }

        $this->sanitizeNode($document->documentElement);

        return (string) $document->saveXML($document->documentElement);
    }

    private function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            if (! in_array(strtolower($node->tagName), $this->allowedElements, true)) {
                $node->parentNode?->removeChild($node);

                return;
            }

            $this->sanitizeAttributes($node);
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->sanitizeNode($child);
        }
    }

    private function sanitizeAttributes(DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = trim((string) $attribute->nodeValue);

            if (str_starts_with($name, 'on') || ! in_array($name, $this->allowedAttributes, true)) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if (in_array($name, ['href', 'xlink:href'], true) && ! $this->isSafeReference($value)) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if ($this->containsUnsafeCss($value)) {
                $element->removeAttributeNode($attribute);
            }
        }
    }

    private function isSafeReference(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        return str_starts_with($value, '#');
    }

    private function containsUnsafeCss(string $value): bool
    {
        $normalized = strtolower($value);

        return str_contains($normalized, 'javascript:')
            || str_contains($normalized, 'expression(')
            || str_contains($normalized, 'url(http')
            || str_contains($normalized, 'url(//');
    }
}
