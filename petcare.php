<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include './include/PHPExcel/IOFactory.php';
$file = './include/file.xlsx';
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

for ($j = 2; $j < 100; $j++) { 
  $danhsach[]= [
    'nguoiban' => $sheet->getCell("A$j")->getValue(),
    'mahang' => $sheet->getCell("B$j")->getValue(),
    'tenhang' => $sheet->getCell("C$j")->getValue(),
    'soluong' => $sheet->getCell("D$j")->getValue(),
  ];
}

$dulieu = [];

foreach ($danhsach as $row) {
  if (empty($dulieu[$row['nguoiban']])) $dulieu[$row['nguoiban']] = [
    'X Xịt khử mùi Epetcare 50ml' => ['tenhang' => 'SP4197087', 'soluong' => 0],
    'X Xịt khử mùi Epetcare 100ml' => ['tenhang' => 'SP4197348', 'soluong' => 0],
    'X Xịt khử mùi Epetcare 200ml' => ['tenhang' => 'SP4197086', 'soluong' => 0],
    'X Xịt vết thương Epetcare 50ml' => ['tenhang' => 'SP4195733', 'soluong' => 0],
    'X Xịt vết thương Epetcare 100ml' => ['tenhang' => 'SP4197349', 'soluong' => 0],
    'X xịt vết thương Epetcare 200ml' => ['tenhang' => 'SP4195705', 'soluong' => 0],
  ];

  if (!empty($dulieu[$row['nguoiban']][$row['tenhang']])) $dulieu[$row['nguoiban']][$row['tenhang']]['soluong'] += $row['soluong'];
  
}

echo "<pre>";
foreach ($dulieu as $nhanvien => $hanghoa) {
  echo "$nhanvien";
  foreach ($hanghoa as $thongtin) {
    echo "&#9;$thongtin[soluong]";
  }
  echo "<br>";
}
echo "</pre>";
die();