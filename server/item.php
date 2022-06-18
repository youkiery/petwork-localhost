<?php

function expire() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_item where name = '$data->name'";
  if (empty($item = $db->fetch($sql))) {
    $sql = "insert into pet_phc_item (name, code, shop, storage, catid, border, image) values('$data->name', '$data->code', 0, 0, 0, 10, '')";
    $item['id'] = $db->insertid($sql);
  }
  
  $data->expire = totime($data->expire);
  $sql = "insert into pet_phc_item_expire (rid, number, expire, time) values($item[id], $data->number, $data->expire, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = 'Đã thêm hạn sử dụng';
  $result['data'] = checkExpire($data->id);

  return $result;
}

function outstocks() {
  global $data, $db, $result;

  $userid = checkuserid();
  $time = time();
  foreach ($data->data as $item) {
    $sql = "update pet_phc_item set outstock = $item->number, recuserid = $userid, rectime = $time where id = $item->id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function expire_done() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_item_expire where id = $data->id";
  $db->query($sql);
  
  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select a.id, b.name, a.expire from pet_phc_item_expire a inner join pet_phc_item b on a.rid = b.id where expire < $limit";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $list[$key]['expire'] = date('d/m/Y', $value['expire']);
  }
}

function getpurchased() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['list'] = getPurchasedList();
  return $result;
}

function removerecommended() {
  global $db, $data, $result;

  $sql = "delete from pet_phc_item_recommend where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getPurchasedList();
  return $result;
}

function getPurchasedList() {
  global $db, $data;
  $sql = "select a.*, b.name as user from pet_phc_item_recommend a inner join pet_phc_users b on a.userid = b.userid where a.status = 1";
  
  $res = $db->all($sql);
  foreach ($res as $key => $row) {
    $image = explode(',', $row['image']);
    if (count($image) == 1 && strlen($image[0]) == 0) $image = array();
    $res[$key]['image'] = $image;
  }

  return $res;
}

function getPurchasedCount() {
  global $db, $data;
  $sql = "select id from pet_phc_item_recommend where status = 1";
  $count = $db->count($sql);
  return $count;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_phc_item_recommend set status = 1 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getPurchaseList();
  return $result;
}

function expired_init() {
  global $data, $db, $result;

  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select a.id, b.name, a.expire from pet_phc_item_expire a inner join pet_phc_item b on a.rid = b.id where expire < $limit";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $list[$key]['expire'] = date('d/m/Y', $value['expire']);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;
  
  return $result;
}

function incat() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_item_cat where name = '$data->cat'";
  if (!empty($row = $db->fetch($sql))) $result['messenger'] = 'Danh mục đã tồn tại';
  else {
    $sql = "insert into pet_phc_item_cat (name) values('$data->cat')";
    $result['status'] = 1;
    $result['cat'] = $db->insertid($sql);
    $result['catlist'] = getCatList();
  }

  return $result;
}

function getPurchaseList() {
  global $db, $data;
  $sql1 = "select a.image, a.id, a.name, a.outstock, a.shop + a.storage as remain, a.recuserid as userid, a.rectime as time, b.name as user from pet_phc_item a inner join pet_phc_users b on a.recuserid = b.userid where a.outstock > 0";
  $sql2 = "select a.*, b.name as user from pet_phc_item_recommend a inner join pet_phc_users b on a.userid = b.userid where a.status = 0";
  
  $res = array('item' => array(), 'recommend' => $db->all($sql2));
  foreach ($res['recommend'] as $key => $row) {
    $image = explode(',', $row['image']);
    if (count($image) == 1 && strlen($image[0]) == 0) $image = array();
    $res['recommend'][$key]['image'] = $image;
  }

  $list = $db->all($sql1);
  $temp = array();

  foreach ($list as $key => $row) {
    if (empty($temp[$row['user']])) $temp[$row['user']] = array();
    $image = explode(',', $row['image']);
    if (count($image) == 1 && strlen($image[0]) == 0) $image = array();
    $row['image'] = $image;
    $temp[$row['user']] []= $row;
  }

  foreach ($temp as $user => $list) {
    $res['item'] []= array('user' => $user, 'list' => $list);
  }

  return $res;
}

