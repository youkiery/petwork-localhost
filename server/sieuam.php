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
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user from pet_phc_sieuam a inner join pet_phc_customer b on a.customerid = b.id inner join pet_phc_users c on a.userid = c.userid where a.time between $start and $end order by id desc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['image'] = explode(',', $row['image']);
    if (count($list[$key]['image']) == 1 && $list[$key]['image'][0] == '') $list[$key]['image'] = array();
  }

  return $list;
}

function removeneed() {
  global $data, $db, $result;

  $sql = "update pet_phc_xray_row set sieuam = 0 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getneed();
  return $result;
}

function getneed() {
  global $data, $db, $result;
    
  // $userid = checkuserid();
  // $sql = "select placeid from pet_phc_users where userid = $userid";
  // $user = $db->fetch($sql);
  // $placeid = $user['placeid'];

  // $sql = "select a.id, a.xrayid, a.image from pet_phc_xray_row a inner join pet_phc_users b on a.userid = b.userid where a.sieuam < 0 and (b.placeid = $placeid or b.placeid = 0)";

  $sql = "select id, xrayid, image from pet_phc_xray_row where sieuam < 0";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $sql = "select b.name as petname, b.name, b.phone, b.address from pet_phc_xray a inner join pet_phc_customer b on a.customerid = b.id where a.id = $row[xrayid]";
    $info = $db->fetch($sql);
    $list[$key]['petname'] = $info['petname'];
    $list[$key]['name'] = $info['name'];
    $list[$key]['phone'] = $info['phone'];
    $list[$key]['address'] = $info['address'];
    $list[$key]['image'] = explode(',', $row['image']);
    if (count($list[$key]['image']) == 1 && $list[$key]['image'][0] == '') $list[$key]['image'] = array();
  }

  return $list;
}

function insert() {
  global $data, $db, $result, $userid;
  
  $userid = checkuserid();
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $time = time();
  $sql = "insert into pet_phc_sieuam (userid, customerid, image, note, time) values($userid, $customerid, '$image', '$data->note', $time)";
  $id = $db->insertid($sql);

  if (isset($data->xrayid)) {
    $sql = "update pet_phc_xray_row set sieuam = $id where id = $data->xrayid";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['need'] = getneed();
  return $result;
}

function update() {
  global $data, $db, $result, $userid;
  
  $userid = checkuserid();
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $sql = "update pet_phc_sieuam set customerid = $customerid, image = '$image', note = '$data->note' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function remove() {
  global $data, $db, $result, $userid;
  
  $sql = "delete from pet_phc_sieuam where id = $data->id";
  $db->query($sql);

  $sql = "update pet_phc_xray_row set sieuam = -1 where sieuam = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getinfo() {
  global $data, $db, $result, $userid;
  
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user from pet_phc_sieuam a inner join pet_phc_customer b on a.customerid = b.id inner join pet_phc_users c on a.userid = c.userid where a.id = $data->id";
  $data = $db->fetch($sql);
  $data['time'] = date('d/m/Y', $data['time']);
  $data['image'] = explode(',', $data['image']);
  if (count($data['image']) == 1 && $data['image'][0] == '') $data['image'] = array();

  $result['status'] = 1;
  $result['data'] = $data;
  return $result;
}

function checkcustomer() {
  global $db, $data;

  $sql = "select * from pet_phc_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_phc_customer set name = '$data->name', address = '$data->address' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_phc_customer (name, phone, address) values ('$data->name', '$data->phone', '$data->address')";
    $customer['id'] = $db->insertid($sql);
  }

  return $customer['id'];
}
