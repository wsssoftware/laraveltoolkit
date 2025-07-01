### SEO

Provide an easy way to use SEO on your application.

```php
use Illuminate\Http\Request;
use Inertia\Response;
use Laraveltoolkit\Facades\SEO;
use Laraveltoolkit\SEO\Image;

class ExampleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        SEO::withTitle('Client Orders')
            ->withoutDescription('A long description of this page')
            ->withCanonical('https://foo.com')
            ->withOpenGraphImage(new Image('public', 'seo.webp'))
            ->withTwitterCardImage(new Image('public', 'seo.webp', 'alt title'));
            
        // If you want to remove a property you can call methods prefixed with `without`
        SEO::withoutOpenGraphImage();
            
        return Inertia::render('Example');
    }
}
```

On Inertia (If you use) middleware put:

```php
//...
public function share(Request $request): array
    {
        return [
            //...
            'seo' => fn() => SEO::payload(),
        ];
    }
//...
```

On your views vue `seo` blade component to this works on non JS crawlers

```bladehtml
<head>
    ...
    <x-seo/>
    ...
</head>
```

On your Vue layout load Head component to update head properties when navigate between pages.

```vue
<template>
    <div>
        <Head/>
    </div>
</template>

<script lang="ts">
    import {defineComponent} from "vue";
    import {Head} from "laraveltoolkit";

    export default defineComponent({
        name: "TestLayout",
        components: {
            Head,
        }
    });
</script>
```

SEO facade also provide some utils methods

```php
use Laraveltoolkit\Facades\SEO;

// Returns true if request agent is a crawler
SEO::isCrawler();
// or
SEO::isCrawler('user agent');

// Transform a human string into a pretty wrote url string
SEO::friendlyUrlString('A example of string!')
// returns 'a-example-of-string'
```

In conjunction with the [Sitemap](SITEMAP.md) helpers, you can use the SEO facade to generate your Robots.txt.

There are 3 possibilities:

1. Keep your `robots.txt` in the public folder, nothing will happen.
2. Change its name to `robots.stub`, so, the Sitemap helper will take the stub content and add the sitemap url every
   time the user accesses https://foobar.com/robots.txt.
3. Or finally, you can simply remove it and then and SEO facade will generate all then.

You can configura using the follow facade methods:

```php
use Laraveltoolkit\Facades\SEO;
// Disallow some path
SEO::withRobotsTxtRule('user_agent_name', null, collect(['disallow_this_path', 'this_other_too']));

// Allow some path
SEO::withRobotsTxtRule('*', collect(['allow_this_path', 'this_other_too']));

// Remove one user agent
SEO::withoutRobotsTxtRule('google_bot');

// Remove all users agent
SEO::withoutRobotsTxtRule();

// Passing sitemap url
SEO::withRobotsTxtSitemap('https://fooobar.com/custom-sitemap.txt');

// Removing sitemap url
SEO::withoutRobotsTxtSitemap();
```

> If you not pass sitemap url, will be used de default sitemap route (`lt.sitemap`)
