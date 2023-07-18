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
  if (empty($data->loai)) $loai = "spa";
  else $loai = "dieutri";
  $sql = "update pet_". PREFIX ."_". $loai ."_datlich set thoigian = $ngayhen where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function khongden() {
  global $data, $db, $result;
  
  if (empty($data->loai)) $loai = "spa";
  else $loai = "dieutri";
  $sql = "update pet_". PREFIX ."_". $loai ."_datlich set trangthai = 2 where id = $data->id";
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
    $sql = "select *, 0 as loai from pet_". PREFIX ."_spa_datlich where chinhanh = 0 and (tenkhach like '%$data->tukhoa%' or dienthoai like '%$data->tukhoa%')";
    $danhsach = $db->all($sql);
    $sql = "select *, 1 as loai from pet_". PREFIX ."_dieutri_datlich where chinhanh = 0 and (tenkhach like '%$data->tukhoa%' or dienthoai like '%$data->tukhoa%')";
    array_merge($danhsach, $db->all($sql));
  }
  else {
    $daungay = strtotime(date("Y/m/d"));
    $cuoingay = $daungay + 60 * 60 * 24 - 1;
    $sql = "select *, 0 as loai from pet_". PREFIX ."_spa_datlich where chinhanh = 0 and ((thoigian between $daungay and $cuoingay) or (trangthai = 0 and thoigian < $daungay))";
    $danhsach = $db->all($sql);
    $sql = "select *, 1 as loai from pet_". PREFIX ."_dieutri_datlich where chinhanh = 0 and ((thoigian between $daungay and $cuoingay) or (trangthai = 0 and thoigian < $daungay))";
    $danhsach = array_merge($danhsach, $db->all($sql));
  }

  usort($danhsach, "sosanhthoigian");

  foreach ($danhsach as $key => $value) {
    $danhsach[$key]['thoigian'] = date("d/m/Y", $value["thoigian"]);
  }

  return $danhsach;
}