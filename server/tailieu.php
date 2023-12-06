<?php
function khoitao() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  $result['danhsach'] = danhsachtailieu();
  return $result;
}

function danhsachtailieu() {
  global $db, $data, $result;

  $timkiem = $data->timkiem;
  $sql = "select * from pet_danhmuc order by tendanhmuc";
  $danhsachdanhmuc = $db->all($sql);

  $danhsachdanhmuc[]= ["id" => 0, "tendanhmuc" => "Chưa phân loại"];

  foreach ($danhsachdanhmuc as $thutu => $danhmuc) {
    $sql = "select * from pet_tailieu where tentailieu like '%$timkiem->tukhoa%' and iddanhmuc = $danhmuc[id] order by id desc";
    $danhsachdanhmuc[$thutu]["danhsach"] = $db->all($sql);
  }
  return $danhsachdanhmuc;
}

function danhsachdanhmuc() {
  global $db, $data, $result;

  $sql = "select * from pet_danhmuc order by id desc";
  return $db->all($sql);
}

function capnhattailieu() {
  global $db, $data, $result;

  if (!empty($data->id)) $sql = "update pet_tailieu set tentailieu = '$data->tentailieu', gioithieu = '$data->gioithieu', iddanhmuc = $data->iddanhmuc, link = '$data->link' where id = $data->id";
  else $sql = "insert into pet_tailieu (tentailieu, gioithieu, iddanhmuc, link) values('$data->tentailieu', '$data->gioithieu', $data->iddanhmuc, '$data->link')";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachtailieu();
  return $result;
}

function capnhatdanhmuc() {
  global $db, $data, $result;

  if (!empty($data->id)) $sql = "update pet_danhmuc set tendanhmuc = '$data->tendanhmuc' where id = $data->id";
  else $sql = "insert into pet_danhmuc (tendanhmuc) values('$data->tendanhmuc')";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  return $result;
}

function xoatailieu() {
  global $db, $data, $result;

  $sql = "delete from pet_tailieu where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachtailieu();
  return $result;
}

function xoadanhmuc() {
  global $db, $data, $result;

  $sql = "delete from pet_danhmuc where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  return $result;
}

