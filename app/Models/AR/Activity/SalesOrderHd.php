<?php

namespace App\Models\AR\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;

use function PHPUnit\Framework\isNull;

class SalesOrderHd extends BaseModel
{
    use HasFactory;

    protected $table = 'artrpurchaseorderhd';

    public $timestamps = false;

    public static $rulesInsert = [
        'custid' => 'required',
        'transdate' => 'required',
        'tglkirim' => 'required',
        'salesid' => 'required'
    ];

    public static $messagesInsert = [
        'custid' => 'Kolom kode pelanggan harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.',
        'tglkirim' => 'Kolom tanggal kirim harus diisi.',
        'salesid' => 'Kolom kode sales harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'soid' => 'required',
        'custid' => 'required',
        'transdate' => 'required',
        'tglkirim' => 'required',
        'salesid' => 'required'
    ];

    public static $messagesUpdate = [
        'soid' => 'nomor so tidak ditemukan.',
        'custid' => 'Kolom kode pelanggan harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.',
        'tglkirim' => 'Kolom tanggal kirim harus diisi.',
        'salesid' => 'Kolom kode sales harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO artrpurchaseorderhd 
            (poid,prid,custid,transdate,note,upddate,upduser,tglkirim,salesid,warehouseid,jenis,fob,fgtax,nilaippn,administrasi,currid,address,attn,telp,fgkomisi,ship,svc,term,hterm,disc,tbagasi) 
            VALUES 
            (:soid,:pocust,:custid,:transdate,:note,getdate(),:upduser,:tglkirim,:salesid,'01GU','T',:fob,:fgtax,:nilaitax,0,:currid,:address,:attn,:telp,'E',:ship,:svc,:term,:termin,:disc,:tb)",
            [
                'soid' => $param['soid'],
                'pocust' => $param['pocust'],
                'custid' => $param['custid'],
                'transdate' => $param['transdate'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'tglkirim' => $param['tglkirim'],
                'salesid' => $param['salesid'],
                'fob' => $param['fob'],
                'fgtax' => $param['fgtax'],
                'nilaitax' => $param['nilaitax'],
                'currid' => $param['currid'],
                'address' => $param['address'],
                'attn' => $param['attn'],
                'telp' => $param['telp'],
                'ship' => $param['ship'],
                'svc' => $param['svc'],
                'term' => $param['term'],
                'termin' => $param['termin'],
                'disc' => $param['disc'],
                'tb' => $param['tb'],
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE artrpurchaseorderhd SET 
            prid = :pocust,
            custid = :custid,
            transdate = :transdate,
            note = :note,
            upddate = getdate(),
            upduser = :upduser,
            tglkirim = :tglkirim,
            salesid = :salesid,
            fob = :fob,
            fgtax = :fgtax,
            nilaippn = :nilaitax,
            currid = :currid,
            address = :address,
            attn = :attn,
            telp = :telp,
            ship = :ship,
            svc = :svc,
            term = :term,
            hterm = :termin,
            disc = :disc
            tbagasi = :tb
            WHERE poid = :soid',
            [
                'soid' => $param['soid'],
                'pocust' => $param['pocust'],
                'custid' => $param['custid'],
                'transdate' => $param['transdate'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'tglkirim' => $param['tglkirim'],
                'salesid' => $param['salesid'],
                'fob' => $param['fob'],
                'fgtax' => $param['fgtax'],
                'nilaitax' => $param['nilaitax'],
                'currid' => $param['currid'],
                'address' => $param['address'],
                'attn' => $param['attn'],
                'telp' => $param['telp'],
                'ship' => $param['ship'],
                'svc' => $param['svc'],
                'term' => $param['term'],
                'termin' => $param['termin'],
                'disc' => $param['disc'],
                'tb' => $param['tb']
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
            "SELECT a.poid as soid,a.transdate,a.tglkirim,a.custid,b.custname,a.salesid,c.salesname,
            isnull(a.term,'') as term,isnull(a.hterm,0) as termin,isnull(a.address,'') as address,isnull(a.ship,'') as ship,
            isnull(a.prid,'') as pocust,isnull(a.note,'') as note,a.fgtax,a.nilaippn as nilaitax,isnull(a.fob,'') as fob,
            a.stso as subtotal,a.ppn as ppn,
            a.ttlso as grandtotal,a.ttlso,
            isnull(round((select sum(x.Qty*(x.Price-x.Modal)) from ARTrPurchaseOrderDt x where x.poid=a.poid),2),0)-isnull(a.svc,0) as margin,
            CASE WHEN A.Jenis='L' THEN 'OVERLIMIT'
            WHEN A.Jenis='T' THEN 'BELUM PROSES'
            WHEN A.Jenis='Y' THEN 'APPROVED'
            WHEN A.Jenis='X' THEN 'REJECTED'
            WHEN A.Jenis='D' THEN 'OVERDUE'
            WHEN A.Jenis='W' THEN 'WAITING APPROVAL ONLY'
            WHEN A.Jenis='R' THEN 'JUAL RUGI'
            WHEN A.Jenis='RD' THEN 'JUAL RUGI & OVERDUE'
            WHEN A.Jenis='RL' THEN 'JUAL RUGI & OVERLIMIT'
            WHEN A.Jenis='DL' THEN 'OVERDUE & OVERLIMIT'
            WHEN A.Jenis='RDL' THEN 'JUAL RUGI, OVERDUE, & OVERLIMIT' END as statusoto,isnull(otoby,'') as otouser,isnull(a.svc,0) as svc,a.attn,a.telp,a.upddate,a.upduser
            from ARTrPurchaseOrderHd a 
            inner join armscustomer b on a.custid=b.custid
            inner join armssales c on a.salesid=c.salesid
            left join armssales d on a.warehouseid=d.salesid
            where convert(varchar(10),a.transdate,112) between :dari and :sampai 
            and isnull(a.custid,'') like :custid and isnull(a.salesid,'') like :salesid and a.poid like :keyword
            order by a.transdate $order",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'custid' => '%' . $param['custid'] . '%',
                'salesid' => '%' . $param['salesid'] . '%',
                'keyword' => '%' . $param['keyword'] . '%',
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.poid as soid,a.transdate,a.tglkirim,a.custid,b.custname,a.salesid,c.salesname,
            isnull(a.term,'') as term,isnull(a.hterm,0) as termin,isnull(a.address,'') as address,isnull(a.ship,'') as ship,
            isnull(a.prid,'') as pocust,isnull(a.note,'') as note,a.fgtax,a.nilaippn as nilaitax,isnull(a.fob,'') as fob,
            a.stso as subtotal,a.ppn as ppn,
            a.ttlso as grandtotal,a.ttlso,
            isnull(round((select sum(x.Qty*(x.Price-x.Modal)) from ARTrPurchaseOrderDt x where x.poid=a.poid),2),0)-isnull(a.svc,0) as margin,
            CASE WHEN A.Jenis='L' THEN 'OVERLIMIT'
            WHEN A.Jenis='T' THEN 'BELUM PROSES'
            WHEN A.Jenis='Y' THEN 'APPROVED'
            WHEN A.Jenis='X' THEN 'REJECTED'
            WHEN A.Jenis='D' THEN 'OVERDUE'
            WHEN A.Jenis='W' THEN 'WAITING APPROVAL ONLY'
            WHEN A.Jenis='R' THEN 'JUAL RUGI'
            WHEN A.Jenis='RD' THEN 'JUAL RUGI & OVERDUE'
            WHEN A.Jenis='RL' THEN 'JUAL RUGI & OVERLIMIT'
            WHEN A.Jenis='DL' THEN 'OVERDUE & OVERLIMIT'
            WHEN A.Jenis='RDL' THEN 'JUAL RUGI, OVERDUE, & OVERLIMIT' END as statusoto,isnull(otoby,'') as otouser,isnull(a.svc,0) as svc,a.attn,a.telp,
            a.upddate,a.upduser
            from ARTrPurchaseOrderHd a 
            inner join armscustomer b on a.custid=b.custid
            inner join armssales c on a.salesid=c.salesid
            left join armssales d on a.warehouseid=d.salesid
            WHERE a.poid = :soid",
            [
                'soid' => $param['soid']
            ]
        );

        return $result;
    }

    function getListSOBlmPO($param)
    {
        $result = DB::select(
            "SELECT k.poid as soid,k.transdate,k.custname from (
            select a.poid,a.transdate,sum(b.qty) as total,a.jenis,c.custname,
            isnull((select sum(x.qty) from artrpenawarandt x inner join artrpenawaranhd y on x.gbuid=y.gbuid and y.flag='B' 
            where y.soid=a.poid),0) as jumpo 
            from artrpurchaseorderhd a inner join artrpurchaseorderdt b on a.poid=b.poid 
            inner join armscustomer c on a.custid=c.custid 
            group by a.poid,a.transdate,a.jenis,c.custname,a.ttlso
            ) as k 
            where k.total-k.jumpo > 0 and k.jenis='Y' 
            and convert(varchar(8),k.transdate,112) <= :transdate
            order by k.poid",
            [
                'transdate' => $param['transdate']
            ]
        );

        return $result;
    }

    function getListOto()
    {
        $result = DB::select(
            "SELECT a.poid as soid,a.custid,B.custname,a.transdate,A.FgTax as fgtax,ISNULL(A.TTLSO,0) as total,
            a.salesid,C.SalesName as salesname,
            CASE WHEN A.Jenis='L' THEN 'OVERLIMIT' 
                 WHEN A.Jenis='D' THEN 'OVERDUE' 
                 WHEN A.Jenis='R' THEN 'JUAL RUGI' 
                 WHEN A.Jenis='W' THEN 'WAITING APPROVAL ONLY' 
                 WHEN A.Jenis='RD' THEN 'JUAL RUGI & OVERDUE' 
                 WHEN A.Jenis='RL' THEN 'JUAL RUGI & OVERLIMIT' 
                 WHEN A.Jenis='DL' THEN 'OVERDUE & OVERLIMIT' 
                 WHEN A.Jenis='RDL' THEN 'JUAL RUGI, OVERDUE, & OVERLIMIT' 
            END as note 
            FROM ARTrPurchaseOrderHd A INNER JOIN ARMsCustomer B ON A.CustID=B.CustID 
            INNER JOIN ARMsSales C ON A.SalesID=C.SalesID WHERE A.Jenis NOT IN ('Y','X','T')  
            ORDER BY CONVERT(VARCHAR(8),Transdate,112),POID"
        );

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM artrpurchaseorderhd WHERE poid = :soid',
            [
                'soid' => $param['soid']
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($transdate)
    {
        $pt = 'GGS/';
        $year = substr($transdate, 0, 6);

        $autoNumber = $this->autoNumber($this->table, 'poid', 'SO-' . $pt . $year . '/', '0000');

        return $autoNumber;
    }

    function cekOtorisasi($param)
    {

        $cekrugi = DB::selectOne(
            "SELECT case when isnull(sum(a.qty*(a.price-a.modal)),0) < 0 then 'R' else '' end as flag 
            from artrpurchaseorderdt a where poid=:soid ",
            [
                'soid' => $param['soid']
            ]
        );

        if (is_null($cekrugi)) {
            $a = '';
        } else {
            $a = $cekrugi->flag;
        }

        $cekoverdue = DB::selectOne(
            "SELECT case when sum(case when convert(varchar(8),dateadd(day,k.term,k.transdate),112) >= :transdate
            then 0 else 1 end) > 0 then 'D' else '' end as flag from (
            select a.transdate,isnull(b.hterm,0) as term,a.custid,isnull(a.ttlpj,0) as total,
            isnull((select isnull(sum(x.valuepayment),0) from artrpiutangdt x inner join artrpiutanghd y on x.piutangid=y.piutangid 
            where x.saleid=a.saleid and y.custid=a.custid 
            and convert(varchar(8),y.transdate,112) <= :transdate1 ),0) as bayar,
            isnull((select sum(qty*price)from artrreturpenjualandt p inner join artrreturpenjualanhd q on p.returnid=q.returnid 
            where p.saleid=a.saleid and q.custid=a.custid),0) as retur 
            from artrpenjualanhd a inner join armscustomer b on a.custid=b.custid) as k 
            where k.custid=:custid and isnull(k.total-k.bayar-k.retur,0) > 1 ",
            [
                'custid' => $param['custid'],
                'transdate' => $param['transdate'],
                'transdate1' => $param['transdate']
            ]
        );

        if (is_null($cekoverdue)) {
            $b = '';
        } else {
            $b = $cekoverdue->flag;
        }

        $ceklimit = DB::selectOne(
            "SELECT case when isnull(h.limitpiutang,0)-isnull(sum(l.piutang),0)-
            isnull((select m.ttlso from artrpurchaseorderhd m where m.poid=:soid),0) < 0 then 'L' else '' end as flag
            from (
            select k.custid,case when k.currid='idr' then isnull(sum(k.ttlpj-k.bayar),0) else 
            isnull(sum(k.ttlpj-k.bayar),0) * (select top 1 rate from satrrate order by convert(varchar(8),transdate,112) desc) end as piutang 
            from (
            select a.saleid,a.currid,a.ttlpj,a.custid,
            (select isnull(sum(e.valuepayment),0) from artrpiutangdt e inner join artrpiutanghd f on e.piutangid=f.piutangid 
            where f.custid=a.custid and f.currid=a.currid and e.saleid=a.saleid) as bayar from artrpenjualanhd a) as k 
            group by k.custid,k.currid) as l inner join armscustomer h on l.custid=h.custid 
            where l.custid=:custid group by l.custid,h.limitpiutang ",
            [
                'soid' => $param['soid'],
                'custid' => $param['custid']
            ]
        );

        if (is_null($ceklimit)) {
            $c = '';
        } else {
            $c = $ceklimit->flag;
        }

        $result = $a . $b . $c;

        if ($result == '') {
            $result = 'W';
        }

        return $result;
    }

    function hitungTotal($param)
    {

        $result = DB::selectONe(
            "SELECT isnull(sum(qty*price),0) as subtotal,
            sum(qty*price)*isnull(b.disc,0)/100 as disc,
            case when b.fgtax='y' then isnull((sum(qty*price)-(sum(qty*price)*isnull(b.disc,0)/100))*b.nilaippn/100,0) else 0 end as ppn,
            case when b.fgtax='y' then isnull((sum(qty*price)-(sum(qty*price)*isnull(b.disc,0)/100))*(1+(b.nilaippn/100)) ,0) 
            else isnull(sum(qty*price)-(sum(qty*price)*isnull(b.disc,0)/100),0) end as total,
            isnull(sum(qty*price)*1.1,0)+isnull(b.administrasi,0) as grandtotal,
            isnull(sum(qty*(price-modal))-(sum(qty*price)*isnull(b.disc,0)/100)-b.svc,0) as margin,
            case when sum(qty*modal) = 0 then 100 else ((sum(qty*(price-modal))-(sum(qty*price)*isnull(b.disc,0)/100)-b.svc)/sum(qty*modal)*100) end as pmargin
            from artrpurchaseorderdt a
            inner join artrpurchaseorderhd b on a.poid=b.poid
            where a.poid=:soid
            group by b.administrasi,b.fgtax,b.svc,b.nilaippn,b.disc
            ",
            [
                'soid' => $param['soid']
            ]
        );

        return $result;
    }

    function updateTotal($param)
    {
        $result = DB::update(
            'UPDATE artrpurchaseorderhd SET ttlso = :grandtotal,stso = :subtotal,ppn = :ppn WHERE poid = :soid',
            [
                'soid' => $param['soid'],
                'grandtotal' => $param['grandtotal'],
                'subtotal' => $param['subtotal'],
                'ppn' => $param['ppn'],
            ]
        );

        return $result;
    }

    function updateJenis($param)
    {
        $result = DB::update(
            'UPDATE artrpurchaseorderhd SET jenis = :jenis WHERE poid = :soid',
            [
                'soid' => $param['soid'],
                'jenis' => $param['jenis']
            ]
        );

        return $result;
    }

    function cekSalesorder($soid)
    {
        $result = DB::selectOne(
            'SELECT * from artrpurchaseorderhd WHERE poid = :soid',
            [
                'soid' => $soid
            ]
        );

        return $result;
    }

    function cekBolehEdit($soid)
    {
        $result = DB::selectOne(
            "SELECT top 1 k.saleid,k.soid from (
             select saleid,soid from artrpenjualanhd union all 
             select gbuid,soid from artrpenawaranhd 
             ) as k 
             where k.soid=:soid ",
            [
                'soid' => $soid
            ]
        );

        return $result;
    }
}
