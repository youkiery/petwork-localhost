<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function henngay() {
  global $data, $db, $result;
  
  $ngayhen = isodatetotime($data->ngayhen);
  $sql = "update pet_". PREFIX ."_datlich set thoigian = $ngayhen where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function khongden() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function dadendieutri() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 1 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function sosanhthoigian($a, $b) {
  return $a["thoigian"] < $b["thoigian"];
}

function danhsachdatlich() {
  global $data, $db, $result;

  if (!empty($data->tukhoa)) {
    $sql = "select a.*, b.phone as dienthoai, b.name as tenkhach from pet_". PREFIX ."_datlich a inner join pet_". PREFIX ."_customer b on a.idkhachhang = b.id where b.name like '%$data->tukhoa%' or b.phone like '%$data->tukhoa%'";
    $danhsach = $db->all($sql);
  }
  else {
    $daungay = strtotime(date("Y/m/d"));
    $cuoingay = $daungay + 60 * 60 * 24 - 1;
    $sql = "select a.*, b.phone as dienthoai, b.name as tenkhach, 0 as loai from pet_". PREFIX ."_datlich a inner join pet_". PREFIX ."_customer b on a.idkhachhang = b.id where (thoigian between $daungay and $cuoingay) or (trangthai = 0 and thoigian < $daungay)";
    $danhsach = $db->all($sql);
  }

  usort($danhsach, "sosanhthoigian");

  foreach ($danhsach as $key => $value) {
    $danhsach[$key]['thoigian'] = date("d/m/Y H:i", $value["ngaydat"]);
    $sql = "select a.id, a.tendanhmuc from pet_". PREFIX ."_danhmuc a inner join pet_". PREFIX ."_datlichchitiet b on a.id = b.iddanhmuc where b.iddatlich = $value[id]";
    $danhsach[$key]["dichvu"] = implode(", ", $db->arr($sql, "tendanhmuc"));
    $danhsach[$key]["iddichvu"] = $db->arr($sql, "id");
  }

  return $danhsach;
}