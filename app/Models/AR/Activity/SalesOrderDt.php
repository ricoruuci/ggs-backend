<?php

namespace App\Models\AR\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class SalesOrderDt extends Model
{
    use HasFactory;

    protected $table = 'artrpurchaseorderdt';

    public $timestamps = false;
    
    public static $rulesInsert = [
        'detail.*.itemid' => 'required',
        'detail.*.qty' => 'required',
        'detail.*.price' => 'required',
        'detail.*.itemname' => 'required',
        'detail.*.modal' => 'required'
    ];

    public static $messagesInsert = [
        'detail.*.itemid.required' => 'Kolom item ID (posisi index ke-:position) harus diisi.',
        'detail.*.qty.required' => 'Kolom jumlah (posisi index ke-:position) harus diisi.',
        'detail.*.price.required' => 'Kolom harga (posisi index ke-:position) harus diisi.',
        'detail.*.itemname.required' => 'Kolom nama (posisi index ke-:position) harus diisi.',
        'detail.*.modal.required' => 'Kolom modal (posisi index ke-:position) harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO artrpurchaseorderdt
            (poid,urut,itemid,qty,price,upddate,upduser,itemname,modal,bagasi,keterangan) 
            VALUES 
            (:soid,:urut,:itemid,:qty,:price,getdate(),:upduser,:itemname,:modal,0,:note)", 
             
            [
                'soid' => $param['soid'],
                'itemid' => $param['itemid'],
                'urut' => $param['urut'],
                'qty' => $param['qty'],
                'price' => $param['price'],
                'upduser' => $param['upduser'],
                'itemname' => $param['itemname'],
                'modal' => $param['modal'],
                'note' => $param['note']
            ]
        );     

        return $result;
    }

    function getData($param)
    {
        $result = DB::select(
            "SELECT a.itemid,a.urut,a.qty,a.price,b.uomid,a.upddate,a.upduser,a.itemname,a.modal,
            isnull(a.keterangan,'') as note,a.qty*a.price as total
            from artrpurchaseorderdt a inner join inmsitem b on a.itemid=b.itemid WHERE a.poid = :soid ",
            [
                'soid' => $param['soid']
            ]
        );

        return $result;
    }

    
    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM artrpurchaseorderdt WHERE poid = :soid ',
            [
                'soid' => $param['soid']
            ]
        );

        return $result;
    }
}

?>