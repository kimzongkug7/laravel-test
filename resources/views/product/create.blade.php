@extends("public.layout")
@section("content")

<form class="form-horizontal" action="{{ route("product.store") }}" method="POST">

    {{ csrf_field() }}

    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-10">
            <h1>添加商品</h1>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">商品名称</label>
        <div class="col-sm-10">
            <input name="title" type="text" class="form-control" placeholder="商品名称">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">商品价格</label>
        <div class="col-sm-10">
            <input name="price" type="text" class="form-control" placeholder="商品价格">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">商品详情</label>
        <div class="col-sm-10">
            <textarea name="desc" class="form-control" placeholder="商品详情"></textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-success">添加</button>
            <a href="{{ route("product.index") }}"><button type="button" class="btn btn-warning">返回列表</button></a>
        </div>
    </div>

</form>

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@endsection