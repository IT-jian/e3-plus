<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdidasWmsQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Adidas Wms Queue
 * Class AdidasWmsQueueController
 * @package App\Http\Controllers\Admin
 */
class AdidasWmsQueueController extends Controller
{
    /**
     * Display a listing of the Adidas Wms Queue.
     * GET|HEAD /adidas_wms_queue
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($bisId = $request->get('bis_id')) {
            $where['bis_id'] = $bisId;
        }
        if ($id = $request->get('id')) {
            $where['id'] = $id;
        }

        if ($wms = $request->get('wms')) {
            $where['wms'] = $wms;
        }

        if ($method = $request->get('method')) {
            $where['method'] = $method;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }
        $adidasWmsQueues = AdidasWmsQueue::where($where)->orderByDesc('updated_at')
                    ->paginate($request->get('perPage', 15));

        return $adidasWmsQueues;
    }
}
