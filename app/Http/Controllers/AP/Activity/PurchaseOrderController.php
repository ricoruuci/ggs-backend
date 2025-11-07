<?php

namespace App\Http\Controllers\AP\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AP\Activity\PurchaseOrderHd;
use App\Models\AP\Activity\PurchaseOrderDt;
use App\Models\AP\Master\APMsSupplier;
use App\Models\AR\Master\ARMsSales;
use App\Models\IN\Master\INMsItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $sales = new PurchaseOrderHd();

        $modelDetail = new PurchaseOrderDt();

        $mssales = new ARMsSales();

        $mscustomer = new APMsSupplier();

        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $sales::$rulesInsert, $sales::$messagesInsert);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        }

        $validatorDetail = Validator::make($request->all(), $modelDetail::$rulesInsert, $modelDetail::$messagesInsert);

        if ($validatorDetail->fails()) {
            return $this->responseError($validatorDetail->messages(), 400);
        }

        $cek = $mscustomer->cekSupplier($request->input('suppid'));

        if ($cek == false) {

            return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
        }

        $cek = $mssales->cekSales($request->input('purchasingid'));

        if ($cek == false) {

            return $this->responseError('kode purchasing tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $hasilpoid = $sales->beforeAutoNumber($request->input('transdate'), $request->input('fgtax'));

            $insertheader = $sales->insertData([
                'poid' => $hasilpoid,
                'transdate' => $request->input('transdate'),
                'suppname' => $request->input('suppname'),
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'up' => $request->input('up'),
                'purchasingid' => $request->input('purchasingid'),
                'telp' => $request->input('telp'),
                'fax' => $request->input('email'),
                // 'email' => $request->input('email'),
                'note' => $request->input('note'),
                'fgtax' => $request->input('fgtax'),
                'suppid' => $request->input('suppid'),
                'soid' => $request->input('soid'),
                'nilaitax' => $request->input('nilaitax'),
                'term' => $request->input('term')
            ]);

            if ($insertheader == false) {
                DB::rollBack();

                return $this->responseError('insert header gagal', 400);
            }

            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {

                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'poid' => $hasilpoid,
                    'itemid' => $arrDetail[$i]['itemid'],
                    'urut' => $arrDetail[$i]['urut'],
                    'itemname' => $arrDetail[$i]['itemname'],
                    'note' => $arrDetail[$i]['note'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'partno' => $arrDetail[$i]['partno']
                ]);

                if ($insertdetail == false) {
                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }
            }

            $hitung = $sales->hitungTotal([
                'poid' => $hasilpoid
            ]);

            $sales->updateTotal([
                'grandtotal' => $hitung->grandtotal,
                'poid' => $hasilpoid
            ]);

            DB::commit();

            return $this->responseSuccess('insert berhasil', 200, ['poid' => $hasilpoid]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function updateAllData(Request $request)
    {
        $sales = new PurchaseOrderHd();

        $modelDetail = new PurchaseOrderDt();

        $mssales = new ARMsSales();

        $mscustomer = new APMsSupplier();

        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $sales::$rulesUpdateAll, $sales::$messagesUpdate);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        }

        $validatorDetail = Validator::make($request->all(), $modelDetail::$rulesInsert, $modelDetail::$messagesInsert);

        if ($validatorDetail->fails()) {
            return $this->responseError($validatorDetail->messages(), 400);
        }

        $cek = $sales->cekPurchaseOrder($request->input('poid'));

        if ($cek == false) {

            return $this->responseError('nomor purchase order tidak terdaftar', 400);
        }

        $cek = $sales->cekBolehEdit($request->input('poid'));

        if ($cek == true) {

            return $this->responseError('sudah ada penerimaan ' . $cek->saleid . ' tidak bisa edit', 400);
        }

        $cek = $mscustomer->cekSupplier($request->input('suppid'));

        if ($cek == false) {

            return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
        }

        $cek = $mssales->cekSales($request->input('purchasingid'));

        if ($cek == false) {

            return $this->responseError('kode purchasing tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $insertheader = $sales->updateAllData([
                'poid' => $request->input('poid'),
                'transdate' => $request->input('transdate'),
                'suppname' => $request->input('suppname'),
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'up' => $request->input('up'),
                'purchasingid' => $request->input('purchasingid'),
                'telp' => $request->input('telp'),
                'fax' => $request->input('email'),
                // 'email' => $request->input('email'),
                'note' => $request->input('note'),
                'fgtax' => $request->input('fgtax'),
                'suppid' => $request->input('suppid'),
                'soid' => $request->input('soid'),
                'nilaitax' => $request->input('nilaitax'),
                'term' => $request->input('term')
            ]);

            if ($insertheader == false) {
                DB::rollBack();

                return $this->responseError('insert header gagal', 400);
            }

            $deletedetail = $modelDetail->deleteData([
                'poid' => $request->input('poid')
            ]);

            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {
                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'poid' => $request->input('poid'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'urut' => $arrDetail[$i]['urut'],
                    'itemname' => $arrDetail[$i]['itemname'],
                    'note' => $arrDetail[$i]['note'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'partno' => $arrDetail[$i]['partno']
                ]);

                if ($insertdetail == false) {
                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }
            }

            $hitung = $sales->hitungTotal([
                'poid' => $request->input('poid')
            ]);

            $sales->updateTotal([
                'grandtotal' => $hitung->grandtotal,
                'poid' => $request->input('poid')
            ]);

            DB::commit();

            return $this->responseSuccess('update berhasil', 200, ['poid' => $request->input('poid')]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function getListData(Request $request)
    {
        $sales = new PurchaseOrderHd();
        $salesdt = new PurchaseOrderDt();

        if ($request->input('poid')) {

            $resultheader = $sales->getdata(
                [
                    'poid' => $request->input('poid')
                ]
            );

            $resultdetail = $salesdt->getdata(
                [
                    'poid' => $request->input('poid')
                ]
            );

            $result = [
                'header' => $resultheader,
                'detail' => $resultdetail
            ];

            return $this->responseData($result);
        } else {

            $result = $sales->getListData(
                [
                    'dari' => $request->input('dari'),
                    'sampai' => $request->input('sampai'),
                    'custid' => $request->input('custid') ?? '',
                    'suppid' => $request->input('suppid') ?? '',
                    'purchasingid' => $request->input('purchasingid') ?? '',
                    'keyword' => $request->input('keyword') ?? '',
                    'sortby' => $request->input('sortby') ?? 'old'
                ]
            );

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function deleteData(Request $request)
    {
        $sales = new PurchaseOrderHd();

        $cek = $sales->cekPurchaseOrder($request->input('poid'));

        if ($cek == false) {

            return $this->responseError('nomor purchase order tidak terdaftar', 400);
        }

        $cek = $sales->cekBolehEdit($request->input('poid'));

        if ($cek == true) {

            return $this->responseError('sudah ada penerimaan ' . $cek->saleid . ' tidak bisa edit', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $sales->deleteData([
                'poid' => $request->input('poid')
            ]);

            if ($deleted) {
                DB::commit();

                return $this->responseSuccess('delete berhasil', 200, ['poid' => $request->input('poid')]);
            } else {
                DB::rollBack();

                return $this->responseError('delete gagal', 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

  
}