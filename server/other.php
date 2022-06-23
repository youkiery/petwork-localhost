<?php
function init() {
  global $data, $db, $result;
    
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['need'] = getneed();
  $result['type'] = gettypelist();
  return $result;
}

function getlist() {
  global $data, $db, $result;

  $start = isodatetotime($data->start);
  $end = isodatetotime($data->end) + 60 * 60 * 24 -1;
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user, d.name as exam from pet_phc_exam a inner join pet_phc_customer b on a.customerid = b.id inner join pet_phc_users c on a.userid = c.userid inner join pet_phc_exam_type d on a.typeid = d.id where a.time between $start and $end and a.status = 1";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['image'] = explode(',', $row['image']);
    if (count($list[$key]['image']) == 1 && $list[$key]['image'][0] == '') $list[$key]['image'] = array();
  }

  return $list;
}

function getneed() {
  global $data, $db, $result;
    
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user, d.name as exam from pet_phc_exam a inner join pet_phc_customer b on a.customerid = b.id inner join pet_phc_users c on a.userid = c.userid inner join pet_phc_exam_type d on a.typeid = d.id where a.status = 0";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['image'] = explode(',', $row['image']);
    if (count($list[$key]['image']) == 1 && $list[$key]['image'][0] == '') $list[$key]['image'] = array();
  }

  return $list;
}

function gettypelist() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_exam_type where active = 1 order by name desc";
  $list = $db->all($sql);

  return $list;
}

function insert() {
  global $data, $db, $result, $userid;
  
  $userid = checkuserid();
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $time = time();
  
  $sql = "insert into pet_phc_exam (userid, customerid, treatid, typeid, image, note, time, status) values($userid, $customerid, 0, $data->typeid, '$image', '$data->note', $time, 1)";

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
  $sql = "update pet_phc_exam set customerid = $customerid, image = '$image', note = '$data->note', status = 1 where id = $data->id";
  $id = $db->insertid($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['need'] = getneed();
  return $result;
}

function remove() {
  global $data, $db, $result, $userid;
  
  $sql = "delete from pet_phc_exam where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getinfo() {
  global $data, $db, $result, $userid;
  
  
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user, d.name as exam from pet_phc_exam a inner join pet_phc_customer b on a.customerid = b.id inner join pet_phc_users c on a.userid = c.userid inner join pet_phc_exam_type d on a.typeid = d.id where a.id = $data->id";
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

function inserttype() {
  global $data, $db, $result, $userid;
  
  $sql = "insert into pet_phc_exam_type (name) values('$data->name')";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function updatetype() {
  global $data, $db, $result, $userid;
  
  $sql = "update pet_phc_exam_type set name = '$data->name' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function removetype() {
  global $data, $db, $result, $userid;
  
  $sql = "update pet_phc_exam_type set active = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}