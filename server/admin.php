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

function luucauhinhvaccine() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_config where module = 'vaccine' and name = 'ngay'";
  if (empty($config = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('vaccine', 'ngay', $data->ngay, 0)";
    $config = ['id' => $db->insertid($sql)];
  }
  else {
    $sql = "update pet_". PREFIX ."_config set value = $data->ngay where id = $config[id]";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  return $result;
}

function getform() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_form where name = '$data->form'";
  $form = $db->fetch($sql);
  if (empty($form['value'])) $form['value'] = '';
  $result['status'] = 1;
  $result['html'] = $form['value'];
  return $result;
}

function reduce() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_vaccine set status = 4 where calltime < $data->date and status < 3";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_usg set status = 8 where calltime < $data->date and status < 7";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function configform() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_form where name = '$data->form'";
  $form = $db->fetch($sql);

  if (empty($form)) $sql = "insert into pet_". PREFIX ."_form (name, value) values('$data->form', '$data->html')";
  else $sql = "update pet_". PREFIX ."_form set value = '$data->html' where id = $form[id]";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu';
  return $result;
}

function config() {
  global $db, $data, $result;

  foreach ($data->config as $name => $value) {
    $sql = "select * from pet_". PREFIX ."_config where module = 'site' and name = '$name'";
    if (empty($r = $db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values ('site', '$name', '$value', 0)";
    else $sql = "update pet_". PREFIX ."_config set value = '$value' where id = $r[id]";
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
  $sql = "select userid from pet_". PREFIX ."_users where userid not in (select userid from pet_". PREFIX ."_user_per where module = 'doctor' and type = 1)";
  $doctor = array_merge($data->doctor, $db->arr($sql, 'userid'));
  $sql = "select userid from pet_". PREFIX ."_user_per where module = 'doctor' and type = 1 and userid not in (". implode(',', $data->doctor) .")";
  $target = $db->arr($sql, 'userid');

    if (!empty($data->userid)) {
      $sql = "update pet_". PREFIX ."_vaccine set userid = $data->userid where (status < 3 or status = 5) and userid in (". implode(',', $doctor) .")";
      $db->query($sql);
    }
    else {
      $sql = "select a.id, b.fullname as name from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users b on a.userid = b.userid where (a.status < 3 or a.status = 5) and a.userid in (". implode(',', $doctor) .")";
      $list = $db->all($sql);

      $l = count($list);
      $d = count($target);
      $n = (int) ($l / $d);
  
      $c = 0;
      for ($i = 0; $i < $l; $i++) { 
        if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
        $sql = "update pet_". PREFIX ."_vaccine set userid = $target[$c] where id = ". $list[$i]['id'];
        $db->query($sql);
      }
    }

  $t = time() - 60 * 60 * 24 * 90; 
  $sql = "update pet_". PREFIX ."_vaccine status = 4 where status < 3 and calltime < $t"; // đã trôi qua 3 tháng không nhắc nữa
  $db->query($sql);

  if (!empty($data->userid)) {
    $sql = "update pet_". PREFIX ."_usg set userid = $data->userid where (status < 7 or status = 9) and userid in (". implode(',', $doctor) .")";
    $db->query($sql);
  }
  else {
    $sql = "select a.id, b.fullname as name from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users b on a.userid = b.userid where (a.status < 7 or a.status = 9) and a.userid in (". implode(',', $doctor) .")";
    $list = $db->all($sql);
    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);
    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_". PREFIX ."_usg set userid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }
  
  if (!empty($data->userid)) {
    $sql = "update pet_". PREFIX ."_xray set doctorid = $data->userid where doctorid in (". implode(',', $doctor) .")";
    $db->query($sql);
  }
  else {
    $sql = "select a.id, b.fullname as name from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_users b on a.doctorid = b.userid where a.userid in (". implode(',', $doctor) .")";
    $list = $db->all($sql);
    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);
    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_". PREFIX ."_xray set doctorid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  return $result;
}

function vaccine() {
  global $db, $data, $result;
  
  $sql = "select * from pet_". PREFIX ."_config where name = 'vaccine-comma'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value) values ('vaccine', 'vaccine-comma', ';')";
    $db->query($sql);
    $c['value'] = '-';
  }

  $result['status'] = 1;
  $result['code'] = $c['value'];
  return $result;
}

function savevaccine() {
  global $db, $data, $result;
  
  $sql = "update pet_". PREFIX ."_config set value = '$data->comma' where name = 'vaccine-comma'";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function spa() {
  global $db, $data, $result;
  $sql = "select id, name, value, alt from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function type() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_type where active = 1";
  $type = $db->all($sql);

  $sql = "select * from pet_". PREFIX ."_config where module = 'vaccine' and name = 'ngay'";
  if (empty($config = $db->fetch($sql))) $ngay = 0;
  else $ngay = $config['value'];

  $result['status'] = 1;
  $result['list'] = $type;
  $result['ngay'] = $ngay;
  return $result;
}

function usg() {
  global $db, $data, $result;
  $sql = "select id, name from pet_". PREFIX ."_config where module = 'usg'";
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

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->userid and module = '$data->per'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->userid, '$data->per', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
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
  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->userid and module = '$data->per'";
  if (empty($p = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->userid, '$data->per', 1)";
    $db->query($sql);
  }
  else {
    $rev = $reversal[$p['type']];
    $sql = "update pet_". PREFIX ."_user_per set type = $rev where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function manager() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->userid and module = 'manager'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->userid, 'manager', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function admin() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->userid and module = 'admin'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->userid, 'admin', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function insert() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_users set active = 1 where userid = $data->userid";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = filterUser();
  $result['admin'] = getList();
  $result['messenger'] = 'Đã thêm nhân viên';
  
  return $result;
}

function update() {
  global $db, $data, $result;
  
  $sql = "update pet_". PREFIX ."_users set fullname = '$data->fullname', name = '$data->fullname' where userid = $data->userid";
  $db->query($sql);

  foreach ($data->module as $name => $value) {
    $sql = "select * from pet_". PREFIX ."_user_per where module = '$name' and userid = $data->userid";
    $userinfo = $db->fetch($sql);
  
    if (empty($userinfo)) {
      $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->userid, '$name', '$value')";
    }
    else {
      $sql = "update pet_". PREFIX ."_user_per set type = '$value' where module = '$name' and userid = $data->userid";
    }
    $db->query($sql);
  }
    
  $result['status'] = 1;
  $result['list'] = getList();
  $result['config'] = getConfig();
  return $result;
}

function xoanhanvien() {
  global $db, $data, $result;

  $time = time();
  // chuyển sang cho người dùng khác
  $sql = "update pet_". PREFIX ."_vaccine set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_usg set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_spa set doctorid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_import set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_hotel set returnuserid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_exam set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_kaizen set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_physical set doctor = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_profile set doctor = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_ride set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_sieuam set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xquang set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xray set doctorid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xray_read set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  
  // xóa đăng ký lịch
  $sql = "delete from pet_". PREFIX ."_row where user_id = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_row_log where user_id = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_notify where user_id = $data->nhanvienxoa";
  $db->query($sql);

  // xóa lương
  $sql = "delete from pet_". PREFIX ."_luong_nhanvien where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_luong_tra where userid = $data->nhanvienxoa";
  $db->query($sql);

  // xóa đăng nhập
  $sql = "delete from pet_". PREFIX ."_session where userid = $data->nhanvienxoa";
  $db->query($sql);

  // xóa quyền
  $sql = "delete from pet_". PREFIX ."_user_per where userid = $data->nhanvienxoa";
  $db->query($sql);

  // xóa người dùng
  $sql = "delete from pet_". PREFIX ."_users where userid = $data->nhanvienxoa";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function getList() {
  global $db;

  $module = [
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
    'work' => 0,
    'nhantin' => 0,
  ];

  $sql = "select name, username, fullname, userid, placeid, birthday from pet_". PREFIX ."_users where active = 1 and userid <> 1";
  $list = $db->all($sql);
  
  foreach ($list as $index => $row) {
    $sql = "select * from pet_". PREFIX ."_config where id = $row[placeid]";
    if (empty($place = $db->fetch($sql))) $place = array('name' => 'Chưa chọn');
    $list[$index]['place'] = $place['name'];
    $list[$index]['birthday'] = date(DATE_ISO8601, $row['birthday']);

    $sql = "select * from pet_". PREFIX ."_user_per where userid = $row[userid]";
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

  $sql = "select name, 0 as per from pet_". PREFIX ."_config where module = 'setting'";
  $c = $db->obj($sql, 'name', 'per');
  return $c;
}

function getConfig() {
  global $db;
  
  $userid = checkuserid();
  $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid";
  return $db->obj($sql, 'module', 'type');
}

function filterUser() {
  global $db, $data;
  
  $sql = "select userid from pet_". PREFIX ."_users";
  $list = $db->obj($sql, 'userid', 'userid');
  
  $sql = "select userid, fullname, username from pet_". PREFIX ."_users where active = 0 and (username like '%$data->key%' or fullname like '%$data->key%')";

  return $db->all($sql);
}

function signup() {
  global $data, $db, $result;
  $username = mb_strtolower($data->username);
  $password = $data->password;

  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);
  $birthday = 0;
  if (isset($data->birthday)) $birthday = isodatetotime($data->birthday);

  $sql = "select userid, password from `pet_". PREFIX ."_users` where LOWER(username) = '$username'";
  if (!empty($user = $db->fetch($sql))) $result['messenger'] = 'Tên người dùng đã tồn tại';
  else {
    $time = time();
    $sql = "insert into pet_". PREFIX ."_users (username, name, fullname, password, photo, regdate, birthday, active) values ('$data->username', '', '$data->fullname', '". $crypt->hash_password($data->password) ."', '', $time, $birthday, 1)";
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
  $birthday = isodatetotime($data->birthday);
  
  $sql = "update pet_". PREFIX ."_users set username = '$data->username', name = '$data->fullname', fullname = '$data->fullname', password = '". $crypt->hash_password($data->password) ."', birthday = $birthday where userid = $data->userid";
  $userid = $db->insertid($sql);
    
  $result['status'] = 1;
  $result['list'] = getList();
  
  return $result;
}

function customercell() {
  global $db, $result;

  $arr = array('name' => 'A2', 'phone' => 'B2', 'address' => 'C2');
  $sql = "select * from pet_". PREFIX ."_config where module = 'customer-config'";
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
    $sql = "select * from pet_". PREFIX ."_config where name = '$key' and module = 'customer-config'";
    if (!empty($d = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_config set value = '$value' where name = '$key' and module = 'customer-config'";
    else $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('customer-config', '$key', '$value', 0)";
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
      $sql = "select * from pet_". PREFIX ."_customer where phone = '$x[phone]'";
      if (!empty($c = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_customer set name = '$x[name]', address = '$x[address]' where id = $c[id]";
      else $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$x[name]', '$x[phone]', '$x[address]')";
      $db->query($sql);
    }
  }

  $result['messenger'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}

function placeinit() {
  global $db, $result, $data;

  $result['status'] = 1;
  $result['list'] = placelist();
  return $result;
}

function placelist() {
  global $db, $result, $data;

  $sql = "select id, name from pet_". PREFIX ."_config where module = 'place' and alt = 1";
  $list = $db->all($sql);
  return $list;
}

function placeremove() {
  global $db, $result, $data;

  $sql = "update pet_". PREFIX ."_config where set alt = 0 where id = $data->placeid";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = placelist();
  return $result;
}

function placeupdate() {
  global $db, $result, $data;

  $sql = "update pet_". PREFIX ."_config set name = '$data->name' where id = $data->placeid";
  $db->query($sql);

  $sql = "update pet_". PREFIX ."_users set placeid = $data->placeid where userid = $data->userid";
  $db->query($sql);

  $result['status'] = 1;
  $result['placelist'] = placelist();
  $result['adminlist'] = getlist();
  return $result;
}

function placeinsert() {
  global $db, $result, $data;

  $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('place', '$data->name', '', 1)";
  $id = $db->insertid($sql);

  $sql = "update pet_". PREFIX ."_users set placeid = $id where userid = $data->userid";
  $db->query($sql);

  $result['status'] = 1;
  $result['placelist'] = placelist();
  $result['adminlist'] = getlist();
  return $result;
}

function placeselect() {
  global $db, $result, $data;

  $sql = "update pet_". PREFIX ."_users set placeid = $data->placeid where userid = $data->userid";
  $db->query($sql);

  $result['status'] = 1;
  $result['adminlist'] = getlist();
  return $result;
}

function thaydoichotlich() {
  global $db, $result, $data;

  $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'chotlich'";
  if (empty($chotlich = $db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('config', 'chotlich', '$data->loaichot', '')";
  else $sql = "update pet_". PREFIX ."_config set value = '$data->loaichot' where id = $chotlich[id]";
  $db->query($sql);

  $result['status'] = 1;
  $result['chotlich'] = $data->loaichot;
  return $result;
}

function cauhinhlich() {
  global $db, $result, $data;

  $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'schedule-config'";
  if (empty($config = $db->fetch($sql))) {
    $config = [
      ['thu' => '', 'gioihan' => 0, 'phat' => 0],
      ['thu' => 'T2', 'gioihan' => 3, 'phat' => 0],
      ['thu' => 'T3', 'gioihan' => 3, 'phat' => 0],
      ['thu' => 'T4', 'gioihan' => 3, 'phat' => 0],
      ['thu' => 'T5', 'gioihan' => 3, 'phat' => 0],
      ['thu' => 'T6', 'gioihan' => 3, 'phat' => 0],
      ['thu' => 'T7', 'gioihan' => 2, 'phat' => 1],
      ['thu' => 'CN', 'gioihan' => 1, 'phat' => 1],
    ];
    $json = json_encode($config);
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('config', 'schedule-config', '$json', '')";
    $db->query($sql);
  }
  else $config = json_decode($config['value']);

  $sql = "select * from pet_". PREFIX ."_config where name = 'thoigianmo'";
  if (empty($thoigianmo = $db->fetch($sql))) $thoigianmo = ['value' => '0000-00-00T00:00:00+07:00'];
  $sql = "select * from pet_". PREFIX ."_config where name = 'thoigiandong'";
  if (empty($thoigiandong = $db->fetch($sql))) $thoigiandong = ['value' => '0000-00-00T00:00:00+07:00'];

  $sql = "select * from pet_". PREFIX ."_config where name = 'khoathoigian'";
  if (empty($khoathoigian = $db->fetch($sql))) $khoathoigian = ['value' => '0'];

  $result['status'] = 1;
  $result['dulieu'] = $config;
  $result['khoathoigian'] = $khoathoigian['value'];
  $result['thoigianmo'] = chuyenthoigianiso($thoigianmo['value']);
  $result['thoigiandong'] = chuyenthoigianiso($thoigiandong['value']);
  return $result;
}

function chuyenthoigianiso($thoigian) {
  if (empty($thoigian)) $thoigian = time();
  return date(DATE_ISO8601, $thoigian);
}

function luucauhinhlich() {
  global $db, $result, $data;

  $json = json_encode($data->dulieu);
  $sql = "update pet_". PREFIX ."_config set value = '$json' where module = 'config' and name = 'schedule-config'";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  return $result;
}

function luucauhinhthoigian() {
  global $db, $result, $data;

  $data->thoigianmo = strtotime($data->thoigianmo);
  $data->thoigiandong = strtotime($data->thoigiandong);

  $sql = "select * from pet_". PREFIX ."_config where name = 'khoathoigian'";
  if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('config', 'khoathoigian', $data->khoathoigian, 0)";
  else $sql = "update pet_". PREFIX ."_config set value = $data->khoathoigian where name = 'khoathoigian'";
  $db->query($sql);

  if (intval($data->khoathoigian)) {
    $sql = "select * from pet_". PREFIX ."_config where name = 'thoigianmo'";
    if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('config', 'thoigianmo', $data->thoigianmo, 0)";
    else $sql = "update pet_". PREFIX ."_config set value = $data->thoigianmo where name = 'thoigianmo'";
    $db->query($sql);
  
    $sql = "select * from pet_". PREFIX ."_config where name = 'thoigiandong'";
    if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('config', 'thoigiandong', $data->thoigiandong, 0)";
    else $sql = "update pet_". PREFIX ."_config set value = $data->thoigiandong where name = 'thoigiandong'";
    $db->query($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_config set value = 0 where name = 'thoigiandong'";
    $db->query($sql);
    $sql = "update pet_". PREFIX ."_config set value = 0 where name = 'thoigianmo'";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  return $result;
}

function khoitaonhantin() {
  global $db, $result, $data;

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
  $result['cauhinh'] = cauhinhnhantin();
  return $result;
}

function khoitaodaguitin() {
  global $db, $result, $data;

  $result['status'] = 1;
  $result['danhsach'] = danhsachdaguitin();
  return $result;
}

function khoitaoloaitru() {
  global $db, $result, $data;

  $result['status'] = 1;
  $result['danhsach'] = danhsachloaitru();
  $result['cauhinh'] = cauhinhnhantin();
  return $result;
}

function cauhinhnhantin() {
  global $db;

  $cauhinh = [
    'min' => laycauhinh('min'),
    'max' => laycauhinh('max'),
    'mautin' => danhsachmautin(),
    'danhsachloai' => danhsachloai()
  ];

  if (empty($cauhinh['min'])) $cauhinh['min'] = 1;
  if (empty($cauhinh['max'])) $cauhinh['max'] = 2;
  return $cauhinh;
}

function luumautin() {
  global $db, $result, $data;

  if ($data->id) {
    // cập nhật mẫu tin
    // thêm những cái không có
    // xóa những cái không có
    $sql = "update pet_". PREFIX ."_vaccinemautin set mautin = '$data->mautin', lich = $data->lich, loainhac = $data->loainhac where id = $data->id";
    $db->query($sql);

    foreach ($data->loai as $idloai) {
      $sql = "select * from pet_". PREFIX ."_vaccinelienketmautin where idmautin = $data->id and idloai = $idloai";
      if (empty($db->fetch($sql))) {
        $sql = "insert into pet_". PREFIX ."_vaccinelienketmautin (idloai, idmautin) values ($idloai, $data->id)";
        $db->query($sql);
      }
    }
    $sql = "delete from pet_". PREFIX ."_vaccinelienketmautin where idmautin = $data->id and idloai not in (". implode(', ', $data->loai) .")";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_vaccinemautin (mautin, lich, loainhac) values ('$data->mautin', $data->lich, $data->loainhac)";
    $idmautin = $db->insertid($sql);

    foreach ($data->loai as $idloai) {
      $sql = "insert into pet_". PREFIX ."_vaccinelienketmautin (idloai, idmautin) values ($idloai, $idmautin)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachmautin();
  $result['danhsachloai'] = danhsachloai();
  return $result;
}

function danhsachloai() {
  global $db;

  $sql = "select id, name from pet_". PREFIX ."_type where active = 1 order by id asc";
  return $db->all($sql);
}

function danhsachmautin() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vaccinemautin order by lich desc, id desc";
  $danhsach = $db->all($sql);
  $hauto = [' tái chủng', ' sinh', ' tái khám'];

  foreach ($danhsach as $thutu => $mautin) {
    if ($mautin['lich'] == 0) $thoigian = 'Đúng ngày';
    else if ($mautin['lich'] > 0) $thoigian = "Sau $mautin[lich] ngày";
    else $thoigian = "Trước ". abs($mautin['lich']) ." ngày";
    $thoigian .= $hauto[$mautin['loainhac']];
    $danhsach[$thutu]['mautin'] = str_replace('<br>', PHP_EOL, $mautin['mautin']);
    $danhsach[$thutu]['thoigian'] = $thoigian;
    if ($mautin['loainhac'] == 0) {
      $sql = "select b.id, b.name from pet_". PREFIX ."_vaccinelienketmautin a inner join pet_". PREFIX ."_type b on a.idloai = b.id where a.idmautin = $mautin[id]";
      $danhsach[$thutu]['loai'] = $db->all($sql);
    }
    else $danhsach[$thutu]['loai'] = [];
  }

  return $danhsach;
}

function xoanhantin() {
  global $db, $result, $data;

  $sql = "update pet_". PREFIX ."_customer set loaitru = 1 where id = $data->idkhachhang";
  // die($sql)
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
  return $result;
}

function diendulieu($mautin, $dulieu) {
  $danhsachtruong = ['[loainhac]' => 'loaitiem', '[ngayden]' => 'cometime', '[ngaynhac]' => 'calltime', '[khachhang]' => 'name', '[dienthoai]' => 'phone', '[thucung]' => 'thucung'];
  foreach ($danhsachtruong as $tentruong => $tenbien) {
    $mautin = str_replace($tentruong, alias($dulieu[$tenbien]), $mautin);
  }
  return $mautin;
}

function danhsachdaguitin() {
  global $db, $data;

  $daungay = isodatetotime($data->ngay);
  $cuoingay = $daungay + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_vaccinenhantin where thoigian between $daungay and $cuoingay group by thoigian order by thoigian desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $thongtin) {
    // khách hàng
    $sql = "select * from pet_". PREFIX ."_vaccine where id = $thongtin[idvaccine]";
    $vaccine = $db->fetch($sql);

    $sql = "select b.* from pet_". PREFIX ."_pet a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.id = $vaccine[petid]";
    $khachhang = $db->fetch($sql);
    // loại tiêm
    $sql = "select * from pet_". PREFIX ."_type where id = $vaccine[typeid]";
    $loaitiem = $db->fetch($sql);
    $danhsach[$thutu]['loainhac'] = $loaitiem['name'];
    $danhsach[$thutu]['khachhang'] = $khachhang['name'];
    $danhsach[$thutu]['dienthoai'] = $khachhang['phone'];
    $danhsach[$thutu]['thoigiantoi'] = date('d/m/Y', $vaccine['cometime']);
    $danhsach[$thutu]['thoigiannhac'] = date('d/m/Y', $vaccine['calltime']);
    $danhsach[$thutu]['thoigiangui'] = date('d/m/Y H:i:s', $thongtin['thoigian']);
  }

  return $danhsach;
}

function chuanhoatenkhach($tenkhach) {
  $mau = ['Huy', 'Khang', 'Bảo', 'Minh', 'Phúc', 'Anh', 'Khoa', 'Phát', 'Đạt', 'Khôi', 'Long', 'Nam', 'Duy', 'Quân', 'Kiệt', 'Thịnh', 'Tuấn', 'Hưng', 'Hoàng', 'Hiếu', 'Nhân', 'Trí', 'Tài', 'Phong', 'Nguyên', 'An', 'Phú', 'Thành', 'Đức', 'Dũng', 'Lộc', 'Khánh', 'Vinh', 'Tiến', 'Nghĩa', 'Thiện', 'Hào', 'Hải', 'Đăng', 'Quang', 'Lâm', 'Nhật', 'Trung', 'Thắng', 'Tú', 'Hùng', 'Tâm', 'Sang', 'Sơn', 'Thái', 'Cường', 'Vũ', 'Toàn', 'Ân', 'Thuận', 'Bình', 'Trường', 'Danh', 'Kiên', 'Phước', 'Thiên', 'Tân', 'Việt', 'Khải', 'Tín', 'Dương', 'Tùng', 'Quý', 'Hậu', 'Trọng', 'Triết', 'Luân', 'Phương', 'Quốc', 'Thông', 'Khiêm', 'Hòa', 'Thanh', 'Tường', 'Kha', 'Vỹ', 'Bách', 'Khanh', 'Mạnh', 'Lợi', 'Đại', 'Hiệp', 'Đông', 'Nhựt', 'Giang', 'Kỳ', 'Phi', 'Tấn', 'Văn', 'Vương', 'Công', 'Hiển', 'Linh', 'Ngọc', 'Vĩ'];

  $tenkhach = mb_strtoupper($tenkhach);

  return $tenkhach;
}

function danhsachnhantin() {
  global $db, $data;
  
  $thoigian = isodatetotime($data->thoigian);
  $homnay = strtotime(date('Y/m/d', $thoigian));
  $cuoingay = $homnay + 60 * 60 * 24 - 1;
  $motngay = 60 * 60 * 24;
  // $ketthuc = $cuoingay + 3 * 60 * 60 * 24; // trước ngày nhắc 3 ngày

  // lấy từ mẫu tin, kiểm tra những mục cần nhắn
  $sql = "select a.*, b.idmautin, c.mautin, c.lich, c.loainhac from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_vaccinelienketmautin b on a.typeid = b.idloai inner join pet_". PREFIX ."_vaccinemautin c on b.idmautin = c.id and a.status < 3 and (a.calltime between ($homnay + 60 * 60 * 24 * c.lich * -1) and ($homnay + 60 * 60 * 24 * (c.lich * -1 + 1) - 1))";
  $danhsachmautin = $db->all($sql);

  $sql = "select a.id, a.cometime, a.calltime, a.customerid, a.note, b.id as idmautin, b.mautin, b.lich, b.loainhac from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_vaccinemautin b on b.loainhac = 1 and a.number > 0 and (a.calltime between ($homnay + 60 * 60 * 24 * b.lich * -1) and ($homnay + 60 * 60 * 24 * (b.lich * -1 + 1) - 1))";
  $danhsachmautin = array_merge($danhsachmautin, $db->all($sql));

  $danhsachnhantin = [];
  $danhsachdienthoai = [];
  $hauto = [' tái chủng', ' sinh', ' tái khám'];
  $danhsachloai = [1 => ' siêu âm', ' tái khám'];

  // lọc loại bỏ những mục đã nhắn tin
  foreach ($danhsachmautin as $mautin) {
    $sql = "select * from pet_". PREFIX ."_vaccinenhantin where idmautin = $mautin[idmautin] and idvaccine = $mautin[id]";

    if (empty($mautin['petid'])) {
      $sql2 = "select * from pet_". PREFIX ."_customer where id = $mautin[customerid]";
      $khachhang = $db->fetch($sql2);
      $khachhang['thucung'] = '';
    }
    else {
      $sql2 = "select a.name as thucung, b.* from pet_". PREFIX ."_pet a inner join pet_". PREFIX ."_customer b on a.customerid = b.id and a.id = $mautin[petid]";
      $khachhang = $db->fetch($sql2);
    }
    
    if (empty($db->fetch($sql)) && $khachhang['loaitru'] == 0) {
      if ($mautin['loainhac'] == 0) {
        $sql = "select * from pet_". PREFIX ."_type where id = $mautin[typeid]";
        $loaitiem = $db->fetch($sql);
      }
      else {
        $loaitiem = ['name' => $danhsachloai[$mautin['loainhac']]];
      }

      if ($mautin['lich'] == 0) $thoigian = 'Đúng ngày';
      else if ($mautin['lich'] > 0) $thoigian = "Sau $mautin[lich] ngày";
      else $thoigian = "Trước ". abs($mautin['lich']) ." ngày";
      $mautin['thoigian'] = $thoigian;
      $thoigian .= $hauto[$mautin['loainhac']];
    
      $mautin['loaitiem'] = $loaitiem['name'];
      $mautin['cometime'] = date('d/m/Y', $mautin['cometime']);
      $mautin['calltime'] = date('d/m/Y', $mautin['calltime']);
      $mautin['idkhachhang'] = $khachhang['id'];
      $mautin['phone'] = $khachhang['phone'];
      $mautin['thucung'] = $khachhang['thucung'];
      $mautin['name'] = chuanhoatenkhach($khachhang['name']);
      $mautin['mautin'] = str_replace('<br>', PHP_EOL, $mautin['mautin']);
  
      if (empty($danhsachdienthoai[$khachhang['phone']])) {
        $danhsachnhantin []= [
          'id' => $mautin['id'],
          'idmautin' => $mautin['idmautin'],
          'loainhac' => $mautin['loaitiem'],
          'thoigiantoi' => $mautin['cometime'],
          'thoigiannhac' => $mautin['calltime'],
          'ghichu' => $mautin['note'],
          'idkhachhang' => $mautin['idkhachhang'],
          'khachhang' => $mautin['name'],
          'dienthoai' => $mautin['phone'],
          'mautin' => diendulieu($mautin['mautin'], $mautin),
          'trangthai' => 0,
          'thoigian' => $thoigian
        ];
        $danhsachdienthoai[$khachhang['phone']] = 1;
      }
      else {
        foreach ($danhsachnhantin as $thutunhantin => $dulieunhantin) {
          if ($dulieu['idkhachhang'] == $dulieunhantin['idkhachhang']) {
            $danhsachnhantin[$thutunhantin]['id'] .= ',' . $dulieunhantin['id'];
            $danhsachnhantin[$thutunhantin]['idmautin'] .= ',' . $dulieunhantin['idmautin'];
            break;
          }
        }
      }
    };
  }

  return $danhsachnhantin;
}

function danhsachloaitru() {
  global $db;

  $sql = "select id, name as khachhang, phone as dienthoai from pet_". PREFIX ."_customer where loaitru = 1 order by name asc";
  return $db->all($sql);
}

function xacnhandagui() {
  global $db, $data, $result;

  $thoigian = time();
  $sql = "insert into pet_". PREFIX ."_vaccinenhantin (idvaccine, idmautin, thoigian) values($data->id, $data->idmautin, $thoigian)";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function xacnhandanhsachloi() {
  global $db, $data, $result;

  $thoigian = time();
  foreach ($data->danhsach as $dulieu) {
    $sql = "select * from pet_". PREFIX ."_vaccinenhantin where idvaccine = $dulieu->id and idmautin = $dulieu->idmautin";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_vaccinenhantin (idvaccine, idmautin, thoigian) values($dulieu->id, $dulieu->idmautin, $thoigian)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  return $result;
}

function boloaitru() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_customer set loaitru = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachloaitru();
  return $result;
}

function xoamautin() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_vaccinemautin where id = $data->id";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_vaccinelineketmautin where idmautin = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachmautin();
  return $result;
}

function doitenkhach() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_customer set name = '$data->ten' where id = $data->idkhachhang";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
  return $result;
}

function luucauhinhnhantin() {
  global $db, $data, $result;

  luucauhinh('min', $data->min);
  luucauhinh('max', $data->max);

  $result['status'] = 1;
  $result['messenger'] = "Đã lưu cấu hình";
  return $result;
}

function luucauhinh($ten, $giatri) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_config where module = 'nhantin' and name = '$ten'";
  if (!empty($cauhinh = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_config set value = '$giatri' where id = $cauhinh[id]";
  else $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('nhantin', '$ten', '$giatri', 1)";
  $db->query($sql);
}

function khoitaothongke() {
  global $db, $data, $result;

  $batdau = isodatetotime($data->batdau);
  $ketthuc = isodatetotime($data->ketthuc) + 60 * 60 * 24 - 1;
  $gioihanbatdau = $batdau - 60 * 60 * 24 * 10;
  $gioihanketthuc = $ketthuc + 60 * 60 * 24 * 10;

  $dulieu = [
    'tongnhan' => 0,
    'tongkhach' => 0,
    'danhsachden' => [], 
    'danhsachkhongden' => []
  ];

  $sql = "select * from pet_". PREFIX ."_vaccinenhantin where thoigian between $batdau and $ketthuc";
  $danhsach = $db->all($sql);
  $danhsachkhach = [];

  foreach ($danhsach as $nhantin) {
    $dulieu['tongnhan'] ++;

    $sql = "select b.customerid, c.name as khachhang, c.phone as dienthoai from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid  = c.id where a.id = $nhantin[idvaccine]";
    $khachhang = $db->fetch($sql);
    if (empty($danhsachkhach[$khachhang['customerid']])) {
      $danhsachkhach[$khachhang['customerid']] = 1;

      $sql = "select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id where b.customerid = $khachhang[customerid] and (a.time between $gioihanbatdau and $gioihanketthuc)";
      if (empty($db->fetch($sql))) $dulieu['danhsachkhongden'] []= $khachhang;
      else $dulieu['danhsachden'] []= $khachhang;
    }
  }

  $dulieu['tongkhach'] = count($danhsachkhach);

  $homnay = strtotime(date('Y/m/d'));
  $homqua = strtotime(date('Y/m/d')) - 60 * 60 * 24;
  $dautuannay = $homnay - (date('N') - 1) * 60 * 60 * 24;
  $dautuantruoc = $dautuannay - 60 * 60 * 24 * 7;
  $dauthangnay = strtotime(date('Y/m/1'));
  $cuoithangnay = strtotime(date('Y/m/t'));
  $dauthangtruoc = strtotime("first day of last month");
  $cuoithangtruoc = strtotime(date('Y/m/t', $dauthangtruoc));

  $chonngay = [
    ['ten' => 'Hôm nay', 'batdau' => date(DATE_ISO8601, $homnay), 'ketthuc' => date(DATE_ISO8601, $homnay)],
    ['ten' => 'Hôm qua', 'batdau' => date(DATE_ISO8601, $homqua), 'ketthuc' => date(DATE_ISO8601, $homqua)],
    ['ten' => 'Tuần này', 'batdau' => date(DATE_ISO8601, $dautuannay), 'ketthuc' => date(DATE_ISO8601, $dautuannay + 60 * 60 * 24 * 6)],
    ['ten' => 'Tuần trước', 'batdau' => date(DATE_ISO8601, $dautuantruoc), 'ketthuc' => date(DATE_ISO8601, $dautuantruoc + 60 * 60 * 24 * 6)],
    ['ten' => 'Tháng này', 'batdau' => date(DATE_ISO8601, $dauthangnay), 'ketthuc' => date(DATE_ISO8601, $cuoithangnay)],
    ['ten' => 'Tháng trước', 'batdau' => date(DATE_ISO8601, $dauthangtruoc), 'ketthuc' => date(DATE_ISO8601, $cuoithangtruoc)],
  ];


  $result['status'] = 1;
  $result['dulieu'] = $dulieu;
  $result['chonngay'] = $chonngay;
  return $result;
}

function laycauhinh($ten, $macdinh = 0) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_config where module = 'nhantin' and name = '$ten'";
  if (empty($cauhinh = $db->fetch($sql))) return $macdinh;
  return $cauhinh['value'];
}
