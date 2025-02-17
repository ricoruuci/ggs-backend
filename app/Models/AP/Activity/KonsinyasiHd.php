<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;

use function PHPUnit\Framework\isNull;

class KonsinyasiHd extends BaseModel
{
    use HasFactory;

    protected $table = 'aptrkonsinyasihd';

    public $timestamps = false;

    public static $rulesInsert = [
        'poid' => 'required',
        'transdate' => 'required',
        'suppid' => 'required'
    ];

    public static $messagesInsert = [
        'poid' => 'Kolom nomor po harus diisi.',
        'suppid' => 'Kolom kode supplier harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'grnid' => 'required',
        'poid' => 'required',
        'suppid' => 'required',
        'transdate' => 'required'
    ];

    public static $messagesUpdate = [
        'grnid' => 'nomor penerimaan tidak ditemukan.',
        'poid' => 'nomor po tidak ditemukan.',
        'suppid' => 'Kolom kode supplier harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO aptrkonsinyasihd 
           (konsinyasiid,transdate,suppid,warehouseid,note,poid,upddate,upduser)
            VALUES 
           (:grnid,:transdate,:suppid,:warehouseid,:note,:poid,getdate(),:upduser)",
            [
                'grnid' => $param['grnid'],
                'transdate' => $param['transdate'],
                'suppid' => $param['suppid'],
                'warehouseid' => $param['warehouseid'],
                'note' => $param['note'],
                'poid' => $param['poid'],
                // 'nomordo' => $param['nomordo'],
                'upduser' => $param['upduser'],
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE aptrkonsinyasihd SET 
            transdate = :transdate,
            suppid = :suppid,
            warehouseid = :warehouseid,
            note = :note,
            poid = :poid,
            -- nomordo = :nomordo,
            upddate = getdate(),
            upduser = :upduser
            WHERE konsinyasiid = :grnid',
            [
                'grnid' => $param['grnid'],
                'transdate' => $param['transdate'],
                'suppid' => $param['suppid'],
                'warehouseid' => $param['warehouseid'],
                'note' => $param['note'],
                'poid' => $param['poid'],
                // 'nomordo' => $param['nomordo'],
                'upduser' => $param['upduser']
            ]
        );

        return $result;
    }

