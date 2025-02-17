<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class PurchaseOrderDt extends Model
{
    use HasFactory;

    protected $table = 'artrpenawarandt';

    public $timestamps = false;
    
    public static $rulesInsert = [
        'detail.*.itemid' => 'required',
        'detail.*.qty' => 'required',
        'detail.*.price' => 'required',
        'detail.*.itemname' => 'required'
    ];

    public static $messagesInsert = [
        'detail.*.itemid.required' => 'Kolom item ID (posisi index ke-:position) harus diisi.',
        'detail.*.qty.required' => 'Kolom jumlah (posisi index ke-:position) harus diisi.',
        'detail.*.price.required' => 'Kolom harga (posisi index ke-:position) harus diisi.',
        'detail.*.itemname.required' => 'Kolom nama (posisi index ke-:position) harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO artrpenawarandt
            (gbuid,urut,produk,description,qty,price,upddate,upduser,itemid,partno)
            VALUES 
            (:poid,:urut,:itemname,:note,:qty,:price,getdate(),:upduser,:itemid,:partno)",  
            [
                'poid' => $param['poid'],
                'urut' => $param['urut'],
                'itemname' => $param['itemname'],
                'note' => $param['note'],
                'qty' => $param['qty'],
                'price' => $param['price'],
                'upduser' => $param['upduser'],
                'itemid' => $param['itemid'],
                'partno' => $param['partno']                
            ]
        );     

        return $result;
    }

    function getData($param)
    {
        $result = DB::select(
            "SELECT a.gbuid as poid,a.urut,a.itemid,a.produk as itemname,a.partno,
            isnull(a.description,'') as note,a.qty,a.price,a.qty*a.price as total,a.upddate,a.upduser 
            from artrpenawarandt a WHERE a.gbuid = :poid ",
            [
                'poid' => $param['poid']
            ]
        );

        return $result;
    }

    
    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM artrpenawarandt WHERE gbuid = :poid ',
            [
                'poid' => $param['poid']
            ]
        );

        return $result;
    }
}

?>