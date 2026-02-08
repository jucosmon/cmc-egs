<?php

use App\Http\Controllers\{
    DepartmentController, ProgramController, SubjectController,
    BlockController, InstructorController, StudentController,
    CurriculumController, AcademicTermController,
    ScheduledSubjectController, EnrollmentController,
    GradeController, ReportController, ClassController, DashboardController,
    ProfileController, AccountController, ProgramHeadController
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

Route::middleware('auth')->group(function () {
    Route::get('/unauthorized', function () {
        return response('Unauthorized', 403);
    })->name('unauthorized');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // IT Admin routes
    Route::middleware('role:it_admin')->group(function () {
        Route::resource('departments', DepartmentController::class);
    });

    // Admin, Dean, Program Head - Programs
    Route::middleware('role:it_admin,dean,program_head')->group(function () {
        Route::resource('programs', ProgramController::class);
        Route::resource('blocks', BlockController::class);
        // API endpoint for getting available subjects for a block
        Route::get('api/blocks/{block}/available-subjects', [BlockController::class, 'getAvailableSubjects'])
            ->name('api.blocks.available-subjects');
        Route::resource('curriculums', CurriculumController::class)
            ->whereNumber('curriculum');
        Route::put('curriculums/{curriculum}/activate', [CurriculumController::class, 'activate'])
            ->name('curriculums.activate');
        Route::resource('instructors', InstructorController::class);
        Route::resource('students', StudentController::class);
    });

    // Registrar routes
    Route::middleware('role:registrar')->group(function () {
        Route::resource('terms', AcademicTermController::class);
        Route::post('terms/{term}/activate', [AcademicTermController::class, 'activate'])
            ->name('terms.activate');

        Route::resource('subjects', SubjectController::class);

        //enrollments
        Route::get('enrollments/manage', [EnrollmentController::class, 'registrarManage'])
        ->name('enrollments.registrar-manage');
        Route::post('enrollments/{student}/create', [EnrollmentController::class, 'registrarCreateEnrollment'])
            ->name('enrollments.registrar-create');
        Route::post('enrollments/{enrollment}/enroll-subject', [EnrollmentController::class, 'enrollSubject'])
            ->name('enrollments.enroll-subject');
        Route::delete('enrolled-subjects/{enrolledSubject}/drop', [EnrollmentController::class, 'dropSubject'])
            ->name('enrollments.drop-subject');
        Route::get('enrollments/search-subject', [EnrollmentController::class, 'searchSubject'])
            ->name('enrollments.search-subject');

        //grades
        Route::get('grades/registrar', [GradeController::class, 'index'])
            ->name('grades.registrar');
        Route::put('grades/{enrolledSubject}/update-single', [GradeController::class, 'updateSingleGrade'])
            ->name('grades.update-single');

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

    // Enrollment routes (Program Head specific)
    Route::middleware('role:program_head')->group(function () {
        Route::get('/enrollments', [BlockController::class, 'index'])->name('enrollments.index');

        Route::get('program-head/instructor-loads', [ProgramHeadController::class, 'instructorLoads'])
            ->name('program-head.instructor-loads');
    });

    // General enrollment routes
    Route::middleware('role:student,registrar,program_head')->group(function () {
        Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show'])
            ->whereNumber('enrollment')
            ->name('enrollments.show');
        Route::get('enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create');
        Route::post('enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
        Route::get('enrollments/subjects/available', [EnrollmentController::class, 'getAvailableSubjects'])
            ->name('enrollments.available-subjects');
    });

    // Classes (Instructor, Program Head)
    Route::middleware('role:instructor,program_head')->group(function () {
        Route::resource('classes', ClassController::class)->only(['index', 'show']);
        Route::get('classes/{class}/attendance', [ClassController::class, 'attendance'])
            ->name('classes.attendance');

        // Scheduled subjects (for program heads creating schedules)
        Route::resource('scheduled-subjects', ScheduledSubjectController::class)
            ->except(['index']);
        Route::get('scheduled-subjects/curriculum-subjects',
            [ScheduledSubjectController::class, 'getCurriculumSubjects'])
            ->name('scheduled-subjects.curriculum-subjects');

        //grade routes
        Route::get('grades/instructor', [GradeController::class, 'index'])
            ->name('grades.instructor');
        Route::get('grades/{scheduledSubject}/submit', [GradeController::class, 'edit'])
            ->name('grades.submit');
        Route::post('grades/{scheduledSubject}/save', [GradeController::class, 'update'])
            ->name('grades.save');
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

    // Instructor enrollment view
    Route::middleware('role:instructor')->group(function () {
        Route::get('enrollments/instructor-classes', [EnrollmentController::class, 'instructorClasses'])
            ->name('enrollments.instructor-classes');
    });

    // Accounts (IT Admin, Dean, Program Head, Registrar)
    Route::middleware('role:it_admin,dean,program_head,registrar')->group(function () {
        Route::resource('accounts', AccountController::class);
        Route::post('accounts/{account}/reset-password', [AccountController::class, 'resetPassword'])
            ->name('accounts.reset-password');
    });

    // (Student)
    Route::middleware('role:student')->group(function () {
        //enrollments
        Route::get('my-enrollment', [EnrollmentController::class, 'studentView'])
            ->name('enrollments.student-view');
        Route::get('enrollments/{enrollment}/download-schedule', [EnrollmentController::class, 'downloadSchedule'])
            ->whereNumber('enrollment')
            ->name('enrollments.download-schedule');

        // curriculum checklist
        Route::get('curriculums/checklist', [CurriculumController::class, 'studentChecklist'])
            ->name('curriculums.student-checklist');

        //grades
        Route::get('my-grades', [GradeController::class, 'index'])
            ->name('grades.student');
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
