@if(session()->has('_errors'))
    <div class="alert alert-danger" role="alert">
        <strong>警告：</strong>
        @if(session()->has('_errors'))
            @foreach(session('_errors') as $k=>$error)
                <p>{{$loop->index+1}}. {{$error}}</p>
            @endforeach
        @endif

    </div>
@endif

@if(session()->has('_message'))
    <div class="alert alert-success" role="alert">
        @if(session()->has('_message'))
            {{session('_message')}}
        @endif

    </div>
@endif
