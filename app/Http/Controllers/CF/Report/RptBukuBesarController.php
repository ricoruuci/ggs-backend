<?php

namespace App\Http\Controllers\CF\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CF\Report\RptBukuBesar1;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class RptBukuBesarController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function getLaporan(Request $request)
    {
        $bb = new RptBukuBesar1();

        $result = $bb->getLaporanBukuBesarHd([
            'dari' => $request->input('dari'),
            'sampai' => $request->input('sampai'),
            'rekeningid' => $request->input('rekeningid') ?? ''
        ]);
        return $result;

        // $resultPaginated = $this->arrayPaginator($request, $result);
        // return $this->responsePagination($resultPaginated);
    }
}