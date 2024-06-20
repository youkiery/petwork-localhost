<?php
define('SERVERSIDE', 0);
define('CLIENTSIDE', 1);

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['nhanvien'] = laydanhsachnhanvien();
  $result['type'] = gettypelist();
  $result['disease'] = getdiseaselist();
  return $result;
}

function filter() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function add() {
  global $data, $db, $result;

  $sql = "insert into pet_". PREFIX ."_xray_his (petid, his, time) values($data->petid, '$data->his', ". time() .")";
  $db->query($sql);
  
  $sql = "select * from pet_". PREFIX ."_xray_his where petid = $data->petid";
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
//     $sql = "insert into pet_". PREFIX ."_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($data->id, $data->userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(',', $data->image) ."', '$data->status', $time)";
//     $db->query($sql);
//   }
//   else {
//     $petid = checkpet();
//     $sql = "insert into pet_". PREFIX ."_xray(petid, doctorid, insult, time) values($petid, $data->userid, 0, $time)";
//     $id = $db->insertid($sql);
    
//     $sql = "insert into pet_". PREFIX ."_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($id, $data->userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(',', $data->image) ."', '$data->status', $time)";
//     $db->query($sql);
//   }

//   // xóa phiếu tạm
//   $sql = "delete from pet_". PREFIX ."_his_temp where id = $data->tempid";
//   $db->query($sql);

//   $result['status'] = 1;
//   $result['list'] = getManager();
//   return $result;
// }

function printer() {
  global $data, $db, $result;

  // $html = file_get_contents ( DIR. 'include/template3.php');
  $sql = "select * from pet_". PREFIX ."_form where name = 'his'";
  $html = $db->fetch($sql)['value'];

  $sql = "select * from pet_". PREFIX ."_xray_row where id = $data->id";
  $detail = $db->fetch($sql);

  $sql = "select * from pet_". PREFIX ."_xray where id = $detail[xrayid]";
  $xray = $db->fetch($sql);

  $sql = "select * from pet_". PREFIX ."_users where userid = $detail[doctorid]";
  $doctor = $db->fetch($sql);
  $names = explode(' ', $doctor['fullname']);
  $count = count($names);
  $doctor['halfname'] = mb_strtoupper($names[$count - 2] . ' ' . $names[$count - 1]);

  $sql = "select * from pet_". PREFIX ."_config where id = $doctor[placeid]";
  if (empty($place = $db->fetch($sql))) $place = array('name' => '');

  $sql = "select * from pet_". PREFIX ."_customer where id = $xray[customerid]";
  $customer = $db->fetch($sql);
  $sex = array(0 => '', 'Đực', 'Cái');

  $html = str_replace('{place}', $place['name'], $html);
  $html = str_replace('{doctor}', $doctor['halfname'], $html);
  $html = str_replace('{customer}', $customer['name'], $html);
  $html = str_replace('{address}', $customer['address'], $html);
  $html = str_replace('{phone}', $customer['phone'], $html);
  $html = str_replace('{name}', $xray['petname'], $html);
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
  // $sql = "select b.name from pet_". PREFIX ."_exam a inner join pet_". PREFIX ."_exam_type b on a.typeid = b.id where a.treatid = $data->id";
  // $list = $db->all($sql);

  // foreach ($list as $row) {
  //   $p []= '☑ '. $row['name'];
  // }

  // for ($i = 0; $i < 6; $i++) { 
  //   $t = (isset($p[$i]) ? $p[$i] : '');
  //   $html = str_replace('{a'. $i .'}', $t, $html);
  // }

  $html = str_replace('{DD}', date('d', $detail['time']), $html);
  $html = str_replace('{MM}', date('m', $detail['time']), $html);
  $html = str_replace('{YY}', date('Y', $detail['time']), $html);
  $html = str_replace('{doctor2}', $doctor['fullname'], $html);

  $result['status'] = 1;
  $result['html'] = $html;
  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xray set pos = $data->pos, petname = '$data->petname', weight = '$data->weight', age = '$data->age', gender = $data->gender, species = '$data->species' where id = $data->id";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_config where module = 'hisdisease' and name = $data->id";
  $db->query($sql);
  
  foreach ($data->disease->list as $key => $value) {
    if ($value) {
      $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('hisdisease', $data->id, $key, 0)";
      $db->query($sql);
    }
  }

  if (!empty($data->near)) {
    $near = isodatetotime($data->near);
    $sql = "select * from pet_". PREFIX ."_xray_schedule where status = 0";
    if (!empty($sc = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_xray_schedule set time = $near where id = $sc[id]";
    else $sql = "insert into pet_". PREFIX ."_xray_schedule (xrayid, time, status) values($data->id, $near, 0)";
  }
  else $sql = "delete from pet_". PREFIX ."_xray_schedule where status = 0 and xrayid = $data->id";
  $db->query($sql);

  $arr = array('xquang','sinhly','sinhhoa','sieuam','nuoctieu');
  $sql = "select * from pet_". PREFIX ."_xray_row where id = $data->detailid";
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
  $sql = "update pet_". PREFIX ."_xray_row set $xtra temperate = '$data->temperate', other = '$data->other', treat = '$data->treat', status = '$data->status', image = '$image', conclude = '$data->conclude', subother = '$data->subother', time = $time where id = $data->detailid";
  $db->query($sql);

  foreach ($data->exam as $exam) {
    // kiểm tra xem có hay chưa, chưa thì insert
    $sql = "select * from pet_". PREFIX ."_exam where treatid = $data->detailid and typeid = $exam->id";
    if (empty($row = $db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_exam (userid, customerid, treatid, typeid, image, note, time) values($userid, $customerid, $data->detailid, $exam->id, '', '', $time)";
      $db->query($sql);
    }
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getinfo() {
  global $data, $db, $result, $userid;
  
  $type = $data->typed;
  if ($type == 'xquang' || $type == 'sieuam') {
    $sql = "select b.name, b.phone, b.address, '' as note, 1 as his from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.id = $data->xrayid";
    $info = $db->fetch($sql);
    $info['image'] = array();
    $info['xrayid '] = $data->id;
  }
  else if ($type == 'physical' || $type == 'profile') {
    $sql = "select a.petname, a.weight, a.age, a.gender, a.species, c.name, c.phone, c.address, a.weight, a.age, a.gender from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id where a.id = $data->xrayid";
    $info = $db->fetch($sql);
    // doctor, weight, age, gender, species, serial, sampletype, samplenumber, samplesymbol, samplestatus, symptom, target
    $info['doctor'] = checkuserid();
    $sql = "select * from pet_". PREFIX ."_config where module = 'profile' and name = 'serial' limit 1";
    $serial = $db->fetch($sql)['value'];
    $info['serial'] = $serial;
    $sql = "select id from pet_". PREFIX ."_config where module = 'physical' and name = 'sampletype' limit 1";
    $info['sampletype'] = $db->fetch($sql)['id'];
    $info['samplenumber'] = 1;
    $info['samplesymbol'] = $serial;
    $info['samplestatus'] = '1';
    $info['symptom'] = '';
    $info['his'] = 1;
    $sql = "select * from pet_". PREFIX ."_target where active = 1 and module = 'physical' order by id asc";
    $list = $db->arr($sql, 'id');
    $target = array();
    foreach ($list as $value) {
      $target[$value] = '';
    }
    $info['target'] = $target;
    $info['act'] = $type;
    $info['xrayid'] = $data->xrayid;
    $info['image'] = array();
    $info['xrayid'] = $data->id;
  }

  $result['status'] = 1;
  $result['data'] = $info;
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
  $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'his' and type = 2";
  $xtra = "";
  if (empty($p = $db->fetch($sql))) $xtra = "a.doctorid = $userid and";

  $sql = "select a.*, c.name as customer, c.phone, d.fullname as doctor from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id inner join pet_". PREFIX ."_users d on a.doctorid = d.userid where $xtra (a.time between $data->start and $data->end) order by id desc";
  $list = $db->all($sql);
  $data = array();
  $rev = array(0 => 'dieutri', 'xuatvien', 'dachet');
  
  foreach ($list as $key => $value) {
    if (empty($data[$value['doctorid']])) $data[$value['doctorid']] = array(
      'doctorid' => $value['doctorid'],
      'name' => $value['doctor'],
      'balance' => 0,
      'dieutri' => 0,
      'xuatvien' => 0,
      'dachet' => 0,
      'tongca' => 0,
      'danhgia' => 0,
      'tongdanhgia' => 0
    );
    $data[$value['doctorid']][$rev[$value['insult']]] ++;
    $data[$value['doctorid']]['tongca'] ++;
    if ($data[$value['doctorid']]['danhgia'] > 0) {
      $data[$value['doctorid']]['danhgia'] += $value['rate'];
      $data[$value['doctorid']]['tongdanhgia'] += 3;
    }
  }
  
  foreach ($data as $doctorid => $thongtin) {
    $data[$doctorid]['tiledieutri'] = ($thongtin['tongca'] > 0 ? number_format($thongtin ['dieutri'] * 100 / $thongtin['tongca'], 1) : 0);
    $data[$doctorid]['tilexuatvien'] = ($thongtin['tongca'] > 0 ? number_format($thongtin ['xuatvien'] * 100 / $thongtin['tongca'], 1) : 0);
    $data[$doctorid]['tiledachet'] = ($thongtin['tongca'] > 0 ? number_format($thongtin ['dachet'] * 100 / $thongtin['tongca'], 1) : 0);
    $data[$doctorid]['tiledanhgia'] = ($thongtin['tongdanhgia'] > 0 ? number_format($thongtin ['danhgia'] * 100 / $thongtin['tongdanhgia'], 1) : 0);
    if ($thongtin['xuatvien'] > $thongtin['dachet']) $data[$doctorid]['balance'] = 1;
    else if ($thongtin['xuatvien'] < $thongtin['dachet']) $data[$doctorid]['balance'] = 2;
  }

  $danhsach = array();
  foreach ($data as $thongtin) {
    $danhsach []= $thongtin;
  }
  
  $result['status'] = 1;
  $result['data'] = $danhsach;
  return $result;
}

function statistic2() {
  global $data, $db, $result;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $sql = "select a.*, c.name as customer, c.phone, d.fullname as doctor from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id inner join pet_". PREFIX ."_users d on a.doctorid = d.userid where (a.time between $data->start and $data->end) order by id desc";
  $danhsach = $db->all($sql);
  $dulieu = array();
  $rev = array(0 => 'dieutri', 'xuatvien', 'dachet');
  
  foreach ($danhsach as $dieutri) {
    $sql = "select a.value as idbenh, b.name from pet_". PREFIX ."_config a inner join pet_". PREFIX ."_xray_disease b on a.value = b.id where a.module = 'hisdisease' and a.name = $dieutri[id]";
    $benh = $db->all($sql, 'value');
    foreach ($benh as $thongtin) {
      if (empty($dulieu[$thongtin['idbenh']])) $dulieu[$thongtin['idbenh']] = array(
        'id' => $thongtin['idbenh'],
        'balance' => 0,
        'tenbenh' => $thongtin['name'],
        'dieutri' => 0,
        'xuatvien' => 0,
        'dachet' => 0,
        'tongca' => 0,
        'danhsachxuatvien' => array(),
        'danhsachdachet' => array()
      );
      if ($dieutri['insult'] == 1) $dulieu[$thongtin['idbenh']]['danhsachxuatvien'] []= $dieutri['id'];
      else if ($dieutri['insult'] == 2) $dulieu[$thongtin['idbenh']]['danhsachdachet'] []= $dieutri['id'];
      $dulieu[$thongtin['idbenh']]['tongca'] ++;
      $dulieu[$thongtin['idbenh']][$rev[$dieutri['insult']]] ++;
    }
  }

  foreach ($dulieu as $idbenh => $thongtin) {
    $dulieu[$idbenh]['tiledieutri'] = ($thongtin['tongca'] > 0 ? number_format($thongtin ['dieutri'] * 100 / $thongtin['tongca'], 1) : 0);
    $dulieu[$idbenh]['tilexuatvien'] = ($thongtin['tongca'] > 0 ? number_format($thongtin ['xuatvien'] * 100 / $thongtin['tongca'], 1) : 0);
    $dulieu[$idbenh]['tiledachet'] = ($thongtin['tongca'] > 0 ? number_format($thongtin ['dachet'] * 100 / $thongtin['tongca'], 1) : 0);
    if ($thongtin['xuatvien'] > $thongtin['dachet']) $dulieu[$idbenh]['balance'] = 1;
    else if ($thongtin['xuatvien'] < $thongtin['dachet']) $dulieu[$idbenh]['balance'] = 2;
  }

  $danhsach = array();
  foreach ($dulieu as $thongtin) {
    $danhsach []= $thongtin;
  }
  
  $result['status'] = 1;
  $result['data'] = $danhsach;
  return $result;
}

function ratest($a, $b) {
  return $a[4] > $b[4];
}

function returned() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xray set insult = 1 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['insult'] = 1;
  
  return $result;
}

function xoachitiet() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_xray_row where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa chitiết';
  $result['list'] = getlist();
  
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_xray where id = $data->id";
  $db->query($sql);
  
  $sql = "delete from pet_". PREFIX ."_xray_row where xrayid = $data->id";
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
    $sql = "update pet_". PREFIX ."_xray_disease set name = '$row->name' where id = $row->id";
    $db->query($sql);
  }

  if (!empty($data->key)) {
    $sql = "insert into pet_". PREFIX ."_xray_disease (name) values('$data->key')";
    $db->query($sql);
  }
  // $sql = "delete fron pet_". PREFIX ."_xray_disease where id >= $index";
  // $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getdiseaselist();
  return $result;
}

function removedisease() {
  global $data, $db, $result, $userid;
  
  $sql = "update pet_". PREFIX ."_xray_disease set active = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  $result['list'] = getdiseaselist();
  return $result;
}

function insertdisease() {
  global $data, $db, $result, $userid;
  
  $sql = "insert into pet_". PREFIX ."_xray_disease (name) values('$data->key')";
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
  $sql = "insert into pet_". PREFIX ."_xray (customerid, doctorid, insult, time, pos, petname, age, gender, species, weight) values($customerid, $userid, 0, $data->time, $data->pos, '$data->petname', '$data->age', '$data->gender', '$data->species', '$data->weight')";
  $id = $db->insertid($sql);
  $arr = array('0' => '0', '1' => '-1');
  $data->xquang = $arr[$data->xquang];
  $data->sinhly = $arr[$data->sinhly];
  $data->sinhhoa = $arr[$data->sinhhoa];
  $data->sieuam = $arr[$data->sieuam];
  $data->nuoctieu = $arr[$data->nuoctieu];
  $image = implode(',', $data->image);

  foreach ($data->disease->list as $key => $value) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('hisdisease', $id, $key, 0)";
    $db->query($sql);
  }

  $sql = "insert into pet_". PREFIX ."_xray_row (xrayid, doctorid, subother, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu, conclude) values($id, $userid, '$data->subother', '$data->temperate', '$data->other', '$data->treat', '$image', '$data->status', $data->time, $data->xquang, $data->sinhly, $data->sinhhoa, $data->sieuam, $data->nuoctieu, '$data->conclude')";
  $id = $db->query($sql);

  if (!empty($data->near)) {
    $near = isodatetotime($data->near);
    $sql = "insert into pet_". PREFIX ."_xray_schedule (xrayid, time, status) values($id, $near, 0)";
    $db->query($sql);
  }

  foreach ($data->exam as $exam) {
    $sql = "insert into pet_". PREFIX ."_exam (userid, customerid, treatid, typeid, image, note, time) values($userid, $customerid, $id, $exam->id, '', '', $time)";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
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

function getdiseaselist() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_xray_disease where active = 1 order by id asc";
  $list = $db->all($sql);
  return $list;
}

function getdiseaseobj() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_xray_disease order by id asc";
  $list = $db->obj($sql, 'id', 'name');
  return $list;
}

function gettypelist() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_exam_type where active = 1 order by name desc";
  $list = $db->all($sql);
  return $list;
}

function detail() {
  global $data, $db, $result;

  $userid = checkuserid();
  $data->time = isodatetotime($data->time);
  
  $sql = "update pet_". PREFIX ."_xray set pos = $data->pos, petname = '$data->petname', weight = '$data->weight', age = '$data->age', gender = $data->gender, species = '$data->species' where id = $data->id";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_config where module = 'hisdisease' and name = $data->id";
  $db->query($sql);
  
  foreach ($data->disease->list as $key => $value) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('hisdisease', $data->id, $key, 0)";
    $db->query($sql);
  }

  if (!empty($data->near)) {
    $near = isodatetotime($data->near);
    $sql = "select * from pet_". PREFIX ."_xray_schedule where status = 0";
    if (!empty($sc = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_xray_schedule set time = $near where id = $sc[id]";
    else $sql = "insert into pet_". PREFIX ."_xray_schedule (xrayid, time, status) values($data->id, $near, 0)";
  }
  else $sql = "delete from pet_". PREFIX ."_xray_schedule where status = 0 and xrayid = $data->id";
  $db->query($sql);

  $arr = array('0' => '0', '1' => '-1');
  $data->xquang = $arr[$data->xquang];
  $data->sinhly = $arr[$data->sinhly];
  $data->sinhhoa = $arr[$data->sinhhoa];
  $data->sieuam = $arr[$data->sieuam];
  $data->nuoctieu = $arr[$data->nuoctieu];

  $sql = "insert into pet_". PREFIX ."_xray_row (xrayid, doctorid, subother, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu, conclude) values($data->id, $userid, '$data->subother', '$data->temperate', '$data->other', '$data->treat', '". implode(',', $data->image) ."', '$data->status', $data->time, $data->xquang, $data->sinhly, $data->sinhhoa, $data->sieuam, $data->nuoctieu, '$data->conclude')";
  $id = $db->insertid($sql);

  $customerid = checkcustomer();
  $time = time();

  foreach ($data->exam as $exam) {
    $sql = "insert into pet_". PREFIX ."_exam (userid, customerid, treatid, typeid, image, note, time) values($userid, $customerid, $id, $exam->id, '', '', $time)";
    $db->query($sql);
  }
  
  $sql = "select a.*, b.fullname as doctor, a.time from pet_". PREFIX ."_xray_row a inner join pet_". PREFIX ."_users b on a.doctorid = b.userid where a.id = $id order by time asc";
  $row = $db->fetch($sql);
  $row['time'] = date('d/m/Y', $row['time']);
  
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);
  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xray set insult = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['insult'] = 2;

  return $result;
}

function pay() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_xray_row set pay = $data->pay where id = $data->detailid";
  $db->query($sql);
  $result['status'] = 1;
  return $result;
}

