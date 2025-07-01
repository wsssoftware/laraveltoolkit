<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class SEO
{
    private Payload $payload;

    public function __construct()
    {
        $this->payload = new Payload;
        $robots = config('laraveltoolkit.seo.defaults.robots', []);
        $robots = is_array($robots) ? $robots : [];
        $this->withRobots(...$robots);
    }

    public function friendlyUrlString(
        string $string,
        ?string $separator = null,
        ?string $language = null,
        ?array $dictionary = null,
    ): string {
        $separator = $separator ?? config('laraveltoolkit.seo.friendly_url.separator') ?? '-';
        $language = $language ?? config('laraveltoolkit.seo.friendly_url.language') ?? config('app.locale');
        $dictionary = $dictionary ?? config('laraveltoolkit.seo.friendly_url.dictionary') ?? [];

        return str($string)->slug($separator, $language, $dictionary);
    }

    public function getRobotsTxtSitemap(): ?string
    {
        return $this->payload->robotsTxt->sitemap;
    }

    public function isCrawler(?string $agent = null): bool
    {
        return (new CrawlerDetect)->isCrawler($agent);
    }

    public function payload(): array
    {
        return once(function () {
            if (empty($this->payload->canonical) && $this->payload->canonical !== false) {
                $this->payload->canonical = request()->url();
            }
            if (empty($this->payload->openGraph->url) && $this->payload->openGraph->url !== false) {
                $this->payload->openGraph->url = request()->url();
            }

            return SEOResource::make($this->payload)->response()->getData(true);
        });
    }

    public function robotsTxt(): string
    {
        $lines = collect();
        foreach ($this->payload->robotsTxt->rules as $rule) {
            $lines->push("User-agent: $rule->userAgent");
            $lines->push(
                $rule->allow->isNotEmpty()
                    ? $rule->allow->map(fn (string $path) => "Allow: $path".PHP_EOL)->implode('')
                    : false
            );
            $lines->push(
                $rule->disallow->isNotEmpty()
                    ? $rule->disallow->map(fn (string $path) => "Disallow: $path".PHP_EOL)->implode('')
                    : false
            );
            $lines->push('');
        }
        $sitemap = $this->payload->robotsTxt->sitemap;
        if (! empty($sitemap)) {
            $lines->push("Sitemap: $sitemap");
        }

        return $lines
            ->filter(fn ($line) => $line !== false)
            ->implode(PHP_EOL);
    }

    public function withoutCanonical(?bool $propagate = null): self
    {
        $this->payload->canonical = false;
        if ($propagate ?? $this->payload->propagation) {
            $this->payload->openGraph->url = null;
        }

        return $this;
    }

    public function withoutDescription(?bool $propagate = null): self
    {
        $this->payload->description = null;
        if ($propagate ?? $this->payload->propagation) {
            $this->payload->openGraph->description = null;
            $this->payload->twitterCard->description = null;
        }

        return $this;
    }

    public function withoutOpenGraphType(): self
    {
        $this->payload->openGraph->type = null;

        return $this;
    }

    public function withoutOpenGraphTitle(): self
    {
        $this->payload->openGraph->title = null;

        return $this;
    }

    public function withoutOpenGraphDescription(): self
    {
        $this->payload->openGraph->description = null;

        return $this;
    }

    public function withoutOpenGraphUrl(): self
    {
        $this->payload->openGraph->url = null;

        return $this;
    }

    public function withoutOpenGraphImage(): self
    {
        $this->payload->openGraph->image = null;

        return $this;
    }

    public function withoutPropagation(): self
    {
        $this->payload->propagation = false;

        return $this;
    }

    public function withoutRobots(): self
    {
        $this->payload->robots = collect();

        return $this;
    }

    public function withoutRobotsTxtRule(?string $userAgent = null): self
    {
        $userAgent = str($userAgent)->trim()->toString();
        if (empty($userAgent)) {
            $this->payload->robotsTxt->rules = collect();
        } else {
            $this->payload->robotsTxt->rules->offsetUnset($userAgent);
        }

        return $this;
    }

    public function withoutRobotsTxtSitemap(): self
    {
        $this->payload->robotsTxt->sitemap = Route::has('lt.sitemap') ? route('lt.sitemap') : null;

        return $this;
    }

    public function withoutTitle(?bool $propagate = null): self
    {
        $this->payload->title = null;
        if ($propagate ?? $this->payload->propagation) {
            $this->payload->openGraph->title = null;
            $this->payload->twitterCard->title = null;
        }

        return $this;
    }

    public function withoutTwitterCardSite(): self
    {
        $this->payload->twitterCard->site = null;

        return $this;
    }

    public function withoutTwitterCardCreator(): self
    {
        $this->payload->twitterCard->creator = null;

        return $this;
    }

    public function withoutTwitterCardTitle(): self
    {
        $this->payload->twitterCard->title = null;

        return $this;
    }

    public function withoutTwitterCardDescription(): self
    {
        $this->payload->twitterCard->description = null;

        return $this;
    }

    public function withoutTwitterCardImage(): self
    {
        $this->payload->twitterCard->image = null;

        return $this;
    }

    public function withCanonical(string $canonical, ?bool $propagate = null): self
    {
        $this->payload->canonical = $canonical;
        if ($propagate ?? $this->payload->propagation) {
            $this->payload->openGraph->url = $canonical;
        }

        return $this;
    }

    public function withDescription(string $description, ?bool $propagate = null): self
    {
        $this->payload->description = $description;
        if ($propagate ?? $this->payload->propagation) {
            $this->payload->openGraph->description = $description;
            $this->payload->twitterCard->description = $description;
        }

        return $this;
    }

    public function withOpenGraphType(string $type): self
    {
        $this->payload->openGraph->type = $type;

        return $this;
    }

    public function withOpenGraphTitle(string $title): self
    {
        $this->payload->openGraph->title = $title;

        return $this;
    }

    public function withOpenGraphDescription(string $description): self
    {
        $this->payload->openGraph->description = $description;

        return $this;
    }

    public function withOpenGraphUrl(string $url): self
    {
        $this->payload->openGraph->url = $url;

        return $this;
    }

    public function withOpenGraphImage(Image $image): self
    {
        $this->payload->openGraph->image = $image;

        return $this;
    }

    public function withPropagation(): self
    {
        $this->payload->propagation = true;

        return $this;
    }

    public function withRobots(RobotRule|string ...$items): self
    {
        $this->payload->robots = collect($items)
            ->map(
                fn (string|RobotRule $item) => is_string($item)
                    ? [RobotRule::from(explode(':', $item)[0]), explode(':', $item)[1] ?? null]
                    : [$item, null]
            );

        return $this;
    }

    public function withRobotsTxtRule(
        ?string $userAgent = null,
        ?Collection $allow = null,
        ?Collection $disallow = null
    ): self {
        $userAgent = str($userAgent)->trim()->toString();
        $this->payload->robotsTxt->rules->put($userAgent, new RobotsTxtRule(
            $userAgent,
            $allow ?? collect(),
            $disallow ?? collect()
        ));

        return $this;
    }

    public function withRobotsTxtSitemap(string $url): self
    {
        $this->payload->robotsTxt->sitemap = $url;

        return $this;
    }

    public function withTitle(string $title, ?bool $propagate = null): self
    {
        $this->payload->title = $title;
        if ($propagate ?? $this->payload->propagation) {
            $this->payload->openGraph->title = $title;
            $this->payload->twitterCard->title = $title;
        }

        return $this;
    }

    public function withTwitterCardSite(string $site): self
    {
        $this->payload->twitterCard->site = $site;

        return $this;
    }

    public function withTwitterCardCreator(string $creator): self
    {
        $this->payload->twitterCard->creator = $creator;

        return $this;
    }

    public function withTwitterCardTitle(string $title): self
    {
        $this->payload->twitterCard->title = $title;

        return $this;
    }

    public function withTwitterCardDescription(string $description): self
    {
        $this->payload->twitterCard->description = $description;

        return $this;
    }

    public function withTwitterCardImage(Image $image): self
    {
        $this->payload->twitterCard->image = $image;

        return $this;
    }
}
