<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class Dashboard extends Model
{
    function getListData()
    {

        $result = DB::select(
            "WITH calendar as (
            select cast(convert(varchar(4), 2024, 112) + '0101' as date) as date union all
            select dateadd(day, 1, date) from calendar
            where dateadd(day, 1, date) <= cast(convert(varchar(4), getdate(), 112) + '1231' as date)
            )
            select convert(char(8), c.date, 112) as date,isnull(sum(k.ttlpj), 0) as total_sales from calendar c
            left join (
            select convert(date, a.transdate) as transdate, a.ttlpj
            from artrpenjualanhd a
            inner join armssales b on a.salesid = b.salesid
            ) as k on c.date = k.transdate
            where year(c.date) = year(getdate())  and month(c.date) = month(getdate())
            group by 
            convert(char(8), c.date, 112)
            order by 
            date
            option (maxrecursion 0); "
        );

        return $result;
    }

    function gettahun()
    {

        $result = DB::select(
            "SELECT  left(convert(varchar(10), getdate(), 112), 4) as tahun "
        );

        return $result;
    }

    function getRekapJualTahunan()
    {

        $result = DB::select(
            "SELECT 
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0101' and left(convert(varchar(10), getdate(), 112), 4) + '0131' then k.ttlpj else 0 end), 0) as jan,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0201' and left(convert(varchar(10), getdate(), 112), 4) + '0229' then k.ttlpj else 0 end), 0) as feb,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0301' and left(convert(varchar(10), getdate(), 112), 4) + '0331' then k.ttlpj else 0 end), 0) as mar,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0401' and left(convert(varchar(10), getdate(), 112), 4) + '0430' then k.ttlpj else 0 end), 0) as apr,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0501' and left(convert(varchar(10), getdate(), 112), 4) + '0531' then k.ttlpj else 0 end), 0) as may,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0601' and left(convert(varchar(10), getdate(), 112), 4) + '0630' then k.ttlpj else 0 end), 0) as jun,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0701' and left(convert(varchar(10), getdate(), 112), 4) + '0731' then k.ttlpj else 0 end), 0) as jul,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0801' and left(convert(varchar(10), getdate(), 112), 4) + '0831' then k.ttlpj else 0 end), 0) as aug,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '0901' and left(convert(varchar(10), getdate(), 112), 4) + '0930' then k.ttlpj else 0 end), 0) as sep,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '1001' and left(convert(varchar(10), getdate(), 112), 4) + '1031' then k.ttlpj else 0 end), 0) as oct,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '1101' and left(convert(varchar(10), getdate(), 112), 4) + '1130' then k.ttlpj else 0 end), 0) as nov,
            isnull(sum(case when convert(varchar(10), k.transdate, 112) between left(convert(varchar(10), getdate(), 112), 4) + '1201' and left(convert(varchar(10), getdate(), 112), 4) + '1231' then k.ttlpj else 0 end), 0) as des
            from (
            select left(convert(varchar(10), a.transdate, 112), 4) as periode, a.transdate, a.ttlpj 
            from artrpenjualanhd a
            ) as k 
            where k.periode = left(convert(varchar(10), getdate(), 112), 4); "
        );

        return $result;
    }

    function getTotal()
    {

        $result = DB::select(
            "SELECT (select count(poid) as total_count from artrpurchaseorderhd where convert(varchar(12),transdate,112) = convert(varchar(12),getdate(),112)) as totaljso,
            (select isnull(sum(ttlso),0) as total_so from artrpurchaseorderhd where convert(varchar(12),transdate,112) = convert(varchar(12),getdate(),112)) as totalso,
            (select isnull(sum(ttlpj),0) as total_jual from artrpenjualanhd where  convert(varchar(12),transdate,112) = convert(varchar(12),getdate(),112)) as totalj,
            (select isnull(sum(ttlpb),0) as total_beli from aptrpurchasehd where  convert(varchar(12),transdate,112) = convert(varchar(12),getdate(),112)) as totalb,
            (select count(*) as sopending from artrpurchaseorderhd where jenis not in ('y','x','t')	) as sopending,
            (select count(gbuid) as total_count from artrpenawaranhd where flag='b' and convert(varchar(12),transdate,112) = convert(varchar(12),getdate(),112)) as  totaljpo,
            (select isnull(sum(ttlgbu),0) as total_po from artrpenawaranhd where flag='B' and convert(varchar(12),transdate,112) = convert(varchar(12),getdate(),112)) as totalpo"
        );

        return $result;
    }

    function getUserAktif()
    {

        $result = DB::select(
            "SELECT count(*) as user_active from personal_access_tokens where expires_at>getdate()"
        );

        return $result;
    }


    function getNetCash()
    {

        $result = DB::select(
            "SELECT k.kode,k.bankid,k.bankname,k.[1] as amount FROM (
            SELECT 'AP' as kode,'AP' as bankid,'HUTANG DAGANG' as bankname, ISNULL(( 
            SELECT ISNULL(SUM(K.TTLPb-K.Retur-K.Bayar),0) as Hutang FROM ( 
            SELECT A.Transdate,A.TTLPb, 
            ISNULL((SELECT ISNULL(Sum(Price*Qty),0) FROM APTrReturnDt F INNER JOIN APTrReturnHd G  ON F.ReturnID=G.ReturnID 
            WHERE G.FlagRetur='B' AND F.purchaseID=A.PurchaseID  AND G.SuppID=A.SuppID AND 
            CONVERT(VARCHAR(8),G.TransDate,112) <= GETDATE()),0) as Retur, 
            ISNULL((SELECT ISNULL(SUM(ValuePayment),0) FROM APTrPaymentHd M INNER JOIN APtrPaymentDt N ON M.PaymentID=N.PaymentID 
            WHERE N.PurchaseID=A.PurchaseID AND M.SuppID=A.SuppID AND CONVERT(VARCHAR(8),M.TransDate,112) <= GETDATE()),0) as Bayar 
            FROM APTrPurchaseHd A 
            UNION ALL 
            SELECT A.Transdate,A.TTLPb, 
            ISNULL((SELECT ISNULL(CASE WHEN H.FgTax='Y' THEN SUM(Price*Qty)*1.1 ELSE SUM(Qty*Price)END,0) FROM APTrReturnDt F INNER JOIN APTrReturnHd G  ON F.ReturnID=G.ReturnID 
            INNER JOIN APTrPurchaseHd H ON F.PurchaseID=H.PurchaseID AND G.SuppID=H.SuppID WHERE G.FlagRetur='B' AND F.purchaseID=A.PurchaseID  AND G.SuppID=A.SuppID AND 
            CONVERT(VARCHAR(8),G.TransDate,112) <= GETDATE() GROUP BY H.FgTax),0), 
            ISNULL((SELECT ISNULL(SUM(ValuePayment),0) FROM APTrPaymentHd M INNER JOIN APTrPaymentDt N ON M.PaymentID=N.PaymentID 
            WHERE N.PurchaseID=A.PurchaseID AND M.SuppID=A.SuppID AND CONVERT(VARCHAR(8),M.TransDate,112) <= GETDATE()),0)+ 
            ISNULL((SELECT ISNULL(SUM(ValuePayment),0) FROM APTrPaymentHd M INNER JOIN APTrPaymentDt N ON M.PaymentID=N.PaymentID 
            WHERE N.PurchaseID=A.PurchaseID AND M.SuppID=A.SuppID AND CONVERT(VARCHAR(8),M.TransDate,112) <= GETDATE()),0) as Bayar 
            FROM APTrPurchaseHd A 
            UNION ALL 
            SELECT A.Transdate,(SELECT SUM(B.Amount)  FROM CFTrKKBBDt B) AS Amount,0,ISNULL((SELECT ISNULL(SUM(ValuePayment),0) FROM APTrPaymentHd M 
            INNER JOIN APTrPaymentDt N ON M.PaymentID=N.PaymentID WHERE N.PurchaseID=A.VoucherNo AND M.SuppID=A.SuppID AND 
            CONVERT(VARCHAR(8),M.Transdate,112) <= GETDATE()),0) as Bayar FROM CFTrKKBBHd A 
            ) as K 
            WHERE CONVERT(VARCHAR(8),K.TransDate,112) <= GETDATE() 
            AND ISNULL(K.TTLPb-K.Retur-K.Bayar,0)>0.01 ),0) as '1'

            UNION ALL
            SELECT 'AR','AR','PIUTANG DAGANG',
            ISNULL((SELECT ISNULL(SUM(K.TTLPj-K.Retur-K.Payment),0) as Total FROM ( 
            SELECT A.FlagCounter,A.CustID,A.Transdate,A.CurrID,ISNULL(A.TTLPj,0) as TTLPj, 
            ISNULL((SELECT ISNULL(Sum(Price*Qty),0) FROM ARTrReturPenjualanDt F INNER JOIN ARTrReturPenjualanHd G ON F.ReturnId=G.ReturnId 
            WHERE G.FlagRetur='B' AND F.SaleId=A.SaleId AND CONVERT(VARCHAR(8),G.Transdate,112) <= GETDATE() ),0) as Retur, 
            ISNULL((SELECT ISNULL(sum(L.ValuePayment),0) FROM ARTrPiutangDt L INNER JOIN ARTrPiutangHd Q ON L.PiutangId=Q.PiutangId 
            WHERE L.SaleID=A.SaleID  AND CONVERT(VARCHAR(8),Q.Transdate,112) <= GETDATE()),0) as Payment 
            FROM ARTrPenjualanHd A 
            UNION ALL 
            SELECT 'L',A.CustID,A.Transdate,A.CurrID,ISNULL(A.TTLKj,0),0, 
            ISNULL((SELECT ISNULL(sum(L.ValuePayment),0) FROM ARTrPiutangDt L INNER JOIN ARTrPiutangHd Q ON L.PiutangId=Q.PiutangId WHERE L.SaleID=A.KonInvPelID 
            AND CONVERT(VARCHAR(8),Q.Transdate,112) <= GETDATE()),0) FROM ARTrKonInvPelHd A 
            ) as K 
            WHERE CONVERT(VARCHAR(8),K.Transdate,112) <= GETDATE() AND ISNULL(K.TTLPj-K.Retur-K.Payment,0) <> 0 ),0) as '1'

            ) as K"
        );

        return $result;
    }

    function getJualTahunan()
    {

        $result = DB::select(
            "SELECT  k.periode AS tahun,ISNULL(SUM(k.ttlpj), 0) AS total_tahunan
            FROM (
            SELECT LEFT(CONVERT(VARCHAR(10), a.transdate, 112), 4) AS periode, 
            a.transdate,a.ttlpj FROM artrpenjualanhd a
            ) AS k 
            WHERE k.periode BETWEEN CONVERT(VARCHAR(4), YEAR(GETDATE()) - 5) AND CONVERT(VARCHAR(4), YEAR(GETDATE()))
            GROUP BY k.periode
            ORDER BY k.periode "
        );

        return $result;
    }
}