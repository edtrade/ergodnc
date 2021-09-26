<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Office;
use App\Http\Resources\OfficeResource;
class OfficeController extends Controller
{
    //

    public function index()
    {
        $offices = Office::query()
            ->latest()
            ->get();

        return OfficeResource::collection($offices);    
    }
}
