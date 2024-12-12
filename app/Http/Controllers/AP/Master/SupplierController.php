<?php

namespace App\Http\Controllers\AP\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AP\Master\APMsSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $supplier = new APMsSupplier();

        $validator = Validator::make($request->all(), $supplier::$rulesInsert, $supplier::$messagesInsert);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $kodesupp = $supplier->beforeAutoNumber($request->input('suppname'));

            DB::beginTransaction();

            try {
                $insert = $supplier->insertData([
                    'suppid' => $kodesupp,
                    'suppname' => $request->input('suppname'),
                    'alamat' => $request->input('alamat'),
                    'kota' => $request->input('kota'),
                    'contact' => $request->input('contact'),
                    'telp' => $request->input('telp'),
                    'fax' => $request->input('fax'),
                    'email' => $request->input('email'),
                    'note' => $request->input('note'),
                    'termin' => $request->input('termin'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);

                if ($insert) {
                    DB::commit();

                    return $this->responseSuccess('insert berhasil', 200, ['suppid' => $kodesupp]);
                } else {
                    DB::rollBack();

                    return $this->responseError('insert gagal', 400);
                }
            } catch (\Exception $e) {
                DB::rollBack();

                return $this->responseError($e->getMessage(), 400);
            }
        }
    }

    public function updateAllData(Request $request)
    {
        $supplier = new APMsSupplier();

        $validator = Validator::make($request->all(), $supplier::$rulesUpdateAll, $supplier::$messagesUpdate);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $cek = $supplier->cekSupplier($request->input('suppid'));

            if ($cek == false) {

                return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
            }

            DB::beginTransaction();

            try {
                $updated = $supplier->updateAllData([
                    'suppid' => $request->input('suppid'),
                    'suppname' => $request->input('suppname'),
                    'alamat' => $request->input('alamat'),
                    'kota' => $request->input('kota'),
                    'contact' => $request->input('contact'),
                    'telp' => $request->input('telp'),
                    'fax' => $request->input('fax'),
                    'email' => $request->input('email'),
                    'note' => $request->input('note'),
                    'termin' => $request->input('termin'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);

                if ($updated) {
                    DB::commit();

                    return $this->responseSuccess('update berhasil', 200, ['suppid' => $request->input('suppid')]);
                } else {
                    DB::rollBack();

                    return $this->responseError('update gagal', 400);
                }
            } catch (\Exception $e) {
                DB::rollBack();

                return $this->responseError($e->getMessage(), 400);
            }
        }
    }

    public function getListData(Request $request)
    {
        $supplier = new APMsSupplier();

        if ($request->input('suppid')) {

            $result = $supplier->getdata(
                [
                    'suppid' => $request->input('suppid')
                ]
            );

            return $this->responseData($result);
        } else {
            $result = $supplier->getListData([
                'suppidkeyword' => $request->input('suppidkeyword') ?? '',
                'suppnamekeyword' => $request->input('suppnamekeyword') ?? '',
                'sortby' => $request->input('sortby') ?? 'suppid',
            ]);

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function deleteData(Request $request)
    {
        $supplier = new APMsSupplier();

        $cek = $supplier->cekSupplier($request->input('suppid'));

        if ($cek == false) {

            return $this->responseError('kode supplier tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $supplier->deleteData([
                'suppid' => $request->input('suppid')
            ]);

            if ($deleted) {

                DB::commit();

                return $this->responseSuccess('delete berhasil', 200, ['suppid' => $request->input('suppid')]);
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
