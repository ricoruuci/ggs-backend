<?php

namespace App\Models\AR\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\AR\Activity\PenjualanSN;

use function PHPUnit\Framework\isNull;

class PenjualanDt extends Model
{
    use HasFactory;

    protected $table = 'artrpenjualandt';

    public $timestamps = false;

    public static $rulesInsert = [
        'detail.*.itemid' => 'required',
        'detail.*.price' => 'required',
        'detail.*.qty' => 'required'
    ];

    public static $messagesInsert = [
        'detail.*.itemid.required' => 'Kolom item ID (posisi ke-:position) harus diisi.',
        'detail.*.qty.required' => 'Kolom Qty (posisi ke-:position) harus diisi.',
        'detail.*.price.required' => 'Kolom Harga (posisi ke-:position) harus diisi.'
    ];

    public static $itemid = [
        'soid' => 'required',
    ];

    public static $itemidmessage = [
        'soid' => 'Kolom SO Harus diisi !.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO ARTrPenjualanDt
            (saleid,itemid,price,warehouseid,qty,upduser,upddate,note,note2,flagretur,komisi,uomid,modal)
            VALUES
            (:saleid,:itemid,:price,'01GU',:qty,:upduser,getdate(),:itemname,:note,'T',:titipan,:uomid,:modal)",

            [
                'saleid' => $param['saleid'],
                'itemid' => $param['itemid'],
                'price' => $param['price'],
                'qty' => $param['qty'],
                'uomid' => $param['uomid'],
                'upduser' => $param['upduser'],
                'note' => $param['note'],
                'itemname' => $param['itemname'],
                'titipan' => $param['titipan'],
                'modal' => $param['modal']
            ]
        );

        return $result;
    }

    function getData($param)
    {

        $detailsn = new PenjualanSN();

        $result = DB::select(
            "SELECT a.saleid,a.itemid,b.itemname,a.qty,a.uomid,a.price,isnull(a.komisi,0) as titipan,a.qty*a.price as total,isnull(a.note2,'') as note,a.upduser,a.upddate
            from artrpenjualandt a
            inner join inmsitem b on a.itemid=b.itemid
            where a.saleid = :saleid ",
            [
                'saleid' => $param['saleid']
            ]
        );

        foreach ($result as $data) {

            $datadetailResult = $detailsn->getData([
                'saleid' => $param['saleid'],
                'itemid' => $data->itemid
            ]);

            $data->detailsn = $datadetailResult;
        }

        return $result;
    }


    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM artrpenjualandt WHERE saleid = :saleid ',
            [
                'saleid' => $param['saleid']
            ]
        );

        return $result;
    }

    function cariDetailBarangBaru($param)
    {
        $result = DB::select(
            "SELECT l.itemid,l.itemname,isnull(k.invoice,0) as qtyinvoice,isnull(k.qty-k.invoice,0) as qty,k.price,l.uomid as uomid,k.bagasi,isnull((k.qty-k.invoice)*k.price,0) as total,k.Keterangan as note
            from (
            select a.poid,a.itemid,b.jenis,a.qty,
            isnull((select sum(x.qty) from artrpenjualandt x inner join artrpenjualanhd y on x.saleid=y.saleid
            where y.soid=a.poid and x.itemid=a.itemid),0) as invoice,isnull(a.price,0) as Price,isnull(a.bagasi,0) as Bagasi,isnull(a.keterangan,'') as keterangan
            from artrpurchaseorderdt a inner join artrpurchaseorderhd b on a.poid=b.poid
            ) as k
            inner join inmsitem l on k.itemid=l.itemid
            where k.jenis='Y'  and k.poid=:soid
            AND l.itemname like :itemnamekeyword and k.itemid like :itemidkeyword",
            [
                'soid' => $param['soid'],
                'itemnamekeyword' => '%' . $param['itemnamekeyword'] . '%',
                'itemidkeyword' => '%' . $param['itemidkeyword'] . '%'
            ]
        );

        return $result;
    }

    function cekSudahInvoice($saleid, $soid, $itemid, $qty)
    {
        $result = DB::selectOne(
            "SELECT isnull(k.qty-K.invoice,0) as sisa from (
            select a.poid,a.itemid,a.qty,
            isnull((select sum(x.qty) from artrpenjualandt x inner join artrpenjualanhd y on x.saleid=y.saleid
            where y.soid=a.poid and x.itemid=a.itemid and y.saleid<>:saleid ),0) as invoice
            from artrpurchaseorderdt a where a.itemid=:itemid
            ) as k
            where k.poid=:soid ",
            [
                'soid' => $soid,
                'saleid' => $saleid,
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