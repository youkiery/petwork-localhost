<?php

function khoitao() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhomtin();
  $result['cauhinh'] = cauhinhnhantin();
  return $result;
}

function cauhinhnhantin() {
  global $db;

  $cauhinh = [
    'min' => laycauhinh('min'),
    'max' => laycauhinh('max'),
  ];

  if (empty($cauhinh['min'])) $cauhinh['min'] = 1;
  if (empty($cauhinh['max'])) $cauhinh['max'] = 2;
  return $cauhinh;
}

function laycauhinh($ten, $macdinh = 0) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_config where module = 'nhantin' and name = '$ten'";
  if (empty($cauhinh = $db->fetch($sql))) return $macdinh;
  return $cauhinh['value'];
}

function chitiet() {
  global $db, $data, $result;

  $sql = "select a.id, b.name as khachhang, b.phone as dienthoai from pet_". PREFIX ."_nhomtinlienket a inner join pet_". PREFIX ."_customer b on a.idkhachhang = b.id where a.idnhomtin = $data->id";
  $danhsach = $db->all($sql);
  $danhsachbien = ['[khachhang]' => 'khachhang'];
  $mautin = str_replace('<br>', PHP_EOL, $data->mautin);

  foreach ($danhsach as $thutu => $khachhang) {
    $sql = "select * from pet_". PREFIX ."_nhomtinchitiet where idlienket = $khachhang[id]";
    if (empty($db->fetch($sql))) $danhsach[$thutu]['dagui'] = 0;
    else $danhsach[$thutu]['dagui'] = 1;
    $danhsach[$thutu]['mautin'] = $data->mautin;
    $danhsach[$thutu]['khachhang'] = chuanhoatenkhach($khachhang['khachhang']);
    $danhsach[$thutu]['mautin'] = $mautin;

    foreach ($danhsachbien as $mau => $tenbien) {
      $danhsach[$thutu]['mautin'] = str_replace($mau, $khachhang[$tenbien], $danhsach[$thutu]['mautin']);
    }
  }

  $result['status'] = 1;
  $result['danhsach'] = $danhsach;
  return $result;
}

function danhsachnhomtin() {
  global $db;
  
  $sql = "select * from pet_". PREFIX ."_nhomtin order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $dulieu) {
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $dulieu['thoigian']);
    $sql = "select id from pet_". PREFIX ."_nhomtinlienket where idnhomtin = $dulieu[id]";
    $danhsach[$thutu]['tongso'] = $db->count($sql);
    $sql = "select a.idkhachhang from pet_". PREFIX ."_nhomtinlienket a inner join pet_". PREFIX ."_nhomtinchitiet b on a.id = b.idlienket where a.idnhomtin = $dulieu[id] group by a.idkhachhang";
    $danhsach[$thutu]['dagui'] = $db->count($sql);
  }

  return $danhsach;
}

function themnhomtin() {
  global $data, $db, $result, $_FILES;

  $file = $_FILES['file']['tmp_name'];
  include DIR .'include/PHPExcel/IOFactory.php';
    
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $thoigian = time();
  $sql = "insert pet_". PREFIX ."_nhomtin (tennhom, mautin, thoigian) values('$data->tennhom', '$data->mautin', $thoigian)";
  $idnhomtin = $db->insertid($sql);

  $danhsach = [];
  for ($i = 2; $i < $highestRow; $i++) { 
    $ten = $sheet->getCell("A". $i)->getValue();
    $dienthoai = $sheet->getCell("B". $i)->getValue();
    if (!empty($dienthoai)) {
      $idkhachhang = kiemtrakhachhang($ten, $dienthoai);

      $sql = "insert into pet_". PREFIX ."_nhomtinlienket (idnhomtin, idkhachhang, ) values($idnhomtin, $idkhachhang)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã thêm chiến dịch từ tệp khách hàng';
  $result['danhsach'] = danhsachnhomtin();
  return $result;
}

function kiemtrakhachhang($ten, $dienthoai) {
  global $db;
  $sql = "select * from pet_". PREFIX ."_customer where phone = '$dienthoai'";

  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$ten', '$dienthoai', '')";
    $c['id'] = $db->insertid($sql);
  }
  return $c['id'];
}


function chuanhoatenkhach($tenkhach) {
  $tenkhach = mb_strtoupper($tenkhach);

  return $tenkhach;
}
