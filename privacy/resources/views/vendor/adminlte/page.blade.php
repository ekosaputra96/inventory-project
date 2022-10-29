@extends('adminlte::master')

@section('adminlte_css')
    <link rel="stylesheet"
          href="{{ asset('vendor/adminlte/dist/css/skins/skin-' . config('adminlte.skin', 'blue') . '.min.css')}} ">
    <link rel="icon" type="image/png" href="/gui_inventory_laravel/css/logo_gui.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/gui_inventory_laravel/css/logo_gui.png" sizes="32x32">
    @stack('css')
    @yield('css')
@stop

@section('body_class', 'skin-' . config('adminlte.skin', 'blue') . ' sidebar-mini ' . (config('adminlte.layout') ? [
    'boxed' => 'layout-boxed',
    'fixed' => 'fixed',
    'top-nav' => 'layout-top-nav'
][config('adminlte.layout')] : '') . (config('adminlte.collapse_sidebar') ? ' sidebar-collapse ' : ''))

@section('body')
<?php use App\Models\Company; ?>
    <div class="wrapper">

        <!-- Main Header -->
        <header class="main-header"><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            @if(config('adminlte.layout') == 'top-nav')
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ url(config('adminlte.dashboard_url', 'home')) }}" class="navbar-brand">
                            {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
                        </a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>



                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">

                        <ul class="nav navbar-nav">
                            @each('adminlte::partials.menu-item-top-nav', $adminlte->menu(), 'item')
                        </ul>
                    </div>
                    <!-- /.navbar-collapse -->
            @else
            <!-- Logo -->
            <a href="{{ url(config('adminlte.dashboard_url', 'home')) }}" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini">{!! config('adminlte.logo_mini', '<b>A</b>LT') !!}</span>
                <!-- logo for regular state and mobile devices -->
            <?php $company = Company::find(auth()->user()->kode_company); ?>
                <span class="logo-lg" style="font-size:10px"><?php echo $company->nama_company; ?></span>
            </a>

            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">{{ trans('adminlte::adminlte.toggle_navigation') }}</span>
                </a>

                        <span class="pull-left">
                            <div class="col">
                    
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="font-size: 14px; background-color: coral"><font color="white"><div id="txt"></div></font></a>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="font-size: 14px; background-color: coral"><font color="white"><div id="txt2"></div></font></a>

                            </div>
                        </span>
            @endif

                <!-- Navbar Right Menu -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <div class="col" style="text-align: right">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="color: white">{{ auth()->user()->name }}&nbsp&nbsp&nbsp&nbsp&nbsp</a><br>
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="color: white"><b>{{ $nama_lokasi }} <span class="caret"></span>&nbsp&nbsp&nbsp&nbsp&nbsp</b></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"
                                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                            >Log-out</a>
                                        </li>

                                        <form id="logout-form" action="{{ url(config('adminlte.logout_url', 'auth/logout')) }}" method="POST" style="display: none;">
                                            @if(config('adminlte.logout_method'))
                                                {{ method_field(config('adminlte.logout_method')) }}
                                            @endif
                                            {{ csrf_field() }}
                                        </form>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </ul>
                </div>
                @if(config('adminlte.layout') == 'top-nav')
                </div>
                @endif
            </nav>
        </header>

        @if(config('adminlte.layout') != 'top-nav')
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">

            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar" style="font-size: 13px;">

                <!-- Sidebar Menu -->
                <ul class="sidebar-menu" data-widget="tree">
                    @each('adminlte::partials.menu-item', $adminlte->menu(), 'item')
                </ul>
                <!-- /.sidebar-menu -->
            </section>
            <!-- /.sidebar -->
        </aside>
        @endif

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @if(config('adminlte.layout') == 'top-nav')
            <div class="container">
            @endif

            <!-- Main content -->
            <section class="content">
                
                @yield('content')
            </section>
            <!-- /.content -->
            @if(config('adminlte.layout') == 'top-nav')
            </div>
            <!-- /.container -->
            @endif
        </div>
        <!-- /.content-wrapper -->

    </div>
    <!-- ./wrapper -->
    
    <div class="se-pre-con"></div>
@stop

<style>
    .no-js #loader { display: none;  }
            .js #loader { display: block; position: absolute; left: 100px; top: 0; }
            .se-pre-con {
                position: fixed;
                left: 0px;
                top: 0px;
                width: 100%;
                height: 100%;
                z-index: 9999;
                background: url(https://wallpapercave.com/uwp/uwp1578707.gif) center no-repeat #fff;
            }
</style>

@section('adminlte_js')
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/vendor/vuejs/vue.js') }}"></script>
    <!--<script src="{{ asset('vendor/adminlte/vendor/axios/axios.min.js') }}"></script>-->
    @stack('js')
    @yield('js')
@stop

<script>
                                function formatDate(date) {
                                          var monthNames = [
                                            "January", "February", "March",
                                            "April", "May", "June", "July",
                                            "August", "September", "October",
                                            "November", "December"
                                          ];

                                          var day = date.getDate();
                                          var monthIndex = date.getMonth();
                                          var year = date.getFullYear();

                                          return day + ' ' + monthNames[monthIndex] + ' ' + year;
                                        }

                                function startTime() {
                                      
                                      var today = new Date();
                                      var today2 = formatDate(new Date())
                                      var h = today.getHours();
                                      var m = today.getMinutes();
                                      var s = today.getSeconds();
                                      var open_month = "<?php echo $period ?>";
                                      m = checkTime(m);
                                      s = checkTime(s);                                    
                                      
                                      document.getElementById('txt').innerHTML =
                                       "<b>"+ today2 + "</b> | <b>" + h + ":" + m + ":" + s + "</b>";
                                      document.getElementById('txt2').innerHTML = "Periode Aktif : " + "<b>" + open_month + "</b>";
                                      var t = setTimeout(startTime, 500);
                                      
                                      $(document).ready(function() {
                                        $(".se-pre-con").fadeOut("slow");
                                      });
                                      
                                    }

                                    function checkTime(i) {
                                      if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
                                      return i;
                                    }                   
                                      
</script>

