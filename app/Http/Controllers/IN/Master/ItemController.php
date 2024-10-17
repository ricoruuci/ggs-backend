<?php

namespace App\Http\Controllers\IN\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IN\Master\INMsItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $item = new INMsItem();

        $validator = Validator::make($request->all(), $item::$rulesInsert, $item::$messagesInsert);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $kodeitem = $item->beforeAutoNumber($request->input('groupid'), $request->input('productid'));

            DB::beginTransaction();

            try {
                $insert = $item->insertData([
                    'itemid' => $kodeitem,
                    'itemname' => $request->input('itemname'),
                    'productid' => $request->input('productid'),
                    'groupid' => $request->input('groupid'),
                    'partno' => $request->input('partno'),
                    'satuan' => $request->input('satuan'),
                    'note' => $request->input('note'),
                    'userprice' => $request->input('userprice'),
                    'dealerprice' => $request->input('dealerprice'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);

                if ($insert) {
                    DB::commit();

                    return $this->responseSuccess('insert berhasil', 200, ['itemid' => $kodeitem]);
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
        $supplier = new INMsItem();

        if ($request->input('itemid')) {

            $result = $supplier->getdata(
                [
                    'itemid' => $request->input('itemid')
                ]
            );

            return $this->responseData($result);
        } else {
            $result = $supplier->getListData([
                'itemidkeyword' => $request->input('itemidkeyword') ?? '',
                'itemnamekeyword' => $request->input('itemnamekeyword') ?? '',
                'sortby' => $request->input('sortby') ?? 'suppid',
            ]);

            $resultPaginated = $this->arrayPaginator($request, $result);

            return $this->responsePagination($resultPaginated);
        }
    }

    public function updateAllData(Request $request)
    {
        $item = new INMsItem();

        $validator = Validator::make($request->all(), $item::$rulesUpdateAll, $item::$messagesUpdate);

        if ($validator->fails()) {
            return $this->responseError($validator->messages(), 400);
        } else {

            $cek = $item->cekBarang($request->input('itemid'));

            if ($cek == false) {

                return $this->responseError('kode item tidak terdaftar dalam master', 400);
            }

            DB::beginTransaction();

            try {
                $updated = $item->updateAllData([
                    'itemid' => $request->input('itemid'),
                    'itemname' => $request->input('itemname'),
                    'partno' => $request->input('partno'),
                    'note' => $request->input('note'),
                    'userprice' => $request->input('userprice'),
                    'dealerprice' => $request->input('dealerprice'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser']
                ]);

                if ($updated) {
                    DB::commit();

                    return $this->responseSuccess('update berhasil', 200, ['itemid' => $request->input('itemid')]);
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
        $item = new INMsItem();

        $cek = $item->cekBarang($request->input('itemid'));

        if ($cek == false) {

            return $this->responseError('kode Barang tidak terdaftar dalam master', 400);
        }

        DB::beginTransaction();

        try {
            $deleted = $item->deleteData([
                'itemid' => $request->input('itemid')
            ]);

            if ($deleted) {

                DB::commit();

                return $this->responseSuccess('delete berhasil', 200, ['itemid' => $request->input('itemid')]);
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
