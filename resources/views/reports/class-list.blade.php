<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Class List</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        .header { margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; }
        .muted { color: #555; }
        .meta { margin-top: 6px; }
        .meta div { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Class List</div>
        <div class="muted">Generated on {{ $generatedDate }}</div>
        <div class="meta">
            <div><strong>Subject:</strong> {{ $scheduledSubject->curriculumSubject->subject->code }} - {{ $scheduledSubject->curriculumSubject->subject->title }}</div>
            <div><strong>Academic Term:</strong> {{ $scheduledSubject->academicTerm->academic_year }} - {{ ucfirst($scheduledSubject->academicTerm->semester) }}</div>
            <div><strong>Instructor:</strong> {{ $scheduledSubject->instructor->user->first_name ?? '' }} {{ $scheduledSubject->instructor->user->last_name ?? '' }}</div>
            <div><strong>Schedule:</strong> {{ $scheduledSubject->day }} {{ $scheduledSubject->time_start }} - {{ $scheduledSubject->time_end }}</div>
            <div><strong>Room:</strong> {{ $scheduledSubject->room }}</div>
            <div><strong>Block:</strong> {{ $scheduledSubject->block->code ?? 'N/A' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Program</th>
                <th>Year Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($scheduledSubject->enrolledSubjects as $enrolledSubject)
                <tr>
                    <td>{{ $enrolledSubject->enrollment->student->user->official_id }}</td>
                    <td>
                        {{ $enrolledSubject->enrollment->student->user->last_name }},
                        {{ $enrolledSubject->enrollment->student->user->first_name }}
                    </td>
                    <td>{{ $enrolledSubject->enrollment->student->program->code ?? '' }}</td>
                    <td>{{ $enrolledSubject->enrollment->year_level }}</td>
                    <td>{{ ucfirst($enrolledSubject->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No enrolled students.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
