<?php

namespace Laraveltoolkit\Sitemap;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Carbon;

readonly class Url implements ToXml
{
    public function __construct(
        public string $loc,
        public ?Carbon $lastModified = null,
        public ?ChangeFrequency $changeFrequency = null,
        public ?float $priority = null,
    ) {}

    public function toXml(DOMDocument $xml, DOMElement $root): void
    {
        $currentTrack = $xml->createElement('url');
        $currentTrack = $root->appendChild($currentTrack);
        collect([
            'loc' => $this->loc,
            'lastmod' => $this->lastModified?->format('Y-m-d'),
            'changefreq' => $this->changeFrequency?->value,
            'priority' => $this->priority,
        ])
            ->filter(fn ($item) => $item !== null)
            ->each(fn ($value, $key) => $currentTrack->appendChild($xml->createElement($key, $value)));

    }
}
