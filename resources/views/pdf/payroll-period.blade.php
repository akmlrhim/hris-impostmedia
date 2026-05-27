<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Payroll {{ $period->code }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 12px;
      color: #0f172a;
      background: #fff;
      padding: 30px 36px;
    }

    /* ── HEADER ── */
    .header {
      display: table;
      width: 100%;
      border-bottom: 3px solid #1e293b;
      padding-bottom: 14px;
      margin-bottom: 20px;
    }
    .header-left {
      display: table-cell;
      vertical-align: middle;
      width: 60px;
    }
    .header-left img {
      width: 52px;
      height: 52px;
    }
    .header-center {
      display: table-cell;
      vertical-align: middle;
      padding-left: 12px;
    }
    .company-name {
      font-size: 18px;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.3px;
    }
    .company-sub {
      font-size: 11px;
      color: #64748b;
      margin-top: 2px;
    }
    .header-right {
      display: table-cell;
      vertical-align: middle;
      text-align: right;
    }
    .doc-title {
      font-size: 16px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #1e293b;
    }
    .doc-meta {
      font-size: 11px;
      color: #475569;
      margin-top: 4px;
      line-height: 1.7;
    }
    .doc-meta strong {
      color: #0f172a;
    }

    /* ── TABLE ── */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }

    thead tr {
      background: #1e293b;
    }
    thead th {
      padding: 10px 12px;
      font-size: 10.5px;
      font-weight: 700;
      color: #f1f5f9;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      border: 1px solid #334155;
    }
    th.right, td.right { text-align: right; }
    th.center, td.center { text-align: center; }

    tbody tr:nth-child(odd)  { background: #ffffff; }
    tbody tr:nth-child(even) { background: #f8fafc; }

    tbody td {
      padding: 9px 12px;
      border: 1px solid #e2e8f0;
      font-size: 11.5px;
      color: #1e293b;
      vertical-align: middle;
    }
    .td-no {
      color: #94a3b8;
      font-size: 11px;
      text-align: center;
    }
    .td-name {
      font-weight: 700;
      font-size: 12px;
      color: #0f172a;
    }
    .td-emp {
      font-size: 11px;
      color: #64748b;
      text-align: center;
    }
    .td-bank {
      font-size: 11.5px;
      font-weight: 600;
      color: #334155;
    }
    .td-rekening {
      font-size: 11.5px;
      color: #334155;
    }
    .td-net {
      font-size: 12.5px;
      font-weight: 700;
      color: #065f46;
      text-align: right;
    }

    tfoot tr {
      background: #0f172a;
    }
    tfoot td {
      padding: 10px 12px;
      border: 1px solid #1e293b;
      font-size: 12px;
      font-weight: 700;
      color: #f1f5f9;
    }
    .tfoot-total-label {
      text-align: right;
      color: #94a3b8;
      font-size: 11px;
      font-weight: 400;
    }
    .tfoot-total-label strong {
      color: #f1f5f9;
      font-size: 12px;
    }
    .tfoot-amount {
      text-align: right;
      color: #34d399;
      font-size: 13px;
    }

    /* ── FOOTER ── */
    .footer {
      display: table;
      width: 100%;
      margin-top: 28px;
    }
    .footer-left {
      display: table-cell;
      vertical-align: bottom;
    }
    .footer-note {
      font-size: 9.5px;
      color: #94a3b8;
      line-height: 1.6;
    }
    .footer-right {
      display: table-cell;
      vertical-align: bottom;
      text-align: center;
      width: 200px;
    }
    .ttd-label {
      font-size: 10.5px;
      color: #475569;
    }
    .ttd-space { height: 52px; }
    .ttd-line {
      font-size: 11px;
      font-weight: 700;
      color: #0f172a;
      border-top: 1.5px solid #64748b;
      padding-top: 5px;
    }
  </style>
</head>
<body>

  {{-- HEADER --}}
  <div class="header">
    @if ($logo)
      <div class="header-left">
        <img src="{{ $logo }}" alt="Logo">
      </div>
    @endif
    <div class="header-center">
      <div class="company-name">{{ config('app.name') }}</div>
      <div class="company-sub">Laporan Penggajian Karyawan</div>
    </div>
    <div class="header-right">
      <div class="doc-title">Laporan Payroll</div>
      <div class="doc-meta">
        Periode: <strong>{{ $period->code }}</strong><br>
        {{ $period->start_date->translatedFormat('d F Y') }} &ndash; {{ $period->end_date->translatedFormat('d F Y') }}<br>
        @if ($period->payment_date)
          Tgl. Bayar: <strong>{{ $period->payment_date->translatedFormat('d F Y') }}</strong><br>
        @endif
        Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
      </div>
    </div>
  </div>

  {{-- TABLE --}}
  <table>
    <thead>
      <tr>
        <th style="width:32px;" class="center">No</th>
        <th>Nama Karyawan</th>
        <th style="width:110px;" class="center">No. Karyawan</th>
        <th style="width:130px;">Nama Bank</th>
        <th style="width:160px;">No. Rekening</th>
        <th style="width:140px;" class="right">Gaji Diterima</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($payrolls as $i => $payroll)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td class="td-name">{{ $payroll->employee->full_name }}</td>
          <td class="td-emp">{{ $payroll->employee->employee_number }}</td>
          <td class="td-bank">{{ strtoupper($payroll->employee->bank_name ?? '-') }}</td>
          <td class="td-rekening">{{ $payroll->employee->bank_account_number ?? '-' }}</td>
          <td class="td-net">Rp {{ number_format((float) $payroll->net_salary, 0, ',', '.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align:center; padding:24px; color:#94a3b8; font-size:12px;">
            Tidak ada data payroll untuk periode ini.
          </td>
        </tr>
      @endforelse
    </tbody>
    @if ($payrolls->isNotEmpty())
      <tfoot>
        <tr>
          <td colspan="5" class="tfoot-total-label">
            <strong>Total Gaji Diterima</strong> &mdash; {{ $payrolls->count() }} karyawan
          </td>
          <td class="tfoot-amount">
            Rp {{ number_format((float) $payrolls->sum('net_salary'), 0, ',', '.') }}
          </td>
        </tr>
      </tfoot>
    @endif
  </table>

  {{-- FOOTER --}}
  <div class="footer">
    <div class="footer-left">
      <div class="footer-note">
        Dokumen ini dicetak secara otomatis oleh sistem {{ config('app.name') }}.<br>
        Status periode: <strong>{{ strtoupper($period->status) }}</strong> &bull;
        Dokumen bersifat rahasia dan hanya untuk keperluan internal perusahaan.
      </div>
    </div>
    <div class="footer-right">
      <div class="ttd-label">Disetujui oleh,</div>
      <div class="ttd-space"></div>
      <div class="ttd-line">( HRD / Pimpinan )</div>
    </div>
  </div>

</body>
</html>
