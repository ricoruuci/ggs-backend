<?php

namespace App\Models\CF\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class RptBukuBesar extends Model
{
    function getLaporanBukuBesarHd($param)
    {
        $result = DB::select(
            "SELECT  k.rekeningid,l.rekeningname,k.rekeningid+'-'+l.rekeningname as rekening,
            isnull(sum(case when k.transdate between :dari1 and :sampai1 then (case when k.jenis = 'D'
			then k.amount else 0 end) else 0 end),0) as debit,
			isnull(sum(case when k.transdate between :dari2 and :sampai2 then (case when k.jenis = 'K'
			then k.amount else 0 end) else 0 end),0) as kredit,
			isnull(sum(case when k.transdate < :dari3 then (case when k.jenis = 'D'
			then k.amount else k.amount*-1 end) else 0 end),0) as saldoawal,
			isnull(sum(case when k.transdate <= :sampai3 then (case when k.jenis = 'D'
			then k.amount else k.amount*-1 end) else 0 end),0) as saldoakhir FROM (
            SELECT B.FlagKKBB,'D' as Kode,A.RekeningID,B.Transdate,A.Jenis,ISNULL(A.Amount,0) as Amount,A.Note,
            CASE WHEN B.FlagKKBB IN ('BK','BM','GC','APB','ARB','ARC','APC') THEN B.VoucherNo ELSE A.VoucherID END as VoucherID,B.FgPayment,B.VoucherID as BNote,B.CurrID,B.Rate 
            FROM CFTrKKBBDt A INNER JOIN CFTrKKBBHd B ON A.VoucherID=B.VoucherID UNION ALL
            SELECT A.FlagKKBB,'H',B.RekeningID,A.Transdate,CASE WHEN C.FlagKKBB IN ('BM','ARB','ARC') THEN 'D' ELSE 'K' END,
            ISNULL(CASE WHEN C.FlagKKBB IN ('BM','ARB','ARC') THEN A.JumlahD WHEN C.FlagKKBB IN ('BK','APB','APC') THEN A.JumlahK END,0),
            C.Note,A.VoucherNo,A.FgPayment,A.VoucherID,A.CurrID,A.Rate FROM CFTrKKBBHd A INNER JOIN CFMsBank B ON A.BankID=B.BankID
            INNER JOIN CFTrKKBBHd C ON A.IDVoucher=C.VoucherID UNION ALL
            SELECT A.FlagKKBB,'H',B.RekeningID,A.Transdate,CASE WHEN A.FlagKKBB IN ('BM','ARB','ARC') THEN 'D' ELSE 'K' END,
            ISNULL(CASE WHEN A.FlagKKBB IN ('BM','ARB','ARC') THEN JumlahD WHEN A.FlagKKBB IN ('BK','APB','APC') THEN JumlahK END,0),
            A.Note,CASE WHEN A.FlagKKBB IN ('BK','BNM','GC','ARB','ARC','APB','APC') THEN A.VoucherNo ELSE A.VoucherID END,
            A.FgPayment,A.VoucherID,A.CurrID,A.Rate FROM CFTrKKBBHd A INNER JOIN CFMsBank B ON A.BankID=B.BankID
            WHERE A.FlagKKBB IN ('BM','BK','ARB','ARC','APB','APC') UNION ALL
            SELECT A.FlagKKBB,'H','110100.0001',A.Transdate,CASE WHEN A.FlagKKBB IN ('KM','ARK') THEN 'D' ELSE 'K' END,
            ISNULL(CASE WHEN A.FlagKKBB IN ('KM','ARK') THEN JumlahD WHEN A.FlagKKBB IN ('KK','APK') THEN JumlahK END,0),
            A.Note,A.VoucherID,A.FgPayment,A.VoucherID,A.CurrID,A.Rate FROM CFTrKKBBHd A WHERE A.FlagKKBB IN ('KM','KK','ARK','APK') UNION ALL
            SELECT 'AR','D',RekeningU,Transdate,'D',ISNULL(TTLPj,0),A.SaleID,A.SaleID,'T' as FgPayment,A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL
            SELECT 'AR','D',RekPersediaan,Transdate,'K',ISNULL(HPP,0),A.SaleID,A.SaleID,'T' as FgPayment,A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL
            SELECT 'AR','D',RekHPP,Transdate,'D',ISNULL(HPP,0),A.SaleID,A.SaleID,'T' as FgPayment,A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL
            SELECT 'AR','D',RekeningK,Transdate,'K',ISNULL(CASE WHEN FgTax='T' THEN TTLPj ELSE TTLPj/(1+(PPNFee*0.01))+Discount END,0),A.SaleID,A.SaleID,'T',A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL 
            SELECT 'AR','D',RekeningP,Transdate,'K',ISNULL(CASE WHEN FgTax='T' THEN 0 ELSE TTLPj/(1+(PPNFee*0.01))*PPNFee*0.01 END,0),A.SaleID,A.SaleID,'T',A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL 
            SELECT 'AR','D',TaxID,Transdate,'D',ISNULL(CASE WHEN FgTax='T' THEN 0 ELSE TTLPj/(1+(PPNFee*0.01))*0.1 END,0),A.SaleID,A.SaleID,'T',A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL 
            SELECT 'AP','P',RekeningU,Transdate,'K',ISNULL(TTLPb,0),'AA',A.PurchaseID,'T' as FgPayment,A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL
            SELECT 'AP','P',RekPersediaan,Transdate,'D',CASE WHEN FgTax='Y' THEN ISNULL(TTLPb/(1+(Rate*0.01)),0) ELSE ISNULL(TTLPb,0) END,'AA',A.PurchaseID,'T' as FgPayment,A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL 
            SELECT 'AP','P',RekHPP,Transdate,'K',CASE WHEN FgTax='Y' THEN ISNULL(TTLPb/(1+(Rate*0.01)),0) ELSE ISNULL(TTLPb,0) END,'AA',A.PurchaseID,'T' as FgPayment,A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL 
            SELECT 'AP','P',RekeningK,Transdate,'D',ISNULL(CASE WHEN FgTax='T' THEN TTLPb ELSE TTLPb/(1+(Rate*0.01)) END,0),'BB',A.PurchaseID ,'T',A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL 
            SELECT 'AP','P',RekeningP,Transdate,'D',ISNULL(CASE WHEN FgTax='T' THEN 0 ELSE TTLPb/(1+A.Rate*0.01)*A.Rate*0.01 END,0),'CC',A.PurchaseID,'T',A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A 
            ) as K
            INNER JOIN CFMsRekening L ON K.RekeningID=L.RekeningID
            WHERE --CONVERT(VARCHAR(8),K.Transdate,112) BETWEEN :dari and :sampai AND
            K.Amount<> 0
            AND K.FgPayment='T' and isnull(k.rekeningid,'') like :rekeningid
            GROUP BY K.RekeningID,L.RekeningName",
            [
                'dari1' => $param['dari'],
                'sampai1' => $param['sampai'],
                'dari2' => $param['dari'],
                'sampai2' => $param['sampai'],
                'dari3' => $param['dari'],
                'sampai3' => $param['sampai'],
                'rekeningid' => '%' . $param['rekeningid'] . '%'
            ]
        );

        $result2 = [];
        $saldo = 0;
        $hasil = 0;

        foreach ($result as $dataHeader) {
            $saldo = $dataHeader->saldoawal;

            $datadetailResult = $this->getLaporanBukuBesarDt([
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'rekeningid' => $dataHeader->rekeningid
            ]);

            foreach ($datadetailResult as $hasil) {
                if ($hasil->jenis == 'D') {
                    $saldo = $saldo + $hasil->amount;
                } else {
                    $saldo = $saldo - $hasil->amount;
                }

                $hasil->saldo = strval($saldo);
            }

            $dataHeader->mutasi = $hasil;

            $result2[] = [
                "rekeningid" => $dataHeader->rekeningid,
                "rekeningname" => $dataHeader->rekeningname,
                "rekening" => $dataHeader->rekening,
                "debit" => $dataHeader->debit,
                "kredit" => $dataHeader->kredit,
                "saldoawal" => $dataHeader->saldoawal,
                "saldoakhir" => $dataHeader->saldoakhir,
                "detail" => $datadetailResult
            ];
        }

        return [
            'data' => $result2,
        ];
    }

