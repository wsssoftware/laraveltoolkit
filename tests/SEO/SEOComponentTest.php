<?php

use Laraveltoolkit\Facades\SEO;
use Laraveltoolkit\SEO\SEOComponent;

it('', function () {
    $result = SEOComponent::resolve(['payload' => SEO::payload()]);
    expect($result->render()->render())
        ->toBeString()
        ->toStartWith('<title');
});
