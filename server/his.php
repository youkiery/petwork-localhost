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
  $result['his'] = implode(',', $his);
  
  return $result;
}

// function confirm() {
//   global $data, $db, $result;

//   // thêm vào liệu trình
//   $time = isodatetotime($data->time);
//   if ($data->id) {
//     $sql = "insert into pet_phc_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($data->id, $data->userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(',', $data->image) ."', '$data->status', $time)";
//     $db->query($sql);
//   }
//   else {
//     $petid = checkpet();
//     $sql = "insert into pet_phc_xray(petid, doctorid, insult, time) values($petid, $data->userid, 0, $time)";
//     $id = $db->insertid($sql);
    
//     $sql = "insert into pet_phc_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($id, $data->userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(',', $data->image) ."', '$data->status', $time)";
//     $db->query($sql);
//   }

//   // xóa phiếu tạm
//   $sql = "delete from pet_phc_his_temp where id = $data->tempid";
//   $db->query($sql);

//   $result['status'] = 1;
//   $result['list'] = getManager();
//   return $result;
// }

function printer() {
  global $data, $db, $result;

  $html = file_get_contents ( DIR. 'include/template3.php');

  $sql = "select * from pet_phc_xray_row where id = $data->id";
  $detail = $db->fetch($sql);

  $sql = "select * from pet_phc_xray where id = $detail[xrayid]";
  $xray = $db->fetch($sql);

  $sql = "select * from pet_phc_users where userid = $detail[doctorid]";
  $doctor = $db->fetch($sql);
  $names = explode(' ', $doctor['fullname']);
  $count = count($names);
  $doctor['halfname'] = mb_strtoupper($names[$count - 2] . ' ' . $names[$count - 1]);

  $sql = "select * from pet_phc_config where id = $doctor[placeid]";
  if (empty($place = $db->fetch($sql))) $place = array('name' => '');

  $sql = "select * from pet_phc_customer where id = $xray[customerid]";
  $customer = $db->fetch($sql);
  $sex = array(0 => '', 'Đực', 'Cái');

  $html = str_replace('{place}', $place['name'], $html);
  $html = str_replace('{doctor}', $doctor['halfname'], $html);
  $html = str_replace('{customer}', $customer['name'], $html);
  $html = str_replace('{address}', $customer['address'], $html);
  $html = str_replace('{phone}', $customer['phone'], $html);
  $html = str_replace('{petname}', $xray['petname'], $html);
  $html = str_replace('{weight}', $xray['weight'], $html);
  $html = str_replace('{age}', $xray['age'], $html);
  $html = str_replace('{species}', $xray['species'], $html);
  $html = str_replace('{gender}', $sex[$xray['gender']], $html);
  $html = str_replace('{temperate}', $detail['temperate'], $html);
  $lim = 65;
  $treat = nl2br($detail['other']);
  $treats = explode('<br />', $treat);
  for ($i = 0; $i < 3; $i++) { 
    $t = (isset($treats[$i]) ? $treats[$i] : '');
    $html = str_replace('{treat'. ($i + 1) .'}', $t, $html);
  }
  $treat = nl2br($detail['subother']);
  $treats = explode('<br />', $treat);
  for ($i = 0; $i < 3; $i++) { 
    $t = (isset($treats[$i]) ? $treats[$i] : '');
    $html = str_replace('{treating'. ($i + 1) .'}', $t, $html);
  }
  $treat = nl2br($detail['conclude']);
  $treats = explode('<br />', $treat);
  for ($i = 0; $i < 3; $i++) { 
    $t = (isset($treats[$i]) ? $treats[$i] : '');
    $html = str_replace('{result'. ($i + 1) .'}', $t, $html);
  }
  $treat = nl2br($detail['treat']);
  $treats = explode('<br />', $treat);
  for ($i = 0; $i < 5; $i++) { 
    $t = (isset($treats[$i]) ? $treats[$i] : '');
    $html = str_replace('{drug'. ($i + 1) .'}', $t, $html);
  }

  // $p = array();
  // if ($detail['sieuam'] != '0') $p []= '☑ Siêu âm';
  // if ($detail['xquang'] != '0') $p []= '☑ X Quang';
  // if ($detail['sinhly'] != '0') $p []= '☑ Sinh lý';
  // if ($detail['sinhhoa'] != '0') $p []= '☑ Sinh hóa';
  // if ($detail['nuoctieu'] != '0') $p []= '☑ Nước tiểu';
  // $sql = "select b.name from pet_phc_exam a inner join pet_phc_exam_type b on a.typeid = b.id where a.treatid = $data->id";
  // $list = $db->all($sql);

  // foreach ($list as $row) {
  //   $p []= '☑ '. $row['name'];
  // }

  // for ($i = 0; $i < 6; $i++) { 
  //   $t = (isset($p[$i]) ? $p[$i] : '');
  //   $html = str_replace('{a'. $i .'}', $t, $html);
  // }

  $html = str_replace('{date}', date('d', $detail['time']), $html);
  $html = str_replace('{month}', date('m', $detail['time']), $html);
  $html = str_replace('{year}', date('Y', $detail['time']), $html);
  $html = str_replace('{doctor2}', $doctor['fullname'], $html);

  $result['status'] = 1;
  $result['html'] = $html;
  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "update pet_phc_xray set pos = $data->pos, diseaseid = $data->diseaseid, petname = '$data->petname', weight = '$data->weight', age = '$data->age', gender = $data->gender, species = '$data->species' where id = $data->id";
  $db->query($sql);

  if (!empty($data->near)) {
    $near = isodatetotime($data->near);
    $sql = "select * from pet_phc_xray_schedule where status = 0";
    if (!empty($sc = $db->fetch($sql))) $sql = "update pet_phc_xray_schedule set time = $near where id = $sc[id]";
    else $sql = "insert into pet_phc_xray_schedule (xrayid, time, status) values($data->id, $near, 0)";
  }
  else $sql = "delete from pet_phc_xray_schedule where status = 0 and xrayid = $data->id";
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
  if (count($list)) $xtra = implode(',', $list) . ', ';
  $image = implode(',', $data->image);

  $time = isodatetotime($data->time);
  $sql = "update pet_phc_xray_row set $xtra temperate = '$data->temperate', other = '$data->other', treat = '$data->treat', status = '$data->status', image = '$image', conclude = '$data->conclude', subother = '$data->subother', time = $time where id = $data->detailid";
  $db->query($sql);

  foreach ($data->exam as $exam) {
    // kiểm tra xem có hay chưa, chưa thì insert
    $sql = "select * from pet_phc_exam where treatid = $data->detailid and typeid = $exam->id";
    if (empty($row = $db->fetch($sql))) {
      $sql = "insert into pet_phc_exam (userid, customerid, treatid, typeid, image, note, time) values($userid, $customerid, $data->detailid, $exam->id, '', '', $time)";
      $db->query($sql);
    }
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
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

  $sql = "select a.*, c.name as customer, c.phone, d.fullname as doctor from pet_phc_xray a inner join pet_phc_customer c on a.customerid = c.id inner join pet_phc_users d on a.doctorid = d.userid where $xtra (a.time between $data->start and $data->end) order by id desc";
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

function savedisease() {
  global $data, $db, $result, $userid;
  
  foreach ($data->list as $row) {
    $sql = "update pet_phc_xray_disease set name = '$row->name' where id = $row->id";
    $db->query($sql);
  }

  if (!empty($data->key)) {
    $sql = "insert into pet_phc_xray_disease (name) values('$data->key')";
    $db->query($sql);
  }
  // $sql = "delete fron pet_phc_xray_disease where id >= $index";
  // $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getdiseaselist();
  return $result;
}

function removedisease() {
  global $data, $db, $result, $userid;
  
  $sql = "update pet_phc_xray_disease set active = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getdiseaselist();
  return $result;
}

function insertdisease() {
  global $data, $db, $result, $userid;
  
  $sql = "insert into pet_phc_xray_disease (name) values('$data->key')";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getdiseaselist();
  return $result;
}

function insert() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  $userid = checkuserid();
  $data->time = isodatetotime($data->time);
  $time = time();
  $sql = "insert into pet_phc_xray (customerid, doctorid, insult, time, pos, diseaseid, petname, age, gender, species, weight) values($petid, $userid, 0, $data->time, $data->pos, $data->diseaseid, '$data->petname', '$data->age', '$data->gender', '$data->species', '$data->weight')";
  $id = $db->insertid($sql);
  $arr = array('0' => '0', '1' => '-1');
  $data->xquang = $arr[$data->xquang];
  $data->sinhly = $arr[$data->sinhly];
  $data->sinhhoa = $arr[$data->sinhhoa];
  $data->sieuam = $arr[$data->sieuam];
  $data->nuoctieu = $arr[$data->nuoctieu];
  $image = implode(',', $data->image);

  $sql = "insert into pet_phc_xray_row (xrayid, doctorid, subother, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu, conclude) values($id, $userid, '$data->subother', '$data->temperate', '$data->other', '$data->treat', '$image', '$data->status', $data->time, $data->xquang, $data->sinhly, $data->sinhhoa, $data->sieuam, $data->nuoctieu, '$data->conclude')";
  $id = $db->query($sql);

  if (!empty($data->near)) {
    $near = isodatetotime($data->near);
    $sql = "insert into pet_phc_xray_schedule (xrayid, time, status) values($id, $near, 0)";
    $db->query($sql);
  }

  foreach ($data->exam as $exam) {
    $sql = "insert into pet_phc_exam (userid, customerid, treatid, typeid, image, note, time) values($userid, $customerid, $id, $exam->id, '', '', $time)";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
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
  $result['type'] = gettypelist();
  $result['disease'] = getdiseaselist();
  return $result;
}

function getdiseaselist() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_xray_disease where active = 1 order by id asc";
  $list = $db->all($sql);
  return $list;
}

function getdiseaseobj() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_xray_disease order by id asc";
  $list = $db->obj($sql, 'id', 'name');
  return $list;
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

  $sql = "update pet_phc_xray set pos = $data->pos, diseaseid = $data->diseaseid, petname = '$data->petname', weight = '$data->weight', age = '$data->age', gender = $data->gender, species = '$data->species' where id = $data->id";
  $db->query($sql);

  if (!empty($data->near)) {
    $near = isodatetotime($data->near);
    $sql = "select * from pet_phc_xray_schedule where status = 0";
    if (!empty($sc = $db->fetch($sql))) $sql = "update pet_phc_xray_schedule set time = $near where id = $sc[id]";
    else $sql = "insert into pet_phc_xray_schedule (xrayid, time, status) values($data->id, $near, 0)";
  }
  else $sql = "delete from pet_phc_xray_schedule where status = 0 and xrayid = $data->id";
  $db->query($sql);

  $arr = array('0' => '0', '1' => '-1');
  $data->xquang = $arr[$data->xquang];
  $data->sinhly = $arr[$data->sinhly];
  $data->sinhhoa = $arr[$data->sinhhoa];
  $data->sieuam = $arr[$data->sieuam];
  $data->nuoctieu = $arr[$data->nuoctieu];

  $sql = "insert into pet_phc_xray_row (xrayid, doctorid, subother, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu, conclude) values($data->id, $userid, '$data->subother', '$data->temperate', '$data->other', '$data->treat', '". implode(',', $data->image) ."', '$data->status', $data->time, $data->xquang, $data->sinhly, $data->sinhhoa, $data->sieuam, $data->nuoctieu, '$data->conclude')";
  $id = $db->insertid($sql);
  
  $sql = "select a.*, b.fullname as doctor, a.time from pet_phc_xray_row a inner join pet_phc_users b on a.doctorid = b.userid where a.id = $id order by time asc";
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

  $diseaseobj = getdiseaseobj();

  $filter = $data->filter;
  $filter->start = isodatetotime($filter->start);
  $filter->end = isodatetotime($filter->end) + 60 * 60 * 24 - 1;
  $userid = checkuserid();
  $xtra = array();

  $sql = "select * from pet_phc_user_per where userid = $userid and module = 'his'";
  $role = $db->fetch($sql);
  
  if ($role['type'] < 2) $xtra []= " (a.doctorid = $userid or a.share = 1 or pos = 1) ";
  else if (isset($filter->{'docs'})) {
    if (empty($filter->docscover)) $filter->docscover = '';
    $docs = implode(',', $filter->docs);
    $sql = "update pet_phc_config set value = '$docs' where module = 'docs' and name = '$userid'";
    $db->query($sql);
    $sql = "update pet_phc_config set value = '$filter->docscover' where module = 'docscover' and name = '$userid'";
    $db->query($sql);
    if (count($filter->docs)) $xtra []= " a.doctorid in ($docs) ";
  }

  if (!empty($filter->diseaseid)) {
    $xtra []= ' a.diseaseid = '. $filter->diseaseid .' ';
  }

  if (count($xtra)) $xtra = implode(" and ", $xtra) . "and";
  else $xtra = "";

  $sql = "select a.*, c.name as customer, c.phone, d.fullname as doctor from pet_phc_xray a inner join pet_phc_customer c on a.customerid = c.id inner join pet_phc_users d on a.doctorid = d.userid where $xtra ((a.time between $filter->start and $filter->end) or (a.time < $filter->start and a.insult = 0)) ". ($id ? " and a.id = $id " : '') ." and (c.phone like '%$filter->keyword%' or c.name like '%$filter->keyword%') order by a.insult asc, id desc";
  $list = $db->all($sql);
  $time = strtotime(date('Y/m/d')) + 60 * 60 * 24;
  
  foreach ($list as $key => $value) {
    $list[$key]['chat'] = getChatCount($value['id']);
    $sql = "select a.*, b.fullname as doctor from pet_phc_xray_row a inner join pet_phc_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = $db->all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
      $image = explode(', ', $detail['image']);
      if (count($image) == 1 && $image[0] == '') $row[$index]['image'] = array();
      else $row[$index]['image'] = $image;
      $sql = "select a.image, a.note, a.id, a.status, b.name from pet_phc_exam a inner join pet_phc_exam_type b on a.typeid = b.id where treatid = $detail[id]";
      $row[$index]['exam'] = $db->all($sql);

      $row[$index]['sinhlyimg'] = array();
      $row[$index]['sinhhoaimg'] = array();

      if ($detail['sinhly'] > 0) {
        $sql = "select image from pet_phc_physical where id = $detail[sinhly]";
        $d = $db->fetch($sql);
        $row[$index]['sinhlyimg'] = parseimage($d['image']);
      }
      if ($detail['sinhhoa'] > 0) {
        $sql = "select image from pet_phc_profile where id = $detail[sinhhoa]";
        $d = $db->fetch($sql);
        $row[$index]['sinhhoaimg'] = parseimage($d['image']);
      }

      foreach ($row[$index]['exam'] as $examindex => $exam) {
        $image = explode(', ', $exam['image']);
        if (count($image) == 1 && $image[0] == '') $row[$index]['exam'][$examindex]['image'] = array();
        else $row[$index]['exam'][$examindex]['image'] = $image;
      }
    }

    $list[$key]['disease'] = $diseaseobj[$value['diseaseid']];

    $sql = "select * from pet_phc_xray_schedule where xrayid = $value[id] order by id desc limit 1";
    $list[$key]['sc'] = '';
    if (!empty($sc = $db->fetch($sql))) {
      $list[$key]['sc'] = date('d/m/Y', $sc['time']);
      $list[$key]['scstatus'] = $sc['status'];
    }

    if (count($row)) {
      $list[$key]['status'] = $row[count($row) - 1]['status'];
      $list[$key]['time'] = $row[0]['time'];
    }
    else {
      $list[$key]['status'] = 0;
      $list[$key]['time'] = date('d/m/Y');
    }
    $list[$key]['rate'] = intval($value['rate']);
    if (!count($row)) $row = array(array('xquang' => 0, 'sieuam' => 0, 'nuoctieu' => 0, 'sinhly' => 0, 'sinhhoa' => 0));
    $list[$key]['detail'] = $row;
  }
  return $list;
}



function changesc() {
  global $db, $data;

  $rev = array(0 => 1, 1 => 0);
  $status = $rev[$data->status];
  $sql = "update pet_phc_xray_schedule set status = $status where xrayid = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
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

function schedule() {
  global $db, $data, $result;

  $near = isodatetotime($data->time);
  $sql = "select * from pet_phc_xray_schedule where status = 0 and id = $data->id";
  if (!empty($sc = $db->fetch($sql))) $sql = "update pet_phc_xray_schedule set time = $near where id = $sc[id]";
  else $sql = "insert into pet_phc_xray_schedule (xrayid, time, status) values($data->id, $near, 0)";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
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
