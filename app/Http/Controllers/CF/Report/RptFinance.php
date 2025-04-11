<?php

namespace App\Http\Controllers\CF\Report; //ini cek foldernya

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CF\Report\RptBukuBesar; //cek nama model nya
use App\Models\CF\Report\RptLabaRugi;
use App\Models\CF\Report\RptNeraca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class RptFinance extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function getRptBukuBesar(Request $request)
    {
        $rek = new RptBukuBesar();

        if($request->input('rekeningid'))
        {   
            $result = $rek->getRptBukuBesar(
            [
                'dari' => $request->input('dari'),
                'sampai' => $request->input('sampai'),
                'rekeningid' => $request->input('rekeningid')
            ]);
        }
        else
        {
            $result = $rek->getRptBukuBesar([
                'dari' => $request->input('dari'),
                'sampai' => $request->input('sampai')
            ]);
        }

        return $this->responseData($result); 

    }

    public function getRptLabaRugi(Request $request)
    {
        $rek = new RptLabaRugi();

        $result = $rek->getRptLabaRugi([
            'dari' => $request->input('dari'),
            'sampai' => $request->input('sampai')
        ]);
        
        return $this->responseData($result); 

    }

    public function getRptNeraca(Request $request)
    {
        $rek = new RptNeraca();

        $result = $rek->getRptNeraca([
            'periode' => $request->input('periode')
        ]);
        
        return $this->responseData($result); 

    }

}

?>