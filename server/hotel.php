<?php
function init() {
  global $data, $db, $result;
    
  $result['status'] = 1;
  $result['cat'] = getcatlist();
  $result['list'] = getlist();
  return $result;
}

function getcatobj() {
  global $data, $db;

  $sql = "select * from pet_". PREFIX ."_hotel_cat";
  return $db->obj($sql, 'id', 'price');
}

function getlist() {
  global $data, $db, $result;

  $filter = $data->filter;
  $start = isodatetotime($filter->start);	
  $end = isodatetotime($filter->end) + 60 * 60 * 24 - 1;	
  $catobj = getcatobj();
  $list = array(0 => array(), array());
  $xtra = array(0 => "", "and (cometime between $start and $end)");
  foreach ($list as $key => $row) {
    $sql = "select a.*, b.name, b.phone, b.address from pet_". PREFIX ."_hotel a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where status = $key $xtra[$key]";
    $list[$key] = $db->all($sql);

    foreach ($list[$key] as $k => $r) {
      if ($r['status']) {
        $sql = "select fullname from pet_". PREFIX ."_users where userid = $r[returnuserid]";
        $u = $db->fetch($sql);
        $list[$key][$k]['fullname'] = $u['fullname'];
      }
      else $list[$key][$k]['fullname'] = '';

      $list[$key][$k]['price'] = number_format($catobj[$r['catid']]);
      if ($r['status']) $list[$key][$k]['total'] = number_format($catobj[$r['catid']] * round(($r['returntime'] - $r['cometime']) / 60 / 60 / 24));
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
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $time = time();
  $sql = "insert into pet_". PREFIX ."_hotel (customerid, catid, health, cometime, calltime, returntime, image, returnimage, returnuserid, status) values($customerid, $data->catid, '$data->health', $cometime, $calltime, 0, '$image', '', 0, 0)";
  $id = $db->insertid($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function update() {
  global $data, $db, $result, $userid;
  
  $cometime = isodatetotime($data->cometime);
  $calltime = isodatetotime($data->calltime);
  $customerid = checkcustomer();
  $image = implode(',', $data->image);
  $sql = "update pet_". PREFIX ."_hotel set customerid = $customerid, health = '$data->health', cometime = '$cometime', calltime = '$calltime', image = '$image' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function returning() {
  global $data, $db, $result, $userid;
  
  $returntime = isodatetotime($data->returntime) + 60 * 60 * 24 - 1;
  $customerid = checkcustomer();
  $returnimage = implode(',', $data->image);
  $sql = "update pet_". PREFIX ."_hotel set customerid = $customerid, health = '$data->health', returntime = $returntime, returnimage = '$returnimage', returnuserid = '$data->returnuserid', status = 1 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function hopital() {
  global $data, $db, $result, $userid;
  
  $sql = "update pet_". PREFIX ."_hotel set status = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function remove() {
  global $data, $db, $result, $userid;
  
  $sql = "delete from pet_". PREFIX ."_hotel where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function savecatlist() {
  global $data, $db, $result, $userid;
  
  foreach ($data->list as $row) {
    $price = str_replace(',', '', $row->price);
    $sql = "update pet_". PREFIX ."_hotel_cat set name = '$row->name', price = '$price' where id = $row->id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getcatlist();
  return $result;
}

function removecat() {
  global $data, $db, $result, $userid;
  
  $sql = "update pet_". PREFIX ."_hotel_cat set active = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getcatlist();
  return $result;
}

function insertcat() {
  global $data, $db, $result, $userid;
  
  $price = str_replace(',', '', $data->price);
  $sql = "insert into pet_". PREFIX ."_hotel_cat (name, price) values('$data->name', '$data->price')";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getcatlist();
  return $result;
}

// function getinfo() {
//   global $data, $db, $result, $userid;
  
//   $sql = "select a.*, b.name, b.phone, b.address, c.fullname as user from pet_". PREFIX ."_hotel a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.userid = c.userid where a.id = $data->id";
//   $data = $db->fetch($sql);
//   $data['time'] = date('d/m/Y', $data['time']);
//   $data['image'] = explode(',', $data['image']);
//   if (count($data['image']) == 1 && $data['image'][0] == '') $data['image'] = array();

//   $result['status'] = 1;
//   $result['data'] = $data;
//   return $result;
// }

function catlist() {
  global $data, $db, $result, $userid;
  
  $result['status'] = 1;
  $result['list'] = getcatlist();
  return $result;
}

function getcatlist() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_hotel_cat where active = 1 order by id asc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['price'] = number_format($row['price']);
  }
  return $list;
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
