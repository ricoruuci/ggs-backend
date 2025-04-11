<?php

namespace App\Models\CF\Report; //1

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class RptNeraca extends BaseModel 
{
    use HasFactory;

    public $timestamps = false;

    function getRptNeraca($param)
    {

        $addquery = $this->queryAccounting();

        $result = DB::select(
            "SELECT k.fgtipe,k.subkomponen as keterangan,l.total as total from 
            (
                select 'A' as fgtipe,'TOTAL AKTIVA' as subkomponen union all 
                select 'P','TOTAL PASSIVA' 
            ) as K 
            inner join 
            (
            select p.fgtipe,case when p.fgtipe='a' then sum(p.jumlah) else sum(p.jumlah*-1) end as total from (
            select y.grouprekid,case when y.tipe in (4,5) then 3 else y.tipe end as tipe,y.fgtipe,
            isnull((select sum(case when x.jenis='d' then x.amount else x.amount*-1 end) from (
            $addquery
            ) as x 
            where x.rekeningid=y.rekeningid and convert(varchar(8),x.transdate,112) <= :periode),0) as jumlah 
            from cfmsrekening y
            ) as P group by p.fgtipe 
            ) as L on k.fgtipe=l.fgtipe 
            order by k.fgtipe",
            [
                'periode' => $param['periode']
            ]
        );

        foreach ($result as $row) {

            $cekdata = DB::select(
                "SELECT K.fgtipe,K.SubKomponen as komponen,K.Tipe as tipe,L.Total as total FROM 
                (
                SELECT 'A' as FgTipe,'CURRENT ASSET' as SubKomponen,1 as Tipe UNION ALL 
                SELECT 'A','FIXED ASSET',6 UNION ALL 
                SELECT 'A','INTER ACCOUNT',7 UNION ALL 
                SELECT 'P','LIABILITIES',2 UNION ALL 
                SELECT 'P','CAPITAL',3
                ) as K 
                INNER JOIN 
                (
                SELECT P.Tipe,P.FgTipe,CASE WHEN P.FgTipe='A' THEN SUM(P.Jumlah) ELSE SUM(P.Jumlah*-1) END as Total FROM (
                SELECT Y.GroupRekID,CASE WHEN Y.Tipe IN (4,5) THEN 3 ELSE Y.Tipe END as Tipe,Y.FgTipe,
                ISNULL((SELECT SUM(CASE WHEN X.Jenis='D' THEN X.Amount ELSE X.Amount*-1 END) FROM (
                $addquery
                ) as X 
                WHERE X.RekeningID=Y.RekeningID AND CONVERT(VARCHAR(8),X.Transdate,112) <= :periode),0) as Jumlah 
                FROM CFMsRekening Y
                ) as P GROUP BY P.Tipe,P.FgTipe
                ) as L ON K.Tipe=L.Tipe AND K.FgTipe=L.FgTipe 
                WHERE K.FgTipe=:fgtipe 
                ORDER BY K.FgTipe,K.Tipe",
                [
                    'periode' => $param['periode'],
                    'fgtipe' => $row->fgtipe,
                ]
            );
        
            foreach ($cekdata as $hasil)
            {
            
                $cekdetail = DB::select(
                    "SELECT X.GroupRekID as grouprekid,X.GroupRekName as grouprekname,X.Total as amount FROM (
                    SELECT P.GroupRekID,Q.GroupRekName,CASE WHEN P.FgTipe='A' THEN SUM(P.Jumlah) ELSE SUM(P.Jumlah*-1) END as Total FROM (
                    SELECT CASE WHEN Y.Tipe IN (4,5) THEN (select DGK from SAMsSet) ELSE Y.GroupRekID END as GroupRekID,
                    CASE WHEN Y.Tipe IN (4,5) THEN 3 ELSE Y.Tipe END as Tipe,Y.FgTipe,
                    ISNULL((SELECT SUM(CASE WHEN X.Jenis='D' THEN X.Amount ELSE X.Amount*-1 END) FROM (
                    $addquery
                    ) as X 
                    WHERE X.RekeningID=Y.RekeningID AND CONVERT(VARCHAR(8),X.Transdate,112) <= :periode),0) as Jumlah 
                    FROM CFMsRekening Y) as P INNER JOIN CFMsGroupRek Q ON P.GroupRekID=Q.GroupRekID 
                    WHERE P.Tipe=:tipe AND P.FgTipe=:fgtipe
                    GROUP BY P.GroupRekID,Q.GroupRekName,P.FgTipe ) as X WHERE X.Total <> 0 ORDER BY X.GroupRekID ",
                    [
                        'periode' => $param['periode'],
                        'tipe' => $hasil->tipe,
                        'fgtipe' => $hasil->fgtipe
                    ]
                );
                // dd(var_dump("SELECT X.GroupRekID as grouprekid,X.GroupRekName as grouprekname,X.Total as amount FROM (
                //     SELECT P.GroupRekID,Q.GroupRekName,CASE WHEN P.FgTipe='A' THEN SUM(P.Jumlah) ELSE SUM(P.Jumlah*-1) END as Total FROM (
                //     SELECT CASE WHEN Y.Tipe IN (4,5) THEN (select DGK from SAMsSet) ELSE Y.GroupRekID END as GroupRekID,
                //     CASE WHEN Y.Tipe IN (4,5) THEN 3 ELSE Y.Tipe END as Tipe,Y.FgTipe,
                //     ISNULL((SELECT SUM(CASE WHEN X.Jenis='D' THEN X.Amount ELSE X.Amount*-1 END) FROM (
                //     $addquery
                //     ) as X 
                //     WHERE X.RekeningID=Y.RekeningID AND CONVERT(VARCHAR(8),X.Transdate,112) <= :periode),0) as Jumlah 
                //     FROM CFMsRekening Y) as P INNER JOIN CFMsGroupRek Q ON P.GroupRekID=Q.GroupRekID 
                //     WHERE P.Tipe=:tipe AND P.FgTipe=:fgtipe
                //     GROUP BY P.GroupRekID,Q.GroupRekName,P.FgTipe ) as X WHERE X.Total <> 0 ORDER BY X.GroupRekID "));

                $hasil->detail = $cekdetail;
                
            }

            $row->header = $cekdata;
        }

        return $result;
    }

}

?>