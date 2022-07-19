<?php
$x = array();
$xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
foreach ($xr as $key => $value) {
  $x[$value] = $key;
}

function auto() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function reduce() {
  global $db, $data, $result;

  $sql = "update pet_phc_vaccine set status = 4 where calltime < $data->date and status < 3";
  $db->query($sql);
  $sql = "update pet_phc_usg set status = 8 where calltime < $data->date and status < 7";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function config() {
  global $db, $data, $result;

  foreach ($data as $name => $value) {
    $sql = "select * from pet_phc_config where module = 'site' and name = '$name'";
    if (empty($r = $db->fetch($sql))) $sql = "insert into pet_phc_config (module, name, value, alt) values ('site', '$name', '$value', 0)";
    else $sql = "update pet_phc_config set value = '$value' where id = $r[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  return $result;
}

function recycle() {
  global $db, $data, $result;

  // foreach data->option
  // lấy danh sách data->doctor + db(user not in doctor), chuyển cho danh sách db(doctor - data->doctor)
  $sql = "select userid from pet_phc_users where userid not in (select userid from pet_phc_user_per where module = 'doctor' and type = 1)";
  $doctor = array_merge($data->doctor, $db->arr($sql, 'userid'));
  $sql = "select userid from pet_phc_user_per where module = 'doctor' and type = 1 and userid not in (". implode(',', $data->doctor) .")";
  $target = $db->arr($sql, 'userid');

  if (in_array('vaccine', $data->option) !== false) {
    $sql = "select a.id, b.fullname as name from pet_phc_vaccine a inner join pet_phc_users b on a.userid = b.userid where (a.status < 3 or a.status = 5) and a.userid in (". implode(',', $doctor) .")";
    $list = $db->all($sql);

    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);

    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_phc_vaccine set userid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }

  $t = time() - 60 * 60 * 24 * 90; 
  $sql = "update pet_phc_vaccine status = 4 where status < 3 and calltime < $t"; // sau 3 tháng không nhắc nữa
  $db->query($sql);

  if (in_array('vaccine', $data->option) !== false) {
    $sql = "select a.id, b.fullname as name from pet_phc_usg a inner join pet_phc_users b on a.userid = b.userid where (a.status < 7 or a.status = 9) and a.userid in (". implode(',', $doctor) .")";
    $list = $db->all($sql);

    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);

    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_phc_usg set userid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  return $result;
}

function vaccine() {
  global $db, $data, $result;
  
  $sql = "select * from pet_phc_config where name = 'vaccine-comma'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_phc_config (module, name, value) values ('vaccine', 'vaccine-comma', ';')";
    $db->query($sql);
    $c['value'] = '-';
  }

  $result['status'] = 1;
  $result['code'] = $c['value'];
  return $result;
}

