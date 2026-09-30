<?php
use App\Config\Config;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Official Attendance Report - <?= htmlspecialchars($event['event_name']) ?></title>
  <style>
    @page { size: portrait; margin: 15mm; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11pt; color: #1e293b; margin: 0; padding: 20px; }
    .header { text-align: center; border-bottom: 2px solid #6b0f0f; padding-bottom: 12px; margin-bottom: 20px; }
    .header img { width: 70px; height: 70px; vertical-align: middle; margin-bottom: 6px; }
    .header h1 { font-size: 14pt; margin: 0; font-weight: 800; color: #6b0f0f; text-transform: uppercase; }
    .header h2 { font-size: 11pt; margin: 2px 0 0 0; font-weight: 600; color: #334155; }
    .header p { font-size: 9pt; margin: 2px 0 0 0; color: #64748b; }
    
    .meta-box { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10pt; }
    .meta-box td { padding: 4px 6px; }
    .meta-box strong { color: #0f172a; }

    table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9.5pt; }
    table.data-table th { background-color: #f1f5f9; color: #0f172a; font-weight: 700; border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
    table.data-table td { border: 1px solid #cbd5e1; padding: 5px 8px; }
    table.data-table tr:nth-child(even) { background-color: #f8fafc; }

    .signatures { margin-top: 40px; width: 100%; border-collapse: collapse; }
    .signatures td { width: 50%; vertical-align: top; padding-top: 20px; }
    .sig-line { width: 220px; border-bottom: 1px solid #0f172a; margin-top: 45px; margin-bottom: 4px; }
    .sig-title { font-size: 9pt; color: #64748b; }

    @media print {
      body { padding: 0; }
      .no-print { display: none; }
    }
  </style>
</head>
<body>

  <div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print()" style="padding: 8px 16px; background: #6b0f0f; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
      Print / Save as PDF
    </button>
  </div>

  <!-- Header -->
  <div class="header">
    <img src="<?= Config::BASE_URL ?>/images/stctandag-logo.png" alt="STC Logo">
    <h1><?= Config::INSTITUTION_NAME ?></h1>
    <h2><?= Config::INSTITUTION_SUBTITLE ?></h2>
    <p>Tandag City, Surigao del Sur, Philippines</p>
  </div>

  <!-- Event Details -->
  <table class="meta-box">
    <tr>
      <td><strong>Event Name:</strong> <?= htmlspecialchars($event['event_name']) ?></td>
      <td><strong>Date:</strong> <?= date('F j, Y', strtotime($event['date'])) ?></td>
    </tr>
    <tr>
      <td><strong>Host:</strong> <?= htmlspecialchars($event['host_department_acronym'] ?? 'All College') ?></td>
      <td><strong>Semester:</strong> <?= htmlspecialchars($event['semester_name']) ?></td>
    </tr>
    <tr>
      <td><strong>Report Type:</strong> <?= strtoupper($type) ?> ROSTER</td>
      <td><strong>Generated On:</strong> <?= date('F j, Y h:i A') ?></td>
    </tr>
  </table>

  <!-- Report Table -->
  <?php if ($type === 'absentees'): ?>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 30px;">#</th>
          <th>Student ID</th>
          <th>Full Name</th>
          <th>Course</th>
          <th>Department</th>
          <th style="width: 80px; text-align: center;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($absentees)): ?>
          <tr><td colspan="6" style="text-align: center;">No absentees. 100% attendance!</td></tr>
        <?php else: ?>
          <?php $i = 1; foreach ($absentees as $s): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($s['ID_Number']) ?></td>
              <td><strong><?= htmlspecialchars($s['Student_name']) ?></strong></td>
              <td><?= htmlspecialchars($s['Course']) ?></td>
              <td><?= htmlspecialchars($s['department_name'] ?? $s['departments_id']) ?></td>
              <td style="text-align: center; color: #dc2626; font-weight: bold;">ABSENT</td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  <?php else: ?>
    <!-- Attendees -->
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 30px;">#</th>
          <th>Student ID</th>
          <th>Full Name</th>
          <th>Course</th>
          <th>Time-In</th>
          <th>Time-Out</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($attendees)): ?>
          <tr><td colspan="6" style="text-align: center;">No attendance records found.</td></tr>
        <?php else: ?>
          <?php $i = 1; foreach ($attendees as $a): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($a['Student_ID']) ?></td>
              <td><strong><?= htmlspecialchars($a['Student_name']) ?></strong></td>
              <td><?= htmlspecialchars($a['Course']) ?></td>
              <td style="color: #166534; font-family: monospace; font-weight: bold;"><?= !empty($a['Time_in']) ? date('H:i:s', strtotime($a['Time_in'])) : '—' ?></td>
              <td style="font-family: monospace;"><?= !empty($a['Time_out']) ? date('H:i:s', strtotime($a['Time_out'])) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- Summary Statistics -->
  <p style="margin-top: 20px; font-size: 10pt;">
    <strong>Total Records:</strong> <?= $type === 'absentees' ? count($absentees) : count($attendees) ?>
  </p>

  <!-- Signatures -->
  <table class="signatures">
    <tr>
      <td>
        <div class="sig-title">Prepared by:</div>
        <div class="sig-line"></div>
        <strong>Attendance Marshall / Officer</strong>
      </td>
      <td>
        <div class="sig-title">Noted by:</div>
        <div class="sig-line"></div>
        <strong>Dean / OSAS Director</strong>
      </td>
    </tr>
  </table>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      // Auto open print dialog
      setTimeout(() => window.print(), 600);
    });
  </script>
</body>
</html>
