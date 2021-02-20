@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        <h3>
            CURD 生成器
            <small>
            </small>
        </h3>
        <p>
            此生成器可以生成Controller、Request Form、Resource，并对应Model生成增、删、改、查等相关接口。<br/>
            但不会生成Model类，而是依赖于一个已经存在的Eloquent Model类。
        </p>
    </div>


    <form method="post" action="{{route("gii.curd.preview")}}">
        <div class="form-group">
            <label for="model">Eloquent Model 类</label>
            <input type="text" class="form-control" id="model" name="model" placeholder=""
                   value="App\Models\" >
            <span class="help-block">
                请输入一个已存在的Eloquent Model。<br/>
                输入完成后，您可以点击
                <a  onclick="Current.guessByModel()" class="btn btn-link ">联想</a>
                来实现根据Model自动载入其他输入项。
            </span>
        </div>

        <div class="form-group">
            <label for="ctrl">Controller 类</label>
            <input type="text" class="form-control" name="ctrl" id="ctrl" placeholder=""
                   value="">
            <span class="help-block">请填写完整类名，并以<code>{{config('gii.suffix.class.controller')}}</code>结尾。
                您可以点击<a  onclick="Current.guessByCtrl()" class="btn btn-link ">联想</a>
                来实现根据Controller自动载入其他输入项。</span>
        </div>

        <div class="form-group">
            <label for="baseCtrl">Base Controller 类</label>
            <input type="text" class="form-control" name="baseCtrl" id="baseCtrl" placeholder=""
                   value="Illuminate\Routing\Controller">
            <span class="help-block">默认使用Laravel提供的<code>Illuminate\Routing\Controller</code>类，
                但强烈建议您实现自己的Controller基类。</span>

        </div>
        <div class="form-group">
            <label for="form">Form Request 类</label>
            <input type="text" class="form-control" name="form" id="form" placeholder=""
                   value="">
            <span class="help-block">表单类建议用后缀<code>{{\Smart\Gii\Services\ConfigService::formRequestSuffix()}}</code>结尾。</span>

        </div>
        <div class="form-group">
            <label for="resource">Resource 类</label>
            <input type="text" class="form-control" name="resource" id="resource" placeholder=""
                   value="">
            <span class="help-block">资源类建议以后缀<code>{{\Smart\Gii\Services\ConfigService::resourceSuffix()}}</code>结尾。</span>
        </div>

        <div class="form-group">
            <label for="form">Biz 类</label>
            <input type="text" class="form-control" name="repository" id="repository" placeholder=""
                   value="App\Http\Biz\">
            <span class="help-block">对于Biz命名，强烈建议您按照相对应的Controller名称命名
                     并用后缀<code>{{\Smart\Gii\Services\ConfigService::repositorySuffix()}}</code>结尾。

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
                    $('input[name=ctrl]').val(response.classes.controller);
                    $('input[name=form]').val(response.classes.form);
                    $('input[name=resource]').val(response.classes.resource);
                    $('input[name=model]').val(response.classes.model);
                    $('input[name=repository]').val(response.classes.repository);

                })
            },
            guessByCtrl: function () {
                let cls = $('input[name=ctrl]').val();
                if (!cls) {
                    return false;
                }
                let url = '{{route('gii.curd.guess-by-ctrl',['controller'=>'/'])}}/' + encodeURI(cls);
                AppRequest.get(url, {}, function (response) {
                    console.log('response =', response);
                    $('input[name=ctrl]').val(response.classes.controller);
                    $('input[name=form]').val(response.classes.form);
                    $('input[name=resource]').val(response.classes.resource);
                    $('input[name=repository]').val(response.classes.repository);

                    //$('input[name=model]').val(response.classes.model);
                })
            },
            init: function () {
                $('#fc-files').addClass('hidden');
                $('#btn-create').addClass('hidden');
                $('pre').addClass('hidden');
            },
            preview: function (form) {
                Current.init();
                form.action = '{{route("gii.curd.preview")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response files = ', response);
                    $('#fc-files').removeClass('hidden');
                    $('#btn-create').removeClass('hidden');
                    $('pre').addClass('hidden');
                    fillFiles(response.files);
                });

            },
            generate: function (form) {
                form.action = '{{route("gii.curd.generate")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    Current.init();
                    console.log('response = ', response);
                    Current.init();


                    let fillResult=function(data){
                        let str = "请将如下代码添加到路由文件中：\n```php\n";
                        str += data.code+"\n```\n";
                        str += "其中前prefix_name根据实际情况命名，可以省略。\n\n";
                        for (let i in data.files) {
                            if (data.files[i].isDone == true) {
                                str += data.files[i].file + " 创建成功\n";
                            } else {
                                str += data.files[i].file + " 创建失败\n";
                            }
                        }
                        $('pre').removeClass('hidden').html(str);
                    };

                    fillResult(response);
                });
            }
        };

        $(function () {

        });
    </script>
@endsection
