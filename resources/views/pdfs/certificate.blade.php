<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Georgia, 'Times New Roman', serif;
            background: #fff;
            color: #1a1a2e;
            width: 297mm;
            height: 210mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page {
            width: 100%;
            height: 100%;
            padding: 12mm;
            position: relative;
        }

        /* Outer decorative border */
        .border-outer {
            position: absolute;
            top: 8mm; left: 8mm; right: 8mm; bottom: 8mm;
            border: 3px solid #c9a84c;
        }

        /* Inner decorative border */
        .border-inner {
            position: absolute;
            top: 11mm; left: 11mm; right: 11mm; bottom: 11mm;
            border: 1px solid #c9a84c;
        }

        .content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 10mm 20mm;
        }

        .logo-area {
            margin-bottom: 6mm;
        }

        .logo-text {
            font-size: 22pt;
            font-weight: bold;
            color: #c9a84c;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .cert-title {
            font-size: 28pt;
            font-weight: bold;
            color: #1a1a2e;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4mm;
        }

        .cert-subtitle {
            font-size: 11pt;
            color: #666;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 8mm;
        }

        .divider {
            width: 80mm;
            height: 1px;
            background: linear-gradient(to right, transparent, #c9a84c, transparent);
            margin: 4mm auto;
        }

        .presented-to {
            font-size: 11pt;
            color: #555;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 3mm;
        }

        .student-name {
            font-size: 30pt;
            color: #1a1a2e;
            font-style: italic;
            margin-bottom: 6mm;
            font-family: 'Palatino Linotype', Palatino, serif;
        }

        .completion-text {
            font-size: 11pt;
            color: #444;
            line-height: 1.6;
            margin-bottom: 4mm;
        }

        .course-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 8mm;
            font-style: italic;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            width: 100%;
            margin-top: 8mm;
        }

        .footer-item {
            text-align: center;
            flex: 1;
        }

        .footer-label {
            font-size: 8pt;
            color: #888;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2mm;
        }

        .footer-value {
            font-size: 10pt;
            color: #333;
            font-weight: bold;
        }

        .signature-line {
            width: 40mm;
            height: 1px;
            background: #333;
            margin: 0 auto 2mm;
        }

        .cert-number {
            font-size: 8pt;
            color: #aaa;
            letter-spacing: 1px;
            margin-top: 6mm;
        }

        /* Corner ornaments */
        .corner {
            position: absolute;
            width: 12mm;
            height: 12mm;
            border-color: #c9a84c;
            border-style: solid;
        }
        .corner-tl { top: 14mm; left: 14mm; border-width: 2px 0 0 2px; }
        .corner-tr { top: 14mm; right: 14mm; border-width: 2px 2px 0 0; }
        .corner-bl { bottom: 14mm; left: 14mm; border-width: 0 0 2px 2px; }
        .corner-br { bottom: 14mm; right: 14mm; border-width: 0 2px 2px 0; }
    </style>
</head>
<body>
<div class="page">
    <div class="border-outer"></div>
    <div class="border-inner"></div>
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <div class="content">
        <div class="logo-area">
            <div class="logo-text">{{ config('app.name', 'CoursePalette') }}</div>
        </div>

        <div class="cert-title">Certificate of Completion</div>
        <div class="cert-subtitle">This is to certify that</div>

        <div class="divider"></div>

        <div class="student-name">{{ $certificate->student->name }}</div>

        <div class="completion-text">
            has successfully completed all requirements of the course
        </div>

        <div class="course-name">{{ $certificate->course->title }}</div>

        <div class="divider"></div>

        <div class="footer">
            <div class="footer-item">
                <div class="signature-line"></div>
                <div class="footer-value">{{ $certificate->course->teacher->name }}</div>
                <div class="footer-label">Instructor</div>
            </div>

            <div class="footer-item">
                <div class="footer-value">
                    {{ $certificate->issue_date?->format('F j, Y') ?? now()->format('F j, Y') }}
                </div>
                <div class="footer-label">Date of Completion</div>
            </div>

            <div class="footer-item">
                <div class="footer-value">{{ config('app.name', 'CoursePalette') }}</div>
                <div class="footer-label">Issued By</div>
            </div>
        </div>

        <div class="cert-number">Certificate No: {{ $certificate->certificate_number }}</div>
    </div>
</div>
</body>
</html>