    function getLaporanBukuBesarDt($param)
    {
        $result = DB::select(
            "SELECT CONVERT(VARCHAR(10),K.Transdate,103) as tanggal,K.VoucherID as voucherno,
            K.note,k.rekeningid,k.jenis,case when k.jenis = 'D' then 'Debit' else 'Kredit' end as jenisvalue,
            CASE WHEN K.CurrID='IDR' THEN K.Amount ELSE K.Amount*K.Rate END as amount FROM (
            SELECT B.FlagKKBB,'D' as Kode,A.RekeningID,B.Transdate,A.Jenis,ISNULL(A.Amount,0) as Amount,A.Note,
            CASE WHEN B.FlagKKBB IN ('BK','BM','GC','APB','ARB','ARC','APC') THEN B.VoucherNo ELSE A.VoucherID END as VoucherID,B.FgPayment,B.VoucherID as BNote,B.CurrID,B.Rate
            FROM CFTrKKBBDt A INNER JOIN CFTrKKBBHd B ON A.VoucherID=B.VoucherID UNION ALL
            SELECT A.FlagKKBB,'H',B.RekeningID,A.Transdate,CASE WHEN C.FlagKKBB IN ('BM','ARB','ARC') THEN 'D' ELSE 'K' END,
            ISNULL(CASE WHEN C.FlagKKBB IN ('BM','ARB','ARC') THEN A.JumlahD WHEN C.FlagKKBB IN ('BK','APB','APC') THEN A.JumlahK END,0),
            C.Note,A.VoucherNo,A.FgPayment,A.VoucherID,A.CurrID,A.Rate FROM CFTrKKBBHd A INNER JOIN CFMsBank B ON A.BankID=B.BankID
            INNER JOIN CFTrKKBBHd C ON A.IDVoucher=C.VoucherID UNION ALL
            SELECT A.FlagKKBB,'H',B.RekeningID,A.Transdate,CASE WHEN A.FlagKKBB IN ('BM','ARB','ARC') THEN 'D' ELSE 'K' END,
            ISNULL(CASE WHEN A.FlagKKBB IN ('BM','ARB','ARC') THEN JumlahD WHEN A.FlagKKBB IN ('BK','APB','APC') THEN JumlahK END,0),
            A.Note,CASE WHEN A.FlagKKBB IN ('BK','BNM','GC','ARB','ARC','APB','APC') THEN A.VoucherNo ELSE A.VoucherID END,
            A.FgPayment,A.VoucherID,A.CurrID,A.Rate FROM CFTrKKBBHd A INNER JOIN CFMsBank B ON A.BankID=B.BankID
            WHERE A.FlagKKBB IN ('BM','BK','ARB','ARC','APB','APC') UNION ALL
            SELECT A.FlagKKBB,'H','110100.0001',A.Transdate,CASE WHEN A.FlagKKBB IN ('KM','ARK') THEN 'D' ELSE 'K' END,
            ISNULL(CASE WHEN A.FlagKKBB IN ('KM','ARK') THEN JumlahD WHEN A.FlagKKBB IN ('KK','APK') THEN JumlahK END,0),
            A.Note,A.VoucherID,A.FgPayment,A.VoucherID,A.CurrID,A.Rate FROM CFTrKKBBHd A WHERE A.FlagKKBB IN ('KM','KK','ARK','APK') UNION ALL
            SELECT 'AR','D',RekeningU,Transdate,'D',ISNULL(TTLPj,0),A.SaleID,A.SaleID,'T' as FgPayment,A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL
            SELECT 'AR','D',RekPersediaan,Transdate,'K',ISNULL(HPP,0),A.SaleID,A.SaleID,'T' as FgPayment,A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL
            SELECT 'AR','D',RekHPP,Transdate,'D',ISNULL(HPP,0),A.SaleID,A.SaleID,'T' as FgPayment,A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL
            SELECT 'AR','D',RekeningK,Transdate,'K',ISNULL(CASE WHEN FgTax='T' THEN TTLPj ELSE TTLPj/(1+(PPNFee*0.01))+Discount END,0),A.SaleID,A.SaleID,'T',A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL 
            SELECT 'AR','D',RekeningP,Transdate,'K',ISNULL(CASE WHEN FgTax='T' THEN 0 ELSE TTLPj/(1+(PPNFee*0.01))*PPNFee*0.01 END,0),A.SaleID,A.SaleID,'T',A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL 
            SELECT 'AR','D',TaxID,Transdate,'D',ISNULL(CASE WHEN FgTax='T' THEN 0 ELSE TTLPj/(1+(PPNFee*0.01))*0.1 END,0),A.SaleID,A.SaleID,'T',A.SaleID,A.CurrID,A.Rate FROM ARTrPenjualanHd A UNION ALL 
            SELECT 'AP','P',RekeningU,Transdate,'K',ISNULL(TTLPb,0),'AA',A.PurchaseID,'T' as FgPayment,A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL
            SELECT 'AP','P',RekPersediaan,Transdate,'D',CASE WHEN FgTax='Y' THEN ISNULL(TTLPb/(1+(Rate*0.01)),0) ELSE ISNULL(TTLPb,0) END,'AA',A.PurchaseID,'T' as FgPayment,A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL 
            SELECT 'AP','P',RekHPP,Transdate,'K',CASE WHEN FgTax='Y' THEN ISNULL(TTLPb/(1+(Rate*0.01)),0) ELSE ISNULL(TTLPb,0) END,'AA',A.PurchaseID,'T' as FgPayment,A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL 
            SELECT 'AP','P',RekeningK,Transdate,'D',ISNULL(CASE WHEN FgTax='T' THEN TTLPb ELSE TTLPb/(1+(Rate*0.01)) END,0),'BB',A.PurchaseID ,'T',A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A UNION ALL 
            SELECT 'AP','P',RekeningP,Transdate,'D',ISNULL(CASE WHEN FgTax='T' THEN 0 ELSE TTLPb/(1+A.Rate*0.01)*A.Rate*0.01 END,0),'CC',A.PurchaseID,'T',A.PurchaseID,A.CurrID,A.Rate FROM APTrPurchaseHd A 
            ) as K
            WHERE CONVERT(VARCHAR(8),K.Transdate,112) BETWEEN :dari and :sampai AND K.Amount<> 0
            AND K.FgPayment='T'
            and k.rekeningid = :rekeningid
            ORDER BY CONVERT(VARCHAR(8),K.Transdate,112),K.RekeningID,K.VoucherID,K.Jenis",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'rekeningid' => $param['rekeningid']
            ]
        );

        return $result;
    }

    public function getSaldoAwal($param)
    {
        $result = DB::select(
            ""
        );
    }
}
