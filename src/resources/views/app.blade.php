<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', session()->get('locale', substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)) ?: config('app.locale')) }}">

<head>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-P7R4V8B3');
    </script>
    <!-- End Google Tag Manager -->

    {{-- could flare analytics --}}

    <!-- Cloudflare Web Analytics -->
    <script defer src='https://static.cloudflareinsights.com/beacon.min.js'
        data-cf-beacon='{"token": "a9bcb1d97b42448789842972c3848ebc"}'></script><!-- End Cloudflare Web Analytics -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'PassportET') }} - Ethiopian Passport Services Aid</title>
    <meta name="description"
        content="Ethiopian Passport Services Aid portal. Information on how to Apply for, renew, or track your Ethiopian passport easily and securely.">
    <meta name="keywords"
        content="Ethiopian passport, passport renewal, passport application, Ethiopia travel documents, visa services, Ethiopian immigration, track passport">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://passport.et/">
    <meta property="og:title" content="{{ config('app.name', 'PassportET') }} - Ethiopian Passport Services Aid">
    <meta property="og:description"
        content="The official portal for Ethiopian Passport Services Aid. Information on how Apply, renew, and track your passport with ease.">
    <meta property="og:image" content="{{ asset('pass_welcome.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="{{ asset('passport_et.svg') }}">
    <meta property="twitter:url" content="https://passport.et/">
    <meta property="twitter:title" content="{{ config('app.name', 'PassportET') }} - Ethiopian Passport Services">
    <meta property="twitter:description"
        content="The official portal for Ethiopian Passport Services Aid. Information on how Apply, renew, and track your passport with ease.">
    <meta property="twitter:image" content="{{ asset('pass_welcome.png') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">



    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @viteReactRefresh
    @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
    @inertiaHead

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
</head>

<body class="font-sans antialiased">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P7R4V8B3" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @inertia
</body>

</html>
