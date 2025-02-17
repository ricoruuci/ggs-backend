<?php

namespace App\Http\Controllers\AP\Activity;

use App\Http\Controllers\Controller;
use App\Models\AP\Activity\OtorisasiPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class OtorisasiPembelianController extends Controller
{
    use ArrayPaginator, HttpResponse;


    public function getListOto(Request $request)
    {

        $otorisasi = new OtorisasiPembelian();

        if ($request->input('purchaseid')) {

            $a = $otorisasi->getData(
                [
                    'purchaseid' => $request->input('purchaseid')
                ]
            );

            $b = $otorisasi->getDataDetail(
                [
                    'purchaseid' => $request->input('purchaseid')
                ]
            );

            $result = [
                'header' => $a,
                'detail' => $b
            ];

            return $this->responseData($result);
        } else {
            $result = $otorisasi->getListOto([
                'purchaseidkeyword' => $request->input('purchaseidkeyword') ?? '',
                'suppnamekeyword' => $request->input('suppnamekeyword') ?? ''
            ]);
        }
        //dd(var_dump('a'));
        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function updateData(Request $request)
    {
        $otorisasi = new OtorisasiPembelian();

        $cek = $otorisasi->cekPurchase($request->input('purchaseid'));

        if ($cek == false) {

            return $this->responseError('nota purchase tidak terdaftar', 400);
        }

        DB::beginTransaction();

        try {
            $update = $otorisasi->updateData([
                'purchaseid' => $request->input('purchaseid'),
                'jenis' => $request->input('jenis')
            ]);

            DB::commit();

            return $this->responseSuccess('otorisasi berhasil', 200, ['nota purchase :' => $request->input('purchaseid')]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }
}
