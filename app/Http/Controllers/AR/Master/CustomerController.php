<?php

namespace App\Http\Controllers\AR\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AR\Master\ARMsCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $customer = new ARMsCustomer();

        $validator = Validator::make($request->all(), $customer::$rulesInsert, $customer::$messagesInsert);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $kodecust = $customer->beforeAutoNumber($request->input('custname'), $request->input('tipe'));

            DB::beginTransaction();

            try {
                $insert = $customer->insertData([
                    'custid' => $kodecust,
                    'custname' => $request->input('custname'),
                    'alamat' => $request->input('alamat') ?? '',
                    'kota' => $request->input('kota'),
                    'telp' => $request->input('telp'),
                    'email' => $request->input('email'),
                    'npwp' => $request->input('npwp') ?? '',
                    'note' => $request->input('note') ?? '',
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'limitpiutang' => $request->input('limitpiutang'),
                    'termin' => $request->input('termin'),
                    'term' => $request->input('term'),
                    'cp' => $request->input('cp') ?? '',
                    'alamatnpwp' => $request->input('alamatnpwp') ?? '',
                    'salesid' => $request->input('salesid'),
                    'tipe' => $request->input('tipe') ?? 'D'
                ]);

                if ($insert) {
                    DB::commit();

                    return $this->responseSuccess('insert berhasil', 200, ['custid' => $kodecust]);
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

    public function getListData(Request $request)
    {
        $customer = new ARMsCustomer();

        if ($request->input('custid')) {

            $cek = $customer->cekCustomer($request->input('custid'));

            if ($cek == false) {

                return $this->responseError('kode customer tidak terdaftar dalam master', 400);
            }

            $result = $customer->getdata(
                [
                    'custid' => $request->input('custid')
                ]
            );

            return $this->responseData($result);
        } else {
            $result = $customer->getListData([
                'custidkeyword' => $request->input('custidkeyword') ?? '',
                'custnamekeyword' => $request->input('custnamekeyword') ?? '',
                'sortby' => $request->input('sortby') ?? 'custid',
            ]);

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function updateAllData(Request $request)
    {
        $customer = new ARMsCustomer();

        $validator = Validator::make($request->all(), $customer::$rulesUpdateAll, $customer::$messagesUpdate);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $cek = $customer->cekCustomer($request->input('custid'));

            if ($cek == false) {

                return $this->responseError('kode customer tidak terdaftar dalam master', 400);
            }


            DB::beginTransaction();

            try {
                $updated = $customer->updateAllData([
                    'custid' => $request->input('custid'),
                    'custname' => $request->input('custname'),
                    'alamat' => $request->input('alamat'),
                    'kota' => $request->input('kota'),
                    'telp' => $request->input('telp'),
                    'email' => $request->input('email'),
                    'npwp' => $request->input('npwp'),
                    'note' => $request->input('note'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'limitpiutang' => $request->input('limitpiutang'),
                    'termin' => $request->input('termin'),
                    'term' => $request->input('term'),
                    'cp' => $request->input('cp'),
                    'alamatnpwp' => $request->input('alamatnpwp'),
                    'salesid' => $request->input('salesid'),
                    'tipe' => $request->input('tipe')
                ]);

                if ($updated) {

                    DB::commit();

                    return $this->responseSuccess('update berhasil', 200, ['custid' => $request->input('custid')]);
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

    public function deleteData(Request $request)
    {
        $customer = new ARMsCustomer();

        $cek = $customer->cekCustomer($request->input('custid'));

        if ($cek == false) {

            return $this->responseError('kode customer tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $customer->deleteData([
                'custid' => $request->input('custid')
            ]);

            if ($deleted) {
                DB::commit();

                return $this->responseSuccess('delete berhasil', 200, ['custid' => $request->input('custid')]);
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
