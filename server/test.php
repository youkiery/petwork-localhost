<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
date_default_timezone_set('Asia/Ho_Chi_Minh');
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

include_once('../include/config.php');
include_once(DIR. '/include/db.php');
include_once(DIR. '/include/global.php');
define('PREFIX', $config['prefix']);

$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

/*
  1: thống kê danh sách khách spa
*/

// $sql = "select * from pet_phc_spadichvu where idnhanvien in (5,6)";
// $danhsach = $db->all($sql);
// $dulieu = [];

// $sql = "select * from pet_phc_spaloai";
// $dulieuloai = $db->obj($sql, 'id', 'name');

// foreach($danhsach as $thongtin) {
//   if (empty($dulieu[$thongtin['idkhach']])) {
//     $sql = "select * from pet_phc_customer where id = $thongtin[idkhach]";
//     $khach = $db->fetch($sql);
//     $dulieu[$thongtin['idkhach']] = [
//       'khachhang' => $khach['name'],
//       'dienthoai' => $khach['phone'],
//       'tongtien' => 0,
//       'ngay' => [],
//       'dichvu' => [],
//     ];
//   }
//   $ngay = strtotime(date('Y/m/d', $thongtin['thoigian']));
//   if (empty($dulieu[$thongtin['idkhach']]['ngay'][$ngay])) $dulieu[$thongtin['idkhach']]['ngay'][$ngay] = 1;
//   if (empty($dulieu[$thongtin['idkhach']]['dichvu'][$dulieuloai[$thongtin['idloai']]])) $dulieu[$thongtin['idkhach']]['dichvu'][$dulieuloai[$thongtin['idloai']]] = 0;
//   $dulieu[$thongtin['idkhach']]['dichvu'][$dulieuloai[$thongtin['idloai']]] += $thongtin['soluong'];
//   $dulieu[$thongtin['idkhach']]['tongtien'] += $thongtin['tongtien'];
// }

// echo "<pre>";
// foreach ($dulieu as $idkhach => $thongtin) {
//   $tonglan = count($thongtin['ngay']);
//   $dichvu = [];
//   foreach ($thongtin['dichvu'] as $loai => $soluong) {
//     $dichvu []= "$loai: $soluong";
//   }
//   $dichvu = implode(', ', $dichvu);
//   $tongtien = number_format($thongtin['tongtien']);
//   echo "$thongtin[khachhang]&#9;$thongtin[dienthoai]&#9;$tongtien&#9;$tonglan&#9;$dichvu<br>";
// }
// echo "</pre>";

include DIR .'include/PHPExcel/IOFactory.php';
    
$des = DIR . "/include/kien.xlsx";
$inputFileType = PHPExcel_IOFactory::identify($des);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($des);

$sheet = $objPHPExcel->getSheet(0); 

$highestRow = $sheet->getHighestRow(); 
$highestColumn = $sheet->getHighestColumn();

$danhsachkhach = ['0837955905', '0966172909', '0934848282', '0963217508', '0941279979', '0815524747', '0943839930', '0862463790', '0975487979', '0946088388', '0773640322', '0842510789', '0913998088', '0905740077', '0937474140', '0966377903', '0987686784', '0969050581', '0352326789', '0905590649', '0905002812', '0909982707', '0903596412', '0906635487', '0935790101', '0962496973', '0982060563', '0917267475', '0975112332', '0906577009', '0905611974', '0332723468', '0914504379', '0905960904', '0931890379', '0913444804', '0383328928', '0948617577', '0901470314', '0935450799', '0905689379', '0982500345', '0973238006', '0905319329', '0979482242', '0972804030', '0978192192', '0905598525', '0839195173', '0943061357', '0846470447', '0888248888', '0904932699', '0913423684', '0822910559', '0919411034', '0335023698', '0948041155', '0934990266', '0395248348', '0855555447', '0909023466', '0833690907', '0903545525', '0919491040', '0935496244', '0933337723', '0971111518, 09693803', '0903549919', '0911666674', '0935888235', '0903871981', '0336436170', '0987764297', '0942434564', '0989055688', '0914999528', '0794880123', '0386209487', '0935745657', '0842878899', '0395673413', '0918811010', '0963910121', '0946673067', '0968117489', '0389933023', '0968524302', '0816866616', '0395909101', '0935897887', '0943053723', '0905012626', '0971774761', '0918070108', '0989392185', '0945415599', '0935782545', '0762618172', '0982024748', '0846348182', '0941749294', '0786866847', '0911434768', '0905449090'];
$danhsachspa = ['SP4195753','SP4195752','SP4195752','SP4195609','SP4195608','SP4195607','SP4195606','SP4195605','SP4195604','SP4195603','SP4195602','SP4195601','SP4195600','SP4195599','SP4195598','SP4195597','SP4195596','SP4195595','SP4195594','SP4195593','SP4195592','SP4195591','SP4195590','SP4195589','SP4195586','SP4195585','SP4195584','SP4195583','SP4195581','SP4195580','SP4195579','SP4195578','SP4195577','SP4195576','SP4195575','SP4195574','SP4195573','SP4195572','SP4195571','SPA032','SPA031','SPA026','SPA025','SPA015','SPA014','SPA012','SPA011','SPA003','SPA002'];

$danhsach = [];
foreach ($danhsachkhach as $dienthoai) {
  $danhsach[$dienthoai] = [];
}
for ($i = 2; $i <= $highestRow; $i ++) { 
  $dulieu = [
    'khachhang' => $sheet->getCell("B$i")->getValue(),
    'dienthoai' => $sheet->getCell("C$i")->getValue(),
    'mahang' => $sheet->getCell("D$i")->getValue(),
    'tenhang' => $sheet->getCell("E$i")->getValue(),
    'soluong' => $sheet->getCell("F$i")->getValue(),
    'giaban' => $sheet->getCell("G$i")->getValue(),
  ];
  if (isset($danhsach[$dulieu['dienthoai']])) {
    if (empty($danhsach[$dulieu['dienthoai']])) $danhsach[$dulieu['dienthoai']] = [];
    if (in_array($dulieu['mahang'], $danhsachspa) == false) {
      if (empty($danhsach[$dulieu['dienthoai']][$dulieu['tenhang']])) $danhsach[$dulieu['dienthoai']][$dulieu['tenhang']] = 0;
      $danhsach[$dulieu['dienthoai']][$dulieu['tenhang']] += $dulieu['soluong'];
    }
  }
}

echo "<pre>";
foreach ($danhsach as $dienthoai => $dichvu) {
  $dv = [];
  foreach ($dichvu as $loai => $soluong) {
    $dv []= "$loai: $soluong";
  }
  $dv = implode(', ', $dv);
  echo "$dv<br>";
}
echo "</pre>";

// sheet 1: tổng dùng để đối chiếu
// sheet 2: spa
// sheet 2: điều trị
// sheet 2: vaccine

die();