function move() {
  global $db, $data, $result;
  $reversal = array(0 => 1, 0);
  $rev = $reversal[$data->pos];

  $sql = "update pet_". PREFIX ."_xray set pos = $rev where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getList();
  $result['count'] = getChatTotalCount($result['list']);
  return $result;
}

function hopital() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_xray set insult = 0, rate = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getList();
  $result['count'] = getChatTotalCount($result['list']);
  return $result;
}

function share() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_xray set share = ". intval(!$data->share) ." where id = $data->id";
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

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'his'";
  $role = $db->fetch($sql);
  
  if ($role['type'] < 2) $xtra []= " (a.doctorid = $userid or a.share = 1 or pos = 1) ";
  else if (!empty($data->chonnhanvien)) {
    $xtra []= " a.doctorid in (". implode(',', $data->chonnhanvien) .") ";
  }

  if (count($xtra)) $xtra = implode(" and ", $xtra) . "and";
  else $xtra = "";

  if (!empty($filter->diseaseid)) {
    $sql = "select a.*, c.name as customer, c.phone, c.address, d.fullname as doctor from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id inner join pet_". PREFIX ."_users d on a.doctorid = d.userid inner join pet_". PREFIX ."_config e on (e.name = a.id and e.module = 'hisdisease' and e.value = $filter->diseaseid) where $xtra (select time from pet_". PREFIX ."_xray_row where (time between $filter->start and $filter->end) and xrayid = a.id order by time desc limit 1) ". ($id ? " and a.id = $id " : '') ." and (c.phone like '%$filter->keyword%' or c.name like '%$filter->keyword%') order by a.insult asc, id desc";
  }
  else {
    $sql = "select a.*, c.name as customer, c.phone, c.address, d.fullname as doctor from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id inner join pet_". PREFIX ."_users d on a.doctorid = d.userid where $xtra (select time from pet_". PREFIX ."_xray_row where (time between $filter->start and $filter->end) and xrayid = a.id order by time desc limit 1) ". ($id ? " and a.id = $id " : '') ." and (c.phone like '%$filter->keyword%' or c.name like '%$filter->keyword%') order by a.insult asc, id desc";
  }
  $list = $db->all($sql);
  $time = strtotime(date('Y/m/d')) + 60 * 60 * 24;
  
  foreach ($list as $key => $value) {
    $list[$key]['chat'] = getChatCount($value['id']);
    $sql = "select a.*, b.fullname as doctor from pet_". PREFIX ."_xray_row a inner join pet_". PREFIX ."_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = $db->all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
      $image = parseimage($detail['image']);
      if (count($image) == 1 && $image[0] == '') $row[$index]['image'] = array();
      else $row[$index]['image'] = $image;
      $sql = "select a.image, a.note, a.id, a.status, b.name from pet_". PREFIX ."_exam a inner join pet_". PREFIX ."_exam_type b on a.typeid = b.id where treatid = $detail[id]";
      $row[$index]['exam'] = $db->all($sql);

      $row[$index]['sinhlyimg'] = array();
      $row[$index]['sinhhoaimg'] = array();

      if ($detail['sinhly'] > 0) {
        $sql = "select image from pet_". PREFIX ."_physical where id = $detail[sinhly]";
        $d = $db->fetch($sql);
        $row[$index]['sinhlyimg'] = parseimage($d['image']);
      }
      if ($detail['sinhhoa'] > 0) {
        $sql = "select image from pet_". PREFIX ."_profile where id = $detail[sinhhoa]";
        $d = $db->fetch($sql);
        $row[$index]['sinhhoaimg'] = parseimage($d['image']);
      }

      foreach ($row[$index]['exam'] as $examindex => $exam) {
        $image = parseimage($exam['image']);
        if (count($image) == 1 && $image[0] == '') $row[$index]['exam'][$examindex]['image'] = array();
        else $row[$index]['exam'][$examindex]['image'] = $image;
      }
    }

    $sql = "select b.id, b.name, 1 as done from pet_". PREFIX ."_config a inner join pet_". PREFIX ."_xray_disease b on a.value = b.id where a.module = 'hisdisease' and a.name = '$value[id]'";
    $list[$key]['disease'] = array('text' => implode(', ', $db->arr($sql, 'name')), 'list' => $db->obj($sql, 'id', 'done'));

    $sql = "select * from pet_". PREFIX ."_xray_schedule where xrayid = $value[id] order by id desc limit 1";
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
  $sql = "update pet_". PREFIX ."_xray_schedule set status = $status where xrayid = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getChatList() {
  global $db, $data;

  $time = time();
  $sql = "update pet_". PREFIX ."_xray_read set time = $time where side = 0 and postid = $data->id";
  $db->query($sql);

  $sql = "select * from pet_". PREFIX ."_xray_chat where postid = $data->id";
  return $db->all($sql);  
}

