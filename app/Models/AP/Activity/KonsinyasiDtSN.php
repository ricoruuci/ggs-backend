<?php

namespace App\Models\AP\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class KonsinyasiDtSN extends Model
{
    use HasFactory;

    protected $table = 'aptrkonsinyasidtsn';

    public $timestamps = false;

    public static $rulesInsert = [
        'detailsn.*.snid' => 'required'
    ];

    public static $messagesInsert = [
        'detailsn.*.snid.required' => 'Kolom SN  (posisi index ke-:position) harus diisi.'
    ];

    public function insertData($param)
    {



        $result = DB::insert(
            "INSERT INTO aptrkonsinyasidtsn
            (konsinyasiid,itemid,snid,fgjual,fgsn,upddate,upduser)
            VALUES 
            (:grnid,:itemid,:snid,:fgjual,'T',getdate(),:upduser)",
            [
                'grnid' => $param['grnid'],
                'itemid' => $param['itemid'],
                'snid' => $param['snid'],
                'fgjual' => $param['fgjual'],
                'upduser' => $param['upduser']
            ]
        );

        return $result;
    }

    function getData($param)
    {
        $result = DB::select(
            "SELECT a.snid,a.fgjual
            FROM aptrkonsinyasidtsn a WHERE a.konsinyasiid = :grnid and itemid = :itemid ",
            [
                'grnid' => $param['grnid'],
                'itemid' => $param['itemid']
            ]
        );

        return $result;
    }


    function deleteData($param)
    {

        $result = DB::delete(
            'DELETE FROM aptrkonsinyasidtsn WHERE konsinyasiid = :grnid  ',
            [
                'grnid' => $param['grnid']
            ]
        );

        return $result;
    }

    public function cekLastSN($grnid, $itemid)
    {
        $result = DB::selectOne(
            'SELECT top 1 snid from aptrkonsinyasidtsn WHERE konsinyasiid = :grnid and itemid = :itemid order by snid desc',
            [
                'grnid' => $grnid,
                'itemid' => $itemid
            ]
        );

        return $result;
    }


    public function autoGenerateSN($grnid, $itemid, $qty)
    {
        $serialNumbers = []; // Array untuk menyimpan SN yang dihasilkan

        // Gunakan range untuk membuat array dengan jumlah $qty
        $numbers = range(1, $qty);
        $date = date('ymd');
        $time = date('His');

        // Loop menggunakan foreach untuk generate serial number
        foreach ($numbers as $number) {
            // Format SN dengan kombinasi GRN ID, Item ID, dan nomor urut (atau format lain yang diperlukan)
            $serialNumber = "SN" . '-'  . $date . $time . str_pad($number, 3, '0', STR_PAD_LEFT);

            // Tambahkan SN yang dihasilkan ke dalam array
            $serialNumbers[] = $serialNumber;
        }

        // Kembalikan array berisi serial number yang telah di-generate
        return $serialNumbers;
    }
}