function purchase() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getPurchaseList();
  return $result;
}

function insertsource() {
  global $data, $db, $result;

  $name = mb_strtolower($data->name);
  $sql = "insert into pet_phc_item_source (name) values ('$name')";
  $id = $db->insertid($sql);

  $result['status'] = 1;
  $result['data'] = array('id' => $id, 'name' => $data->name);
  return $result;
}

function recommend() {
  global $data, $db, $result;

  $data->number = intval($data->number);
  $userid = checkuserid();
  $image = implode(',', $data->image);
  $time = time();

  $sql = "insert into pet_phc_item_recommend (content, number, name, phone, image, userid, time) values('$data->content', $data->number, '$data->name', '$data->phone', '$image', $userid, $time)";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getPurchaseList();
  return $result;
}

function updaterecommend() {
  global $data, $db, $result;

  $data->number = intval($data->number);
  $image = implode(',', $data->image);
  $time = time();

  $sql = "update pet_phc_item_recommend set content = '$data->content', number = '$data->number', name = '$data->name', phone = '$data->phone', image = '$image' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getPurchaseList();
  return $result;
}

function purchased() {
  global $data, $db, $result;

  foreach ($data->list->item as $id) {
    $sql = "update pet_phc_item set outstock = 0 where id = $id";
    $db->query($sql);
  }

  foreach ($data->list->recommend as $id) {
    $sql = "update pet_phc_item_recommend set status = 1 where id = $id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['list'] = getPurchaseList();
  return $result;
}

function init() {
  global $data, $db, $result;

  $userid = checkuserid();
  $result['status'] = 1;
  $result['purchase'] = getPurchase();
  $result['purchased'] = getPurchasedCount();
  $result['transfer'] = getTransfer();
  $result['expired'] = getExpire();
  $result['catlist'] = getCatList();
  $result['all'] = getSuggestList();
  $result['image'] = getImagePos();
  $result['list'] = getList();
  $result['position'] = getPosList();
  $result['source'] = getSourceList();
  $result['user'] = getUserList();
  $result['cat'] = getUserCat($userid);
  $result['cats'] = getUserCats($userid);
  $result['usercat'] = getUserCatlist($userid);
  $result['purchase'] = getPurchaseList();
  // $result['outstock'] = getOutstock();
  // $result['expired'] = getExpired();
  
  return $result;
}

// function getOutstock() {
//   global $data, $db, $result;
  
//   $sql = "select * from pet_phc_item where shop + storage < border";
//   $number = $db->count($sql);
//   return $number;
// }

// function getExpired() {
//   global $data, $db, $result;

//   $time = time();
//   $sql = "select * from pet_phc_item_expire where expire < $time";
//   $number = $db->count($sql);
//   return $number;
// }

function getPosList() {
  global $db, $data;

  $sql = "select id, name from pet_phc_item_pos order by name asc";
  $list = $db->all($sql);
  foreach ($list as $key => $row) {
    $list[$key]['alias'] = lower($row['name']);
  }
  return $list;
}

function getSourceList() {
  global $db, $data;

  $sql = "select id, name from pet_phc_item_source order by name asc";
  $list = $db->all($sql);
  foreach ($list as $key => $row) {
    $list[$key]['alias'] = lower($row['name']);
  }
  return $list;
}

function getUserCat($userid) {
  global $db;
  $sql = "select value from pet_phc_config where module = 'itemcat' and name = $userid";
  return $db->arr($sql, 'value');
}

function getUserCats($userid) {
  global $db;
  $sql = "select b.name from pet_phc_config a inner join pet_phc_item_cat b on a.value = b.id where a.module = 'itemcat' and a.name = $userid";
  return implode(', ', $db->arr($sql, 'name'));
}

function getUserCatlist($userid) {
  global $db;
  $sql = "select b.* from pet_phc_config a inner join pet_phc_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $userid";
  return $db->all($sql);
}


function getUserList() {
  global $db;

  $sql = "select a.userid, b.name, b.username, b.fullname from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where a.module = 'item' and a.type > 0";
  $list = $db->all($sql);
  foreach ($list as $key => $row) {
    $sql = "select b.name from pet_phc_config a inner join pet_phc_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $row[userid]";
    $list[$key]['storage'] = implode(', ', $db->arr($sql, 'name'));
    $sql = "select b.id, b.name from pet_phc_config a inner join pet_phc_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $row[userid]";
    $list[$key]['per'] = $db->all($sql);
  }
  return $list;
}

function per() {
  global $data, $db, $result;

  if (count($data->cat)) {
    $cats = implode(', ', $data->cat);
    $sql = "delete from pet_phc_config where module = 'itemper' and name = $data->userid and value not in ($cats)";
  }
  else $sql = "delete from pet_phc_config where module = 'itemper' and name = $data->userid";
  $db->query($sql);
  foreach ($data->cat as $cat) {
    $sql = "select * from pet_phc_config where module = 'itemper' and name = $data->userid and value = $cat";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_phc_config (module, name, value) values('itemper', $data->userid, $cat)";
      $db->query($sql);
    }
  }
  
  $sql = "select b.name from pet_phc_config a inner join pet_phc_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $data->userid";
  $result['storage'] = implode(', ', $db->arr($sql, 'name'));
  $sql = "select b.id, b.name from pet_phc_config a inner join pet_phc_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $data->userid";
  $result['per'] = $db->all($sql);

  $result['status'] = 1;
  return $result;
}

