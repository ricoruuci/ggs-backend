<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class PembelianDtSN extends Model
{
    use HasFactory;

    protected $table = 'aptrpurchasedtsn';

    public $timestamps = false;

    public static $rulesInsert = [
        'detailsn.*.snid' => 'required'
    ];

    public static $messagesInsert = [
        'detailsn.*.snid.required' => 'Kolom SN  (posisi index ke-:position) harus diisi.'
    ];

    public function insertData($param)
    {
        $result = DB::insert(
            "INSERT INTO aptrpurchasedtsn
            (purchaseid,suppid,itemid,snid,upddate,upduser,fgjual,fgsn,warehouseid,price)
            VALUES 
            (:purchaseid,:suppid,:itemid,:snid,getdate(),:upduser,'T',:fgsn,:warehouseid,:price)",
            [
                'purchaseid' => $param['purchaseid'],
                'suppid' => $param['suppid'],
                'itemid' => $param['itemid'],
                'snid' => $param['snid'],
                'upduser' => $param['upduser'],
                'fgsn' => $param['fgsn'],
                'warehouseid' => $param['warehouseid'],
                'price' => $param['price']
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::select(
            "SELECT a.snid
            FROM aptrpurchasedtsn a WHERE a.purchaseid = :purchaseid and itemid = :itemid ",
            [
                'purchaseid' => $param['purchaseid'],
                'itemid' => $param['itemid']
            ]
        );

        return $result;
    }


    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM aptrpurchasedtsn WHERE purchaseid = :purchaseid  ',
            [
                'purchaseid' => $param['purchaseid']
            ]
        );

        return $result;
    }

    function cariSN($param)
    {
        $result = DB::select(
            "SELECT konsinyasiid as grnid,itemid,snid,upddate,upduser,fgjual,fgsn from APTrKonsinyasiDtSN
            WHERE konsinyasiid = :grnid and itemid = :itemid and snid like :snidkeyword ",
            [
                'grnid' => $param['grnid'],
                'itemid' => $param['itemid'],
                'snidkeyword' => '%' . $param['snidkeyword'] . '%'

            ]
        );

        return $result;
    }
}