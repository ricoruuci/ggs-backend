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
            (poid,prid,custid,transdate,note,upddate,upduser,tglkirim,salesid,warehouseid,jenis,fob,fgtax,nilaippn,administrasi,currid,address,attn,telp,fgkomisi,ship,svc,term,hterm,disc,tbagasi,fgclose) 
            VALUES 
            (:soid,:pocust,:custid,:transdate,:note,getdate(),:upduser,:tglkirim,:salesid,'01GU','T',:fob,:fgtax,:nilaitax,0,:currid,:address,:attn,:telp,'E',:ship,:svc,:term,:termin,:disc,:tb,'T')",
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
            disc = :disc,
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
            isnull(a.prid,'') as pocust,isnull(a.note,'') as note,isnull(a.fgtax,'Y') as fgtax,a.nilaippn as nilaitax,isnull(a.fob,'') as fob,
            isnull(a.stso,0) as subtotal,isnull(a.ppn,0) as ppn,
            isnull(a.ttlso,0) as grandtotal,isnull(a.ttlso,0) as ttlso,
            ISNULL(ROUND((
            (
                SELECT SUM(x.Qty * (x.Price - x.Modal))
                FROM ARTrPurchaseOrderDt x
                WHERE x.poid = a.poid
            ) - ISNULL(a.Disc, 0) - a.TBagasi
            ), 2), 0) AS margin,
                        ISNULL(ROUND(
            (
                SELECT 
                CASE 
                    WHEN SUM(x.qty * x.modal) = 0 THEN 100 
                    ELSE 
                    (SUM(x.Qty * (x.Price - x.Modal)) - ISNULL(a.Disc, 0) - a.TBagasi) 
                    / NULLIF(SUM(x.qty * x.modal), 0) * 100
                END
                FROM ARTrPurchaseOrderDt x  
                WHERE x.poid = a.poid
            ), 2
            ), 0) AS pmargin,

            CASE WHEN A.Jenis='L' THEN 'OVERLIMIT'
            WHEN A.Jenis='T' THEN 'BELUM PROSES'
            WHEN A.Jenis='Y' THEN 'APPROVED'
            WHEN A.Jenis='X' THEN 'REJECTED'
            WHEN A.Jenis='O' THEN 'OVERDUE'
            WHEN A.Jenis='W' THEN 'WAITING APPROVAL ONLY'
            WHEN A.Jenis='G' THEN 'JUAL RUGI'
            WHEN A.Jenis='OG' THEN 'JUAL RUGI & OVERDUE'
            WHEN A.Jenis='GL' THEN 'JUAL RUGI & OVERLIMIT'
            WHEN A.Jenis='OL' THEN 'OVERDUE & OVERLIMIT'
            WHEN A.Jenis='OLG' THEN 'JUAL RUGI, OVERDUE, & OVERLIMIT' END as statusoto,isnull(otoby,'') as otouser,isnull(a.svc,0) as svc,
			
			isnull(a.attn,'') as attn,isnull(a.telp,'') as telp,a.upddate,a.upduser,a.fgclose,
            Case when a.fgclose='Y' then 'CLOSED' else 'OPEN' end as statusclose,a.closeby,a.closedate
            from ARTrPurchaseOrderHd a 
            inner join armscustomer b on a.custid=b.custid
            inner join armssales c on a.salesid=c.salesid
            left join armssales d on a.warehouseid=d.salesid
            where convert(varchar(10),a.transdate,112) between :dari and :sampai 
            and isnull(a.custid,'') like :custidkeyword
            and isnull(b.custname,'') like :custnamekeyword
            and isnull(a.salesid,'') like :salesidkeyword
            and isnull(c.salesname,'') like :salesnamekeyword 
            and a.poid like :sokeyword
            order by a.transdate $order",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'custidkeyword' => '%' . $param['custidkeyword'] . '%',
                'custnamekeyword' => '%' . $param['custnamekeyword'] . '%',
                'salesidkeyword' => '%' . $param['salesidkeyword'] . '%',
                'salesnamekeyword' => '%' . $param['salesnamekeyword'] . '%',
                'sokeyword' => '%' . $param['sokeyword'] . '%'
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.poid as soid,a.transdate,a.tglkirim,a.custid,b.custname,a.salesid,c.salesname,
            isnull(a.term,'') as term,isnull(a.hterm,0) as termin,isnull(a.address,'') as address,isnull(a.ship,'') as ship,
            isnull(a.prid,'') as pocust,isnull(a.note,'') as note,isnull(a.fgtax,'Y') as fgtax,a.nilaippn as nilaitax,isnull(a.fob,'') as fob,
            isnull(a.stso,0) as subtotal,isnull(a.ppn,0) as ppn,
            isnull(a.ttlso,0) as grandtotal,isnull(a.ttlso,0) as ttlso,
            ISNULL(ROUND((
            (
                SELECT SUM(x.Qty * (x.Price - x.Modal))
                FROM ARTrPurchaseOrderDt x
                WHERE x.poid = a.poid
            ) - ISNULL(a.Disc, 0) - a.TBagasi
            ), 2), 0) AS margin,
                        ISNULL(ROUND(
            (
                SELECT 
                CASE 
                    WHEN SUM(x.qty * x.modal) = 0 THEN 100 
                    ELSE 
                    (SUM(x.Qty * (x.Price - x.Modal)) - ISNULL(a.Disc, 0) - a.TBagasi) 
                    / NULLIF(SUM(x.qty * x.modal), 0) * 100
                END
                FROM ARTrPurchaseOrderDt x  
                WHERE x.poid = a.poid
            ), 2
            ), 0) AS pmargin,

            CASE WHEN A.Jenis='L' THEN 'OVERLIMIT'
            WHEN A.Jenis='T' THEN 'BELUM PROSES'
            WHEN A.Jenis='Y' THEN 'APPROVED'
            WHEN A.Jenis='X' THEN 'REJECTED'
            WHEN A.Jenis='O' THEN 'OVERDUE'
            WHEN A.Jenis='W' THEN 'WAITING APPROVAL ONLY'
            WHEN A.Jenis='G' THEN 'JUAL RUGI'
            WHEN A.Jenis='OG' THEN 'JUAL RUGI & OVERDUE'
            WHEN A.Jenis='GL' THEN 'JUAL RUGI & OVERLIMIT'
            WHEN A.Jenis='OL' THEN 'OVERDUE & OVERLIMIT'
            WHEN A.Jenis='OLG' THEN 'JUAL RUGI, OVERDUE, & OVERLIMIT' END as statusoto,isnull(otoby,'') as otouser,isnull(a.svc,0) as svc,
			
			isnull(a.attn,'') as attn,isnull(a.telp,'') as telp,a.upddate,a.upduser,a.fgclose,
            Case when a.fgclose='Y' then 'CLOSED' else 'OPEN' end as statusclose,a.closeby,a.closedate
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
            select a.poid,a.transdate,sum(b.qty) as total,a.jenis,isnull(a.fgclose,'T') as fgclose,c.custname,
            isnull((select sum(x.qty) from artrpenawarandt x inner join artrpenawaranhd y on x.gbuid=y.gbuid and y.flag='B' 
            where y.soid=a.poid),0) as jumpo 
            from artrpurchaseorderhd a inner join artrpurchaseorderdt b on a.poid=b.poid 
            inner join armscustomer c on a.custid=c.custid 
            group by a.poid,a.transdate,a.jenis,c.custname,a.ttlso,a.fgclose
            ) as k 
            where k.total-k.jumpo > 0 and k.jenis='Y' and k.fgclose='T'
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
            "SELECT a.poid as soid,a.transdate,a.tglkirim,a.custid,b.custname,a.salesid,c.salesname,
            isnull(a.term,'') as term,isnull(a.hterm,0) as termin,isnull(a.address,'') as address,isnull(a.ship,'') as ship,
            isnull(a.prid,'') as pocust,isnull(a.note,'') as note,isnull(a.fgtax,'Y') as fgtax,a.nilaippn as nilaitax,isnull(a.fob,'') as fob,
            isnull(a.stso,0) as subtotal,isnull(a.ppn,0) as ppn,
            isnull(a.ttlso,0) as grandtotal,isnull(a.ttlso,0) as ttlso,
            ISNULL(ROUND((
            (
                SELECT SUM(x.Qty * (x.Price - x.Modal))
                FROM ARTrPurchaseOrderDt x
                WHERE x.poid = a.poid
            ) - ISNULL(a.Disc, 0) - a.TBagasi
            ), 2), 0) AS margin,
                        ISNULL(ROUND(
            (
                SELECT 
                CASE 
                    WHEN SUM(x.qty * x.modal) = 0 THEN 100 
                    ELSE 
                    (SUM(x.Qty * (x.Price - x.Modal)) - ISNULL(a.Disc, 0) - a.TBagasi) 
                    / NULLIF(SUM(x.qty * x.modal), 0) * 100
                END
                FROM ARTrPurchaseOrderDt x  
                WHERE x.poid = a.poid
            ), 2
            ), 0) AS pmargin,

            CASE WHEN A.Jenis='L' THEN 'OVERLIMIT'
            WHEN A.Jenis='T' THEN 'BELUM PROSES'
            WHEN A.Jenis='Y' THEN 'APPROVED'
            WHEN A.Jenis='X' THEN 'REJECTED'
            WHEN A.Jenis='O' THEN 'OVERDUE'
            WHEN A.Jenis='W' THEN 'WAITING APPROVAL ONLY'
            WHEN A.Jenis='G' THEN 'JUAL RUGI'
            WHEN A.Jenis='OG' THEN 'JUAL RUGI & OVERDUE'
            WHEN A.Jenis='GL' THEN 'JUAL RUGI & OVERLIMIT'
            WHEN A.Jenis='OL' THEN 'OVERDUE & OVERLIMIT'
            WHEN A.Jenis='OLG' THEN 'JUAL RUGI, OVERDUE, & OVERLIMIT' END as statusoto,isnull(otoby,'') as otouser,isnull(a.svc,0) as svc,
			
			isnull(a.attn,'') as attn,isnull(a.telp,'') as telp,a.upddate,a.upduser
            from ARTrPurchaseOrderHd a 
            inner join armscustomer b on a.custid=b.custid
            inner join armssales c on a.salesid=c.salesid
            left join armssales d on a.warehouseid=d.salesid
            where A.Jenis NOT IN ('Y','X','T')  and a.fgclose='T'
            ORDER BY a.transdate ,A.POID"
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
        $cekoverdue = DB::selectOne(
            "SELECT case when sum(case when convert(varchar(8),dateadd(day,k.term,k.transdate),112) >= :transdate
            then 0 else 1 end) > 0 then 'O' else '' end as flag from (
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
            $a = '';
        } else {
            $a = $cekoverdue->flag;
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
            $b = '';
        } else {
            $b = $ceklimit->flag;
        }




        $cekrugi = DB::selectOne(
            "SELECT case when isnull(sum(a.qty*(a.price-a.modal)),0) < 0 then 'G' else '' end as flag 
            from artrpurchaseorderdt a where poid=:soid ",
            [
                'soid' => $param['soid']
            ]
        );

        if (is_null($cekrugi)) {
            $c = '';
        } else {
            $c = $cekrugi->flag;
        }

        $result = $a . $c . $b  ;

        if ($result == '') {
            $result = 'W';
        }

       //dd($result) ;

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
            'UPDATE artrpurchaseorderhd SET ttlso = :grandtotal,stso = :subtotal,ppn = :ppn,margin = :margin WHERE poid = :soid',
            [
                'soid' => $param['soid'],
                'grandtotal' => $param['grandtotal'],
                'subtotal' => $param['subtotal'],
                'ppn' => $param['ppn'],
                'margin' => $param['margin']
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

    function cekOtoLevel($userid)
    {
        $cek = DB::selectOne(
            "SELECT userid,isnull(fglevel,'T') as fglevel from sysmsuser where userid=:userid ",
            [
                'userid' => $userid
            ]
        );

        if ($cek->fglevel == 'Y') {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    function cekOtoMargin($userid)
    {
        $result = DB::selectOne(
            "SELECT isnull(otomargin,2) as otomargin from sysmsuser where userid=:userid ",
            [
                'userid' => $userid
            ]
        );

        return $result;
    }

    function cekMargin($soid,$userid)
    {
         $cekUser = DB::selectOne(
            "SELECT isnull(otomargin,2) as otomargin from sysmsuser where userid=:userid ",
            [
                'userid' => $userid
            ]
        );

        $cek = DB::selectOne(
            "SELECT  case when sum(qty*modal) = 0 then 100 else ((sum(qty*(price-modal))-(sum(qty*price)*isnull(a.disc,0)/100)-a.tbagasi)/sum(qty*modal)*100) end as pmargin
	        from artrpurchaseorderhd a inner join artrpurchaseorderdt b 
            on a.poid=b.poid 
            where a.poid=:soid
            group by a.fgkomisi,a.fgtax,a.tbagasi,a.svc,a.disc ",
            [
                'soid' => $soid
            ]
        );

        if ($cek) {
    if ($cek->pmargin < 0) {
        // Kalau margin minus, otomatis gagal
        $result = false;
    } elseif ($cek->pmargin < $cekUser->otomargin) {
        // Kalau margin lebih kecil dari otomargin, gagal juga
        $result = false;
    } else {
        // Kalau margin cukup, lolos
        $result = true;
    }
    } else {
        $result = false;
    }

        return $result;
    }

    function closeSO($param)
    {
        $result = DB::update(
            'UPDATE artrpurchaseorderhd SET fgclose = :fgclose, closeby = :closeby, closedate = getdate() WHERE poid = :soid',
            [
                'soid' => $param['soid'],
                'fgclose' => $param['fgclose'],
                'closeby' => $param['closeby']
            ]
        );

        return $result;
    }
}