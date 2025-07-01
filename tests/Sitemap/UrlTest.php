<?php

use Laraveltoolkit\Sitemap\ChangeFrequency;
use Laraveltoolkit\Sitemap\Url;

it('can instantiate', function () {
    $xml = new DOMDocument('1.0', 'utf-8');
    $xmlns = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    $xmlRoot = $xml->appendChild($xml->createElementNS($xmlns, 'urlset'));
    $url = new Url(
        'http://example.com/',
        now()->subWeek(),
        ChangeFrequency::ALWAYS,
        0.5
    );
    $url2 = new Url(
        'http://example.com/',
    );
    expect($url)
        ->toBeInstanceOf(Url::class)
        ->and($xmlRoot->childElementCount)
        ->toBe(0)
        ->and($url->toXml($xml, $xmlRoot))
        ->toBeNull()
        ->and($xmlRoot->childElementCount)
        ->toBe(1)
        ->and($url2)
        ->toBeInstanceOf(Url::class)
        ->and($url2->toXml($xml, $xmlRoot))
        ->toBeNull()
        ->and($xmlRoot->childElementCount)
        ->toBe(2);
});
