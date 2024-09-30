<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;
use Stringable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'sysmsuser';

    public $timestamps = false;

    public static function myCrypt($password)
    {
        $cryptedPassword = '';
        $rotate = 41;
        $pan = strlen($password);
        $data = array();

        //memasukkan password menjadi array data[]
        for ( $i = 1; $i <= $pan; $i = $i + 1) 
        {
            $data[$i] = substr($password,$i-1,1);
        }
        
        //melakukan pengacakan array data[]
        for ( $i = 1; $i <= $pan; $i = $i + 1 ) 
        {
            $tamp = $data[$i];
            $ctr = ($i*$rotate) % $pan;
            if ($ctr == 0) {
                $ctr = $pan;
            }
            $data[$i] = $data[$ctr];
            $data[$ctr] = $tamp; 
        }

        //melakukan enkripsi dari variabel data[]
        for ( $i = 1; $i <= $pan; $i = $i + 1 ) 
        {
            $asc = ord($data[$i]);
            $asc = $asc + ($i * $rotate);
            $kumpul = $asc / 16 + 65;
            $hsl1 = chr($kumpul);
            $kumpul = $asc % 16 + 65;
            $hsl2 = chr($kumpul);
            $cryptedPassword = $cryptedPassword . $hsl1 . $hsl2;
        } 

        return $cryptedPassword;
    }

    public function isLoginValid($userid, $password)
    {
        $password = $this::myCrypt($password);

        $result = DB::selectOne(
            'SELECT userid FROM sysmsuser WHERE userid = :userid AND passwd = :password',
            [
                'userid' => $userid,
                'password' => $password
            ]
        );

        if($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function deleteData($param)
    {
        $deleted = DB::delete(
            'DELETE FROM personal_access_tokens WHERE tokenable_id = :tokenable_id',
            [
                'tokenable_id' => $param['tokenable_id']
            ]
        );
    }

    function updateData($id)
    {
        $updated = DB::update(
            'update personal_access_tokens set expires_at=dateadd(day,1,created_at),namauser=(select userid from sysmsuser where id=tokenable_id ) where id=:id',
            [
                'id' => $id
            ]
        );
    }

}