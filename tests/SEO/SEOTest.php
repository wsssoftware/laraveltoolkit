<?php

use Laraveltoolkit\Facades\SEO;
use Laraveltoolkit\SEO\Image;
use Laraveltoolkit\SEO\RobotRule;

it('test friendly url string', function () {
    expect(SEO::friendlyUrlString('Testing a string with some diferente 3,000 words'))
        ->toEqual('testing-a-string-with-some-diferente-3000-words')
        ->and(SEO::friendlyUrlString('Now WiTh sOmE cRazY example@google!'))
        ->toEqual('now-with-some-crazy-example-em-google');
});

it('test crawler', function () {
    expect(SEO::isCrawler('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36'))
        ->toBeFalse()
        ->and(SEO::isCrawler('googlebot'))
        ->toBeTrue();
});

it('test propagation on', function () {
    SEO::withPropagation();
    SEO::withTitle('title test 1')
        ->withDescription('description test 1')
        ->withCanonical('https://google.com');

    expect(SEO::payload()['title'])
        ->toEqual('title test 1')
        ->and(SEO::payload()['description'])
        ->toEqual('description test 1')
        ->and(SEO::payload()['canonical'])
        ->toEqual('https://google.com')
        ->and(SEO::payload()['twitter_card']['title'])
        ->toEqual('title test 1')
        ->and(SEO::payload()['twitter_card']['description'])
        ->toEqual('description test 1')
        ->and(SEO::payload()['open_graph']['title'])
        ->toEqual('title test 1')
        ->and(SEO::payload()['open_graph']['description'])
        ->toEqual('description test 1')
        ->and(SEO::payload()['open_graph']['url'])
        ->toEqual('https://google.com');
});

