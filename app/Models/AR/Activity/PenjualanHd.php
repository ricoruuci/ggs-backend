<?php

namespace App\Models\AR\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;

use function PHPUnit\Framework\isNull;


class PenjualanHd extends BaseModel
{
    use HasFactory;

    protected $table = 'artrpenjualanhd';

    public $timestamps = false;

    public static $rulesInsert = [
        'custid' => 'required',
        'transdate' => 'required',
        'salesid' => 'required',
        'fgtax' => 'required'
    ];

    public static $messagesInsert = [
        'custid' => 'Kolom kode pelanggan harus diisi.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.',
        'salesid' => 'Kolom kode sales harus diisi.',
        'fgtax' => 'Kolom Jenis Tax harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'saleid' => 'required',
        'transdate' => 'required'
    ];

    public static $messagesUpdate = [
        'saleid' => 'nomor penjualan tidak ditemukan.',
        'transdate' => 'Kolom tanggal transaksi harus diisi.'
    ];

    public static $cariso = [
        'transdate' => 'required'
    ];

    public static $carisomessage = [
        'transdate' => 'Tanggal tidak boleh kosong !'
    ];

    public static $caripelanggan = [
        'salesid' => 'required'
    ];

    public static $caripelangganmessage = [
        'salesid' => 'Sales tidak boleh kosong !'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO artrpenjualanhd (saleid,soid,poid,transdate,kontransbrgid,custid,salesid,note,jatuhtempo,discount,currid,upddate,upduser,dp,flagcounter,rate,
            charge,nama,kasir,fgtax,ppnfee,alamat,administrasi,fglunas,fgtrans,fgupload,taxid,actor,fgform,nilaitax,alamatkirim)
            VALUES (:saleid,:soid,:pocust,:transdate,:nopi,:custid,:salesid,:note,:term,:discamount,'IDR',getdate(),:upduser,:dp,'L',1,
            0,'',:kasir,:fgtax,0,'',0,'B','B','T',:taxid,:nama,'AR',:nilaitax,:alamat)",
            [
                'saleid' => $param['saleid'],
                'soid' => $param['soid'],
                'pocust' => $param['pocust'],
                'transdate' => $param['transdate'],
                'nopi' => $param['nopi'],
                'custid' => $param['custid'],
                'salesid' => $param['salesid'],
                'note' => $param['note'],
                'term' => $param['term'],
                'discamount' => $param['discamount'],
                'dp' => $param['dp'],
                'upduser' => $param['upduser'],
                'nama' => $param['nama'],
                'kasir' => $param['upduser'],
                'fgtax' => $param['fgtax'],
                'nilaitax' => $param['nilaitax'],
                'taxid' => $param['taxid'],
                // 'alamat' => $param['alamat'],
                'alamat' => $param['alamat']
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE artrpenjualanhd SET
            transdate = :transdate,
            soid = :soid,
            poid = :pocust,
            custid = :custid,
            salesid = :salesid,
            kontransbrgid = :nopi,
            jatuhtempo = :term,
            discount = :discount,
            actor = :nama,
            kasir = :kasir,
            upddate = getdate(),
            upduser = :upduser,
            fgtax = :fgtax,
            nilaitax = :nilaitax,
            note = :note,
            dp = :dp,
            alamatkirim = :alamat
            
            WHERE saleid = :saleid',
            [
                'saleid' => $param['saleid'],
                'transdate' => $param['transdate'],
                'soid' => $param['soid'],
                'pocust' => $param['pocust'],
                'custid' => $param['custid'],
                'nopi' => $param['nopi'],
                'salesid' => $param['salesid'],
                'term' => $param['term'],
                'discount' => $param['discamount'],
                'nama' => $param['nama'],
                'kasir' => $param['upduser'],
                'upduser' => $param['upduser'],
                'fgtax' => $param['fgtax'],
                'nilaitax' => $param['nilaitax'],
                'alamat' => $param['alamat'],
                'note' => $param['note'],
                'dp' => $param['dp']
            ]
        );

        return $result;
    }

