<?php

namespace App\Models\AR\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use function PHPUnit\Framework\isNull;

class ARMsSales extends BaseModel
{
    use HasFactory;

    protected $table = 'armssales';

    public $timestamps = false;

    public static $rulesInsert = [
        'salesname' => 'required'
    ];

    public static $messagesInsert = [
        'salesname' => 'Kolom nama pelanggan harus diisi.'
    ];

    public static $rulesUpdateAll = [
        'salesid' => 'required',
        'salesname' => 'required'
    ];

    public static $messagesUpdate = [
        'salesid' => 'Kode sales tidak ditemukan.',
        'salesname' => 'Kolom nama sales harus diisi.'
    ];

    public function insertData($param)
    {

        $result = DB::insert(
            "INSERT INTO armssales
            (salesid,salesname,address,phone,hp,email,note,upddate,upduser,jabatan,uangmakan,uangbulanan,fgactive,tglgabung,limitkasbon,kerajinan,
            tomzet,kom1,kom2,kom3,kom4)
            VALUES
            (:salesid,:salesname,:address,:telp,:hp,:email,:note,getdate(),:upduser,:jabatan,0,0,:fgactive,:tglgabung,0,0,
            :tomzet,:kom1,:kom2,:kom3,:kom4)",
            [
                'salesid' => $param['salesid'],
                'salesname' => $param['salesname'],
                'address' => $param['address'],
                'telp' => $param['telp'],
                'hp' => $param['hp'],
                'email' => $param['email'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'jabatan' => $param['jabatan'],
                'fgactive' => $param['fgactive'],
                'tglgabung' => $param['tglgabung'],
                'tomzet' => $param['tomzet'],
                'kom1' => $param['kom1'],
                'kom2' => $param['kom2'],
                'kom3' => $param['kom3'],
                'kom4' => $param['kom4']
            ]
        );

        return $result;
    }

    function updateAllData($param)
    {
        $result = DB::update(
            "UPDATE armssales SET
            salesname = :salesname,
            address = :address,
            phone = :telp,
            hp = :hp,
            email = :email,
            note = :note,
            upddate = getdate(),
            upduser = :upduser,
            jabatan = :jabatan,
            fgactive =:fgactive,
            tglgabung = :tglgabung,
            tomzet = :tomzet,
            kom1 = :kom1,
            kom2 = :kom2,
            kom3 = :kom3,
            kom4 = :kom4
            
            WHERE salesid = :salesid ",
            [
                'salesid' => $param['salesid'],
                'salesname' => $param['salesname'],
                'address' => $param['address'],
                'telp' => $param['telp'],
                'hp' => $param['hp'],
                'email' => $param['email'],
                'note' => $param['note'],
                'upduser' => $param['upduser'],
                'jabatan' => $param['jabatan'],
                'fgactive' => $param['fgactive'],
                'tglgabung' => $param['tglgabung'],
                'tomzet' => $param['tomzet'],
                'kom1' => $param['kom1'],
                'kom2' => $param['kom2'],
                'kom3' => $param['kom3'],
                'kom4' => $param['kom4']
            ]
        );

        return $result;
    }

    function getListData($param)
    {
        if ($param['sortby'] == 'salesid') {
            $order = 'a.salesid';
        } else {
            $order = 'a.salesname';
        }

        if ($param['active'] == 'all') {
            $active = '0,1,2';
        } else if ($param['active'] == 'ya') {
            $active = '1';
        } else {
            $active = '0,2';
        }

        $result = DB::select(
            "SELECT a.salesid,a.salesname,a.address,a.phone as telp,a.hp,a.email,a.jabatan,a.tglgabung,
            a.upddate,a.upduser,a.note,a.fgactive,
            case when a.fgactive=0 then 'TIDAK' when a.fgactive=1 then 'YA' else 'ISIDENTIL' end as statusactive,
            a.kom1,a.kom2,a.kom3,a.kom4,a.tomzet
            from armssales a
            where a.salesid like :salesidkeyword and a.salesname like :salesnamekeyword
            and a.fgactive in ($active)
            order by $order",
            [
                'salesidkeyword' => '%' . $param['salesidkeyword'] . '%',
                'salesnamekeyword' => '%' . $param['salesnamekeyword'] . '%'
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::selectOne(
            "SELECT a.salesid,a.salesname,a.address,a.phone as telp,a.hp,a.email,a.jabatan,a.tglgabung,
            a.upddate,a.upduser,a.note,a.fgactive,
            case when a.fgactive=0 then 'TIDAK' when a.fgactive=1 then 'YA' else 'ISIDENTIL' end as statusactive,
            a.kom1,a.kom2,a.kom3,a.kom4,a.tomzet
            from armssales a where a.salesid=:salesid",
            [
                'salesid' => $param['salesid']
            ]
        );

        return $result;
    }

    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM armssales WHERE salesid = :salesid',
            [
                'salesid' => $param['salesid']
            ]
        );

        return $result;
    }

    function cekSales($salesid)
    {

        $result = DB::selectOne(
            'SELECT * from armssales WHERE salesid = :salesid',
            [
                'salesid' => $salesid
            ]
        );

        return $result;
    }

    public function beforeAutoNumber($salesname)
    {

        $nama = strtoupper(substr($salesname, 0, 1));

        $autoNumber = $this->autoNumber($this->table, 'salesid', $nama, '000');

        return $autoNumber;
    }
}
