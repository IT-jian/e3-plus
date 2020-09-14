<?php

namespace App\Http\Controllers;

use App\Models\Shangdian;
use Illuminate\Http\Request;

class ShangdianController extends Controller
{
    public function index(Request $request)
    {
        $result = Shangdian::where([])->paginate($request->get('perPage', 15));

        return $result;
    }

    public function show($id)
    {
        return Shangdian::findOrFail($id);
    }
}
