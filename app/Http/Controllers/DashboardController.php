<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Si el usuario es admin, ve todo. Si no, solo su área.
        // Simulamos un usuario con área asignada si existe (esto se conectará con Auth)
        $user = $request->user();
        
        if ($user && !$user->is_admin && $user->area_id) {
            $areas = Area::where('id', $user->area_id)->get();
        } else {
            $areas = Area::all();
        }
        
        return view('global-dashboard', compact('areas'));
    }

    public function almacen()
    {
        return view('almacen.index');
    }

    public function hr()
    {
        return view('hr.index');
    }

    public function risk()
    {
        return view('risk.index');
    }

    public function planning()
    {
        return view('planning.index');
    }
}
