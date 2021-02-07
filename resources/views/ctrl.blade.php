@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        <h3>
            Controller 生成器
            <small> </small>
        </h3>
        <p>
            该生成器可为您快速生成一个Api Controller类，并且实现增、删、改、查等方法，
            您也可以在添加一些其他的自定义方法。
            请确定您在使用之前已经创建了相应的Eloquent Model、 Request Form和Resource资源类。
        </p>
    </div>


    <form method="post" action="{{route("gii.ctrl.preview")}}">

        <div class="form-group">
            <label for="ctrl">Controller 类</label>
            <input type="text" class="form-control" name="ctrl" id="ctrl" placeholder=""
                   value="App\Http\Controllers\">
            <span class="help-block">请填写完整类名，并以{{config('gii.suffix.class.controller')}}结尾。
                您可以点击<a onclick="Current.loadClasses()" class="btn btn-link ">联想</a>
                来自动实现自动载入其他输入项。</span>

        </div>
        <div class="form-group">
            <label for="baseCtrl">Base Controller 类</label>
            <input type="text" class="form-control" name="baseCtrl" id="baseCtrl" placeholder=""
                   value="Illuminate\Routing\Controller">
            <span class="help-block">默认使用Laravel提供的<code>Illuminate\Routing\Controller</code>类，
                但强烈建议您实现自己的Controller基类。</span>

        </div>
        <div class="form-group">
            <label for="form">Request Form 类</label>
            <input type="text" class="form-control" name="form" id="form" placeholder=""
                   value="">
            <span class="help-block"></span>
        </div>
        <div class="form-group">
            <label for="resource">Resource 类</label>
            <input type="text" class="form-control" name="resource" id="resource" placeholder=""
                   value="">
            <span class="help-block"></span>
        </div>
        <div class="form-group">
            <label for="form">Repository 类</label>
            <input type="text" class="form-control" name="repository" id="repository" placeholder=""
                   value="">
            <span class="help-block"></span>
        </div>
        <div class="form-group">
            <label for="model">Eloquent Model 类</label>
            <input type="text" class="form-control" id="model" name="model" placeholder=""
                   value="">
            <span class="help-block"></span>
        </div>

        <button type="button" class="btn btn-primary" onclick="Current.preview(this.form)">预览</button>
        <button type="button" class="btn btn-default hidden" onclick="Current.generate(this.form)" id="btn-create">创建
        </button>
        <br/>
        @include('gii::layout._files')
    </form>


    <script>
        let Current = {
            loadClasses: function () {
                let cls = $('input[name=ctrl]').val();
                if (!cls) {
                    return false;
                }
                let url = '{{route('gii.ctrl.classes',['class'=>'/'])}}/' + encodeURI(cls);
                AppRequest.get(url, {}, function (response) {
                    console.log('response =', response);
                    $('input[name=ctrl]').val(response.classes.controller);
                    $('input[name=form]').val(response.classes.form);
                    $('input[name=resource]').val(response.classes.resource);
                    $('input[name=model]').val(response.classes.model);
                    $('input[name=repository]').val(response.classes.repository);

                })
            },

            init: function () {
                $('#fc-files').addClass('hidden');
                $('#btn-create').addClass('hidden');
                $('pre').addClass('hidden');
            },
            preview: function (form) {
                Current.init();
                form.action = '{{route("gii.ctrl.preview")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    $('#fc-files').removeClass('hidden');
                    $('#btn-create').removeClass('hidden');
                    $('pre').addClass('hidden');
                    fillFiles(response.files);
                });

            },
            generate: function (form) {
                form.action = '{{route("gii.ctrl.generate")}}';
                AppRequest.submitForm(form, function (response) {
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
