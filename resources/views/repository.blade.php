@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        <h3>
            Repository 生成器
            <small> </small>
        </h3>
        <p>
            此生成器为您生成一个带有Model查询的Repository。
        </p>
    </div>


    <form method="post" action="{{route("gii.repository.preview")}}">

        <div class="form-group">
            <label for="model">Eloquent Model 类</label>
            <input type="text" class="form-control" id="model" name="model" placeholder=""
                   value="App\Models\" >
            <span class="help-block">
                请输入一个已存在的Eloquent Model。<br/>
                输入完成后，您可以点击
                <a  onclick="Current.guessByModel()" class="btn btn-link ">联想</a>
                来实现根据Model自动载入FormRequest类，但不保证正确性。
            </span>
        </div>
        <div class="form-group">
            <label for="form">Repository 类</label>
            <input type="text" class="form-control" name="repository" id="repository" placeholder=""
            value="App\Http\Repositories\">
            <span class="help-block">对于Repository类的命名，强烈建议您按照相对应的Controller名称命名
                     并用后缀<code>{{config('gii.suffix.class.repository')}}</code>结尾。

            </span>
        </div>


        <button type="button" class="btn btn-primary" onclick="Current.preview(this.form)">预览</button>
        <button type="button" class="btn btn-default hidden" onclick="Current.generate(this.form)" id="btn-create">创建
        </button>
        <br/>
        @include('gii::layout._files')
    </form>





    <script>
        let Current = {
            guessByModel: function () {
                let cls = $('input[name=model]').val();
                if (!cls) {
                    return false;
                }
                let url = '{{route('gii.curd.guess-by-model',['model'=>'/'])}}/' + encodeURI(cls);
                AppRequest.get(url, {}, function (response) {
                    console.log('response =', response);
                    $('input[name=repository]').val(response.classes.repository);
                    $('input[name=model]').val(response.classes.model);
                })
            },

            init: function () {
                $('#fc-files').addClass('hidden');
                $('#btn-create').addClass('hidden');
                $('pre').addClass('hidden');
            },
            preview: function (form) {
                Current.init();
                form.action = '{{route("gii.repository.preview")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    $('#fc-files').removeClass('hidden');
                    $('#btn-create').removeClass('hidden');
                    $('pre').addClass('hidden');
                    fillFiles(response.files);
                });

            },
            generate: function (form) {
                form.action = '{{route("gii.repository.generate")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    Current.init();
                    fillResult(response);

                });
            }
        };

        $(function () {

        });
    </script>
@endsection
