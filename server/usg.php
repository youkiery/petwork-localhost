<?php
// 0: 'Nhắc tiêm phòng trước salơ',
// 1: 'Nhắc test Progesterone',
// 2: 'Tư vấn trước sinh',
// 3: 'Ngày sinh',
// 4: 'Nhắc sổ giun lần 1',
// 5: 'Nhắc tiêm vaccine',
// 6: 'Đã gọi tiêm vaccine',
// 7: 'Đã hoàn thành',
// 8: 'Không theo dõi nữa',
// 9: 'Phiếu tạm',
$aday = 60 * 60 * 24;
$cover = array(
  0 => array('s' => 1, 't' => $aday * 30 * 6),
  1 => array('s' => 1, 't' => $aday * 30 * 6),
  2 => array('s' => 3, 't' => $aday * 1), 
  3 => array('s' => 4, 't' => $aday * 7 * 5), 
  4 => array('s' => 5, 't' => $aday * 7 * 6), 
  5 => array('s' => 6, 't' => 0), 
);

function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  // $result['type'] = gettypeobj();
  // $result['doctor'] = getDoctor();
  $result['temp'] = gettemplist();
  
  return $result;
}

function filter() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function gettemplist() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->idnguoidung and module = 'vaccine'";
  $role = $db->fetch($sql);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $data->idnguoidung ";
  else if (!empty($data->chonnhanvien)) {
    $xtra []= " a.userid in (". implode(',', $data->chonnhanvien) .") ";
  }
  if (!empty($data->time)) {
    $data->time = isodatetotime($data->time) + 60 * 60 * 24 - 1;
    $xtra []= " a.time < $data->time ";
  }
  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.fullname as doctor from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer d on a.customerid = d.id where a.status = 9 $xtra order by a.id desc";
  $v = $db->all($sql);
  $e = array();
  $l = array();
  $list = array(
    0 => array(), array()
  );

  foreach ($v as $row) {
    $temp = tempdatacover($row);
    if (empty($temp['phone']) || !$row['calltime']) $e []= $temp;
    else $l []= $temp;
  }

  $list[0] = array_merge($l, $e);
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 - 1;
  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.fullname as doctor from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer d on a.customerid = d.id where utemp = 1 and (time between $start and $end) $xtra order by a.id desc";
  $l = $db->all($sql);

  foreach ($l as $row) {
    $list[1] []= tempdatacover($row);
  }
  return $list;
}

function tempdatacover($data) {
  return array(
    'id' => $data['id'],
    'note' => $data['note'],
    'doctor' => $data['doctor'],
    'customerid' => $data['customerid'],
    'name' => $data['name'],
    'phone' => $data['phone'],
    'address' => $data['address'],
    'number' => $data['number'],
    'called' => ($data['called'] ? date('d/m/Y', $data['called']) : ''),
    'cometime' => date('d/m/Y', $data['cometime']),
    'calltime' => ($data['calltime'] ? date('d/m/Y', $data['calltime']) : ''),
    'time' => date('d/m/Y', $data['time']),
  );
}

function getlist($today = false) {
  global $db, $data, $userid;

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->idnguoidung and module = 'vaccine'";
  $role = $db->fetch($sql);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $data->idnguoidung ";
  else if (!empty($data->chonnhanvien)) {
    $xtra []= " a.userid in (". implode(',', $data->chonnhanvien) .") ";
  }

  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $start = strtotime(date('Y/m/d'));

  if ($today) {
    // danh sách đã thêm hôm nay
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (a.time between $start and ". time() . ") $xtra and a.status < 6 order by a.id desc";
    $list = dataCover($db->all($sql));
  }
  else if (empty($data->keyword)) {
    // danh sách nhắc hôm nay
    $daungay = strtotime(date('Y/m/d'));
    $cuoingay = $daungay + 60 * 60 * 24 - 1;

    $list = array(0 => array());
    
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.status = 3 and (calltime < $cuoingay) and called = 0 $xtra order by a.recall asc";
    $list[0] = dataCover($db->all($sql));
    
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (called between $daungay and $cuoingay) $xtra order by a.recall asc";
    $list[1] = dataCover($db->all($sql));
  }
  else {
    // danh sách tìm kiếm khách hàng
    $key = trim($data->keyword);
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (b.name like '%$key%' or b.phone like '%$key%') and status < 8 order by a.recall desc";
    $list = dataCover($db->all($sql));
  }

  return $list;
}

