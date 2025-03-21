<?php

namespace App\Http\Controllers\CF\Master; //ini cek foldernya

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CF\Master\CFMsRekening; //cek nama model nya
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class RekeningController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function insertData(Request $request)
    {

        $rek = new CFMsRekening();

        $validator = Validator::make($request->all(), $rek::$rulesInsert);

        if ($validator->fails())
        {
            return $this->responseError($validator->messages(), 400);
        }
        else
        {

            DB::beginTransaction();

            try 
            {
                $result = $rek->insertData([
                    'rekeningid' => $request->input('rekeningid'),
                    'rekeningname' => $request->input('rekeningname'),
                    'grouprekid' => $request->input('grouprekid'),
                    'note' => $request->input('note'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'tipe' => $request->input('tipe'),
                    'fgtipe' => $request->input('fgtipe'),
                    'lbrg' => $request->input('lbrg'),
                    'fgactive' => $request->input('fgactive'),
                    
                ]);

                DB::commit();

                return $this->responseData($result);

            } 
            catch (\Exception $e)
            {
                DB::rollBack();

                return $this->responseError($e->getMessage(), 400);
            }

        }

    }

    public function getListData(Request $request)
    {
        $sales = new CFMsRekening();

        $result = $sales->getListData([
            'rekeningidkeyword' => $request->input('rekeningidkeyword') ?? '',
            'rekeningnamekeyword' => $request->input('rekeningnamekeyword') ?? '',
            'grouprekidkeyword' => $request->input('grouprekidkeyword') ?? '',
            'groupreknamekeyword' => $request->input('groupreknamekeyword') ?? ''
        ]);

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }
    public function getData(Request $request, $rekeningid)
    {
        $rek = new CFMsRekening();

        $result = $rek->getdata(
            [
                'rekeningid' => $rekeningid
            ]
        );

        return $this->responseData($result);

    }

    public function updateAllData(Request $request, $rekeningid)
    {
        $rek = new CFMsRekening();

        $validator = Validator::make($request->all(), $rek::$rulesUpdateAll);

        if ($validator->fails())
        {
            return $this->responseError($validator->messages(), 400);
        }
        else
        {
            DB::beginTransaction();

            try 
            {
                $result = $rek->updateAllData([
                    'rekeningid' => $request->input('rekeningid'),
                    'rekeningname' => $request->input('rekeningname'),
                    'grouprekid' => $request->input('grouprekid'),
                    'note' => $request->input('note'),
                    'upduser' => Auth::user()->currentAccessToken()['namauser'],
                    'tipe' => $request->input('tipe'),
                    'fgtipe' => $request->input('fgtipe'),
                    'lbrg' => $request->input('lbrg'),
                    'fgactive' => $request->input('fgactive'),         
                ]);

                DB::commit();

                return $this->responseData($result);
            } 
            catch (\Exception $e)
            {
                DB::rollBack();

                return $this->responseError($e->getMessage(), 400);
            }

            
        }

    }
    
    public function deleteData(Request $request, $rekeningid)
    {
        $rek = new CFMsRekening();

        DB::beginTransaction();

        try 
        {
            $result = $rek->deleteData([
                'rekeningid' => $rekeningid
            ]);

            DB::commit();

            return $this->responseData($result);
        } 
        catch (\Exception $e)
        {
            DB::rollBack();

            $result = [
                "success" => false,
                "message" =>  $e->getMessage()

            ];

            return $this->responseError($e->getMessage(), 400);
        }
    }
}
