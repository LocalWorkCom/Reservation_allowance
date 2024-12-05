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
        $main = 1;

        if (Auth::user()->rule->id != 1 && Auth::user()->rule->id != 2) {
            $check = departements::where('manger', auth()->user()->id)->first();

            if ($check && !$check->parent_id) {
                $main = 1;
            } else {
                $main = 0;
            }
        }
        $sectorCount = Sector::where('manager', auth()->user()->id)->first();
        if ($sectorCount) {
            $main = 1;
        }

        // dd(0);
        if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2) { //superadmin
            $empCount = User::where('flag', 'employee')->count();
            $depMainCount = departements::where('parent_id', null)->count();
            $depChiledCount = departements::whereNotNull('parent_id')->count();
            $sectorCount = Sector::count();
        } elseif (Auth::user()->rule->id == 4) {
            $empCount = User::where('flag', 'employee')->where('sector', auth()->user()->sector)->count();
            $depMainCount = departements::where('parent_id', null)->where('sector_id', auth()->user()->sector)->count();
            // $depChiledCount = departements::whereNotNull('parent_id')->where('sector_id', auth()->user()->sector)->count();
            $depChiledCount = departements::where('parent_id',   auth()->user()->department_id)->count();
            $dep = departements::whereNull('parent_id')->where('id', auth()->user()->department_id)->first();

            $sec_id = ($dep) ? $dep->sector_id : 0;
            $sectorCount = Sector::where('id', $sec_id)->count();
        } elseif (Auth::user()->rule->id == 3) {
            $empCount = User::where('flag', 'employee')->where('department_id', auth()->user()->department_id)->count();
            $depMainCount = departements::where('parent_id', null)->where('id', auth()->user()->department_id)->count();
            $depChiledCount = departements::where('parent_id',  auth()->user()->department_id)->orwhere('manger',  auth()->user()->id)->count();
            // dd($depChiledCount);
            // $depSector = departements::find(auth()->user()->department_id);
            $dep = departements::whereNull('parent_id')->where('id', auth()->user()->department_id)->first();
            $sec_id = ($dep) ? $dep->sector_id : 0;

            $sectorCount = Sector::where('id', $sec_id)->count();
        } else {
            $empCount = User::where('flag', 'employee')->where('department_id', auth()->user()->department_id)->count();
            $depMainCount = departements::where('parent_id', null)->where('id', auth()->user()->department_id)->count();
            $depChiledCount = departements::where('parent_id',  auth()->user()->department_id)->orwhere('manger',  auth()->user()->id)->count();
            // dd($depChiledCount);
            // $depSector = departements::find(auth()->user()->department_id);
            $dep = departements::whereNull('parent_id')->where('id', auth()->user()->department_id)->first();
            $sec_id = ($dep) ? $dep->sector_id : 0;

            $sectorCount = Sector::where('id', $sec_id)->count();
        }

        $user = auth()->user();
        $userGrade = $user->grade->name ?? 'N/A'; // Assuming the `grade` relationship exists
        return view('home.index', compact('empCount', 'depMainCount', 'depChiledCount', 'sectorCount', 'userGrade', 'main'));
    }
}
