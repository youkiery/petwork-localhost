<?php
define('SERVERSIDE', 0);
define('CLIENTSIDE', 1);

function add() {
  global $data, $db, $result;

  $sql = "insert into pet_phc_xray_his (petid, his, time) values($data->petid, '$data->his', ". time() .")";
  $db->query($sql);
  
  $sql = "select * from pet_phc_xray_his where petid = $data->petid";
  $his = $db->obj($sql, 'id', 'his');
  
  $result['status'] = 1;
  $result['his'] = implode(', ', $his);
  
  return $result;
}

function confirm() {
  global $data, $db, $result;

  // thêm vào liệu trình
  $time = isodatetotime($data->time);
  if ($data->id) {
    $sql = "insert into pet_phc_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($data->id, $data->userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(', ', $data->image) ."', '$data->status', $time)";
    $db->query($sql);
  }
  else {
    $petid = checkpet();
    $sql = "insert into pet_phc_xray(petid, doctorid, insult, time) values($petid, $data->userid, 0, $time)";
    $id = $db->insertid($sql);
    
    $sql = "insert into pet_phc_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($id, $data->userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(', ', $data->image) ."', '$data->status', $time)";
    $db->query($sql);
  }

  // xóa phiếu tạm
  $sql = "delete from pet_phc_his_temp where id = $data->tempid";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getManager();
  return $result;
}

function temp() {
  global $data, $db, $result;

  $sql = "select a.*, b.id as petid, b.name as pet, c.name as customer, c.phone, d.name as doctor from pet_phc_xray a inner join pet_phc_pet b on a.petid = b.id inner join pet_phc_customer c on b.customerid = c.id inner join pet_phc_users d on a.doctorid = d.userid where a.insult = 0 order by id desc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $sql = "select * from pet_phc_xray_row where xrayid = $row[id] order by id desc limit 1";
    $r = $db->fetch($sql);
    $list[$key]['eye'] = (empty($r['eye']) ? '' : $r['eye']);
    $list[$key]['temperate'] = (empty($r['temperate']) ? '' : $r['temperate']);
    $list[$key]['other'] = (empty($r['other']) ? '' : $r['other']);
    $list[$key]['status'] = (empty($r['status']) ? 0 : $r['status']);
  }

  $temp = array();
  $left = array();
  foreach ($list as $row) {
    // echo $row['phone'] . ": $data->phone <br>";
    if ($row['phone'] == $data->phone) $temp []= $row;
    else $left []= $row;
  }

  foreach ($left as $row) {
    $temp []= $row;
  }

  $result['status'] = 1;
  $result['list'] = $temp;
  return $result;
}

function manager() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getManager();
  return $result;
}

function getManager() {
  global $data, $db;

  $userid = checkuserid();
  $sql = "select * from pet_phc_user_per where userid = $userid and module = 'his'";
  $role = $db->fetch($sql);
  if ($role['type'] > 1) $sql = "select a.*, b.name as user from pet_phc_his_temp a inner join pet_phc_users b on a.userid = b.userid";
  else $sql = "select a.*, b.name as user from pet_phc_his_temp a inner join pet_phc_users b on a.userid = b.userid where a.userid = $userid";

  return $db->all($sql);
}

