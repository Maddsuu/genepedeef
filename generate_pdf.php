<?php
require 'vendor/autoload.php'; // Include TCPDF

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data pengguna dan pembayaran (hardcoded)
    $payment = [
        'amount' => 1500000,
        'payment_date' => '2024-10-15'
    ];

    // Baca template HTML
    $html = file_get_contents('file.html');

    // Ganti placeholder dengan data dinamis
    $html = str_replace('{{amount}}', number_format($payment['amount'], 2), $html);
    $html = str_replace('{{payment_date}}', date('d-m-Y', strtotime($payment['payment_date'])), $html);

    // Buat instance TCPDF
    $pdf = new TCPDF();
    $pdf->AddPage();

    // Tulis HTML ke dalam PDF
    $pdf->writeHTML($html);

    // Output PDF
    $pdf->Output('surat_tagihan.pdf', 'I'); // 'I' untuk inline download, 'D' untuk force download
} else {
    echo 'Harap gunakan metode POST untuk mengakses halaman ini.';
}
?>
