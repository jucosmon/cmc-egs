<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Enrollment Statistics</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        .header { margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; }
        .muted { color: #555; }
        .meta { margin-top: 6px; }
        .meta div { margin: 2px 0; }
        .section { margin-top: 16px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .chart-row { display: table; width: 100%; margin-top: 8px; }
        .chart-cell { display: table-cell; vertical-align: top; width: 50%; }
        .chart-box { border: 1px solid #ddd; padding: 8px; margin-right: 8px; }
        .chart-box.last { margin-right: 0; }
        .legend { margin-top: 8px; font-size: 10px; }
        .legend-item { margin-bottom: 4px; }
        .swatch { display: inline-block; width: 10px; height: 10px; margin-right: 6px; vertical-align: middle; }
        .mini-label { font-size: 10px; color: #444; margin-top: 4px; }
    </style>
</head>
<body>
    @php
        $programTotal = $byProgram->sum('total');
        $yearLevelMax = $byYearLevel->max('total') ?: 1;
        $statusTotal = max(1, ($stats['total_enrollments'] ?? 0));
        $statusItems = [
            ['label' => 'Enrolled', 'value' => $stats['enrolled'] ?? 0, 'color' => '#2563eb'],
            ['label' => 'Completed', 'value' => $stats['completed'] ?? 0, 'color' => '#16a34a'],
            ['label' => 'Other', 'value' => $statusTotal - (($stats['enrolled'] ?? 0) + ($stats['completed'] ?? 0)), 'color' => '#f59e0b'],
        ];
        $pieColors = ['#2563eb', '#16a34a', '#f59e0b', '#8b5cf6', '#ef4444', '#0ea5e9', '#10b981'];
    @endphp
    <div class="header">
        <div class="title">Enrollment Statistics</div>
        <div class="muted">Generated on {{ $generatedDate }}</div>
        <div class="meta">
            <div><strong>Academic Term:</strong> {{ $term ? $term->academic_year . ' - ' . ucfirst($term->semester) : 'All' }}</div>
            <div><strong>Program:</strong> {{ $program ? ($program->code ?? $program->name) : 'All' }}</div>
            <div><strong>Year Level:</strong> {{ $yearLevel ?? 'All' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Total Enrollments</th>
                    <th>Enrolled</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="right">{{ $stats['total_enrollments'] ?? 0 }}</td>
                    <td class="right">{{ $stats['enrolled'] ?? 0 }}</td>
                    <td class="right">{{ $stats['completed'] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Visual Summary</div>
        <div class="chart-row">
            <div class="chart-cell">
                <div class="chart-box">
                    <div class="mini-label">Enrollment by Program (Pie)</div>
                    @php
                        $cx = 70;
                        $cy = 70;
                        $r = 55;
                        $circ = 2 * pi() * $r;
                        $offset = 0;
                    @endphp
                    <svg width="140" height="140" viewBox="0 0 140 140">
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="#f3f4f6"></circle>
                        @foreach ($byProgram as $i => $row)
                            @php
                                $value = $row->total;
                                $portion = $programTotal > 0 ? $value / $programTotal : 0;
                                $dash = $circ * $portion;
                                $color = $pieColors[$i % count($pieColors)];
                                $start = $offset;
                                $offset += $dash;
                            @endphp
                            @if ($dash > 0)
                                <circle
                                    cx="{{ $cx }}"
                                    cy="{{ $cy }}"
                                    r="{{ $r }}"
                                    fill="transparent"
                                    stroke="{{ $color }}"
                                    stroke-width="{{ $r * 2 }}"
                                    stroke-dasharray="{{ $dash }} {{ $circ - $dash }}"
                                    stroke-dashoffset="-{{ $start }}"
                                    transform="rotate(-90 {{ $cx }} {{ $cy }})"
                                ></circle>
                            @endif
                        @endforeach
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r * 0.45 }}" fill="#fff"></circle>
                    </svg>
                    <div class="legend">
                        @foreach ($byProgram as $i => $row)
                            @php $color = $pieColors[$i % count($pieColors)]; @endphp
                            <div class="legend-item">
                                <span class="swatch" style="background: {{ $color }};"></span>
                                {{ $row->name }} ({{ $row->total }})
                            </div>
                        @endforeach
                        @if ($byProgram->isEmpty())
                            <div class="legend-item">No data available.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="chart-cell">
                <div class="chart-box last">
                    <div class="mini-label">Enrollment by Year Level (Bar)</div>
                    <svg width="260" height="160" viewBox="0 0 260 160">
                        @php
                            $barX = 10;
                            $barY = 20;
                            $barH = 16;
                            $gap = 8;
                        @endphp
                        @foreach ($byYearLevel as $i => $row)
                            @php
                                $barW = 200 * ($row->total / $yearLevelMax);
                                $y = $barY + ($i * ($barH + $gap));
                            @endphp
                            <text x="0" y="{{ $y + 12 }}" font-size="10">{{ $row->year_level }}</text>
                            <rect x="20" y="{{ $y }}" width="{{ $barW }}" height="{{ $barH }}" fill="#2563eb"></rect>
                            <text x="{{ 25 + $barW }}" y="{{ $y + 12 }}" font-size="10">{{ $row->total }}</text>
                        @endforeach
                        @if ($byYearLevel->isEmpty())
                            <text x="10" y="80" font-size="10">No data available.</text>
                        @endif
                    </svg>
                </div>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-cell" style="width: 100%;">
                <div class="chart-box last">
                    <div class="mini-label">Enrollment Status (Bar)</div>
                    <svg width="520" height="90" viewBox="0 0 520 90">
                        @php
                            $sx = 10;
                            $sy = 20;
                            $sH = 16;
                            $sGap = 8;
                        @endphp
                        @foreach ($statusItems as $i => $item)
                            @php
                                $barW = 420 * ($item['value'] / $statusTotal);
                                $y = $sy + ($i * ($sH + $sGap));
                            @endphp
                            <text x="0" y="{{ $y + 12 }}" font-size="10">{{ $item['label'] }}</text>
                            <rect x="90" y="{{ $y }}" width="{{ $barW }}" height="{{ $sH }}" fill="{{ $item['color'] }}"></rect>
                            <text x="{{ 95 + $barW }}" y="{{ $y + 12 }}" font-size="10">{{ $item['value'] }}</text>
                        @endforeach
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">By Program</div>
        <table>
            <thead>
                <tr>
                    <th>Program</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($byProgram as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td class="right">{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">By Year Level</div>
        <table>
            <thead>
                <tr>
                    <th>Year Level</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($byYearLevel as $row)
                    <tr>
                        <td>{{ $row->year_level }}</td>
                        <td class="right">{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
