<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model
{
    public function autoNumber($namatable, $namafield, $formatso, $formatnumber)
    {
        //dd(var_dump("select '".$formatso."'+FORMAT(ISNULL((select top 1 RIGHT(".$namafield.",".strlen($formatnumber).") from ".$namatable." 
        //where ".$namafield." like '%".$formatso."%' order by ".$namafield." desc),0)+1,'".$formatnumber."') as nomor "));
        
        $autonumber = DB::selectOne(
            "select '".$formatso."'+FORMAT(ISNULL((select top 1 RIGHT(".$namafield.",".strlen($formatnumber).") from ".$namatable." 
            where ".$namafield." like '".$formatso."%' order by ".$namafield." desc),0)+1,'".$formatnumber."') as nomor "
            
            // from ".$namatable." where ".$namafield." like '%".$formatso."%'"
        );

        return $autonumber->nomor;
    }

    public function queryAccounting()
    {
        $result = 
        "SELECT b.flagkkbb,'d' as kode,a.rekeningid,b.transdate,a.jenis,isnull(a.amount,0) as amount,a.note,
        case when b.flagkkbb in ('bk','bm','gc','apb','arb','arc','apc') then b.voucherno else a.voucherid end as voucherid,b.fgpayment,b.voucherid as bnote,b.currid,b.rate 
        from cftrkkbbdt a inner join cftrkkbbhd b on a.voucherid=b.voucherid union all 
        select a.flagkkbb,'h',b.rekeningid,a.transdate,case when c.flagkkbb in ('bm','arb','arc') then 'd' else 'k' end,
        isnull(case when c.flagkkbb in ('bm','arb','arc') then a.jumlahd when c.flagkkbb in ('bk','apb','apc') then a.jumlahk end,0),
        c.note,a.voucherno,a.fgpayment,a.voucherid,a.currid,a.rate from cftrkkbbhd a inner join cfmsbank b on a.bankid=b.bankid 
        inner join cftrkkbbhd c on a.idvoucher=c.voucherid union all 
        select a.flagkkbb,'h',b.rekeningid,a.transdate,case when a.flagkkbb in ('bm','arb','arc') then 'd' else 'k' end,
        isnull(case when a.flagkkbb in ('bm','arb','arc') then jumlahd when a.flagkkbb in ('bk','apb','apc') then jumlahk end,0),
        a.note,case when a.flagkkbb in ('bk','bnm','gc','arb','arc','apb','apc') then a.voucherno else a.voucherid end,
        a.fgpayment,a.voucherid,a.currid,a.rate from cftrkkbbhd a inner join cfmsbank b on a.bankid=b.bankid 
        where a.flagkkbb in ('bm','bk','arb','arc','apb','apc') union all 
        select a.flagkkbb,'h','+sdrkas+',a.transdate,case when a.flagkkbb in ('km','ark') then 'd' else 'k' end,
        isnull(case when a.flagkkbb in ('km','ark') then jumlahd when a.flagkkbb in ('kk','apk') then jumlahk end,0),
        a.note,a.voucherid,a.fgpayment,a.voucherid,a.currid,a.rate from cftrkkbbhd a where a.flagkkbb in ('km','kk','ark','apk') union all 
        select 'ar','d',rekeningu,transdate,'d',isnull(ttlpj,0),a.saleid,a.saleid,'t' as fgpayment,a.saleid,a.currid,a.rate from artrpenjualanhd a union all 
        select 'ar','d',rekpersediaan,transdate,'k',isnull(hpp,0),a.saleid,a.saleid,'t' as fgpayment,a.saleid,a.currid,a.rate from artrpenjualanhd a union all 
        select 'ar','d',rekhpp,transdate,'d',isnull(hpp,0),a.saleid,a.saleid,'t' as fgpayment,a.saleid,a.currid,a.rate from artrpenjualanhd a union all 
        select 'ar','d',rekeningk,transdate,'k',isnull(case when fgtax='t' then ttlpj else ttlpj/1.1+discount end,0),a.saleid,a.saleid,'t',a.saleid,a.currid,a.rate from artrpenjualanhd a union all 
        select 'ar','d',rekeningp,transdate,'k',isnull(case when fgtax='t' then 0 else ttlpj/1.1*0.1 end,0),a.saleid,a.saleid,'t',a.saleid,a.currid,a.rate from artrpenjualanhd a union all 
        select 'ar','d',taxid,transdate,'d',isnull(case when fgtax='t' then 0 else ttlpj/1.1*0.1 end,0),a.saleid,a.saleid,'t',a.saleid,a.currid,a.rate from artrpenjualanhd a union all 
        select 'ap','p',rekeningu,transdate,'k',isnull(ttlpb,0),'aa',a.purchaseid,'t' as fgpayment,a.purchaseid,a.currid,a.rate from aptrpurchasehd a union all 
        select 'ap','p',rekpersediaan,transdate,'d',case when fgtax='y' then isnull(ttlpb/1.1,0) else isnull(ttlpb,0) end,'aa',a.purchaseid,'t' as fgpayment,a.purchaseid,a.currid,a.rate from aptrpurchasehd a union all 
        select 'ap','p',rekhpp,transdate,'k',case when fgtax='y' then isnull(ttlpb/1.1,0) else isnull(ttlpb,0) end,'aa',a.purchaseid,'t' as fgpayment,a.purchaseid,a.currid,a.rate from aptrpurchasehd a union all 
        select 'ap','p',rekeningk,transdate,'d',isnull(case when fgtax='t' then ttlpb else ttlpb/1.1 end,0),'bb',a.purchaseid ,'t',a.purchaseid,a.currid,a.rate from aptrpurchasehd a union all 
        select 'ap','p',rekeningp,transdate,'d',isnull(case when fgtax='t' then 0 else ttlpb/1.1*0.1 end,0),'cc',a.purchaseid,'t',a.purchaseid,a.currid,a.rate from aptrpurchasehd a";

        return $result;
    }
}





// $arrMenu = [
//     ['100001', 'Pembelian', '/pembelian'],
//     ['100002', 'Supplier', '/pembelian/supplier']
// ];