<?php
include './include/PHPExcel/IOFactory.php';
$file = './include/c.xlsx';
$inputFileType = PHPExcel_IOFactory::identify($file);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($file);
$sheet = $objPHPExcel->getSheet(0); 

$gioihan = strtotime("2023/08/01") - 1;
$danhsach1 = [];
$danhsach2 = [];
$danhsach = [];
for ($i = 2; $i < 2075; $i++) { 
	$danhsach1 [$sheet->getCell("B$i")->getValue()] = $sheet->getCell("A$i")->getValue();
}
for ($i = 2; $i < 1267; $i++) { 
	$danhsach2 [$sheet->getCell("D$i")->getValue()] = $sheet->getCell("C$i")->getValue();
}

$daden = 0;
$chuaden = 0;

foreach($danhsach1 as $dienthoai => $ten) {
	$danhsach []= [
		'dienthoai' => $dienthoai,
		'khachhang' => $ten,
	];
}

$danhsachkhachden = [];
$danhsachkhachkhongden = [];

foreach($danhsach1 as $dienthoai => $ten) {
	$khachhang = [
		'dienthoai' => $dienthoai,
		'khachhang' => $ten
	];

	if (isset($danhsach2[$dienthoai])) $danhsachkhachden []= $khachhang;
	else $danhsachkhachkhongden []= $khachhang;
}

echo "<pre>";
foreach ($danhsachkhachkhongden as $khachhang) {
    echo "$khachhang[khachhang]&#9;\"$khachhang[dienthoai]\"<br>";
}
echo "</pre>";