it('test propagation off', function () {
    SEO::withoutPropagation();
    SEO::withTitle('title test 1')
        ->withDescription('description test 1')
        ->withCanonical('https://google.com');

    expect(SEO::payload()['title'])
        ->toEqual('title test 1')
        ->and(SEO::payload()['description'])
        ->toEqual('description test 1')
        ->and(SEO::payload()['canonical'])
        ->toEqual('https://google.com')
        ->and(SEO::payload()['twitter_card']['title'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['twitter_card']['description'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['title'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['description'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['url'] ?? null)
        ->toBeNull();
});

it('test valid robots', function () {
    config()->set('laraveltoolkit.seo.defaults.robots', ['noindex', 'nofollow', 'max-snippet:standard']);

    expect(SEO::payload()['robots'])
        ->toEqual('noindex,nofollow,max-snippet:standard');
});

it('test invalid robots', function () {
    expect(fn () => SEO::withRobots('foo_bar'))
        ->toThrow(ValueError::class);
});

it('test default open graph', function () {
    config()->set('laraveltoolkit.seo.defaults.open_graph.type', 'foo_type');
    config()->set('laraveltoolkit.seo.defaults.open_graph.title', 'foo');
    config()->set('laraveltoolkit.seo.defaults.open_graph.description', 'bar');
    config()->set('laraveltoolkit.seo.defaults.open_graph.url', 'https://google.com');
    config()->set('laraveltoolkit.seo.defaults.open_graph.image',
        ['disk' => 'public', 'path' => 'bar', 'alt' => 'all bar']);
    expect(SEO::payload()['open_graph']['type'])
        ->toEqual('foo_type')
        ->and(SEO::payload()['open_graph']['title'])
        ->toEqual('foo')
        ->and(SEO::payload()['open_graph']['description'])
        ->toEqual('bar')
        ->and(SEO::payload()['open_graph']['url'])
        ->toEqual('https://google.com')
        ->and(SEO::payload()['open_graph']['image']['url'])
        ->toEqual('/storage/bar')
        ->and(SEO::payload()['open_graph']['image']['alt'])
        ->toEqual('all bar');
});

it('test default twitter card', function () {
    config()->set('laraveltoolkit.seo.defaults.twitter_card.site', '@foo');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.creator', '@bar');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.title', 'foo');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.description', 'bar');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.image',
        ['disk' => 'public', 'path' => 'bar', 'alt' => 'all bar']);
    expect(SEO::payload()['twitter_card']['site'])
        ->toEqual('@foo')
        ->and(SEO::payload()['twitter_card']['creator'])
        ->toEqual('@bar')
        ->and(SEO::payload()['twitter_card']['title'])
        ->toEqual('foo')
        ->and(SEO::payload()['twitter_card']['description'])
        ->toEqual('bar')
        ->and(SEO::payload()['twitter_card']['image']['url'])
        ->toEqual('/storage/bar')
        ->and(SEO::payload()['twitter_card']['image']['alt'])
        ->toEqual('all bar');
});

it('test with methods', function () {
    SEO::withTitle('foo title')
        ->withDescription('foo description')
        ->withCanonical('https://google.com')
        ->withRobots(RobotRule::ALL, RobotRule::NOARCHIVE)
        ->withOpenGraphTitle('foo og title')
        ->withOpenGraphDescription('foo og description')
        ->withOpenGraphUrl('https://google.com/og')
        ->withOpenGraphType('music.song')
        ->withOpenGraphImage(new Image('public', 'og.png', null))
        ->withTwitterCardSite('@foo')
        ->withTwitterCardCreator('@bar')
        ->withTwitterCardTitle('foo tc title')
        ->withTwitterCardDescription('foo tc description')
        ->withTwitterCardImage(new Image('public', 'tc.png', null));
    expect(SEO::payload()['title'])
        ->toEqual('foo title')
        ->and(SEO::payload()['description'])
        ->toEqual('foo description')
        ->and(SEO::payload()['canonical'])
        ->toEqual('https://google.com')
        ->and(SEO::payload()['robots'])
        ->toEqual('all,noarchive')
        ->and(SEO::payload()['open_graph']['title'])
        ->toEqual('foo og title')
        ->and(SEO::payload()['open_graph']['description'])
        ->toEqual('foo og description')
        ->and(SEO::payload()['open_graph']['url'])
        ->toEqual('https://google.com/og')
        ->and(SEO::payload()['open_graph']['type'])
        ->toEqual('music.song')
        ->and(SEO::payload()['open_graph']['image']['url'])
        ->toEqual('/storage/og.png')
        ->and(SEO::payload()['open_graph']['image']['alt'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['twitter_card']['site'])
        ->toEqual('@foo')
        ->and(SEO::payload()['twitter_card']['creator'])
        ->toEqual('@bar')
        ->and(SEO::payload()['twitter_card']['title'])
        ->toEqual('foo tc title')
        ->and(SEO::payload()['twitter_card']['description'])
        ->toEqual('foo tc description')
        ->and(SEO::payload()['twitter_card']['image']['url'])
        ->toEqual('/storage/tc.png')
        ->and(SEO::payload()['twitter_card']['image']['alt'] ?? null)
        ->toBeNull();
});

it('test without methods', function () {
    config()->set('laraveltoolkit.seo.defaults.title', 'title');
    config()->set('laraveltoolkit.seo.defaults.description', 'description');
    config()->set('laraveltoolkit.seo.defaults.canonical', 'https://google.com');
    config()->set('laraveltoolkit.seo.defaults.robots', ['nofollow']);
    config()->set('laraveltoolkit.seo.defaults.open_graph.type', 'foo_type');
    config()->set('laraveltoolkit.seo.defaults.open_graph.title', 'foo');
    config()->set('laraveltoolkit.seo.defaults.open_graph.description', 'bar');
    config()->set('laraveltoolkit.seo.defaults.open_graph.url', 'https://google.com');
    config()->set('laraveltoolkit.seo.defaults.open_graph.image',
        ['disk' => 'public', 'path' => 'bar', 'alt' => 'all bar']);
    config()->set('laraveltoolkit.seo.defaults.twitter_card.site', '@foo');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.creator', '@bar');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.title', 'foo');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.description', 'bar');
    config()->set('laraveltoolkit.seo.defaults.twitter_card.image',
        ['disk' => 'public', 'path' => 'bar', 'alt' => 'all bar']);
    SEO::withPropagation()
        ->withoutTitle()
        ->withoutDescription()
        ->withoutCanonical()
        ->withoutRobots()
        ->withoutOpenGraphType()
        ->withoutOpenGraphTitle()
        ->withoutOpenGraphDescription()
        ->withoutOpenGraphUrl()
        ->withoutOpenGraphImage()
        ->withoutTwitterCardSite()
        ->withoutTwitterCardCreator()
        ->withoutTwitterCardTitle()
        ->withoutTwitterCardDescription()
        ->withoutTwitterCardImage();

    expect(SEO::payload()['title'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['description'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['canonical'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['robots'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['type'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['title'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['description'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['url'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['open_graph']['image'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['twitter_card']['site'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['twitter_card']['creator'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['twitter_card']['title'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['twitter_card']['description'] ?? null)
        ->toBeNull()
        ->and(SEO::payload()['twitter_card']['image'] ?? null)
        ->toBeNull();
});

it('test robots txt', function () {
    expect(SEO::robotsTxt())
        ->toContain(route('lt.sitemap'));

    SEO::withRobotsTxtSitemap('https://foobar.com/sitemap.txt');
    expect(SEO::robotsTxt())
        ->toContain('https://foobar.com/sitemap.txt')
        ->not
        ->toContain(route('lt.sitemap'));

    SEO::withoutRobotsTxtSitemap();
    expect(SEO::robotsTxt())
        ->toContain(route('lt.sitemap'))
        ->not
        ->toContain('https://foobar.com/sitemap.txt');

    SEO::withRobotsTxtRule('foo_bar_bot', collect(['path', 'other_path']), collect(['not_path', 'other_not_path']));
    SEO::withRobotsTxtRule('foo_bar_bot2', collect(['path', 'other_path']), collect(['not_path', 'other_not_path']));

    expect(SEO::robotsTxt())
        ->toContain('User-agent: foo_bar_bot')
        ->toContain('User-agent: foo_bar_bot2')
        ->toContain('Allow: path')
        ->toContain('Allow: other_path')
        ->toContain('Disallow: not_path')
        ->toContain('Disallow: other_not_path')
        ->and(SEO::getRobotsTxtSitemap())
        ->toBeUrl();

    SEO::withoutRobotsTxtRule('foo_bar_bot2');

    expect(SEO::robotsTxt())
        ->toContain('User-agent: foo_bar_bot')
        ->not
        ->toContain('User-agent: foo_bar_bot2');

    SEO::withoutRobotsTxtRule();

    expect(SEO::robotsTxt())
        ->not
        ->toContain('User-agent');

});
