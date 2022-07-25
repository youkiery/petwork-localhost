<?php
function download() {
  global $data, $db, $result;
    
  $zip = new ZipArchive;
  
  $fileToModify = 'word/document.xml';
  $wordDoc = DIR. "/include/template2.docx";
  $name = "analysis-". time() .".docx";
  $exportDoc = DIR. "/include/export/". $name;
  
  copy($wordDoc, $exportDoc);
  if ($zip->open($exportDoc) === TRUE) {
    $sql = "select * from pet_phc_physical where id = $data->id";
    $query = $db->query($sql);
    $prof = $query->fetch_assoc();
    
    $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_phc_physical_data a inner join pet_phc_target b on a.pid = $data->id and a.tid = b.id and b.module = 'physical'";
    $query = $db->query($sql);
    $prof['target'] = array();
    while ($row = $query->fetch_assoc()) {
      $flag = explode(' - ', $row['flag']);
      $value = floatval($row['value']);
      if (count($flag) == 2) {
        $s = floatval($flag[0]);
        $e = floatval($flag[1]);
      }
      else {
        $s = 0; $e = 1;
      }
      $tick = '';
      $tar = '';
      if ($value < $s) {
        $tick = '<';
        $tar = $row['name'] .' giảm: '. $row['down'];
      }
      else if ($value > $e) {
        $tick = '>'; 
        $tar = $row['name'] .' tăng: '. $row['up'];
      }
      
      $prof['target'] []= array(
        'name' => $row['name'],
        'value' => $row['value'],
        'unit' => $row['unit'],
        'flag' => $row['flag'],
        'tar' => $tar,
        'tick' => $tick
      );
    }
    
    $sql = 'select value from pet_phc_config where name = "species" limit 1';
    $row = $db->fetch($sql);
    $prof['species'] = $row['value'];
    
    $sql = 'select value from pet_phc_config where name = "sampletype" limit 1';
    $row = $db->fetch($sql);
    $prof['sampletype'] = $row['value'];
    
    $sql = 'select * from pet_phc_users where userid = '. $prof['doctor'];
    $doctor = $db->fetch($sql);
    
    $prof['doctor'] = $doctor['fullname'];
    
    $oldContents = $zip->getFromName($fileToModify);
  
    $sex = array(0 => '', 'Đực', 'Cái');
    $newContents = str_replace('{customer}', $prof['customer'], $oldContents);
    $newContents = str_replace('{address}', $prof['address'], $newContents);
    $newContents = str_replace('{name}', $prof['name'], $newContents);
    $newContents = str_replace('{weight}', $prof['weight'], $newContents);
    $newContents = str_replace('{age}', $prof['age'], $newContents);
    $newContents = str_replace('{gender}', $sex[$prof['gender']], $newContents);
    $newContents = str_replace('{type}', $prof['species'], $newContents);
    $newContents = str_replace('{sampleid}', $prof['id'], $newContents);
    $newContents = str_replace('{serial}', $prof['serial'], $newContents);
    $newContents = str_replace('{sampletype}', $prof['sampletype'], $newContents);
    $newContents = str_replace('{samplenumber}', $prof['samplenumber'], $newContents);
    $newContents = str_replace('{samplesymbol}', $prof['samplesymbol'], $newContents);
    $newContents = str_replace('{samplestatus}', ($prof['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $newContents);
    $newContents = str_replace('{doctor}', $prof['doctor'], $newContents);
    $newContents = str_replace('{time}', date('d/m/Y', $prof['time']), $newContents);
    $newContents = str_replace('{DD}', date('d', $prof['time']), $newContents);
    $newContents = str_replace('{MM}', date('m', $prof['time']), $newContents);
    $newContents = str_replace('{YYYY}', date('Y', $prof['time']), $newContents);
  
    for ($i = 1; $i <= 18; $i++) { 
      if (!empty($prof['target'][$i - 1])) {
        $physical = $prof['target'][$i - 1];
        $newContents = str_replace('{target'. $i .'}', $physical['name'] ,$newContents);
        $newContents = str_replace('{unit'. $i .'}', $physical['unit'], $newContents);
        $newContents = str_replace('{range'. $i .'}', $physical['flag'], $newContents);
        $newContents = str_replace('{restar'. $i .'}', $physical['tar'], $newContents);
  
        if ($physical['tick'] == '<') {
          $newContents = str_replace('{rg'. $i .'}', $physical['value'], $newContents);
          $newContents = str_replace('{rn'. $i .'}', '', $newContents);
          $newContents = str_replace('{rt'. $i .'}', '', $newContents);
        }
        else if ($physical['tick'] == '>') {
          $newContents = str_replace('{rt'. $i .'}', $physical['value'], $newContents);
          $newContents = str_replace('{rn'. $i .'}', '', $newContents);
          $newContents = str_replace('{rg'. $i .'}', '', $newContents);
        }
        else {
          $newContents = str_replace('{rn'. $i .'}', $physical['value'], $newContents);
          $newContents = str_replace('{rt'. $i .'}', '', $newContents);
          $newContents = str_replace('{rg'. $i .'}', '', $newContents);
        }
      }
      else {
        $newContents = str_replace('{target'. $i .'}', '', $newContents);
        $newContents = str_replace("{rn$i}", '', $newContents);
        $newContents = str_replace("{rt$i}", '', $newContents);
        $newContents = str_replace("{rg$i}", '', $newContents);
        $newContents = str_replace('{unit'. $i .'}', '', $newContents);
        $newContents = str_replace('{flag'. $i .'}', '', $newContents);
        $newContents = str_replace('{range'. $i .'}', '', $newContents);
        $newContents = str_replace('{restar'. $i .'}', '', $newContents);
      }
    }  
  
    $zip->deleteName($fileToModify);
    $zip->addFromString($fileToModify, $newContents);
    $return = $zip->close();
    If ($return==TRUE){
      $result['status'] = 1;
      $result['link'] = 'https://'. $_SERVER['HTTP_HOST']. '/include/export/'. $name;
    }
  } else {
    $result['messenger'] = 'Không thể xuất file';
  }

  return $result;
}

function get() {
  global $data, $db, $result;
    
  $sql = 'select * from pet_phc_physical where id = '. $id;
  $query = $db->query($sql);
  $data = $query->fetch_assoc();
  $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_phc_physical_data a inner join pet_phc_target b on a.pid = $id and a.tid = b.id and b.module = 'physical'";
  $query = $db->query($sql);
  $data['target'] = array();
  $i = 1;
  while ($row = $query->fetch_assoc()) {
    $flag = explode(' - ', $row['flag']);
    $value = floatval($row['value']);
    if (count($flag) == 2) {
      $s = floatval($flag[0]);
      $e = floatval($flag[1]);
    }
    else {
      $s = 0; $e = 1;
    }
    $tick = '';
    $tar = '';
    if ($value < $s) {
      $tick = 'v';
      $tar = '<b>'. $i . '. '. $row['name'] .' giảm:</b> '. $row['down'];
      $i ++;
    }
    else if ($value > $e) {
      $tick = '^'; 
      $tar = '<b>'. $i . '. '. $row['name'] .' tăng:</b> '. $row['up'];
      $i ++;
    }
  
    $data['target'] []= array(
      'name' => $row['name'],
      'value' => $row['value'],
      'unit' => $row['unit'],
      'flag' => $row['flag'],
      'tar' => $tar,
      'tick' => $tick
    );
  }
  
  $sql = 'select value from pet_phc_config where name = "type" limit 1 offset '. $data['type'];
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['type'] = $row['value'];
  
  $sql = 'select value from pet_phc_config where name = "sampletype" limit 1 offset '. $data['sampletype'];
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['sampletype'] = $row['value'];
  
  $sql = 'select * from pet_phc_users where userid = '. $data['doctor'];
  $query = $db->query($sql);
  $doctor = $query->fetch_assoc();
  
  $data['doctor'] = $doctor['fullname'];
  
  $result['status'] = 1;
  $result['data'] = $data;

  return $result;
}

function init() {
  global $data, $db, $result;
    
  $sql = "select * from pet_phc_config where module = 'physical' and name = 'serial' limit 1";
  if (empty($serial = $db->fetch($sql))) {
    $serial = 1;
    $sql = "insert into pet_phc_config (module, name, value, alt) values('physical', 'serial', 1, 0)";
    $db->query($sql);
  }
  else $serial = intval($serial['value']) + 1;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['import'] = getImport();
  $result['serial'] = $serial;
  $result['type'] = typelist();
  $result['species'] = specieslist();
  $result['target'] = targetlist();
  $result['need'] = getneed();

  return $result;
}

function getneed() {
  global $data, $db, $result;
    
  $sql = "select id, xrayid, image from pet_phc_xray_row where sinhly < 0";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $sql = "select petname, weight, age, gender, species, c.name, c.phone, c.address from pet_phc_xray a inner join pet_phc_customer c on a.customerid = c.id where a.id = $row[xrayid]";
    $info = $db->fetch($sql);
    $list[$key]['petname'] = $info['petname'];
    $list[$key]['age'] = $info['age'];
    $list[$key]['weight'] = $info['weight'];
    $list[$key]['gender'] = $info['gender'];
    $list[$key]['species'] = $info['species'];
    $list[$key]['name'] = $info['name'];
    $list[$key]['phone'] = $info['phone'];
    $list[$key]['address'] = $info['address'];
    $list[$key]['image'] = explode(',', $row['image']);
    if (count($list[$key]['image']) == 1 && $list[$key]['image'][0] == '') $list[$key]['image'] = array();
  }

  return $list;
}

function import() {
  global $data, $db, $result;

  $userid = checkuserid();
  $data->name = str_replace(',', '', $data->name);
  $sql = "insert into pet_phc_import (price, module, userid, note, time) values($data->name, 'physical', $userid, '$data->note', ". time() .")";
  $db->query($sql);

  $result['status'] = 1;
  $result['import'] = getImport();
  return $result;
}

function statistic() {
  global $data, $db, $result;

  $data->start = strtotime(str_replace('-', '/', $data->start));
  $data->end = strtotime(str_replace('-', '/', $data->end)) + 60 * 60 * 24 - 1;

  $statis = array(
    'total' => 0,
    'price' => 0,
    'last' => '',
    'cycle' => ''
  );

  $sql = "select id from pet_phc_physical where time between $data->start and $data->end";
  $statis['total'] = $db->count($sql);

  $sql = "select sum(price) as price from pet_phc_import where module = 'physical' and (time between $data->start and $data->end)";
  $i = $db->fetch($sql);
  $statis['price'] = $i['price'];

  $sql = "select * from pet_phc_import where module = 'physical' order by time desc limit 2";
  $l = $db->all($sql);
  $c = count($l);

  switch ($c) {
    case 1:
      $statis['last'] = number_format($l[0]['price'], 0, '', ',') ." vào ngày ". date('d/m/Y', $l[0]['time']);
      $statis['cycle'] = intval((time() - $l[0]['time']) / 60 / 60 / 24) . " ngày";
      break;
    case 2:
      $statis['last'] = number_format($l[0]['price'], 0, '', ',') ." vào ngày ". date('d/m/Y', $l[0]['time']);
      $statis['cycle'] = intval(($l[0]['time'] - $l[1]['time']) / 60 / 60 / 24) . " ngày";
      break;
    default:
    $statis['last'] = "";
    $statis['cycle'] = "";
}

  $result['status'] = 1;
  $result['data'] = $statis;
  return $result;
}

function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getmore();
  return $result;
}

function updatephysical() {
  global $data, $db, $result;

  $userid = checkuserid();
  $image = implode(',', $data->image);

  $time = time();
  $sql = "update pet_phc_physical set customer = '$data->name', phone = '$data->phone', address = '$data->address', name = '$data->petname', weight = '$data->weight', age = '$data->age', gender = $data->gender, species = '$data->species', serial = '$data->serial', sampletype = '$data->sampletype', samplenumber = '$data->samplenumber', samplesymbol = '$data->samplesymbol', samplestatus = '$data->samplestatus', symptom = '$data->symptom', image = '$image' where id = $data->id";
  $db->query($sql);

  foreach ($data->target as $tid => $target) {
    $sql = "select * from pet_phc_physical_data where pid = $data->id and tid = $tid";
    if (strlen($target) == 0) $target = 0;
    if (empty($d = $db->fetch($sql))) $sql = "insert into pet_phc_physical_data (pid, tid, value) values ($data->id, $tid, '$target')";
    else $sql = "update pet_phc_physical_data set value = $target where id = $d[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_target where active = 1 and module = 'physical' order by id asc";
  $query = $db->query($sql);
  $list = $db->all($sql);
  $userid = checkuserid();
  $image = implode(',', $data->image);

  $time = time();
  $sql = "insert into pet_phc_physical (customer, phone, address, name, weight, age, gender, species, serial, sampletype, samplenumber, samplesymbol, samplestatus, symptom, doctor, time, image) values ('$data->name', '$data->phone', '$data->address', '$data->petname', '$data->weight', '$data->age', '$data->gender', '$data->species', '$data->serial', $data->sampletype, '$data->samplenumber', '$data->samplesymbol', '$data->samplestatus', '$data->symptom', $userid, $time, '$image')";
  $id = $db->insertid($sql);
  // $id = 18;
  if (isset($data->xrayid)) {
    $sql = "update pet_phc_xray_row set sinhly = $id where id = $data->xrayid";
    $db->query($sql);
  }

  foreach ($list as $target) {
    if (strlen($data->target->{$target['id']})) {
      $sql = "insert into pet_phc_physical_data (pid, tid, value) values ($id, $target[id], '". $data->target->{$target['id']} ."')";
      $db->query($sql);
    }
  }

  $serial = floatval($data->serial) + 1;
  $sql = 'select * from pet_phc_config where name = "serial"';
  $query = $db->query($sql);
  $config = $query->fetch_assoc();
  if (empty($config)) $sql = "insert into pet_phc_config (module, name, value) values('physical', 'serial', '$serial')";
  else $sql = "update pet_phc_config set value = '$serial' where module = 'physical' and name = 'serial'";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['need'] = getneed();
  $result['serial'] = $serial;
  return $result;
}

function printword() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_physical where id = $data->id";
  $prof = $db->fetch($sql);

  $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_phc_physical_data a inner join pet_phc_target b on a.pid = $data->id and a.tid = b.id and b.module = 'physical'";
  $l = $db->all($sql);
  $prof['target'] = array();
  $i = 1;
  foreach ($l as $row) {
    $flag = explode(' - ', $row['flag']);
    $value = floatval($row['value']);
    if (count($flag) == 2) {
      $s = floatval($flag[0]);
      $e = floatval($flag[1]);
    }
    else {
      $s = 0; $e = 1;
    }
    $tick = '';
    $tar = '';
    if ($value < $s) {
      $tick = '<';
      $tar = '<b>'. $row['name'] .' giảm:</b> '. $row['down'];
    }
    else if ($value > $e) {
      $tick = '>'; 
      $tar = '<b>'. $row['name'] .' tăng:</b> '. $row['up'];
      $i ++;
    }

    $prof['target'] []= array(
      'name' => $row['name'],
      'value' => $row['value'],
      'unit' => $row['unit'],
      'flag' => $row['flag'],
      'tar' => $tar,
      'tick' => $tick
    );
  }

  $sql = 'select value from pet_phc_config where name = "species" limit 1';
  $row = $db->fetch($sql);
  $prof['species'] = $row['value'];

  $sql = 'select value from pet_phc_config where name = "sampletype" limit 1';
  $row = $db->fetch($sql);
  $prof['sampletype'] = $row['value'];

  $sql = 'select * from pet_phc_users where userid = '. $prof['doctor'];
  $doctor = $db->fetch($sql);

  $prof['doctor'] = $doctor['fullname'];

  $html = file_get_contents ( DIR. '/include/template2.php');

  $sex = array(0 => '', 'Đực', 'Cái');
  $html = str_replace('{customer}', $prof['customer'], $html);
  $html = str_replace('{address}', $prof['address'], $html);
  $html = str_replace('{name}', $prof['name'], $html);
  $html = str_replace('{weight}', $prof['weight'], $html);
  $html = str_replace('{age}', $prof['age'], $html);
  $html = str_replace('{gender}', $sex[$prof['gender']], $html);
  $html = str_replace('{type}', $prof['species'], $html);
  $html = str_replace('{sampleid}', $prof['id'], $html);
  $html = str_replace('{serial}', $prof['serial'], $html);
  $html = str_replace('{sampletype}', $prof['sampletype'], $html);
  $html = str_replace('{samplenumber}', $prof['samplenumber'], $html);
  $html = str_replace('{samplesymbol}', $prof['samplesymbol'], $html);
  $html = str_replace('{samplestatus}', ($prof['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $html);
  $html = str_replace('{doctor}', $prof['doctor'], $html);
  $time = $prof['time'];
  $html = str_replace('{time}', date('d/m/Y', $time), $html);
  $html = str_replace('{DD}', date('d', $time), $html);
  $html = str_replace('{MM}', date('m', $time), $html);
  $html = str_replace('{YYYY}', date('Y', $time), $html);

  for ($i = 1; $i <= 18; $i++) { 
    if (!empty($prof['target'][$i - 1])) {
      $physical = $prof['target'][$i - 1];
      $html = str_replace('{target'. $i .'}', $physical['name'] ,$html);
      $html = str_replace('{unit'. $i .'}', $physical['unit'], $html);
      $html = str_replace('{range'. $i .'}', $physical['flag'], $html);
      $html = str_replace('{restar'. $i .'}', $physical['tar'], $html);

      if ($physical['tick'] == '<') {
        $html = str_replace('{rg'. $i .'}', $physical['value'], $html);
        $html = str_replace('{rn'. $i .'}', '', $html);
        $html = str_replace('{rt'. $i .'}', '', $html);
      }
      else if ($physical['tick'] == '>') {
        $html = str_replace('{rt'. $i .'}', $physical['value'], $html);
        $html = str_replace('{rn'. $i .'}', '', $html);
        $html = str_replace('{rg'. $i .'}', '', $html);
      }
      else {
        $html = str_replace('{rn'. $i .'}', $physical['value'], $html);
        $html = str_replace('{rt'. $i .'}', '', $html);
        $html = str_replace('{rg'. $i .'}', '', $html);
      }
    }
    else {
      $html = str_replace('{target'. $i .'}', '', $html);
      $html = str_replace('{rn'. $i .'}', '', $html);
      $html = str_replace('{rt'. $i .'}', '', $html);
      $html = str_replace('{rg'. $i .'}', '', $html);
      $html = str_replace('{unit'. $i .'}', '', $html);
      $html = str_replace('{range'. $i .'}', '', $html);
      $html = str_replace('{restar'. $i .'}', '', $html);
    }
  }  

  $result['status'] = 1;
  $result['html'] = $html;

  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = 'delete from pet_phc_physical where id = '. $data->id;
  $query = $db->query($sql);
  $sql = 'delete from pet_phc_physical_data where pid = '. $data->id;
  $query = $db->query($sql);
  $sql = 'update pet_phc_xray_row set sinhly = 0 where sinhly = '. $data->id;
  $query = $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function insertselect() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_config where module = 'physical' and name = '$data->typename' and value = '$data->typevalue'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_phc_config (module, name, value) values('physical', '$data->typename', '$data->typevalue')";
    $db->query($sql);
  }

  $result['status'] = 1;
  if ($data->typename == 'sampletype') $result['list'] = typelist();
  else $result['list'] = specieslist();
  return $result;
}

// function suggestinsert() {
//   global $data, $db, $result;
    
//   $sql = "select * from pet_phc_config where name = '$type' and value = '$name'";
//   $query = $db->query($sql);
//   $data = $query->fetch_assoc();
  
//   if (!empty($data)) {
//     $result['messenger'] = 'Đã tồn tại';
//   }
//   else {
//     $sql = "insert into pet_phc_config (name, value) values ('$type', '$name')";
//     $query = $db->query($sql);
    
//     $list = array();
//     $sql = 'select * from pet_phc_config where name = "'. $type .'" order by id asc';
//     $query = $db->query($sql);
//     $index = 0;
//     while ($row = $query->fetch_assoc()) {
//       $list []= array(
//         'id' => $index ++,
//         'name' => $row['value']
//       );
//     }
//     $result['list'] = $list;
//     $result['status'] = 1;
//   }

//   return $result;
// }

function checkcustomer() {
  global $db, $data;
  $sql = 'select * from pet_phc_customer where phone = "'. $data->phone .'"';
  $c = $db->fetch($sql);

  if (empty($c)) {
    $sql = "insert into pet_phc_customer (name, phone, address) values('$data->name', '$data->phone', '$data->address')";
    $c['id'] = $db->insertid($sql);
  }
  else {
    $sql = "update pet_phc_customer set name = '$data->name', address = '$data->address' where phone = '$data->phone'";
    $db->query($sql);
  }
  return $c['id'];
}

function getImport() {
  global $db, $data;

  $sql = "select a.id, a.price, a.time, a.note, b.fullname as name from pet_phc_import a inner join pet_phc_users b on a.userid = b.userid where a.module = 'physical' order by id desc limit 20";
  $l = $db->all($sql);

  foreach ($l as $i => $row) {
    $l[$i]['time'] = date('d/m/Y', $row['time']);
  }
  return $l;
}

function getlist() {
  global $db, $data;

  $filter = $data->filter;
  $start = isodatetotime($filter->start);
  $end = isodatetotime($filter->end) + 60 * 60 * 24 - 1;
  $sql = "select a.*, c.fullname as doctor from pet_phc_physical a inner join pet_phc_users c on a.doctor = c.userid where (a.phone like '%$filter->key%' or a.customer like '%$filter->key%') and (time between $start and $end) order by id desc";
  $query = $db->query($sql);
  $list = array();
  
  while ($row = $query->fetch_assoc()) {
    $sql = "select tid, value from pet_phc_physical_data where pid = $row[id]";
    $row['target'] = $db->obj($sql, 'tid', 'value');
    $row['image'] = parseimage($row['image']);
    $row['time'] = date('d/m/Y', $row['time']);
    $list []= $row;
  }
  return $list;
}

function typelist() {
  global $db;

  $sql = "select id, value as name from pet_phc_config where module = 'physical' and name = 'sampletype' order by value asc";
  return $db->all($sql);
}

function specieslist() {
  global $db;

  $sql = "select id, value as name from pet_phc_config where module = 'physical' and name = 'species' order by value asc";
  return $db->all($sql);
}

function targetlist() {
  global $db;

  $sql = "select * from pet_phc_target where active = 1 and module = 'physical' order by id asc";

  return $db->all($sql);
}
