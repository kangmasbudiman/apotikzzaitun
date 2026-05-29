<!doctype html>
<html class="no-js" lang="en">

<head>
    
    @include('_includes.head')

</head>

<body class="open">

    <!-- Left Panel -->

    @include('_partials.sidebar')

    <!-- /#left-panel -->

    <!-- Left Panel -->

    <!-- Right Panel -->

    <div id="right-panel" class="right-panel">

        <!-- Header-->
        
        <x-navbar />

        <!-- /header -->
        <!-- Header-->

        <div class="breadcrumbs">
            <div class="col-sm-4">
                <div class="page-header float-left">
                    <div class="page-title">
                        <h1>@yield('title')</h1>
                    </div>
                </div>
            </div>
            @yield('nav')
        </div>

        <div class="content mt-3">

            @yield('content')
            
        </div> <!-- .content -->
    </div><!-- /#right-panel -->

    <!-- Right Panel -->

   @include('_includes/foot')

</body>

</html>
