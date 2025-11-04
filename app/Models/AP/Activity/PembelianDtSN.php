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
            (purchaseid,suppid,itemid,price,snid,upddate,upduser,fgjual,fgsn)
            VALUES 
            (:purchaseid,:suppid,:itemid,:price,:snid,getdate(),:upduser,'T',:fgsn)",
            [
                'purchaseid' => $param['purchaseid'],
                'suppid' => $param['suppid'],
                'itemid' => $param['itemid'],
                'price' => $param['price'],
                'snid' => $param['snid'],
                'upduser' => $param['upduser'],
                'fgsn' => $param['fgsn']
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
}