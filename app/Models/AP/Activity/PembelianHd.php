<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;

use function PHPUnit\Framework\isNull;

class PembelianHd extends BaseModel
{
    use HasFactory;

    protected $table = 'aptrpurchasehd';

    public $timestamps = false;

    public static $rulesInsert = [
        'purchaseid' => 'required',
        'grnid' => 'required',
        'transdate' => 'required',
        'suppid' => 'required'
    ];

    public static $messagesInsert = [
        'purchaseid' => 'Kolom nomor pembelian harus diisi.',
        'grnid' => 'Kolom nota penerimaan harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.',
        'suppid' => 'Kolom kode supplier harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.',
    ];

    public static $rulesUpdateAll = [
        'purchaseid' => 'required',
        'transdate' => 'required'
    ];

    public static $messagesUpdate = [
        'purchaseid' => 'nota pembelian tidak ditemukan.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.'
    ];

    public function insertData($param)
    {
        

        $result = DB::insert(
            "INSERT INTO aptrpurchasehd 
           (purchaseid,transdate,currid,fpsid,konsinyasiid,suppid,nilaitax,fgtax,note,jatuhtempo,upddate,upduser,rekeningp,rekeningu,rekeningk,rekpersediaan,rekhpp,fgoto,npwp,rate)
            VALUES 
           (:purchaseid,:transdate,:currid,:nofps,:grnid,:suppid,:nilaitax,:fgtax,:note,:jatuhtempo,getdate(),:upduser,:rekeningp,:rekeningu,:rekeningk,:rekpersediaan,:rekhpp,'T',:npwp,:rate)",
            [
                'purchaseid' => $param['purchaseid'],
                'transdate' => $param['transdate'],
                'nofps' => $param['nofps'],
                'grnid' => $param['grnid'],
                'suppid' => $param['suppid'],
                'nilaitax' => $param['nilaitax'] ,
                'fgtax' => $param['fgtax'] ,
                'note' => $param['note'],
                'jatuhtempo' => $param['jatuhtempo'],
                'upduser' => $param['upduser'],
                'npwp' => $param['npwp'],
                'rekeningp' => $param['rekeningp'],
                'rekeningu' => $param['rekeningu'],
                'rekeningk' => $param['rekeningk'],
                'rekpersediaan' => $param['rekpersediaan'],
                'rekhpp' => $param['rekhpp'],
                'currid' => $param['currid'],
                'rate' => $param['rate'],
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE aptrpurchasehd SET 
            transdate = :transdate,
            fpsid = :nofps,
            konsinyasiid = :grnid,
            suppid = :suppid,
            nilaitax = :nilaitax,
            fgtax = :fgtax,
            note = :note,
            jatuhtempo = :jatuhtempo,
            upddate = getdate(),
            upduser = :upduser,
            npwp = :npwp,
            rekeningp = :rekeningp,
            rekeningu = :rekeningu,
            rekeningk = :rekeningk,
            rekpersediaan = :rekpersediaan,
            rekhpp = :rekhpp,
            rate = :rate,
            currid = :currid
            
            WHERE purchaseid = :purchaseid ',
            [
                'purchaseid' => $param['purchaseid'],
                'transdate' => $param['transdate'],
                'nofps' => $param['nofps'],
                'grnid' => $param['grnid'],
                'suppid' => $param['suppid'],
                'nilaitax' => $param['nilaitax'],
                'fgtax' => $param['fgtax'],
                'note' => $param['note'],
                'jatuhtempo' => $param['jatuhtempo'],
                'upduser' => $param['upduser'],
                'npwp' => $param['npwp'],
                'rekeningp' => $param['rekeningp'],
                'rekeningu' => $param['rekeningu'],
                'rekeningk' => $param['rekeningk'],
                'rekpersediaan' => $param['rekpersediaan'],
                'rekhpp' => $param['rekhpp'],
                'currid' => $param['currid'],
                'rate' => $param['rate'],
            ]
        );

        return $result;
    }

    function getListData($param)
    {
        if ($param['sortby'] == 'id') {
            $order = 'a.purchaseid ';
        } else 
        if ($param['sortby'] == 'datenew') {
            $order = 'a.transdate DESC ';
        } else if ($param['sortby'] == 'dateold') {
            $order = 'a.transdate ';
        }

        $result = DB::select(
            "SELECT a.purchaseid,isnull(a.fpsid,'') as fpsid,a.transdate,b.suppid,b.suppname,isnull(e.custid,'') as custid,isnull(d.soid+' - '+f.custname,'') as custname,
            a.konsinyasiid as grnid,isnull(c.poid,'') as poid,isnull(a.note,'') as note,a.jatuhtempo,a.Transdate + isnull(a.JatuhTempo,0) as tgljatuhtempo,
            a.rate,a.currid,a.upduser,a.upddate,
            isnull((select sum(x.qty*x.price) from aptrpurchasedt x where x.purchaseid=a.purchaseid),0) as subtotal,
            a.nilaitax,a.fgtax,
            case when a.fgtax = 't' then 0 else isnull((select sum(x.qty*x.price) * a.nilaitax * 0.01 from aptrpurchasedt x where x.purchaseid=a.purchaseid),0) end as taxamount,
            a.ttlpb as grandtotal,a.fgoto,case when a.fgoto='t' then 'Belum Otorisasi' else 'Sudah Otorisasi' end as statusoto,isnull(a.npwp,'') as npwp,a.otoby,a.otodate
            from aptrpurchasehd a
            inner join apmssupplier b on a.suppid=b.suppid
            inner join aptrkonsinyasihd c on a.konsinyasiid=c.konsinyasiid
            left join artrpenawaranhd d on d.gbuid=c.poid
			left join ARTrPurchaseOrderHd e on e.POID=d.SOID
            left join armscustomer f on f.custid=e.custid
            where convert(varchar(10),a.transdate,112) between :dari and :sampai 
            and isnull(a.purchaseid,'') like :purchaseidkeyword 
            and isnull(a.konsinyasiid,'') like :grnidkeyword 
            and isnull(a.suppid,'') like :suppidkeyword 
            and isnull(b.suppname,'') like :suppnamekeyword 
            and isnull(c.poid,'') like :poidkeyword
            and isnull(e.custid,'') like :custidkeyword 
            and isnull(f.custname,'') like :custnamekeyword
            order by $order ",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'purchaseidkeyword' => '%' . $param['purchaseidkeyword'] . '%',
                'grnidkeyword' => '%' . $param['grnidkeyword'] . '%',
                'suppidkeyword' => '%' . $param['suppidkeyword'] . '%',
                'suppnamekeyword' => '%' . $param['suppnamekeyword'] . '%',
                'custidkeyword' => '%' . $param['custidkeyword'] . '%',
                'custnamekeyword' => '%' . $param['custnamekeyword'] . '%',
                'poidkeyword' => '%' . $param['poidkeyword'] . '%'
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.purchaseid,isnull(a.fpsid,'') as fpsid,a.transdate,b.suppid,b.suppname,isnull(e.custid,'') as custid,isnull(d.soid+' - '+f.custname,'') as custname,
            a.konsinyasiid as grnid,isnull(c.poid,'') as poid,isnull(a.note,'') as note,a.jatuhtempo,a.Transdate + isnull(a.JatuhTempo,0) as tgljatuhtempo,
            a.rate,a.currid,a.upduser,a.upddate,
            isnull((select sum(x.qty*x.price) from aptrpurchasedt x where x.purchaseid=a.purchaseid),0) as subtotal,
            a.nilaitax,a.fgtax,
            case when a.fgtax = 't' then 0 else isnull((select sum(x.qty*x.price) * a.nilaitax * 0.01 from aptrpurchasedt x where x.purchaseid=a.purchaseid),0) end as taxamount,
            a.ttlpb as grandtotal,a.fgoto,case when a.fgoto='t' then 'Belum Otorisasi' else 'Sudah Otorisasi' end as statusoto,isnull(a.npwp,'') as npwp,a.otoby,a.otodate
            from aptrpurchasehd a
            inner join apmssupplier b on a.suppid=b.suppid
            inner join aptrkonsinyasihd c on a.konsinyasiid=c.konsinyasiid
            left join artrpenawaranhd d on d.gbuid=c.poid
			left join ARTrPurchaseOrderHd e on e.POID=d.SOID
            left join armscustomer f on f.custid=e.custid
            where a.purchaseid = :purchaseid ",
            [
                'purchaseid' => $param['purchaseid']
            ]
        );

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM aptrpurchasehd WHERE purchaseid = :purchaseid ',
            [
                'purchaseid' => $param['purchaseid']
            ]
        );

        return $result;
    }

