<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include './include/PHPExcel/IOFactory.php';
$file = './include/danhsach.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);

$danhsach = [];
$sheet = $objPHPExcel->getSheet(0); 

for ($j = 2; $j < 1629; $j++) { 
  $danhsach[]= [
    'thoigian' => chuyenthoigian($sheet->getCell("A$j")->getValue()),
    'khachhang' => $sheet->getCell("D$j")->getValue(),
    'dienthoai' => $sheet->getCell("E$j")->getValue(),
    'nguoilam' => $sheet->getCell("F$j")->getValue(),
    'solan' => 1
  ];
}

$file = './include/danhsach2.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);
$sheet = $objPHPExcel->getSheet(0); 

for ($j = 2; $j < 1629; $j++) { 
  $danhsach[]= [
    'thoigian' => chuyenthoigian($sheet->getCell("A$j")->getValue()),
    'khachhang' => $sheet->getCell("D$j")->getValue(),
    'dienthoai' => $sheet->getCell("E$j")->getValue(),
    'nguoilam' => $sheet->getCell("F$j")->getValue(),
    'solan' => 1
  ];
}

usort($danhsach, "cmp");
$dulieu = [];
foreach ($danhsach as $row) {
  if (empty($dulieu[$row["dienthoai"]])) $dulieu[$row["dienthoai"]] = $row;
  else $dulieu[$row["dienthoai"]]["solan"] ++;
}

echo "<pre>";
foreach ($dulieu as $dienthoai => $thongtin) {
  echo date("d/m/Y", $thongtin["thoigian"]) . "&#9;$thongtin[khachhang]&#9;$thongtin[dienthoai]&#9;$thongtin[nguoilam]&#9;$thongtin[solan]<br>";
}
echo "</pre>";
die();

function chuyenthoigian($serial) {
  $utc_days  = floor($serial - 25569);
  $utc_value = $utc_days * 86400;
  return $utc_value;
}

function cmp($a, $b) {
  return $a["thoigian"] < $b["thoigian"];
}