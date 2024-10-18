<?php

namespace App\Http\Controllers\AR\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AR\Master\ARMsSales;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function getListData(Request $request)
    {
        $sales = new ARMsSales();

        if ($request->input('salesid')) {

            $cek = $sales->cekSales($request->input('salesid'));

            if ($cek == false) {

                return $this->responseError('kode sales tidak terdaftar dalam master', 400);
            }

            $result = $sales->getdata(
                [
                    'salesid' => $request->input('salesid')
                ]
            );

            return $this->responseData($result);
        } else {
            $result = $sales->getListData([
                'salesidkeyword' => $request->input('salesidkeyword') ?? '',
                'salesnamekeyword' => $request->input('salesnamekeyword') ?? '',
                'active' => $request->input('active') ?? 'all',
                'sortby' => $request->input('sortby') ?? 'salesid'
            ]);

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function insertData(Request $request)
    {

        $sales = new ARMsSales();

        $validator = Validator::make($request->all(), $sales::$rulesInsert, $sales::$messagesInsert);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $kodecust = $sales->beforeAutoNumber($request->input('salesname'));

            DB::beginTransaction();

            try {
                $insert = $sales->insertData([
                    'salesid' => $kodecust,
                    'salesname' => $request->input('salesname'),
                    'address' => $request->input('address'),
                    'hp' => $request->input('hp'),
                    'telp' => $request->input('telp'),
                    'email' => $request->input('email'),
                    'note' => $request->input('note'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'jabatan' => $request->input('jabatan'),
                    'termin' => $request->input('termin'),
                    'term' => $request->input('term'),
                    'fgactive' => $request->input('fgactive'),
                    'tglgabung' => $request->input('tglgabung'),
                    'tomzet' => $request->input('tomzet'),
                    'kom1' => $request->input('kom1'),
                    'kom2' => $request->input('kom2'),
                    'kom3' => $request->input('kom3'),
                    'kom4' => $request->input('kom4')
                ]);

                if ($insert) {
                    DB::commit();

                    return $this->responseSuccess('insert berhasil', 200, ['salesid' => $kodecust]);
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
        $customer = new ARMsSales();

        $validator = Validator::make($request->all(), $customer::$rulesUpdateAll, $customer::$messagesUpdate);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $cek = $customer->cekSales($request->input('salesid'));

            if ($cek == false) {

                return $this->responseError('kode sales tidak terdaftar dalam master', 400);
            }


            DB::beginTransaction();

            try {
                $updated = $customer->updateAllData([
                    'salesid' => $request->input('salesid'),
                    'salesname' => $request->input('salesname'),
                    'address' => $request->input('address'),
                    'hp' => $request->input('hp'),
                    'telp' => $request->input('telp'),
                    'email' => $request->input('email'),
                    'note' => $request->input('note'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'jabatan' => $request->input('jabatan'),
                    'termin' => $request->input('termin'),
                    'fgactive' => $request->input('fgactive'),
                    'tglgabung' => $request->input('tglgabung'),
                    'tomzet' => $request->input('tomzet'),
                    'kom1' => $request->input('kom1'),
                    'kom2' => $request->input('kom2'),
                    'kom3' => $request->input('kom3'),
                    'kom4' => $request->input('kom4')
                ]);

                if ($updated) {

                    DB::commit();

                    return $this->responseSuccess('update berhasil', 200, ['salesid' => $request->input('salesid')]);
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
        $customer = new ARMsSales();

        $cek = $customer->cekSales($request->input('salesid'));

        if ($cek == false) {

            return $this->responseError('kode sales tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $customer->deleteData([
                'salesid' => $request->input('salesid')
            ]);

            if ($deleted) {
                DB::commit();

                return $this->responseSuccess('delete berhasil', 200, ['salesid' => $request->input('salesid')]);
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