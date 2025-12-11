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
            (:soid,:urut,:itemid,:qty,:price,getdate(),:upduser,:itemname,:modal,:bagasi,:note)",

            [
                'soid' => $param['soid'],
                'itemid' => $param['itemid'],
                'urut' => $param['urut'],
                'qty' => $param['qty'],
                'price' => $param['price'],
                'upduser' => $param['upduser'],
                'itemname' => $param['itemname'],
                'modal' => $param['modal'],
                'note' => $param['note'],
                'bagasi' => $param['bagasi']
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::select(
            "SELECT a.itemid,a.urut,a.qty,a.price,b.uomid,a.upddate,a.upduser,a.itemname,a.modal,isnull(a.bagasi,0) as bagasi,
            isnull(a.keterangan,'') as note,a.qty*a.price as total
            from artrpurchaseorderdt a inner join inmsitem b on a.itemid=b.itemid WHERE a.poid = :soid ",
            [
                'soid' => $param['soid']
            ]
        );

        return $result;
    }

    function getListBarangSO($param)
    {
        $result = DB::select(
            "SELECT k.itemid,l.itemname,k.price,k.keterangan,k.qty-k.jumpo as qty,l.uomid from (
            select a.poid,isnull(a.modal,0) as price,isnull(a.qty,0) as qty,a.itemid,isnull(a.keterangan,'') as keterangan,
            isnull((select sum(x.qty) from artrpenawarandt x 
            inner join artrpenawaranhd y on x.gbuid=y.gbuid and y.flag='b' where y.soid=a.poid and x.itemid=a.itemid),0) as jumpo 
            from artrpurchaseorderdt a
            ) as k inner join inmsitem l on k.itemid=l.itemid 
            where k.poid=:soid ",
            [
                "soid" => $param['soid']
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