function inpos() {
  global $data, $db, $result;

  $image = '';
  foreach ($data->image as $value) {
    if (strlen($value) > 50) $image = $value;
  }
  
  $sql = "insert into pet_phc_item_pos (name, image) values('$data->pos', '$image')";
  
  $result['status'] = 1;
  $result['id'] = $db->insertid($sql);
  $result['image'] = $image;

  return $result;
}

function inpositem() {
  global $data, $db, $result;

  foreach ($data->list as $key => $value) {
    $sql = "select * from pet_phc_item_pos_item where posid = $data->posid and itemid = $value->id";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_phc_item_pos_item (posid, itemid) values($data->posid, $value->id)";
      $db->query($sql);
    }
  }
  
  $sql = "select b.*, a.name from pet_phc_item a inner join pet_phc_item_pos_item b on a.id = b.itemid where b.posid = $data->posid";
  $result['list'] = $db->all($sql);
  $result['status'] = 1;
  
  return $result;
}

function filter() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getList();

  return $result;
}

function purchase_init() {
  global $data, $db, $result;

  $sql = "select name, storage + shop as number from pet_phc_item where storage + shop < border order by name asc";
  $list = $db->all($sql);

  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}

function insert() {
  global $data, $db, $result;

  $name_sql = "select * from pet_phc_item where name = '$data->name'";
  $code_sql = "select * from pet_phc_item where code = '$data->code'";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "insert into pet_phc_item (name, code, shop, storage, catid, border, image) values('$data->name', '$data->code', 0, 0, $data->cat, 10, '". implode(', ', $data->image) ."')";
    $id = $db->insertid($sql);
    foreach ($data->position as $key => $row) {
      $sql = "insert into pet_phc_item_pos_item (itemid, posid) values($id, $row->id)";
      $db->query($sql);
    }

    foreach ($data->source as $key => $row) {
      $sql = "insert into pet_phc_item_source_item (itemid, sourceid) values($id, $row->id)";
      $db->query($sql);
    }
  
    $result['status'] = 1;
    $result['list'] = getList();
  }
    
  return $result;
}

