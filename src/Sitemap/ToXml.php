<?php

namespace Laraveltoolkit\Sitemap;

use DOMDocument;
use DOMElement;

interface ToXml
{
    public function toXml(DOMDocument $xml, DOMElement $root): void;
}
