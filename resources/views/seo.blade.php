@if(!empty($payload['title']))
        <title inertia>{{ $payload['title'] }} - {{ config('app.name', 'Laravel') }}</title>
@else
        <title inertia>{{ config('app.name', 'Laravel') }}</title>
@endif
@if(!empty($payload['description']))
        <meta  name="description" content="{{ $payload['description'] }}" inertia/>
@endif
@if(!empty($payload['robots']))
        <meta  name="robots" content="{{ $payload['robots'] }}" inertia/>
@endif
@if(!empty($payload['canonical']))
        <link rel="canonical" href="{{ $payload['canonical'] }}" inertia/>
@endif

@if(!empty($payload['open_graph']['type']))
        <meta  property="og:type" content="{{ $payload['open_graph']['type'] }}" inertia/>
@endif
@if(!empty($payload['open_graph']['title']))
        <meta  property="og:title" content="{{ $payload['open_graph']['title'] }}" inertia/>
@endif
@if(!empty($payload['open_graph']['description']))
        <meta  property="og:description" content="{{ $payload['open_graph']['description'] }}" inertia/>
@endif
@if(!empty($payload['open_graph']['url']))
        <meta  property="og:url" content="{{ $payload['open_graph']['url'] }}" inertia/>
@endif
@if(!empty($payload['open_graph']['image']['url']))
        <meta  property="og:image" content="{{ $payload['open_graph']['image']['url'] }}" inertia/>
@endif
@if(!empty($payload['open_graph']['image']['alt']))
        <meta  property="og:image.alt" content="{{ $payload['open_graph']['image']['alt'] }}" inertia/>
@endif

@if(!empty($payload['twitter_card']['card']))
        <meta  name="twitter:card" content="{{ $payload['twitter_card']['card'] }}" inertia/>
@endif
@if(!empty($payload['twitter_card']['site']))
        <meta  name="twitter:site" content="{{ $payload['twitter_card']['site'] }}" inertia/>
@endif
@if(!empty($payload['twitter_card']['creator']))
        <meta  name="twitter:creator" content="{{ $payload['twitter_card']['creator'] }}" inertia/>
@endif
@if(!empty($payload['twitter_card']['title']))
        <meta  name="twitter:title" content="{{ $payload['twitter_card']['title'] }}" inertia/>
@endif
@if(!empty($payload['twitter_card']['description']))
        <meta  name="twitter:description" content="{{ $payload['twitter_card']['description'] }}" inertia/>
@endif
@if(!empty($payload['twitter_card']['image']['url']))
        <meta  name="twitter:image" content="{{ $payload['twitter_card']['image']['url'] }}" inertia/>
@endif
@if(!empty($payload['twitter_card']['image']['alt']))
        <meta  name="twitter:image.alt" content="{{ $payload['twitter_card']['image']['alt'] }}" inertia/>
@endif
