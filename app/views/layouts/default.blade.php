<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Main meta rules -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="Laravel tasks application" content="addtasks|deletetasks">
        <!-- Favicon-->
        <link rel="shortcut icon" href="" />

        <title>
            @section('title')
                Laravel Todo List
            @show
        </title>
        
        @section('styles')
            {{HTML::style('/css/bootstrap.min.css')}}

            {{ HTML::style('/css/bootstrap-theme.min.css') }}

            {{HTML::style('/css/gsd.css')}}
        @show
        
    </head>
    <body>
        @include('partials.topnavbar')
        @include('partials.notifications')
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('partials.sidebar')
                </div>
                <div class="col-md-9">
                    @yield('content')
                </div>
            </div>
        </div>

        @include('partials.footer')
        @include('partials.taskmodal')
        @include('partials.listmodal')
        @section('scripts')
            {{HTML::script('/js/jquery-1.11.1.min.js')}}
            {{HTML::script('/js/bootstrap.min.js')}}
            {{HTML::script('/js/gsd.js')}}
            <!-- Html5shiv for explorer -->
            <!--[if lt IE 9]>
                <script src="js/html5shiv.min.js"></script>
            <![endif]-->
            <script type="text/javascript">
                gsd.defaultList = "{{$default_list}}";
            </script>
        @show
    </body>
</html>
