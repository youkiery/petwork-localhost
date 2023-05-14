<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachthuchi();
  return $result;
}

function danhsachthuchi() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_luong_taichinh where (thoigian between $dauthang and $cuoithang) order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $taichinh) {
    $sql = "select * from pet_". PREFIX ."_luong_taichinh_hinhanh where idtaichinh = $taichinh[id] order by id desc";
    $danhsach[$thutu]['hinhanh'] = $db->arr($sql, 'hinhanh');
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $taichinh['thoigian']);
  }
  return $danhsach;
}

function xoathu() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_luong_taichinh where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachthuchi();
  return $result;
}

function themthu() {
  global $db, $data, $result;

  $hinhanh = $data->hinhanh;
  $thoigian = isodatetotime($data->thoigian);
  $tienthu = purenumber($data->tienthu);
  $tienchi = purenumber($data->tienchi);

  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX ."_luong_taichinh (tienthu, tienchi, thoigian) values($tienthu, $tienchi, $thoigian)";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_luong_taichinh set tienthu = $tienthu, tienchi = $tienchi, thoigian = $thoigian where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_luong_taichinh_hinhanh where idtaichinh = $data->id";
  $db->query($sql);

  foreach ($hinhanh as $url) {
    $sql = "insert into pet_". PREFIX ."_luong_taichinh_hinhanh (idtaichinh, hinhanh) values($data->id, '$url')";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachthuchi();
  return $result;
}
