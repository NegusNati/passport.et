<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>

    {{-- Google Ads --}}
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1062387645972505"
        crossorigin="anonymous"></script>
    <!-- End Google Ads -->

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
        content="Ethiopian Passport Services Aid portal. Check the status of your Ethiopian passport, learn how to register for a new passport, and track the status of your application online before your go to ICS ">
    <meta name="keywords"
        content="Ethiopian passport,  check passport status, passport application process, immigration services, passport renewal, passport registration, passport application, Ethiopia travel documents, visa services, Ethiopian immigration, track passport">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://passport.et/">
    <meta property="og:title" content="Passport.ET - Ethiopian Passport Services Aid">
    <meta property="og:description"
        content="The official portal for Ethiopian Passport Services Aid. Information on how Apply, renew, and track your passport with ease.">
    <meta property="og:image" content="{{ asset('pass_welcome.png') }}">
    <meta property="og:image:alt" content="Passport.ET - Ethiopian Passport Services Aid">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" >
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
