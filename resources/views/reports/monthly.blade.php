<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #3d2c1e;
            background: #fff;
        }

        /* Header gradient bar */
        .header {
            background: linear-gradient(135deg, #f59e0b, #f87171);
            padding: 24px 32px;
            color: #fff;
        }
        .header-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .header-subtitle {
            font-size: 13px;
            opacity: 0.9;
        }

        /* Content area */
        .content {
            padding: 24px 32px;
        }

        /* Summary cards row using display:table */
        .cards-row {
            display: table;
            width: 100%;
            margin-bottom: 24px;
            border-spacing: 10px 0;
        }
        .card {
            display: table-cell;
            width: 25%;
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px 16px;
            text-align: center;
            vertical-align: top;
        }
        .card-coral {
            border-color: #fca5a5;
            background: #fff5f5;
        }
        .card-purple {
            border-color: #d8b4fe;
            background: #faf5ff;
        }
        .card-red {
            border-color: #fca5a5;
            background: #fef2f2;
        }
        .card-red .card-value {
            color: #ef4444;
        }
        .card-label {
            font-size: 11px;
            color: #78716c;
            margin-bottom: 4px;
        }
        .card-value {
            font-size: 22px;
            font-weight: bold;
        }
        .card-coral .card-value {
            color: #ef4444;
        }
        .card-purple .card-value {
            color: #a855f7;
        }
        .card-unit {
            font-size: 11px;
            color: #78716c;
        }

        /* Student table */
        .table-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #3d2c1e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        thead th {
            background: #fef3c7;
            border-bottom: 2px solid #fde68a;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #78716c;
        }
        thead th:last-child {
            text-align: center;
        }
        tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #f5f0e8;
            font-size: 12px;
        }
        tbody td:last-child {
            text-align: center;
            font-weight: bold;
        }
        tbody tr:nth-child(even) {
            background: #fffbeb;
        }
        .unpaid-cell {
            text-align: center;
            font-weight: bold;
        }
        .unpaid-cell--red {
            color: #ef4444;
        }
        .unpaid-cell--grey {
            color: #a3a3a3;
        }
        .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }
        .dot-coral { background: #f87171; }
        .dot-purple { background: #a855f7; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #a3a3a3;
            font-size: 13px;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #f5f0e8;
            padding: 16px 32px;
            font-size: 10px;
            color: #a3a3a3;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; vertical-align: middle; width: 50px;">
                <img src="{{ public_path('images/logo-libra.png') }}" style="width: 42px; height: 42px; border-radius: 6px;" alt="Libra" />
            </div>
            <div style="display: table-cell; vertical-align: middle; padding-left: 12px;">
                <div class="header-title">{{ $monthName }} {{ $year }}</div>
                <div class="header-subtitle">Libra - Mjesečni izvještaj</div>
            </div>
        </div>
    </div>

    <div class="content">
        {{-- Report label --}}
        <div style="font-size: 16px; font-weight: bold; color: #3d2c1e; margin-bottom: 16px;">
            Libra Izvještaj {{ $monthName }}
        </div>

        {{-- Tutor summary cards --}}
        <div class="cards-row">
            <div class="card card-coral">
                <div class="card-label">Marina</div>
                <div class="card-value">{{ $marinaHours }}</div>
                <div class="card-unit">sati</div>
            </div>
            <div class="card card-purple">
                <div class="card-label">Valentina</div>
                <div class="card-value">{{ $valentinaHours }}</div>
                <div class="card-unit">sati</div>
            </div>
            <div class="card">
                <div class="card-label">Ukupno</div>
                <div class="card-value">{{ $totalHours }}</div>
                <div class="card-unit">sati</div>
            </div>
            @if ($unpaidTotal > 0)
                <div class="card card-red">
                    <div class="card-label">Neplaćeno</div>
                    <div class="card-value">{{ $unpaidTotal }}</div>
                    <div class="card-unit">sesija</div>
                </div>
            @endif
        </div>

        {{-- Student table --}}
        <div class="table-title">Pregled po učenicima</div>

        @if ($students->isEmpty())
            <div class="empty-state">Nema zakazanih sesija za ovaj mjesec.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ime</th>
                        <th>Prezime</th>
                        <th>Predmet</th>
                        <th>Tutor</th>
                        <th>Ukupno sati</th>
                        <th>Neplaćeno</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $index => $student)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student['first_name'] }}</td>
                            <td>{{ $student['last_name'] }}</td>
                            <td>{{ $student['subjects'] }}</td>
                            <td>
                                @foreach ($student['tutors'] as $tutor)
                                    <span class="dot dot-{{ $tutor['color'] }}"></span>{{ $tutor['name'] }}@if (!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>{{ $student['hours'] }}</td>
                            <td class="unpaid-cell {{ $student['unpaid'] > 0 ? 'unpaid-cell--red' : 'unpaid-cell--grey' }}">
                                {{ $student['unpaid'] > 0 ? $student['unpaid'] : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        <div class="footer-left">&copy; {{ now()->year }} Libra</div>
        <div class="footer-right">Generirano: {{ now()->format('d.m.Y. H:i') }}</div>
    </div>

</body>
</html>
