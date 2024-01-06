<!DOCTYPE html>
<html lang="en">
    <head>
    @include('devRantLayouts.head')
    @yield('css')
    </head>
    <body>
        @include('devRantLayouts.header')
        <main id="" style="margin-top:80px">
            @yield('content')
        </main>
        @yield('modals')
        @include('devRantLayouts.footer')
        @include('devRantLayouts.scripts')
        @yield('scripts')
    </body>
</html>