    function getListData($param)
    {
        if ($param['sortby'] == 'id') {
            $order = ' a.saleid ';
        } else if ($param['sortby'] == 'dateold') {
            $order = 'a.transdate ';
        } else {
            $order = ' a.transdate DESC';
        }

        $result = DB::select(
            "SELECT a.saleid,a.transdate,a.soid,a.custid,a.poid as pocust,isnull(a.kontransbrgid,'') as nopi,isnull(a.taxid,'') as taxid,a.note,b.custname,a.salesid,c.salesname,a.jatuhtempo as term,
            DATEADD(DAY,ISNULL(a.jatuhtempo,0),a.transdate) as jatuhtempo,
            a.administrasi,a.actor as nama,isnull(a.alamatkirim,'') as alamat,b.email as npwp,a.discount as discamount,isnull(a.stpj-a.discount,0) as subtotal,
            a.fgtax,a.dp,a.ppnfee as nilaitax,a.ppn as taxamount,a.ttlpj as grandtotal,
            isnull((select sum(x.qty*x.komisi) from artrpenjualandt x where x.saleid=a.saleid),0) as totalbagasi,
            a.upddate,a.upduser
            FROM artrpenjualanhd a
            inner join armscustomer b on b.CustID=a.custid
            inner join armssales c on c.salesid=a.salesid
            where convert(varchar(10),a.transdate,112) between :dari and :sampai 
            and isnull(a.custid,'') like :custidkeyword 
            and isnull(b.custname,'') like :custnamekeyword
            and isnull(a.salesid,'') like :salesidkeyword
            and isnull(c.salesname,'') like :salesnamekeyword
            and isnull(a.soid,'') like :soidkeyword
            and isnull(a.nama,'') like :namakeyword
            and a.saleid like :keyword
            order by $order",
            [
                'dari' => $param['dari'],
                'sampai' => $param['sampai'],
                'keyword' => '%' . $param['keyword'] . '%',
                'soidkeyword' => '%' . $param['soidkeyword'] . '%',
                'namakeyword' => '%' . $param['namakeyword'] . '%',
                'custidkeyword' => '%' . $param['custidkeyword'] . '%',
                'custnamekeyword' => '%' . $param['custnamekeyword'] . '%',
                'salesidkeyword' => '%' . $param['salesidkeyword'] . '%',
                'salesnamekeyword' => '%' . $param['salesnamekeyword'] . '%'
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.saleid,a.transdate,a.soid,a.custid,a.poid as pocust,isnull(a.kontransbrgid,'') as nopi,isnull(a.taxid,'') as taxid,a.note,b.custname,a.salesid,c.salesname,a.jatuhtempo as term,
            DATEADD(DAY,ISNULL(a.jatuhtempo,0),a.transdate) as jatuhtempo,
            a.administrasi,a.actor as nama,isnull(a.alamatkirim,'') as alamat,a.discount as discamount,isnull(a.stpj-a.discount,0) as subtotal,
            a.fgtax,a.dp,a.nilaitax as nilaitax,a.ppn as taxamount,a.ttlpj as grandtotal,
            isnull((select sum(x.qty*x.komisi) from artrpenjualandt x where x.saleid=a.saleid),0) as totalbagasi,
            a.upddate,a.upduser
            FROM artrpenjualanhd a
            inner join armscustomer b on b.CustID=a.custid
            inner join armssales c on c.salesid=a.salesid
            where a.saleid = :saleid",
            [
                'saleid' => $param['saleid']
            ]
        );

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM artrpenjualanhd WHERE saleid = :saleid',
            [
                'saleid' => $param['saleid']
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($transdate)
    {

        $year = substr($transdate, 2, 2);

        $month = substr($transdate, 4, 2);

        $autoNumber = $this->autoNumber($this->table, 'saleid', 'GGS.' . $year . '.' . $month . '.', '0000');

        return $autoNumber;
    }

    function hitungTotal($param)
    {

        $result = DB::selectONe(
            "SELECT k.subtotal,
            case when k.fgtax='y' then (k.subtotal-k.dp)*k.nilaitax * 0.01 else 0 end as pajak,
            (k.subtotal-k.dp)+(k.subtotal-k.dp)*k.nilaitax *0.01 +k.ongkir as total,k.modal from (
            select isnull(sum(qty*price),0) as subtotal,isnull(b.dp,0) as dp,isnull(sum(qty*modal),0) as modal,
			isnull(b.administrasi,0) as ongkir,b.fgtax,isnull(b.nilaitax,0) as nilaitax
            from artrpenjualandt a
            inner join artrpenjualanhd b on a.saleid=b.saleid
            where a.saleid=:saleid
            group by b.dp,b.administrasi,b.fgtax,b.nilaitax) as k
",
            [
                'saleid' => $param['saleid']
            ]
        );

        return $result;
    }

    function updateTotal($param)
    {
        $result = DB::update(
            'UPDATE artrpenjualanhd SET ttlpj = :total, stpj = :subtotal, ppn = :pajak, hpp = :modal  WHERE saleid = :saleid ',
            [
                'saleid' => $param['saleid'],
                'total' => $param['total'],
                'subtotal' => $param['subtotal'],
                'pajak' => $param['pajak'],
                'modal' => $param['modal'],
            ]
        );

        return $result;
    }

    function deleteAllItem($param)
    {
        $result = DB::delete(
            'DELETE from allitem where voucherno = :saleid ',
            [
                'saleid' => $param['saleid']
            ]
        );

        return $result;
    }

    function insertAllItem($param)
    {
        $result = DB::insert(
            "INSERT allitem (VoucherNo,TransDate,WareHouseId,ItemID,FgTrans,Qty,Price,ModuleID,TempField2) values
            (:saleid,:transdate,:warehouseid,:itemid,55,:qty,:price,'AR',:custname) ",
            [
                'saleid' => $param['saleid'],
                'transdate' => $param['transdate'],
                'warehouseid' => $param['warehouseid'],
                'itemid' => $param['itemid'],
                'qty' => $param['qty'],
                'price' => $param['price'],
                'custname' => $param['custname']
            ]
        );

        return $result;
    }

    // function insertAllLog($param)
    // {
    //     $result = DB::insert(
    //         "INSERT INTO AllPenjualan (saleid,salesid,soid,itemid,price,qty,upddate,upduser,custid,discount,currid,transdate,dp,nama,administrasi) 
    //          VALUES (:saleid,:salesid,:soid,:itemid,:price,:qty,getdate(),:upduser,:custid,:discount,'IDR',:transdate,:dp,:nama,:administrasi) ",
    //         [
    //             'saleid' => $param['saleid'],
    //             'salesid' => $param['salesid'],
    //             'soid' => $param['soid'],
    //             'itemid' => $param['itemid'],
    //             'qty' => $param['qty'],
    //             'price' => $param['price'],
    //             'upduser' => $param['upduser'],
    //             'custid' => $param['custid'],
    //             'discount' => $param['discount'],
    //             'transdate' => $param['transdate'],
    //             'dp' => $param['dp'],
    //             'nama' => $param['nama'],
    //             'administrasi' => $param['administrasi']
    //         ]
    //     );

    //     return $result;
    // }

    function cekPenjualan($saleid)
    {
        $result = DB::selectOne(
            'SELECT * from artrpenjualanhd WHERE saleid = :saleid',
            [
                'saleid' => $saleid
            ]
        );

        return $result;
    }

    function cekBayar($saleid)
    {
        $result = DB::select(
            "SELECT transdate,voucherid,amount as amount,isnull(bankid,'') as bankid,bankname from (
            select b.transdate,a.voucherid,a.amount,b.bankid,a.note,c.rekeningname,b.flagkkbb
            from cftrkkbbdt a inner join cftrkkbbhd b on a.voucherid=b.voucherid
			inner join cfmsbank c on a.bankid=c.bankid
            ) as k 
            where k.note= :saleid and 
			k.flagkkbb in ('ARB','ARK','ARC')
            order by k.transdate",
            [
                'saleid' => $saleid
            ]
        );

        return $result;
    }

    function totalBayar($saleid)
    {
        $result = DB::select(
            "SELECT sum(amount) as total from (
            select a.amount
            from cftrkkbbdt a inner join cftrkkbbhd b on a.voucherid=b.voucherid
			where b.flagkkbb in ('ARB','ARK','ARC')
            ) as k
           where k.note= :saleid ",
            [
                'saleid' => $saleid
            ]
        );

        return $result;
    }

    function cariSO($param)
    {
        $result = DB::select(
            "SELECT distinct k.poid as soid,prid as pocust,k.transdate,k.salesid,m.salesname,k.custid,l.custname as custname,k.total from ( 
            select a.poid,b.prid,b.transdate,b.salesid,b.custid,isnull(b.ttlso,0) as total,b.jenis,a.qty,b.fgclose,
            isnull((select sum(x.qty) from artrpenjualandt x inner join artrpenjualanhd y on x.saleid=y.saleid where y.soid=a.poid and x.itemid=a.itemid),0) as invoice 
            from artrpurchaseorderdt a inner join artrpurchaseorderhd b on a.poid=b.poid 
            ) as k
            inner join armscustomer l on k.custid=l.custid
            inner join armssales m on k.salesid=m.salesid
            where k.jenis='Y' and k.fgclose='T'  and k.qty-k.invoice>0 AND CONVERT(varchar(10),k.transdate,112) <= :tanggal
            AND k.POID like :soidkeyword and L.custname like :custnamekeyword
            and k.custid like :custidkeyword and k.salesid like :salesidkeyword and m.salesname like :salesnamekeyword
            ORDER BY K.POID",
            [
                'tanggal' => $param['tanggal'],
                'soidkeyword' => '%' . $param['soidkeyword'] . '%',
                'custidkeyword' => '%' . $param['custidkeyword'] . '%',
                'custnamekeyword' => '%' . $param['custnamekeyword'] . '%',
                'salesidkeyword' => '%' . $param['salesidkeyword'] . '%',
                'salesnamekeyword' => '%' . $param['salesnamekeyword'] . '%'
            ]
        );

        return $result;
    }

