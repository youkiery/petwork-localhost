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

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
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

  $thoigian = time();
  if (empty($data->id)) {
    $sql = "insert pet_". PREFIX ."_nhomtin (tennhom, mautin, thoigian) values('$data->tennhom', '$data->mautin', $thoigian)";
    $idnhomtin = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_nhomtin set tennhom = '$data->tennhom', mautin = '$data->mautin' where id = $data->id";
    $db->query($sql);
    $idnhomtin = $data->id;
  }
  
  $soluong = 0;
  if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    include DIR .'include/PHPExcel/IOFactory.php';
      
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($file);
    $sheet = $objPHPExcel->getSheet(0);
  
    $highestRow = $sheet->getHighestRow(); 
    $highestColumn = $sheet->getHighestColumn();
  
    $danhsach = [];
    for ($i = 2; $i < $highestRow; $i++) { 
      $ten = $sheet->getCell("A". $i)->getValue();
      $dienthoai = $sheet->getCell("B". $i)->getValue();
      if (!empty($dienthoai)) {
        $idkhachhang = kiemtrakhachhang($ten, $dienthoai);
        $sql = "select * from pet_". PREFIX ."_nhomtinlienket where idnhomtin = $idnhomtin and idkhachhang = $idkhachhang";
        if (empty($db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_nhomtinlienket (idnhomtin, idkhachhang) values($idnhomtin, $idkhachhang)";
          $db->query($sql);
          $soluong ++;
        }
      }
    }
  }

  $result['status'] = 1;
  if ($soluong) $result['messenger'] = "Đã thêm $soluong khách hàng";
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

function danhsachnhantin() {
  global $db, $data;

  $sql = "select a.id, b.id as idkhachhang, b.name as khachhang, b.phone as dienthoai from pet_". PREFIX ."_nhomtinlienket a inner join pet_". PREFIX ."_customer b on a.idkhachhang = b.id where a.idnhomtin = $data->id";
  $danhsach = $db->all($sql);
  $danhsachbien = ['[khachhang]' => 'khachhang', '[dienthoai]' => 'dienthoai'];
  $mautin = str_replace('<br>', PHP_EOL, $data->mautin);

  foreach ($danhsach as $thutu => $khachhang) {
    $sql = "select * from pet_". PREFIX ."_nhomtinchitiet where idlienket = $khachhang[id]";
    if (empty($db->fetch($sql))) $danhsach[$thutu]['dagui'] = 0;
    else $danhsach[$thutu]['dagui'] = 1;
    $danhsach[$thutu]['mautin'] = $data->mautin;
    $danhsach[$thutu]['khachhang'] = chuanhoatenkhach($khachhang['khachhang']);
    $danhsach[$thutu]['mautin'] = $mautin;

    foreach ($danhsachbien as $mau => $tenbien) {
      // $mautin = str_replace($tentruong, alias($dulieu[$tenbien]), $mautin);
      $danhsach[$thutu]['mautin'] = str_replace($mau, alias($danhsach[$thutu][$tenbien]), $danhsach[$thutu]['mautin']);
    }
  }

  return $danhsach;
}

function doitenkhach() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_customer set name = '$data->ten' where id = $data->idkhachhang";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
  return $result;
}

function xoanhantin() {
  global $db, $result, $data;

  $sql = "delete from pet_". PREFIX ."_nhomtinlienket where id = $data->idlienket";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_nhomtinchitiet where idlienket = $data->idlienket";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
  return $result;
}

function xacnhanthucong() {
  global $db, $result, $data;

  $thoigian = time();
  $sql = "insert into pet_". PREFIX ."_nhomtinchitiet (idlienket, thoigian) values($data->idlienket, $thoigian)";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
  return $result;
}

function xacnhandagui() {
  global $db, $data, $result;

  $thoigian = time();
  $sql = "insert into pet_". PREFIX ."_nhomtinchitiet (idlienket, thoigian) values($data->idlienket, $thoigian)";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function timkiem() {
  global $db, $data, $result;

  $sql = "select b.idnhomtin, a.name as khachhang, a.phone as dienthoai, a.id from pet_". PREFIX ."_customer a inner join pet_". PREFIX ."_nhomtinlienket b on a.id = b.idkhachhang and (a.name like '%$data->tukhoa%' or a.phone like '%$data->tukhoa%') group by a.id";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $khachhang) {
    $sql = "select * from pet_". PREFIX ."_nhomtin a inner join pet_". PREFIX ."_nhomtinlienket b on a.id = b.idnhomtin where idkhachhang = $khachhang[id]";
    $danhsach[$thutu]['nhomtin'] = implode(', ', $db->arr($sql, 'tennhom'));
  }

  $result['status'] = 1;
  $result['danhsach'] = $danhsach;
  return $result;
}

function xoanhomtin() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_nhomtinlienket where idnhomtin = $data->id";
  $danhsachlienket = $db->all($sql);

  foreach ($danhsachlienket as $lienket) {
    $sql = "delete from pet_". PREFIX ."_nhomtinchitiet where idlienket = $lienket[id]";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_nhomtinlienket where idnhomtin = $data->id";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_nhomtin where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhomtin();
  return $result;
}

function xacnhandanhsachloi() {
  global $db, $data, $result;

  $thoigian = time();
  foreach ($data->danhsach as $dulieu) {
    $sql = "select * from pet_". PREFIX ."_nhomtinchitiet where idlienket = $dulieu->idlienket";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_nhomtinchitiet (idlienket, thoigian) values($data->idlienket, $thoigian)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  return $result;
}
