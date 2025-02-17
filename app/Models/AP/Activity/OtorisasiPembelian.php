<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;

use function PHPUnit\Framework\isNull;

class OtorisasiPembelian extends BaseModel
{
    use HasFactory;

    protected $table = 'aptrpurchasehd';

    public $timestamps = false;

    function updateData($param)
    {
        $result = DB::update(
            "UPDATE aptrpurchasehd SET 
            fgoto = :jenis
            WHERE purchaseid = :purchaseid ",
            [
                'purchaseid' => $param['purchaseid'],
                'jenis' => $param['jenis']
            ]
        );

        return $result;
    }

    function getListOto($param)
    {
        $result = DB::select(
            "SELECT a.purchaseid,b.suppname,a.transdate,a.jatuhtempo as term, 
            a.transdate as jthtempo,a.ttlpb as total, 
            a.upduser,a.upddate, 
            (case when a.fgoto='y' then 'approved' else 'pending' end) as statusoto 
            from aptrpurchasehd a inner join apmssupplier b on a.suppid=b.suppid 
            where a.fgoto='T' and a.purchaseid like :purchaseidkeyword and b.suppname like :suppnamekeyword ",
            [
                'purchaseidkeyword' => '%' . $param['purchaseidkeyword'] . '%',
                'suppnamekeyword' => '%' . $param['suppnamekeyword'] . '%'
            ]
        );

        foreach ($result as $data) {

            $detail = DB::select(
                "SELECT a.purchaseid,a.itemid,b.itemname,a.qty,b.uomid,a.price,a.qty*a.price as total
                from aptrpurchasedt a inner join inmsitem b on a.itemid=b.itemid
                where a.purchaseid= :purchaseid ",
                [
                    'purchaseid' => $data->purchaseid
                ]
            );

            $data->detail = $detail;

            foreach ($detail as $sn) {
                $detailsn = DB::select(
                    "SELECT k.snid,case when l.saleid <> '-' then 'Terjual ke '+l.custname+' dengan harga : '+cast(l.price as varchar)+' nota no '+l.saleid else 'Stock' end as note,
                    isnull((select isnull(sum(p.amount),0) from cftrkkbbdt p inner join cftrkkbbhd q on p.voucherid=q.voucherid 
                    where p.note=l.saleid and p.rekeningid=(select drpj from samsset)
                    ),0) as bayar 
                    from (
                    select purchaseid,itemid,snid,fgjual from aptrpurchasedtsn) as k left join (select m.snid,m.saleid,m.price,x.custname,
                    convert(varchar(8),m.transdate,112) as tgl,m.status,case when m.currid='idr' then 'rp' else '$' end as currid,m.fgtax from (
                    select a.snid,a.itemid,a.saleid,a.price,b.custid,b.transdate,1 as status,b.currid,b.fgtax 
                    from artrpenjualansn a inner join artrpenjualanhd b on a.saleid=b.saleid) as m 
                    inner join armscustomer x on m.custid=x.custid ) as l on k.snid=l.snid 
                    where k.purchaseid= :purchaseid and k.itemid= :itemid and 
                    k.snid not in (select snid from aptrreturnsn)",
                    [
                        'purchaseid' => $sn->purchaseid,
                        'itemid' => $sn->itemid,
                    ]
                );

                $sn->detailsn = $detailsn;
            }
        }

        return $result;
    }

    function getData($param)
    {
        $result = DB::select(
            "SELECT a.purchaseid,b.suppname,a.transdate,a.jatuhtempo as term, 
            a.transdate as jthtempo,a.ttlpb as total, 
            a.upduser,a.upddate, 
            (case when a.fgoto='y' then 'approved' else 'pending' end) as statusoto 
            from aptrpurchasehd a inner join apmssupplier b on a.suppid=b.suppid 
            where a.fgoto='T' and a.purchaseid=:purchaseid  ",
            [
                'purchaseid' => $param['purchaseid']
            ]
        );

        return $result;
    }


    function getDataDetail($param)
    {

        $result = DB::select(
            "SELECT a.purchaseid,a.itemid,b.itemname,a.qty,b.uomid,a.price,a.qty*a.price as total
            from aptrpurchasedt a inner join inmsitem b on a.itemid=b.itemid
            inner join aptrpurchasehd c on a.purchaseid=c.purchaseid
            where c.fgoto='T' and a.purchaseid= :purchaseid ",
            [
                'purchaseid' => $param['purchaseid']
            ]
        );

        foreach ($result as $sn) {
            $detailsn = DB::select(
                "SELECT k.snid,case when l.saleid <> '-' then 'Terjual ke '+l.custname+' dengan harga : '+cast(l.price as varchar)+' nota no '+l.saleid else 'Stock' end as note,
                isnull((select isnull(sum(p.amount),0) from cftrkkbbdt p inner join cftrkkbbhd q on p.voucherid=q.voucherid 
                where p.note=l.saleid and p.rekeningid=(select drpj from samsset)
                ),0) as bayar 
                from (
                select purchaseid,itemid,snid,fgjual from aptrpurchasedtsn) as k left join (select m.snid,m.saleid,m.price,x.custname,
                convert(varchar(8),m.transdate,112) as tgl,m.status,case when m.currid='idr' then 'rp' else '$' end as currid,m.fgtax from (
                select a.snid,a.itemid,a.saleid,a.price,b.custid,b.transdate,1 as status,b.currid,b.fgtax 
                from artrpenjualansn a inner join artrpenjualanhd b on a.saleid=b.saleid) as m 
                inner join armscustomer x on m.custid=x.custid ) as l on k.snid=l.snid 
                where k.purchaseid= :purchaseid and k.itemid= :itemid and 
                k.snid not in (select snid from aptrreturnsn)",
                [
                    'purchaseid' => $sn->purchaseid,
                    'itemid' => $sn->itemid,
                ]
            );

            $sn->detailsn = $detailsn;
        }
        return $result;
    }





    function cekPurchase($purchaseid)
    {
        $result = DB::selectOne(
            "SELECT * from aptrpurchasehd WHERE purchaseid = :purchaseid ",
            [
                'purchaseid' => $purchaseid
            ]
        );

        return $result;
    }
}