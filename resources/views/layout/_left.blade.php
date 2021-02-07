<div class="fixed">


    <div  class="tab-content">
        <div class="tab-pane fade in active" style="padding:10px;" id="home">

            <div class="list-group">

                <a href="{{route('gii.model.index',['menu=model'])}}" class="list-group-item @if(request('menu')=='model' or request('menu')==null) active @endif ">创建Eloquent Model</a>
                <a href="{{route('gii.model.fixer',['menu=model2'])}}" class="list-group-item @if(request('menu')=='model2' or request('menu')==null) active @endif ">修复Eloquent Model</a>
                <a href="{{route('gii.repository.index',['menu=repository'])}}" class="list-group-item @if(request('menu')=='repository') active @endif">创建Repository</a>
                <a href="{{route('gii.form.index',['menu=form'])}}" class="list-group-item @if(request('menu')=='form') active @endif">创建Form Request</a>
                <a href="{{route('gii.resource.index',['menu=resource'])}}" class="list-group-item @if(request('menu')=='resource') active @endif">创建Resource</a>
                <a href="{{route('gii.ctrl.index',['menu=ctrl'])}}" class="list-group-item @if(request('menu')=='ctrl') active @endif">创建Controller</a>
                <a href="{{route('gii.curd.index',['menu=curd'])}}" class="list-group-item @if(request('menu')=='curd') active @endif">创建CURD</a>
                <a href="{{route('gii.sdk.index',['menu=sdk'])}}" class="list-group-item @if(request('menu')=='sdk') active @endif">创建SDK</a>
                <a href="{{route('gii.tester.index',['menu=tester'])}}" class="list-group-item @if(request('menu')=='tester') active @endif">创建Test Case</a>
            </div>
        </div>



    </div>


</div>
