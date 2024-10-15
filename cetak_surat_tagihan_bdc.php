<?php

set_time_limit(0);

session_start();
error_reporting(0);
$file_txt    = 'result.txt';

//require("../config/koneksi.php");
/******localhost******/


$serv_subdomain = array_shift((explode('.', $_SERVER['HTTP_HOST'])));
$serv_host      = $_SERVER['HTTP_HOST'];

if ($serv_host == "localhost") {
  $host       = "localhost";
  $username   = "root";
  $password   = "mysql";
  $db         = "sipema";
} else {
  $host       = "live.ai.web.id";
  $username   = "liveai";
  $password   = "20215uk5e5";
  $db         = "liveai_surat";
}

$conn = mysqli_connect($host, $username, $password, $db);

if (mysqli_connect_errno()) {
  echo "Koneksi database gagal" . mysqli_connect_error();
}

$kode_surat = $_REQUEST['kode_surat'];
if (!empty($kode_surat)) {
  $id_surat = substr($kode_surat, -3); // Mengambil 3 digit terakhir
  $id_mhs = substr($kode_surat, 0, -3); // Mengambil sisa dari string
}

$mhs = $_REQUEST['mhs'];
$kode = $_REQUEST['kode'];
$kodejrs = $_REQUEST['kodejrs'];
// Gunakan API untuk mendapatkan data mahasiswa
// $api_url = 'https://api.p2k.co.id/bdc-marketing/surat-prospek?mhs=50465&kode=wks&kodejrs=S2PO';
// $api_url = 'https://api.p2k.co.id/bdc-marketing/surat-prospek?mhs=50446&kode=iwd&kodejrs=S1BI';
$api_url = 'https://api.p2k.co.id/bdc-marketing/surat-prospek?mhs=' . $mhs . '&kode=' . $kode . '&kodejrs=' . $kodejrs;
$curl = curl_init();

// Setel opsi cURL
curl_setopt_array($curl, array(
  CURLOPT_URL => $api_url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
));

// Jalankan cURL dan ambil respons
$response = curl_exec($curl);
$err = curl_error($curl);

// Tutup cURL
curl_close($curl);

// Periksa apakah ada kesalahan
if ($err) {
  echo "cURL Error #:" . $err;
} else {
  // Mengubah respons API ke array asosiatif
  $api_data = json_decode($response, true);
}

// print_r($api_data);
// exit;

if ($api_data['kode'] === "001" && $api_data['message'] === "Berhasil") {
  // Ambil data dari 'listdata'
  $logokampus = $api_data['listdata']['logokampus'];
  $singkatankampus = $api_data['listdata']['singkatankampus'];
  $kpt = $api_data['listdata']['kpt'];
  $namalengkapkampus = $api_data['listdata']['namalengkapkampus'];
  $alamatkampus = $api_data['listdata']['alamatkampus'];
  $kotakampus = $api_data['listdata']['kotakampus'];
  $nomorsurat = $api_data['listdata']['nomorsurat'];
  $jabatan = $api_data['listdata']['jabatan'];
  $namamahasiswa = $api_data['listdata']['namamahasiswa'];
  $ttl = $api_data['listdata']['ttl'];
  $program = $api_data['listdata']['program'];
  $jurusan = $api_data['listdata']['jurusan'];
  $semester = $api_data['listdata']['semester'];
  $tahunakademik = $api_data['listdata']['tahunakademik'];
  $biayahereg = $api_data['listdata']['biayahereg'];
  $biayaformulir = $api_data['listdata']['biayaformulir'];
  $totaltagihan = $api_data['listdata']['totaltagihan'];
  $tanggalsurat = $api_data['listdata']['tanggalsurat'];
  $stamp = $api_data['listdata']['stamp'];
  $jenjang = substr($jurusan, 0, 2);
} else {
  echo "Gagal mengambil data dari API.";
  exit;
}


$row = $api_data['listdata'];



$filepdf = $_REQUEST['filepdf'] . ".pdf";