function position_init() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_item_pos order by name asc";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select b.*, a.name from pet_phc_item a inner join pet_phc_item_pos_item b on a.id = b.itemid where b.posid = $value[id]";
    $list[$key]['position'] = $db->all($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;
      
  return $result;
}

function position_remove() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_item_pos_item where posid = $data->id";
  $db->query($sql);

  $sql = "delete from pet_phc_item_pos where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_item where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function removeexpire() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_item_expire where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function repos() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_item_pos_item where posid = $data->posid and itemid = $data->itemid";
  $db->query($sql);
  
  $sql = "select b.*, a.name from pet_phc_item a inner join pet_phc_item_pos_item b on a.id = b.itemid where b.posid = $data->posid";
  $result['list'] = $db->all($sql);
  $result['status'] = 1;
  
  return $result;
}

function search() {
  global $data, $db, $result;

  $sql = "select name, code from pet_phc_item where name like '%$data->key%'";
  
  $result['status'] = 1;
  $result['list'] = $db->all($sql);

  return $result;
}

function transfer_init() {
  global $data, $db, $result;

  $sql = "select name, storage, shop from pet_phc_item where shop < border and storage > 0 order by name asc";
  $list = $db->all($sql);
  
  $result['status'] = 1;
  $result['list'] = $list;
  
  return $result;
}

function update() {
  global $data, $db, $result;

  $name_sql = "select * from pet_phc_item where name = '$data->name' and id <> $data->id";
  $code_sql = "select * from pet_phc_item where code = '$data->code' and id <> $data->id";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "update pet_phc_item set catid = $data->cat, name = '$data->name', code = '$data->code', border = '$data->border', image = '". implode(', ', $data->image) ."' where id = $data->id";
    $db->query($sql);
  
    $poslist = array();
    foreach ($data->position as $key => $row) {
      $poslist []= $row->id;
      $sql = "insert into pet_phc_item_pos_item (itemid, posid) values ($data->id, $row->id) on duplicate key update itemid = $data->id and posid = $row->id";
      $db->query($sql);
    }
    // xóa những vị trí không có trong danh sách
    $sql = "delete from pet_phc_item_pos_item where itemid = $data->id and posid not in (". implode(', ', $poslist) .")";
    $db->query($sql);

    $sourcelist = array();
    foreach ($data->source as $key => $row) {
      $sourcelist []= $row->id;
      $sql = "insert into pet_phc_item_source_item (itemid, sourceid) values ($data->id, $row->id) on duplicate key update itemid = $data->id and sourceid = $row->id";
      $db->query($sql);
    }
    // xóa những vị trí không có trong danh sách
    $sql = "delete from pet_phc_item_source_item where itemid = $data->id and sourceid not in (". implode(', ', $poslist) .")";
    $db->query($sql);

    $result['status'] = 1;
    $result['data'] = getItemInfo($data->id);
  }
  
  return $result;
}

function getItemInfo($id) {
  global $db;

  $sql = "select * from pet_phc_item where id = $id";
  $row = $db->fetch($sql);

  $row['alias'] = lower($row['name']);
  $row['image'] = explode(', ', $row['image']);
  $row['expired'] = checkExpire($row['id']);
  $sql = "select a.id, a.name from pet_phc_item_pos a inner join pet_phc_item_pos_item b on a.id = b.posid where b.itemid = $id";
  $row['position'] = $db->all($sql);
  return $row;
}

function uppos() {
  global $data, $db, $result;
  $image = '';
  foreach ($data->image as $value) {
    if (strlen($value) > 50) $image = $value;
  }

  $sql = "update pet_phc_item_pos set name = '$data->pos', image = '$image' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;

  return $result;
}

