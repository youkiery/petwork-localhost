<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachthietbi();
  return $result;
}

function danhsachthietbi() {
  global $data, $db;
  
  $sql = "select * from pet_". PREFIX ."_thietbi order by id desc";
  $danhsach = $db->all($sql);
  foreach ($danhsach as $key => $thietbi) {
    $danhsach[$key]['alias'] = alias($thietbi['ten']);
  }

  return $danhsach;
}

function them() {
  global $data, $db, $result;

  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX. "_thietbi (ten, hinhanh, congdung, link) values ('$data->ten', '$data->hinhanh', '$data->congdung', '$data->link')";
    $db->query($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_thietbi set ten = '$data->ten', hinhanh = '$data->hinhanh', congdung = '$data->congdung', link = '$data->link' where id = $data->id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachthietbi();
  return $result;
}

function xoathietbi() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_thietbi where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachthietbi();
  return $result;
}