function update() {
  global $data, $db, $result;

  $data->time = isodatetotime($data->time);

  $sql = "update pet_phc_xray set pos = $data->pos where id = $data->id";
  $db->query($sql);

  $arr = array('xquang','sinhly','sinhhoa','sieuam','nuoctieu');
  $sql = "select * from pet_phc_xray_row where id = $data->detailid";
  $detail = $db->fetch($sql);
  $list = array();
  $time = time();
  $userid = checkuserid();
  $customerid = checkcustomer();
  // kiểm tra arr, nếu detailid > 0, bỏ qua, nếu = 0, cập nhật = 0, nếu != 0, kiểm tra detail > 0 ? bỏ qua : = -1
  foreach ($arr as $name) {
    if ($detail[$name] <= 0) {
      if ($data->{$name} == 0) $list []= $name . " = 0";
      else if ($data->{$name} > 0) $list []= $name . " = -1";
    }
  }
  $xtra = '';
  if (count($list)) $xtra = implode(', ', $list) . ', ';
  $image = implode(', ', $data->image);

  $sql = "update pet_phc_xray_row set $xtra eye = '$data->eye', temperate = '$data->temperate', other = '$data->other', treat = '$data->treat', status = '$data->status', image = '$image', time = $data->time where id = $data->detailid";
  $db->query($sql);

  foreach ($data->exam as $exam) {
    // kiểm tra xem có hay chưa, chưa thì insert
    $sql = "select * from pet_phc_exam where treatid = $data->detailid and typeid = $exam->id";
    if (empty($row = $db->fetch($sql))) {
      $sql = "insert into pet_phc_exam (userid, customerid, treatid, typeid, image, note, time) values($userid, $customerid, $data->detailid, $exam->id, '', '', $time)";
      $db->query($sql);
    }
  }

  if (!empty($data->near)) {
    $time = isodatetotime($data->near);
    $ctime = time();
    // tìm kiếm xem sau đó có lịch tái khám ngày này không, nếu có thì cập nhật, nếu không thì thêm
    $sql = "select * from pet_phc_his_schedule where customerid = $customerid and time > $ctime and status = 0";
    if (!empty($r = $db->fetch($sql))) $sql = "update pet_phc_his_schedule set time = $time where id = $r[id]";
    else $sql = "insert into pet_phc_his_schedule (customerid, note, image, time, ctime) values($customerid, '', '$image', $time, $ctime)";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);
  $result['near'] = nearlist();
  $result['c'] = nearcount();
  return $result;
}

function getChatTotalCount($data) {
  $count = array(0 => 0, 0);
  foreach ($data as $row) {
    $count[$row['pos']] += $row['chat'];
  }
  return $count;
}

function statistic() {
  global $data, $db, $result;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $userid = checkuserid();
  $sql = "select * from pet_phc_user_per where userid = $userid and module = 'his' and type = 2";
  $xtra = "";
  if (empty($p = $db->fetch($sql))) $xtra = "a.doctorid = $userid and";

  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.name as doctor from pet_phc_xray a inner join pet_phc_pet b on a.petid = b.id inner join pet_phc_customer c on b.customerid = c.id inner join pet_phc_users d on a.doctorid = d.userid where $xtra (a.time between $data->start and $data->end) order by id desc";
  $list = $db->all($sql);
  $data = array();
  
  foreach ($list as $key => $value) {
    if (empty($data[$value['doctorid']])) $data[$value['doctorid']] = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 'name' => $value['doctor']);
    $data[$value['doctorid']] [$value['insult']] ++;
    $data[$value['doctorid']] [3] ++;
    $data[$value['doctorid']] [4] += $value['rate'];
  }
  
  $stat = array();
  
  foreach ($data as $key => $value) {
    if ($value[2] > $value[1]) $data[$key]['balance'] = 2;
    else if ($value[1] > $value[2]) $data[$key]['balance'] = 1;
    else $data[$key]['balance'] = 0;
    $stat []= $data[$key];
  }
  
  usort($data, "ratest");

  $result['status'] = 1;
  $result['data'] = $stat;
  
  return $result;
}

function ratest($a, $b) {
  return $a[4] > $b[4];
}

function returned() {
  global $data, $db, $result;

  $sql = "update pet_phc_xray set insult = 1 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['insult'] = 1;
  
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_xray where id = $data->id";
  $db->query($sql);
  
  $sql = "delete from pet_phc_xray_row where xrayid = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa hồ sơ';
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);
  
  return $result;
}

function removesc() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_his_schedule where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa hồ sơ';
  $result['list'] = nearlist();
  $result['c'] = nearcount();
  
  return $result;
}

function pet() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_customer where phone = '$data->phone'";

  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_phc_customer (name, phone, addess) values('$data->name', '$data->phone', '')";
    $c['id'] = $db->insertid($sql);
  }
  
  $sql = "select * from pet_phc_pet where id = $data->pet";
  if (empty($p = $db->fetch($sql))) {
    $sql = "insert into pet_phc_pet (name, customerid) values('Chưa đặt tên', $c[id])";
    $p['id'] = $db->insertid($sql);
  }
  
  $sql = "select id, name from pet_phc_pet where customerid = $c[id]";
  $list = $db->all($sql);
  
  $result['status'] = 1;
  $result['petid'] = $p['id'];
  $result['petlist'] = $list;
  
  return $result;
}

