<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function sosanhthoigian($a, $b) {
  return $a["thoigian"] < $b["thoigian"];
}


function danhsachdatlich() {
  global $data, $result;
  
  $config = [
    "servername" => "localhost",
    "username" => "root",
    "password" => "",
    "database" => "thanhxuanpet",
  ];
  $db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

  if (!empty($data->tukhoa)) {
    $sql = "select * from pet_phc_spa_datlich where chinhanh = 0 and (tenkhach like '%$data->tukhoa%' or dienthoai like '%$data->tukhoa%')";
    $danhsach = $db->all($sql);
    $sql = "select * from pet_phc_dieutri_datlich where chinhanh = 0 and (tenkhach like '%$data->tukhoa%' or dienthoai like '%$data->tukhoa%')";
    array_merge($danhsach, $db->all($sql));
  }
  else {
    $daungay = strtotime(date("Y/m/d"));
    $cuoingay = $daungay + 60 * 60 * 24 - 1;
    $sql = "select * from pet_phc_spa_datlich where chinhanh = 0 and (thoigian between $daungay and $cuoingay)";
    $danhsach = $db->all($sql);
    $sql = "select * from pet_phc_dieutri_datlich where chinhanh = 0 and (thoigian between $daungay and $cuoingay)";
    array_merge($danhsach, $db->all($sql));
  }


  usort($danhsach, "sosanhthoigian");

  foreach ($danhsach as $key => $value) {
    $danhsach[$key]['thoigian'] = date("d/m/y", $value["thoigian"]);
  }

  return $danhsach;
}