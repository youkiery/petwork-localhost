<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  $result['dichvu'] = danhsachdichvu();
  $result['chuyenmon'] = danhsachchuyenmon();
  return $result;
}

function themchuyenmonnhanvien() {
  global $data, $db, $result;
  
  if ($data->loai) $sql = "delete from pet_". PREFIX ."_chuyenmon where idnhanvien = $data->idnhanvien and iddanhmuc = $data->idchuyenmon";
  else $sql = "insert into pet_". PREFIX ."_chuyenmon (idnhanvien, iddanhmuc) values($data->idnhanvien, $data->idchuyenmon)";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function themchuyenmondichvu() {
  global $data, $db, $result;
  
  if ($data->loai) $sql = "delete from pet_". PREFIX ."_chuyenmondichvu where idchuyenmon = $data->idchuyenmon and iddichvu = $data->iddichvu";
  else $sql = "insert into pet_". PREFIX ."_chuyenmondichvu (idchuyenmon, iddichvu) values($data->idchuyenmon, $data->iddichvu)";
  $db->query($sql);

  $result['status'] = 1;
  $result['dichvu'] = danhsachdichvu();
  return $result;
}

function themchuyenmon() {
  global $data, $db, $result;
  
  if ($data->idchuyenmon) $sql = "update pet_". PREFIX ."_danhmuc set tendanhmuc = '$data->chuyenmon' where id = $data->idchuyenmon";
  else $sql = "insert into pet_". PREFIX ."_danhmuc (tendanhmuc, loaidanhmuc, vitri, macdinh, thoigian, kichhoat) values('$data->chuyenmon', 2, 0, 0, 0, 1)";
  $db->query($sql);

  $result['status'] = 1;
  $result['chuyenmon'] = danhsachchuyenmon();
  return $result;
}

function xoachuyenmon() {
  global $data, $db, $result;
  
  $sql = "delete from pet_". PREFIX ."_chuyenmondichvu where idchuyenmon = $data->idchuyenmon";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_chuyenmon where iddanhmuc = $data->idchuyenmon";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_danhmuc where id = $data->idchuyenmon";
  $db->query($sql);

  $result['status'] = 1;
  $result['chuyenmon'] = danhsachchuyenmon();
  return $result;
}

function danhsachnhanvien() {
  global $data, $db, $result;
  
  $sql = "select fullname as tennhanvien, userid from pet_". PREFIX ."_users where active = 1 order by userid";
  $danhsachnhanvien = $db->all($sql);

  foreach ($danhsachnhanvien as $key => $nhanvien) {
    $danhsachnhanvien[$key]["chuyenmon"] = [];
    $chuyenmon = [];
    $sql = "select b.id, b.tendanhmuc from pet_". PREFIX ."_chuyenmon a inner join pet_". PREFIX ."_danhmuc b on a.iddanhmuc = b.id and a.idnhanvien = $nhanvien[userid]";
    $danhsachchuyenmon = $db->all($sql);
    foreach ($danhsachchuyenmon as $chuyenmon) {
      $chuyenmon []= $chuyenmon["tendanhmuc"];
      $danhsachnhanvien[$key]["chuyenmon"][$chuyenmon["id"]] = 1;
    }
    $danhsachnhanvien[$key]["danhsach"] = implode(", ", $chuyenmon);
  }

  return $danhsachnhanvien;
}

function danhsachdichvu() {
  global $data, $db, $result;

  $sql = "select id, tendanhmuc as dichvu from pet_". PREFIX ."_danhmuc where loaidanhmuc < 2 order by id desc";
  $danhsachdichvu = $db->all($sql);

  foreach ($danhsachdichvu as $key => $dichvu) {
    $danhsachdichvu[$key]["chuyenmon"] = [];
    $sql = "select * from pet_". PREFIX ."_chuyenmondichvu where iddichvu = $dichvu[id]";
    $danhsachchuyenmon = $db->all($sql);
    foreach ($danhsachchuyenmon as $chuyenmon) {
      $danhsachdichvu[$key]["chuyenmon"][$chuyenmon["idchuyenmon"]] = 1;
    }
  }

  return $danhsachdichvu;
}

function danhsachchuyenmon() {
  global $data, $db, $result;

  $sql = "select tendanhmuc as chuyenmon, id from pet_". PREFIX ."_danhmuc where loaidanhmuc = 2 order by id desc";
  return $db->all($sql);
}