function nearchange() {
  global $data, $db, $result;

  $status = intval(!$data->status);
  $sql = "update pet_phc_his_schedule set status = $status where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = nearlist();
  $result['count'] = nearcount();
  return $result;
}

function near() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = nearlist();
  return $result;
}

function nearcount() {
  global $data, $db, $result;

  // Nhắc hôm nay ngày mai
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 * 2 - 1;

  $sql = "select id from pet_phc_his_schedule where ((time between $start and $end) and status = 0) or status = 0";
  return $db->count($sql);
}

function nearlist() {
  global $data, $db, $result;

  // Nhắc hôm nay ngày mai
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 * 2 - 1;

  $sql = "select a.*, b.name, b.phone from pet_phc_his_schedule a inner join pet_phc_customer b on a.customerid = b.id where (time between $start and $end) or status = 0 order by time asc, ctime asc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['image'] = parseimage($row['image']);
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['ctime'] = date('d/m/Y', $row['ctime']);
  }

  return $list;
}

function schedule() {
  global $data, $db, $result;

  $customerid = checkcustomer($data->phone, $data->name);
  $image = implode(', ', $data->image);
  $time = isodatetotime($data->time);
  $ctime = time();
  
  $sql = "insert into pet_phc_his_schedule (customerid, note, image, time, ctime) values($customerid, '$data->note', '$image', $time, $ctime)";
  $db->query($sql);

  $result['near'] = nearlist();
  $result['count'] = nearcount();
  $result['status'] = 1;
  return $result;
}

function updatesc() {
  global $data, $db, $result;

  $customerid = checkcustomer($data->phone, $data->name);
  $image = implode(', ', $data->image);
  $time = isodatetotime($data->time);
  $ctime = time();
  
  $sql = "update pet_phc_his_schedule set customerid = $customerid, note = '$data->note', image = '$image', time = $time where id = $data->id";
  $db->query($sql);

  $result['near'] = nearlist();
  $result['count'] = nearcount();
  $result['status'] = 1;
  return $result;
}

function insert() {
  global $data, $db, $result;

  $petid = checkpet();
  $userid = checkuserid();
  $customerid = checkcustomer();
  $data->time = isodatetotime($data->time);
  $time = time();
  $sql = "insert into pet_phc_xray(petid, doctorid, insult, time, pos) values($petid, $userid, 0, $data->time, $data->pos)";
  $id = $db->insertid($sql);
  $arr = array('0' => '0', '1' => '-1');
  $data->xquang = $arr[$data->xquang];
  $data->sinhly = $arr[$data->sinhly];
  $data->sinhhoa = $arr[$data->sinhhoa];
  $data->sieuam = $arr[$data->sieuam];
  $data->nuoctieu = $arr[$data->nuoctieu];
  $image = implode(', ', $data->image);
  
  $sql = "insert into pet_phc_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu) values($id, $userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '$image', '$data->status', $data->time, $data->xquang, $data->sinhly, $data->sinhhoa, $data->sieuam, $data->nuoctieu)";
  $id = $db->query($sql);

  foreach ($data->exam as $exam) {
    $sql = "insert into pet_phc_exam (userid, customerid, treatid, typeid, image, note, time) values($userid, $customerid, $id, $exam->id, '', '', $time)";
    $db->query($sql);
  }

  if (!$data->near) {
    $sql = "insert into pet_phc_his_schedule (customerid, note, image, time) values($customerid, '', '$image', $time)";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);
  $result['near'] = nearlist();
  $result['c'] = nearcount();
  return $result;
}

function checkcustomer() {
  global $db, $data;

  $sql = "select * from pet_phc_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_phc_customer set name = '$data->name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_phc_customer (name, phone, address) values ('$data->name', '$data->phone', '')";
    $customer['id'] = $db->insertid($sql);
  }

  return $customer['id'];
}