function dataCover($list) {
  global $start;
  $lim = strtotime(date('Y/m/d')) - 1 + 60 * 60 * 24;
  $v = array();
  $stoday = strtotime(date('Y/m/d'));
  $etoday = $stoday + 60 * 60 * 24  - 1;

  foreach ($list as $row) {
    // thời gian gọi
    if (!$row['called']) $called = '-';
    else if ($row['called'] >= $stoday && $row['called'] <= $etoday) $called = 'Hôm hay đã gọi';
    else $called = date('d/m/Y', $row['called']);
    // nếu status < 6, kiểm tra recall < lim hay không
    // nếu không thì bỏ qua
    $over = (($row['status'] < 6 && $row['recall'] < $lim) ? 1 : 0);  
    $v []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'customerid' => $row['customerid'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'number' => $row['number'],
      'status' => $row['status'],
      'over' => $over,
      'called' => $called,
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
      'recall' => date('d/m/Y', $row['recall']),
    );
  }
  return $v;
}

function search() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function searchcustomer() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function insert() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);

  $sql = "insert into pet_". PREFIX ."_usg (customerid, userid, cometime, calltime, recall, number, status, note, time, called) values ($customerid, $data->idnguoidung, $data->cometime, $data->calltime, $data->calltime, $data->number, 3, '$data->note', ". time() .", 0)";
  $result['status'] = 1;
  $result['vid'] = $db->insertid($sql);
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã thêm vào danh sách nhắc";
  return $result;
}

// function uncalled() {
//   global $data, $db, $result;

//   $sql = "select * from pet_". PREFIX ."_usg where id = $data->id";
//   $v = $db->fetch($sql);
//   $time = time();
//   $recall = $v['recall'] + 60 * 60 * 24;
//   if ($time > $v['recall']) $recall = $time + 60 * 60 * 24;

//   $sql = "update pet_". PREFIX ."_usg set note = '". $data->note ."', called = $time, recall = $recall where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['messenger'] = "Đã chuyển dời ngày nhắc đến ". date('d/m/Y', $recall);
//   $result['list'] = getlist();
  
//   return $result;
// }

function update() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  
  $sql = "update pet_". PREFIX ."_usg set customerid = $customerid, note = '$data->note', cometime = $data->cometime, calltime = $data->calltime, recall = $data->calltime, number = $data->number where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã cập nhật phiếu nhắc";
  return $result;
}

function updatehistory() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);

  $sql = "update pet_". PREFIX ."_usg set customerid = $customerid, note = '$data->note', cometime = $data->cometime, calltime = $data->calltime, recall = $data->calltime, number = $data->number where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xác nhận và thêm vào danh sách nhắc';
  $result['old'] = array();
  $result['list'] = gettemplist();

  return $result;
}

function birth() {
  global $data, $db, $result, $cover;

  $calltime = isodatetotime($data->calltime);
  $recall = isodatetotime($data->deworm);
  $time = time();
  if (!empty($data->vaccine)) $vaccine = isodatetotime($data->vaccine);
  else $vaccine = 0;

  $sql = "update pet_". PREFIX ."_usg set vaccinetime = $vaccine, status = 4, called = $time, note = '". $data->note ."', number = $data->number, calltime = $calltime, recall = $recall where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã thay đổi trạng thái";
  $result['list'] = getlist();

  return $result;
}

// function called() {
//   global $data, $db, $result, $cover;

//   $sql = "select * from pet_". PREFIX ."_usg where id = $data->id";
//   $u = $db->fetch($sql);
//   $time = time();
//   if ($u['status'] == 0) $recall = $u['cometime'] + $cover[$u['status']]['t'];
//   else $recall = $u['calltime'] + $cover[$u['status']]['t'];
//   $status = $cover[$u['status']]['s'];

//   $sql = "update pet_". PREFIX ."_usg set status = $status, note = '". $data->note ."', called = $time, recall = $recall where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['messenger'] = "Đã thay đổi trạng thái";
//   $result['list'] = getlist();

//   return $result;
// }

// function deworm() {
//   global $data, $db, $result, $cover;

//   $sql = "select * from pet_". PREFIX ."_usg where id = $data->id";
//   $u = $db->fetch($sql);
//   $time = time();
//   // neu co deworm thi cap nhat, neu khong + 5 tuan
//   if (isset($data->deworm)) {
//     $t = str_replace('-', '/', $data->deworm);
//     $recall = strtotime($t);
//   } 
//   else $recall = $u['calltime'] + 60 * 60 * 24 * 7 * 6;