function getReadtime($id, $side) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_xray_read where side = $side and postid = $id";
  if (empty($r = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_xray_read (side, postid, time) values(0, $id, 0)";
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
  $sql = "insert into pet_". PREFIX ."_xray_chat (postid, side, time, text) values($data->id, 0, $time, '$data->text')";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getChatList();
  return $result;  
}

function schedule() {
  global $db, $data, $result;

  $near = isodatetotime($data->time);
  // $customerid = checkcustomer();
  // $sql = "update pet_". PREFIX ."_xray set customerid = $customerid";
  $sql = "select * from pet_". PREFIX ."_xray_schedule where status = 0 and id = $data->id";
  if (!empty($sc = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_xray_schedule set time = $near where id = $sc[id]";
  else $sql = "insert into pet_". PREFIX ."_xray_schedule (xrayid, time, status) values($data->id, $near, 0)";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;  
}

function getChatCount($id) {
  global $db, $data;

  $readtime = getReadtime($id, SERVERSIDE);
  $sql = "select id from pet_". PREFIX ."_xray_chat where postid = $id and side = 1 and time > $readtime";
  return $db->count($sql);
}

function statrate() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xray set rate = $data->rate where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['count'] = getChatTotalCount($result['list']);

  return $result;
}

function khoitaoloai() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['loaidichvu'] = danhsachdichvu();
  $result['loainhac'] = dulieuloai();
  $result['loaicong'] = dulieucong();
  return $result;
}

function danhsachdichvu() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_danhmuc where loaidanhmuc = 1 and kichhoat = 1 order by vitri asc";
  return $db->all($sql);
}