function getList() {
  global $data, $db;
  
  /**
   * kiểm tra có cat không
   * nếu có, cập nhật csdl
   * nếu không, lấy từ csdl
   * kết quả xtra phải trừ bỏ không có trong config
   */
  
  $userid = checkuserid();
  // $check = true;
  // if (isset($data->cat) && !empty($data->cat)) {
  //   // lọc danh loại hàng từ config
  //   $cat = implode(', ', $data->cat);
  //   $sql = "select * from pet_phc_config where module = 'itemper' and name = $userid and value in ($cat)";
  //   $cat = $db->arr($sql, 'value');
  //   if (count($cat)) {
  //     // có danh sách, hiển thị & cập nhật
  //     $check = false;
  //     $cats = implode(', ', $cat);
  //     $xtra = " a.catid in ($cats)";
  //     $sql = "update pet_phc_config set value = '$cats'";
  //     $db->query($sql);
  //   }
  // }
  // if ($check) {
  //   // không có, lấy từ csdl
  //   $sql = "select * from pet_phc_config where module = 'itemper' and name = $userid";
  //   $cat = $db->arr($sql, 'value');
  //   if (count($cat)) $xtra = ' a.catid in ('. implode(', ', $cat) .')';
  //   else $xtra = ' 0 ';
  // }

  $sql = "select a.*, b.name as cat from pet_phc_item a inner join pet_phc_item_cat b on a.catid = b.id order by a.name asc";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $list[$key]['alias'] = lower($value['name']);
    $list[$key]['image'] = explode(', ', $value['image']);
    $list[$key]['expired'] = checkExpire($value['id']);

    $sql = "select a.id, a.name from pet_phc_item_pos a inner join pet_phc_item_pos_item b on a.id = b.posid where b.itemid = $value[id]";
    $list[$key]['position'] = $db->all($sql);

    $sql = "select a.id, a.name from pet_phc_item_source a inner join pet_phc_item_source_item b on a.id = b.sourceid where b.itemid = $value[id]";
    $list[$key]['source'] = $db->all($sql);
  }

  return $list;
}

function removerecommend() {
  global $db, $data, $result;

  $sql = "delete from pet_phc_item_recommend where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getPurchaseList();
  return $result;
}

function outstock() {
  global $db, $data, $result;
 
  $userid = checkuserid();
  $time = time();
  $sql = "update pet_phc_item set outstock = $data->number, recuserid = $userid, rectime = $time where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['value'] = $data->number;
  return $result;
}

function updateoutstock() {
  global $db, $data, $result;
 
  $sql = "update pet_phc_item set outstock = $data->number where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['value'] = $data->number;
  return $result;
}

function removestock() {
  global $db, $data, $result;
  
  $sql = "update pet_phc_item set outstock = 0, recuserid = 0, rectime = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getPurchaseList();
  return $result;
}

function changeover() {
  global $db, $data, $result;
  
  $userid = checkuserid();
  $time = time();
  $sql = "update pet_phc_item set outstock = 0, recuserid = 0, rectime = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['value'] = 0;
  return $result;
}

function checkExpire($itemid) {
  global $db, $data;

  $sql = "select id, time as name from pet_phc_item_expire where rid = $itemid";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $list[$key]['name'] = date('d/m/Y', $value['name']);
  }
  return $list;
}

function getPurchase() {
  global $data, $db;

  $sql = "select count(*) as number from pet_phc_item where storage + shop < border";
  $number = $db->fetch($sql);
  return $number['number'];
}

function getTransfer() {
  global $data, $db;

  $sql = "select count(*) as number from pet_phc_item where shop < border and storage > 0";
  $number = $db->fetch($sql);
  return $number['number'];
}

function getExpire() {
  global $data, $db;

  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select count(*) as number from pet_phc_item_expire where expire < $limit";
  $number = $db->fetch($sql);
  return $number['number'];
}

function getCatList() {
  global $data, $db;
  
  $userid = checkuserid();
  $sql = "select * from pet_phc_item_cat order by id asc";
  return $db->all($sql);
}

function getSuggestList() {
  global $data, $db;
  
  $sql = "select id, name from pet_phc_item order by name asc";
  $list = $db->all($sql);
  foreach ($list as $key => $value) {
    $list[$key]['alias'] = lower($value['name']);
  }
  return $list;
}