/*
$Query     = "SELECT * FROM kirim_surat WHERE id_mhs='$id_mhs' and id_surat='$id_surat' ";
$result    = mysqli_query($conn, $Query);

$row = mysqli_fetch_array($result);
if (!$row) {
  echo "Data tidak ditemukan";
  exit;
}
*/

// $pakai_config = array("umt","ump");

// $config = array();
// if (in_array($row["kode_kampus"], $pakai_config)) {
//     include 'config.php';

//     if ($row["kode_kampus"] == 'umt' || $row["kode_kampus"] == 'ump') {
//         $config = $ttd['mputantular'];
//         $nama_panjang = $config['kampus'];
//         $nama_singkatan = $config['singkatan'];
//         $ttd_nama = $config['ttd_nama'];
//         $ttd_jabatan = $config['ttd_jabatan'];
//         $kop_alamat = $config['kop_alamat'];
//         $kop_telp = '';
//     }
// }else{
//   $nama_panjang = $row['nama_panjang_kampus'];
//   $nama_singkatan = $row['nama_pendek_kampus'];
//   $nama_singkatan_asli = $row['singkatan_asli'];
//   $ttd_nama = $row['nama_rektor'];
//   $ttd_jabatan = $row['jabatan_pimpinan'];
//   $kop_alamat = $row['alamat_kampus'];
//   $kop_telp = '';
// }


$filepdf  = "surat-tagihan.pdf";
// $no_va    = $row["no_va"] . "-" . $row["nama_mahasiswa"];
// //$zfilepdf = "surat-tagihan-$no_surat.pdf";
$zfilepdf = "surat-heregistrasi-$mhs.pdf";

// //$tglskrg = date("m-d-Y");
// function tanggalIndo($tanggal)
// {
//   $bulan = [
//     1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
//     5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
//     9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
//   ];

//   $tanggal_pisah = explode(' ', $tanggal);
//   $bulanAngka = (int)$tanggal_pisah[1];
//   $tanggal_indo = $tanggal_pisah[0] . ' ' . $bulan[$bulanAngka] . ' ' . $tanggal_pisah[2];

//   return $tanggal_indo;
// }

// $now = date("d m Y");
// $tglskrg = tanggalIndo($now);

// $date = date("d m Y", strtotime($row['tgl_surat']));
// $tgl_surat = tanggalIndo($date);

// //$logo_kampus = "http://siakad.my.id/app/_assets/_logo/logo_utn.png";
// $logo_kampus = $row["logo_kampus"];

// $kode_kampus_besar = strtoupper($row['kode_kampus']);
// $stempel_path = "../stempel_p2k/STAMP-" . $kode_kampus_besar . ".jpg";

// if (file_exists($stempel_path)) {
//     $stempel = $stempel_path;
// } else {
//     $singkatan_besar = str_replace(" ", "", $nama_singkatan_asli);
//     $singkatan_besar = strtoupper($singkatan_besar);
//     $stempel_path = "../stempel_p2k/STAMP-" . $singkatan_besar . ".jpg";

//     if (file_exists($stempel_path)) {
//         $stempel = $stempel_path;
//     } else {
//         $stempel = "";
//     }
// }


// $supported_image = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
// $src_file_name = $logo_kampus;
// $ext = strtolower(pathinfo($src_file_name, PATHINFO_EXTENSION));

// if (in_array($ext, $supported_image)) {
//   $cek_logo = "Y";
// } else {
//   $cek_logo = "N";
// }

// if($row['jenjang']=='S1') $jenjang = 'Sarjana';
// elseif($row['jenjang']=='S2') $jenjang = 'Magister';
// elseif($row['jenjang']=='D1' OR $row['jenjang']=='D3') $jenjang = 'Diploma';

