<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Block;
use App\Mail\AccountCreatedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts by type.
     */
    public function index(Request $request): Response
    {
        $type = $request->query('type', 'student');
        $user = Auth::user();

        // Validate user access
        $this->authorizeAccountAccess($user, $type);

        // Get accounts based on user role and type
        $accounts = $this->getAccountsByType($user, $type);

        // Get available user types for current user role
        $visibleUserTypes = $this->getVisibleUserTypes($user);

        // Check if user can create accounts
        $canCreate = $this->canCreateAccounts($user, $type);

        return Inertia::render('Accounts/Index', [
            'accounts' => $accounts,
            'userType' => $type,
            'visibleUserTypes' => $visibleUserTypes,
            'canCreate' => $canCreate,
            'currentUserRole' => $user->role,
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create(Request $request): Response
    {
        $type = $request->query('type', 'student');
        $user = Auth::user();

        // Validate user access
        $this->authorizeAccountAccess($user, $type);

        // Only IT Admin and Registrar can create certain account types
        if (!$this->canCreateAccounts($user, $type)) {
            abort(403);
        }

        $departments = [];
        $programs = [];
        $blocks = [];

        // Get departments and programs for selection
        if ($user->role === 'it_admin') {
            $departments = Department::all()->pluck('name', 'id');
            $programs = Program::all()->pluck('name', 'id');
        } elseif ($user->role === 'dean') {
            $department = $user->departmentAsDean;
            if ($department) {
                $departments = collect([$department->id => $department->name]);
                $programs = $department->programs()->pluck('name', 'id');
            }
        } elseif ($user->role === 'registrar') {
            $programs = Program::query()->orderBy('name')->pluck('name', 'id');
        }

        if ($type === 'student') {
            $programIds = $programs ? $programs->keys()->all() : [];
            $blocks = Block::query()
                ->when(count($programIds), function ($query) use ($programIds) {
                    $query->whereIn('program_id', $programIds);
                })
                ->where('status', 'active')
                ->orderByDesc('admission_year')
                ->orderBy('code')
                ->get(['id', 'code', 'admission_year', 'program_id', 'status']);
        }

        return Inertia::render('Accounts/Create', [
            'userType' => $type,
            'departments' => $departments,
            'programs' => $programs,
            'blocks' => $blocks,
            'student_statuses' => ['regular', 'irregular', 'graduated'],
            'year_levels' => [1, 2, 3, 4, 5],
        ]);
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $type = $request->input('type', 'student');
        $user = Auth::user();

        // Validate user access and creation permissions
        $this->authorizeAccountAccess($user, $type);
        if (!$this->canCreateAccounts($user, $type)) {
            abort(403);
        }

        $isStudent = $type === 'student';

        $validated = $request->validate([
            'email' => 'nullable|email|unique:users',
            'personal_email' => 'required|email|unique:users,personal_email',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'official_id' => 'nullable|unique:users',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:Male,Female',
            'department_id' => 'nullable|exists:departments,id',
            'program_id' => $isStudent ? 'required|exists:programs,id' : 'nullable',
            'year_level' => $isStudent ? 'required|integer|min:1|max:6' : 'nullable',
            'status' => $isStudent ? 'required|in:regular,irregular,graduated' : 'nullable',
            'block_id' => $isStudent ? 'required|exists:blocks,id' : 'nullable',
        ]);

        // Generate email if not provided
        if (empty($validated['email'])) {
            $baseEmail = strtolower(str_replace(' ', '.', trim($validated['first_name'] . '.' . $validated['last_name'])));
            $validated['email'] = $baseEmail . '@cmc.edu.ph';

            // Ensure unique email
            $counter = 1;
            $originalEmail = $validated['email'];
            while (User::where('email', $validated['email'])->exists()) {
                $validated['email'] = substr($originalEmail, 0, -strlen('@cmc.edu.ph')) . $counter . '@cmc.edu.ph';
                $counter++;
            }
        }

        // Generate official_id if not provided
        if (empty($validated['official_id'])) {
            $rolePrefix = match($type) {
                'dean' => 'DEAN',
                'program_head' => 'PH',
                'registrar' => 'REG',
                'instructor' => 'INST',
                'student' => 'STU',
                default => 'USR'
            };

            $lastUser = User::where('role', $type)->latest('id')->first();
            $counter = ($lastUser ? (int)substr($lastUser->official_id, -4) : 0) + 1;
            $validated['official_id'] = $rolePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);

            // Ensure unique official_id
            while (User::where('official_id', $validated['official_id'])->exists()) {
                $counter++;
                $validated['official_id'] = $rolePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
            }
        }

        // Validate block before creating any records to avoid partial saves
        $selectedBlock = null;
        if ($type === 'student' && isset($validated['program_id'])) {
            $latestAdmissionYear = Block::where('program_id', $validated['program_id'])
                ->where('status', 'active')
                ->max('admission_year');
            if ($validated['block_id']) {
                $selectedBlock = Block::find($validated['block_id']);
                if (!$selectedBlock || (int) $selectedBlock->program_id !== (int) $validated['program_id']) {
                    return back()->withErrors([
                        'block_id' => 'Selected block does not belong to the selected program.'
                    ]);
                }
                if ($latestAdmissionYear && (int) $selectedBlock->admission_year !== (int) $latestAdmissionYear) {
                    return back()->withErrors([
                        'block_id' => 'Please select a block from the latest admission year.'
                    ]);
                }
            }
        }

        // Generate default password
        $defaultPassword = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $newUser = DB::transaction(function () use ($validated, $type, $defaultPassword, $selectedBlock) {
            $user = User::create([
                'email' => $validated['email'],
                'personal_email' => $validated['personal_email'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? '',
                'last_name' => $validated['last_name'],
                'official_id' => $validated['official_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'sex' => $validated['sex'] ?? null,
                'role' => $type,
                'password' => Hash::make($defaultPassword),
                'is_active' => true,
            ]);

            if ($type === 'student' && isset($validated['program_id'])) {
                Student::create([
                    'user_id' => $user->id,
                    'program_id' => $validated['program_id'],
                    'year_level' => $validated['year_level'] ?? null,
                    'status' => $validated['status'] ?? null,
                    'block_id' => $selectedBlock?->id,
                ]);
            }

            if ($type === 'instructor' && isset($validated['department_id'])) {
                Instructor::create([
                    'user_id' => $user->id,
                    'department_id' => $validated['department_id'],
                ]);
            }

            return $user;
        });

        // Send email with default password
        $emailSent = false;
        try {
            $mailtrapToken = config('services.mailtrap.token');
            if ($mailtrapToken) {
                $fromAddress = config('mail.from.address');
                $fromName = config('mail.from.name') ?: 'CMC EGS';
                $html = view('emails.account-created', [
                    'user' => $newUser,
                    'temporaryPassword' => $defaultPassword,
                ])->render();

                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::withToken($mailtrapToken)
                    ->post(config('services.mailtrap.endpoint'), [
                        'from' => [
                            'email' => $fromAddress,
                            'name' => $fromName,
                        ],
                        'to' => [
                            ['email' => $validated['personal_email']],
                        ],
                        'subject' => 'Welcome to Carmen Municipal College',
                        'text' => 'Your account has been created. Please log in using your official email and the temporary password provided.',
                        'html' => $html,
                    ]);

                $emailSent = $response->successful();

                if (!$emailSent) {
                    Log::warning('Mailtrap API send failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'user_id' => $newUser->id,
                    ]);
                }
            } else {
                Mail::to($validated['personal_email'])->send(
                    new AccountCreatedMail($newUser, $defaultPassword)
                );
                $emailSent = true;
            }
        } catch (\Exception $e) {
            Log::warning('Account email failed to send', [
                'user_id' => $newUser->id,
                'error' => $e->getMessage(),
            ]);
        }

        $redirect = redirect()->route('accounts.index', ['type' => $type])
            ->with('success', 'You have successfully created an account!');

        if ($emailSent) {
            $redirect->with(
                'info',
                'Welcome email sent to ' . $validated['personal_email'] . '.'
            );
        } else {
            $redirect->with(
                'warning',
                'Account created, but the welcome email could not be sent. Please verify SMTP settings.'
            );
        }

        return $redirect;
    }

    /**
     * Display the specified account.
     */
    public function show(User $account): Response
    {
        $currentUser = Auth::user();
        $type = request('type', $account->role);

        // Ensure related nested relations are loaded for display
        $account->load([
            'student.program.department',
            'student.block',
            'instructor.department',
            'programAsHead.department',
            'departmentAsProgramHead',
            'departmentAsDean.programs',
        ]);

        // Validate user access
        $this->authorizeAccountAccess($currentUser, $type);
        $this->authorizeAccountView($currentUser, $account, $type);

        // Get related data based on role
        $student = $account->role === 'student' ? $account->student : null;
        $instructor = $account->role === 'instructor' ? $account->instructor : null;
        $program = $account->role === 'program_head' ? $account->programAsHead : null;
        $department = $account->role === 'dean' ? $account->departmentAsDean : null;

        // Check if current user can update this account
        $canUpdate = $this->canUpdateAccount($currentUser, $account, $type);

        return Inertia::render('Accounts/Show', [
            'account' => $account,
            'userType' => $type,
            'student' => $student,
            'instructor' => $instructor,
            'program' => $program,
            'department' => $department,
            'canUpdate' => $canUpdate,
            'canResetPassword' => $canUpdate,
        ]);
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit(User $account): Response
    {
        $currentUser = Auth::user();
        $type = request('type', $account->role);

        // Ensure related data is loaded for the edit form, including student's program and departments
        $account->load([
            'student.program.department',
            'instructor.department',
            'programAsHead.department',
            'departmentAsDean',
        ]);

        // Validate user access and edit permissions
        $this->authorizeAccountAccess($currentUser, $type);
        if (!$this->canUpdateAccount($currentUser, $account, $type)) {
            abort(403);
        }

        $departments = [];
        $programs = [];
        $blocks = [];

        if ($currentUser->role === 'it_admin') {
            $departments = Department::all()->pluck('name', 'id');
            $programs = Program::all()->pluck('name', 'id');
        } elseif ($currentUser->role === 'dean') {
            $department = $currentUser->departmentAsDean;
            if ($department) {
                $departments = collect([$department->id => $department->name]);
                $programs = $department->programs()->pluck('name', 'id');
            }
        } elseif ($currentUser->role === 'registrar') {
            $programs = Program::query()->orderBy('name')->pluck('name', 'id');
        }

        // Ensure program_id options are available when editing a program head
        if ($type === 'program_head' && $currentUser->role === 'it_admin') {
            if (!count($programs)) {
                $programs = Program::all()->pluck('name', 'id');
            }
        }

        // Provide blocks and statuses when editing students (useful for registrars)
        if ($type === 'student') {
            $programIds = $programs ? $programs->keys()->all() : [];
            $assignedBlockId = $account->student?->block_id;
            $blocks = Block::query()
                ->when(count($programIds), function ($query) use ($programIds) {
                    $query->whereIn('program_id', $programIds);
                })
                ->where(function ($query) use ($assignedBlockId) {
                    $query->where('status', 'active');
                    if ($assignedBlockId) {
                        $query->orWhere('id', $assignedBlockId);
                    }
                })
                ->orderByDesc('admission_year')
                ->orderBy('code')
                ->get(['id', 'code', 'admission_year', 'program_id', 'status']);
        }

        // Expose the account's program/dept explicitly to the Inertia view to avoid
        // serialization surprises and make frontend prefill logic reliable.
        $program = $account->programAsHead ?? null;
        $department = $program?->department ?? $account->departmentAsDean ?? null;

        return Inertia::render('Accounts/Edit', [
            'account' => $account,
            'userType' => $type,
            'departments' => $departments,
            'programs' => $programs,
            'program' => $program,
            'department' => $department,
            'blocks' => $blocks,
            'student_statuses' => ['regular', 'irregular'],
            'year_levels' => [1, 2, 3, 4, 5],
        ]);
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, User $account): \Illuminate\Http\RedirectResponse
    {
        $currentUser = Auth::user();
        $type = $request->input('type', $account->role);

        // Validate user access and update permissions
        $this->authorizeAccountAccess($currentUser, $type);
        if (!$this->canUpdateAccount($currentUser, $account, $type)) {
            abort(403);
        }

        $isStudent = $type === 'student';

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $account->id,
            'personal_email' => 'nullable|email|unique:users,personal_email,' . $account->id,
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'official_id' => 'nullable|unique:users,official_id,' . $account->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:Male,Female',
            'department_id' => 'nullable|exists:departments,id',
            'program_id' => $isStudent ? 'required|exists:programs,id' : 'nullable',
            'year_level' => $isStudent ? 'required|integer|min:1|max:6' : 'nullable',
            'status' => $isStudent ? 'required|in:regular,irregular,graduated' : 'nullable',
            'block_id' => $isStudent ? 'required|exists:blocks,id' : 'nullable',
        ]);

        // Update base user fields
        $account->update(array_filter($validated, function ($k) {
            return in_array($k, ['email', 'personal_email', 'first_name', 'middle_name', 'last_name', 'official_id', 'phone', 'address', 'date_of_birth', 'sex']);
        }, ARRAY_FILTER_USE_KEY));

        // Update related records depending on role
        if ($account->role === 'student') {
            $student = $account->student;
            if (isset($validated['block_id']) && $validated['block_id']) {
                $block = Block::find($validated['block_id']);
                if (!$block || (int) $block->program_id !== (int) $validated['program_id']) {
                    return back()->withErrors([
                        'block_id' => 'Selected block does not belong to the selected program.'
                    ]);
                }
            }
            if ($student) {
                $student->update([
                    'program_id' => $validated['program_id'] ?? $student->program_id,
                    'year_level' => $validated['year_level'] ?? $student->year_level,
                    'status' => $validated['status'] ?? $student->status,
                    'block_id' => $validated['block_id'] ?? $student->block_id,
                ]);
            } elseif (isset($validated['program_id'])) {
                \App\Models\Student::create([
                    'user_id' => $account->id,
                    'program_id' => $validated['program_id'],
                ]);
            }
        } elseif ($account->role === 'instructor') {
            $instructor = $account->instructor;
            if ($instructor) {
                $instructor->update([
                    'department_id' => $validated['department_id'] ?? $instructor->department_id,
                ]);
            } elseif (isset($validated['department_id'])) {
                \App\Models\Instructor::create([
                    'user_id' => $account->id,
                    'department_id' => $validated['department_id'],
                ]);
            }
        } elseif ($account->role === 'program_head') {
            // program head's department is stored on their Program (programAsHead)
            $program = $account->programAsHead;
            // If program_id provided, assign this user as program head for that program
            if (isset($validated['program_id']) && $validated['program_id']) {
                $newProgram = \App\Models\Program::find($validated['program_id']);
                if ($newProgram) {
                    // if they had a different program previously, clear it
                    if ($program && $program->id !== $newProgram->id) {
                        $program->update(['program_head_id' => null]);
                    }
                    $newProgram->update([
                        'program_head_id' => $account->id,
                        'department_id' => $validated['department_id'] ?? $newProgram->department_id,
                    ]);
                }
            } elseif ($program) {
                // otherwise, just update department on their existing program if provided
                $program->update([
                    'department_id' => $validated['department_id'] ?? $program->department_id,
                ]);
            }
        }

        return redirect()->route('accounts.show', ['account' => $account, 'type' => $type])
            ->with('success', 'You have successfully updated an account!');
    }

    /**
     * Reset password for the specified user.
     */
    public function resetPassword(Request $request, User $account): \Illuminate\Http\RedirectResponse
    {
        $currentUser = Auth::user();
        $type = $request->input('type', $account->role);

        // Validate user access and reset permissions
        $this->authorizeAccountAccess($currentUser, $type);
        if (!$this->canResetPassword($currentUser, $account, $type)) {
            abort(403);
        }

        $validated = $request->validate([
            'password' => 'required|string|confirmed',
        ]);

        $account->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('accounts.show', ['account' => $account, 'type' => $type])
            ->with('success', 'You have successfully reset the password!');
    }

    /**
     * Delete the specified account.
     */
    public function destroy(User $account): \Illuminate\Http\RedirectResponse
    {
        $currentUser = Auth::user();
        $type = $account->role;

        // Only IT Admin can delete accounts
        if ($currentUser->role !== 'it_admin') {
            abort(403);
        }

        $account->delete();

        return redirect()->route('accounts.index', ['type' => $type])
            ->with('success', 'Account deleted successfully!');
    }

    /**
     * Get accounts based on user role and requested type.
     */
    private function getAccountsByType($user, $type)
    {
        $query = User::where('role', $type);

        // Apply role-based filters
        if ($user->role === 'dean') {
            // Dean can see users in their department
            $department = $user->departmentAsDean;
            if ($department) {
                if ($type === 'program_head') {
                    $query = $query->whereHas('departmentAsProgramHead', function ($q) use ($department) {
                        $q->where('department_id', $department->id);
                    });
                } elseif ($type === 'instructor') {
                    $query = $query->whereHas('instructor', function ($q) use ($department) {
                        $q->where('department_id', $department->id);
                    });
                } elseif ($type === 'student') {
                    $query = $query->whereHas('student', function ($q) use ($department) {
                        $q->whereHas('program', function ($q2) use ($department) {
                            $q2->where('department_id', $department->id);
                        });
                    });
                }
            }
        } elseif ($user->role === 'program_head') {
            // Program Head can see students in their program
            if ($type === 'student') {
                $program = $user->programAsHead;
                if ($program) {
                    $query = $query->whereHas('student', function ($q) use ($program) {
                        $q->where('program_id', $program->id);
                    });
                }
            }
        }

        // Eager load nested relations so department names are available
        return $query->with([
            'student.program.department',
            'instructor.department',
            'programAsHead.department',
            'departmentAsDean',
        ])->paginate(15)->through(function ($account) {
            // determine department name based on role and available relations
            $departmentName = null;
            // Dean's department
            if ($account->role === 'dean' && $account->departmentAsDean) {
                $departmentName = $account->departmentAsDean->name;
            }
            if ($account->role === 'instructor' && $account->instructor && $account->instructor->department) {
                $departmentName = $account->instructor->department->name;
            } elseif ($account->role === 'student' && $account->student && $account->student->program && $account->student->program->department) {
                $departmentName = $account->student->program->department->name;
            } elseif ($account->role === 'program_head' && $account->programAsHead && $account->programAsHead->department) {
                $departmentName = $account->programAsHead->department->name;
            }

            return [
                'id' => $account->id,
                'first_name' => $account->first_name,
                'middle_name' => $account->middle_name,
                'last_name' => $account->last_name,
                'full_name' => $account->full_name,
                'email' => $account->email,
                'official_id' => $account->official_id,
                'role' => $account->role,
                'is_active' => $account->is_active,
                'student' => $account->student,
                'instructor' => $account->instructor,
                'department_name' => $departmentName,
            ];
        });
    }

    /**
     * Get visible user types for current user's role.
     */
    private function getVisibleUserTypes($user)
    {
        $types = [];

        if ($user->role === 'it_admin') {
            $types = [
                ['name' => 'Dean', 'value' => 'dean'],
                ['name' => 'Program Head', 'value' => 'program_head'],
                ['name' => 'Registrar', 'value' => 'registrar'],
                ['name' => 'Instructor', 'value' => 'instructor'],
                ['name' => 'Student', 'value' => 'student'],
            ];
        } elseif ($user->role === 'dean') {
            $types = [
                ['name' => 'Program Head', 'value' => 'program_head'],
                ['name' => 'Instructor', 'value' => 'instructor'],
                ['name' => 'Student', 'value' => 'student'],
            ];
        } elseif ($user->role === 'program_head') {
            $types = [
                ['name' => 'Student', 'value' => 'student'],
            ];
        } elseif ($user->role === 'registrar') {
            $types = [
                ['name' => 'Student', 'value' => 'student'],
            ];
        }

        return $types;
    }

    /**
     * Check if user can create accounts of this type.
     */
    private function canCreateAccounts($user, $type): bool
    {
        if ($user->role === 'it_admin') {
            return in_array($type, ['dean', 'program_head', 'registrar', 'instructor']);
        }

        if ($user->role === 'registrar') {
            return $type === 'student';
        }

        return false;
    }

    /**
     * Check if user can update accounts of this type.
     */
    private function canUpdateAccount($user, $targetUser, $type): bool
    {
        if ($user->role === 'it_admin') {
            return in_array($type, ['dean', 'program_head', 'registrar', 'instructor']);
        }

        if ($user->role === 'registrar' && $type === 'student') {
            return true;
        }

        return false;
    }

    /**
     * Check if user can reset password for this account.
     */
    private function canResetPassword($user, $targetUser, $type): bool
    {
        if ($user->role === 'it_admin') {
            return in_array($type, ['dean', 'program_head', 'registrar', 'instructor']);
        }

        return false;
    }

    /**
     * Authorize user to access accounts of this type.
     */
    private function authorizeAccountAccess($user, $type): void
    {
        $visibleTypes = $this->getVisibleUserTypes($user);
        $allowedTypes = array_column($visibleTypes, 'value');

        if (!in_array($type, $allowedTypes)) {
            abort(403);
        }
    }

    /**
     * Authorize user to view a specific account.
     */
    private function authorizeAccountView($user, $targetUser, $type): void
    {
        if ($user->id === $targetUser->id) {
            return;
        }

        if ($user->role === 'dean') {
            $department = $user->departmentAsDean;
            if (!$department) {
                abort(403);
            }

            if ($type === 'student' && $targetUser->student) {
                if ($targetUser->student->program->department_id !== $department->id) {
                    abort(403);
                }
            } elseif ($type === 'instructor' && $targetUser->instructor) {
                if ($targetUser->instructor->department_id !== $department->id) {
                    abort(403);
                }
            }
        } elseif ($user->role === 'program_head') {
            if ($type === 'student' && $targetUser->student) {
                // Assuming user has programAsHead relationship
                $program = $user->programAsHead ?? null;
                if (!$program || $targetUser->student->program_id !== $program->id) {
                    abort(403);
                }
            }
        }
    }
}
