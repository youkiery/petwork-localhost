<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include './include/PHPExcel/IOFactory.php';
$file = './include/file2.xlsx';
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
$row = $sheet->getHighestRow(); 

for ($j = 2; $j < $row; $j++) { 
  $danhsach[]= [
    'nguoiban' => $sheet->getCell("A$j")->getValue(),
    'soluong' => $sheet->getCell("D$j")->getValue(),
  ];
}

$dulieu = [];

foreach ($danhsach as $row) {
  if (empty($dulieu[$row['nguoiban']])) $dulieu[$row['nguoiban']] = 0;
  $dulieu[$row['nguoiban']] += $row['soluong'];
}

echo "<pre>";
foreach ($dulieu as $nhanvien => $hanghoa) {
  echo "$nhanvien&#9;$hanghoa";
  echo "<br>";
}
echo "</pre>";
die();