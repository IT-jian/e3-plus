<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sys\Shop;
use App\Services\Platform\ShopAuthorizationManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $where = [];

        if ($name = $request->get('id')) {
            $where['id'] = $name;
        }

        if ($code = $request->get('code')) {
            $where['code'] = $code;
        }

        if ($ids = $request->get('ids')) {
            $where[] = ['in' => ['id' => $ids]];
        }

        if ($name = $request->get('name')) {
            $where[] = ['name', 'like', "%{$name}%"];
        }

        $shops = Shop::where($where)
            ->paginate($request->get('perPage', 15));

        return $shops;
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $shop = Shop::create($input);
        $shop->clearCache();

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($shop);
    }

    public function show($id)
    {
        $shop = Shop::findOrFail($id);

        return $this->respond($shop);
    }

    public function update(Request $request,$id)
    {
        $input = $request->all();

        $shop = Shop::findOrFail($id);

        $shop->fill($input)->save();
        $shop->clearCache();

        return $this->respond($shop);
    }

    //
    public function destroy($id)
    {
        $shop = Shop::findOrFail($id);

        $shop->delete();
        $shop->clearCache();

        return $this->success([]);
    }

}