// $formulir       = "100000";
// $formatted_tagihan = number_format($row['tagihan_herreg'], 0, '.', ',');
// $max_length = strlen($formatted_tagihan);
// // Memformat dan meratakan nilai-nilai dengan benar
// $biaya_formulir = str_pad(number_format($formulir, 0, '.', ','), $max_length, " ", STR_PAD_LEFT);
// $biaya_hereg    = str_pad($formatted_tagihan, $max_length, " ", STR_PAD_LEFT);
// $total_tagihan  = str_pad($formatted_tagihan, $max_length, " ", STR_PAD_LEFT);

ob_start();



?>
<?php
$dokumen_tersedia = 1;

$stempel = '../stempel/' . $kpt . '.png';
$stempelp2k = '../stempel_p2k/' . $stamp;
$direkturp2k = '(Asep Feri Setiawan, S.E., M.M.)';

if (file_exists($stempel)) {
  $img_file = $stempel;
  $direktur = '';
} else {
  if (file_exists($stempelp2k)) {
    $img_file = $stempelp2k;
    $direktur = $direkturp2k;
  } else {
    $dokumen_tersedia = 0;
  }
}


$image_directory = "../logo/";

// Nama file gambar berdasarkan logokampus
$image_name = $logokampus;

// Periksa apakah file PNG ada
if (file_exists($image_directory . $image_name . ".png")) {
  // Jika file PNG ada, tampilkan file PNG
  $image_path = $image_directory . $image_name . ".png";
}
// Jika tidak ada file PNG, periksa apakah ada file JPG
elseif (file_exists($image_directory . $image_name . ".jpg")) {
  // Jika file JPG ada, tampilkan file JPG
  $image_path = $image_directory . $image_name . ".jpg";
} else {
  $dokumen_tersedia = 0;
}


if ($dokumen_tersedia == 0) {

  echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dokumen Belum Tersedia</title>
        <style>
            body {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                font-family: Arial, sans-serif;
                background-color: #f8f9fa;
                color: #333;
            }
            h1 {
                font-size: 2em;
                margin-bottom: 20px;
            }
            img {
                max-width: 250px;
                width: 100%;
                height: auto;
                margin-bottom: 20px;
            }
            p {
                font-size: 1.2em;
            }
        </style>
    </head>
    <body>
        <h1>Dokumen Belum Tersedia</h1>
        <img src="dokumen_belum_tersedia.svg" alt="Dokumen Error Image">
        <p>Mohon maaf, Dokumen belum tersedia saat ini. Silakan coba lagi nanti.</p>
    </body>
    </html>
    ';
  exit;
}
?>

<html>
<!--
<script src="jquery-1.10.2.js"></script>
!-->

<style type="text/css">
  .double-line {
    border-top: 2px solid #2E3A5B;
    border-bottom: 4px solid #2E3A5B;
    height: 2px;
    width: 100%;
  }

  .spaced-text {
    letter-spacing: 2px;
  }
</style>

<body>
  <!--
  <table   border="0" 
      style="padding-left: 2mm;padding-right: 2mm; 
      /*background-image:url(bg_02.png);*/
      background-repeat: no-repeat;
      background-size: cover;
      border-collapse: collapse;
      width:160mm; margin-top:5mm; margin-left:5mm;
      font-family: Arial, Helvetica, sans-serif  ;  
      font-size:14px;">