function filter() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);
  $result['type'] = gettypelist();
  $result['near'] = nearlist();
  $result['c'] = nearcount();
  return $result;
}

function gettypelist() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_exam_type where active = 1 order by name desc";
  $list = $db->all($sql);
  return $list;
}

function detail() {
  global $data, $db, $result;

  $userid = checkuserid();
  $data->time = isodatetotime($data->time);
  
  $sql = "update pet_phc_xray set pos = $data->pos where id = $data->id";
  $db->query($sql);
  $arr = array('0' => '0', '1' => '-1');
  $data->xquang = $arr[$data->xquang];
  $data->sinhly = $arr[$data->sinhly];
  $data->sinhhoa = $arr[$data->sinhhoa];
  $data->sieuam = $arr[$data->sieuam];
  $data->nuoctieu = $arr[$data->nuoctieu];

  $sql = "insert into pet_phc_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu) values($data->id, $userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(', ', $data->image) ."', '$data->status', $data->time, $data->xquang, $data->sinhly, $data->sinhhoa, $data->sieuam, $data->nuoctieu)";
  $id = $db->insertid($sql);
  
  $sql = "select a.*, b.name as doctor, a.time from pet_phc_xray_row a inner join pet_phc_users b on a.doctorid = b.userid where a.id = $id order by time asc";
  $row = $db->fetch($sql);
  $row['time'] = date('d/m/Y', $row['time']);
  
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);
  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_phc_xray set insult = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['insult'] = 2;

  return $result;
}

function pay() {
  global $db, $data, $result;

  $sql = "update pet_phc_xray_row set pay = $data->pay where id = $data->detailid";
  $db->query($sql);
  $result['status'] = 1;
  return $result;
}

function move() {
  global $db, $data, $result;
  $reversal = array(0 => 1, 0);
  $rev = $reversal[$data->pos];

  $sql = "update pet_phc_xray set pos = $rev where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getList();
  $result['count'] = getChatTotalCount($result['list']);
  return $result;
}

function hopital() {
  global $db, $data, $result;

  $sql = "update pet_phc_xray set insult = 0, rate = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getList();
  $result['count'] = getChatTotalCount($result['list']);
  return $result;
}

function share() {
  global $db, $data, $result;

  $sql = "update pet_phc_xray set share = ". intval(!$data->share) ." where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getList();
  $result['count'] = getChatTotalCount($result['list']);
  return $result;
}

