<?php

namespace App\Http\Controllers\AR\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AR\Activity\PenjualanHd;
use App\Models\AR\Activity\PenjualanDt;
use App\Models\AR\Activity\PenjualanSN;
// use App\Models\AR\Activity\SalesOrderBiaya;
use App\Models\AR\Master\ARMsCustomer;
use App\Models\AR\Master\ARMsSales;
use App\Models\IN\Master\INMsItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $sales = new PenjualanHd();
        $modelDetail = new PenjualanDt();
        $modelBiaya = new PenjualanSN();


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

        $validatorBiaya = Validator::make($request->all(), $modelBiaya::$rulesInsert, $modelBiaya::$messagesInsert);

        if ($validatorBiaya->fails()) {
            return $this->responseError($validatorBiaya->messages(), 400);
        }

        $cek = $mscustomer->cekCustomer($request->input('custid'));

        $custname = $cek->CustName;

        if ($cek == false) {

            return $this->responseError('kode pelanggan tidak terdaftar dalam master', 400);
        }

        $cek = $mssales->cekSales($request->input('salesid'));

        if ($cek == false) {

            return $this->responseError('kode sales tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $hasilpoid = $sales->beforeAutoNumber($request->input('transdate'), $request->input('fgtrans'), $request->input('custid'));
            //$soid = $sales->CariSO($request->input('soid'));
            //dd(var_dump($soid));

            $insertheader = $sales->insertData([
                'saleid' => $hasilpoid,
                'soid' => $request->input('soid'),
                'poid' => $request->input('poid'),
                'transdate' => $request->input('transdate'),
                'custid' => $request->input('custid'),
                'salesid' => $request->input('salesid'),
                'jatuhtempo' => $request->input('term') ?? 30,
                'note' => $request->input('note') ?? '',
                'discamount' => $request->input('discamount') ?? 0,
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'dp' => $request->input('dp') ?? 0,
                'nama' => $request->input('nama') ?? '',
                'fgtax' => $request->input('fgtax') ?? 'T',
                'nilaitax' => $request->input('nilaitax') ?? '',
                'alamat' => $request->input('alamat') ?? '',
                'alamatkirim' => $request->input('alamatkirim') ?? ''
            ]);

            //dd(var_dump($insertheader));

            if ($insertheader == false) {

                DB::rollBack();

                return $this->responseError('insert header gagal', 400);
            }

            $deleteallitem = $sales->deleteAllItem([
                'saleid' => $hasilpoid,
            ]);

            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {

                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $cek = $modelDetail->cekSudahInvoice($hasilpoid, $request->input('soid'), $arrDetail[$i]['itemid'], $arrDetail[$i]['qty']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('jumlah barang (kode :' . $arrDetail[$i]['itemid'] . ') melebihi jumlah SO ', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'saleid' => $hasilpoid,
                    'itemid' => $arrDetail[$i]['itemid'],
                    'itemname' => $arrDetail[$i]['itemname'],
                    'price' => $arrDetail[$i]['price'],
                    'qty' => $arrDetail[$i]['qty'],
                    'titipan' => $arrDetail[$i]['titipan'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'note' => $arrDetail[$i]['note'],
                    'modal' => $arrDetail[$i]['modal']
                ]);

                if ($insertdetail == false) {

                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }

                $insertallitem = $sales->insertAllItem([
                    'saleid' => $hasilpoid,
                    'transdate' => $request->input('transdate'),
                    'warehouseid' => $request->input('warehouseid') ?? '01GU',
                    'itemid' => $arrDetail[$i]['itemid'],
                    'price' => $arrDetail[$i]['price'],
                    'qty' => $arrDetail[$i]['qty'],
                    'custname' => $custname

                ]);

                //dd(var_dump($insertheader));

                if ($insertallitem == false) {

                    DB::rollBack();

                    return $this->responseError('insert allitem gagal', 400);
                }

                $insertalllog = $sales->insertAllLog([
                    'saleid' => $hasilpoid,
                    'salesid' => $request->input('salesid'),
                    'soid' => $request->input('soid'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'custid' => $request->input('custid'),
                    'discount' => $request->input('discount') ?? 0,
                    'dp' => $request->input('dp') ?? 0,
                    'nama' => $request->input('nama') ?? '',
                    'administrasi' => $request->input('administrasi') ?? 0,
                    'transdate' => $request->input('transdate'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'price' => $arrDetail[$i]['price'],
                    'qty' => $arrDetail[$i]['qty']

                ]);

                if ($insertalllog == false) {

                    DB::rollBack();

                    return $this->responseError('insert alllog gagal', 400);
                }

                if (isset($arrDetail[$i]['detailsn'])) {

                    $arrDetailsn = $arrDetail[$i]['detailsn'];

                    if (sizeof($arrDetailsn) <> $arrDetail[$i]['qty']) {

                        DB::rollBack();

                        return $this->responseError('jumlah SN barang (kode :' . $arrDetail[$i]['itemid'] . ') tidak sama dengan jumlah detail', 400);
                    }

                    for ($u = 0; $u < sizeof($arrDetailsn); $u++) {

                        /*$cek = $msitem->cekBarang($arrDetailsn[$u]['packageid']);

                        if ($cek == false) {

                            DB::rollBack();

                            return $this->responseError('kode package tidak terdaftar dalam master', 400);
                        }*/

                        $insertdetail = $modelBiaya->insertData([
                            'snid' => $arrDetailsn[$u]['snid'],
                            'saleid' => $hasilpoid,
                            'itemid' => $arrDetail[$i]['itemid'],
                            'price' => $arrDetail[$i]['price'],
                            'modal' => $arrDetailsn[$u]['modal'],
                            'purchaseid' => $arrDetailsn[$u]['purchaseid'],
                            'upduser' => Auth::user()->currentAccessToken()['namauser']
                        ]);

                        if ($insertdetail == false) {
                            DB::rollBack();

                            return $this->responseError('insert SN gagal', 400);
                        }
                    }
                }
            }

            $hitung = $sales->hitungTotal([
                'saleid' => $hasilpoid
            ]);

            $sales->updateTotal([
                'total' => $hitung->total,
                'pajak' => $hitung->pajak,
                'subtotal' => $hitung->subtotal,
                'titipan' => $hitung->titipan,
                'saleid' => $hasilpoid
            ]);


            DB::commit();

            return $this->responseSuccess('insert berhasil', 200, ['saleid' => $hasilpoid]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function updateAllData(Request $request)
    {
        $sales = new PenjualanHd();
        $modelDetail = new PenjualanDt();
        $modelBiaya = new PenjualanSN();

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

        $validatorBiaya = Validator::make($request->all(), $modelBiaya::$rulesInsert, $modelBiaya::$messagesInsert);

        if ($validatorBiaya->fails()) {
            return $this->responseError($validatorBiaya->messages(), 400);
        }

        $cek = $mscustomer->cekCustomer($request->input('custid'));


        $custname = $cek->CustName;

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
                'saleid' => $request->input('saleid'),
                'soid' => $request->input('soid'),
                'poid' => $request->input('poid'),
                'transdate' => $request->input('transdate'),
                'custid' => $request->input('custid'),
                'salesid' => $request->input('salesid'),
                'term' => $request->input('term') ?? 30,
                'discamount' => $request->input('discamount') ?? 0,
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'kasir' => Auth::user()->currentAccessToken()['namauser'],
                'dp' => $request->input('dp') ?? 0,
                'nama' => $request->input('nama') ?? '',
                'fgtax' => $request->input('fgtax') ?? 'T',
                'nilaitax' => $request->input('nilaitax') ?? '',
                'alamat' => $request->input('alamat') ?? '',
                'alamatkirim' => $request->input('alamatkirim') ?? '',
            ]);

            if ($insertheader == false) {

                DB::rollBack();

                return $this->responseError('insert header gagal', 400);
            }

            $deletedetail = $modelDetail->deleteData([
                'saleid' => $request->input('saleid')
            ]);

            $deletallitem = $sales->deleteAllItem([
                'saleid' => $request->input('saleid')
            ]);

            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {
                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $cek = $modelDetail->cekSudahInvoice($request->input('saleid'), $request->input('soid'), $arrDetail[$i]['itemid'], $arrDetail[$i]['qty']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('jumlah barang (kode :' . $arrDetail[$i]['itemid'] . ') melebihi jumlah SO ', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'saleid' => $request->input('saleid'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'itemname' => $arrDetail[$i]['itemname'],
                    'price' => $arrDetail[$i]['price'],
                    'qty' => $arrDetail[$i]['qty'],
                    'titipan' => $arrDetail[$i]['titipan'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'note' => $arrDetail[$i]['note'],
                    'modal' => $arrDetail[$i]['modal']
                ]);

                if ($insertdetail == false) {

                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }

                $insertallitem = $sales->insertAllItem([
                    'saleid' => $request->input('saleid'),
                    'transdate' => $request->input('transdate'),
                    'warehouseid' => $request->input('warehouseid') ?? '01GU',
                    'itemid' => $arrDetail[$i]['itemid'],
                    'price' => $arrDetail[$i]['price'],
                    'qty' => $arrDetail[$i]['qty'],
                    'custname' => $custname,

                ]);

                //dd(var_dump($insertheader));

                if ($insertallitem == false) {

                    DB::rollBack();

                    return $this->responseError('insert allitem gagal', 400);
                }

                $insertalllog = $sales->insertAllLog([
                    'saleid' => $request->input('saleid'),
                    'salesid' => $request->input('salesid'),
                    'soid' => $request->input('soid'),
                    'transdate' => $request->input('transdate'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'custid' => $request->input('custid'),
                    'discount' => $request->input('discount') ?? 0,
                    'dp' => $request->input('dp') ?? 0,
                    'nama' => $request->input('nama') ?? '',
                    'administrasi' => $request->input('administrasi') ?? 0,
                    'itemid' => $arrDetail[$i]['itemid'],
                    'price' => $arrDetail[$i]['price'],
                    'qty' => $arrDetail[$i]['qty']

                ]);

                if ($insertalllog == false) {

                    DB::rollBack();

                    return $this->responseError('insert alllog gagal', 400);
                }



                if (isset($arrDetail[$i]['detailsn'])) {

                    $arrDetailsn = $arrDetail[$i]['detailsn'];

                    if (sizeof($arrDetailsn) <> $arrDetail[$i]['qty']) {

                        DB::rollBack();

                        return $this->responseError('jumlah SN barang (kode :' . $arrDetail[$i]['itemid'] . ') tidak sama dengan jumlah detail', 400);
                    }

                    for ($u = 0; $u < sizeof($arrDetailsn); $u++) {

                        $insertdetail = $modelBiaya->insertData([
                            'saleid' => $request->input('saleid'),
                            'snid' => $arrDetailsn[$u]['snid'],
                            'itemid' => $arrDetail[$i]['itemid'],
                            'price' => $arrDetail[$i]['price'],
                            'modal' => $arrDetailsn[$u]['modal'],
                            'purchaseid' => $arrDetailsn[$u]['purchaseid'],
                            'upduser' => Auth::user()->currentAccessToken()['namauser']
                        ]);

                        if ($insertdetail == false) {

                            DB::rollBack();

                            return $this->responseError('update SN gagal !', 400);
                        }
                    }
                }
            }

            $hitung = $sales->hitungTotal([
                'saleid' => $request->input('saleid')
            ]);

            $sales->updateTotal([
                'total' => $hitung->total,
                'pajak' => $hitung->pajak,
                'subtotal' => $hitung->subtotal,
                'titipan' => $hitung->titipan,
                'saleid' => $request->input('saleid')
            ]);

            $hitung = $sales->hitungTotal([
                'saleid' => $request->input('saleid')
            ]);



            DB::commit();

            return $this->responseSuccess('update berhasil', 200, ['saleid' => $request->input('saleid')]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function getListData(Request $request)
    {
        $sales = new PenjualanHd();
        $salesdt = new PenjualanDt();

        if ($request->input('saleid')) {

            $resultheader = $sales->getdata(
                [
                    'saleid' => $request->input('saleid')
                ]
            );

            $resultdetail = $salesdt->getdata(
                [
                    'saleid' => $request->input('saleid')
                ]
            );

            $result = [
                'header' => $resultheader,
                'detail' => $resultdetail
            ];

            return $this->responseData($result);
        } /*else {
            if ($request->input('oto')) {

                $result = $sales->getListOto([
                    'keyword' => $request->input('keyword') ?? '',
                    'pocustkeyword' => $request->input('pocustkeyword') ?? '',
                    'custkeyword' => $request->input('custkeyword') ?? '',
                    'saleskeyword' => $request->input('saleskeyword') ?? '',
                    'sortby' => $request->input('sortby') ?? 'dateold'
                ]);

                $resultPaginated = $this->arrayPaginator($request, $result);

                return $this->responsePagination($resultPaginated);
            } */ else {

            $result = $sales->getListData(
                [
                    'dari' => $request->input('dari'),
                    'sampai' => $request->input('sampai'),
                    'keyword' => $request->input('keyword') ?? '',
                    'soidkeyword' => $request->input('soidkeyword') ?? '',
                    'namakeyword' => $request->input('namakeyword') ?? '',
                    'custidkeyword' => $request->input('custidkeyword') ?? '',
                    'custnamekeyword' => $request->input('custnamekeyword') ?? '',
                    'salesidkeyword' => $request->input('salesidkeyword') ?? '',
                    'salesnamekeyword' => $request->input('salesnamekeyword') ?? '',
                    'sortby' => $request->input('sortby') ?? 'dateold'
                ]
            );

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
        //}
    }

    public function deleteData(Request $request)
    {
        $sales = new PenjualanHd();

        $cek = $sales->cekPenjualan($request->input('saleid'));

        if ($cek == false) {

            return $this->responseError('nomor Penjualan tidak terdaftar', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $sales->deleteData([
                'saleid' => $request->input('saleid')
            ]);

            $deletallitem = $sales->deleteAllItem([
                'saleid' => $request->input('saleid')
            ]);

            if ($deleted) {
                DB::commit();

                return $this->responseSuccess('delete berhasil', 200, ['saleid' => $request->input('saleid')]);
            } else {
                DB::rollBack();

                return $this->responseError('delete gagal', 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function cekBayar(Request $request)
    {
        $sales = new PenjualanHd();

        if ($request->input('saleid')) {

            $cekbayar = $sales->cekBayar($request->input('saleid'));

            $totalbayar = $sales->totalBayar($request->input('saleid'));
            $bayar = $totalbayar[0]->total;

            $totalinvoice = $sales->cekPenjualan($request->input('saleid'));
            $invoice = $totalinvoice->TTLPj;

            $sisa = $invoice - $bayar;
            //dd(var_dump($invoice));

            $result = [
                'totalinvoice' => $invoice,
                'totalbayar' => $bayar,
                'sisa' => strval($sisa),
                'data' => $cekbayar
            ];

            // $result = [
            //     'header' => $resultheader
            // ];
            // return $this->responseData($result);
        }

        return $result;

        // $resultPaginated = $this->arrayPaginator($request, $result);

        // return $this->responsePagination($resultPaginated);
    }

    public function cariSO(Request $request)
    {
        $sales = new PenjualanHd();

        $validatorBiaya = Validator::make($request->all(), $sales::$cariso, $sales::$carisomessage);

        if ($validatorBiaya->fails()) {
            return $this->responseError($validatorBiaya->messages(), 400);
        } else {

            if ($request->input('transdate')) {

                $resultheader = $sales->cariSO(
                    [
                        'tanggal' => $request->input('transdate'),
                        'soidkeyword' => $request->input('soidkeyword') ?? '',
                        'custidkeyword' => $request->input('custidkeyword') ?? '',
                        'custnamekeyword' => $request->input('custnamekeyword') ?? '',
                        'salesidkeyword' => $request->input('salesidkeyword') ?? '',
                        'salesnamekeyword' => $request->input('salesnamekeyword') ?? ''
                    ]
                );

                // $result = [
                //     'header' => $resultheader
                // ];
                // return $this->responseData($result);
            }

            $resultPaginated = $this->arrayPaginator($request, $resultheader);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function cariDetail(Request $request)
    {
        $sales = new PenjualanDt();

        //$tanggal = $saleshd->cekSales($request->input('transdate'));

        $validatorBiaya = Validator::make($request->all(), $sales::$itemid, $sales::$itemidmessage);

        if ($validatorBiaya->fails()) {
            return $this->responseError($validatorBiaya->messages(), 400);
        } else {

            if ($request->input('soid')) {

                $resultdetail = $sales->cariDetailBarangBaru(
                    [
                        'soid' => $request->input('soid'),
                        'tanggal' => $request->input('tanggal'),
                        'itemnamekeyword' => $request->input('itemnamekeyword') ?? '',
                        'itemidkeyword' => $request->input('itemidkeyword') ?? ''
                    ]
                );

                // $result = [
                //     'detail' => $resultdetail
                // ];
                // return $this->responseData($result);
            }

            $resultPaginated = $this->arrayPaginator($request, $resultdetail);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function cariSN(Request $request)
    {
        $sales = new PenjualanSN();

        //$tanggal = $saleshd->cekSales($request->input('transdate'));

        $validatorBiaya = Validator::make($request->all(), $sales::$rulesInsert2, $sales::$messagesInsert2);

        if ($validatorBiaya->fails()) {
            return $this->responseError($validatorBiaya->messages(), 400);
        } else {

            $resultdetail = $sales->selectSN(
                [
                    'itemid' => $request->input('itemid'),
                    'suppidkeyword' => $request->input('suppidkeyword') ?? '',
                    'suppnamekeyword' => $request->input('suppnamekeyword') ?? '',
                    'purchaseidkeyword' => $request->input('purchaseidkeyword') ?? ''
                ]
            );


            //return $this->responseData($resultdetail);

            $resultPaginated = $this->arrayPaginator($request, $resultdetail);

            return $this->responsePagination($resultPaginated);
        }
    }
}