function dulieuloai() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_dieutrima order by id desc";
  return $db->all($sql);
}

function dulieucong() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_dieutricong where active = 1 order by id desc";
  return $db->all($sql);
}

function themloai() {
  global $data, $db, $result;
  $data->code = trim($data->code);

  if ($data->id) $sql = "update pet_". PREFIX ."_dieutrima set code = '$data->code' where id = $data->id";
  else $sql = "insert into pet_". PREFIX ."_dieutrima (code) values('$data->code')";
  $db->query($sql);

  $result['status'] = 1;
  $result['loainhac'] = dulieuloai();
  return $result;
}

function xoaloai() {
  global $data, $db, $result;
  $sql = "delete from pet_". PREFIX ."_dieutrima where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['loainhac'] = dulieuloai();
  return $result;
}

function themloaicong() {
  global $data, $db, $result;
  $data->name = trim($data->name);
  $data->code = trim($data->code);

  if ($data->id) $sql = "update pet_". PREFIX ."_dieutricong set code = '$data->code', name = '$data->name' where id = $data->id";
  else $sql = "insert into pet_". PREFIX ."_dieutricong (code, name) values('$data->code', '$data->name')";
  $db->query($sql);

  $result['status'] = 1;
  $result['loaicong'] = dulieucong();
  return $result;
}