function getlist($id = 0) {
  global $db, $data;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;
  $userid = checkuserid();
  $xtra = array();

  $sql = "select * from pet_phc_user_per where userid = $userid and module = 'his'";
  $role = $db->fetch($sql);
  if ($role['type'] < 2) $xtra []= " (a.doctorid = $userid or a.share = 1 or pos = 1) ";
  else if (isset($data->{'docs'}) && !empty($data->docs)) {
    if (empty($data->docscover)) $data->docscover = '';
    $docs = implode(', ', $data->docs);
    $sql = "update pet_phc_config set value = '$docs' where module = 'docs' and name = '$userid'";
    $db->query($sql);
    $sql = "update pet_phc_config set value = '$data->docscover' where module = 'docscover' and name = '$userid'";
    $db->query($sql);
    $xtra []= " a.doctorid in ($docs) ";
  }

  if (count($xtra)) $xtra = implode(" and ", $xtra) . "and";
  else $xtra = "";

  $sql = "select a.*, b.id as petid, b.name as pet, b.customerid, c.name as customer, c.phone, d.name as doctor from pet_phc_xray a inner join pet_phc_pet b on a.petid = b.id inner join pet_phc_customer c on b.customerid = c.id inner join pet_phc_users d on a.doctorid = d.userid where $xtra ((a.time between $data->start and $data->end) or (a.time < $data->start and a.insult = 0)) ". ($id ? " and a.id = $id " : '') ." and (c.phone like '%$data->keyword%') order by a.insult asc, id desc";
  $list = $db->all($sql);
  $time = strtotime(date('Y/m/d')) + 60 * 60 * 24;
  
  foreach ($list as $key => $value) {
    $list[$key]['chat'] = getChatCount($value['id']);
    $sql = "select a.*, b.name as doctor from pet_phc_xray_row a inner join pet_phc_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time desc";
    $row = $db->all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
      $image = explode(', ', $detail['image']);
      if (count($image) == 1 && $image[0] == '') $row[$index]['image'] = array();
      else $row[$index]['image'] = $image;
      $sql = "select a.image, a.note, a.id, a.status, b.name from pet_phc_exam a inner join pet_phc_exam_type b on a.typeid = b.id where treatid = $detail[id]";
      $row[$index]['exam'] = $db->all($sql);
      foreach ($row[$index]['exam'] as $examindex => $exam) {
        $image = explode(', ', $exam['image']);
        if (count($image) == 1 && $image[0] == '') $row[$index]['exam'][$examindex]['image'] = array();
        else $row[$index]['exam'][$examindex]['image'] = $image;
      }
    }

    $sql = "select * from pet_phc_his_schedule where customerid = $value[customerid] and status = 0 and time > $time";
    $sc = $db->fetch($sql);
    if (empty($sc)) $sc = '';
    else $sc = date('d/m/Y', $sc['time']);
    $list[$key]['near'] = $sc;
  
    $sql = "select * from pet_phc_xray_his where petid = $value[petid]";
    $his = $db->obj($sql, 'id', 'his');

    if (count($row)) {
      $list[$key]['status'] = $row[count($row) - 1]['status'];
      $list[$key]['time'] = $row[0]['time'];
    }
    else {
      $list[$key]['status'] = 0;
      $list[$key]['time'] = date('d/m/Y');
    }
    $list[$key]['rate'] = intval($value['rate']);
    $list[$key]['detail'] = $row;
    $list[$key]['his'] = implode(', ', $his);
  }
  return $list;
}

function getChatList() {
  global $db, $data;

  $time = time();
  $sql = "update pet_phc_xray_read set time = $time where side = 0 and postid = $data->id";
  $db->query($sql);

  $sql = "select * from pet_phc_xray_chat where postid = $data->id";
  return $db->all($sql);  
}

function getReadtime($id, $side) {
  global $db;

  $sql = "select * from pet_phc_xray_read where side = $side and postid = $id";
  if (empty($r = $db->fetch($sql))) {
    $sql = "insert into pet_phc_xray_read (side, postid, time) values(0, $id, 0)";
    $db->query($sql);
    return 0;
  }
  return $r['time'];
}

function getchat() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['list'] = getChatList();
  return $result;  
}

function postchat() {
  global $db, $data, $result;

  $time = time();
  $sql = "insert into pet_phc_xray_chat (postid, side, time, text) values($data->id, 0, $time, '$data->text')";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getChatList();
  return $result;  
}

function getChatCount($id) {
  global $db, $data;

  $readtime = getReadtime($id, SERVERSIDE);
  $sql = "select id from pet_phc_xray_chat where postid = $id and side = 1 and time > $readtime";
  return $db->count($sql);
}

function statrate() {
  global $data, $db, $result;

  $sql = "update pet_phc_xray set rate = $data->rate where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);

  return $result;
}

function checkpet() {
  global $data, $db;
  $sql = "select * from pet_phc_customer where phone = '$data->phone'";

  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_phc_customer (name, phone, address) values('$data->name', '$data->phone', '')";
    $c['id'] = $db->insertid($sql);
  }
  else {
    $sql = "update pet_phc_customer set name = '$data->name' where id = $c[id]";
    $db->query($sql);
  }

  if (empty($data->petid)) {
    $sql = "select * from pet_phc_pet where name = '$data->pet' and customerid = '$c[id]'";
    if (empty($p = $db->fetch($sql))) {
      $sql = "insert into pet_phc_pet (name, customerid) values('$data->pet', $c[id])";
      $p['id'] = $db->insertid($sql);
    }
    return $p['id'];
  }
  else {
    $sql = "update pet_phc_pet set name = '$data->pet' where id = $data->petid";
    $db->query($sql);
    return $data->petid;
  }
}
