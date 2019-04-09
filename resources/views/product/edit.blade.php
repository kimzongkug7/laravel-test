@extends("public.layout")
@section("content")

<form class="form-horizontal" action="{{ route("product.update", $product['id']) }}" method="POST">

    {{ csrf_field() }}
    {{ method_field("PUT") }}

    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-10">
            <h1>修改商品</h1>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">商品名称</label>
        <div class="col-sm-10">
            <input name="title" type="text" class="form-control" placeholder="商品名称" value="{{ $product['title'] }}">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">商品价格</label>
        <div class="col-sm-10">
            <input name="price" type="text" class="form-control" placeholder="商品价格" value="{{ $product['price'] }}">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">商品详情</label>
        <div class="col-sm-10">
            <textarea name="desc" class="form-control" placeholder="商品详情">{{ $product['desc'] }}</textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-success">修改</button>
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