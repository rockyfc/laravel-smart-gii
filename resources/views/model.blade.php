@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        <h3>
            Eloquent Model 生成器
            <small> </small>
        </h3>
        <p>
            此生成器为指定的数据库表生成 Eloquent Model 类。
        </p>
    </div>


    <form method="post" action="{{route("gii.model.preview")}}">
        <div class="form-group">

            <label for="tables">数据库连接 / 表名称</label>
            <div class="form-group form-inline">
                <select class="form-control " id="resource" name="connection" onchange="Current.loadTables(this.value)">
                    @foreach($connections as $key=>$item)
                        <option value="{{$key}}" @if($defaultConnections==$key) selected @endif>{{$key}}</option>
                    @endforeach
                </select>
                &nbsp;&nbsp;
                <select class="form-control " id="tables" name="table" onchange="Current.fillModelName(this.value)">
                    @foreach($tables as $key=>$table)
                        <option value="{{$table}}">{{$table}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="model">Eloquent Model类</label>
            <input type="text" class="form-control" name="model" id="model" placeholder="">
            <span class="help-block">对于Model类的命名，强烈建议您使用单数形式。</span>
        </div>
        <div class="form-group">
            <label for="baseModel">Model基类</label>
            <input type="text" class="form-control" id="baseModel" name="baseModel" placeholder=""
                   value="Illuminate\Database\Eloquent\Model">
            <span class="help-block">
                默认使用Laravel提供的<code>Illuminate\Database\Eloquent\Model</code>类， 但强烈建议您实现自己的Model基类。
            </span>
        </div>

        {{--<div class="checkbox">
            <label>
                <input type="checkbox"> Check me out
            </label>
        </div>--}}
        <button type="button" class="btn btn-primary" onclick="Current.preview(this.form)">预览</button>
        <button type="button" class="btn btn-default hidden" onclick="Current.generate(this.form)" id="btn-create">创建
        </button>
        <br/>
        @include('gii::layout._files')
    </form>





    <script>
        let Current = {
            loadTables: function (con) {
                Current.init();
                var url = "{{route('gii.model.tables',['connection'=>'/'])}}/" + con;
                $.get(url, {}, function (response) {
                    if (response.error) {
                        alert(response.error);
                        return;
                    }
                    Current.fillTables(response.tables);
                })
            },
            fillTables: function (tables) {
                Current.init();
                var str = '';
                for (let i in tables) {
                    let name = tables[i];
                    str += '<option value="' + name + '">' + name + '</option>'
                }
                $('#tables').html(str).trigger('change');
            },
            fillModelName: function (table) {
                Current.init();
                let url = '{{route("gii.model.table-to-model",["table"=>'/'])}}/' + table
                $.get(url, {}, function (response) {
                    console.log(response);
                    let name = 'App\\Models\\' + response.name
                    $('input[name=model]').val(name);

                    $('pre').addClass('hidden');
                }, 'json')
            },
            init: function () {
                $('#fc-files').addClass('hidden');
                $('#btn-create').addClass('hidden');
                $('pre').addClass('hidden');
            },
            preview: function (form) {
                Current.init();
                form.action = '{{route("gii.model.preview")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    $('#fc-files').removeClass('hidden');
                    $('#btn-create').removeClass('hidden');
                    $('pre').addClass('hidden');
                    fillFiles(response.files);
                });

            },
            generate: function (form) {
                form.action = '{{route("gii.model.generate")}}';
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    Current.init();
                    fillResult(response);

                });
            }
        };

        $(function () {
            Current.fillModelName($('#tables').val());
        });
    </script>
@endsection
