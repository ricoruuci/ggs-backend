<?php

namespace App\Http\Controllers\AP\Activity;

use App\Http\Controllers\Controller;
use App\Models\AP\Activity\KonsinyasiDt;
use App\Models\AP\Activity\KonsinyasiDtSN;
use App\Models\AP\Activity\KonsinyasiHd;
use App\Models\AP\Master\APMsSupplier;
use App\Models\AR\Master\ARMsCustomer;
use App\Models\IN\Master\INMsItem;
use App\Models\IN\Master\INMsWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class KonsinyasiController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $konsinyasi = new KonsinyasiHd();
        $modelDetail = new KonsinyasiDt();
        $modelSn = new KonsinyasiDtSN();


        $mswarehouse = new INMsWarehouse();
        $mscustomer = new APMsSupplier();
        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $konsinyasi::$rulesInsert, $konsinyasi::$messagesInsert);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        }

        $validatorDetail = Validator::make($request->all(), $modelDetail::$rulesInsert, $modelDetail::$messagesInsert);

        if ($validatorDetail->fails()) {
            return $this->responseError($validatorDetail->messages(), 400);
        }

        $validatorSn = Validator::make($request->all(), $modelSn::$rulesInsert, $modelSn::$messagesInsert);

        if ($validatorSn->fails()) {
            return $this->responseError($validatorSn->messages(), 400);
        }

        $cek = $mscustomer->cekSupplier($request->input('suppid'));
        $suppname = $cek->SuppName;

        if ($cek == false) {

            return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
        }

        $cek = $mswarehouse->cekWarehouse($request->input('warehouseid'));

        if ($cek == false) {

            return $this->responseError('kode gudang tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $hasilkonsinyasiid = $konsinyasi->beforeAutoNumber($request->input('transdate'));

            //dd(var_dump($hasilkonsinyasiid));

            $insertheader = $konsinyasi->insertData([
                'grnid' => $hasilkonsinyasiid,
                'transdate' => $request->input('transdate'),
                'poid' => $request->input('poid'),
                'suppid' => $request->input('suppid'),
                'note' => $request->input('note') ?? '',
                'warehouseid' => $request->input('warehouseid') ?? '01GU',
                // 'nomordo' => $request->input('nomordo') ?? '',
                'upduser' => Auth::user()->currentAccessToken()['namauser']
            ]);

            if ($insertheader == false) {


                DB::rollBack();


                return $this->responseError('insert header gagal', 400);
            }

            $deleteAllItem = $konsinyasi->deleteAllItem([
                'grnid' => $hasilkonsinyasiid
            ]);


            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {

                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $cek = $modelDetail->cekSudahTerima($hasilkonsinyasiid, $request->input('poid'), $arrDetail[$i]['itemid'], $arrDetail[$i]['qty']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('jumlah barang (kode: ' . $arrDetail[$i]['itemid'] . ') melebihi jumlah PO', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'grnid' => $hasilkonsinyasiid,
                    'itemid' => $arrDetail[$i]['itemid'],
                    'partno' => $arrDetail[$i]['partno'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);



                if ($insertdetail == false) {



                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }


                $insertallitem = $konsinyasi->insertAllItem([
                    'grnid' => $hasilkonsinyasiid,
                    'itemid' => $arrDetail[$i]['itemid'],
                    'partno' => $arrDetail[$i]['partno'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'suppname' => $suppname,
                    'transdate' => $request->input('transdate'),
                    'warehouseid' => $request->input('warehouseid') ?? '01GU'


                ]);

                if (isset($arrDetail[$i]['detailsn'])) {

                    $arrDetailSn = $arrDetail[$i]['detailsn'];

                    if (sizeof($arrDetailSn) <> $arrDetail[$i]['qty']) {
                        DB::rollBack();

                        return $this->responseError('jumlah SN tidak sama dengan jumlah barang (kode: ' . $arrDetail[$i]['itemid'] . ')', 400);
                    }


                    for ($u = 0; $u < sizeof($arrDetailSn); $u++) {

                        $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                        if ($cek == false) {

                            DB::rollBack();

                            return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                        }


                        $insertdetail = $modelSn->insertData([
                            'grnid' => $hasilkonsinyasiid,
                            'itemid' => $arrDetail[$i]['itemid'],
                            'snid' => $arrDetailSn[$u]['snid'],
                            'fgjual' => $arrDetailSn[$u]['fgjual'] ?? 'T',
                            'upduser' => Auth::user()->currentAccessToken()['namauser']
                        ]);

                        if ($insertdetail == false) {

                            DB::rollBack();

                            return $this->responseError('insert detail sn gagal', 400);
                        }
                    }
                }
            }



            //dd(var_dump($hitung));
            DB::commit();

            return $this->responseSuccess('insert berhasil', 200, ['grnid' => $hasilkonsinyasiid]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function generateSN(Request $request)
    {
        // Ambil input dari request
        $grnid = $request->input('grnid');
        $itemid = $request->input('itemid');
        $qty = $request->input('qty');

        // Buat instance dari model KonsinyasiDtSN
        $modelSn = new KonsinyasiDtSN();

        // Panggil method autoGenerateSN di model untuk generate serial numbers
        $generatedSNs = $modelSn->autoGenerateSN($grnid, $itemid, $qty);

        $result = [];
        foreach ($generatedSNs as $snid) {
            $autosn[] =  [
                'snid' => $snid
            ];
        }
        $result = ['data' => $autosn];

        // Return hasil SN dalam format yang diinginkan
        return response()->json($result);
    }

    public function updateAllData(Request $request)
    {
        $konsinyasi = new konsinyasiHd();
        $modelDetail = new konsinyasiDt();
        $modelSn = new KonsinyasiDtSN();

        $mswarehouse = new INMsWarehouse();
        $mscustomer = new APMsSupplier();
        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $konsinyasi::$rulesUpdateAll, $konsinyasi::$messagesUpdate);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        }

        $validatorDetail = Validator::make($request->all(), $modelDetail::$rulesInsert, $modelDetail::$messagesInsert);

        if ($validatorDetail->fails()) {
            return $this->responseError($validatorDetail->messages(), 400);
        }

        $v = Validator::make($request->all(), $modelSn::$rulesInsert, $modelSn::$messagesInsert);

        if ($v->fails()) {
            return $this->responseError($v->messages(), 400);
        }

        $cek = $konsinyasi->cekkonsinyasi($request->input('grnid'));

        if ($cek == false) {

            return $this->responseError('nomor penerimaan tidak terdaftar', 400);
        }

        $cek = $mscustomer->cekSupplier($request->input('suppid'));

        $suppname = $cek->SuppName;

        if ($cek == false) {

            return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
        }

        $cek = $mswarehouse->cekWarehouse($request->input('warehouseid'));

        if ($cek == false) {

            return $this->responseError('kode gudang tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $insertheader = $konsinyasi->updateAllData([
                'grnid' => $request->input('grnid'),
                'transdate' => $request->input('transdate'),
                'poid' => $request->input('poid'),
                'suppid' => $request->input('suppid'),
                'note' => $request->input('note') ?? '',
                'warehouseid' => $request->input('warehouseid') ?? '01GU',
                // 'nomordo' => $request->input('nomordo') ?? '',
                'upduser' => Auth::user()->currentAccessToken()['namauser']
            ]);

            if ($insertheader == false) {

                DB::rollBack();

                return $this->responseError('update header gagal', 400);
            }

            $deletedetail = $modelDetail->deleteData([
                'grnid' => $request->input('grnid')
            ]);

            // if ($deletedetail == false) {

            //     DB::rollBack();

            //     return $this->responseError('something went wrong', 400);
            // }
            $deleteAllItem = $konsinyasi->deleteAllItem([
                'grnid' => $request->input('grnid')
            ]);

            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {
                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $cek = $modelDetail->cekSudahTerima($request->input('grnid'), $request->input('poid'), $arrDetail[$i]['itemid'], $arrDetail[$i]['qty']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('jumlah barang (kode: ' . $arrDetail[$i]['itemid'] . ') melebihi jumlah PO', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'grnid' => $request->input('grnid'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'partno' => $arrDetail[$i]['partno'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);



                if ($insertdetail == false) {

                    DB::rollBack();

                    return $this->responseError('update detail gagal', 400);
                }


                $insertallitem = $konsinyasi->insertAllItem([
                    'grnid' => $request->input('grnid'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'partno' => $arrDetail[$i]['partno'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'suppname' => $suppname,
                    'transdate' => $request->input('transdate'),
                    'warehouseid' => $request->input('warehouseid') ?? '01GU'


                ]);

                if (isset($arrDetail[$i]['detailsn'])) {

                    $arrDetailSn = $arrDetail[$i]['detailsn'];

                    if (sizeof($arrDetailSn) <> $arrDetail[$i]['qty']) {
                        DB::rollBack();

                        return $this->responseError('jumlah SN tidak sama dengan jumlah barang (kode: ' . $arrDetail[$i]['itemid'] . ')', 400);
                    }

                    for ($u = 0; $u < sizeof($arrDetailSn); $u++) {


                        $insertdetail = $modelSn->insertData([
                            'grnid' => $request->input('grnid'),
                            'itemid' => $arrDetail[$i]['itemid'],
                            'snid' => $arrDetailSn[$u]['snid'],
                            'fgjual' => $arrDetailSn[$u]['fgjual'] ?? 'T',
                            'upduser' => Auth::user()->currentAccessToken()['namauser']
                        ]);

                        if ($insertdetail == false) {
                            DB::rollBack();

                            return $this->responseError('update detail sn gagal', 400);
                        }
                    }
                }
            }

            DB::commit();

            return $this->responseSuccess('update berhasil', 200, ['grnid' => $request->input('grnid')]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function getListData(Request $request)
    {
        $konsinyasi = new konsinyasiHd();
        $konsinyasidt = new konsinyasiDt();
        //$konsinyasidtsn = new KonsinyasiDtSN();

        if ($request->input('grnid')) {

            $resultheader = $konsinyasi->getdata(
                [
                    'grnid' => $request->input('grnid')
                ]
            );

            $resultdetail = $konsinyasidt->getdata(
                [
                    'grnid' => $request->input('grnid')
                ]
            );

            $result = [
                'header' => $resultheader,
                'detail' => $resultdetail
            ];

            return $this->responseData($result);
        } else {
            $result = $konsinyasi->getListData(
                [
                    'dari' => $request->input('dari'),
                    'sampai' => $request->input('sampai'),
                    'grnkeyword' => $request->input('grnkeyword') ?? '',
                    'sokeyword' => $request->input('sokeyword') ?? '',
                    'pokeyword' => $request->input('pokeyword') ?? '',
                    'suppkeyword' => $request->input('suppkeyword') ?? '',
                    'suppnamekeyword' => $request->input('suppnamekeyword') ?? '',
                    'custkeyword' => $request->input('custkeyword') ?? '',
                    'custnamekeyword' => $request->input('custnamekeyword') ?? '',
                    'sortby' => $request->input('sortby') ?? 'dateold'
                ]
            );

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function getListPO(Request $request)
    {
        $konsinyasi = new konsinyasiHd();

        $result = $konsinyasi->getListPO(
            [
                'transdate' => $request->input('transdate') ?? '',
                'pokeyword' => $request->input('pokeyword') ?? '',
                'suppnamekeyword' => $request->input('suppnamekeyword') ?? '',
                'suppkeyword' => $request->input('suppkeyword') ?? '',
                'sortby' => $request->input('sortby') ?? 'dateold'
            ]
        );

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function getListPODt(Request $request)
    {
        $konsinyasi = new KonsinyasiDt();

        $result = $konsinyasi->getListPODt(
            [
                'poid' => $request->input('poid')
            ]
        );

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function deleteData(Request $request)
    {
        $grnid = new KonsinyasiHd();

        $cek = $grnid->cekKonsinyasi($request->input('grnid'));

        if ($cek == false) {

            return $this->responseError('nomor penerimaan tidak terdaftar', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $grnid->deleteData([
                'grnid' => $request->input('grnid')
            ]);

            $deleteAllItem = $grnid->deleteAllItem([
                'grnid' => $request->input('grnid')
            ]);

            if ($deleted) {
                DB::commit();

                return $this->responseSuccess('hapus berhasil', 200, ['grnid' => $request->input('grnid')]);
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