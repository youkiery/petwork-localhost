<?php

// post

function khoitao() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['dulieu'] = dulieuvattu();
  $result['danhsachtang'] = danhsachtang();
  return $result;
}

function xoavattu() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_vattu where id = $data->idvattu";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['dulieu'] = dulieuvattu();
  return $result;
}

function themtang() {
  global $db, $data, $result;

  $sql = "insert into pet_". PREFIX ."_vattutang (ten) values('$data->ten')";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsachtang'] = danhsachtang();
  return $result;
}

function capnhattang() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_vattutang set ten = '$data->ten' where id = $data->idtang";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsachtang'] = danhsachtang();
  return $result;
}

function xoatang() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_vattutang where id = $data->idtang";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsachtang'] = danhsachtang();
  return $result;
}

function themvattu() {
  global $db, $data, $result;

  $dulieu = $data->dulieu;
  $dulieu->giatri = purenumber($dulieu->giatri);
  $dulieu->soluong = purenumber($dulieu->soluong);
  $dulieu->thoigian = isodatetotime($dulieu->thoigian);

  if ($dulieu->id) {
    $sql = "update pet_". PREFIX ."_vattu set ten = '$dulieu->ten', donvi = '$dulieu->donvi', soluong = $dulieu->soluong, thoigian = $dulieu->thoigian, giatri = $dulieu->giatri, ghichu = '$dulieu->ghichu' where id = $dulieu->id";
    $db->query($sql);

    // thêm cái không có
    // xóa cái không tồn tại
    $danhsach = [];
    foreach ($dulieu->thuoctang as $idtang => $giatri) {
      $danhsach []= $idtang;
      $sql = "select * from pet_". PREFIX ."_vattunoitang where idvattu = $dulieu->id and idtang = $idtang";
      if (empty($db->fetch($sql))) {
        $sql = "insert into pet_". PREFIX ."_vattunoitang (idvattu, idtang) values($dulieu->id, $idtang)";
        $db->query($sql);
      }
    }
    if (!count($danhsach)) $sql = "delete from pet_". PREFIX ."_vattunoitang where idvattu = $dulieu->id";
    else $sql = "delete from pet_". PREFIX ."_vattunoitang where idvattu = $dulieu->id and idtang not in (". implode(', ', $danhsach) .")";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_vattu (ten, donvi, soluong, thoigian, giatri, ghichu) values('$dulieu->ten', '$dulieu->donvi', $dulieu->soluong, $dulieu->thoigian, $dulieu->giatri, '$dulieu->ghichu')";
    $idvattu = $db->insertid($sql);
    foreach ($dulieu->thuoctang as $idtang => $giatri) {
      $sql = "insert into pet_". PREFIX ."_vattunoitang (idvattu, idtang) values($idvattu, $idtang)";
      $db->query($sql);
    }
  }
  
  $result['status'] = 1;
  $result['dulieu'] = dulieuvattu();
  return $result;
}

// chức năng

function dulieuvattu() {
  global $db, $data, $result;
  
  $dulieu = [
    'tongsoluong' => 0,
    'tongvattu' => 0,
    'tonggiatri' => 0,
    'danhsach' => []
  ];

  // nếu có tầng thì lọc
  // nếu không để toàn bô
  if (count($data->loctang)) {
    $xtra = "where id in (select idvattu as id from pet_". PREFIX ."_vattunoitang where idtang in (". implode(', ', $data->loctang) ."))";
  
    $sql = "select * from pet_". PREFIX ."_vattutang where id in (". implode(', ', $data->loctang) .") order by ten asc";
  }
  else {
    $xtra = "";
    $sql = "select * from pet_". PREFIX ."_vattutang order by ten asc";
  }
  $danhsachtang = $db->all($sql);

  $sql = "select * from pet_". PREFIX . "_vattu $xtra";
  $danhsachvattu = $db->all($sql);

  foreach ($danhsachvattu as $thutu => $vattu) {
    $dulieu['tongvattu'] ++;
    $dulieu['tongsoluong'] += $vattu['soluong'];
    $dulieu['tonggiatri'] += $vattu['giatri'];
    $danhsachvattu[$thutu]['thuoctang'] = danhsachlienkettang($vattu['id']);
    $danhsachvattu[$thutu]['tang'] = dulieulienkettang($vattu['id']);
    $danhsachvattu[$thutu]['giatri'] = number_format($vattu['giatri']);
    $danhsachvattu[$thutu]['thoigian'] = date('d/m/Y', $vattu['thoigian']);
  }
  $dulieu['danhsach'] = $danhsachvattu;

  $dulieu['tongsoluong'] = number_format($dulieu['tongsoluong']);
  $dulieu['tongvattu'] = number_format($dulieu['tongvattu']);
  $dulieu['tonggiatri'] = number_format($dulieu['tonggiatri']);
  
  return $dulieu;
}

function danhsachtang() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_vattutang order by ten asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $tang) {
    $sql = "select b.* from pet_". PREFIX ."_vattunoitang a inner join pet_". PREFIX ."_vattu b on a.idvattu = b.id and a.idtang = $tang[id]";
    $danhsachvattu = $db->all($sql);
    $danhsach[$thutu]['vattu'] = 0;
    $danhsach[$thutu]['soluong'] = 0;
    $danhsach[$thutu]['giatri'] = 0;
    foreach ($danhsachvattu as $vattu) {
      $danhsach[$thutu]['vattu'] ++;
      $danhsach[$thutu]['soluong'] += $vattu['soluong'];
      $danhsach[$thutu]['giatri'] += $vattu['giatri'];
    }
    $danhsach[$thutu]['vattu'] = number_format($danhsach[$thutu]['vattu']);
    $danhsach[$thutu]['soluong'] = number_format($danhsach[$thutu]['soluong']);
    $danhsach[$thutu]['giatri'] = number_format($danhsach[$thutu]['giatri']);
  }
  return $danhsach;
}

function danhsachlienkettang($idvattu) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vattunoitang where idvattu = $idvattu";
  $danhsachketnoi = $db->all($sql);
  $danhsach = [];
  foreach ($danhsachketnoi as $ketnoi) {
    $sql = "select * from pet_". PREFIX ."_vattutang where id = $ketnoi[idtang]";
    $tang = $db->fetch($sql);
    $danhsach []= $tang['ten'];
  }
  if (count($danhsach)) return implode(', ', $danhsach);
  else return 'Không có';
}

function dulieulienkettang($idvattu) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vattunoitang where idvattu = $idvattu";
  $danhsachketnoi = $db->all($sql);
  $danhsach = [];
  foreach ($danhsachketnoi as $ketnoi) {
    $sql = "select * from pet_". PREFIX ."_vattutang where id = $ketnoi[idtang]";
    $tang = $db->fetch($sql);
    $danhsach[$tang['id']] = 1;
  }
  return $danhsach;
}
