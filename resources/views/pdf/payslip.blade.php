<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Slip Gaji {{ $payroll->period->code }} - {{ $payroll->employee->full_name }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 12px;
      color: #0f172a;
      background: #fff;
      padding: 32px 36px;
    }

    /* ── HEADER ── */
    .header {
      display: table;
      width: 100%;
      border-bottom: 3px solid #1e293b;
      padding-bottom: 14px;
      margin-bottom: 20px;
    }

    .header-logo {
      display: table-cell;
      vertical-align: middle;
      width: 58px;
    }

    .header-logo img {
      width: 50px;
      height: 50px;
    }

    .header-company {
      display: table-cell;
      vertical-align: middle;
      padding-left: 10px;
    }

    .company-name {
      font-size: 17px;
      font-weight: 700;
      color: #0f172a;
    }

    .company-sub {
      font-size: 10.5px;
      color: #64748b;
      margin-top: 2px;
    }

    .header-title {
      display: table-cell;
      vertical-align: middle;
      text-align: right;
    }

    .slip-title {
      font-size: 15px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #1e293b;
    }

    .slip-period {
      font-size: 11px;
      color: #475569;
      margin-top: 4px;
      line-height: 1.6;
    }

    .slip-period strong {
      color: #0f172a;
    }

    /* ── INFO BOX ── */
    .info-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 14px 16px;
      margin-bottom: 18px;
    }

    .info-table {
      display: table;
      width: 100%;
    }

    .info-row {
      display: table-row;
    }

    .info-label {
      display: table-cell;
      font-size: 11px;
      color: #64748b;
      padding: 3.5px 0;
      width: 140px;
    }

    .info-sep {
      display: table-cell;
      color: #cbd5e1;
      padding: 3.5px 10px;
      font-size: 11px;
    }

    .info-value {
      display: table-cell;
      font-size: 12px;
      font-weight: 700;
      color: #0f172a;
      padding: 3.5px 0;
    }

    /* ── SECTION ── */
    .section-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #64748b;
      margin-bottom: 8px;
    }

    /* ── ITEMS TABLE ── */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 16px;
    }

    .items-table thead th {
      background: #f1f5f9;
      padding: 7px 10px;
      font-size: 10px;
      font-weight: 700;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: 1px solid #e2e8f0;
    }

    .items-table thead th.right {
      text-align: right;
    }

    .items-table tbody td {
      padding: 8px 10px;
      font-size: 12px;
      border: 1px solid #e2e8f0;
      color: #1e293b;
    }

    .items-table tbody td.amount {
      text-align: right;
      font-weight: 600;
    }

    .items-table tbody td.notes {
      font-size: 10px;
      color: #94a3b8;
    }

    .items-table tfoot td {
      padding: 9px 10px;
      font-size: 12.5px;
      font-weight: 700;
      border-top: 2px solid #cbd5e1;
      border-left: 1px solid #e2e8f0;
      border-right: 1px solid #e2e8f0;
      border-bottom: 1px solid #e2e8f0;
    }

    .earn-total {
      color: #059669;
    }

    .ded-total {
      color: #dc2626;
    }

    .earn-amount {
      color: #059669;
      font-weight: 600;
    }

    .ded-amount {
      color: #dc2626;
      font-weight: 600;
    }

    /* ── NET BOX ── */
    .net-box {
      background: #0f172a;
      border-radius: 8px;
      padding: 16px 18px;
      display: table;
      width: 100%;
      margin-top: 4px;
    }

    .net-label {
      display: table-cell;
      vertical-align: middle;
      font-size: 13px;
      font-weight: 700;
      color: #f1f5f9;
    }

    .net-amount {
      display: table-cell;
      vertical-align: middle;
      text-align: right;
      font-size: 22px;
      font-weight: 700;
      color: #34d399;
    }

    /* ── BANK BOX ── */
    .bank-box {
      display: table;
      width: 100%;
      margin-top: 14px;
      border: 1.5px solid #e2e8f0;
      border-radius: 6px;
      padding: 12px 14px;
    }

    .bank-col {
      display: table-cell;
      vertical-align: middle;
    }

    .bank-col+.bank-col {
      border-left: 1px solid #e2e8f0;
      padding-left: 16px;
    }

    .bank-label {
      font-size: 10px;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 700;
    }

    .bank-value {
      font-size: 13px;
      font-weight: 700;
      color: #0f172a;
      margin-top: 3px;
    }

    .bank-sub {
      font-size: 10.5px;
      color: #475569;
      margin-top: 2px;
    }

    /* ── FOOTER ── */
    .footer {
      display: table;
      width: 100%;
      margin-top: 28px;
      padding-top: 12px;
      border-top: 1px solid #e2e8f0;
    }

    .footer-note {
      display: table-cell;
      vertical-align: bottom;
      font-size: 9px;
      color: #94a3b8;
      line-height: 1.6;
    }

    .footer-ttd {
      display: table-cell;
      vertical-align: bottom;
      text-align: center;
      width: 180px;
    }

    .ttd-label {
      font-size: 10px;
      color: #475569;
    }

    .ttd-space {
      height: 50px;
    }

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
      <div class="header-logo">
        <img src="{{ $logo }}" alt="Logo">
      </div>
    @endif
    <div class="header-company">
      <div class="company-name">{{ config('app.name') }}</div>
      <div class="company-sub">Slip Gaji Karyawan</div>
    </div>
    <div class="header-title">
      <div class="slip-title">Slip Gaji</div>
      <div class="slip-period">
        Periode: <strong>{{ $payroll->period->code }}</strong><br>
        {{ \Carbon\Carbon::create()->month($payroll->period->month)->translatedFormat('F') }}
        {{ $payroll->period->year }}<br>
        {{ $payroll->period->start_date->translatedFormat('d M Y') }} &ndash;
        {{ $payroll->period->end_date->translatedFormat('d M Y') }}<br>
        @if ($payroll->period->payment_date)
          Tgl. Bayar: <strong>{{ $payroll->period->payment_date->translatedFormat('d M Y') }}</strong>
        @endif
      </div>
    </div>
  </div>

  {{-- INFO KARYAWAN --}}
  <div class="info-box">
    <div class="info-table">
      <div class="info-row">
        <span class="info-label">Nama Karyawan</span>
        <span class="info-sep">:</span>
        <span class="info-value">{{ $payroll->employee->full_name }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Nomor Karyawan</span>
        <span class="info-sep">:</span>
        <span class="info-value">{{ $payroll->employee->employee_number }}</span>
      </div>
      @if ($payroll->employee->position)
        <div class="info-row">
          <span class="info-label">Jabatan</span>
          <span class="info-sep">:</span>
          <span class="info-value">{{ $payroll->employee->position }}</span>
        </div>
      @endif
      <div class="info-row">
        <span class="info-label">Hadir / Hari Kerja</span>
        <span class="info-sep">:</span>
        <span class="info-value">{{ $payroll->present_days }} hari / {{ $payroll->working_days }} hari</span>
      </div>
    </div>
  </div>

  {{-- PENDAPATAN --}}
  <div class="section-title">Pendapatan</div>
  <table class="items-table">
    <thead>
      <tr>
        <th>Komponen</th>
        <th class="right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($earnings as $item)
        <tr>
          <td>{{ $item->component_name }}</td>
          <td class="amount earn-amount">Rp {{ number_format((float) $item->amount, 0, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td class="earn-total">Total Pendapatan</td>
        <td class="earn-total" style="text-align:right;">Rp
          {{ number_format((float) $payroll->total_earnings, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

  {{-- POTONGAN --}}
  @if ($deductions->isNotEmpty())
    <div class="section-title">Potongan</div>
    <table class="items-table">
      <thead>
        <tr>
          <th>Komponen</th>
          <th class="right">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($deductions as $item)
          <tr>
            <td>
              {{ $item->component_name }}
              @if ($item->notes)
                <br><span class="notes">{{ $item->notes }}</span>
              @endif
            </td>
            <td class="amount ded-amount">Rp {{ number_format((float) $item->amount, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td class="ded-total">Total Potongan</td>
          <td class="ded-total" style="text-align:right;">
            Rp
            {{ number_format((float) ($payroll->total_deductions + $payroll->total_tax_pph21 + $payroll->total_bpjs), 0, ',', '.') }}
          </td>
        </tr>
      </tfoot>
    </table>
  @endif

  {{-- GAJI DITERIMA --}}
  <div class="net-box">
    <div class="net-label">Gaji Diterima (Bersih)</div>
    <div class="net-amount">Rp {{ number_format((float) $payroll->net_salary, 0, ',', '.') }}</div>
  </div>

  {{-- INFO REKENING --}}
  @if ($payroll->employee->bank_name || $payroll->employee->bank_account_number)
    <div class="bank-box">
      <div class="bank-col">
        <div class="bank-label">Nama Bank</div>
        <div class="bank-value">{{ strtoupper($payroll->employee->bank_name ?? '-') }}</div>
      </div>
      <div class="bank-col">
        <div class="bank-label">Nomor Rekening</div>
        <div class="bank-value">{{ $payroll->employee->bank_account_number ?? '-' }}</div>
        @if ($payroll->employee->bank_account_holder)
          <div class="bank-sub">a.n. {{ $payroll->employee->bank_account_holder }}</div>
        @endif
      </div>
    </div>
  @endif

  {{-- FOOTER --}}
  <div class="footer">
    <div class="footer-note">
      Dokumen ini dicetak secara otomatis oleh sistem {{ config('app.name') }}.<br>
      Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &bull;
      Dokumen bersifat rahasia.
    </div>
    <div class="footer-ttd">
      <div class="ttd-label">Mengetahui,</div>
      <div class="ttd-space"></div>
      <div class="ttd-line">( HRD / Pimpinan )</div>
    </div>
  </div>

</body>

</html>
