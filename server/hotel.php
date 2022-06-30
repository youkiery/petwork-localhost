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

  $list = array(0 => array(), array());
  foreach ($list as $key => $row) {
    $sql = "select a.*, b.name, b.phone, b.address, c.fullname from pet_phc_hotel a inner join pet_phc_customer b on a.customerid = b.id inner join pet_phc_users c on a.returnuserid = c.userid where status = $key";
    $list[$key] = $db->all($sql);

    foreach ($list[$key] as $k => $r) {
      $list[$key][$k]['cometime'] = date('d/m/Y', $r['cometime']);
      $list[$key][$k]['calltime'] = date('d/m/Y', $r['calltime']);
      $list[$key][$k]['returntime'] = date('d/m/Y', $r['returntime']);
      $list[$key][$k]['image'] = parseimage($r['image']);
      $list[$key][$k]['returnimage'] = parseimage($r['returnimage']);
    }
  }

  return $list;
}

function insert() {
  global $data, $db, $result, $userid;
  
  $cometime = isodatetotime($data->cometime);
  $calltime = isodatetotime($data->calltime);
  $returntime = isodatetotime($data->returntime);
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $returnimage = implode(',', $data->returnimage);
  $time = time();
  $sql = "insert into pet_phc_hotel (customerid, health, cometime, calltime, returntime, image, returnimage, returnuserid, status) values($customerid, '$data->health', $cometime, $calltime, 0, '$image', '', $userid)";
  $id = $db->insertid($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function update() {
  global $data, $db, $result, $userid;
  
  $cometime = isodatetotime($data->cometime);
  $calltime = isodatetotime($data->calltime);
  $returntime = isodatetotime($data->returntime);
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $returnimage = implode(',', $data->returnimage);
  $sql = "update pet_phc_hotel set customerid = $customerid, health = '$data->health', cometime = '$cometime', calltime = '$calltime', image = '$image' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function remove() {
  global $data, $db, $result, $userid;
  
  $sql = "delete from pet_phc_hotel where id = $data->id";
  $db->query($sql);

  $sql = "update pet_phc_xray_row set xquang = -1 where xquang = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getinfo() {
  global $data, $db, $result, $userid;
  
  $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user from pet_phc_hotel a inner join pet_phc_customer b on a.customerid = b.id inner join pet_phc_users c on a.userid = c.userid where a.id = $data->id";
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
