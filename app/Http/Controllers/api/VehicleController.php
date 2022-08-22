<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->first();

        return response([
            'vehicle' => $vehicle,
            'message' => 'success',
            'error'=> false
        ], 200);
    }

    public function add(Request $request)
    {
        $fields = $request->validate([
            'regnum' => 'required|string|unique:vehicles,regnum',
            'name' => 'required|string',
            'vclass' => 'required|numeric'
        ]);

        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if(!is_null($vehicle)){
            return response([
                'message' => 'A vehicle is already registered',
                'error'=> true
            ],400);
        }

        $vehicle = Vehicle::create([
            'user_id' => Auth::id(),
            'regnum' => $fields['regnum'],
            'name' => $fields['name'],
            'vclass' => $fields['vclass']
        ]);

        return response([
            'vehicle' => $vehicle,
            'message' => 'successfully added a vehicle',
            'error'=> false
        ],201);
    }

    public function update(Request $request)
    {
        $fields = $request->validate([
            'regnum' => 'required|string',
            'name' => 'required|string',
            'vclass' => 'required|numeric'
        ]);

        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if(is_null($vehicle)){
            return response([
                'message' => 'No vehicle was found',
                'error'=> true
            ],400);
        }

        $vehicle->regnum = $fields['regnum'];
        $vehicle->name = $fields['name'];
        $vehicle->vclass = $fields['vclass'];
        $vehicle->save();

        return response([
            'vehicle' => $vehicle,
            'message' => 'successfully updated vehicle details',
            'error'=> false
        ],201);
    }
}