    // public function beforeAutoNumber($transdate, $fgtax)
    // {
    //     if ($fgtax == 'T') {
    //         $pt = 'AP-TEC';
    //     } else {
    //         $pt = 'AP-SAS';
    //     }


    //     $year = substr($transdate, 2, 2);
    //     $month = substr($transdate, 5, 2);

    //     // $month = substr($transdate, 4, 2);

    //     $autoNumber = $this->autoNumber($this->table, 'konsinyasiinvid', $pt . '/' . $year . '/' . $month . '/', '0000');

    //     return $autoNumber;
    // }

    function cariPenerimaan($param)
    {
        if ($param['sortby'] == 'id') {
            $order = 'a.konsinyasiid ';
        } else if ($param['sortby'] == 'datenew') {
            $order = 'a.transdate DESC ';
        } else {
            $order = 'a.transdate ';
        }

        $result = DB::select(
            "SELECT a.konsinyasiid as grnid,a.transdate,a.poid,a.suppid,c.suppname as suppname,d.poid+' - '+e.custname as custname
            from aptrkonsinyasihd a 
            inner join artrpenawaranhd b on a.poid=b.gbuid and a.suppid=b.custid and a.suppid=b.custid and b.flag='b'
            inner join apmssupplier c on a.suppid=c.suppid and b.custid=c.suppid
			left join ARTrPurchaseOrderHd d on d.poid=b.soid
			left join ARMsCustomer e on e.custid=d.CustID
            where isnull(a.poid,'')<>'' and a.konsinyasiid not in (select isnull(konsinyasiid,'') from aptrpurchasehd)  and
            convert(varchar(10),a.transdate,112) <= :transdate
            and isnull(a.konsinyasiid,'') like :grnidkeyword
            and isnull(a.suppid,'') like :suppidkeyword
            and isnull(c.suppname,'') like :suppnamekeyword
            order by $order",
            [
                'transdate' => $param['transdate'],
                'suppnamekeyword' => '%' . $param['suppnamekeyword'] . '%',
                'suppidkeyword' => '%' . $param['suppidkeyword'] . '%',
                'grnidkeyword' => '%' . $param['grnidkeyword'] . '%'
            ]
        );

        return $result;
    }

