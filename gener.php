<?php
require 'vendor/autoload.php'; // Include TCPDF

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data pengguna dan pembayaran (hardcoded)
    $user = [
        'nama' => 'Budi Santoso',
        'alamat' => 'Jl. Mawar No. 12, Jakarta',
        'email' => 'budi@example.com'
    ];

    $payment = [
        'amount' => 1500000,
        'payment_date' => '2024-10-15'
    ];

    // Buat instance TCPDF
    $pdf = new TCPDF();
    $pdf->AddPage();

    // Isi HTML surat tagihan
    $html = '
        <h1><b>PEMBAYARAN KODE VA MELALUI
        <br>M-BANKING SEMUA BANK</b></h1>
        <br>
        <p>Pembayaran pertama Anda: Rp ' . number_format($payment['amount'], 2) . '</p>
        <p>Tanggal Pembayaran: ' . date('d-m-Y', strtotime($payment['payment_date'])) . '</p>
    ';

    // Tulis HTML ke dalam PDF
    $pdf->writeHTML($html);

    // Output PDF
    $pdf->Output('surat_tagihan.pdf', 'I'); // 'I' untuk inline download, 'D' untuk force download
} else {
    echo 'Harap gunakan metode POST untuk mengakses halaman ini.';
}
?>
