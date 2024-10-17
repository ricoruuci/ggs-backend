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
            (:productid,:groupid,:itemid,:itemname,:partno,:uomid,:note,:userprice,0, getdate(), :upduser, 'Y','Y',0,:dealerprice)",
            [
                'itemid' => $param['itemid'],
                'itemname' => $param['itemname'],
                'productid' => $param['productid'],
                'groupid' => $param['groupid'],
                'partno' => $param['partno'],
                'uomid' => $param['satuan'],
                'note' => $param['note'],
                'userprice' => $param['userprice'],
                'dealerprice' => $param['dealerprice'],
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
            ,a.groupid,c.groupdesc,isnull(a.partno,'') as partno,a.uomid,a.userprice,a.dealerprice from inmsitem a 
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



    function getData($param)
    {

        $result = DB::selectOne(
            "SELECT a.itemid,a.itemname,a.productid,b.productdesc
            ,a.groupid,c.groupdesc,isnull(a.partno,'') as partno,a.uomid,a.userprice,a.dealerprice from inmsitem a
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
            userprice = :userprice,
            dealerprice = :dealerprice, 
            upddate = getdate(), 
            upduser = :upduser 
            WHERE itemid = :itemid',
            [
                'itemid' => $param['itemid'],
                'itemname' => $param['itemname'],
                'partno' => $param['partno'],
                'note' => $param['note'],
                'userprice' => $param['userprice'],
                'dealerprice' => $param['dealerprice'],
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
