<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    use ArrayPaginator, HttpResponse;


    public function getOtoAbsen(Request $request)
    {
        $barang = new Absensi();

        $result = $barang->getOtoAbsen();

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }

    public function postOtoAbsen(Request $request)
    {
        $barang = new Absensi();

        DB::beginTransaction();

        try {
            $insertheader = $barang->updateReqAbsen([
                'transid' => $request->input('transid'),
                'upduser' => Auth::user()->currentAccessToken()['namauser'],
                'jenis' => $request->input('jenis')
            ]);

            if ($insertheader) {
                $updateabsen = $barang->postOtoAbsen([
                    'transid' => $request->input('transid'),
                    'userid' => Auth::user()->currentAccessToken()['namauser'],
                ]);

                if ($updateabsen) {

                    $result = [
                        'success' => true
                    ];
                } else {
                    DB::rollBack();

                    return $this->responseError('otorisasi gagal', 400);
                }
            } else {
                DB::rollBack();

                return $this->responseError('otorisasi gagal', 400);
            }

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function insertAbsen(Request $request)
    {
        $barang = new Absensi();

        $sales = DB::selectOne(
            "SELECT salesid FROM sysmsuser WHERE userid = :userid1",
            [
                'userid1' => Auth::user()->currentAccessToken()['namauser']
            ]
            );

            $salesid = $sales->salesid ?? null;

            if (!$salesid || $salesid === null) {
                return $this->responseError('User tidak ditemukan di master sales', 400);
            }


        $cek = $barang->cekAbsen(Auth::user()->currentAccessToken()['namauser']);
        // dd(var_dump($cek));

        if ($cek == true) {

            return $this->responseError('Anda sudah absen hari ini, tidak bisa absen lagi', 400);
        }

        DB::beginTransaction();

        try {
            $insertheader = $barang->insertAbsen([
                'userid' => Auth::user()->currentAccessToken()['namauser'],
                'keterangan' => $request->input('keterangan'),
                'keperluan' => $request->input('keperluan'),
                'foto' => $request->input('foto')
            ]);

            if ($insertheader) {
                $result = [
                    'success' => true
                ];
            } else {
                DB::rollBack();

                return $this->responseError('insert absen gagal', 400);
            }

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function getLaporanAbsen(Request $request)
    {
        $user = new Absensi();

        $result = $user->getLaporanAbsen(
            [
                // "userid" => Auth::user()->currentAccessToken()['namauser'],
                "userid" => $request->input('userid'),
                "dari" => $request->input('dari'),
                "sampai" => $request->input('sampai')
            ]
        );

        $resultPaginated = $this->arrayPaginator($request, $result);

        return $this->responsePagination($resultPaginated);
    }
}