function xoaloaicong() {
  global $data, $db, $result;
  $sql = "update pet_". PREFIX ."_dieutricong set active = 0 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['loaicong'] = dulieucong();
  return $result;
}

function checkpet() {
  global $data, $db;
  $sql = "select * from pet_". PREFIX ."_customer where phone = '$data->phone'";

  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$data->name', '$data->phone', '')";
    $c['id'] = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_customer set name = '$data->name' where id = $c[id]";
    $db->query($sql);
  }

  if (empty($data->petid)) {
    $sql = "select * from pet_". PREFIX ."_pet where name = '$data->pet' and customerid = '$c[id]'";
    if (empty($p = $db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_pet (name, customerid) values('$data->pet', $c[id])";
      $p['id'] = $db->insertid($sql);
    }
    return $p['id'];
  }
  else {
    $sql = "update pet_". PREFIX ."_pet set name = '$data->pet' where id = $data->petid";
    $db->query($sql);
    return $data->petid;
  }
}

function xemdieutri() {
  global $db, $data, $result;

  $diseaseobj = getdiseaseobj();

  $sql = "select a.*, c.name as customer, c.phone, c.address, d.fullname as doctor from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id inner join pet_". PREFIX ."_users d on a.doctorid = d.userid where a.id in (". implode(', ', $data->danhsach) .") order by a.insult asc, id desc";
  $list = $db->all($sql);
  $time = strtotime(date('Y/m/d')) + 60 * 60 * 24;
  
  foreach ($list as $key => $value) {
    $list[$key]['chat'] = getChatCount($value['id']);
    $sql = "select a.*, b.fullname as doctor from pet_". PREFIX ."_xray_row a inner join pet_". PREFIX ."_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = $db->all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
      $row[$index]['image'] = parseimage($detail['image']);
    }

    $sql = "select b.id, b.name, 1 as done from pet_". PREFIX ."_config a inner join pet_". PREFIX ."_xray_disease b on a.value = b.id where a.module = 'hisdisease' and a.name = '$value[id]'";
    $list[$key]['disease'] = array('text' => implode(', ', $db->arr($sql, 'name')), 'list' => $db->obj($sql, 'id', 'done'));

    if (count($row)) {
      $list[$key]['status'] = $row[count($row) - 1]['status'];
      $list[$key]['time'] = $row[0]['time'];
    }
    else {
      $list[$key]['status'] = 0;
      $list[$key]['time'] = date('d/m/Y');
    }
    if (!count($row)) $row = array(array('xquang' => 0, 'sieuam' => 0, 'nuoctieu' => 0, 'sinhly' => 0, 'sinhhoa' => 0));
    $list[$key]['detail'] = $row;
  }
  $result['status'] = 1;
  $result['danhsach'] = $list;
  return $result;
}


