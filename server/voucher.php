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
    $sql = "update pet_". PREFIX ."_voucher set ten = '$data->ten', hansudung = $data->hansudung, hinhanh = '$data->image' where id = $data->id";
  }
  else {
    $sql = "insert into pet_". PREFIX ."_voucher (ten, hansudung, hinhanh) values('$data->ten', $data->hansudung, '$data->image')";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachvoucher();
  return $result;  
}

function khoitaocauhinh() {
  global $data, $db, $result;

  $cauhinh = [
    "voucher" => false,
    "admin" => false,
    "quanly" => false,
    "nhanvien" => false,
  ];

  foreach ($cauhinh as $key => $value) {
    $sql = "select * from pet_". PREFIX ."_config where name = 'voucher-$key'";
    if (!empty($config = $db->fetch($sql))) $cauhinh[$key] = boolval($config["value"]);
  }

  $result['status'] = 1;
  $result['cauhinh'] = $cauhinh;
  return $result;  
}

function capnhatcauhinh() {
  global $data, $db, $result;

  foreach ($data as $key => $value) {
    $sql = "select * from pet_". PREFIX ."_config where name = 'voucher-$key'";
    $val = intval($value);
    if (!empty($config = $db->fetch($sql))) {
      $sql = "update pet_". PREFIX ."_config set value = '$val' where id = $config[id]";
    }
    else {
      $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('voucher', 'voucher-$key', $val, 0)";
    }
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachvoucher();
  return $result;  
}

function xoavoucher() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_voucher set kichhoat = 0 where id = $data->idvoucher";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachvoucher();
  return $result;  
}

function khoitaothongke() {
  global $data, $db, $result;

  $dulieu = [
    "danhan" => ["danhsach" => [], "soluong" => 0, "morong" => 0],
    "dasudung" => ["danhsach" => [], "soluong" => 0, "morong" => 0],
  ];

  $batdau = isodatetotime($data->batdau);
  $ketthuc = isodatetotime($data->ketthuc);

  $sql = "select * from pet_". PREFIX ."_voucher_danhsach a inner join pet_". PREFIX ."_voucher b on a.idvoucher = b.id and (a.thoigian between $batdau and $ketthuc)";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $voucher) {
    $dulieu["danhan"]["soluong"] ++;
    if (empty($dulieu["danhan"]["danhsach"][$voucher["idvoucher"]])) $dulieu["danhan"]["danhsach"][$voucher["idvoucher"]] = ["ten" => $voucher["ten"], "soluong" => 0];
    $dulieu["danhan"]["danhsach"][$voucher["idvoucher"]]["soluong"] ++;
    if ($voucher["trangthai"] == 1) {
      $dulieu["dasudung"]["soluong"] ++;
      if (empty($dulieu["dasudung"]["danhsach"][$voucher["idvoucher"]])) $dulieu["dasudung"]["danhsach"][$voucher["idvoucher"]] = ["ten" => $voucher["ten"], "soluong" => 0];
      $dulieu["dasudung"]["danhsach"][$voucher["idvoucher"]]["soluong"] ++;
    }
  }

  foreach ($dulieu as $songan => $thongtindulieu) {
    $danhsach = [];
    foreach ($thongtindulieu["danhsach"] as $thutu => $thongtin) {
      $danhsach []= $thongtin;
    }
    $dulieu[$songan]["danhsach"] = $danhsach;
  }

  $result['status'] = 1;
  $result['dulieu'] = $dulieu;
  return $result; 
}