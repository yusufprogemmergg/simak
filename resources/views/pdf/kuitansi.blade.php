<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pembayaran</title>
    <style>
        @page {
            margin: 30px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .company-logo {
            width: 60px;
            height: auto;
        }
        .company-details {
            padding-left: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .company-contact {
            font-size: 10px;
            color: #666;
        }
        .receipt-title-box {
            text-align: right;
        }
        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .receipt-no {
            font-size: 12px;
            color: #555;
        }
        
        .content-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .content-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .col-label {
            width: 150px;
            color: #555;
        }
        .col-colon {
            width: 15px;
        }
        .col-value {
            font-weight: bold;
        }
        .amount-words {
            font-style: italic;
            font-size: 13px;
        }
        
        .footer {
            width: 100%;
            margin-top: 30px;
        }
        .footer table {
            width: 100%;
        }
        .amount-box {
            border: 2px solid #333;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            min-width: 150px;
            text-align: center;
        }
        .signature-area {
            text-align: center;
            width: 200px;
        }
        .signature-date {
            margin-bottom: 50px;
        }
        .signature-name {
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin: 0 auto;
            width: 80%;
        }
        .footer-note {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <!-- Logo & Company -->
                <td width="60%" valign="middle">
                    <table cellspacing="0" cellpadding="0">
                        <tr>
                            @if($logoBase64)
                                <td width="70"><img src="{{ $logoBase64 }}" class="company-logo" alt="Logo"></td>
                            @endif
                            <td class="company-details">
                                <div class="company-name">{{ $profile ? $profile->name : 'NAMA PERUSAHAAN' }}</div>
                                <div class="company-contact">
                                    {{ $profile ? $profile->alamat : 'Alamat Perusahaan' }}<br>
                                    Telp: {{ $profile ? $profile->telepon : '-' }} | Email: {{ $profile ? $profile->email : '-' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                
                <!-- Title & No -->
                <td width="40%" valign="middle" class="receipt-title-box">
                    <div class="receipt-title">KUITANSI</div>
                    <div class="receipt-no">No:<br>{{ $kuitansiNo }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="content-table" cellspacing="0" cellpadding="0">
        <tr>
            <td class="col-label">Telah Diterima Dari</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $buyer ? $buyer->nama : '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Uang Sejumlah</td>
            <td class="col-colon">:</td>
            <td class="col-value amount-words">{{ $terbilang }}</td>
        </tr>
        <tr>
            <td class="col-label">Untuk Pembayaran</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $payment->keterangan }}</td>
        </tr>
        <tr>
            <td class="col-label">Blok</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $kavling ? $kavling->blok_nomor : '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Kavling</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $project ? $project->nama_project : '-' }}</td>
        </tr>
    </table>

    <div class="footer">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td valign="bottom" width="60%">
                    <div class="amount-box">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </div>
                </td>
                <td valign="bottom" width="40%" align="right">
                    <table class="signature-area" align="right">
                        <tr>
                            <td class="signature-date">
                                {{ date('d M Y', strtotime($payment->tanggal)) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="signature-name">
                                {{ $profile ? $profile->nama_ttd_admin : 'Admin' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    @if($profile && $profile->catatan_kaki_cetakan)
    <div class="footer-note">
        {{ $profile->catatan_kaki_cetakan }}
    </div>
    @endif

</body>
</html>
