<?php

namespace App\Models\CF\Report; //1

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class RptBukuBesar extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    function getRptBukuBesar($param)
    {
        if(isset($param['rekeningid']))
        {
            $addCon = "and K.rekeningid='".$param['rekeningid']."'";
        }
        else
        {
            $addCon = '';
        }

        $addquery = $this->queryAccounting();

        $result = DB::select(
            "SELECT l.tipe,k.rekeningid,l.grouprekid,l.rekeningname,
            isnull(sum(case when convert(varchar(8),k.transdate,112) < :dari1 then (case when k.currid='idr' then k.amount else k.amount*k.rate end)*(case when k.jenis='d' then 1 else -1 end) else 0 end),0) as awal,
            isnull(sum(case when convert(varchar(8),k.transdate,112) between :dari2 and :sampai1 then (case when k.currid='idr' then k.amount else k.amount*k.rate end)*(case when k.jenis='d' then 1 else 0 end) else 0 end),0) as debet,
            isnull(sum(case when convert(varchar(8),k.transdate,112) between :dari3 and :sampai2 then (case when k.currid='idr' then k.amount else k.amount*k.rate end)*(case when k.jenis='k' then 1 else 0 end) else 0 end),0) as kredit,
            isnull(sum(case when convert(varchar(8),k.transdate,112) <= :sampai3 then (case when k.currid='idr' then k.amount else k.amount*k.rate end)*(case when k.jenis='d' then 1 else -1 end) else 0 end),0) as akhir
            from (
            $addquery
            ) as k
            inner join cfmsrekening l on k.rekeningid=l.rekeningid
            where convert(varchar(8),k.transdate,112) <= :sampai and k.amount<> 0 --and k.fgpayment='t' 
            $addCon
            group by l.tipe,k.rekeningid,l.rekeningname,l.grouprekid
            order by l.tipe,l.grouprekid,k.rekeningid",
            [
                'dari1' => $param['dari'],
                'dari2' => $param['dari'],
                'dari3' => $param['dari'],
                'sampai1' => $param['sampai'],
                'sampai2' => $param['sampai'],
                'sampai3' => $param['sampai'],
                'sampai' => $param['sampai']
            ]
        );

        foreach ($result as $row) {
            $endBalance = $row->awal;

            $cekdata = DB::select(
                "SELECT k.transdate,k.voucherid,isnull(k.note,'') as keterangan,case when k.currid='idr' then k.amount else k.amount*k.rate end as amount,UPPER(k.jenis) as jenis,k.flagkkbb
                from (
                $addquery
                ) as k
                where convert(varchar(8),k.transdate,112) between :dari and :sampai and k.rekeningid=:rekeningid --and k.fgpayment='t'
                order by k.transdate,k.jenis,k.voucherid",
                [
                    'dari' => $param['dari'],
                    'sampai' => $param['sampai'],
                    'rekeningid' => $row->rekeningid,
                ]
            );

            foreach ($cekdata as $hasil)
            {
                if (strtoupper($hasil->jenis)=='D')
                {
                    $endBalance = $endBalance + $hasil->amount;
                }
                else
                {
                    $endBalance = $endBalance - $hasil->amount;
                }

                $hasil->saldo = strval($endBalance);
            }


            $row->mutasi = $cekdata;
        }

        return $result;
    }

}