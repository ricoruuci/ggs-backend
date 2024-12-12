<?php

namespace App\Models\AP\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class APMsSupplier extends BaseModel
{
    use HasFactory;

    protected $table = 'apmssupplier';

    public $timestamps = false;
    
    public static $rulesInsert = [
        'suppname' => 'required'
    ];

    public static $messagesInsert = [
        'suppname' => 'Kolom nama supplier harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'suppid' => 'required',
        'suppname' => 'required'
    ];

    public static $messagesUpdate = [
        'suppid' => 'Kode supplier tidak ditemukan.',
        'suppname' => 'Kolom nama supplier harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO apmssupplier
            (suppid, suppname, address, city, contactperson, phone, fax, email, note, term, upddate, upduser) 
            VALUES 
            (:suppid, :suppname, :alamat, :kota, :contact, :telp, :fax, :email, :note, :termin, getdate(), :upduser)", 
            [
                'suppid' => $param['suppid'],
                'suppname' => $param['suppname'],
                'alamat' => $param['alamat'],
                'kota' => $param['kota'],
                'contact' => $param['contact'],
                'telp' => $param['telp'],
                'fax' => $param['fax'],
                'email' => $param['email'],
                'note' => $param['note'],
                'termin' => $param['termin'],
                'upduser' => $param['upduser']
            ]
        );        

        return $result;
    }

    function getListData($param)
    {
        if($param['sortby']=='suppid')
        {
            $order = 'suppid';
        }
        else 
        {
            $order = 'suppname';
        }
        
        $result = DB::select(
            "SELECT suppid,suppname,isnull(address,'') as alamat,isnull(city,'') as kota,
            isnull(contactperson,'') as contact,isnull(phone,'') as telp,isnull(fax,'') as fax,
            isnull(email,'') as email,isnull(note,'') as note,isnull(term,30) as termin,
            upddate,upduser from apmssupplier 
            WHERE suppid like :suppidkeyword and suppname like :suppnamekeyword
            order by $order",
            [
                'suppidkeyword' => '%'.$param['suppidkeyword'].'%',
                'suppnamekeyword' => '%'.$param['suppnamekeyword'].'%'
            ]
        );

        return $result;
    }

    function getData($param)
    {

        $result = DB::selectOne(
            "SELECT suppid,suppname,isnull(address,'') as alamat,isnull(city,'') as kota,
            isnull(contactperson,'') as contact,isnull(phone,'') as telp,isnull(fax,'') as fax,
            isnull(email,'') as email,isnull(note,'') as note,isnull(term,30) as termin,
            upddate,upduser from apmssupplier WHERE suppid = :suppid",
            [
                'suppid' => $param['suppid']
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            'UPDATE apmssupplier SET 
            suppname = :suppname, 
            address = :alamat,
            city = :kota,
            contactperson = :contact,
            phone = :telp,
            fax = :fax,
            email = :email,
            note = :note,
            term = :termin,
            upddate = getdate(), 
            upduser = :upduser 
            WHERE suppid = :suppid',
            [
                'suppid' => $param['suppid'],
                'suppname' => $param['suppname'],
                'alamat' => $param['alamat'],
                'kota' => $param['kota'],
                'contact' => $param['contact'],
                'telp' => $param['telp'],
                'fax' => $param['fax'],
                'email' => $param['email'],
                'note' => $param['note'],
                'termin' => $param['termin'],
                'upduser' => $param['upduser']
            ]
        );

        return $result;
    }


    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM apmssupplier WHERE suppid = :suppid',
            [
                'suppid' => $param['suppid']
            ]
        );

        return $result;
    }

    function cekSupplier($suppid)
    {

        $result = DB::selectOne(
            'SELECT * from apmssupplier WHERE suppid = :suppid',
            [
                'suppid' => $suppid
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($suppname)
    {

        $kode = 'S';

        $nama = substr($suppname,0,1);

        $autoNumber = $this->autoNumber($this->table, 'suppid', $kode.$nama, '000');

        return $autoNumber;
    }
}