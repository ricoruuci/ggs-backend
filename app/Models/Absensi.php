<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class Absensi extends Model
{

    function getOtoAbsen()
    {
        $result = DB::select(
            "SELECT a.transid,b.salesname,
            convert(varchar(10),A.tgldari,103)+' - '+convert(varchar(10),A.tglsmp,103) as periode,
            case when A.fgabsen='L' then 'CUSTOMER VISIT' when A.fgabsen='I' then 'IZIN'
            when A.fgabsen='T' then 'TELAT/MASUK SIANG' when A.fgabsen='S' then 'SAKIT'
            when A.fgabsen='D' then 'DINAS' when A.fgabsen='C' then 'CUTI'
            when A.fgabsen='W' then 'WORK FROM HOME' end as keperluan,A.keterangan,
            case when A.fgsubmit='O' then 'OFFICE' else 'MOBILE' end as submit,A.foto
            from trreqabsen A inner join ARmsSales B on A.salesid=B.Salesid where fgoto='T' "
        );

        // $result = DB::select(
        //     "SELECT a.transid,b.salesname,
        //     convert(varchar(10),A.tgldari,103)+' - '+convert(varchar(10),A.tglsmp,103) as periode,
        //     case when A.fgabsen='L' then 'CUSTOMER VISIT' when A.fgabsen='I' then 'IZIN'
        //     when A.fgabsen='T' then 'TELAT/MASUK SIANG' when A.fgabsen='S' then 'SAKIT'
        //     when A.fgabsen='D' then 'DINAS' when A.fgabsen='C' then 'CUTI'
        //     when A.fgabsen='W' then 'WORK FROM HOME' end as keperluan,A.keterangan,
        //     case when A.fgsubmit='O' then 'OFFICE' else 'MOBILE' end as submit,A.foto
        //     from trreqabsen A inner join ARmsSales B on A.salesid=B.Salesid where fgoto='T' "
        // );

        return $result;
    }

    function postOtoAbsen($param)
    {
        $getsales = DB::selectOne(
            "SELECT salesid,fgabsen,
            convert(varchar(10),transdate,112) as tanggal,
            convert(varchar(10),tgldari,112) as dari,
            convert(varchar(10),tglsmp,112) as sampai
            from TrReqAbsen where transid=:transid ",
            [
                'transid' => $param['transid']
            ]
        );

        $salesid = $getsales->salesid;
        $tanggal = $getsales->tanggal;
        $fgabsen = $getsales->fgabsen;
        $dari = $getsales->dari;
        $sampai = $getsales->sampai;

        $result = DB::statement(
            "IF NOT EXISTS ( SELECT * from trabsensi where salesid=:salesid and convert(varchar(10),tanggal,112)=:tanggal )

            INSERT into trabsensi
            (salesid,tanggal,jammasuk,jamkeluar,fgoff,fgkeluar,fgabsen,keterangan)
            select A.salesid,A.transdate,A.transdate,null,0,'T',A.fgabsen,A.keterangan
            from trreqabsen A
            where transid=:transid1
            else
            update TrAbsensi set fgabsen=:fgabsen where convert(varchar(10),Tanggal,112) between :dari and :sampai and SalesID=:salesid1 ",
            [
                'transid1' => $param['transid'],
                'salesid' => $salesid,
                'salesid1' => $salesid,
                'tanggal' => $tanggal,
                'fgabsen' => $fgabsen,
                'dari' => $dari,
                'sampai' => $sampai
            ]
        );

        return $result;
    }

    function updateReqAbsen($param)
    {
        $result = DB::update(
            "UPDATE trreqabsen set fgoto=:jenis,otoby=:upduser,otodate=getdate() where transid=:transid ",
            [
                'transid' => $param['transid'],
                'upduser' => $param['upduser'],
                'jenis' => $param['jenis']
            ]
        );

        return $result;
    }

    public function insertAbsen($param)
    {

        $getsales = DB::selectOne(
            "SELECT salesid from sysmsuser where userid=:userid ",
            [
                'userid' => $param['userid']
            ]
        );

        $salesid = $getsales->salesid;

        

        // $tanggalHariIni = getdate(); 
        // $tanggalHariIni = date("Y-m-d H:i:s");
        $tahun = date("Y");

        $result = DB::insert(
            "INSERT trreqabsen (transid,salesid,tgldari,tglsmp,fgabsen,upddate,upduser,fgoto,transdate,keterangan,fgsubmit,foto)
             select
             case when k.jumlah<10 then :tahun1+:salesid3+'00'+convert(varchar(10), jumlah+1)
                  when k.jumlah<100 then :tahun2+:salesid4+'0'+convert(varchar(10), jumlah+1)
                  else :tahun3+:salesid5+convert(varchar(10), jumlah+1) end as transid,
            --  :salesid1,:tanggal1,:tanggal2,:fgabsen,getdate(),:userid1,'T',:tanggal3,:note,'M',:foto
             :salesid1,getdate(),getdate(),:fgabsen,getdate(),:userid1,'T',getdate(),:note,'M',:foto
             from (
             select isnull(count(*),0) as jumlah from trreqabsen a
             where a.fgsubmit='M' and a.salesid=:salesid2 and left(convert(varchar(10),a.transdate,112),4)=:tahun4
             ) as k",
            [
                'userid1' => $param['userid'],
                'fgabsen' => $param['keperluan'],
                'note' => $param['keterangan'],
                'salesid1' => $salesid,
                'salesid2' => $salesid,
                'salesid3' => $salesid,
                'salesid4' => $salesid,
                'salesid5' => $salesid,
                // 'tanggal1' => $tanggalHariIni,
                // 'tanggal2' => $tanggalHariIni,
                // 'tanggal3' => $tanggalHariIni,
                'foto' => $param['foto'],
                'tahun1' => $tahun,
                'tahun2' => $tahun,
                'tahun3' => $tahun,
                'tahun4' => $tahun,
            ]
        );

        return $result;
    }

    function cekAbsen($sales)
    {
        // dd(var_dump('aaaa'));
        $getsales = DB::select(
            "SELECT salesid from sysmsuser where userid=:userid ",
            [
                'userid' => $sales,
            ]
        );

        $salesid = $getsales[0]->salesid;

        // dd(var_dump($salesid));


        $result = DB::selectOne(
            'SELECT * FROM TrReqAbsen
            WHERE salesid = :salesid
            AND CONVERT(varchar(11), transdate, 112) = CONVERT(varchar(11), GETDATE(), 112)',
            [
                'salesid' => $salesid
            ]
        );


        return $result;
    }

    public function getLaporanAbsen($param)
    {
        // Ambil kdgroup dan salesid user
        $cek = DB::selectOne(
            "SELECT kdgroup, salesid FROM sysmsuser WHERE userid = :userid",
            ['userid' => $param['userid']]
        );

        $bindings = [
            'dari' => $param['dari'],
            'sampai' => $param['sampai']
        ];

        // Cek apakah user bukan admin/manager
        $addCon = '';
        if (
            isset($cek->kdgroup) &&
            !in_array(strtolower($cek->kdgroup), ['admin', 'manager'])
        ) {
            $addCon = "AND a.salesid = :salesid";
            $bindings['salesid'] = $cek->salesid;
        }

        $query = "SELECT 
            a.salesid, b.salesname, a.transdate,
            ISNULL(CONVERT(varchar(5), a.tgldari, 108), '') AS jammasuk,
            ISNULL(CONVERT(varchar(5), a.tglsmp, 108), '') AS jamkeluar,a.keterangan,a.foto
        FROM TrReqAbsen a
        LEFT JOIN ARmsSales b ON a.salesid = b.salesid
        WHERE CONVERT(varchar(10), a.transdate, 112) BETWEEN :dari AND :sampai
        $addCon
        ORDER BY a.transdate
        ";
        /*
        $query = "
        SELECT 
            a.salesid, b.salesname, a.tanggal,
            ISNULL(CONVERT(varchar(5), a.jammasuk, 108), '') AS jammasuk,
            ISNULL(CONVERT(varchar(5), a.jamkeluar, 108), '') AS jamkeluar 
        FROM trabsensi a
        LEFT JOIN ARmsSales b ON a.salesid = b.salesid
        WHERE CONVERT(varchar(10), a.tanggal, 112) BETWEEN :dari AND :sampai
        $addCon
        ORDER BY a.tanggal
    ";
    */

        $result = DB::select($query, $bindings);

        return $result;
    }
}
