### Sitemap

Provide a way to inform for Search engines the pages of your site.

Publish sitemap files using:

```bash
php artisan vendor:publish --tag=laraveltoolkit-sitemap
```

in routes/sitemap.php (after publishes it):

```php
use App\Models\User;
use Laraveltoolkit\Facades\Sitemap;

// You can pass only url
Sitemap::addUrl(route('index'));
// Or a complete info (url, last modified, change frequency and priority
Sitemap::addUrl(route('login'), today()->subDay(), ChangeFrequency::DAILY, 0.5);

// You can interact with queries or collections
Sitemap::fromQuery(User::query(), function (User $user) {
    Sitemap::addUrl(route('index.user', $user->id), $user->created_at);
});
Sitemap::fromCollection(collect([10, 53, 29]), function (User $user) {
    Sitemap::addUrl(route('index.user', $user->id));
});

// A group of sitemaps that only will return if domain matches. Other registries out from this group will be ignored
Sitemap::domain('abc.dev.test', function () {
    Sitemap::addUrl(route('login'));
});

// An index group used for huge sitemaps with more than 50k rows or 50MB
Sitemap::index('users', function () {
 
    Sitemap::fromQuery(User::query(), function (User $user) {
        Sitemap::addUrl(route('index.user', $user->id), $user->created_at);
    });
});
// will be accessible on `route('lt.sitemap_group', "users");`

// then to put this index on main sitemap use:
Sitemap::addIndex('users');
```

> For mor info about sitemap in robots.txt go to [SEO docs](SEO.md)
