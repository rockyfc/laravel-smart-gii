@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        <h3>
            Eloquent Model Comment 修复
            <small> </small>
        </h3>
        <p>
            此生成器为指定的Model类生成或者修复注释内容
        </p>
    </div>


    <form method="post" action="{{route("gii.model.fixer")}}">
        <div class="form-group">

            <label for="tables">数据库连接 / 表名称</label>
            <div class="form-group form-inline">

                <select class="form-control " id="model" name="model">
                    <option value="">请选择...</option>
                    @foreach($models as $class=>$path)
                        <option value="{{$class}}">{{$class}}</option>
                    @endforeach
                </select>
                <span class="help-block">列表中显示的model，注释内容可以做修复</span>

            </div>
        </div>


        {{--<div class="checkbox">
            <label>
                <input type="checkbox"> Check me out
            </label>
        </div>--}}
        <button type="button" class="btn btn-default" onclick="Current.submit(this.form)" id="btn-create">创建
        </button>
        <br/>
        @include('gii::layout._files')
    </form>

    <script>
        let Current = {
            submit: function (form) {
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    //fillResult(response);
                    alert('操作成功');
                    window.location.reload();
                });
            }
        };
    </script>
@endsection
