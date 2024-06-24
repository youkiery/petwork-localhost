<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['clock'] = getclock();
  $result['list'] = getlist();
  return $result;
}

function remove() {
  global $data, $db, $result;

  if ($data->segment == '0') $sql = "delete from pet_". PREFIX ."_ride where id = $data->id";
  else $sql = "delete from pet_". PREFIX ."_import where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function cole() {
  global $data, $db, $result;

  $money = str_replace(',', '', $data->money);
  $time = time();
  $sql = "insert into pet_". PREFIX ."_ride (userid, clockf, clocke, money, destination, note, time) values($data->idnguoidung, $data->clockf, $data->clocke, $money, '$data->destination', '$data->note', $time)";
  $db->query($sql);

  $sql = "update pet_". PREFIX ."_config set name = '$data->clocke' where module = 'clock'";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function pay() {
  global $data, $db, $result;

  $money = str_replace(',', '', $data->money);
  $time = time();
  $sql = "insert into pet_". PREFIX ."_import (userid, price, module, note, time) values($data->idnguoidung, $money, 'ride', '$data->note', $time)";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getclock() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_config where module = 'clock'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value) values ('clock', '0','')";
    $db->query($sql);
    return 0;
  }
  return $c['name'];
}

function getlist($today = false) {
  global $db, $data;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end);
  $ride = "select a.*, b.hoten as user from pet_". PREFIX ."_ride a inner join pet_nhanvien b on a.userid = b.id where (a.time between $data->start and $data->end)";
  $pay = "select a.*, b.hoten as user from pet_". PREFIX ."_import a inner join pet_nhanvien b on a.userid = b.id where a.module = 'ride' and (a.time between $data->start and $data->end)";

  return array(
    0 => $db->all($ride),
    $db->all($pay),
  );
}

function statistic() {
  global $data, $db, $result;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->idnguoidung and module = 'ride' and type = 2";
  $xtra = "";
  if (empty($p = $db->fetch($sql))) $xtra = "a.userid = $data->idnguoidung and";

  $sql = "select * from pet_". PREFIX ."_ride where $xtra (time between $data->start and $data->end) order by id desc";
  $cole = $db->all($sql);
  $sql = "select * from pet_". PREFIX ."_import where $xtra module = 'ride' and (time between $data->start and $data->end) order by id desc";
  $pay = $db->all($sql);
  $data = array(
    'cole' => 0, 'pay' => 0, 'count' => 0, 'list' => array()
  );
  $temp = array();

  foreach ($cole as $row) {
    $data['cole'] += $row['money'];
    $data['count'] ++;
    if (empty($temp[$row['userid']])) {
      $sql = "select fullname from pet_nhanvien where userid = $row[userid]";
      $u = $db->fetch($sql);
      $temp[$row['userid']] = array('name' => $u['fullname'], 'clock' => 0, 'cole' => 0, 'pay' => 0, 'count' => 0);
    }
    $temp[$row['userid']]['clock'] += $row['clocke'] - $row['clockf'];
    $temp[$row['userid']]['cole'] += $row['money'];
    $temp[$row['userid']]['count'] ++;
  }

  foreach ($pay as $row) {
    $data['pay'] += $row['price'];
    if (empty($temp[$row['userid']])) {
      $sql = "select hoten from pet_nhanvien where id = $row[userid]";
      $u = $db->fetch($sql);
      $temp[$row['userid']] = array('name' => $u['hoten'], 'clock' => 0, 'cole' => 0, 'pay' => 0, 'count' => 0);
    }
    $temp[$row['userid']]['pay'] = $row['price'];
  }

  foreach ($temp as $row) {
    $data['list'][] = $row;
  }
  
  $result['status'] = 1;
  $result['data'] = $data;
  return $result;
}
