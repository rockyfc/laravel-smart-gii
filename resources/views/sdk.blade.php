@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        <h3>
            SDK 生成器
            <small> </small>
        </h3>
        <p>
            该生成器可为您快速生成一个SDK调用包。<br>
            生成的SDK调用包不会受限于当前所使用的底层框架，可在任何不同框架实现的项目当中使用。
        </p>
    </div>


    <form method="post" action="{{route("gii.sdk.preview")}}">

        <div class="form-group">
            <label for="ctrl">命名空间</label>
            <input type="text" class="form-control" name="namespace" id="namespace" placeholder=""
                   value="App\Sdk\">
            <span class="help-block"></span>

        </div>


        <button type="button" class="btn btn-primary" onclick="Current.preview(this.form)">预览</button>
        <button type="button" class="btn btn-default hidden" onclick="Current.generate(this.form)" id="btn-create">创建
        </button>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href="#" id="zip-download" class="hidden"></a>
        <br/>
        @include('gii::layout._files')
    </form>


    <script>
        let Current = {


            init: function () {
                $('#fc-files').addClass('hidden');
                $('#btn-create').addClass('hidden');
                $('pre').addClass('hidden');
            },
            preview: function (form) {
                Current.init();
                form.action = '{{route("gii.sdk.preview")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    $('#fc-files').removeClass('hidden');
                    $('#btn-create').removeClass('hidden');
                    $('pre').addClass('hidden');
                    fillFiles(response.files);
                });

            },
            generate: function (form) {
                form.action = '{{route("gii.sdk.generate")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    Current.init();

                    fillResult(response.sdk);


                    //'gii.sdk.download'

                    $('#zip-download')
                        .attr('href', '{{route("gii.sdk.download",['file'=>'/'])}}'+encodeURI(response.zip))
                        .removeClass('hidden')
                        .html('下载SDK：'+response.zip);

                });
            }
        };

        $(function () {

        });
    </script>
@endsection
