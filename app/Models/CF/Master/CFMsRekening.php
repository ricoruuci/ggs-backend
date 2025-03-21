<?php

namespace App\Models\CF\Master; //1

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class CFMsRekening extends Model //nama class
{
    use HasFactory;

    protected $table = 'cfmsrekening'; 

    public $timestamps = false;

    function getListData($param)
    {
        $result = DB::select(
            "SELECT a.rekeningid,a.rekeningname,a.grouprekid,b.grouprekname,a.note,a.fgactive from cfmsrekening a
            inner join cfmsgrouprek b on a.grouprekid=b.grouprekid where a.fgactive='Y' and a.rekeningid like :rekeningidkeyword
            and a.rekeningname like :rekeningnamekeyword and a.grouprekid like :grouprekidkeyword and b.grouprekname like :groupreknamekeyword ",
            [
                'rekeningidkeyword' => '%' . $param['rekeningidkeyword'] . '%',
                'rekeningnamekeyword' => '%' . $param['rekeningnamekeyword'] . '%',
                'grouprekidkeyword' => '%' . $param['grouprekidkeyword'] . '%',
                'groupreknamekeyword' => '%' . $param['groupreknamekeyword'] . '%'
            ]
        );

        return $result;
    }

    function cekRekening($rekeningid)
    {

        $result = DB::selectOne(
            'SELECT * from cfmsrekening WHERE rekeningid = :rekeningid',
            [
                'rekeningid' => $rekeningid
            ]
        );
        
        return $result;
    }

}

?>