<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;

use function PHPUnit\Framework\isNull;

class PurchaseOrderHd extends BaseModel
{
    use HasFactory;

    protected $table = 'artrpenawaranhd';

    public $timestamps = false;

    public static $rulesInsert = [
        'suppid' => 'required',
        'transdate' => 'required',
        'purchasingid' => 'required'
    ];

    public static $messagesInsert = [
        'suppid' => 'Kolom kode supplier harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.',
        'purchasingid' => 'Kolom kode purchasing harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'poid' => 'required',
        'suppid' => 'required',
        'transdate' => 'required',
        'purchasingid' => 'required'
    ];

    public static $messagesUpdate = [
        'poid' => 'nomor po tidak ditemukan.',
        'suppid' => 'Kolom kode supplier harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.',
        'purchasingid' => 'Kolom kode purchasing harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO artrpenawaranhd 
           (gbuid,transdate,currid,customer,up,salesid,phone,fax,email,note,upddate,upduser,
           ttlgbu,fgtax,disc,custid,flag,fob,soid,nilaitax,term,bankid)
            VALUES 
           (:poid,:transdate,'IDR',:suppname,:up,:purchasingid,:telp,:fax,'',:note,getdate(),:upduser,0,:fgtax,0,:suppid,'B',
           (select top 1 fob from artrpurchaseorderhd where poid=:nomorso),:soid,:nilaitax,:term,'')",
            [
                'poid' => $param['poid'],
                'transdate' => $param['transdate'],
                'suppname' => $param['suppname'],
                'up' => $param['up'],
                'purchasingid' => $param['purchasingid'],
                'telp' => $param['telp'],
                'fax' => $param['fax'],
                // 'email' => $param['email'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'fgtax' => $param['fgtax'],
                'suppid' => $param['suppid'],
                'nomorso' => $param['soid'],
                'soid' => $param['soid'],
                'nilaitax' => $param['nilaitax'],
                'term' => $param['term']
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE artrpenawaranhd SET 
            transdate = :transdate,
            customer = :suppname,
            up = :up,
            salesid = :purchasingid,
            phone = :telp,
            fax = :fax,
            -- email = :email,
            note = :note,
            upddate = getdate(),
            upduser = :upduser,
            fgtax = :fgtax,
            nilaitax = :nilaitax,
            custid = :suppid,
            soid = :soid,
            term = :term
            WHERE gbuid = :poid',
            [
                'poid' => $param['poid'],
                'transdate' => $param['transdate'],
                'suppname' => $param['suppname'],
                'up' => $param['up'],
                'purchasingid' => $param['purchasingid'],
                'telp' => $param['telp'],
                // 'fax' => $param['fax'],
                'fax' => $param['fax'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'fgtax' => $param['fgtax'],
                'suppid' => $param['suppid'],
                'soid' => $param['soid'],
                'nilaitax' => $param['nilaitax'],
                'term' => $param['term']
            ]
        );

        return $result;
    }

    function getListData($param)
    {
        if ($param['sortby'] == 'new') {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }

        $result = DB::select(
            "SELECT a.gbuid as poid,a.transdate,a.custid as suppid,a.customer as suppname,a.up,a.phone as telp,a.fax,a.email,
            a.note,a.salesid as purchasingid,b.salesname as purchasingname,a.upddate,a.upduser,a.fgtax,a.NilaiTax as nilaitax,a.soid,d.custname,
            case when a.fgtax='y' then a.ttlgbu/(1+(a.NilaiTax*0.01)) else a.ttlgbu end as subtotal,
            case when a.fgtax='y' then a.ttlgbu/(1+(a.NilaiTax*0.01))*(a.NilaiTax*0.01) else 0 end as ppn,
            a.ttlgbu,isnull(a.term,'30') as term
            from artrpenawaranhd a 
            inner join armssales b on a.salesid=b.salesid 
            left join artrpurchaseorderhd c on a.soid=c.poid
            left join armscustomer d on c.custid=d.custid
            where a.flag='B' and convert(varchar(10),a.transdate,112) between :dari and :sampai 
            and isnull(d.custid,'') like :custid and isnull(a.custid,'') like :suppid  and a.gbuid like :keyword  
            and isnull(a.salesid,'') like :purchasingid     
            order by a.transdate $order",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'custid' => '%' . $param['custid'] . '%',
                'suppid' => '%' . $param['suppid'] . '%',
                'purchasingid' => '%' . $param['purchasingid'] . '%',
                'keyword' => '%' . $param['keyword'] . '%'
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.gbuid as poid,a.transdate,a.custid as suppid,a.customer as suppname,a.up,a.phone as telp,a.fax as email,DATEADD(DAY, a.term, a.transdate) as jatuhtempo,
            a.note,a.salesid as purchasingid,b.salesname as purchasingname,a.upddate,a.upduser,a.fgtax,a.nilaitax as nilaitax,a.soid,d.custname,
            case when a.fgtax='y' then a.ttlgbu/(1+(a.nilaitax*0.01)) else a.ttlgbu end as subtotal,
            case when a.fgtax='y' then a.ttlgbu/(1+(a.nilaitax*0.01))*(a.nilaitax*0.01) else 0 end as ppn,
            a.ttlgbu,isnull(a.term,30) as term
            from artrpenawaranhd a 
            inner join armssales b on a.salesid=b.salesid 
            left join artrpurchaseorderhd c on a.soid=c.poid
            left join armscustomer d on c.custid=d.custid
            where a.flag='B' and a.gbuid = :poid",
            [
                'poid' => $param['poid']
            ]
        );

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM artrpenawaranhd WHERE gbuid = :poid',
            [
                'poid' => $param['poid']
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($transdate, $fgtax)
    {
        if ($fgtax == 'Y') {
            $pt = 'SAS';
        } else {
            $pt = '1Tech';
        }

        $year = substr($transdate, 0, 4);

        $month = substr($transdate, 4, 2);

        $autoNumber = $this->autoNumber($this->table, 'gbuid', $pt . '-PO/' . $year . '-' . $month, '0000');

        return $autoNumber;
    }

    function hitungTotal($param)
    {

        $result = DB::selectONe(
            "SELECT case when k.fgtax='T' then k.subtotal-k.disc else (k.subtotal-k.disc)+(k.subtotal-k.disc)*k.nilaitax*0.01 end as grandtotal from (
            select isnull(sum(a.qty*a.price),0) as subtotal,b.fgtax,b.gbuid,b.disc,b.nilaitax
            from artrpenawarandt a inner join artrpenawaranhd b on a.gbuid=b.gbuid
            group by b.fgtax,b.gbuid,b.disc,b.nilaitax) as k
            where k.gbuid=:poid",
            [
                'poid' => $param['poid']
            ]
        );

        return $result;
    }

    function updateTotal($param)
    {
        $result = DB::update(
            'UPDATE artrpenawaranhd SET ttlgbu = :grandtotal WHERE gbuid = :poid',
            [
                'poid' => $param['poid'],
                'grandtotal' => $param['grandtotal']
            ]
        );

        return $result;
    }

    function cekPurchaseOrder($poid)
    {
        $result = DB::selectOne(
            'SELECT * from artrpenawaranhd WHERE gbuid = :poid',
            [
                'poid' => $poid
            ]
        );

        return $result;
    }

    function cekBolehEdit($poid)
    {
        $result = DB::selectOne(
            "SELECT top 1 k.saleid,k.poid from (
             select konsinyasiid as saleid,poid from aptrkonsinyasihd
             ) as k 
             where k.poid=:poid ",
            [
                'poid' => $poid
            ]
        );

        return $result;
    }
}