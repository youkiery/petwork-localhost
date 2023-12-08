<?php
function khoitao() {
  global $data, $db, $result;
    
  $result['status'] = 1;
  $result['danhsach'] = danhsachvoucher();
  return $result;
}

function tracuu() {
  global $data, $db, $result;
    
  $result['status'] = 1;
  $result['danhsach'] = danhsachtracuu();
  return $result;
}

function hoanthanh() {
  global $data, $db, $result;
    
  $sql = "update pet_". PREFIX ."_voucher_danhsach set trangthai = 1 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachtracuu();
  return $result;
}

function danhsachtracuu() {
  global $data, $db, $result;

  $hansudung = strtotime(date("Y/m/d")) + 60 * 60 * 24;
  $sql = "select a.ten, b.*, c.name as khachhang, c.phone as dienthoai from pet_". PREFIX ."_voucher a inner join pet_". PREFIX ."_voucher_danhsach b on a.id = b.idvoucher and b.hansudung >= $hansudung and b.trangthai = 0 inner join pet_". PREFIX ."_customer c on b.idkhachhang = c.id and c.phone like '%$data->tukhoa%' order by b.id";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $voucher) {
    $danhsach[$thutu]["thoigian"] = date("d/m/Y", $voucher["thoigian"]);
    $danhsach[$thutu]["hansudung"] = date("d/m/Y", $voucher["hansudung"]);
  }
  return $danhsach;
}

function danhsachvoucher() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_voucher where kichhoat = 1 order by id desc";
  $danhsach = $db->all($sql);
  $thoigian = strtotime(date("Y/m/d")) + 60 * 60 * 24;

  foreach ($danhsach as $thutu => $voucher) {
    $sql = "select id from pet_". PREFIX ."_voucher_danhsach where idvoucher = $voucher[id] and trangthai = 0 and hansudung >= $thoigian";
    $conhan = $db->count($sql);

    $sql = "select id from pet_". PREFIX ."_voucher_danhsach where idvoucher = $voucher[id] and trangthai = 0 and hansudung < $thoigian";
    $hethan = $db->count($sql);

    $sql = "select id from pet_". PREFIX ."_voucher_danhsach where idvoucher = $voucher[id]";
    $tong = $db->count($sql);

    $danhsach[$thutu]["conhan"] = $conhan;
    $danhsach[$thutu]["hethan"] = $hethan;
    $danhsach[$thutu]["tong"] = $tong;
  }
  return $danhsach;
}

function capnhatvoucher() {
  global $data, $db, $result;

  if ($data->id) {
    $sql = "update pet_". PREFIX ."_voucher set ten = '$data->ten', hansudung = $data->hansudung where id = $data->id";
  }
  else {
    $sql = "insert into pet_". PREFIX ."_voucher (ten, hansudung) values('$data->ten', $data->hansudung)";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachvoucher();
  return $result;  
}

function xoavoucher() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_voucher set kichhoat = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachvoucher();
  return $result;  
}
