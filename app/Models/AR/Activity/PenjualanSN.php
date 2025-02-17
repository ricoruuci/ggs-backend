<?php

namespace App\Models\AR\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class PenjualanSN extends Model
{
    use HasFactory;

    protected $table = 'artrpenjualansn';

    public $timestamps = false;

    public static $rulesInsert = [
        'detailsn.*.itemid' => 'required'
    ];

    public static $messagesInsert = [
        'detailsn.*.itemid.required' => 'Kolom item ID (posisi index ke-:index) harus diisi.'
    ];

    public static $rulesInsert2 = [
        'itemid' => 'required'
    ];

    public static $messagesInsert2 = [
        'itemid' => 'Kolom item ID harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO ARTrPenjualansn
            (snid,saleid,itemid,price,warehouseid,upddate,upduser,purchaseid,fgsn,modal)
            VALUES
            (:snid,:saleid,:itemid,:price,:warehouseid,getdate(),:upduser,:purchaseid,'T',:modal)",

            [
                'snid' => $param['snid'],
                'saleid' => $param['saleid'],
                'itemid' => $param['itemid'],
                'price' => $param['price'],
                'warehouseid' => '01GU',
                'upduser' => $param['upduser'],
                'purchaseid' => $param['purchaseid'],
                'modal' => $param['modal']
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::select(
            'SELECT a.snid,a.saleid,a.itemid,b.itemname,a.price,a.modal,a.purchaseid,a.upddate,a.upduser,a.fgsn from ARTrPenjualansn a
            inner join inmsitem b on a.itemid=b.itemid
            WHERE a.saleid = :saleid and a.itemid=:itemid ',
            [
                'saleid' => $param['saleid'],
                'itemid' => $param['itemid']
            ]
        );

        return $result;
    }


    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM ARTrPenjualanSN WHERE saleid = :saleid and itemid = :itemid ',
            [
                'saleid' => $param['saleid'],
                'itemid' => $param['itemid']
            ]
        );

        return $result;
    }

    function selectSN($param)
    {
        $result = DB::select(
            "SELECT k.snid,k.purchaseid,k.transdate,k.suppid, k.suppname,k.purchaseid,k.price as modal,k.fgsn
            from (
            select a.snid, c.konsinyasiid as purchaseid, c.transdate, c.suppid, d.suppname, b.itemid, f.itemname, a.fgjual, b.price,a.fgsn
            from aptrkonsinyasidtsn a
            inner join aptrkonsinyasidt b on a.konsinyasiid=b.konsinyasiid and a.itemid=b.itemid
            inner join aptrkonsinyasihd c on b.konsinyasiid=c.konsinyasiid
            inner join apmssupplier d on c.suppid=d.suppid
            inner join inmsitem f on f.itemid=b.itemid
            ) as k
            where k.itemid=:itemid and k.fgjual='T'
            and k.suppid like :suppidkeyword
            and k.suppname like :suppnamekeyword
            and k.purchaseid like :purchaseidkeyword
            order by k.snid ",
            [
                'itemid' => $param['itemid'],
                'suppidkeyword' => '%' . $param['suppidkeyword'] . '%',
                'suppnamekeyword' => '%' . $param['suppnamekeyword'] . '%',
                'purchaseidkeyword' => '%' . $param['purchaseidkeyword'] . '%'
            ]
        );

        return $result;
    }
}
