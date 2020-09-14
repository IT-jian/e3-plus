<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdidasItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Adidas商家编码
 * Class AdidasItemController
 * @package App\Http\Controllers\Admin
 */
class AdidasItemController extends Controller
{
    /**
     * Display a listing of the Adidas商家编码.
     * GET|HEAD /adidas_item
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($itemId = $request->get('item_id')) {
            $where['item_id'] = $itemId;
        }
        if ($outerSkuId = $request->get('outer_sku_id')) {
            $where['outer_sku_id'] = $outerSkuId;
        }

        if ($size = $request->get('size')) {
            $where['size'] = $size;
        }
        $adidasItems = AdidasItem::where($where)
            ->paginate($request->get('perPage', 15));

        return $adidasItems;
    }

    /**
     * Store a newly created Adidas商家编码 in storage.
     * POST /adidas_item
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = AdidasItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $adidasItem = AdidasItem::create($input);
        (new AdidasItem())->removeItemCache($adidasItem->outer_sku_id);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($adidasItem);
    }

    /**
     * Display the specified Adidas商家编码.
     * GET|HEAD /adidas_item/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $adidasItem = AdidasItem::findOrFail($id);

        return $this->respond($adidasItem);
    }

    /**
     * Update the specified Adidas商家编码 in storage.
     * PUT/PATCH /adidas_item/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $rules = AdidasItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $adidasItem = AdidasItem::findOrFail($id);

        $adidasItem->fill($input)->save();
        (new AdidasItem())->removeItemCache($adidasItem->outer_sku_id);

        return $this->respond($adidasItem);
    }

    /**
     * Remove the specified Adidas商家编码 from storage.
     * DELETE /adidas_item/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $adidasItem = AdidasItem::findOrFail($id);

        $adidasItem->delete();
        (new AdidasItem())->removeItemCache($adidasItem->outer_sku_id);

        return $this->success([]);
    }

}
