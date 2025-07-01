<?php

namespace Laraveltoolkit\Actions\Sitemap;

use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Laraveltoolkit\Facades\Sitemap;
use Laraveltoolkit\Sitemap\Index;
use Laraveltoolkit\Sitemap\SitemapRequestedEvent;
use Laraveltoolkit\Sitemap\Url;

class RenderSitemap
{
    protected Request $request;

    protected ?string $index;

    protected ?int $lastModified;

    public function __invoke(Request $request, ?string $index = null)
    {
        $timeout = config('laraveltoolkit.sitemap.timeout');
        if (is_int($timeout)) {
            set_time_limit($timeout);
        }
        $this->bootstrap($request, $index);
        $cacheTtl = config('laraveltoolkit.sitemap.cache');
        $cacheKey = 'lt.sitemap.'.sha1($request->getHost().'::'.($index ?? '').'::'.$this->lastModified);
        $xml = $cacheTtl !== false
            ? Cache::remember($cacheKey, $cacheTtl, fn () => $this->write())
            : $this->write();
        SitemapRequestedEvent::dispatch($request->getHost(), $index, $request->userAgent());

        return response($xml)->header('Content-Type', 'text/xml');
    }

    protected function bootstrap(Request $request, $index): void
    {
        $this->request = $request;
        $this->index = $index;
        $sitemapConfig = base_path('routes/sitemap.php');
        abort_if(! file_exists($sitemapConfig), 404);
        $this->lastModified = filemtime($sitemapConfig);
        if (file_exists(base_path('routes/sitemap.php'))) {
            require $sitemapConfig;
        }
        abort_if(! Sitemap::indexExists($index), 404);
    }

    protected function write(): string
    {
        $items = Sitemap::process($this->request->getHost(), $this->index);
        $maxFileItems = intval(config('laraveltoolkit.sitemap.max_file_items'));
        $count = $items->count();
        if ($count > $maxFileItems) {
            Log::warning(sprintf(
                'The sitemap items count limit of %s was exceeded in %s. This may cause search engines to reject it.',
                Number::format($maxFileItems),
                Number::format($count - $maxFileItems, 0),
            ));
        }
        $type = $items->filter(fn (Index|Url $item
        ) => $item instanceof Index)->count() === 0 ? 'urlset' : 'sitemapindex';
        $xml = new DOMDocument('1.0', 'utf-8');
        $xmlns = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $xmlRoot = $xml->appendChild($xml->createElementNS($xmlns, $type));
        $items->each(fn (Index|Url $item) => $item->toXml($xml, $xmlRoot));

        $content = $xml->saveXML();
        $size = mb_strlen($content, '8bit');
        $maxAllowedSize = intval(config('laraveltoolkit.sitemap.max_file_size'));

        if ($size > $maxAllowedSize) {
            Log::warning(sprintf(
                'The sitemap file size limit of %s was exceeded. This may cause search engines to reject it.',
                Number::fileSize($maxAllowedSize, 2),
            ), [
                'exceeded' => Number::fileSize($size - $maxAllowedSize, 2),
            ]);
        }

        return $content;
    }
}
