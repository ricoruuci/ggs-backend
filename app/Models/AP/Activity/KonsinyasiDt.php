<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class KonsinyasiDt extends Model
{
    use HasFactory;

    protected $table = 'aptrkonsinyasidt';

    public $timestamps = false;

    public static $rulesInsert = [
        'detail.*.itemid' => 'required',
        'detail.*.qty' => 'required',
        'detail.*.price' => 'required'
    ];

    public static $messagesInsert = [
        'detail.*.itemid.required' => 'Kolom item ID (posisi index ke-:position) harus diisi.',
        'detail.*.qty.required' => 'Kolom jumlah (posisi index ke-:position) harus diisi.',
        'detail.*.price.required' => 'Kolom harga (posisi index ke-:position) harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO aptrkonsinyasidt
            (konsinyasiid,itemid,partno,qty,price,upddate,upduser)
            VALUES 
            (:grnid,:itemid,:partno,:qty,:price,getdate(),:upduser)",
            [
                'grnid' => $param['grnid'],
                'itemid' => $param['itemid'],
                'partno' => $param['partno'],
                'qty' => $param['qty'],
                'price' => $param['price'],
                'upduser' => $param['upduser']
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $detailsn = new KonsinyasiDtSN();

        $result = DB::select(
            "SELECT a.konsinyasiid as grnid,a.itemid,d.itemname,a.partno,c.qty as jmlpo,a.qty,
            c.qty-a.qty as sisa,a.price
            FROM aptrkonsinyasidt a
            INNER JOIN aptrkonsinyasihd b on a.konsinyasiid=b.konsinyasiid
            INNER JOIN artrpenawarandt c on b.poid=c.gbuid and a.itemid=c.itemid
            INNER JOIN inmsitem d on a.itemid=d.itemid  WHERE a.konsinyasiid = :grnid ",
            [
                'grnid' => $param['grnid']
            ]
        );

        foreach ($result as $data) {

            $datadetailResult = $detailsn->getData([
                'grnid' => $param['grnid'],
                'itemid' => $data->itemid,
            ]);

            $data->detailsn = $datadetailResult;
        }

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM aptrkonsinyasidt WHERE konsinyasiid = :grnid ',
            [
                'grnid' => $param['grnid']
            ]
        );

        return $result;
    }

    function getListPODt($param)
    {


        $result = DB::select(
            "SELECT k.gbuid as poid,k.itemid,m.partno,m.itemname,ISNULL(k.qty-k.jumterima,0) as qty,m.uomid,k.price FROM (
            SELECT a.gbuid,a.transdate,b.itemid,ISNULL(b.qty,0) as qty,ISNULL(b.price,0) as price,
            ISNULL((SELECT sum(x.qty) FROM aptrkonsinyasidt x INNER JOIN aptrkonsinyasihd y on x.konsinyasiid=y.konsinyasiid 
            WHERE x.itemid=b.itemid AND y.poid=a.gbuid and y.suppid=a.custid),0) as jumterima FROM artrpenawaranhd a 
            INNER JOIN artrpenawarandt b on a.gbuid=b.gbuid where a.flag='b') as k 
            INNER JOIN inmsitem m on k.itemid=m.itemid 
            WHERE k.gbuid=:poid 
            order by m.itemname ",
            [
                'poid' => $param['poid'],

            ]
        );

        return $result;
    }

    function cekSudahTerima($grnid, $poid, $itemid, $qty)
    {

        $result = DB::selectOne(
            "SELECT ISNULL(k.jumlahpo-k.jumterima,0) as sisa,k.jumlahpo,k.jumterima FROM (
            SELECT a.gbuid,a.itemid,ISNULL(a.qty,0) as jumlahpo,
            ISNULL((SELECT sum(x.qty) FROM aptrkonsinyasidt x INNER JOIN aptrkonsinyasihd y on x.konsinyasiid=y.konsinyasiid 
            WHERE x.itemid=a.itemid AND y.poid=a.gbuid and y.konsinyasiid<>:grnid),0) as jumterima 
            FROM artrpenawarandt a 
            ) as k 
            WHERE k.gbuid=:poid and k.itemid=:itemid ",
            [
                'poid' => $poid,
                'grnid' => $grnid,
                'itemid' => $itemid
            ]
        );
        // dd(var_dump($qty - $result->sisa));

        if ($result->sisa - $qty < 0) {
            $result = false;
        } else {
            $result = true;
        }

        return $result;
    }
}