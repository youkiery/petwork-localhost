<?php
include './include/PHPExcel/IOFactory.php';
$file = './include/2023-09-total.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);
$sheet = $objPHPExcel->getSheet(0); 

$gioihan = strtotime("2023/08/01") - 1;
$danhsach = [];
for ($i = 2; $i < 8853; $i++) { 
  $dienthoai = $sheet->getCell("D$i")->getValue();
  $khachhang = $sheet->getCell("C$i")->getValue();
  $tienhang = $sheet->getCell("G$i")->getValue();
  $thoigian = strtotime($sheet->getCell("B$i")->getValue());
  if (empty($danhsach[$dienthoai])) $danhsach[$dienthoai] = [
    "dienthoai" => $dienthoai,
    "khachhang" => $khachhang,
    "tienhang" => 0,   
  ];
  $danhsach[$dienthoai]["tienhang"] += $tienhang;
}

$file = './include/2023-09.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);
$sheet = $objPHPExcel->getSheet(0); 

$danhsachkhach = [];

for ($i = 2; $i < 907; $i++) { 
  $dienthoai = $sheet->getCell("D$i")->getValue();
  if (empty($danhsachkhach[$dienthoai])) $danhsachkhach[$dienthoai] = 1;
}

for ($i = 907; $i < 1378; $i++) { 
  $dienthoai = $sheet->getCell("D$i")->getValue();
  if (isset($danhsachkhach[$dienthoai])) $danhsachkhach[$dienthoai] = 0;
}

echo "<pre>";
foreach ($danhsachkhach as $dienthoai => $value) {
  if ($value) {
    $khachhang = $danhsach[$dienthoai];
    echo "$khachhang[khachhang]&#9;$khachhang[dienthoai]&#9;$khachhang[tienhang]&#9;<br>";
  }
}
echo "</pre>";
