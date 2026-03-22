<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Official Transcript of Records</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111;
            padding: 20px 28px;
            position: relative;
        }

        /* ─── WATERMARK ─────────────────────────────── */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.07;
            z-index: 0;
        }
        .watermark img { width: 520px; height: 520px; object-fit: contain; }

        .content { position: relative; z-index: 1; }

        /* ─── HEADER ────────────────────────────────── */
        .header-wrap { position: relative; margin-bottom: 0; }
        .header-img { width: 100%; display: block; opacity: 0.5; }

        .office-label {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.3px;
            margin: 4px 0 1px;
        }
        .tor-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 5px;
        }
        .divider-thick { border-top: 2.5px solid #111; margin: 3px 0 7px; }

        /* ─── STUDENT INFO ───────────────────────────── */
        .info-outer { width: 100%; border-collapse: collapse; }
        .info-outer td { vertical-align: top; }

        .info-tbl { width: 100%; border-collapse: collapse; }
        .info-tbl td { padding: 1.8px 0; font-size: 11px; vertical-align: top; }
        .info-tbl .lbl { width: 108px; font-weight: normal; }
        .info-tbl .val { font-weight: bold; padding-left: 8px; }

        .photo-cell { width: 92px; padding-left: 6px; }
        .photo-wrap {
            width: 88px; height: 110px;
            border: 1px solid #555;
            overflow: hidden;
            text-align: center;
            line-height: 110px;
            font-size: 8px;
            color: #888;
        }
        .photo-wrap img { width: 88px; height: 110px; object-fit: cover; display: block; }

        /* ─── PRELIMINARY EDUCATION ──────────────────── */
        .section-underline {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 10px 0 5px;
        }
        .prelim-tbl { width: 100%; border-collapse: collapse; font-size: 11px; }
        .prelim-tbl td { padding: 1.8px 3px; }
        .prelim-tbl .ph { font-weight: bold; }
        .prelim-tbl .ps { padding-left: 10px; }
        .prelim-tbl .py { text-align: right; width: 110px; }
        .course-row { font-size: 11px; margin-top: 5px; }
        .course-row span { font-weight: bold; font-style: italic; }

        /* ─── COLLEGIATE RECORD ──────────────────────── */
        .coll-tbl {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #444;
            margin-top: 10px;
        }

        /* "COLLEGIATE RECORD" title row — plain, just bordered */
        .coll-tbl .title-row td {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            padding: 4px 4px;
            text-transform: uppercase;
            border-bottom: 1px solid #444;
        }

        /* column labels */
        .coll-tbl .lbl-row td {
            border-top: 1px solid #444;
            border-bottom: 1px solid #444;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 10.5px;
            vertical-align: bottom;
        }
        .coll-tbl .lbl-row td.r { text-align: right; }

        /* semester sub-header */
        .coll-tbl .sem-row td {
            padding: 3px 6px;
            font-size: 10.5px;
            border-top: 1px solid #aaa;
            border-bottom: 1px solid #aaa;
        }

        /* subject data rows */
        .coll-tbl .subj-row td {
            padding: 2.5px 6px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        .coll-tbl .subj-row td.r { text-align: right; }

        /* withdrawal/special message rows */
        .coll-tbl .msg-row td {
            padding: 5px 6px;
            font-size: 10.5px;
            border-bottom: 1px solid #ddd;
        }

        /* end / continue banner */
        .coll-tbl .end-row td {
            text-align: center;
            font-size: 9.5px;
            padding: 5px 4px;
            letter-spacing: 1.5px;
            border-top: 1px solid #444;
        }

        /* ─── GRADING SYSTEM ─────────────────────────── */
        .grading-wrap { margin-top: 10px; }
        .grading-title { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
        .grading-line { font-size: 10px; line-height: 1.65; font-family: DejaVu Sans Mono, Courier New, monospace; }

        .unit-note { font-size: 11px; margin-top: 7px; line-height: 1.55; }

        /* ─── FOOTER ─────────────────────────────────── */
        .remarks-tbl { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 11px; }
        .remarks-tbl td { padding: 0; vertical-align: bottom; }
        .remarks-tbl .date-td { text-align: right; white-space: nowrap; }

        .prepared-line { font-size: 11px; margin-top: 6px; }
        .prepared-name { font-weight: bold; text-transform: uppercase; }

        .registrar-block { text-align: center; margin-top: 18px; }
        .registrar-name  { font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .registrar-sub   { font-size: 11px; }

        .not-valid { font-size: 11px; font-weight: bold; margin-top: 14px; line-height: 1.5; }
        .page-num  { text-align: right; font-size: 11px; margin-top: 4px; }
    </style>
</head>
<body>

@php
    /* ─── Helper: encode image to base64 data URI ─── */
    function torImg(string $path, string $mime = 'image/jpeg'): ?string {
        if (!file_exists($path)) return null;
        $raw = file_get_contents($path);
        return $raw !== false ? 'data:' . $mime . ';base64,' . base64_encode($raw) : null;
    }

    /* ─── Images ─── */
    // Put your header strip as: public/images/tor-header.jpg
    // This is the single image with all 4 logos + school name text
    $headerImg    = torImg(public_path('images/tor-header.jpg'));
    $watermarkImg = torImg(public_path('images/logo.jpg')); // CMC logo, shown faint in center

    /* ─── Student data ─── */
    $user    = $student->user;
    $program = $student->program;

    $fullName   = strtoupper($user->last_name) . ', ' . strtoupper($user->first_name)
                . ($user->middle_name ? ' ' . strtoupper($user->middle_name) : '');
    $address    = strtoupper($user->address    ?? 'N/A');
    $birthDate  = $user->date_of_birth ? strtoupper($user->date_of_birth->format('F d, Y')) : 'N/A';
    $birthPlace = strtoupper($user->birth_place ?? 'N/A');
    $sex        = ucfirst(strtolower($user->sex ?? 'N/A'));
    $religion   = $user->religion    ?? 'N/A';
    $citizenship= $user->citizenship ?? 'Filipino';

    // Parents: stored as newline-separated or array
    $parentsRaw = $user->parents ?? '';
    $parentLines = is_array($parentsRaw)
        ? $parentsRaw
        : array_filter(array_map('trim', explode("\n", $parentsRaw)));
    $parent1 = strtoupper($parentLines[0] ?? 'N/A');
    $parent2 = isset($parentLines[1]) ? strtoupper($parentLines[1]) : null;

    /* ─── Preliminary education ─── */
    $elemSchool = strtoupper($student->elementary_school ?? 'N/A');
    $elemYear   = $student->elementary_year ?? '';
    $secSchool  = strtoupper($student->secondary_school  ?? 'N/A');
    $secYear    = $student->secondary_year  ?? '';

    /* ─── Photo ─── */
    $photoData = null;
    if (!empty($user->avatar)) {
        $photoData = torImg(public_path('storage/' . $user->avatar))
                  ?? torImg(storage_path('app/public/' . $user->avatar));
    }

    /* ─── Page control (set from controller) ─── */
    $isLastPage = $isLastPage ?? true;
@endphp

{{-- WATERMARK --}}
<div class="watermark">
    @if($watermarkImg)<img src="{{ $watermarkImg }}" alt=""/>@endif
</div>

<div class="content">

{{-- ════════════════════════════════════════ --}}
{{-- HEADER                                   --}}
{{-- ════════════════════════════════════════ --}}
@if($headerImg)
    <div class="header-wrap">
        <img class="header-img" src="{{ $headerImg }}" alt="CMC Header" />
    </div>
@else
    {{-- Fallback plain text header --}}
    <div style="text-align:center;margin-bottom:4px;">
        <div style="font-size:9px;">Republic of the Philippines</div>
        <div style="font-size:13px;font-weight:bold;">CARMEN MUNICIPAL COLLEGE</div>
        <div style="font-size:9px;">Poblacion Norte, Carmen, Bohol &nbsp;|&nbsp; Email: <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="c5acaba3aa85a6a8a6a7aaadaaa9eba0a1b0ebb5ad">[email&#160;protected]</a> &nbsp;|&nbsp; Phone: 09399080233</div>
    </div>
@endif

<div class="office-label">OFFICE OF THE REGISTRAR</div>
<div class="tor-title">Official Transcript of Records</div>
<div class="divider-thick"></div>

{{-- ════════════════════════════════════════ --}}
{{-- STUDENT INFO + 2x2 PHOTO                 --}}
{{-- ════════════════════════════════════════ --}}
<table class="info-outer">
    <tr>
        <td>
            <table class="info-tbl">
                <tr><td class="lbl">Name</td>        <td class="val">{{ $fullName }}</td></tr>
                <tr><td class="lbl">Address</td>      <td class="val">{{ $address }}</td></tr>
                <tr><td class="lbl">Birth Date</td>   <td class="val">{{ $birthDate }}</td></tr>
                <tr><td class="lbl">Birth Place</td>  <td class="val">{{ $birthPlace }}</td></tr>
                <tr><td class="lbl">Sex</td>          <td class="val">{{ $sex }}</td></tr>
                <tr><td class="lbl">Religion</td>     <td class="val">{{ $religion }}</td></tr>
                <tr><td class="lbl">Citizenship</td>  <td class="val">{{ $citizenship }}</td></tr>
                <tr>
                    <td class="lbl">Parents</td>
                    <td class="val">
                        {{ $parent1 }}
                        @if($parent2)<br/>{{ $parent2 }}@endif
                    </td>
                </tr>
            </table>
        </td>
        <td class="photo-cell">
            <div class="photo-wrap">
                @if($photoData)
                    <img src="{{ $photoData }}" alt="Photo" />
                @else
                    2x2<br/>Photo
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ════════════════════════════════════════ --}}
{{-- PRELIMINARY EDUCATION                    --}}
{{-- ════════════════════════════════════════ --}}
<div class="section-underline">Record of Preliminary Education</div>

<table class="prelim-tbl">
    <tr>
        <td class="ph" style="width:82px;">Completed:</td>
        <td class="ph ps">Name of School</td>
        <td class="ph py">School Year</td>
    </tr>
    <tr>
        <td>Elementary</td>
        <td class="ps">{{ $elemSchool }}</td>
        <td class="py">{{ $elemYear }}</td>
    </tr>
    <tr>
        <td>Secondary</td>
        <td class="ps">{{ $secSchool }}</td>
        <td class="py">{{ $secYear }}</td>
    </tr>
</table>

<div class="course-row">
    Course: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <span>{{ $program->name }}</span>
</div>

{{-- ════════════════════════════════════════ --}}
{{-- COLLEGIATE RECORD TABLE                  --}}
{{-- ════════════════════════════════════════ --}}
<table class="coll-tbl">
    <tr class="title-row">
        <td colspan="4">Collegiate Record</td>
    </tr>
    <tr class="lbl-row">
        <td style="width:22%;">SCHOOL TERM &amp;<br/>COURSE NO.</td>
        <td>DESCRIPTIVE TITLE</td>
        <td class="r" style="width:14%;">FINAL RATING</td>
        <td class="r" style="width:10%;">UNITS</td>
    </tr>

    @forelse ($termGroups as $term => $enrollmentsInTerm)

        <tr class="sem-row">
            <td colspan="4">{{ $term }} CARMEN MUNICIPAL COLLEGE POBLACION NORTE, CARMEN, BOHOL</td>
        </tr>

        @foreach ($enrollmentsInTerm as $enrollment)

            @if(in_array($enrollment->status ?? '', ['withdrawn', 'dropped', 'withdrawal']))
                <tr class="msg-row">
                    <td colspan="4">
                        WITHDRAWAL OF ENROLLMENT<br/>
                        <em>*Official Transcript of Records Closed*</em>
                    </td>
                </tr>

            @else
                @foreach ($enrollment->enrolledSubjects as $es)
                    @php
                        $subj  = $es->scheduledSubject->curriculumSubject->subject ?? null;
                        $code  = $subj->code  ?? '';
                        $title = $subj->title ?? '';
                        $units = $subj->units ?? '';
                        $grade = $es->final_grade ?? 'N/A';
                    @endphp
                    <tr class="subj-row">
                        <td>{{ $code }}</td>
                        <td>{{ $title }}</td>
                        <td class="r">{{ $grade }}</td>
                        <td class="r">{{ $units }}</td>
                    </tr>
                @endforeach
            @endif

        @endforeach

    @empty
        <tr class="subj-row">
            <td colspan="4" style="text-align:center;padding:10px;">No academic records found.</td>
        </tr>
    @endforelse

    <tr class="end-row">
        <td colspan="4">
            @if($isLastPage)
                ********************************** END OF TRANSCRIPT ***********************************
            @else
                ******************************* CONTINUE TO NEXT PAGE *******************************
            @endif
        </td>
    </tr>
</table>

{{-- ════════════════════════════════════════ --}}
{{-- GRADING SYSTEM                           --}}
{{-- ════════════════════════════════════════ --}}
<div class="grading-wrap">
    <div class="grading-title">Grading System:</div>
    <div class="grading-line">   1.0 – 99%-100%  1.4 – 91%-92%  1.8 – 87%    2.2 – 83%    2.6 – 79%    3.0 – 75%              INC - Incomplete</div>
    <div class="grading-line">   1.1 – 97%-98%    1.5 – 90%           1.9 – 86%    2.3 – 82%    2.7 – 78%    5.0 – Below 75%  W- Withdrawn</div>
    <div class="grading-line">   1.2 – 95%-96%    1.6 – 89%           2.0 – 85%    2.4 – 81%    2.8 – 77%    DR – Dropped       NG – No Grade</div>
    <div class="grading-line">   1.3 – 93%-94%    1.7 – 88%           2.1 – 84%    2.5 – 80%    2.9 – 76%    NA–Never Attended</div>
</div>

<div class="unit-note">
    One collegiate unit of credit is one hour lecture or recitation each week for one complete semester.<br/>
    Three hours of laboratory work each week are regarded as equivalent to one semester unit or credit.
</div>

{{-- ════════════════════════════════════════ --}}
{{-- REMARKS + PREPARED BY                    --}}
{{-- ════════════════════════════════════════ --}}
<table class="remarks-tbl">
    <tr>
        <td>Remarks:&nbsp;<strong>{{ $remarks ?? 'For Evaluation Purposes' }}</strong></td>
        <td class="date-td">Date: {{ $generatedDate }}</td>
    </tr>
</table>

<div class="prepared-line">
    Prepared by:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <span class="prepared-name">{{ $preparedBy ?? 'RENA MAE T. CABAHUG' }}</span>
</div>

{{-- ════════════════════════════════════════ --}}
{{-- REGISTRAR                                --}}
{{-- ════════════════════════════════════════ --}}
<div class="registrar-block">
    <div class="registrar-name">{{ $registrarName ?? 'GLENA P. BERONDO, LPT' }}</div>
    <div class="registrar-sub">Registrar</div>
</div>

<div class="not-valid">NOT VALID<br/>WITHOUT SEAL</div>

<div class="page-num">Page {{ $pageNumber ?? 1 }}</div>