!-->

  <table height="100%" border="0" style="padding-left: 5mm;
      border-collapse: collapse; 
      margin-top:1mm;
      width:180mm;
      font-family:'Times New Roman', Times, serif;   
      font-size:13px;">


    <tbody>
      <tr align="center">
        <td height="15">

          <!-- Tampilkan gambar -->
          <img src="<?php echo $image_path; ?>" width="150" height="auto">
          <!-- <img src="<?php echo "../logo/" . $logokampus . ".png"; //$row['logo_kampus']; 
                          ?>" width=150 height=auto> -->
        </td>
      </tr>
      <tr align="center">
        <td style="text-transform: uppercase;">
          <p style="font-size:19px;margin:0;margin-bottom:3px;margin-top:5px;"><b><?php echo $namalengkapkampus; ?></b></p>
          <p style="font-size:16px;margin:0;margin-bottom:5px;color:#2E3A5B;"><b><?php echo $program; ?></b></p>
          <span style="text-transform: none;"><?php echo $alamatkampus; ?></span>
        </td>
      </tr>

      <tr>
        <td height="15" style="padding-top:7px;">
          <div class="double-line"></div>
        </td>
      </tr>

      <tr>
        <td style="padding-left: 20px;padding-right: 10px;">
          <table border="0" style="width:100%;font-family: 'Times New Roman', Times, serif; 
              font-size:13px;border-collapse: collapse;">

            <tr align="center">
              <td colspan="3" style="padding-top: 10px;padding-bottom:10px;width:170mm;">
                <b>INFORMASI TAGIHAN MAHASISWA BARU</b>
              </td>
            </tr>

            <tr>
              <td colspan="3" valign="top" colspan="4" height="200">
                <div style="width:670px;margin-bottom:10px">
                  <p style="margin:0;margin-bottom:3px;">Kepada Yth.</p>
                  <b><?php echo $namamahasiswa; ?></b>
                  <!-- <b>LISSA SEREWI</b> -->
                  <!-- <br><b>Nomor Pendaftaran <?php echo $row['nosel']; ?></b> -->
                  <!-- <br><b>Nomor Pendaftaran SAS10G24-11006</b> -->
                  <br>Mahasiswa Baru <?php echo $jurusan; ?>
                  <br><?php echo $namalengkapkampus; ?>
                  <br>di tempat
                  <br>
                  <br>Dengan hormat,
                  <br>
                  <br>Menindaklanjuti proses pendaftaran mahasiswa baru (<b><?php echo $jurusan; ?></b>)
                  <b>Program Perkuliahan Karyawan <?php echo $namalengkapkampus; ?></b>, untuk memperoleh Nomor Induk
                  Mahasiswa, Kartu Rencana Studi, Kartu Tanda Mahasiswa, Sistem Informasi Akademik,
                  Penginputan Pangkalan Data Dikti, Perkuliahan Matrikulasi, Jadwal Perkuliahan dan Pembagian Kelas. Diharapkan kepada seluruh mahasiswa baru untuk segera melengkapi proses pendaftaran dengan melunasi
                  tagihan/kewajiban pembiayaan sebagai berikut :
                </div>
              </td>
            </tr>

            <tr height="20">
              <td colspan="1" style="border: 1px solid black; border-left: none; border-right:none ">
              </td>
              <td style="border-bottom: 1px solid black;border-left: none; border-right:none "></td>
            </tr>

            <tr>
              <td colspan=1 style="width: 220px;background-color: #ADD8E6;border: 1px solid black; text-align: center; ">
                <strong align="center"><em>Nama Mahasiswa</em></strong>
              </td>
              <td style="width: 220px;background-color: #ADD8E6;border: 1px solid black; text-align: center;">
                <strong><em>Jenis dan Jumlah Tagihan</em></strong>
              </td>
            </tr>
            <tr>
              <td colspan=1 valign="top" style="width: 280px;border: 1px solid black;">
                <div style="width: 280px;padding-left:5px;padding:10px 5px">
                  <strong><?php echo $namamahasiswa; ?></strong> <br>
                  <strong><em><?php echo $jurusan; ?></em></strong>
                </div>
              </td>
              <td valign="top" style="width: 220px;border: 1px solid black;padding-top:5px">
                <div style="width: 220px;margin-left: 5px;margin-top:10px;">
                  Biaya Formulir<span style="margin-left: 48px"><strong><?php echo $biayaformulir; ?></strong></span>
                </div>
                <div style="width: 220px;margin-left: 5px;">
                  Biaya Daftar Ulang<span style="margin-left: 26px"><strong><?php echo $biayahereg; ?></strong></span>
                </div>
                <div style="width: 220px;margin-left: 5px;margin-bottom:10px;">
                  <hr>
                  Total Tagihan<span style="margin-left: 55px"><strong><?php echo $totaltagihan; ?></strong></span>
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="3" style="width:80%;">
                <br>Pembayaran dapat dilakukan melalui transfer pada Bank berikut :
                <br>
                <br>Bank Negara Indonesia (BNI) : <b>837 746 559</b> (Program Perkuliahan Karyawan - Gilland Ganesha)
                <br>Bank Mandiri : <b>157 000 7000 111</b> (Program Perkuliahan Karyawan)
                <br>Bank Syariah Indonesia (BSI) : <b>700 722 65 13</b> (Program Perkuliahan Karyawan)
                <br>CIMB Niaga : <b>860007446600</b> (Gilland Ganesha)
                <br>Bank Central Asia (BCA) : <b>1673009411</b> (Gilland Ganesha PT)
                <br>
                <br>Mohon simpan bukti pembayaran dengan baik dan tunjukkan kepada admin jika diminta untuk verifikasi.
                <br>
                <br> Demikian informasi ini disampaikan, agar dapat dipahami dan dilaksanakan sebagaimana mestinya. <br>Atas perhatian dan kerja samanya dihaturkan terima kasih,
              </td>
            </tr>

            <tr>
              <td width="130"></td>
              <td valign="top" style="padding-left: 50mm;">
                <br>
                <?php echo $row['kotakampus']; ?>, <?php echo $tanggalsurat; ?>
                <br>
                <?php
                if (empty($ttd_nama))
                  echo 'Direktur PMB P2K <br> ' . $singkatankampus;
                else
                  echo $ttd_jabatan;
                ?>
                <br><img src="../stempel_p2k/<?php echo $img_file; ?>" width=200 height=auto style="padding-top:10px;">
                <br><b><?php echo $direktur; ?></b>
              </td>
            </tr>
            <tr>
              <td valign="top" colspan="3">
                <p style="padding:0px;margin-bottom: 7px"><b><i><u>Tembusan kepada Yth. :</u></i></b></p>
                1. <?php echo $jabatan; ?> <?php echo $singkatankampus; ?>
                <br>2. Para Wakil <?php echo $jabatan; ?> <?php echo $singkatankampus; ?>
                <br>3. Para Kaprodi <?php echo $singkatankampus; ?>
                <br>4. Arsip
              </td>
            </tr>


          </table>
        </td>
      </tr>

    </tbody>

  </table>

