<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Enrollment Schedule</title>
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
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Enrollment Schedule</div>
        <div class="muted">
            {{ $enrollment->academicTerm->academic_year }}
            - {{ ucfirst($enrollment->academicTerm->semester) }} Semester
        </div>
        <div class="meta">
            <div><strong>Student:</strong> {{ $enrollment->student->user->first_name }} {{ $enrollment->student->user->last_name }}</div>
            <div><strong>Student ID:</strong> {{ $enrollment->student->user->official_id }}</div>
            <div><strong>Program:</strong> {{ $enrollment->student->program->code }}</div>
            <div><strong>Year Level:</strong> {{ $enrollment->year_level }}</div>
            <div><strong>Block:</strong> {{ $enrollment->block->code ?? 'N/A' }}</div>
            <div><strong>Total Units:</strong> {{ $totalUnits }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject Title</th>
                <th>Instructor</th>
                <th>Schedule</th>
                <th>Room</th>
                <th class="right">Units</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($enrollment->enrolledSubjects as $es)
                <tr>
                    <td>{{ $es->scheduledSubject->curriculumSubject->subject->code }}</td>
                    <td>{{ $es->scheduledSubject->curriculumSubject->subject->title }}</td>
                    <td>
                        {{ $es->scheduledSubject->instructor->user->first_name ?? '' }}
                        {{ $es->scheduledSubject->instructor->user->last_name ?? '' }}
                    </td>
                    <td>{{ $es->scheduledSubject->day }} {{ $es->scheduledSubject->time_start }} - {{ $es->scheduledSubject->time_end }}</td>
                    <td>{{ $es->scheduledSubject->room }}</td>
                    <td class="right">{{ $es->scheduledSubject->curriculumSubject->subject->units }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No subjects enrolled.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
