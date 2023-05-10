<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['cauhinh'] = dulieucauhinh();
  $result['luongcoban'] = luongcoban();
  return $result;
}


function khoitaocauhinh() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['cauhinh'] = dulieucauhinh();
  return $result;
}

function luongcoban() {
  global $db;

  $userid = checkuserid();
  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $userid";
  $luong = $db->fetch($sql);

  if (empty($luong)) return 0;

  $homnay = time();
  $dauthanglenluong = strtotime(date('Y/m/1', $luong['lenluong']));
  $sonam = floor(($homnay - $luong['hopdong']) / 365 / 60 / 60 / 24);

  return $sonam * 500000 + $luong['luongcoban'];
}


function dulieucauhinh() {
  global $db;
  $cauhinh = [
    'loinhuanspa' => 0,
    'loinhuandieutri' => 0,
    'chietkhauspa' => 0,
    'chietkhaudieutri' => 0,
  ];

  foreach ($cauhinh as $bien => $giatri) {
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$bien'";
    $truongcauhinh = $db->fetch($sql);
    if (!empty($truongcauhinh)) $cauhinh[$bien] = $truongcauhinh['value'];
  }
  return $cauhinh;
}

function luucauhinh() {
  global $data, $db, $result;
  
  foreach ($data->cauhinh as $bien => $giatri) {
    $giatri = floatval($giatri);
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$bien'";
    if (!empty($truongcauhinh = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_config set value = '$giatri' where id = $truongcauhinh[id]";
    else $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('loinhuan', '$bien', '$giatri', 0)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  $result['cauhinh'] = dulieucauhinh();
  return $result;
}
