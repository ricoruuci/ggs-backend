<?php

namespace App\Http\Controllers\AP\Activity;

use App\Http\Controllers\Controller;
use App\Models\AP\Activity\PembelianDt;
use App\Models\AP\Activity\PembelianDtSN;
use App\Models\AP\Activity\PembelianHd;
use App\Models\AP\Master\APMsSupplier;
use App\Models\IN\Master\INMsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $pembelian = new PembelianHd();
        $modelDetail = new PembelianDt();
        $modelSn = new PembelianDtSN();

        $mssupplier = new APMsSupplier();
        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $pembelian::$rulesInsert, $pembelian::$messagesInsert);

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

        $cek = $mssupplier->cekSupplier($request->input('suppid'));

        if ($cek == false) {

            return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            // $hasilpurchaseid = $pembelian->beforeAutoNumber($request->input('transdate'), $request->input('fgtax'));

            //dd(var_dump($hasilpurchaseid));
            $samsset = DB::selectOne('SELECT * from samsset');
            $rekeningp = $samsset->DGRPLL;
            $rekeningu = $samsset->DGRPb;
            $rekeningk = $samsset->DRPb;
            $rekpersediaan = $samsset->sPersediaan;
            $rekhpp = $samsset->sHPP;
            $fgtax = $samsset->FgTax;
            $nilaitax = $samsset->NilaiTax;

            $insertheader = $pembelian->insertData([
                'purchaseid' => $params['purchaseid'],
                'konsinyasiid' => $request->input('konsinyasiid'),
                'transdate' => $request->input('transdate'),
                'suppid' => $request->input('suppid'),
                'nofps' => $request->input('nofps') ?? '',
                'fgtax' => $request->input('fgtax') ?? $fgtax,
                'ppn' => $request->input('nilaitax') ?? $nilaitax,
                'jatuhtempo' => $request->input('jatuhtempo') ?? '',
                'note' => $request->input('note') ?? '',
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'npwp' => $request->input('npwp') ?? '',
                'rekeningp' => $request->input('rekeningp') ?? $rekeningp,
                'rekeningu' => $request->input('rekeningu') ?? $rekeningu,
                'rekeningk' => $request->input('rekeningk') ?? $rekeningk,
                'rekpersediaan' => $request->input('rekpersediaan') ?? $rekpersediaan,
                'rekhpp' => $request->input('rekhpp') ?? $rekhpp,

            ]);


            //dd($insertheader);

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

                $cek = $modelDetail->cekSudahBeli($hasilpurchaseid, $request->input('konsinyasiid'), $arrDetail[$i]['itemid'], $arrDetail[$i]['qty']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('jumlah barang (kode: ' . $arrDetail[$i]['itemid'] . ') melebihi jumlah Terima', 400);
                }
                //dd(var_dump($konsinyasiid));
                $insertdetail = $modelDetail->insertData([
                    'purchaseid' => $hasilpurchaseid,
                    'suppid' => $request->input('suppid'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'qty' => $arrDetail[$i]['qty'] ?? 1,
                    'price' => $arrDetail[$i]['price'] ?? 0,
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);


                if ($insertdetail == false) {
                    DB::rollBack();

                    return $this->responseError('insert detail gagal', 400);
                }

                if (isset($arrDetail[$i]['detailsn'])) {

                    $arrDetailSn = $arrDetail[$i]['detailsn'];

                    if (sizeof($arrDetailSn) <> $arrDetail[$i]['qty']) {
                        DB::rollBack();

                        return $this->responseError('jumlah SN tidak sama dengan jumlah barang (kode: ' . $arrDetail[$i]['itemid'] . ')', 400);
                    }


                    for ($u = 0; $u < sizeof($arrDetailSn); $u++) {

                        /*$cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                        if ($cek == false) {

                            DB::rollBack();

                            return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                        }*/


                        $insertdetail = $modelSn->insertData([
                            'purchaseid' => $hasilpurchaseid,
                            'suppid' =>  $request->input('suppid'),
                            'itemid' => $arrDetail[$i]['itemid'],
                            'snid' => $arrDetailSn[$u]['snid'],
                            'price' => $arrDetail[$i]['price'],
                            'upduser' => Auth::user()->currentAccessToken()['namauser']
                        ]);

                        if ($insertdetail == false) {

                            DB::rollBack();

                            return $this->responseError('insert detail sn gagal', 400);
                        }
                    }
                }
            }

            $hitung = $pembelian->hitungTotal([
                'purchaseid' => $request->input('purchaseid'),
                'suppid' => $request->input('suppid')
            ]);

            $pembelian->updateTotal([
                'total' => $hitung->total,
                'ppn' => $hitung->ppn,
                'purchaseid' => $request->input('purchaseid')
            ]);



            //dd(var_dump($hitung));
            DB::commit();

            return $this->responseSuccess('insert berhasil', 200, ['purchaseid' => $hasilpurchaseid]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    /*public function generateSN(Request $request)
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
    }*/

    public function updateAllData(Request $request)
    {
        $pembelian = new PembelianHd();
        $modelDetail = new PembelianDt();
        $modelSn = new PembelianDtSN();

        $mssupplier = new APMsSupplier();
        $msitem = new INMsItem();

        $validator = Validator::make($request->all(), $pembelian::$rulesUpdateAll, $pembelian::$messagesUpdate);

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

        $cek = $pembelian->cekInvoice($request->input('purchaseid'));

        if ($cek == false) {

            return $this->responseError('nota pembelian tidak terdaftar', 400);
        }

        $cek = $mssupplier->cekSupplier($request->input('suppid'));

        if ($cek == false) {

            return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        $samsset = DB::selectOne('SELECT * from samsset');
        $rekeningp = $samsset->DGRPLL;
        $rekeningu = $samsset->DGRPb;
        $rekeningk = $samsset->DRPb;
        $rekpersediaan = $samsset->sPersediaan;
        $rekhpp = $samsset->sHPP;
        $fgtax = $samsset->FgTax;
        $nilaitax = $samsset->NilaiTax;

        try {
            $insertheader = $pembelian->updateAllData([
                'purchaseid' => $params['purchaseid'],
                'konsinyasiid' => $request->input('konsinyasiid'),
                'transdate' => $request->input('transdate'),
                'suppid' => $request->input('suppid'),
                'nofps' => $request->input('nofps') ?? '',
                'fgtax' => $request->input('fgtax') ?? $fgtax,
                'ppn' => $request->input('nilaitax') ?? $nilaitax,
                'jatuhtempo' => $request->input('jatuhtempo') ?? '',
                'note' => $request->input('note') ?? '',
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'npwp' => $request->input('npwp') ?? '',
                'rekeningp' => $request->input('rekeningp') ?? $rekeningp,
                'rekeningu' => $request->input('rekeningu') ?? $rekeningu,
                'rekeningk' => $request->input('rekeningk') ?? $rekeningk,
                'rekpersediaan' => $request->input('rekpersediaan') ?? $rekpersediaan,
                'rekhpp' => $request->input('rekhpp') ?? $rekhpp
            ]);

            if ($insertheader == false) {

                DB::rollBack();

                return $this->responseError('update header gagal', 400);
            }

            $deletedetail = $modelDetail->deleteData([
                'purchaseid' => $request->input('purchaseid')
            ]);

            // if ($deletedetail == false) {

            //     DB::rollBack();

            //     return $this->responseError('something went wrong', 400);
            // }

            $arrDetail = $request->input('detail');

            for ($i = 0; $i < sizeof($arrDetail); $i++) {
                $cek = $msitem->cekBarang($arrDetail[$i]['itemid']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('kode barang tidak terdaftar dalam master', 400);
                }

                $cek = $modelDetail->cekSudahBeli($request->input('purchaseid'), $request->input('konsinyasiid'), $arrDetail[$i]['itemid'], $arrDetail[$i]['qty']);

                if ($cek == false) {

                    DB::rollBack();

                    return $this->responseError('jumlah barang (kode: ' . $arrDetail[$i]['itemid'] . ') melebihi jumlah Terima', 400);
                }

                $insertdetail = $modelDetail->insertData([
                    'purchaseid' => $request->input('purchaseid'),
                    'suppid' => $request->input('suppid'),
                    'itemid' => $arrDetail[$i]['itemid'],
                    'qty' => $arrDetail[$i]['qty'],
                    'price' => $arrDetail[$i]['price'],
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);

                if ($insertdetail == false) {

                    DB::rollBack();

                    return $this->responseError('update detail gagal', 400);
                }

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

                            return $this->responseError('kode item tidak terdaftar dalam master', 400);
                        }

                        $insertdetail = $modelSn->insertData([
                            'purchaseid' => $request->input('purchaseid'),
                            'suppid' => $request->input('suppid'),
                            'itemid' => $arrDetail[$i]['itemid'],
                            'snid' => $arrDetailSn[$u]['snid'],
                            'price' => $arrDetail[$i]['price'],
                            'upduser' => Auth::user()->currentAccessToken()['namauser']
                        ]);

                        if ($insertdetail == false) {
                            DB::rollBack();

                            return $this->responseError('update detail sn gagal', 400);
                        }
                    }
                }
            }

            $hitung = $pembelian->hitungTotal([
                'purchaseid' => $request->input('purchaseid'),
                'suppid' => $request->input('suppid')
            ]);

            $pembelian->updateTotal([
                'total' => $hitung->total,
                'ppn' => $hitung->ppn,
                'purchaseid' => $request->input('purchaseid')
            ]);

            DB::commit();

            return $this->responseSuccess('update berhasil', 200, ['purchaseid' => $request->input('purchaseid')]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function getListData(Request $request)
    {
        $pembelian = new PembelianHd();
        $pembeliandt = new PembelianDt();
        //$konsinyasidtsn = new KonsinyasiDtSN();

        if ($request->input('purchaseid')) {

            $resultheader = $pembelian->getdata(
                [
                    'purchaseid' => $request->input('purchaseid')
                ]
            );

            $resultdetail = $pembeliandt->getdata(
                [
                    'purchaseid' => $request->input('purchaseid')
                ]
            );

            $result = [
                'header' => $resultheader,
                'detail' => $resultdetail
            ];

            return $this->responseData($result);
        } else {
            $result = $pembelian->getListData(
                [
                    'dari' => $request->input('dari'),
                    'sampai' => $request->input('sampai'),
                    'purchaseidkeyword' => $request->input('purchaseidkeyword') ?? '',
                    'konsinyasiidkeyword' => $request->input('konsinyasiidkeyword') ?? '',
                    'suppidkeyword' => $request->input('suppidkeyword') ?? '',
                    'suppnamekeyword' => $request->input('suppnamekeyword') ?? '',
                    'custidkeyword' => $request->input('custidkeyword') ?? '',
                    'custnamekeyword' => $request->input('custnamekeyword') ?? '',
                    'poidkeyword' => $request->input('poidkeyword') ?? '',
                    'sortby' => $request->input('sortby') ?? 'dateold'
                ]
            );

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function cariPenerimaan(Request $request)
    {
        $pembelian = new PembelianHd();

        $result = $pembelian->cariPenerimaan(
            [
                'transdate' => $request->input('transdate') ?? '',
                'konsinyasiidkeyword' => $request->input('konsinyasiidkeyword') ?? '',
                'suppnamekeyword' => $request->input('suppnamekeyword') ?? '',
                'suppidkeyword' => $request->input('suppidkeyword') ?? '',
                'sortby' => $request->input('sortby') ?? 'dateold'
            ]
        );

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function cariBarang(Request $request)
    {
        $pembelian = new PembelianDt();

        $result = $pembelian->cariBarang(
            [
                'konsinyasiid' => $request->input('konsinyasiid'),
                'purchaseid' => $request->input('purchaseid'),
                'itemidkeyword' => $request->input('itemidkeyword') ?? '',
                'itemnamekeyword' => $request->input('itemnamekeyword') ?? '',
            ]
        );

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function deleteData(Request $request)
    {
        $purchaseid = new PembelianHd();

        $cek = $purchaseid->cekInvoice($request->input('purchaseid'));

        if ($cek == false) {

            return $this->responseError('nota pembelian tidak terdaftar', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $purchaseid->deleteData([
                'purchaseid' => $request->input('purchaseid')
            ]);

            if ($deleted) {
                DB::commit();

                return $this->responseSuccess('hapus berhasil', 200, ['purchaseid' => $request->input('purchaseid')]);
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