function getImagePos() {
  global $data, $db;
  
  $sql = "select id, image from pet_phc_item_pos order by name asc";
  return $db->obj($sql, 'id', 'image');
}

function excel() {
  global $data, $db, $result, $_FILES;

  $dir = str_replace('/server', '/', ROOTDIR);
  // $des = $dir ."export/DanhSachChiTietHoaDon_KV09102021-222822-523-1633793524.xlsx";

  $raw = $_FILES['file']['tmp_name'];
  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
  $file_name = $name . "-". time() . ".". $ext;
  $des = $dir ."include/export/$file_name";

  move_uploaded_file($raw, $des);

  $x = array();
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }

  include $dir .'include/PHPExcel/IOFactory.php';
    
  $inputFileType = PHPExcel_IOFactory::identify($des);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $col = array(
    'Mã hàng' => '', // 0
    'Bệnh viện' => '', // 2
    'Kho' => '', // 3
  );

  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $val = $sheet->getCell($xr[$j] . '1')->getValue();
    foreach ($col as $key => $value) {
      if ($key == $val) $col[$key] = $j;
    }
  }

  $exdata = array();
  for ($i = 2; $i <= $highestRow; $i ++) { 
    $temp = array();
    foreach ($col as $key => $j) {
      $val = $sheet->getCell($xr[$j] . $i)->getValue();
      $temp []= $val;
    }
    $exdata []= $temp;
  }

  $res = array(
    'on' => 1,
    'total' => 0, 'insert' => 0
  );
  $l = array();

  $sql = "select * from pet_phc_item";
  $item = $db->all($sql);
  foreach ($exdata as $row) {
    foreach ($item as $i) {
      if ($i['code'] == $row[0]) {
        $l []= $row;
        break;
      }
    }
  }

  foreach ($l as $row) {
    $res['total'] ++;
    $xtra = '';
    if (!empty($row[3])) $xtra = ", image = '$row[3]'";
    $n = $row[1] + $row[2];
    $sql = "update pet_phc_item set lowonnumber = 1 where $n >= border";
    $db->query($sql);
    $sql = "update pet_phc_item set lowonnumber = 0 where $n < border";
    $db->query($sql);
    $sql = "update pet_phc_item set shop = $row[1], storage = $row[2] $xtra where code = '$row[0]'";
    if ($db->query($sql)) $res['insert'] ++;
  }

  if (file_exists($des)) {
    unlink($des);
  }

  $result['messenger'] = "Đã chuyển dữ liệu Excel thành phiếu nhắc";
  $result['data'] = $res;
  return $result;
}

