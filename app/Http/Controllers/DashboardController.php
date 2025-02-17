<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ArrayPaginator;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ArrayPaginator, HttpResponse;

    public function getGrafikPenjualan()
    {
        $laporan = new Dashboard();

        $result = $laporan->getListData();


        return $this->responseData($result);
    }

    public function getSalesYear()
    {
        $laporan = new Dashboard();


        $header = $laporan->gettahun();
        $detail = $laporan->getRekapJualTahunan();

        $result = [
            'data' => $header,
            'total' => $detail
        ];

        return $result;
    }

    public function getTotal()
    {
        $laporan = new Dashboard();

        $result = $laporan->getTotal();


        return $this->responseData($result);
    }

    public function getUserAktif()
    {
        $laporan = new Dashboard();

        $result = $laporan->getUserAktif();


        return $this->responseData($result);
    }

    public function getHutangPiutang()
    {
        $laporan = new Dashboard();

        $result = $laporan->getNetCash();


        return $this->responseData($result);
    }

    public function getJualTahunan()
    {
        $laporan = new Dashboard();

        $result = $laporan->getJualTahunan();


        return $this->responseData($result);
    }
}