    function getListData($param)
    {
        if ($param['sortby'] == 'id') {
            $order = 'a.konsinyasiid ';
        } else 
        if ($param['sortby'] == 'datenew') {
            $order = 'a.transdate DESC ';
        } else if ($param['sortby'] == 'dateold') {
            $order = 'a.transdate ';
        }

        $result = DB::select(
            "SELECT a.konsinyasiid AS grnid,a.transdate,
            a.poid,a.suppid,e.suppname,c.poid AS soid,d.custid,d.custname,
            a.warehouseid,f.warehousename,a.note,a.upduser,a.upddate  
            FROM aptrkonsinyasihd a
            INNER JOIN artrpenawaranhd b ON a.poid=b.gbuid AND b.flag='B'
            INNER JOIN artrpurchaseorderhd c ON b.soid=c.poid
            INNER JOIN armscustomer d ON c.custid=d.custid
            INNER JOIN apmssupplier e ON a.suppid=e.suppid
            INNER JOIN inmswarehouse f ON a.warehouseid=f.warehouseid 
            where convert(varchar(10),a.transdate,112) between :dari and :sampai 
            and isnull(a.konsinyasiid,'') like :grnkeyword 
            and isnull(a.poid,'') like :pokeyword 
            and isnull(a.suppid,'') like :suppkeyword 
            and isnull(e.suppname,'') like :suppnamekeyword 
            and isnull(c.poid,'') like :sokeyword
            and isnull(d.custid,'') like :custkeyword 
            and isnull(d.custname,'') like :custnamekeyword               
            order by $order ",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'pokeyword' => '%' . $param['pokeyword'] . '%',
                'suppkeyword' => '%' . $param['suppkeyword'] . '%',
                'suppnamekeyword' => '%' . $param['suppnamekeyword'] . '%',
                'custkeyword' => '%' . $param['custkeyword'] . '%',
                'custnamekeyword' => '%' . $param['custnamekeyword'] . '%',
                'sokeyword' => '%' . $param['sokeyword'] . '%',
                'grnkeyword' => '%' . $param['grnkeyword'] . '%'
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.konsinyasiid AS grnid,a.transdate,
            a.poid,a.suppid,e.suppname,c.poid AS soid,d.custid,d.custname,
            a.warehouseid,f.warehousename,a.note,a.upduser,a.upddate  
            FROM aptrkonsinyasihd a
            INNER JOIN artrpenawaranhd b ON a.poid=b.gbuid AND b.flag='B'
            INNER JOIN artrpurchaseorderhd c ON b.soid=c.poid
            INNER JOIN armscustomer d ON c.custid=d.custid
            INNER JOIN apmssupplier e ON a.suppid=e.suppid
            INNER JOIN inmswarehouse f ON a.warehouseid=f.warehouseid
            where a.konsinyasiid = :grnid ",
            [
                'grnid' => $param['grnid']
            ]
        );

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM aptrkonsinyasihd WHERE konsinyasiid = :grnid',
            [
                'grnid' => $param['grnid']
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($transdate)
    {

        $pt = '1Tech';


        $year = substr($transdate, 2, 4);

        // $month = substr($transdate, 4, 2);

        $autoNumber = $this->autoNumber($this->table, 'konsinyasiid', $pt . '/GR/' . $year . '/', '0000');

        return $autoNumber;
    }

    function getListPO($param)
    {
        if ($param['sortby'] == 'id') {
            $order = 'k.gbuid ';
        } else if ($param['sortby'] == 'datenew') {
            $order = 'k.transdate DESC ';
        } else if ($param['sortby'] == 'dateold') {
            $order = 'k.transdate ';
        } else {
            $order = 'l.suppname ';
        }

        $result = DB::select(
            "SELECT DISTINCT k.gbuid as poid,k.transdate,k.suppid,l.suppname,k.soid,k.custid,k.custname  
            FROM (
            select a.gbuid,a.transdate,a.custid as suppid,b.qty,a.soid,isnull((select sum(x.qty) FROM aptrkonsinyasidt x 
            inner join aptrkonsinyasihd y on x.konsinyasiid=y.konsinyasiid 
            WHERE x.itemid=b.itemid and y.poid=a.gbuid and y.suppid=a.custid),0) as jumterima,d.custid,d.custname
            FROM artrpenawaranhd a 
            INNER JOIN artrpenawarandt b on a.gbuid=b.gbuid and a.flag='b' and ISNULL(a.soid,'')<>''
            inner join artrpurchaseorderhd c on a.soid=c.poid
            inner join armscustomer d on c.custid=d.custid
            ) as k 
            INNER JOIN apmssupplier l on k.suppid=l.suppid 
            where k.qty-k.jumterima<>0  
            and convert(varchar(10),k.transdate,112) <= :transdate 
            and isnull(k.gbuid,'') like :pokeyword 
            and isnull(l.suppid,'') like :suppkeyword 
            and isnull(l.suppname,'') like :suppnamekeyword  
            order by $order ",
            [
                'transdate' => $param['transdate'],
                'suppnamekeyword' => '%' . $param['suppnamekeyword'] . '%',
                'suppkeyword' => '%' . $param['suppkeyword'] . '%',
                'pokeyword' => '%' . $param['pokeyword'] . '%'
            ]
        );

        return $result;
    }

    function cekKonsinyasi($grnid)
    {

        $result = DB::selectOne(
            'SELECT * from aptrkonsinyasihd WHERE konsinyasiid = :grnid',
            [
                'grnid' => $grnid
            ]
        );

        return $result;
    }

    public function insertAllItem($param)
    {

        $result = DB::insert(
            "INSERT INTO allitem(voucherno,transdate,warehouseid,itemid,fgtrans,qty, 
                     price,moduleid,tempfield2)
             VALUES(:grnid,:transdate,:warehouseid,:itemid,7,:qty,:price,'AP',:suppname)",
            [
                'grnid' => $param['grnid'],
                'transdate' => $param['transdate'],
                'itemid' => $param['itemid'],
                'warehouseid' => $param['warehouseid'],
                'qty' => $param['qty'],
                'price' => $param['price'],
                'suppname' => $param['suppname'],
            ]
        );

        return $result;
    }

    function deleteAllItem($param)
    {

        $result = DB::delete(
            'DELETE FROM allitem WHERE voucherno = :grnid',
            [
                'grnid' => $param['grnid']
            ]
        );

        return $result;
    }
}