<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Transcript of Records</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: top; text-align: center; }
        .logo { width: 70px; height: 70px; border: 1px solid #777; text-align: center; font-size: 9px; color: #555; margin: 0 auto 4px; line-height: 70px; }
        .logo-img { width: 70px; height: 70px; object-fit: contain; display: block; margin: 0 auto 4px; }
        .school-block { text-align: center; }
        .school-name { font-size: 14px; font-weight: bold; letter-spacing: 0.5px; }
        .school-sub { font-size: 10px; color: #333; margin-top: 2px; }
        .doc-title { font-size: 16px; font-weight: bold; margin: 6px 0 2px; text-transform: uppercase; }
        .doc-sub { font-size: 10px; color: #555; }
        .divider { border-top: 1px solid #222; margin: 8px 0 10px; }
        .info-grid { margin-top: 6px; }
        .info-row { margin-bottom: 6px; }
        .info-item { display: inline-block; width: 48%; vertical-align: top; }
        .info-label { display: inline-block; width: 140px; font-weight: bold; }
        .info-value { display: inline-block; min-width: 220px; border-bottom: 1px solid #222; padding-bottom: 1px; }
        .section { margin-top: 14px; }
        .section-title { font-size: 12px; font-weight: bold; margin-bottom: 6px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { padding: 4px 6px; text-align: left; }
        thead th { border-bottom: 1px solid #222; font-size: 10.5px; }
        tbody td { border-bottom: 1px solid #ddd; }
        .right { text-align: right; }
        .summary { margin-top: 10px; }
        .summary-box { width: 100%; border-collapse: collapse; }
        .summary-box td { padding: 4px 6px; }
        .summary-label { font-weight: bold; }
        .note { font-size: 9px; color: #444; margin-top: 8px; }
        .signatures { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .signatures td { vertical-align: bottom; padding: 6px; }
        .sig-line { border-bottom: 1px solid #222; height: 18px; }
        .sig-label { font-size: 9px; text-align: center; margin-top: 3px; }
        .seal-box { width: 90px; height: 90px; border: 1px solid #222; text-align: center; font-size: 9px; color: #555; }
        .seal-label { font-size: 9px; text-align: center; margin-top: 3px; }
        .grading-table { font-size: 9.5px; }
        .grading-table thead th { font-size: 9.5px; }
    </style>
</head>
<body>
    @php
        $leftLogo = public_path('images/logo.jpg');
        $logoData = null;
        if (file_exists($leftLogo)) {
            $logoContents = file_get_contents($leftLogo);
            if ($logoContents !== false) {
                $logoData = 'data:image/jpeg;base64,' . base64_encode($logoContents);
            }
        }
        $firstEnrollment = $termGroups->flatten(1)->sortBy('enrolled_at')->first();
        $admissionDate = $firstEnrollment && $firstEnrollment->enrolled_at
            ? $firstEnrollment->enrolled_at->format('F d, Y')
            : 'N/A';
        $birthDate = $student->user->date_of_birth
            ? $student->user->date_of_birth->format('F d, Y')
            : 'N/A';
    @endphp

    <table class="header-table">
        <tr>
            <td class="school-block">
                @if ($logoData)
                    <img class="logo-img" src="{{ $logoData }}" alt="School Logo" />
                @else
                    <div class="logo">SCHOOL LOGO</div>
                @endif
                <div class="school-sub">Republic of the Philippines</div>
                <div class="school-name">Carmen Municipal College</div>
                <div class="school-sub">Poblacion Norte, Carmen, Philippines, 6319</div>
                <div class="school-sub">0917 777 0814 | 038-500-0181</div>
                <div class="doc-title">Transcript of Records</div>
                <div class="doc-sub">Generated on {{ $generatedDate }}</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-item">
                <span class="info-label">Student Name</span>
                <span class="info-value">{{ $student->user->last_name }}, {{ $student->user->first_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Student ID</span>
                <span class="info-value">{{ $student->user->official_id }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-item">
                <span class="info-label">Program</span>
                <span class="info-value">{{ $student->program->code }} - {{ $student->program->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Department</span>
                <span class="info-value">{{ $student->program->department->name }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-item">
                <span class="info-label">Date of Admission</span>
                <span class="info-value">{{ $admissionDate }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date of Birth</span>
                <span class="info-value">{{ $birthDate }}</span>
            </div>
        </div>
    </div>

    @forelse ($termGroups as $term => $enrollmentsInTerm)
        <div class="section">
            <div class="section-title">{{ $term }}</div>
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Title</th>
                        <th class="right">Units</th>
                        <th class="right">Final Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($enrollmentsInTerm as $enrollment)
                        @foreach ($enrollment->enrolledSubjects as $enrolledSubject)
                            <tr>
                                <td>{{ $enrolledSubject->scheduledSubject->curriculumSubject->subject->code }}</td>
                                <td>{{ $enrolledSubject->scheduledSubject->curriculumSubject->subject->title }}</td>
                                <td class="right">{{ $enrolledSubject->scheduledSubject->curriculumSubject->subject->units }}</td>
                                <td class="right">{{ $enrolledSubject->final_grade ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            <div class="summary">
                <table class="summary-box">
                    <tr>
                        <td class="summary-label">Term Units</td>
                        <td class="right">{{ $termSummaries[$term]['units'] ?? 0 }}</td>
                        <td class="summary-label">Term GPA</td>
                        <td class="right">{{ $termSummaries[$term]['gpa'] ?? 0 }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @empty
        <div>No academic records found.</div>
    @endforelse

    <div class="summary">
        <table class="summary-box">
            <tr>
                <td class="summary-label">Total Units Earned</td>
                <td class="right">{{ $totalUnits }}</td>
                <td class="summary-label">Overall GPA</td>
                <td class="right">{{ $overallGPA }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Grading System</div>
        <table class="grading-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Grade</th>
                    <th style="width: 40%;">Equivalent</th>
                    <th style="width: 40%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1.00 - 1.25</td>
                    <td>Excellent</td>
                    <td>Passed</td>
                </tr>
                <tr>
                    <td>1.50 - 1.75</td>
                    <td>Very Good</td>
                    <td>Passed</td>
                </tr>
                <tr>
                    <td>2.00 - 2.25</td>
                    <td>Good</td>
                    <td>Passed</td>
                </tr>
                <tr>
                    <td>2.50 - 2.75</td>
                    <td>Satisfactory</td>
                    <td>Passed</td>
                </tr>
                <tr>
                    <td>3.00</td>
                    <td>Passing</td>
                    <td>Passed</td>
                </tr>
                <tr>
                    <td>5.00</td>
                    <td>Failure</td>
                    <td>Failed</td>
                </tr>
                <tr>
                    <td>INC / DRP</td>
                    <td>Incomplete / Dropped</td>
                    <td>Not Credited</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td style="width: 40%;">
                <div class="sig-line"></div>
                <div class="sig-label">Prepared by</div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 40%;">
                <div class="sig-line"></div>
                <div class="sig-label">Checked by</div>
            </td>
            <td style="width: 10%; text-align: right;">
                <div class="seal-box">DRY SEAL</div>
                <div class="seal-label">School Seal</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="padding-top: 10px;">
                <div class="sig-line"></div>
                <div class="sig-label">Registrar</div>
            </td>
            <td></td>
        </tr>
    </table>

    <div class="note">This document is valid only with the school seal and registrar signature.</div>
</body>
</html>
