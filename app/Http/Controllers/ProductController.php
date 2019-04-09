<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class ProductController extends Controller
{

    /**
     * 返回elasticsearch的client实例
     *
     * @return \Elasticsearch\Client
     */
    private function getESClient()
    {
        return ClientBuilder::create()->build();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pageSize = 5;
        $orderby = "id";
        $where = [
            '_source' => 'false',
            'from' => ($request->input("page", 1) - 1) * $pageSize,
            'size' => $pageSize,
            'sort' => [$orderby => ['order' => "desc"]]
        ];
        if ($request->input("title")) {
            $where['query']['bool']['must'][] = ['match_phrase' => [ 'title' => [
                'query' => $request->input("title"),
                'slop' => 20,
                'analyzer' => 'ik_max_word',
            ]]];
        }
        if ($request->input("price")) {
            $where['query']['bool']['must'][] = ['match' => [ 'price' => $request->input("price") ]];
        }
        if ($request->input("desc")) {
            $where['query']['bool']['must'][] = ['match_phrase' => [ 'desc' => [
                'query' => $request->input("desc"),
                'slop' => 20,
                'analyzer' => 'ik_max_word',
            ]]];
        }
        $params = [
            'index' => 'products',
            'type' => '_doc',
            'body' => $where
        ];
        $client = $this->getESClient();
        $productsESData = $this->getFormatESData($client->search($params));

        $data['products'] = Product::whereIn('id', $productsESData['data'])->orderBy($orderby, 'desc')->get()->toArray();
        $data['paginator'] = new Paginator($data['products'], $productsESData['total'], $pageSize, null, ['path'=> route("product.index")."?".$this->get_current_query_with_no_page()]);
        $data['es_ids'] = $productsESData['data'];
        return view("product.index", $data);
    }

    /**
     * 获取没有page的查询字符串
     *
     * @return string
     */
    private function get_current_query_with_no_page()
    {
        parse_str($_SERVER['QUERY_STRING'], $data);
        unset($data['page']);
        return http_build_query($data);
    }

    /**
     * 传入搜索返回的数据，返回整理好的数据
     *
     * @param $esData
     *
     * @return array
     */
    private function getFormatESData($esData)
    {
        $rdata = [];
        $rdata['total'] = $esData['hits']['total'];
        $rdata['data'] = [];
        if ($rdata['total'] > 0) {
            foreach ($esData['hits']['hits'] as $item) {
                $rdata['data'][] = $item['_id'];
            }
        }
        return $rdata;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("product.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'price' => 'required|numeric',
            'desc' => 'required',
        ]);

        $product = new Product();
        $insertData = $product->create($request->input())->toArray();
        // 如果写入数据库成功，则向elasticsearch中也写入一份文档
        if (isset($insertData['id'])) {
            $insertBody = array_only($insertData, ["title", "desc", "price", "id"]);
            $params = [
                'index' => 'products',
                'type' => '_doc',
                'id' => $insertData['id'],
                'body' => $insertBody
            ];
            $client = $this->getESClient();
            $client->index($params);
        }
        return redirect(route("product.index"))->with("message", "添加成功");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['product'] = Product::find($id)->toArray();
        return view("product.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'price' => 'required|numeric',
            'desc' => 'required',
        ]);

        $product = new Product();
        // 如果数据库更新成功，则向elasticsearch也更新文档
        if ($product->find($id)->update($request->input())) {
            $updateBody = $request->only(["title", "desc", "price"]);
            $updateBody['id'] = $id;
            $params = [
                'index' => 'products',
                'type' => '_doc',
                'id' => $id,
                'body' => [
                    'doc' => $updateBody
                ]
            ];
            $client = $this->getESClient();
            $client->update($params);
        }

        return redirect(route("product.index"))->with("message", "更新成功");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Product::destroy($id)) {
            $params = [
                'index' => 'products',
                'type' => '_doc',
                'id' => $id,
            ];
            $client = $this->getESClient();
            $client->delete($params);
        }
        return response()->json([
            'code' => '204',
            'msg' => '删除成功'
        ]);
    }
}
