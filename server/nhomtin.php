<?php

function khoitao() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhomtin();
  $result['cauhinh'] = cauhinhnhantin();
  $result['cauhinhloai'] = cauhinhloai();
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

function cauhinhloai() {
  global $db;

  $sqlvaccine = "select id, name from pet_". PREFIX ."_vaccineloai where active = 1";
  $sqlusg = "select id, name from pet_". PREFIX ."_config where module = 'usg'";
  $sqlspa = "select id, name from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $sqltreat = "select id, name from pet_". PREFIX ."_dieutricong where active = 1 order by id desc";

  $cauhinh = [
    'vaccine' => $db->all($sqlvaccine),
    'usg' => $db->all($sqlusg),
    'treat' => $db->all($sqlspa),
    'spa' => $db->all($sqltreat),
  ];

  foreach ($cauhinh as $loai => $danhsach) {
    foreach ($danhsach as $thutu => $dulieu) {
      $cauhinh[$loai][$thutu]['checker'] = false;
    }
  }

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

function laydulieunhomtin() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['dulieu'] = dulieunhomtin();
  return $result;
}

function dulieunhomtin() {
  global $db, $data;

  $sqlvaccine = "select id, name from pet_". PREFIX ."_vaccineloai where active = 1";
  $sqlusg = "select id, name from pet_". PREFIX ."_config where module = 'usg'";
  $sqlspa = "select id, name from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $sqltreat = "select id, name from pet_". PREFIX ."_dieutricong where active = 1 order by id desc";

  $dulieu = [
    'vaccine' => $db->all($sqlvaccine),
    'usg' => $db->all($sqlusg),
    'treat' => $db->all($sqlspa),
    'spa' => $db->all($sqltreat),
  ];

  $rev = ['vaccine' => 0, 'usg' => 1, 'treat' => 2, 'spa' => 3];

  foreach ($dulieu as $loai => $danhsach) {
    foreach ($danhsach as $thutu => $thongtin) {
      $sql = "select * from pet_". PREFIX . "_nhomtinloai where idnhomtin = $data->id and loai = $rev[$loai] and idloai = $thongtin[id]";
      if (empty($db->fetch($sql))) $dulieu[$loai][$thutu]['checker'] = false;
      else $dulieu[$loai][$thutu]['checker'] = true;
    }
  }

  return $dulieu;
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

  if (empty($data->dulieu[0])) $data->dulieu[0] = [];
  if (empty($data->dulieu[1])) $data->dulieu[1] = [];
  if (empty($data->dulieu[2])) $data->dulieu[2] = [];
  if (empty($data->dulieu[3])) $data->dulieu[3] = [];

  $xtra = [];
  foreach ($data->dulieu as $loai => $danhsachid) {
    foreach ($danhsachid as $idloai) {
      // tạo query xóa những cái cùng id nhưng không cùng loại 
      $xtra []= "(loai = $loai and idloai = $idloai)";
      // select, nếu chưa có thì thêm
      $sql = "select * from pet_". PREFIX ."_nhomtinloai where idnhomtin = $idnhomtin and loai = $loai and idloai = $idloai";
      if (empty($db->fetch($sql))) {
        $sql = "insert into pet_". PREFIX ."_nhomtinloai (idnhomtin, loai, idloai) values($idnhomtin, $loai, $idloai)";
        $db->query($sql);
      }
    }
  }
  if (empty($xtra)) $xtra = "and 1";
  else $xtra = "and id not in (select id from pet_". PREFIX ."_nhomtinloai where ". implode(' or ', $xtra) .")";
  
  $sql = "delete from pet_". PREFIX ."_nhomtinloai where idnhomtin = $idnhomtin $xtra";
  $db->query($sql);

  $soluong = 0;
  if (isset($_FILES['file'])) {
    if (file_exists($_FILES['file']['tmp_name'])) {
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
  return mb_strtoupper($tenkhach);
}

function danhsachnhantin() {
  global $db, $data;

  $sql = "select a.id, b.id as idkhachhang, b.name as khachhang, b.phone as dienthoai from pet_". PREFIX ."_nhomtinlienket a inner join pet_". PREFIX ."_customer b on a.idkhachhang = b.id where a.idnhomtin = $data->id";
  $danhsach = $db->all($sql);
  $danhsachbien = ['[idkhachhang]' => 'idkhachhang', '[khachhang]' => 'khachhang', '[dienthoai]' => 'dienthoai'];

  $sql = "select * from pet_". PREFIX ."_nhomtin where id = $data->id";
  $nhomtin = $db->fetch($sql);
  
  $mautin = str_replace('<br>', PHP_EOL, $nhomtin['mautin']);

  foreach ($danhsach as $thutu => $khachhang) {
    $sql = "select * from pet_". PREFIX ."_nhomtinchitiet where idlienket = $khachhang[id]";
    if (empty($db->fetch($sql))) $danhsach[$thutu]['dagui'] = 0;
    else $danhsach[$thutu]['dagui'] = 1;
    
    $danhsach[$thutu]['khachhang'] = mb_strtoupper($khachhang['khachhang']);
    $danhsach[$thutu]['mautin'] = $mautin;

    foreach ($danhsachbien as $mau => $tenbien) {
      // $mautin = str_replace($tentruong, alias($dulieu[$tenbien]), $mautin);
      $danhsach[$thutu]['mautin'] = str_replace($mau, fullalias($danhsach[$thutu][$tenbien]), $danhsach[$thutu]['mautin']);
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
