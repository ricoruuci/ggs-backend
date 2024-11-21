<?php

namespace App\Http\Controllers\AR\Report;

use App\Http\Controllers\Controller;
use App\Models\AR\Report\ARRptRekapSO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class RekapSOController extends Controller
{
    use ArrayPaginator, HttpResponse;
    
    public function getRekapSO(Request $request)
    {
        $item = new ARRptRekapSO();
                
        $result = $item->laporanSO(
            [
                'dari' => $request->input('dari'),
                'sampai' => $request->input('sampai'),
                'soidkeyword' => $request->input('soidkeyword') ?? '',
                'custidkeyword' => $request->input('custidkeyword') ?? '',
                'custnamekeyword' => $request->input('custnamekeyword') ?? '',
                'salesidkeyword' => $request->input('salesidkeyword') ?? '',
                'salesnamekeyword' => $request->input('salesnamekeyword') ?? ''
            ]
        );

        $resultPaginated = $this->arrayPaginator($request, $result['data']);

        $response = [
            'page' => $resultPaginated['page'],
            'per_page' => $resultPaginated['per_page'],
            'total' => $resultPaginated['total'],
            'total_page' => $resultPaginated['total_page'],
            'totalqty' => $result['totalqty'],
            'totalpo' => $result['totalpo'],
            'totalmodal' => $result['totalmodal'],
            'totalmargin' => $result['totalmargin'],
            'totaltotalso' => $result['totaltotalso'],
            'data' => $resultPaginated['data']
        ];

        return $this->responsePagination($response);

    }

}

?>