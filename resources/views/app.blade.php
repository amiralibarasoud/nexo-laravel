<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Base -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="نکسووست">
    <link rel="canonical" href="{{ url()->current() }}">

    <title inertia>{{ config('app.name') }}</title>

    <!-- Open Graph defaults (overridden per page via <Head>) -->
    <meta property="og:site_name" content="نکسووست">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.ico">

    <!-- Fonts: Vazirmatn -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead

    <!-- Goftino live chat -->
    <script type="text/javascript">
        !function(){var i="AJiEtE",d=document,g=d.createElement("script"),s="https://www.goftino.com/widget/"+i,l=localStorage.getItem("goftino_"+i);g.type="text/javascript",g.async=!0,g.src=l?s+"?o="+l:s;d.getElementsByTagName("head")[0].appendChild(g);}();
    </script>
</head>
<body class="font-vazir antialiased bg-gray-50">
    @inertia
</body>
</html>
