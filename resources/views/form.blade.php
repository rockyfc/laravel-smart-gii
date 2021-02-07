@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        <h3>
            Form Request 生成器
            <small> </small>
        </h3>
        <p>
            此生成器为您生成一个带有验证规则的表单验证类。
        </p>
    </div>


    <form method="post" action="{{route("gii.form.preview")}}">

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
            <label for="form">Form Request 类</label>
            <input type="text" class="form-control" name="form" id="form" placeholder=""
            value="App\Http\Requests\">
            <span class="help-block">对于Form Request类的命名，强烈建议您使用单数形式。
                     并用后缀<code>{{config('gii.suffix.class.formRequest')}}</code>结尾。

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
                    $('input[name=form]').val(response.classes.form);
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
                form.action = '{{route("gii.form.preview")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    $('#fc-files').removeClass('hidden');
                    $('#btn-create').removeClass('hidden');
                    $('pre').addClass('hidden');
                    fillFiles(response.files);
                });

            },
            generate: function (form) {
                form.action = '{{route("gii.form.generate")}}';
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
