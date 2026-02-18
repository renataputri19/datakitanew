<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Penyelesaian Survei SIBSTR</title>
    <style>
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; text-align: center; color: #333; }
        .certificate-container { border: 5px double #1F2937; padding: 40px; margin: 20px; position: relative; }
        .header { margin-bottom: 30px; }
        .header h1 { font-size: 28px; text-transform: uppercase; margin: 0; color: #1F2937; margin-bottom: 10px; }
        .header h2 { font-size: 18px; font-weight: normal; margin: 0; color: #4B5563; }
        .content { margin: 40px 0; font-size: 16px; line-height: 1.6; }
        .recipient-name { font-size: 24px; font-weight: bold; margin: 20px 0 5px; color: #111827; }
        .company-name { font-size: 20px; font-weight: bold; margin: 5px 0 20px; color: #4B5563; }
        .date { font-weight: bold; }
        .footer { margin-top: 60px; font-size: 14px; color: #6B7280; }
        .signature { margin-top: 40px; }
        .signature p { margin: 5px 0; }
        
        /* Optional: Add a watermark or logo if available */
        .watermark { opacity: 0.05; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 100px; z-index: -1; }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="header">
            <h1>Bukti Penyelesaian Survei</h1>
            <h2>Survei Industri Besar dan Sedang Triwulanan (SIBSTR)</h2>
        </div>
        
        <hr style="border: 1px solid #E5E7EB; margin: 20px 0;">

        <div class="content">
            <p>Diberikan kepada:</p>
            
            <div class="recipient-name">{{ $user->name }}</div>
            <div class="company-name">{{ $response->nama_perusahaan ?? 'Perusahaan' }}</div>
            
            <p>Atas partisipasinya dalam melengkapi dan menyelesaikan<br><strong>Survei Industri Besar dan Sedang Triwulanan (SIBSTR)</strong></p>
            
            <p>Status: <span style="color: #059669; font-weight: bold;">SELESAI</span></p>
            
            <p>Diselesaikan pada tanggal:<br><span class="date">{{ \Carbon\Carbon::parse($completedAt)->isoFormat('D MMMM Y') }}</span></p>
        </div>

        <div class="footer">
            <p>Terima kasih atas kerjasama dan partisipasi Anda.</p>
            
            <div class="signature">
                <p><strong>Badan Pusat Statistik</strong></p>
                <p>Website: www.bps.go.id</p>
            </div>
            
            <div style="font-size: 10px; margin-top: 30px; color: #9CA3AF;">
                Dokumen ini dicetak secara otomatis pada {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</body>
</html>
