<?php

namespace App\Models\IN\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class INMsItem extends BaseModel
{
    use HasFactory;

    protected $table = 'inmsitem';

    public $timestamps = false;

    public static $rulesInsert = [
        'groupid' => 'required',
        'productid' => 'required',
        'itemname' => 'required'
    ];

    public static $messagesInsert = [
        'groupid' => 'Kolom kode group harus diisi.',
        'productid' => 'Kolom kode product harus diisi.',
        'itemname' => 'Kolom nama barang harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'itemid' => 'required'
    ];

    public static $messagesUpdate = [
        'itemid' => 'Kode barang tidak boleh kosong.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO inmsitem
            (productid,groupid,itemid,itemname,partno,uomid,note,userprice,minimumstok,upddate,upduser,ctk,fgactive,komisi,dealerprice) 
            VALUES 
            (:productid,:groupid,:itemid,:itemname,:partno,:uomid,:note,:userprice,:minimumstok, getdate(), :upduser, 'Y','Y',0,0)",
            [
                'itemid' => $param['itemid'],
                'itemname' => $param['itemname'],
                'productid' => $param['productid'],
                'groupid' => $param['groupid'],
                'partno' => $param['partno'],
                'uomid' => $param['satuan'],
                'note' => $param['note'],
                'userprice' => $param['userprice'],
                'minimumstok' => $param['minimumstok'],
                'upduser' => $param['upduser']
            ]
        );

        return $result;
    }



    function getListData($param)
    {
        if ($param['sortby'] == 'itemid') {
            $order = 'itemid';
        } else {
            $order = 'itemname';
        }

        $result = DB::select(
            "SELECT a.itemid,a.itemname,a.productid,b.productdesc
            ,a.groupid,c.groupdesc,isnull(a.partno,'') as partno,a.uomid from inmsitem a 
            inner join inmsproduct b on a.productid=b.productid
            inner join inmsgroup c on a.groupid=c.groupid
            where a.fgactive='Y' and itemid like :itemidkeyword and itemname like :itemnamekeyword order by $order ",
            [
                'itemidkeyword' => '%' . $param['itemidkeyword'] . '%',
                'itemnamekeyword' => '%' . $param['itemnamekeyword'] . '%'
            ]
        );

        return $result;
    }

    function getListBarangSO($param)
    {
        $result = DB::select(
            "SELECT k.itemid,l.itemname,k.price,k.keterangan,k.qty-k.jumpo as jumlah,l.uomid from (
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

    function getData($param)
    {

        $result = DB::selectOne(
            "SELECT a.itemid,a.itemname,a.productid,b.productdesc
            ,a.groupid,c.groupdesc,isnull(a.partno,'') as partno,a.uomid from inmsitem a
            inner join inmsproduct b on a.productid=b.productid
            inner join inmsgroup c on a.groupid=c.groupid
            where a.fgactive='Y' and itemid=:itemid ",
            [
                'itemid' => $param['itemid']
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE inmsitem SET 
            itemname = :itemname, 
            partno = :partno,
            note = :note,
            userprice = :hargauser,
            minimumstok = :minimumstok, 
            upddate = getdate(), 
            upduser = :upduser 
            WHERE itemid = :itemid',
            [
                'itemid' => $param['itemid'],
                'itemname' => $param['itemname'],
                'partno' => $param['partno'],
                'note' => $param['note'],
                'hargauser' => $param['hargauser'],
                'minimumstok' => $param['minimumstok'],
                'upduser' => $param['upduser']
            ]
        );

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM inmsitem WHERE itemid = :itemid',
            [
                'itemid' => $param['itemid']
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($groupid, $productid)
    {

        $autoNumber = $this->autoNumber($this->table, 'itemid',  $groupid . '.' . $productid . '.', '000');

        return $autoNumber;
    }




    function cekBarang($itemid)
    {

        $result = DB::selectOne(
            'SELECT * from inmsitem WHERE itemid = :itemid',
            [
                'itemid' => $itemid
            ]
        );

        return $result;
    }
}
