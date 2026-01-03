<?php

use App\Http\Controllers\AcademicTermController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Department routes (IT Admin only)
    Route::middleware('role:it_admin')->group(function () {
        Route::resource('departments', DepartmentController::class);
    });

    // Program routes (IT Admin, Dean, Program Head)
    Route::middleware('role:it_admin,dean,program_head')->group(function () {
        Route::resource('programs', ProgramController::class);
    });

    // Term routes (Registrar only)
    Route::middleware('role:registrar')->group(function () {
        Route::resource('terms', AcademicTermController::class);
    });

    // Enrollment routes (Student, Registrar, Program Head)
    Route::middleware('role:student,registrar,program_head,instructor')->group(function () {
        Route::resource('enrollments', EnrollmentController::class);
    });

    // Class routes (Instructor only)
    Route::middleware('role:instructor')->group(function () {
        //Route::resource('classes', ClassControlle::class);
    });

    // Grade routes (Instructor, Registrar, Student)
    Route::middleware('role:instructor,registrar,student')->group(function () {
        //Route::resource('grades', GradeController::class);
    });

    // Report routes (Registrar only)
    Route::middleware('role:registrar')->group(function () {
        // Route::prefix('reports')->name('reports.')->group(function () {
        //     Route::get('/enrollment-statistics', [ReportController::class, 'enrollmentStatistics'])
        //         ->name('enrollment-statistics');
        //     Route::get('/generate-tor', [ReportController::class, 'generateTOR'])
        //         ->name('generate-tor');
        //     Route::post('/generate-tor/{student}', [ReportController::class, 'downloadTOR'])
        //         ->name('download-tor');
        // });
    });

});

require __DIR__.'/auth.php';
