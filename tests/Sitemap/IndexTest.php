<?php

use Laraveltoolkit\Sitemap\Index;

it('can instantiate', function () {
    $index = new Index('users');
    $xml = new DOMDocument('1.0', 'utf-8');
    $xmlns = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    $xmlRoot = $xml->appendChild($xml->createElementNS($xmlns, 'sitemapindex'));
    expect($index)
        ->toBeInstanceOf(Index::class)
        ->and($xmlRoot->childElementCount)
        ->toBe(0)
        ->and($index->toXml($xml, $xmlRoot))
        ->toBeNull()
        ->and($xmlRoot->childElementCount)
        ->toBe(1);

});
