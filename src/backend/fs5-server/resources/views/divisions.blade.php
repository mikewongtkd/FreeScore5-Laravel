<?php
    use \App\Models\Division;
    $divisions = \App\Models\Division::orderBy( 'code' )->get();
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Divisions</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Open Sans', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased">
    <ul>
@foreach( $divisions as $division )
        <li>{{ strtoupper( $division->code )}} {{ $division->description }}
            <ul>
@foreach( $division->athletes as $athlete )
                <li>{{ $athlete->fname }} {{ strtoupper( $athlete->lname )}}</li>
@endforeach
            </ul>
        </li>
@endforeach
    </ul>
    </body>
</html>
