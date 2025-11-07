<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class PembelianDt extends Model
{
    use HasFactory;

    protected $table = 'aptrpurchasedt';

    public $timestamps = false;

    public static $rulesInsert = [
        'detail.*.itemid' => 'required',
        'detail.*.qty' => 'required'
    ];

    public static $messagesInsert = [
        'detail.*.itemid.required' => 'Kolom item ID (posisi index ke-:position) harus diisi.',
        'detail.*.qty.required' => 'Kolom jumlah (posisi index ke-:position) harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO aptrpurchasedt
            (purchaseid,suppid,itemid,qty,price,disc,upddate,upduser,warehouseid)
            VALUES 
            (:purchaseid,:suppid,:itemid,:qty,:price,0,getdate(),:upduser,:warehouseid)",
            [
                'purchaseid' => $param['purchaseid'],
                'suppid' => $param['suppid'],
                'itemid' => $param['itemid'],
                'qty' => $param['qty'],
                'price' => $param['price'],
                'upduser' => $param['upduser'],
                'warehouseid' => $param['warehouseid']
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $detailsn = new PembelianDtSN();

        $result = DB::select(
            "SELECT a.purchaseid,a.itemid,b.itemname,a.qty,a.price,isnull(a.price*a.qty,0) as total,a.upduser,a.upddate,a.warehouseid,c.warehousename
            from aptrpurchasedt a
            inner join inmsitem b on a.itemid=b.itemid
            inner join inmswarehouse c on a.warehouseid=c.warehouseid
            WHERE a.purchaseid = :purchaseid ",
            [
                'purchaseid' => $param['purchaseid']
            ]
        );

        foreach ($result as $data) {

            $datadetailResult = $detailsn->getData([
                'purchaseid' => $param['purchaseid'],
                'itemid' => $data->itemid,
            ]);

            $data->detailsn = $datadetailResult;
        }

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM aptrpurchasedt WHERE purchaseid = :purchaseid ',
            [
                'purchaseid' => $param['purchaseid']
            ]
        );

        return $result;
    }

    function cariBarang($param)
    {
        $result = DB::select(
            "SELECT  b.itemname,a.itemid,a.price,a.qty,c.warehouseid,d.warehousename
            from aptrkonsinyasidt a 
            inner join inmsitem b on a.itemid=b.itemid 
			inner join APTrKonsinyasiHd c  on a.KonsinyasiID=c.KonsinyasiID
			inner join INMsWarehouse d on c.warehouseid=d.warehouseid
            where a.konsinyasiid=:grnid
            and a.itemid not in (select itemid from aptrpurchasedt where purchaseid=:purchaseid) 
            and a.itemid like :itemidkeyword and b.itemname like :itemnamekeyword
            order by a.konsinyasiid ",
            [
                'grnid' => $param['grnid'],
                'purchaseid' => $param['purchaseid'],
                'itemidkeyword' => '%' . $param['itemidkeyword'] . '%',
                'itemnamekeyword' => '%' . $param['itemnamekeyword'] . '%'

            ]
        );

        return $result;
    }


    function cekSudahBeli($purchaseid, $konsinyasid, $itemid, $qty)
    {
        $result = DB::selectOne(
            "SELECT isnull(k.sisa,0) as sisa from (
            select a.konsinyasiid,a.itemid,b.suppid,
            isnull(a.qty,0)-(select isnull(sum(qty),0) from aptrpurchasedt d 
            where d.itemid=a.itemid and e.konsinyasiid=a.konsinyasiid and d.purchaseid <> :purchaseid) as sisa from aptrkonsinyasidt a 
            inner join aptrkonsinyasihd b on a.konsinyasiid=b.konsinyasiid inner join inmsitem c on a.itemid=c.itemid
            left join aptrpurchasehd e on b.konsinyasiid=e.konsinyasiid) as k 
            where  k.sisa <> 0 and k.konsinyasiid=:grnid and k.itemid = :itemid 
            order by k.konsinyasiid ",
            [
                'purchaseid' => $purchaseid,
                'grnid' => $konsinyasid,
                'itemid' => $itemid
            ]
        );

        if (is_null($result)) {
            $sisa = 0;
        } else {
            $sisa = $result->sisa;
        }

        if ($sisa - $qty < 0) {
            $response = false;
        } else {
            $response = true;
        }


        return $response;
    }
}