    function cekSalesOrder($soid)
    {
        $result = DB::selectOne(
            'SELECT * from artrpurchaseorderhd WHERE poid = :soid',
            [
                'soid' => $soid
            ]
        );

        return $result;
    }

    function cariFPS($param)
    {
        $result = DB::select(
            "SELECT taxid from armskdpajak 
            where taxid like :taxidkeyword 
            -- and fgactive='Y' 
            and taxid not in (select isnull(taxid,'-') from artrpenjualanhd) 
            order by taxid",
            [
                'taxidkeyword' => '%' . $param['taxidkeyword'] . '%'
            ]
        );

        return $result;
    }

    function cariPi($param)
    {
        $result = DB::select(
            "SELECT performaid as nopi,convert(varchar(10),transdate,103) as tanggal,isnull(poid,'') as poid,isnull(dp*subtotal*0.01,0) as dp 
             from artrperformahd 
             where custid=:custid 
             and soid=:soid 
             and performaid like :nopikeyword
             and isnull(poid,'') like :poidkeyword
             and performaid not in (select kontransbrgid from artrpenjualanhd where fgform='DP' and custid=:custid1)",
            [
                'soid' => $param['soid'],
                'custid' => $param['custid'],
                'custid1' => $param['custid'],
                'nopikeyword' => '%' . $param['nopikeyword'] . '%',
                'poidkeyword' => '%' . $param['poidkeyword'] . '%'
            ]
        );

        return $result;
    }
}