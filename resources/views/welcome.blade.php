<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ url('/login') }}">Login</a>
                        <a href="{{ url('/register') }}">Register</a>
                    @endif
                </div>
            @endif

            <div class="content">
                
                <div>
                    {{ Form::open(['url' => 'gethashtag', 'method' => 'post']) }}
                        {{ Form::text('_hastag','',['placeholder' => 'Hashtag','id' => 'hashtag'])}}
                        {{ Form::submit('Submit', array('id' => 'submit')) }}
                    {{ Form::close() }}
                    
                    @if(@$label)                    
                        
                        @foreach(@$label as $key=>$value)
                        <div>
                            <span>{{$value->query_label}}</span>
                            {{ Form::open(['url' => 'getcomment/'.$value->query_label, 'method' => 'post']) }}
                                {{ Form::submit('Get Comment', array('id' => 'submit',@$hasComment[$value->query_label])) }}
                            {{ Form::close() }}
                        </div>
                            
                        @endforeach
                    @endif
                    <br>
                    
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <script type="text/javascript">
        var a ;
        var token = $('meta[name="csrf-token"]').attr('content');
        /*$('#submit').on('click',function(){
            addToCartRequest = $.ajax("https://www.instagram.com/explore/tags/"+$('#hashtag').val()+"/?__a=1", {
                type: 'GET',
                success: function(result) { 
                    $('#dataig').val(JSON.stringify(result));
                    console.log('success data ig');
                },
                error: function(result) {
                    console.log("Terjadi kesalahan data ig, silakan coba lagi.");
                }
            });
        });
        $('#save').on('click',function(){
            var _val = JSON.parse($('#dataig').val());
            _val.searchval = $('#hashtag').val();
            console.log(_val);
            sendData(_val);
        })
        
        function sendData(data){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            laravel = $.ajax('/gethashtag', {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        data: data,                        
                        type: 'POST',
                        success: function(result) {                    
                        console.log('success');
                        },
                        error: function(result) {
                            console.log("Terjadi kesalahan, silakan coba lagi.");
                        }
             });
             return;
        }
        */
        </script>
    </body>
</html>
