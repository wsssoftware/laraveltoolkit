<?php

namespace Laraveltoolkit\Sitemap;

use DOMDocument;
use DOMElement;

readonly class Index implements ToXml
{
    public function __construct(public string $group)
    {
        //
    }

    public function toXml(DOMDocument $xml, DOMElement $root): void
    {
        $currentTrack = $xml->createElement('sitemap');
        $currentTrack = $root->appendChild($currentTrack);
        $currentTrack->appendChild($xml->createElement(
            'loc',
            route('lt.sitemap_group', ['index' => $this->group]),
        ));
    }
}
