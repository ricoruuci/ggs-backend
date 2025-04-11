<?php

namespace App\Models\CF\Report; //1

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class RptLabaRugi extends BaseModel 
{
    use HasFactory;

    public $timestamps = false;

    function getRptLabaRugi($param)
    {

        $addquery = $this->queryAccounting();

        $result = DB::select(
            "SELECT isnull(sum(k.jumlah),0) as laba from ( 
            select a.rekeningid,a.rekeningname,b.kode,
            isnull((select sum(case when x.jenis='k' then x.amount else x.amount*-1 end) from (
            $addquery
            ) as x 
            where convert(varchar(8),x.transdate,112) between :dari and :sampai
            and x.rekeningid=a.rekeningid),0) as jumlah
            from cfmsrekening a 
            inner join cfmsgrouprek b on a.grouprekid=b.grouprekid 
            ) as k 
            where k.kode in (4,5) and k.jumlah<>0 ",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai']
            ]
        );

        foreach ($result as $row) {
            
            $cekdata = DB::select(
                "SELECT k.kode,case when k.kode in (4) then 'PENDAPATAN' else 'PEMBELIAN & PENGELUARAN' end as keterangan,isnull(sum(case when k.kode=4 then k.jumlah else k.jumlah*-1 end),0) as total
                from ( 
                select a.rekeningid,a.rekeningname,b.kode,
                isnull((select sum(case when x.jenis='k' then x.amount else x.amount*-1 end) from (
                $addquery
                ) as x 
                where convert(varchar(8),x.transdate,112) between :dari and :sampai 
                and x.rekeningid=a.rekeningid),0) as jumlah
                from cfmsrekening a 
                inner join cfmsgrouprek b on a.grouprekid=b.grouprekid 
                ) as k 
                where k.kode in (4,5) and k.jumlah<>0 group by k.kode order by k.kode",
                [
                    'dari' => $param['dari'],
                    'sampai' => $param['sampai']
                ]
            );

            foreach ($cekdata as $baris) {
            
                $cekdetail = DB::select(
                    "SELECT k.kode,k.rekeningid as grouprekid,k.rekeningname as grouprekname,isnull(sum(case when k.kode=4 then k.jumlah else k.jumlah*-1 end),0) as jumlah 
                    from (  
                    select b.kode,a.rekeningid,a.rekeningname,isnull((select sum(case when x.jenis='k' then x.amount else x.amount*-1 end) from (
                    $addquery
                    ) as x 
                    where convert(varchar(8),x.transdate,112) between :dari and :sampai
                    and x.rekeningid=a.rekeningid),0) as jumlah
                    from cfmsrekening a 
                    inner join cfmsgrouprek b on a.grouprekid=b.grouprekid 
                    ) as k 
                    where k.kode=:kode and k.kode in (4,5) and k.jumlah<>0 
                    group by k.kode,k.rekeningid,k.rekeningname 
                    order by k.kode,k.rekeningid",
                    [
                        'dari' => $param['dari'],
                        'sampai' => $param['sampai'],
                        'kode' => $baris->kode
                    ]
                );
    
                $baris->detail = $cekdetail;
            }

            $row->komponen = $cekdata;
        }

        return $result;
    }

}

?>