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
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
      color: #1a1a1a;
      background: #fff;
      padding: 30px 34px;
    }

    /* ── HEADER ── */
    .header {
      display: table;
      width: 100%;
      border-bottom: 3px solid #1a1a1a;
      padding-bottom: 12px;
      margin-bottom: 16px;
    }

    .header-logo {
      display: table-cell;
      vertical-align: middle;
      width: 50px;
    }

    .header-logo img {
      width: 44px;
      height: 44px;
    }

    .header-company {
      display: table-cell;
      vertical-align: middle;
      padding-left: 10px;
    }

    .company-name {
      font-size: 16px;
      font-weight: bold;
    }

    .company-sub {
      font-size: 10.5px;
      color: #555;
      margin-top: 2px;
    }

    .header-title {
      display: table-cell;
      vertical-align: middle;
      text-align: right;
    }

    .slip-title {
      font-size: 26px;
      font-weight: bold;
      text-transform: uppercase;
    }

    /* ── EMPLOYEE INFO ── */
    .info-section {
      display: table;
      width: 100%;
      margin-bottom: 16px;
    }

    .info-col {
      display: table-cell;
      width: 50%;
      vertical-align: top;
    }

    .info-row {
      padding: 2px 0;
    }

    .info-label {
      display: inline-block;
      width: 120px;
      color: #555;
    }

    .info-sep {
      display: inline-block;
      width: 12px;
    }

    .info-value {
      font-weight: bold;
    }

    /* ── PENDAPATAN / POTONGAN ── */
    .columns {
      display: table;
      width: 100%;
      table-layout: fixed;
      margin-bottom: 16px;
    }

    .col {
      display: table-cell;
      width: 50%;
      vertical-align: top;
    }

    .col + .col {
      padding-left: 18px;
    }

    .section-header {
      background: #e5e7eb;
      font-weight: bold;
      font-size: 12px;
      padding: 6px 8px;
      margin-bottom: 4px;
    }

    table.items {
      width: 100%;
      border-collapse: collapse;
    }

    table.items td {
      padding: 4px 8px;
      font-size: 12px;
      vertical-align: top;
    }

    table.items td.amount {
      text-align: right;
      white-space: nowrap;
    }

    table.items td.notes {
      font-size: 10px;
      color: #777;
    }

    .total-row td {
      border-top: 2px solid #1a1a1a;
      font-weight: bold;
      padding-top: 6px;
    }

    /* ── NET BOX ── */
    .net-box {
      display: table;
      width: 100%;
      border: 2px solid #1a1a1a;
      padding: 12px 14px;
      margin-bottom: 16px;
    }

    .net-label {
      display: table-cell;
      vertical-align: middle;
      font-size: 13px;
      font-weight: bold;
    }

    .net-amount {
      display: table-cell;
      vertical-align: middle;
      text-align: right;
      font-size: 20px;
      font-weight: bold;
    }

    /* ── BANK BOX ── */
    .bank-box {
      display: table;
      width: 100%;
      margin-bottom: 16px;
      border: 1px solid #cbd5e1;
      padding: 10px 14px;
    }

    .bank-col {
      display: table-cell;
      vertical-align: middle;
    }

    .bank-col + .bank-col {
      border-left: 1px solid #cbd5e1;
      padding-left: 16px;
    }

    .bank-label {
      font-size: 10px;
      color: #555;
      text-transform: uppercase;
    }

    .bank-value {
      font-size: 12.5px;
      font-weight: bold;
      margin-top: 2px;
    }

    .bank-sub {
      font-size: 10.5px;
      color: #555;
      margin-top: 2px;
    }

    /* ── FOOTER ── */
    .footer {
      display: table;
      width: 100%;
      margin-top: 24px;
      padding-top: 10px;
      border-top: 1px solid #cbd5e1;
    }

    .footer-note {
      display: table-cell;
      vertical-align: bottom;
      font-size: 9px;
      color: #777;
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
      color: #555;
    }

    .ttd-space {
      height: 50px;
    }

    .ttd-line {
      font-size: 11px;
      font-weight: bold;
      border-top: 1.5px solid #555;
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
      <div class="company-sub">Periode: {{ $payroll->period->code }}</div>
    </div>
  </div>

  {{-- INFO KARYAWAN --}}
  <div class="info-section">
    <div class="info-col">
      <div class="info-row">
        <span class="info-label">Nama Karyawan</span><span class="info-sep">:</span>
        <span class="info-value">{{ $payroll->employee->full_name }} ({{ $payroll->employee->employee_number }})</span>
      </div>
      @if ($payroll->employee->position)
        <div class="info-row">
          <span class="info-label">Jabatan</span><span class="info-sep">:</span>
          <span class="info-value">{{ $payroll->employee->position }}</span>
        </div>
      @endif
      @if ($payroll->employee->contract_start_date)
        <div class="info-row">
          <span class="info-label">Tgl Mulai Bekerja</span><span class="info-sep">:</span>
          <span class="info-value">{{ $payroll->employee->contract_start_date->translatedFormat('d M Y') }}</span>
        </div>
      @endif
    </div>
    <div class="info-col">
      <div class="info-row">
        <span class="info-label">Periode Gaji</span><span class="info-sep">:</span>
        <span class="info-value">
          {{ \Carbon\Carbon::create()->month($payroll->period->month)->translatedFormat('F') }}
          {{ $payroll->period->year }}
        </span>
      </div>
      <div class="info-row">
        <span class="info-label">Hari Kerja</span><span class="info-sep">:</span>
        <span class="info-value">{{ $payroll->present_days }} hari</span>
      </div>
      @if ($payroll->period->payment_date)
        <div class="info-row">
          <span class="info-label">Tgl. Bayar</span><span class="info-sep">:</span>
          <span class="info-value">{{ $payroll->period->payment_date->translatedFormat('d M Y') }}</span>
        </div>
      @endif
    </div>
  </div>

  {{-- PENDAPATAN / POTONGAN --}}
  <div class="columns">
    {{-- PENDAPATAN --}}
    <div class="col">
      <div class="section-header">Pendapatan</div>
      <table class="items">
        <tbody>
          @foreach ($earnings as $item)
            <tr>
              <td>{{ $item->component_name }}</td>
              <td class="amount">{{ rupiah($item->amount) }}</td>
            </tr>
          @endforeach
          @if ($payroll->overtime_amount > 0)
            <tr>
              <td>Lembur ({{ number_format($payroll->overtime_minutes / 60, 1) }} Jam)</td>
              <td class="amount">{{ rupiah($payroll->overtime_amount) }}</td>
            </tr>
          @endif
        </tbody>
        <tfoot>
          <tr class="total-row">
            <td>Total Pendapatan</td>
            <td class="amount">{{ rupiah((float) $payroll->total_earnings + (float) $payroll->overtime_amount) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- POTONGAN --}}
    <div class="col">
      <div class="section-header">Potongan</div>
      <table class="items">
        <tbody>
          @forelse ($deductions as $item)
            <tr>
              <td>
                {{ $item->component_name }}
                @if ($item->notes)
                  <br><span class="notes">{{ $item->notes }}</span>
                @endif
              </td>
              <td class="amount">{{ rupiah($item->amount) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="notes">Tidak ada potongan</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr class="total-row">
            <td>Total Potongan</td>
            <td class="amount">
              {{ rupiah($payroll->total_deductions + $payroll->total_tax_pph21 + $payroll->total_bpjs) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- GAJI DITERIMA --}}
  <div class="net-box">
    <div class="net-label">Gaji Diterima (Bersih)</div>
    <div class="net-amount">{{ rupiah($payroll->net_salary) }}</div>
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