function savevaccine() {
  global $db, $data, $result;
  
  $sql = "update pet_phc_config set value = '$data->comma' where name = 'vaccine-comma'";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function spa() {
  global $db, $data, $result;
  $sql = "select id, name, value, alt from pet_phc_config where module = 'spa' order by value asc";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function type() {
  global $db, $data, $result;
  $sql = "select * from pet_phc_type where active = 1";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function usg() {
  global $db, $data, $result;
  $sql = "select id, name from pet_phc_config where module = 'usg'";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function filter() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['list'] = filterUser();
  return $result;
}

function toggle() {
  global $db, $data, $result;

  $sql = "select * from pet_phc_user_per where userid = $data->userid and module = '$data->per'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_phc_user_per (userid, module, type) values ($data->userid, '$data->per', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_phc_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function change() {
  global $db, $data, $result;

  $reversal = array(0 => 1, 2, 0);
  $sql = "select * from pet_phc_user_per where userid = $data->userid and module = '$data->per'";
  if (empty($p = $db->fetch($sql))) {
    $sql = "insert into pet_phc_user_per (userid, module, type) values ($data->userid, '$data->per', 1)";
    $db->query($sql);
  }
  else {
    $rev = $reversal[$p['type']];
    $sql = "update pet_phc_user_per set type = $rev where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function manager() {
  global $db, $data, $result;

  $sql = "select * from pet_phc_user_per where userid = $data->userid and module = 'manager'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_phc_user_per (userid, module, type) values ($data->userid, 'manager', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_phc_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function admin() {
  global $db, $data, $result;

  $sql = "select * from pet_phc_user_per where userid = $data->userid and module = 'admin'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_phc_user_per (userid, module, type) values ($data->userid, 'admin', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_phc_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function insert() {
  global $db, $data, $result;

  $sql = "update pet_phc_users set active = 1 where userid = $data->userid";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = filterUser();
  $result['admin'] = getList();
  $result['messenger'] = 'Đã thêm nhân viên';
  
  return $result;
}

function update() {
  global $db, $data, $result;
  
  $sql = "update pet_phc_users set fullname = '$data->fullname', name = '$data->fullname' where userid = $data->userid";
  $db->query($sql);

  foreach ($data->module as $name => $value) {
    $sql = "select * from pet_phc_user_per where module = '$name' and userid = $data->userid";
    $userinfo = $db->fetch($sql);
  
    if (empty($userinfo)) {
      $sql = "insert into pet_phc_user_per (userid, module, type) values ($data->userid, '$name', '$value')";
    }
    else {
      $sql = "update pet_phc_user_per set type = '$value' where module = '$name' and userid = $data->userid";
    }
    $db->query($sql);
  }
    
  $result['status'] = 1;
  $result['list'] = getList();
  $result['config'] = getConfig();
  return $result;
}

function remove() {
  global $db, $data, $result;
  
  $sql = "update pet_phc_users set active = 0 where userid = $data->userid";
  $db->query($sql);

  $sql = "delete from pet_phc_user_per where userid = $data->userid";
  $db->query($sql);

  $sql = "delete from pet_phc_session where userid = $data->userid";
  $db->query($sql);

  $sql = "delete from pet_phc_row where user_id = $data->userid";
  $db->query($sql);
    
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function getList() {
  global $db;

  $module = array(
    'spa' => 0,
    'vaccine' => 0,
    'schedule' => 0,
    'item' => 0,
    'kaizen' => 0,
    'drug' => 0,
    'price' => 0,
    'ride' => 0,
    'profile' => 0,
    'physical' => 0,
    'his' => 0,
    'sieuam' => 0,
    'xquang' => 0,
    'transport' => 0,
    'excel' => 0,
    'hotel' => 0,
    'other' => 0,
    'luong' => 0,
    'accounting' => 0,
  );

  $sql = 'select name, username, fullname, userid from pet_phc_users where active = 1';
  $list = $db->all($sql);
  
  foreach ($list as $index => $row) {
    $sql = 'select * from pet_phc_user_per where userid = '. $row['userid'];
    $query = $db->query($sql);
    $temp = $module;
    while ($info = $query->fetch_assoc()) {
      $temp[$info['module']] = $info['type'];
    }
    $list[$index]['module'] = $temp;
  }
  return $list;
}

function getPer() {
  global $db;

  $sql = "select name, 0 as per from pet_phc_config where module = 'setting'";
  $c = $db->obj($sql, 'name', 'per');
  return $c;
}

function getConfig() {
  global $db;
  
  $userid = checkuserid();
  $sql = 'select * from pet_phc_user_per where userid = '. $userid;
  return $db->obj($sql, 'module', 'type');
}

function filterUser() {
  global $db, $data;
  
  $sql = 'select userid from pet_phc_users';
  $list = $db->obj($sql, 'userid', 'userid');
  
  $sql = "select userid, fullname, username from pet_phc_users where active = 0 and (username like '%$data->key%' or fullname like '%$data->key%')";

  return $db->all($sql);
}

function signup() {
  global $data, $db, $result;
  $username = mb_strtolower($data->username);
  $password = $data->password;

  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  $sql = 'select userid, password from `pet_phc_users` where LOWER(username) = "'. $username .'"';
  if (!empty($user = $db->fetch($sql))) $result['messenger'] = 'Tên người dùng đã tồn tại';
  else {
    $time = time();
    $sql = "insert into pet_phc_users (username, name, fullname, password, photo, regdate, active) values ('$data->username', '', '$data->fullname', '". $crypt->hash_password($data->password) ."', '', $time, 1)";
    $userid = $db->insertid($sql);
    
    $result['status'] = 1;
    $result['list'] = getList();
  }
  return $result;
}

function updateuser() {
  global $data, $db, $result;
  
  $password = $data->password;

  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);
  
  $sql = "update pet_phc_users set username = '$data->username', name = '$data->fullname', fullname = '$data->fullname', password = '". $crypt->hash_password($data->password) ."' where userid = $data->userid";
  $userid = $db->insertid($sql);
    
  $result['status'] = 1;
  $result['list'] = getList();
  
  return $result;
}

function customercell() {
  global $db, $result;

  $arr = array('name' => 'A2', 'phone' => 'B2', 'address' => 'C2');
  $sql = "select * from pet_phc_config where module = 'customer-config'";
  $c = $db->obj($sql, 'name', 'value');
  foreach ($arr as $key => $value) {
    if (!empty($c[$key])) $arr[$key] = $c[$key];
  }
  $result['status'] = 1;
  $result['data'] = $arr;
  return $result;
}

function customersave() {
  global $db, $data, $result;

  foreach ($data as $key => $value) {
    $sql = "select * from pet_phc_config where name = '$key' and module = 'customer-config'";
    if (!empty($d = $db->fetch($sql))) $sql = "update pet_phc_config set value = '$value' where name = '$key' and module = 'customer-config'";
    else $sql = "insert into pet_phc_config (module, name, value, alt) values('customer-config', '$key', '$value', 0)";
    $db->query($sql);
  }
  $result['status'] = 1;
  return $result;
}

function layso($chuoi) {
  return intval(preg_replace('/[^0-9]/', '', $chuoi));
}

function customer() {
  global $data, $db, $result, $dir, $x, $xr, $_FILES;
  
  $dir = str_replace('/server', '/', ROOTDIR);
  include $dir .'include/PHPExcel/IOFactory.php';
  $file = $_FILES['file']['tmp_name'];
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);
  
  $sheet = $objPHPExcel->getSheet(0); 
  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $name = $data->name[0];
  $phone = $data->phone[0];
  $address = $data->address[0];
  $so = layso($data->name);
  for ($j = $so; $j <= $highestRow; $j ++) {
    $x = array(
      'name' => $sheet->getCell($name . $j)->getValue(),
      'phone' => $sheet->getCell($phone . $j)->getValue(),
      'address' => $sheet->getCell($address . $j)->getValue(),
    );
    if (!empty($x['phone'])) {
      $sql = "select * from pet_phc_customer where phone = '$x[phone]'";
      if (!empty($c = $db->fetch($sql))) $sql = "update pet_phc_customer set name = '$x[name]', address = '$x[address]' where id = $c[id]";
      else $sql = "insert into pet_phc_customer (name, phone, address) values('$x[name]', '$x[phone]', '$x[address]')";
      $db->query($sql);
    }
  }

  $result['messenger'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}

