<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

include './include/PHPExcel/IOFactory.php';
$file = './include/kien.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);

// $x = array();
// $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
// foreach ($xr as $key => $value) {
//   $x[$value] = $key;
// }

$danhsach = [];
for ($i = 0; $i < 4; $i++) { 
  $sheet = $objPHPExcel->getSheet($i); 
  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();
  $danhsach[$i] = [];

  for ($j = 2; $j < $highestRow; $j++) { 
    $danhsach[$i] []= [
      'hoadon' => $sheet->getCell("A$j")->getValue(),
      'khachhang' => $sheet->getCell("B$j")->getValue(),
      'dienthoai' => $sheet->getCell("C$j")->getValue(),
      'tienhang' => $sheet->getCell("D$j")->getValue(),
      'mahang' => $sheet->getCell("E$j")->getValue(),
      'tenhang' => $sheet->getCell("F$j")->getValue(),
      'soluong' => $sheet->getCell("G$j")->getValue(),
    ];
  }
}


$dulieu = [];
$danhsachhoadon = [];
for ($i = 1; $i < 4; $i++) {
  $dulieu[$i] = [
    'hoadon' => [],
    'khachhang' => []
  ];

  foreach ($danhsach[$i] as $dulieukhach) {
    if (empty($dulieu[$i]['hoadon'][$dulieukhach['hoadon']])) {
      $danhsachhoadon[$dulieukhach['hoadon']] = 1;
      $dulieu[$i]['hoadon'][$dulieukhach['hoadon']] = 1;

      if (empty($dulieu[$i]['khachhang'][$dulieukhach['dienthoai']])) $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']] = [
        'khachhang' => $dulieukhach['khachhang'],
        'tienhang' => 0,
        'solan' => 0,
        'soluong' => 0,
      ];
      $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['tienhang'] += $dulieukhach['tienhang'];
      $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['soluong'] += $dulieukhach['soluong'];
      $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['solan'] ++;
    }
  }
}

$i = 0;
$dulieu[$i] = [
  'hoadon' => [],
  'khachhang' => []
];

foreach ($danhsach[$i] as $dulieukhach) {
  // hóa đơn chưa thêm
  // hóa đơn không có trong danh sách hoa đơn
  if (empty($dulieu[$i]['hoadon'][$dulieukhach['hoadon']]) && empty($danhsachhoadon[$dulieukhach['hoadon']])) {
    $danhsachhoadon[$dulieukhach['hoadon']] = 1;
    $dulieu[$i]['hoadon'][$dulieukhach['hoadon']] = 1;
    if (empty($dulieu[$i]['khachhang'][$dulieukhach['dienthoai']])) $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']] = [
      'khachhang' => $dulieukhach['khachhang'],
      'tienhang' => 0,
      'solan' => 0,
      'soluong' => 0,
    ];
    
    $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['tienhang'] += $dulieukhach['tienhang'];
    $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['soluong'] += $dulieukhach['soluong'];
    $dulieu[$i]['khachhang'][$dulieukhach['dienthoai']]['solan'] ++;
  }
}

$file = './include/file-mau.xlsx';
$fileType = PHPExcel_IOFactory::identify($file);
$objPHPExcel = PHPExcel_IOFactory::load($file);

foreach ($dulieu as $thutu => $dulieukhach) {
  $thutuy = 2;
  foreach ($dulieukhach['khachhang'] as $dienthoai => $value) {
    $objPHPExcel
    ->setActiveSheetIndex($thutu)
    ->setCellValue("A" . $thutuy, $value['khachhang'])
    ->setCellValue("B" . $thutuy, $dienthoai)
    ->setCellValue("C" . $thutuy, $value['tienhang'])
    ->setCellValue("D" . $thutuy, $value['solan'])
    ->setCellValue("E" . $thutuy, $value['soluong']);
    $thutuy ++;
  }
}

$time = time(); 
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
$objWriter->save('./include/file-mau-'. $time .'.xlsx');
$objPHPExcel->disconnectWorksheets();
unset($objWriter, $objPHPExcel);

die();
// sheet 1: tổng dùng để đối chiếu
// sheet 2: spa
// sheet 2: điều trị
// sheet 2: vaccine
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
