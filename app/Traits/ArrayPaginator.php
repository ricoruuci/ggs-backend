<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait ArrayPaginator
{
    protected function arrayPaginator(Request $request, $data)
    {   
        if ($request->input('alldata')) {
            $page = 1;
            $totalData = count($data);
            $perPage = $totalData;
            $totalPage = 1;
            $offset = 0;

            $result = [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalData,
                'total_page' => $totalPage,
                'data' => $data
            ];
        }
        else
        {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $totalData = count($data);
            $totalPage = ceil($totalData/$perPage);
            $offset = ($page * $perPage) - $perPage;

            $collectionData = new LengthAwarePaginator(
                array_slice($data, $offset, $perPage, false), 
                count($data), 
                $perPage, 
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
    
            $arrayData = $collectionData->toArray();
    
            // disini setting nama-nama field/element yang mau ditampilkan di response API
            $result = [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalData,
                'total_page' => $totalPage,
                'data' => $arrayData['data']
            ];
        }; 

        return $result;

        
    }
}