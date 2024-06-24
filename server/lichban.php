<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachlichban();
  return $result;
}

function xoa() {
  global $data, $db, $result;
  
  $sql = "delete from pet_". PREFIX ."_lichban where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachlichban();
  return $result;
}

function them() {
  global $data, $db, $result;
  
  $batdau = isodatetotimev2($data->batdau);
  $ketthuc = isodatetotimev2($data->ketthuc);
  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX ."_lichban (idnhanvien, lydo, batdau, ketthuc) values($data->idnguoidung, '$data->lydo', $batdau, $ketthuc)";
  }
  else {
    $sql = "update pet_". PREFIX ."_lichban set lydo = '$data->lydo', batdau = $batdau, ketthuc = $ketthuc where id = $data->id";
  }
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachlichban();
  return $result;
}

function danhsachlichban() {
  global $data, $db, $result;
  
  $daungay = strtotime(date("Y/m/d"));
  $sql = "select * from pet_". PREFIX ."_lichban where idnhanvien = $data->idnguoidung and (batdau >= $daungay or ketthuc >= $daungay)";
  $danhsach = $db->all($sql);

  $sql = "select 0 as id, 'Khách đặt' as lydo, ngaydat as batdau, (ngaydat + thoigiandukien) as ketthuc  from pet_". PREFIX ."_datlich where idnhanvien = $data->idnguoidung and (ngaydat >= $daungay)";
  $danhsach = array_merge($danhsach, $db->all($sql));

  usort($danhsach, "ngaytuthaptoicao");
  $ds = [];

  foreach ($danhsach as $key => $value) {
    $ngay = date("d/m", $value["batdau"]);
    if (empty($ds[$ngay])) $ds[$ngay] = [];
    $ds[$ngay] []= [
      "id" => $value["id"],
      "lydo" => $value["lydo"],
      "batdau" => date("d/m H:i", $value["batdau"]),
      "ketthuc" => date("d/m H:i", $value["ketthuc"]),
      "batdauiso" => date("c", $value["batdau"]),
      "ketthuciso" => date("c", $value["ketthuc"]),
    ];
  }
  $danhsach = [];

  foreach ($ds as $ngay => $danhsachngay) {
    $danhsach []= [
      "ngay" => $ngay,
      "danhsach" => $danhsachngay,
    ];
  }

  return $danhsach;
}

function ngaytuthaptoicao($a, $b) {
  return $a["batdau"] > $b["batdau"];
}
