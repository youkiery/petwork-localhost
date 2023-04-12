<?php
include './include/PHPExcel/IOFactory.php';
$file = './include/kien.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);
$sheet = $objPHPExcel->getSheet(0); 

$danhsach = [];
for ($i = 2; $i < 155; $i++) { 
  $dienthoai = $sheet->getCell("D$i")->getValue();
  $khachhang = $sheet->getCell("C$i")->getValue();
  $tenhang = $sheet->getCell("F$i")->getValue();
  $soluong = intval($sheet->getCell("G$i")->getValue());
  if (empty($danhsach[$dienthoai])) $danhsach[$dienthoai] = [
    'dienthoai' => $dienthoai,
    'khachhang' => $khachhang,
    'danhsach' => [
      'AVT catsrang 500g(gói)' => 0,
      'HL Thức ăn mèo Catsrang 5kg' => 0,
      'Thức ăn mèo Catsrang Kitten 400g' => 0,
    ]
  ];
  $danhsach[$dienthoai]['danhsach'][$tenhang] += $soluong;
}

echo "<pre>";
foreach ($danhsach as $khachhang) {
  echo "$khachhang[khachhang]&#9;$khachhang[dienthoai]&#9;";
  foreach ($khachhang['danhsach'] as $tenhang => $soluong) {
    echo "$soluong&#9;";
  }
  echo "<br>";
}
echo "</pre>";
