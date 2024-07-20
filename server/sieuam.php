<?php
function init() {
  global $data, $db, $result;
    
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['need'] = getneed();
  return $result;
}

function getlist() {
  global $data, $db, $result;

  $start = isodatetotime($data->start);
  $end = isodatetotime($data->end) + 60 * 60 * 24 -1;
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user from pet_". PREFIX ."_sieuam a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.userid = c.userid where a.time between $start and $end order by id desc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['image'] = parseimage($row['image']);
  }

  return $list;
}

function removeneed() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xray_row set sieuam = 0 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getneed();
  return $result;
}

function getneed() {
  global $data, $db, $result;
    
  // $userid = checkuserid();
  // $sql = "select placeid from pet_". PREFIX ."_users where userid = $userid";
  // $user = $db->fetch($sql);
  // $placeid = $user['placeid'];

  // $sql = "select a.id, a.xrayid, a.image from pet_". PREFIX ."_xray_row a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.sieuam < 0 and (b.placeid = $placeid or b.placeid = 0)";

  $sql = "select id, xrayid, image from pet_". PREFIX ."_xray_row where sieuam < 0 order by time desc";
  $list = $db->all($sql);
  $danhsach = [];

  foreach ($list as $key => $row) {
    $mau = $list[$key];
    $sql = "select a.petname, b.name, b.phone, b.address from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.id = $row[xrayid]";
    if (!empty($info = $db->fetch($sql))) {
      $mau['petname'] = $info['petname'];
      $mau['name'] = $info['name'];
      $mau['phone'] = $info['phone'];
      $mau['address'] = $info['address'];
      $mau['image'] = parseimage($row['image']);
      $danhsach []= $mau;
    }
  }

  return $danhsach;
}

function insert() {
  global $data, $db, $result, $userid;
  
  $userid = checkuserid();
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $time = time();
  $sql = "insert into pet_". PREFIX ."_sieuam (userid, customerid, image, note, time) values($userid, $customerid, '$image', '$data->note', $time)";
  $id = $db->insertid($sql);

  if (isset($data->xrayid)) {
    $sql = "update pet_". PREFIX ."_xray_row set sieuam = $id where id = $data->xrayid";
    $db->query($sql);
  }

  $result['status'] = 1;
  if (!isset($data->his)) {
    $result['list'] = getlist();
    $result['need'] = getneed();
  }
  return $result;
}

function update() {
  global $data, $db, $result, $userid;
  
  $userid = checkuserid();
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $sql = "update pet_". PREFIX ."_sieuam set customerid = $customerid, image = '$image', note = '$data->note' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function remove() {
  global $data, $db, $result, $userid;
  
  $sql = "delete from pet_". PREFIX ."_sieuam where id = $data->id";
  $db->query($sql);

  $sql = "update pet_". PREFIX ."_xray_row set sieuam = -1 where sieuam = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getinfo() {
  global $data, $db, $result, $userid;
  
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user from pet_". PREFIX ."_sieuam a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.userid = c.userid where a.id = $data->id";
  $data = $db->fetch($sql);
  $data['time'] = date('d/m/Y', $data['time']);
  $data['image'] = parseimage($data['image']);

  $result['status'] = 1;
  $result['data'] = $data;
  return $result;
}

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