//   $sql = "update pet_". PREFIX ."_usg set status = 5, note = '". $data->note ."', called = $time, recall = $recall where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['messenger'] = "Đã thay đổi trạng thái";
//   $result['list'] = getlist();

//   return $result;
// }

function dead() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_usg set status = 8, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã không được theo dõi nữa";
  $result['list'] = getlist();
  
  return $result;
}

// function done() {
//   global $data, $db, $result;

//   $sql = "update pet_". PREFIX ."_usg set status = 7, note = '$data->note' where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['messenger'] = "Phiếu nhắc đã không được theo dõi nữa";
//   $result['list'] = getlist();
  
//   return $result;
// }

// function repregnant() {
//   global $data, $db, $result;

//   $time = time();
//   $recall = $time + 60 * 60 * 24 * 30 * 5;
//   $sql = "update pet_". PREFIX ."_usg set status = 0, number = 0, cometime = $time, calltime = $recall, recall = $recall, note = '$data->note' where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['messenger'] = "Phiếu nhắc đã lặp lại 5 tháng sau";
//   $result['list'] = getlist();
  
//   return $result;
// }

function checkcustomer() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_". PREFIX ."_customer set name = '$data->name', address = '$data->address' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values ('$data->name', '$data->phone', '$data->address')";
    $customer['id'] = $db->insertid($sql);
  }

  return $customer['id'];
}

function tempauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = gettemplist();
  
  return $result;
}

function typeauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = gettypeobj();
  
  return $result;
}

function doctorauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getDoctor();
  
  return $result;
}

function removeall() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "delete from pet_". PREFIX ."_usg where id = $id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = "Đã xóa các phiếu nhắc tạm";
  $result['list'] = gettemplist();
  return $result;
}

function doneall() {
  global $data, $db, $result;

  $time = time();
  foreach ($data->list as $id) {
    $sql = "select a.number, a.calltime, a.cometime, b.* from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_pet b on a.customerid = b.id where a.id = $id";
    $u = $db->fetch($sql);
    // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
    // nếu không, đặt 5 tháng sau nhắc kỳ salơ

    $sql = "update pet_". PREFIX ."_usg set status = 3, utemp = 1, time = $time where id = $id";
    $db->query($sql);
  }

  $result['list'] = gettemplist();
  $result['messenger'] = "Đã xác nhận các phiếu nhắc tạm";
  $result['status'] = 1;
  return $result;
}

function history() {
  global $data, $db, $result;

  $sql = "select a.*, c.fullname as doctor, b.phone, b.name, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.status < 3 and b.phone = '$data->phone' order by a.id asc";
  $result['status'] = 1;
  $result['old'] = dataCover($db->all($sql));
  return $result;
}

function transfer() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "update pet_". PREFIX ."_usg set userid = $data->uid where id = $id";
    $db->query($sql);
  }
  $sql = "select a.userid, b.fullname as name from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.module = 'doctor' and a.type = 1 and a.userid = $data->uid";
  $d = $db->fetch($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã chuyển phiếu nhắc sang cho nhân viên: $d[name]";
  $result['list'] = gettemplist();

  return $result;
}

function confirm() {
  global $data, $db, $result;

  $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.id = $data->id";
  $c = $db->fetch($sql);

  $sql = "update pet_". PREFIX ."_usg set status = 3, utemp = 1, time = ". time() ." where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã xác nhận và chuyển vào danh sách nhắc";
  $result['temp'] = gettemplist();

  return $result;
}

function donerecall() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "update pet_". PREFIX ."_usg set status = 3 where id = $id";
    $db->query($sql);
  }
  $result['status'] = 1;
  $result['messenger'] = "Tất cả phiếu nhắc được chọn chuyển thành 'Đã tái chủng'";
  $result['new'] = getlist(true);
  return $result;
}

function removetemp() {
  global $data, $db, $result;
  $sql = "delete from pet_". PREFIX ."_usg where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = gettemplist();
  $result['messenger'] = "Đã xóa phiếu tạm";
  return $result;
}

function removetype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_config set module = 'usg-deleted' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function updatetype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_config set name = '$data->name' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function inserttype() {
  global $data, $db, $result;

  $sql = "insert into pet_". PREFIX ."_config (module, name, value) values('usg', '$data->name', '')";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function gettypelist() {
  global $db;

  $sql = "select id, name from pet_". PREFIX ."_config where module = 'usg'";
  return $db->all($sql);
}
