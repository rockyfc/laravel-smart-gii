@extends('gii::layout.layout')

@section('content')
    <div class="page-header">
        @include('gii::layout._message')
        <h3>
            Eloquent Model Comment 修复
            <small> </small>
        </h3>
        <p>
            此生成器为指定的Model类生成注释内容，只生成类注释。注释内容的生成会依据以下几个方面映射成注释中的@property标签：
        <ul>
            <li>数据表中的字段。</li>
            <li>当前Model中特殊的方法，get&ltname&gtAttribute()、set&ltname&gtAttribute()。</li>
            <li>当前Model中的ORM映射关系的方法。</li>
        </ul>
        </p>
    </div>


    <form method="post" action="{{route("gii.model.fixer")}}">
        <div class="form-group">

            <label for="tables">请选择一个Model</label>
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

        <div class="form-group">

            <label for="tables">生成方式</label>
            <div class="form-group form-inline">

                <select class="form-control " id="type" name="type">
                    <option value="1">全量覆盖</option>
                    <option value="2">增量更新</option>
                </select>

                <span class="help-block">全量覆盖：重新生成类注释；<br/>
                增量更新：对于有差异的model类补全缺少的@property；</span>

            </div>
        </div>

        <button type="button" class="btn btn-default" onclick="Current.submit(this.form)" id="btn-create">确定
        </button>
        <br/>
        @include('gii::layout._files')
    </form>

    <script>
        let Current = {
            submit: function (form) {
                AppRequest.submitForm(form, function (response) {
                    console.log('response = ', response);
                    //setTimeout(function(){
                        window.location.reload();
                    //},2000)

                });
            }
        };
    </script>
@endsection