function excel() {
  global $data, $db, $result, $_FILES;

  $dir = str_replace('/server', '/', ROOTDIR);
  // $des = $dir ."export/DanhSachChiTietHoaDon_KV09102021-222822-523-1633793524.xlsx";

  $raw = $_FILES['file']['tmp_name'];
  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
  $file_name = $name . "-". time() . ".". $ext;
  $des = $dir ."include/export/$file_name";

  move_uploaded_file($raw, $des);

  $x = array();
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }

  include $dir .'include/PHPExcel/IOFactory.php';
    
  $inputFileType = PHPExcel_IOFactory::identify($des);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  // $col = array(
  //   'Mã hàng' => '', // 0
  //   'Bệnh viện' => '', // 2
  //   'Kho' => '', // 3
  // );

  $col = array(
    'category' => '', // 0
    'code' => '', // 1
    'name' => '', // 2
    'shop' => '', // 3
    'storage' => '', // 4
    'image' => '', // 4
  );

  // khởi tạo lấy dữ liệu
  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $val = $sheet->getCell($xr[$j] . '1')->getValue();
    foreach ($col as $key => $value) {
      if ($key == $val) $col[$key] = $j;
    }
  }

  // lấy dữ liệu
  $exdata = array();
  for ($i = 2; $i <= $highestRow; $i ++) { 
    $temp = array();
    foreach ($col as $key => $j) {
      $val = $sheet->getCell($xr[$j] . $i)->getValue();
      $temp []= $val;
    }
    $exdata []= $temp;
  }

  $sql = "select * from pet_phc_storage_exchange";
  $category = $db->obj($sql, 'exchange', 'storageid');
  $list = array();
  $res = array(
    'on' => 1,
    'total' => 0, 'insert' => 0
  );
  // $l = array();

  foreach ($exdata as $key => $row) {
    $res['total'] ++;
    // lọc những trường có trong category
    if (!empty($category[$row[0]])) {
      // kiểm tra có mã chưa, nếu có thì thêm vào
      $sql = "select a.id from pet_phc_product a inner join pet_phc_product_code b on b.code = '$row[1]' and a.id = b.proid";
      if (empty($product = $db->fetch($sql))) {
        $sql = "insert into pet_phc_product (name, image, shop, storage, outstock) values('$row[2]', '$row[5]', 0, 0, 0)";
        $product = array('id' => $db->insertid($sql));
        $sql = "insert into pet_phc_product_code (proid, code) values($product[id], '$row[1]')";
        $db->query($sql);
      }
      // nếu chưa có trong list thì push, có rồi thì sum
      if (empty($list[$product['id']])) $list[$product['id']] = $row;
      else {
        $list[$product['id']][3] += $row[3];
        $list[$product['id']][4] += $row[4];
      }
    }
  }

  // mã exchange 
  $sql = "select * from pet_phc_product_exchange";
  $exchange = $db->all($sql);
  foreach ($exchange as $row) {
    if (!empty($list[$row['proid']]) && !empty($list[$row['targetid']])) {
      $list[$row['proid']][3] += number_format($list[$row['targetid']][3] / $row['exchange'], 1, '', '');
      $list[$row['proid']][4] += number_format($list[$row['targetid']][4] / $row['exchange'], 1, '', '');
      $list[$row['targetid']][3] = 0;
      $list[$row['targetid']][4] = 0;
    }
  }

  foreach ($list as $id => $row) {
    $sql = "update pet_phc_product set shop = $row[3], storage = $row[4], image = '$row[5]' where id = $id";
    if ($db->query($sql)) $res['insert'] ++;
  }

  // $sql = "select * from pet_phc_item";
  // $item = $db->all($sql);
  // foreach ($exdata as $row) {
  //   foreach ($item as $i) {
  //     // có trong danh sách
  //     if ($i['code'] == $row[0]) {
  //       $l []= $row;
  //       break;
  //     }
  //     else if ($row['3'] > 0 && $i['code'] != $row[0]) {
  //       // không có trong danh sách nhưng kho có hàng thì thêm vào
  //       $sql = "insert into pet_phc_item (catid, name, code, shop, storage, border, image, outstock, recuserid, rectime, lowonnumber) values(1, '$row[1]', '$row[0]', $row[2], $row[3], 0, '', 0, 0, 0, 0)";
  //       $db->query($sql);
  //       $l []= $row;
  //     }
  //   }
  // }

  // foreach ($l as $row) {
  //   $res['total'] ++;
  //   $xtra = '';
  //   $n = $row[2] + $row[3];
  //   $sql = "update pet_phc_item set lowonnumber = 1 where $n >= border";
  //   $db->query($sql);
  //   $sql = "update pet_phc_item set lowonnumber = 0 where $n < border";
  //   $db->query($sql);
  //   $sql = "update pet_phc_item set shop = $row[2], storage = $row[3] $xtra where code = '$row[0]'";
  //   if ($db->query($sql)) $res['insert'] ++;
  // }

  if (file_exists($des)) {
    unlink($des);
  }

  $result['messenger'] = "Đã chuyển dữ liệu Excel thành phiếu nhắc";
  $result['data'] = $res;
  return $result;
}
