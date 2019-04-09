@extends("public.layout")

@section("content")
<!-- 错误信息 -->
@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- 提示信息 -->
@if(Session::has('message'))
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        {{Session::get('message')}}
    </div>
@endif

<!-- 表单 -->
<div class="row text-center"><h1>商品列表</h1></div>
<br>
<div class="row">
    <form class="form-inline" method="GET">
        {{ csrf_field() }}
        <div class="form-group">
            <label>商品名称</label>
            <input type="text" name="title" class="form-control" placeholder="商品名称" value="{{ request("title") }}">
        </div>
        <div class="form-group">
            <label>商品价格</label>
            <input type="text" name="price" class="form-control" placeholder="商品价格" value="{{ request("price") }}">
        </div>
        <div class="form-group">
            <label>商品描述</label>
            <input type="text" name="desc" class="form-control" placeholder="商品描述" value="{{ request("desc") }}">
        </div>
        <button type="submit" class="btn btn-success">搜索</button>
        <a href="{{ route("product.create") }}">
            <button type="button" class="pull-right btn btn-info">添加商品</button>
        </a>
    </form>
</div>
<br>

<!-- 表格 -->
<div class="row">
    <table class="table table-bordered table-striped">
        <tr>
            <th>ID</th>
            <th>商品名称</th>
            <th>商品价格</th>
            <th>商品描述</th>
            <th>创建时间</th>
            <th>修改时间</th>
            <th>操作</th>
        </tr>
        @if (count($products) > 0)
            @foreach($products as $product)
                <tr>
                    <th scope="row">{{ $product['id'] }}</th>
                    <td>{{ $product['title'] }}</td>
                    <td>{{ $product['price'] }}</td>
                    <td>{{ $product['desc'] }}</td>
                    <td>{{ $product['created_at'] }}</td>
                    <td>{{ $product['updated_at'] }}</td>
                    <td>
                        <a href="{{ route("product.edit", $product['id']) }}" class="btn btn-success">修改</a>
                        <button type="button" class="btn btn-danger delete_product_btn" data_id="{{ $product['id'] }}">删除</button>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <th scope="row" colspan="7" class="text-center">
                    暂无数据~
                </td>
            </tr>
        @endif
    </table>
</div>
<div class="row text-center">
    {{ $paginator->links() }}
</div>

<div class="row">
    @if (count($es_ids) > 0)
        Elasticsearch查询结果：<br>
        @foreach($es_ids as $es_id)
            {{ $es_id }},
        @endforeach
    @endif
</div>
@endsection

@section("page_script")
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<script>
$(function () {
    // 删除数据
    $(document).on("click",'.delete_product_btn',function(){
        var data_id = $(this).attr("data_id");
        layer.confirm("您确定删除吗？", function () {
            $.ajax({
                "type":"POST",
                "data":{
                    "_method":"DELETE",
                    "_token":"{{ csrf_token() }}"
                },
                "url": "/product/"+data_id,
                "success":function(data) {
                    if (data.code == '204') {
                        layer.alert(data.msg);
                        location.href=location.href;
                    } else {
                        console.log(data);
                    }
                }
            });
        });
    });
});
</script>
@endsection