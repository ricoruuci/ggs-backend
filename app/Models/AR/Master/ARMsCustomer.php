<?php

namespace App\Models\AR\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class ARMsCustomer extends BaseModel
{
    use HasFactory;

    protected $table = 'armscustomer';

    public $timestamps = false;

    public static $rulesInsert = [
        'custname' => 'required'
    ];

    public static $messagesInsert = [
        'custname' => 'Kolom nama pelanggan harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'custid' => 'required',
        'custname' => 'required'
    ];

    public static $messagesUpdate = [
        'custid' => 'Kode pelanggan tidak ditemukan.',
        'custname' => 'Kolom nama pelanggan harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO armscustomer
            (custid,custname,address,city,phone,fax,email,note,custtype,upddate,upduser,limitpiutang,limitasli,term,salesid,fgkoma,hterm,up,alamat) 
            VALUES 
            (:custid,:custname,:alamat,:kota,:telp,:fax,:email,:note,:tipe,getdate(),:upduser,:limitpiutang,:limitasli,:term,:salesid,'Y',:termin,:cp,:alamatnpwp)",
            [
                'custid' => $param['custid'],
                'custname' => $param['custname'],
                'alamat' => $param['alamat'],
                'kota' => $param['kota'],
                'telp' => $param['telp'],
                'fax' => $param['fax'],
                'email' => $param['email'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'limitpiutang' => $param['limitpiutang'],
                'limitasli' => $param['limitpiutang'],
                'termin' => $param['termin'],
                'salesid' => $param['salesid'],
                'cp' => $param['cp'],
                'term' => $param['term'],
                'alamatnpwp' => $param['alamatnpwp'],
                'tipe' => $param['tipe']
            ]
        );

        return $result;
    }

    function getListData($param)
    {
        if ($param['sortby'] == 'custid') {
            $order = 'a.custid';
        } else {
            $order = 'a.custname';
        }

        $result = DB::select(
            "SELECT a.custid,a.custname,isnull(a.Address,'') as alamat,isnull(a.city,'') as kota,isnull(a.up,'') as contactperson,isnull(a.phone,'') as telp,
            isnull(a.fax,'') as fax,isnull(a.email,'') as email,isnull(a.limitpiutang,0) as limitpiutang,isnull(a.term,'') as term,isnull(a.hterm,0) as termin,
            isnull(a.note,'') as note,a.upddate,a.upduser,a.salesid,
            isnull((select b.salesname from armssales b where b.salesid=a.salesid),'') as salesname,isnull(a.alamat,'') as alamatnpwp     
            from armscustomer a 
            where a.custid like :custidkeyword and a.custname like :custnamekeyword
            order by $order ",
            [
                'custidkeyword' => '%' . $param['custidkeyword'] . '%',
                'custnamekeyword' => '%' . $param['custnamekeyword'] . '%'
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.custid,a.custname,isnull(a.Address,'') as alamat,isnull(a.city,'') as kota,isnull(a.up,'') as contactperson,isnull(a.phone,'') as telp,
            isnull(a.fax,'') as fax,isnull(a.email,'') as email,isnull(a.limitpiutang,0) as limitpiutang,isnull(a.term,'') as term,isnull(a.hterm,0) as termin,
            isnull(a.note,'') as note,a.upddate,a.upduser,a.salesid,
            isnull((select b.salesname from armssales b where b.salesid=a.salesid),'') as salesname,isnull(a.alamat,'') as alamatnpwp
            from ARMsCustomer a WHERE a.custid = :custid ",
            [
                'custid' => $param['custid']
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE armscustomer SET 
            custname = :custname, 
            address = :alamat,
            city = :kota,
            phone = :telp,
            fax = :fax,
            email = :email,
            note = :note,
            upddate = getdate(), 
            upduser = :upduser,
            limitpiutang = :limitpiutang,
            limitasli =:limitasli,
            term = :term,
            hterm = :termin,
            salesid = :salesid,
            up = :cp,
            alamat = :alamatnpwp,
            custtype = :tipe
            WHERE custid = :custid',
            [
                'custid' => $param['custid'],
                'custname' => $param['custname'],
                'alamat' => $param['alamat'],
                'kota' => $param['kota'],
                'telp' => $param['telp'],
                'fax' => $param['fax'],
                'email' => $param['email'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'limitpiutang' => $param['limitpiutang'],
                'limitasli' => $param['limitpiutang'],
                'termin' => $param['termin'],
                'cp' => $param['cp'],
                'term' => $param['term'],
                'salesid' => $param['salesid'],
                'alamatnpwp' => $param['alamatnpwp'],
                'tipe' => $param['tipe']
            ]
        );

        return $result;
    }


    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM armscustomer WHERE custid = :custid',
            [
                'custid' => $param['custid']
            ]
        );

        return $result;
    }

    function cekCustomer($custid)
    {

        $result = DB::selectOne(
            'SELECT * from armscustomer WHERE custid = :custid',
            [
                'custid' => $custid
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($custname)
    {

        $nama = substr($custname, 0, 1);

        $autoNumber = $this->autoNumber($this->table, 'custid', $nama, '000');

        return $autoNumber;
    }
}
