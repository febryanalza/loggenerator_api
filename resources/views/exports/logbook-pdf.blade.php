<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Logbook Export - {{ $template->name }}</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000000;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18pt;
            color: #1a365d;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 12pt;
            color: #4a5568;
            font-style: italic;
            margin-bottom: 5px;
        }

        .header .institution {
            font-size: 9pt;
            color: #718096;
        }

        /* Section Headers */
        .section-header {
            font-size: 12pt;
            font-weight: bold;
            color: #2d3748;
            margin-top: 20px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Identity Table */
        .identity-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .identity-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .identity-table .label {
            width: 30%;
            background-color: #f7fafc;
            font-weight: bold;
            color: #2d3748;
        }

        .identity-table .value {
            width: 70%;
            color: #000000;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }

        .data-table th {
            background-color: #4a5568;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 5px;
            text-align: center;
            border: 1px solid #cbd5e0;
        }

        .data-table td {
            padding: 6px 5px;
            border: 1px solid #cbd5e0;
            vertical-align: top;
        }

        .data-table tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .data-table tr:hover {
            background-color: #edf2f7;
        }

        .data-table .row-num {
            text-align: center;
            width: 30px;
            font-weight: bold;
        }

        .data-table .status-verified {
            color: #276749;
            font-weight: bold;
        }

        .data-table .status-pending {
            color: #c05621;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 30px;
            color: #718096;
            font-style: italic;
            background-color: #f7fafc;
            border: 1px dashed #cbd5e0;
            margin: 20px 0;
        }

        /* Summary */
        .summary {
            font-size: 9pt;
            color: #718096;
            margin-top: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #718096;
        }

        .footer p {
            margin-bottom: 3px;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Text alignment */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-success {
            background-color: #c6f6d5;
            color: #276749;
        }

        .badge-warning {
            background-color: #feebc8;
            color: #c05621;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 7pt;
            color: #cbd5e0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LOGBOOK EXPORT</h1>
        <h2>{{ $template->name }}</h2>
        @if($template->institution)
            <p class="institution">{{ $template->institution->name }}</p>
        @endif
    </div>

    <!-- Identity Section -->
    <div class="section-header">INFORMASI LOGBOOK</div>
    <table class="identity-table">
        <tr>
            <td class="label">Nama Template</td>
            <td class="value">{{ $template->name }}</td>
        </tr>
        <tr>
            <td class="label">Deskripsi</td>
            <td class="value">{{ $template->description ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Institution</td>
            <td class="value">{{ $template->institution?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Dibuat Oleh</td>
            <td class="value">{{ $template->creator?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Dibuat</td>
            <td class="value">{{ $template->created_at?->format('d F Y, H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Terakhir Diupdate</td>
            <td class="value">{{ $template->updated_at?->format('d F Y, H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Field</td>
            <td class="value">{{ $fields->count() }} field</td>
        </tr>
        <tr>
            <td class="label">Jumlah Data</td>
            <td class="value">{{ $logbookData->count() }} entri</td>
        </tr>
    </table>

    <!-- Data Section -->
    <div class="section-header">DATA LOGBOOK</div>
    
    @if($logbookData->isEmpty())
        <div class="no-data">
            Belum ada data yang dimasukkan ke dalam logbook ini.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th class="row-num">No.</th>
                    @foreach($fields as $field)
                        <th>{{ ucfirst($field->name) }}</th>
                    @endforeach
                    <th style="width: 80px;">Penulis</th>
                    <th style="width: 60px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logbookData as $index => $entry)
                    <tr>
                        <td class="row-num">{{ $index + 1 }}</td>
                        @php
                            $entryData = $entry->data ?? [];
                        @endphp
                        @foreach($fields as $field)
                            <td>{{ formatPdfFieldValue($entryData[$field->name] ?? '-', $field->data_type) }}</td>
                        @endforeach
                        <td class="text-center">{{ $entry->writer?->name ?? '-' }}</td>
                        <td class="text-center">
                            @if($entry->is_verified)
                                <span class="badge badge-success">✓ Verified</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <p class="summary">Total: {{ $logbookData->count() }} entri data</p>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>─────────────────────────────────────────────────────────────────────────</p>
        <p>Dokumen ini diekspor dari LogGenerator System</p>
        <p>Diekspor oleh: {{ $user->name }} ({{ $user->email }})</p>
        <p>Tanggal ekspor: {{ $exportDate->format('d F Y, H:i:s T') }}</p>
        <p>Dokumen ini dibuat secara otomatis dan bersifat rahasia.</p>
    </div>

    <div class="watermark">
        LogGenerator &copy; {{ date('Y') }}
    </div>
</body>
</html>

@php
/**
 * Format field value based on data type for PDF export
 */
function formatPdfFieldValue($value, $dataType) {
    if ($value === null || $value === '-') {
        return '-';
    }

    switch ($dataType) {
        case 'date':
            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y');
            } catch (\Exception $e) {
                return (string) $value;
            }

        case 'datetime':
            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
            } catch (\Exception $e) {
                return (string) $value;
            }

        case 'time':
            try {
                return \Carbon\Carbon::parse($value)->format('H:i');
            } catch (\Exception $e) {
                return (string) $value;
            }

        case 'boolean':
            return $value ? 'Ya' : 'Tidak';

        case 'number':
        case 'integer':
            return number_format((float) $value, 0, ',', '.');

        case 'decimal':
        case 'float':
            return number_format((float) $value, 2, ',', '.');

        case 'array':
        case 'json':
            if (is_array($value)) {
                return implode(', ', $value);
            }
            return (string) $value;

        case 'image':
        case 'file':
            return '[File]';

        default:
            $stringValue = (string) $value;
            if (strlen($stringValue) > 50) {
                return substr($stringValue, 0, 47) . '...';
            }
            return $stringValue;
    }
}
@endphp
