<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

include './include/PHPExcel/IOFactory.php';
$file = './include/kien.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);

$x = array();
$xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
foreach ($xr as $key => $value) {
  $x[$value] = $key;
}

$sheet = $objPHPExcel->getSheet(0); 
$highestRow = $sheet->getHighestRow(); 
$highestColumn = $sheet->getHighestColumn();

$dulieu = [];
// dulieu: { khachhang: { ten, dienthoai, doanhthu, spa, dieutri, hoadon: [] } }
for ($i = 2; $i < $highestRow; $i++) { 
  $hoadon = $sheet->getCell("A$i")->getValue();
  $ten = $sheet->getCell("B$i")->getValue();
  $dienthoai = $sheet->getCell("C$i")->getValue();
  $tienhang = $sheet->getCell("D$i")->getValue();
  $mahang = $sheet->getCell("E$i")->getValue();
  $tenhang = $sheet->getCell("F$i")->getValue();
  $soluong = $sheet->getCell("G$i")->getValue();
  if (empty($dulieu[$dienthoai])) $dulieu[$dienthoai] = [
    'ten' => $ten,
    'dienthoai' => $dienthoai,
    'doanhthu' => 0,
    'spa' => 0,
    'dieutri' => 0,
    'hoadon' => []
  ];
  if (empty($dulieu[$dienthoai]['hoadon'][$hoadon])) {
    $dulieu[$dienthoai]['hoadon'][$hoadon] = 1;
    $dulieu[$dienthoai]['doanhthu'] += $tienhang;
  }
  if (mb_strpos($tenhang, 'Công tắm') !== false) $dulieu[$dienthoai]['spa'] += $soluong;
  if (mb_strpos($mahang, 'BVTK') !== false) $dulieu[$dienthoai]['dieutri'] += $soluong;
}
echo "<pre>";
foreach ($dulieu as $dienthoai => $data) {
  foreach ($data as $value) {
    if (!is_array($value)) echo "$value&#9;";
  }
  echo "<br>";
}
echo "</pre>";

die();

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

for ($i = 0; $i < 7; $i++) { 
  $date = time() + $i * 60 * 60 * 24;
  echo date('w', $date) . ': '. date('d/m/Y', $date) . '<br>';
}
die();

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
include_once(ROOTDIR . '/include/config.php');
include_once(ROOTDIR . '/server/db.php');
include_once(ROOTDIR . '/server/global.php');
$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

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
  $data[$tablename] = array();
  $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$config[database]' AND TABLE_NAME = '$tablename'";
  $l = $db->all($sql);
  foreach ($l as $r) {
    $data[$tablename] []= $r['COLUMN_NAME'];
  }
}

echo json_encode($data);die();
