<?php

namespace App\Models\AR\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class ARRptRekapSO extends Model
{
    function laporanSO($param)
    {

        $result = DB::select(
            "SELECT a.poid as soid,a.transdate,d.custname,e.salesname,b.itemid,c.itemname,isnull(b.qty,0) as qty,isnull(b.qty*b.price,0) as price, 
            count(g.gbuid) as jumlahpo,  isnull(sum(g.qty*g.price),0) as modal,isnull(sum(b.price-g.price),0) as margin,  
            case when sum(g.qty*g.price)=0 then 100 else round(isnull(((b.qty*b.price)-sum(g.qty*g.price))/sum(g.qty*g.price)*100,0),2) end as pmargin, 
            isnull(a.ttlso,0) as ttlso   from artrpurchaseorderhd a  inner join artrpurchaseorderdt b on a.poid=b.poid  inner join inmsitem c on b.itemid=c.itemid 
            inner join armscustomer d on a.custid=d.custid  inner join armssales e on a.salesid=e.salesid  left join artrpenawaranhd f on a.poid=f.soid  
            left join artrpenawarandt g on f.gbuid=g.gbuid and b.itemid=g.itemid  
            where convert(varchar(8),a.transdate,112) between :dari and :sampai 
            and a.poid like :soidkeyword
            and a.custid like :custidkeyword
            and d.custname like :custnamekeyword
            and a.salesid like :salesidkeyword
            and e.salesname like :salesnamekeyword
            group by a.poid, a.transdate, d.custname, e.salesname, b.itemid, c.itemname, b.qty, b.price, a.ttlso order by a.poid ",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'soidkeyword' => '%' .$param['soidkeyword'].'%',
                'custidkeyword' => '%' .$param['custidkeyword'].'%',
                'custnamekeyword' => '%' .$param['custnamekeyword'].'%',
                'salesidkeyword' => '%' .$param['salesidkeyword'].'%',
                'salesnamekeyword' => '%' .$param['salesnamekeyword'].'%'
                
            ]
        );

        $totaltotalso = 0;
        $totalqty = 0;
        $totalmodal = 0;
        $totalpo = 0;
        $totalmargin = 0;

        foreach ($result as $total) {
            $totaltotalso += $total->ttlso;
            $totalqty += $total->qty;
            $totalmodal += $total->modal;
            $totalpo += $total->jumlahpo;
            $totalmargin += $total->margin;
        }

      
        return  [
            'totalqty' => strval($totalqty),
            'totalpo' => strval($totalpo),
            'totalmodal' => strval($totalmodal),
            'totalmargin' => strval($totalmargin),
            'totaltotalso' => strval($totaltotalso),
            'data' => $result
        ];


    }

}

?>