</body>



</html>

<?php


//return;

$html = ob_get_contents();
$content = '<page style="font-family: freeserif;">' . nl2br($html) . '</page>';
ob_end_clean();
require_once('html2pdf.class.php');
$pdf = new HTML2PDF('P', 'A4', 'en');
$pdf->setDefaultFont('Times');
//$pdf = new HTML2PDF('L', array(170, 210), 'en'); /******1/2 halaman**********/
//$html2pdf->pdf->SetProtection(array('print', 'copy'), 'mi_password', null, 0, null);
//$pdf->AddPage();
// $pdf->WriteHTML($html, isset($_GET['vuehtml']));
// $pdf->Output($zfilepdf, 'FI');

$pdf->WriteHTML($html, isset($_GET['vuehtml']));

// Jika ingin langsung create file PDF buka kode ini
$zfilepdf = '../bdcv2/' . basename($zfilepdf);

// Output file ke folder ../arsip/ dengan nama file asli
$pdf->Output($zfilepdf, 'F');

// Buka file yang baru disimpan
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($zfilepdf) . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
@readfile($zfilepdf);



/*
$mail = new PHPMailer();
$mail->setFrom('senderSMTP@yahoo.com', 'sender');
$mail->addAddress('test@gmail.com', 'test');
$mail->Subject = 'TestMail';
$mail->addAttachment($pdf, 'file.pdf');
$mail->Body = 'TestMessage';

if($mail->send())
{
    echo 'success';
}
else
{
    echo $mail->ErrorInfo;
}
*/
?>