function updatetype() {
  global $data, $db, $result;

  if (!$time = intval($data->time)) $time = 0;

  $sql = "update pet_". PREFIX ."_danhmuc set tendanhmuc = '$data->name', thoigian = $time where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = danhsachdichvu();
  return $result;
}

function inserttype() {
  global $data, $db, $result;

  if (!$time = intval($data->time)) $time = 0;
  $sql = "insert into pet_". PREFIX ."_danhmuc (tendanhmuc, loaidanhmuc, vitri, macdinh, thoigian) values('$data->name', 1, 0, 0, $time)";
  $id = $db->insertid($sql);
  $sql = "update pet_". PREFIX ."_danhmuc set vitri = $id where id = $id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = danhsachdichvu();
  return $result;
}

function uptype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_danhmuc set vitri = $data->vitri2 where id = $data->id1";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_danhmuc set vitri = $data->vitri1 where id = $data->id2";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = danhsachdichvu();
  return $result;
}

function downtype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_danhmuc set vitri = $data->vitri2 where id = $data->id1";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_danhmuc set vitri = $data->vitri1 where id = $data->id2";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = danhsachdichvu();
  return $result;
}

function toggletype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_danhmuc set macdinh = '". (intval(!$data->alt) ? 1 : '') ."' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = danhsachdichvu();
  return $result;
}

function removetype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_danhmuc set kichhoat = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = danhsachdichvu();
  return $result;
}
