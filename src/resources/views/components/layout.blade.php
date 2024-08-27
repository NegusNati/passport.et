<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Job platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class=" pb-20 min-h-screen  bg-gray-100 dark:bg-gray-900  m-auto bg-gradient-to-r from-rose-100 to-teal-100">
    <div class="px-8 mt-3">



        <main class="mt-8 max-w-[990px] m-auto">
            {{ $slot }}
        </main>
    </div>
</body>

</html>
