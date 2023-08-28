<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include './include/PHPExcel/IOFactory.php';
$file = './include/khachhang.xlsx';
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
$sheet = $objPHPExcel->getSheet(0); 

for ($j = 2; $j < 912; $j++) { 
  $danhsach[$sheet->getCell("A$j")->getValue()] = 1;
}

for ($i = 2; $i < 972; $i++) { 
  $val = $sheet->getCell("B$i")->getValue();
  if (isset($danhsach[$val])) $danhsach[$val] = 0;
}

// echo json_encode($danhsach);
// die();

$list = [];
foreach ($danhsach as $dienthoai => $value) {
  if ($value) $list []= $dienthoai;
}

echo "<pre>";
foreach ($list as $dienthoai) {
  echo "$dienthoai <br>";
}
echo "</pre>";
die();