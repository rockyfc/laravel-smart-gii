<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('vendor/smart/css/bootstrap.min.css') }}">
    <script src="{{ asset('vendor/smart/js/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/smart/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/smart/js/common.js') }}"></script>
    <style>
        .label-default {
            background: #cfefdf;
            color: #00a854;
            font-weight: normal;
        }

        .table > thead > tr > td, .table > thead > tr > th {
            border: 0;
            background: #eee;
        }

        .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th {
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand " href="#">
                        <img alt="Brand" height="25" src="{{asset('images/logo.png')}}">
                    </a>

                </div>


                <p class="navbar-text navbar-right" style="padding-right: 10px;">
                    <a href="/smart-doc" class="navbar-link">Api Doc </a>
                    <a href="#" class="navbar-link">欢迎使用 </a>
                </p>
            </div>
        </nav>
    </div>
</div>

<div class="container">
    <div class="row" style="margin-top: 60px;"></div>

    <div class="row">

        <div class="col-md-3">
            @include('gii::layout._left')
        </div>

        <div class="col-md-9">
            @yield('content')
        </div>


    </div>


</div>

</body>
</html>
