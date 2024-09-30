<?php
require 'db.php';
require 'vendor/autoload.php'; // Include TCPDF

// require_once('tcpdf/tcpdf.php');

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    // Ambil data pengguna
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buat instance TCPDF
    $pdf = new TCPDF();

    if ($user) {
        // Ambil data pembayaran pertama
        $stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = :user_id ORDER BY payment_date ASC LIMIT 1");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        // Buat surat tagihan
        $pdf = new TCPDF();
        $pdf->AddPage();

        $html = '
            <h1>Surat Tagihan</h1>
            <p>Yth. ' . $user['nama'] . '</p>
            <p>Alamat: ' . $user['alamat'] . '</p>
            <p>Email: ' . $user['email'] . '</p>
            <br>
            <p>Pembayaran pertama Anda: Rp ' . number_format($payment['amount'], 2) . '</p>
            <p>Tanggal Pembayaran: ' . date('d-m-Y', strtotime($payment['payment_date'])) . '</p>
        ';

        $pdf->writeHTML($html);
        $pdf->Output('surat_tagihan.pdf', 'I'); // 'I' untuk inline download, 'D' untuk force download
    } else {
        echo json_encode(['message' => 'User not found']);
    }
}
?>
