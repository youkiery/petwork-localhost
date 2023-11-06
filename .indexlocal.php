<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
date_default_timezone_set('Asia/Ho_Chi_Minh');
include_once(ROOTDIR . '/include/config.php');
include_once(ROOTDIR . '/include/db.php');
include_once(ROOTDIR . '/include/global.php');
$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

// $sql = "select * from pet_tracnghiem_cauhoi where idchuyenmuc = 1 order by rand() limit 10";
// $danhsachcauhoi = $db->all($sql);

// $chuyendoitraloi = [0 => "A", "B", "C", "D"];

// foreach ($danhsachcauhoi as $thutucauhoi => $cauhoi) {
//   $sql = "select * from pet_tracnghiem_cautraloi order by rand() limit 4";
//   $danhsachcautraloi = $db->all($sql);
//   echo "<div><b> $cauhoi[noidung] </b> </div>";
//   foreach ($danhsachcautraloi as $thutucautraloi => $cautraloi) {
//     echo "<div style='display: inline-block;width:25%;'> <label> <input type='radio' name='$thutucauhoi' cauhoi='$thutucauhoi' cautraloi=$thutucautraloi /> $chuyendoitraloi[$thutucautraloi]. $cautraloi[noidung]   </label> </div>";
//   }
// }

// die();

// $sql = "select * from pet_phc_spadichvu order by thoigian";
// $danhsach = $db->all($sql);
// $thoigian = [];

// foreach ($danhsach as $row) {
//   $ngay = date('d/m.y', $row['thoigian']);
//   if (empty($thoigian[$ngay])) $thoigian[$ngay] = 1;
// }

// foreach ($thoigian as $ngay => $value) {
//   echo "$ngay <br>";
// }
// die();

// $dulieu = [];
// $danhsachhoadon = [];
// for ($i = 1; $i < 4; $i++) {
//   $dulieu[$i] = [
//     'hoadon' => [],
//     'khachhang' => []
//   ];

//   foreach ($danhsach[$i] as $dulieukhach) {
//     if (empty($dulieu[$i]['hoadon'][$dulieukhach['hoadon']])) {
//       $danhsachhoadon[$dulieukhach['hoadon']] = 1;
//       $dulieu[$i]['hoadon'][$dulieukhach['hoadon']] = 1;

//       if (empty($dulieu[$i]['khachhang'][$dulieukhach['dienthoai']])) $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']] = [
//         'khachhang' => $dulieukhach['khachhang'],
//         'tienhang' => 0,
//         'solan' => 0,
//         'soluong' => 0,
//       ];
//       $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['tienhang'] += $dulieukhach['tienhang'];
//       $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['soluong'] += $dulieukhach['soluong'];
//       $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['solan'] ++;
//     }
//   }
// }

// $i = 0;
// $dulieu[$i] = [
//   'hoadon' => [],
//   'khachhang' => []
// ];

// foreach ($danhsach[$i] as $dulieukhach) {
//   // hóa đơn chưa thêm
//   // hóa đơn không có trong danh sách hoa đơn
//   if (empty($dulieu[$i]['hoadon'][$dulieukhach['hoadon']]) && empty($danhsachhoadon[$dulieukhach['hoadon']])) {
//     $danhsachhoadon[$dulieukhach['hoadon']] = 1;
//     $dulieu[$i]['hoadon'][$dulieukhach['hoadon']] = 1;
//     if (empty($dulieu[$i]['khachhang'][$dulieukhach['dienthoai']])) $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']] = [
//       'khachhang' => $dulieukhach['khachhang'],
//       'tienhang' => 0,
//       'solan' => 0,
//       'soluong' => 0,
//     ];
    
//     $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['tienhang'] += $dulieukhach['tienhang'];
//     $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['soluong'] += $dulieukhach['soluong'];
//     $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['solan'] ++;
//   }
// }

// foreach ($dulieu as $thutu => $dulieukhach) {
//   $thutuy = 2;
//   foreach ($dulieukhach['khachhang'] as $dienthoai => $value) {
//     $objPHPExcel
//     ->setActiveSheetIndex($thutu)
//     ->setCellValue("A" . $thutuy, $value['khachhang'])
//     ->setCellValue("B" . $thutuy, $dienthoai)
//     ->setCellValue("C" . $thutuy, $value['tienhang'])
//     ->setCellValue("D" . $thutuy, $value['solan'])
//     ->setCellValue("E" . $thutuy, $value['soluong']);
//     $thutuy ++;
//   }
// }

// $time = time(); 
// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
// $objWriter->save('./include/file-mau-'. $time .'.xlsx');
// $objPHPExcel->disconnectWorksheets();
// unset($objWriter, $objPHPExcel);

// die();
// // sheet 1: tổng dùng để đối chiếu
// // sheet 2: spa
// // sheet 2: điều trị
// // sheet 2: vaccine
// echo "<pre>";
// foreach ($dulieu as $dienthoai => $data) {
//   foreach ($data as $value) {
//     if (!is_array($value)) echo "$value&#9;";
//   }
//   echo "<br>";
// }
// echo "</pre>";

// die();

// for ($i = 0; $i < 7; $i++) { 
//   $date = time() + $i * 60 * 60 * 24;
//   echo date('N', $date) . ': '. date('d/m/Y', $date) . '<br>';
// }
// die();

// $sql = "select * from pet_". PREFIX ."_xray";
// $danhsach = $db->all($sql);

// foreach ($danhsach as $key => $his) {
//   $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('hisdisease', $his[id], $his[diseaseid], 0)";
//   $db->query($sql);
// }

// $thoigian = time();
// for ($i = 0; $i < 7; $i++) { 
//   $hientai = $thoigian + 24 * 60 * 60 * $i;
//   echo date('w', $hientai) . ": ". date('d/m/Y', $hientai) . "<br>";
// }

// $sql = "select a.id, b.value from pet_". PREFIX ."_profile a inner join pet_". PREFIX ."_config b on a.species = b.id";
// $list = $db->all($sql);

// foreach ($list as $row) {
//   $sql = "update pet_". PREFIX ."_profile set species = '$row[value]' where id = $row[id]";
//   $db->query($sql);
// }

// $sql = "select a.id, b.value from pet_". PREFIX ."_physical a inner join pet_". PREFIX ."_config b on a.species = b.id";
// $list = $db->all($sql);

// foreach ($list as $row) {
//   $sql = "update pet_". PREFIX ."_physical set species = '$row[value]' where id = $row[id]";
//   $db->query($sql);
// }

// $sql = "select a.*, b.name, b.customerid as cid from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_pet b on a.petid = b.id";
// $list = $db->all($sql);

// foreach ($list as $key => $row) {
//   $sql = "update pet_". PREFIX ."_xray set customerid = $row[cid], petname = '$row[name]' where id = $row[id]";
//   $db->query($sql);
// }
$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$config[database]'";
$list = $db->all($sql);

$data = array();
foreach ($list as $row) {
  $tablename = $row['table_name'];
  if (strpos($tablename, "test") !== false) {
    $data[$tablename] = array();
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$config[database]' AND TABLE_NAME = '$tablename'";
    $l = $db->all($sql);
    foreach ($l as $r) {
      $data[$tablename] []= $r['COLUMN_NAME'];
    }
  }
}

echo json_encode($data);die();
