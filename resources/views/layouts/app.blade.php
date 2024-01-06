<!DOCTYPE html>
<html lang="en">
    <head>
    @include('layouts.head')
    @yield('css')
    </head>
    <body>
        @include('layouts.header')
        @include('layouts.aside')
        <main id="main" class="main">
            @if(Session::has('success')) 
                <div class="alert alert-success text-center">{{Session::get('success')}}</div>
            @endif
            @yield('content')
        </main>
        @yield('modals')
        @include('layouts.footer')
        @include('layouts.scripts')
        @yield('scripts')
    </body>
</html>