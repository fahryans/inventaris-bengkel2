<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'SIMA Bengkel')
    </title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>

        *{
            font-family:Poppins,sans-serif;
        }

        body{

            background:#f4f6f9;

            overflow-x:hidden;

        }

        .wrapper{

            display:flex;

            min-height:100vh;

        }

        .main-content{

            flex:1;

            margin-left:260px;

            transition:.3s;

            min-height:100vh;

        }

        .content-area{

            padding:30px;

        }

        @media(max-width:991px){

            .main-content{

                margin-left:0;

            }

            .sidebar-overlay{

                display:none;

                position:fixed;

                top:0;

                left:0;

                right:0;

                bottom:0;

                background:rgba(0,0,0,0.5);

                z-index:998;

            }

            .sidebar.show ~ .sidebar-overlay{

                display:block;

            }

        }

        @media(min-width:992px){

            .sidebar-overlay{

                display:none !important;

            }

        }

    </style>

    @stack('css')

</head>

<body>

<div class="wrapper">

    @include('partials.sidebar')

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <main class="main-content">

        @include('partials.navbar')

        <div class="content-area">

            @yield('content')

        </div>

        @include('partials.footer')

    </main>

</div>

@include('partials.scripts')

@stack('js')

</body>

</html>