    function cekInvoice($purchaseid)
    {

        $result = DB::selectOne(
            'SELECT * from aptrpurchasehd WHERE purchaseid = :purchaseid',
            [
                'purchaseid' => $purchaseid
            ]
        );

        return $result;
    }

    function cekPenerimaan($konsinyasiid)
    {

        $result = DB::selectOne(
            'SELECT * from aptrkonsinyasihd WHERE konsinyasiid = :grnid',
            [
                'grnid' => $konsinyasiid
            ]
        );

        return $result;
    }

    function hitungTotal($param)
    {

        $result = DB::selectOne(
            "SELECT k.subtotal,case when fgtax='y' then k.subtotal*k.nilaitax*0.01 else 0 end as ppn,
            case when fgtax='y' then k.subtotal + k.subtotal*k.nilaitax*0.01 else k.subtotal end total
            from (
            select a.purchaseid,a.suppid,b.fgtax,isnull(sum(price*qty)-sum(price*qty*disc*0.01),0) as subtotal,b.nilaitax
            from aptrpurchasedt a inner join aptrpurchasehd b on a.purchaseid=b.purchaseid
            group by a.purchaseid,a.suppid,b.fgtax,b.nilaitax
            ) as k
            where k.purchaseid=:purchaseid and k.suppid=:suppid  ",
            [
                'purchaseid' => $param['purchaseid'],
                'suppid' => $param['suppid']
            ]
        );

        return $result;
    }
    

    function updateTotal($param)
    {
        $result = DB::update(
            'UPDATE aptrpurchasehd SET ttlpb = :total, ppn = :ppn  WHERE purchaseid = :purchaseid ',
            [
                'purchaseid' => $param['purchaseid'],
                'total' => $param['total'],
                'ppn' => $param['ppn']
            ]
        );
        return $result;
    }

    
}