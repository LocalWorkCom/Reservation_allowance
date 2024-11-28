<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\outgoings;
use App\Models\Iotelegram;
use App\Models\departements;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    //
    public function index(Request $request)
    {
        // dd(0);
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) { //superadmin
            $empCount = User::where('flag','employee')->count();
            $depMainCount = departements::where('parent_id', null)->count();
            $depChiledCount = departements::whereNotNull('parent_id')->count();
            $sectorCount = Sector::count();
        } elseif (Auth::user()->rule->id == 4) {
            $empCount = User::where('flag','employee')->where('sector', auth()->user()->sector)->count();
            $depMainCount = departements::where('parent_id', null)->where('sector_id', auth()->user()->sector)->count();
            $depChiledCount = departements::whereNotNull('parent_id')->where('sector_id', auth()->user()->sector)->count();
            $sectorCount = Sector::where('id', auth()->user()->sector)->count();
        } elseif (Auth::user()->rule->id == 3) {
            $empCount = User::where('flag','employee')->where('department_id', auth()->user()->department_id)->count();
            $depMainCount = departements::where('parent_id', null)->where('id', auth()->user()->department_id)->count();
            $depChiledCount = departements::whereNotNull('parent_id')->where('id', auth()->user()->department_id)->count();
            $depSector = departements::find(auth()->user()->department_id);
            $sectorCount = Sector::where('id', $depSector->sector_id)->count();
        }

        $user = auth()->user();
        $userGrade = $user->grade->name ?? 'N/A'; // Assuming the `grade` relationship exists
        return view('home.index', compact('empCount', 'depMainCount', 'depChiledCount', 'sectorCount','userGrade'));
    }
}
