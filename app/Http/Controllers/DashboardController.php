<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return abort(403, 'Unauthorized');
        }else{
            $role = $user->role;
            switch ($role) {
                case 'it_admin':
                    return Inertia::render('dashboard/AdminDashboard');
                case 'dean':
                    return Inertia::render('dashboard/DeanDashboard');
                case 'program_head':
                    return Inertia::render('dashboard/ProgramHeadDashboard');
                case 'registrar':
                    return Inertia::render('dashboard/RegistrarDashboard');
                case 'instructor':
                    return Inertia::render('dashboard/InstructorDashboard');
                default:
                    return Inertia::render(component: 'dashboard/StudentDashboard');
            }
        }
    }
}

