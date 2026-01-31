<?php

use App\Http\Controllers\{
    DepartmentController, ProgramController, SubjectController,
    BlockController, InstructorController, StudentController,
    CurriculumController, AcademicTermController,
    ScheduledSubjectController, EnrollmentController,
    GradeController, ReportController, ClassController, DashboardController,
    ProfileController
};

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

        // IT Admin routes
    Route::middleware('role:it_admin')->group(function () {
        Route::resource('departments', DepartmentController::class);
        Route::resource('subjects', SubjectController::class);
    });

    // Admin, Dean, Program Head - Programs
    Route::middleware('role:it_admin,dean,program_head')->group(function () {
        Route::resource('programs', ProgramController::class);
        Route::resource('blocks', BlockController::class);
        Route::resource('curriculums', CurriculumController::class);
        Route::resource('instructors', InstructorController::class);
        Route::resource('students', StudentController::class);
    });

    // Registrar routes
    Route::middleware('role:registrar')->group(function () {
        Route::resource('terms', AcademicTermController::class);
        Route::post('terms/{term}/activate', [AcademicTermController::class, 'activate'])
            ->name('terms.activate');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('enrollment-statistics', [ReportController::class, 'enrollmentStatistics'])
                ->name('enrollment-statistics');
            Route::get('generate-tor', [ReportController::class, 'generateTOR'])
                ->name('generate-tor');
            Route::get('tor/{student}', [ReportController::class, 'downloadTOR'])
                ->name('download-tor');
            Route::get('export-enrollment', [ReportController::class, 'exportEnrollmentStatistics'])
                ->name('export-enrollment');
        });
    });

    // Enrollment routes
    Route::middleware('role:student,registrar,program_head')->group(function () {
        Route::resource('enrollments', EnrollmentController::class);
        Route::get('enrollments/subjects/available', [EnrollmentController::class, 'getAvailableSubjects'])
            ->name('enrollments.available-subjects');
    });

    // Classes (Instructor)
    Route::middleware('role:instructor')->group(function () {
        Route::resource('classes', ClassController::class)->only(['index', 'show']);
        Route::get('classes/{class}/attendance', [ClassController::class, 'attendance'])
            ->name('classes.attendance');

        // Scheduled subjects (for program heads creating schedules)
        Route::resource('scheduled-subjects', ScheduledSubjectController::class)
            ->except(['index']);
        Route::get('scheduled-subjects/curriculum-subjects',
            [ScheduledSubjectController::class, 'getCurriculumSubjects'])
            ->name('scheduled-subjects.curriculum-subjects');
    });

    // Grades
    Route::middleware('role:instructor,registrar,student')->group(function () {
        Route::resource('grades', GradeController::class)->only(['index']);
        Route::get('grades/{scheduledSubject}/edit', [GradeController::class, 'edit'])
            ->name('grades.edit');
        Route::put('grades/{scheduledSubject}', [GradeController::class, 'update'])
            ->name('grades.update');
        Route::put('grades/enrolled-subject/{enrolledSubject}',
            [GradeController::class, 'updateSingleGrade'])
            ->name('grades.update-single');
        Route::post('grades/drop/{enrolledSubject}', [GradeController::class, 'dropSubject'])
            ->name('grades.drop');
        Route::get('grades/{scheduledSubject}/sheet', [GradeController::class, 'classGradeSheet'])
            ->name('grades.class-sheet');
    });


    //     // Department routes (IT Admin only)
    // Route::middleware('role:it_admin')->group(function () {
    //     Route::resource('departments', DepartmentController::class);
    // });

    // // Program routes (IT Admin, Dean, Program Head)
    // Route::middleware('role:it_admin,dean,program_head')->group(function () {
    //     Route::resource('programs', ProgramController::class);
    // });

    // // Term routes (Registrar only)
    // Route::middleware('role:registrar')->group(function () {
    //     Route::resource('terms', AcademicTermController::class);
    // });

    // // Enrollment routes (Student, Registrar, Program Head)
    // Route::middleware('role:student,registrar,program_head,instructor')->group(function () {
    //     Route::resource('enrollments', EnrollmentController::class);
    // });

    // // Class routes (Instructor only)
    // Route::middleware('role:instructor')->group(function () {
    //     //Route::resource('classes', ClassControlle::class);
    // });

    // // Grade routes (Instructor, Registrar, Student)
    // Route::middleware('role:instructor,registrar,student')->group(function () {
    //     //Route::resource('grades', GradeController::class);
    // });

    // // Report routes (Registrar only)
    // Route::middleware('role:registrar')->group(function () {
    //     // Route::prefix('reports')->name('reports.')->group(function () {
    //     //     Route::get('/enrollment-statistics', [ReportController::class, 'enrollmentStatistics'])
    //     //         ->name('enrollment-statistics');
    //     //     Route::get('/generate-tor', [ReportController::class, 'generateTOR'])
    //     //         ->name('generate-tor');
    //     //     Route::post('/generate-tor/{student}', [ReportController::class, 'downloadTOR'])
    //     //         ->name('download-tor');
    //     // });
    // });

});

require __DIR__.'/auth.php';
