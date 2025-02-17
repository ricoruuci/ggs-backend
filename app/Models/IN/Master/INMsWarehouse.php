<?php

namespace App\Models\IN\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class INMsWarehouse extends BaseModel
{
    use HasFactory;

    protected $table = 'inmswarehouse';

    public $timestamps = false;

    function getListData($param)
    {
        $result = DB::select(
            "SELECT a.warehouseid,a.warehousename from inmswarehouse a where a.warehouseid like :warehouseidkeyword and 
            warehousename like :warehousenamekeyword order by a.warehouseid",
            [
                'warehouseidkeyword' => '%' . $param['warehouseidkeyword'] . '%',
                'warehousenamekeyword' => '%' . $param['warehousenamekeyword'] . '%'
            ]
        );

        return $result;
    }

    function cekWarehouse($warehouseid)
    {

        $result = DB::selectOne(
            'SELECT * from inmswarehouse WHERE warehouseid = :warehouseid',
            [
                'warehouseid' => $warehouseid
            ]
        );

        return $result;
    }
}
