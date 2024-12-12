<?php

namespace App\Http\Controllers\AR\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AR\Activity\SalesOrderHd;
use App\Models\AR\Activity\SalesOrderDt;
use App\Models\AR\Master\ARMsCustomer;
use App\Models\AR\Master\ARMsSales;
use App\Models\IN\Master\INMsItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class SalesOrderController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $sales = new SalesOrderHd();

        $modelDetail = new SalesOrderDt();

        $mssales = new ARMsSales();

        $mscustomer = new ARMsCustomer();

        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $sales::$rulesInsert, $sales::$messagesInsert);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        }

        $validatorDetail = Validator::make($request->all(), $modelDetail::$rulesInsert, $modelDetail::$messagesInsert);

        if ($validatorDetail->fails()) {
            return $this->responseError($validatorDetail->messages(), 400);
        }

        $cek = $mscustomer->cekCustomer($request->input('custid'));

        if ($cek == false) {

            return $this->responseError('kode pelanggan tidak terdaftar dalam master', 400);
        }

        $cek = $mssales->cekSales($request->input('salesid'));

        if ($cek == false) {

            return $this->responseError('kode sales tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $hasilpoid = $sales->beforeAutoNumber($request->input('transdate'));

            $insertheader = $sales->insertData([
                'soid' => $hasilpoid,
                'pocust' => $request->input('pocust'),
                'custid' => $request->input('custid'),
                'transdate' => $request->input('transdate'),
                'note' => $request->input('note'),
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'tglkirim' => $request->input('tglkirim'),
                'salesid' => $request->input('salesid'),
                'fob' => $request->input('fob'),
                'fgtax' => $request->input('fgtax'),
                'nilaitax' => $request->input('nilaitax'),
                'currid' => $request->input('currid'),
                'address' => $request->input('address'),
                'attn' => $request->input('attn'),
                'telp' => $request->input('telp'),
                'ship' => $request->input('ship'),
                'svc' => $request->input('svc'),
                'term' => $request->input('term'),
                'termin' => $request->input('termin'),
                'disc' => $request->input('disc') ?? 0,
                'tb' => $request->input('svc')
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
                    'soid' => $hasilpoid,
                    'itemid' => $arrDetail[$i]['itemid'],
                    'urut' => $arrDetail[$i]['urut'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'itemname' => $arrDetail[$i]['itemname'],
                    'modal' => $arrDetail[$i]['modal'],
                    'note' => $arrDetail[$i]['note']
                ]);

                if ($insertdetail == false) {
                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }
            }

            $hitung = $sales->hitungTotal([
                'soid' => $hasilpoid
            ]);

            $sales->updateTotal([
                'grandtotal' => $hitung->grandtotal,
                'subtotal' => $hitung->subtotal,
                'ppn' => $hitung->ppn,
                'soid' => $hasilpoid
            ]);

            $hasilotorisasi = $sales->cekOtorisasi([
                'soid' => $hasilpoid,
                'custid' => $request->input('custid'),
                'transdate' => $request->input('transdate')
            ]);

            $sales->updateJenis([
                'jenis' => $hasilotorisasi,
                'soid' => $hasilpoid
            ]);

            DB::commit();

            return $this->responseSuccess('insert berhasil', 200, ['soid' => $hasilpoid]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function updateAllData(Request $request)
    {
        $sales = new SalesOrderHd();

        $modelDetail = new SalesOrderDt();

        $mssales = new ARMsSales();

        $mscustomer = new ARMsCustomer();

        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $sales::$rulesUpdateAll, $sales::$messagesUpdate);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        }

        $validatorDetail = Validator::make($request->all(), $modelDetail::$rulesInsert, $modelDetail::$messagesInsert);

        if ($validatorDetail->fails()) {
            return $this->responseError($validatorDetail->messages(), 400);
        }

        $cek = $sales->cekSalesorder($request->input('soid'));

        if ($cek == false) {

            return $this->responseError('nomor sales order tidak terdaftar', 400);
        }

        $cek = $sales->cekBolehEdit($request->input('soid'));

        if ($cek == true) {

            return $this->responseError('sudah ada nota ' . $cek->saleid . ' tidak bisa edit', 400);
        }

        $cek = $mscustomer->cekCustomer($request->input('custid'));

        if ($cek == false) {

            return $this->responseError('kode pelanggan tidak terdaftar dalam master', 400);
        }

        $cek = $mssales->cekSales($request->input('salesid'));

        if ($cek == false) {

            return $this->responseError('kode sales tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $insertheader = $sales->updateAllData([
                'soid' => $request->input('soid'),
                'pocust' => $request->input('pocust'),
                'custid' => $request->input('custid'),
                'transdate' => $request->input('transdate'),
                'note' => $request->input('note'),
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'tglkirim' => $request->input('tglkirim'),
                'salesid' => $request->input('salesid'),
                'fob' => $request->input('fob'),
                'fgtax' => $request->input('fgtax'),
                'nilaitax' => $request->input('nilaitax'),
                'currid' => $request->input('currid'),
                'address' => $request->input('address'),
                'attn' => $request->input('attn'),
                'telp' => $request->input('telp'),
                'ship' => $request->input('ship'),
                'svc' => $request->input('svc'),
                'term' => $request->input('term'),
                'termin' => $request->input('termin'),
                'disc' => $request->input('disc') ?? 0,
                'tb' => $request->input('svc') ?? 0
            ]);

            if ($insertheader == false) {
                DB::rollBack();

                return $this->responseError('insert header gagal', 400);
            }

            $deletedetail = $modelDetail->deleteData([
                'soid' => $request->input('soid')
            ]);

            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {
                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'soid' => $request->input('soid'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'urut' => $arrDetail[$i]['urut'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'itemname' => $arrDetail[$i]['itemname'],
                    'modal' => $arrDetail[$i]['modal'],
                    'note' => $arrDetail[$i]['note']
                ]);

                if ($insertdetail == false) {
                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }
            }

            $hitung = $sales->hitungTotal([
                'soid' => $request->input('soid')
            ]);

            $sales->updateTotal([
                'grandtotal' => $hitung->grandtotal,
                'subtotal' => $hitung->subtotal,
                'ppn' => $hitung->ppn,
                'soid' => $request->input('soid')
            ]);

            $hasilotorisasi = $sales->cekOtorisasi([
                'soid' => $request->input('soid'),
                'custid' => $request->input('custid'),
                'transdate' => $request->input('transdate')
            ]);

            $sales->updateJenis([
                'jenis' => $hasilotorisasi,
                'soid' => $request->input('soid')
            ]);

            DB::commit();

            return $this->responseSuccess('update berhasil', 200, ['soid' => $request->input('soid')]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function getListData(Request $request)
    {
        $sales = new SalesOrderHd();
        $salesdt = new SalesOrderDt();

        if ($request->input('soid')) {

            $resultheader = $sales->getdata(
                [
                    'soid' => $request->input('soid')
                ]
            );

            $resultdetail = $salesdt->getdata(
                [
                    'soid' => $request->input('soid')
                ]
            );

            $result = [
                'header' => $resultheader,
                'detail' => $resultdetail
            ];

            return $this->responseData($result);
        } else {
            if ($request->input('oto')) {

                $result = $sales->getListOto();

                $resultPaginated = $this->arrayPaginator($request, $result);

                return $this->responsePagination($resultPaginated);
            } else {
                $result = $sales->getListData(
                    [
                        'dari' => $request->input('dari'),
                        'sampai' => $request->input('sampai'),
                        'custidkeyword' => $request->input('custidkeyword') ?? '',
                        'custnamekeyword' => $request->input('custnamekeyword') ?? '',
                        'salesidkeyword' => $request->input('salesidkeyword') ?? '',
                        'salesnamekeyword' => $request->input('salesnamekeyword') ?? '',
                        'sokeyword' => $request->input('sokeyword') ?? '',
                        'sortby' => $request->input('sortby') ?? 'old'
                    ]
                );

                $resultPaginated = $this->arrayPaginator($request, $result);

                return $this->responsePagination($resultPaginated);
            }
        }
    }




    // public function getListOto(Request $request)
    // {
    //     $sales = new SalesOrderHd();

    //     $result = $sales->getListOto([
    //         'dari' => $request->input('dari'),
    //         'sampai' => $request->input('sampai'),
    //         'custidkeyword' => $request->input('custidkeyword') ?? '',
    //         'custnamekeyword' => $request->input('custnamekeyword') ?? '',
    //         'salesidkeyword' => $request->input('salesidkeyword') ?? '',
    //         'salesnamekeyword' => $request->input('salesnamekeyword') ?? '',
    //         'sokeyword' => $request->input('sokeyword') ?? '',
    //         'sortby' => $request->input('sortby') ?? 'old'
    //     ]);

    //     $resultPaginated = $this->arrayPaginator($request, $result);

    //     return $this->responsePagination($resultPaginated);
    // }

    public function getListSOBlmPO(Request $request)
    {
        $sales = new SalesOrderHd();

        $result = $sales->getListSOBlmPO([
            'transdate' => $request->input('transdate')
        ]);

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function getListBarangSO(Request $request)
    {
        $sales = new SalesOrderDt();

        $result = $sales->getListBarangSO([
            'soid' => $request->input('soid')
        ]);

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function deleteData(Request $request)
    {
        $sales = new SalesOrderHd();

        $cek = $sales->cekSalesorder($request->input('soid'));

        if ($cek == false) {

            return $this->responseError('nomor sales order tidak terdaftar', 400);
        }

        $cek = $sales->cekBolehEdit($request->input('soid'));

        if ($cek == true) {

            return $this->responseError('sudah ada nota ' . $cek->saleid . ' tidak bisa edit', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $sales->deleteData([
                'soid' => $request->input('soid')
            ]);

            if ($deleted) {
                DB::commit();

                return $this->responseSuccess('delete berhasil', 200, ['soid' => $request->input('soid')]);
            } else {
                DB::rollBack();

                return $this->responseError('delete gagal', 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function updateJenis(Request $request)
    {
        $sales = new SalesOrderHd();

        $cekpmargin = $sales->cekMargin($request->input('soid'));

        if ($cekpmargin == true) {

            $cek = $sales->cekOtoLevel(Auth::user()->currentAccessToken()['namauser']);

            if ($cek == false) {

                return $this->responseError('Anda tidak memiliki akses untuk otorisasi SO ini', 400);
            }
        }

        $cek = $sales->cekSalesorder($request->input('soid'));

        if ($cek == false) {

            return $this->responseError('nomor sales order tidak terdaftar', 400);
        }

        DB::beginTransaction();

        try {
            $updated = $sales->updateJenis([
                'soid' => $request->input('soid'),
                'jenis' => $request->input('jenis')
            ]);

            if ($updated) {

                DB::commit();

                return $this->responseSuccess('otorisasi berhasil', 200, ['soid' => $request->input('soid')]);
            } else {
                DB::rollBack();

                return $this->responseError('gagal update', 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }
}
