
<head>

    {{-- umami script  --}}
    <script defer src="https://cloud.umami.is/script.js" data-website-id="c5a13314-68fb-4528-8f6a-b4edfb743294"></script>

    <!-- Cloudflare Web Analytics -->
    <script defer src='https://static.cloudflareinsights.com/beacon.min.js'
        data-cf-beacon='{"token": "a9bcb1d97b42448789842972c3848ebc"}'></script><!-- End Cloudflare Web Analytics -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ethiopian Passport Services & Status Check | ፓስፖርት ኢትዮጵያ | Paaspoortii Itoophiyaa - Passport.ET</title>
    <!-- Optimized Title: Concise, multilingual, includes brand and core service -->

    <meta name="description"
        content="Passport.ET: Your official guide for Ethiopian passport services. Apply, renew, & track your passport status easily. Check status online now!  ፓስፖርትዎን ይፈትሹ! Paaspoortii keessan mirkaneeffadhaa! Get information on application process, requirements, fees, and locations in Ethiopia.">
    <!-- Improved Description:  More engaging, multilingual call to action, expanded service description -->

    <meta name="keywords"
        content="Ethiopian passport, passport ethiopia, ethiopian passport status, check passport status ethiopia, passport renewal ethiopia, passport application ethiopia, immigration ethiopia passport, travel documents ethiopia, visa services ethiopia, የኢትዮጵያ ፓስፖርት, ፓስፖርት ኢትዮጵያ, የፓስፖርት ሁኔታ, ፓስፖርት ማደስ, ፓስፖርት ማመልከቻ, Paaspoortii Itoophiyaa, haala paaspoortii, haaromsaa paaspoortii, iyyannoo paaspoortii, baasii paaspoortii, teessoo paaspoortii">
    <!-- Expanded Keywords: Includes English, Amharic, and Oromo variations of key terms -->

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://passport.et/">
    <meta property="og:title" content="Passport.ET - የኢትዮጵያ ፓስፖርት አገልግሎት | Ethiopian Passport Services Aid">
    <!-- OG Title: Brand name + multilingual service description -->
    <meta property="og:description"
        content="The official portal for Ethiopian Passport Services Aid. Apply, renew, and track your passport status with ease.  ፓስፖርትዎን በቀላሉ ያመልክቱ፣ ያድሱ እና ሁኔታውን ይከታተሉ።">
    <!-- OG Description:  Slightly shorter, multilingual, action-oriented -->
    <meta property="og:image" content="{{ asset('PASSPORT1.jpg') }}">
    <meta property="og:image:alt" content="Passport.ET - Ethiopian Passport Services Aid | የኢትዮጵያ ፓስፖርት">
    <!-- OG Image Alt: Multilingual brand and service description -->

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://passport.et/">
    <meta property="twitter:title" content="Passport.ET - Ethiopian Passport Services | Paaspoortii Itoophiyaa">
    <!-- Twitter Title:  Brand name + multilingual service focus -->
    <meta property="twitter:description"
        content="Official Ethiopian Passport Services portal.  Get information on application, renewal, and status checking.  መረጃ ያግኙ ስለ ፓስፖርት አፕሊኬሽን, እድሳት እና የሁኔታ ፍተሻ።">
    <!-- Twitter Description:  Concise, multilingual service description -->
    <meta property="twitter:image" content="{{ asset('PASSPORT1.jpg') }}">

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

    