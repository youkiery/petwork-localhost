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

function getform() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_form where name = '$data->form'";
  $form = $db->fetch($sql);
  if (empty($form['value'])) $form['value'] = '';
  $result['status'] = 1;
  $result['html'] = $form['value'];
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
  $sql = "select * from pet_". PREFIX ."_danhmuc where loaidanhmuc = 0 and kichhoat = 1 order by vitri asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $key => $spa) {
    $sql = "select khoangcan, sotien from pet_". PREFIX ."_danhmucgia where iddanhmuc = $spa[id] order by id asc";
    $danhsach[$key]["khoangcan"] = $db->all($sql);
  }

  $result['status'] = 1;
  $result['list'] = $danhsach;
  $result['loainhac'] = dulieuloai();
  $result['style'] = dulieustyle();
  return $result;
}

function dulieustyle() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_spa_style where kichhoat = 1 order by id desc";
  return $db->all($sql);
}

function xoakieu() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_spa_style set kichhoat = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['style'] = dulieustyle();
  return $result;
}

function khoitaoloaikhoitaoloai() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_vaccineloai where active = 1";
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

function themspa() {
  global $data, $db, $result;

  if (!$thoigian = intval($data->thoigian)) $thoigian = 0;
  if ($data->id) {
    $sql = "update pet_". PREFIX ."_danhmuc set tendanhmuc = '$data->ten', thoigian = $thoigian where id = $data->id";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_danhmuc (tendanhmuc, loaidanhmuc, vitri, macdinh, thoigian) values('$data->ten', 0, 0, 0, $thoigian)";
    $data->id = $db->insertid($sql);
    $sql = "update pet_". PREFIX ."_danhmuc set vitri = $data->id where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_danhmucgia where iddanhmuc = $data->id";
  $db->query($sql);

  foreach ($data->khoangcan as $dulieu) {
    $sql = "insert into pet_". PREFIX ."_danhmucgia (iddanhmuc, khoangcan, sotien) values($data->id, '$dulieu->khoangcan', $dulieu->sotien)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['list'] = danhsachdanhmucspa();
  return $result;
}

function danhsachdanhmucspa() {
  global $data, $db, $result;
  
  $sql = "select * from pet_". PREFIX ."_danhmuc where loaidanhmuc = 0 and kichhoat = 1 order by vitri asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $key => $spa) {
    $sql = "select khoangcan, sotien from pet_". PREFIX ."_danhmucgia where iddanhmuc = $spa[id] order by id asc";
    $danhsach[$key]["khoangcan"] = $db->all($sql);
  }
  return $danhsach;
}

function toggle() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->idnhanvien and module = '$data->per'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->idnhanvien, '$data->per', 1)";
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
  $sql = "select * from pet_". PREFIX ."_user_per where userid = $data->idnhanvien and module = '$data->per'";
  if (empty($p = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->idnhanvien, '$data->per', 1)";
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

  $sql = "update pet_". PREFIX ."_users set active = 1 where userid = $data->idnhanvien";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = filterUser();
  $result['admin'] = getList();
  $result['messenger'] = 'Đã thêm nhân viên';
  
  return $result;
}

function update() {
  global $db, $data, $result;
  
  $sql = "update pet_". PREFIX ."_users set fullname = '$data->fullname', name = '$data->fullname' where userid = $data->idnhanvien";
  $db->query($sql);

  foreach ($data->module as $name => $value) {
    $sql = "select * from pet_". PREFIX ."_user_per where module = '$name' and userid = $data->idnhanvien";
    $userinfo = $db->fetch($sql);
  
    if (empty($userinfo)) {
      $sql = "insert into pet_". PREFIX ."_user_per (userid, module, type) values ($data->idnhanvien, '$name', '$value')";
    }
    else {
      $sql = "update pet_". PREFIX ."_user_per set type = '$value' where module = '$name' and userid = $data->idnhanvien";
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
  $sql = "update pet_". PREFIX ."_spa set doctorid = $data->nhanvienchuyen where doctorid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_import set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_hotel set returnuserid = $data->nhanvienchuyen where returnuserid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_exam set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_kaizen set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xetnghiem set idnhanvien = $data->nhanvienchuyen where idnhanvien = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_ride set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_sieuam set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xquang set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xray set doctorid = $data->nhanvienchuyen where doctorid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xray_read set userid = $data->nhanvienchuyen where userid = $data->nhanvienxoa";
  $db->query($sql);
  
  // xóa đăng ký lịch
  $sql = "delete from pet_". PREFIX ."_row where user_id = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_row_log where userid = $data->nhanvienxoa";
  $db->query($sql);

  // xóa lương
  $sql = "delete from pet_". PREFIX ."_luong_nhanvien where userid = $data->nhanvienxoa";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_luong_tra where userid = $data->nhanvienxoa";
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
    'vattu' => 0,
    'donhang' => 0,
    'tuyendung' => 0,
    'xetnghiem' => 0,
    'tailieu' => 0,
    'lichban' => 0,
    'thongkenghi' => 0,
    'thietbi' => 0,
    'taichinh' => 0,
    'work' => 0,
    'nhantin' => 0,
    'loinhuan' => 0,
    'khachhang' => 0,
    'chuyenmon' => 0,
    'tracnghiem' => 0,
    'voucher' => 0,
  ];

  $sql = "select name, username, fullname, userid, idvantay, faceid, placeid, birthday, photo, zalouid from pet_". PREFIX ."_users where active = 1";
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

  include_once(DIR . '/include/Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);
  $birthday = 0;
  if (isset($data->birthday)) $birthday = isodatetotime($data->birthday);

  $sql = "select userid, password from `pet_". PREFIX ."_users` where LOWER(username) = '$username'";
  if (!empty($user = $db->fetch($sql))) $result['messenger'] = 'Tên người dùng đã tồn tại';
  else {
    $time = time();
    $sql = "insert into pet_". PREFIX ."_users (username, idvantay, faceid, name, fullname, password, photo, regdate, birthday, active) values ('$data->username', '$data->idvantay', '$data->faceid, '$data->fullname', '$data->fullname', '". $crypt->hash_password($data->password) ."', '$data->image', $time, $birthday, 1)";
    $userid = $db->insertid($sql);
    
    $result['status'] = 1;
    $result['list'] = getList();
  }
  return $result;
}

function updateuser() {
  global $data, $db, $result;
  
  if (strlen($data->password)) $xtra = ", password = '". $crypt->hash_password($data->password) ."', ";
  else $xtra = ",";

  include_once(DIR . '/include/Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);
  $birthday = isodatetotime($data->birthday);
  
  $sql = "update pet_". PREFIX ."_users set username = '$data->username', idvantay = '$data->idvantay', faceid = '$data->faceid', name = '$data->fullname', fullname = '$data->fullname', photo = '$data->image' $xtra birthday = $birthday where userid = $data->idnhanvien";
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

  $sql = "update pet_". PREFIX ."_users set placeid = $data->placeid where userid = $data->idnhanvien";
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

  $sql = "update pet_". PREFIX ."_users set placeid = $id where userid = $data->idnhanvien";
  $db->query($sql);

  $result['status'] = 1;
  $result['placelist'] = placelist();
  $result['adminlist'] = getlist();
  return $result;
}

function placeselect() {
  global $db, $result, $data;

  $sql = "update pet_". PREFIX ."_users set placeid = $data->placeid where userid = $data->idnhanvien";
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

  $sql = "select * from pet_". PREFIX ."_config where name = 'dangkythem'";
  if (empty($dangkythem = $db->fetch($sql))) $dangkythem = ['value' => '0'];
  $sql = "select * from pet_". PREFIX ."_config where name = 'huydangky'";
  if (empty($huydangky = $db->fetch($sql))) $huydangky = ['value' => '0'];

  $sql = "select * from pet_". PREFIX ."_config where name = 'khoathoigian'";
  if (empty($khoathoigian = $db->fetch($sql))) $khoathoigian = ['value' => '0'];

  $result['status'] = 1;
  $result['dulieu'] = $config;
  $result['khoathoigian'] = $khoathoigian['value'];
  $result['thoigianmo'] = chuyenthoigianiso($thoigianmo['value']);
  $result['thoigiandong'] = chuyenthoigianiso($thoigiandong['value']);
  $result['huydangky'] = $huydangky['value'];
  $result['dangkythem'] = $dangkythem['value'];
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

function luudangky() {
  global $db, $result, $data;

  if (empty($data->dangkythem)) $data->dangkythem = 0;
  if (empty($data->huydangky)) $data->huydangky = 0;

  $sql = "select * from pet_". PREFIX ."_config where name = 'dangkythem'";
  if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('config', 'dangkythem', $data->dangkythem, 0)";
  else $sql = "update pet_". PREFIX ."_config set value = $data->dangkythem where name = 'dangkythem'";
  $db->query($sql);
  
  $sql = "select * from pet_". PREFIX ."_config where name = 'huydangky'";
  if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('config', 'huydangky', $data->huydangky, 0)";
  else $sql = "update pet_". PREFIX ."_config set value = $data->huydangky where name = 'huydangky'";
  $db->query($sql);

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
  
  if (empty($data->sukien)) $data->sukien = 0;

  if ($data->id) {
    // cập nhật mẫu tin
    // thêm những cái không có
    // xóa những cái không có
    $sql = "update pet_". PREFIX ."_vaccinemautin set mautin = '$data->mautin', lich = $data->lich, loainhac = $data->loainhac, sukien = $data->sukien where id = $data->id";
    $db->query($sql);

    foreach ($data->nhom as $idnhom) {
      $sql = "select * from pet_". PREFIX ."_vaccinelienketmautin where idmautin = $data->id and idnhom = $idnhom";
      if (empty($db->fetch($sql))) {
        $sql = "insert into pet_". PREFIX ."_vaccinelienketmautin (idnhom, idmautin) values ($idnhom, $data->id)";
        $db->query($sql);
      }
    }
    $sql = "delete from pet_". PREFIX ."_vaccinelienketmautin where idmautin = $data->id and idnhom not in (". implode(', ', $data->nhom) .")";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_vaccinemautin (mautin, lich, loainhac, sukien) values ('$data->mautin', $data->lich, $data->loainhac, $data->sukien)";
    $idmautin = $db->insertid($sql);

    foreach ($data->nhom as $idnhom) {
      $sql = "insert into pet_". PREFIX ."_vaccinelienketmautin (idnhom, idmautin) values ($idnhom, $idmautin)";
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

  $sql = "select id, name from pet_". PREFIX ."_vaccineloainhom where active = 1 order by id asc";
  return $db->all($sql);
}

function danhsachmautin() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vaccinemautin where kichhoat = 1 order by lich desc, id desc";
  $danhsach = $db->all($sql);
  $hauto = [' tái chủng', [' sinh', ' xổ giun', ' vaccine mũi đầu'], ' tái khám', ' đặt lịch'];

  foreach ($danhsach as $thutu => $mautin) {
    if ($mautin['lich'] == 0) $thoigian = 'Đúng ngày';
    else if ($mautin['lich'] > 0) $thoigian = "Sau $mautin[lich] ngày";
    else $thoigian = "Trước ". abs($mautin['lich']) ." ngày";
    if ($mautin['loainhac'] == 1) $thoigian .= $hauto[$mautin['loainhac']][$mautin['sukien']];
    else $thoigian .= $hauto[$mautin['loainhac']];
    $danhsach[$thutu]['mautin'] = str_replace('<br>', PHP_EOL, $mautin['mautin']);
    $danhsach[$thutu]['thoigian'] = $thoigian;
    if ($mautin['loainhac'] == 0) {
      $sql = "select c.id, c.name from pet_". PREFIX ."_vaccinelienketmautin a inner join pet_". PREFIX ."_vaccineloainhom b on a.idnhom = b.id inner join pet_". PREFIX ."_vaccineloai c on b.id = c.idnhom where a.idmautin = $mautin[id]";
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

function xacnhanthucong() {
  global $db, $result, $data;

  $thoigian = time();
  $danhsachvaccine = explode(',', $data->id);
  $danhsachmautin = explode(',', $data->idmautin);
  $danhsachdaxacnhan = [];

  foreach ($danhsachmautin as $thutu => $idmautin) {
    if (empty($danhsachdaxacnhan[$idmautin . $danhsachvaccine[$thutu]])) {
      $danhsachdaxacnhan[$idmautin] = $idmautin . $danhsachvaccine[$thutu];
      $sql = "insert into pet_". PREFIX ."_vaccinenhantin (idvaccine, idmautin, loainhan, thoigian) values($danhsachvaccine[$thutu], $idmautin, 0, $thoigian)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhantin();
  return $result;
}

function diendulieu($mautin, $dulieu) {
  $danhsachtruong = ['[loainhac]' => 'loainhac', '[ngayden]' => 'thoigiantoi', '[ngaynhac]' => 'thoigiannhac', '[ngaygio]' => 'ngaygio', '[khachhang]' => 'khachhang', '[dienthoai]' => 'dienthoai', '[thucung]' => 'thucung'];
  if (isset($dulieu["khachhang"])) $dulieu["khachhang"] = mb_strtoupper($dulieu["khachhang"]);
  if (isset($dulieu["loainhac"])) $dulieu["loainhac"] = mb_strtoupper($dulieu["loainhac"]);
  foreach ($danhsachtruong as $tentruong => $tenbien) {
    $mautin = str_replace($tentruong, fullalias($dulieu[$tenbien]), $mautin);
  }
  return $mautin;
}

function xacnhankhongnhantin() {
  global $db, $result, $data;

  // xác nhận vaccine đã tiêm, cập nhật ghi chú
  $sql = "update pet_". PREFIX . "_vaccine set status = 3". (!empty($data->lydo) ? ", note = '$data->lydo'": "") ." where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdaguitin();
  return $result;
}

function danhsachdaguitin() {
  global $db, $data;

  $daungay = isodatetotime($data->ngay);
  $cuoingay = $daungay + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_vaccinenhantin where thoigian between $daungay and $cuoingay order by thoigian desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $thongtin) {
    // khách hàng
    $sql = "select * from pet_". PREFIX ."_vaccine where id = $thongtin[idvaccine]";
    $vaccine = $db->fetch($sql);

    $sql = "select b.* from pet_". PREFIX ."_pet a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.id = $vaccine[petid]";
    $khachhang = $db->fetch($sql);
    // loại tiêm
    $sql = "select * from pet_". PREFIX ."_vaccineloai where id = $vaccine[typeid]";
    $loaitiem = $db->fetch($sql);
    $danhsach[$thutu]['loainhac'] = $loaitiem['name'];
    $danhsach[$thutu]['khachhang'] = $khachhang['name'];
    $danhsach[$thutu]['dienthoai'] = $khachhang['phone'];
    $danhsach[$thutu]['thoigiantoi'] = date('d/m/Y', $vaccine['cometime']);
    $danhsach[$thutu]['thoigiannhac'] = date('d/m/Y', $vaccine['calltime']);
    $danhsach[$thutu]['idvaccine'] = $vaccine['id'];
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
  // nhắn tin vaccine
  $sql = "select a.*, c.idmautin, d.mautin, d.lich, d.loainhac, 0 as sukien from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_vaccineloai b on a.typeid = b.id inner join pet_". PREFIX ."_vaccinelienketmautin c on b.idnhom = c.idnhom inner join pet_". PREFIX ."_vaccinemautin d on c.idmautin = d.id and d.loainhac = 0 and (a.status < 3 or a.status = 5) and d.kichhoat = 1 and (a.calltime between ($homnay + 60 * 60 * 24 * d.lich * -1) and ($homnay + 60 * 60 * 24 * (d.lich * -1 + 1) - 1))";
  $danhsachmautin = $db->all($sql);

  // nhắn tin siêu âm
  // nếu sự kiện = 0 thì dùng birthday, nếu sự kiện = 1 thì deworm
  $sql = "select a.id, a.cometime, a.recall as calltime, a.vaccinetime, a.customerid, a.note, b.id as idmautin, b.mautin, b.lich, b.loainhac, b.sukien from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_vaccinemautin b on b.loainhac = 1 and ((b.sukien = 2 and b.kichhoat = 1 and (a.vaccinetime between ($homnay + 60 * 60 * 24 * b.lich * -1) and ($homnay + 60 * 60 * 24 * (b.lich * -1 + 1) - 1))) or (b.sukien < 2 and (a.status = 3 + b.sukien or a.status = 9 + b.sukien) and a.number > 0 and b.kichhoat = 1 and (a.recall between ($homnay + 60 * 60 * 24 * b.lich * -1) and ($homnay + 60 * 60 * 24 * (b.lich * -1 + 1) - 1))))";
  $danhsachmautin = array_merge($danhsachmautin, $db->all($sql));

  // nhắn tin tái khám
  $sql = "select a.id, a.thoigianden as cometime, a.thoigian as calltime, a.idkhach as customerid, '' as note, b.id as idmautin, b.mautin, b.lich, b.loainhac, b.sukien from pet_". PREFIX ."_dieutritaikham a inner join pet_". PREFIX ."_vaccinemautin b on b.loainhac = 2 and a.trangthai = 0 and b.kichhoat = 1 and (a.thoigian between ($homnay + 60 * 60 * 24 * b.lich * -1) and ($homnay + 60 * 60 * 24 * (b.lich * -1 + 1) - 1))";
  $danhsachmautin = array_merge($danhsachmautin, $db->all($sql));

  // nhắn tin đặt lịch
  $sql = "select a.id, a.thoigian as cometime, a.ngaydat as calltime, a.idkhachhang as customerid, ghichu as note, b.id as idmautin, b.mautin, b.lich, b.loainhac, b.sukien from pet_". PREFIX ."_datlich a inner join pet_". PREFIX ."_vaccinemautin b on b.loainhac = 3 and a.trangthai = 0 and b.kichhoat = 1 and (a.ngaydat between ($homnay + 60 * 60 * 24 * b.lich * -1) and ($homnay + 60 * 60 * 24 * (b.lich * -1 + 1) - 1))";
  $danhsachmautin = array_merge($danhsachmautin, $db->all($sql));

  $danhsachnhantin = [];
  $danhsach = [];
  $hauto = [' tái chủng', [' sinh', ' xổ giun', ' vaccine'], ' tái khám', " đặt lịch"];
  $danhsachloai = [1 => ' siêu âm', ' tái khám', " đặt lịch"];

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
    
    if (empty($db->fetch($sql)) && isset($khachhang["loaitru"]) && $khachhang['loaitru'] == 0) {
      $loainhac = $mautin["loainhac"];
      $sukien = $mautin["sukien"];
      if ($mautin['loainhac'] == 0) {
        $sql = "select * from pet_". PREFIX ."_vaccineloai where id = $mautin[typeid]";
        $loaitiem = $db->fetch($sql);
      }
      else {
        $loaitiem = ['name' => $danhsachloai[$mautin['loainhac']]];
      }

      if ($mautin['lich'] == 0) $thoigian = 'Đúng ngày';
      else if ($mautin['lich'] > 0) $thoigian = "Sau $mautin[lich] ngày";
      else $thoigian = "Trước ". abs($mautin['lich']) ." ngày";
      $mautin['thoigian'] = $thoigian;
      if ($mautin['loainhac'] == 1) $thoigian .= $hauto[$mautin['loainhac']][$mautin['sukien']];
      else $thoigian .= $hauto[$mautin['loainhac']];
    
      $mautin['loainhac'] = $loaitiem['name'];
      $mautin['thoigiantoi'] = date('d/m/Y', $mautin['cometime']);
      $mautin['thoigiannhac'] = date('d/m/Y', $mautin['calltime']);
      $mautin['ngaygio'] = date('d/m/Y H:i', $mautin['calltime']);
      if (isset($mautin['vaccinetime']) && !empty($mautin['vaccinetime'])) $mautin['thoigiannhac'] = date('d/m/Y', $mautin['vaccinetime']);
      $mautin['idkhachhang'] = $khachhang['id'];
      $mautin['dienthoai'] = $khachhang['phone'];
      $mautin['thucung'] = $khachhang['thucung'];
      $mautin['khachhang'] = chuanhoatenkhach($khachhang['name']);
      $mautin['mautin'] = str_replace('<br>', PHP_EOL, $mautin['mautin']);
      // mỗi loại nhắc cùng loại chỉ nên gửi khách 1 cái
      // vd: 2 cái vaccine, xổ giun thì gộp lại, thay đổi mẫu tin, 
      if (empty($danhsach[$mautin["dienthoai"]])) {
        $danhsach[$mautin["dienthoai"]] = [];
      }
      if (empty($danhsach[$mautin["dienthoai"]][$loainhac])) {
        $danhsach[$mautin["dienthoai"]][$loainhac] = [];
      }
      if (empty($danhsach[$mautin["dienthoai"]][$loainhac][$sukien])) {
        $danhsach[$mautin["dienthoai"]][$loainhac][$sukien] = [
          'id' => $mautin['id'],
          'idmautin' => $mautin['idmautin'],
          'loainhac' => $mautin['loainhac'],
          'thoigiantoi' => $mautin['thoigiantoi'],
          'thoigiannhac' => $mautin['thoigiannhac'],
          'ghichu' => $mautin['note'],
          'idkhachhang' => $mautin['idkhachhang'],
          'khachhang' => $mautin['khachhang'],
          'dienthoai' => $mautin['dienthoai'],
          'thucung' => $mautin['thucung'],
          'mautin' => diendulieu($mautin['mautin'], $mautin),
          'trangthai' => 0,
          'ngaygio' => $mautin['ngaygio'],
          'thoigian' => $thoigian
        ];
      }
      else {
        $danhsach[$mautin["dienthoai"]][$loainhac][$sukien]['loainhac'] .= ', ' . $mautin['loainhac'];
        $danhsach[$mautin["dienthoai"]][$loainhac][$sukien]["mautin"] = diendulieu($danhsach[$mautin["dienthoai"]][$loainhac][$sukien]['mautin'], $danhsach[$mautin["dienthoai"]][$loainhac][$sukien]);
        $danhsach[$mautin["dienthoai"]][$loainhac][$sukien]['id'] .= ',' . $mautin['id'];
        $danhsach[$mautin["dienthoai"]][$loainhac][$sukien]['idmautin'] .= ',' . $mautin['idmautin'];
      }
    };
  }

  foreach ($danhsach as $danhsachsukien) {
    foreach ($danhsachsukien as $sukien) {
      foreach ($sukien as $mautin) {
        $danhsachnhantin []= $mautin;
      }
    }
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
  $danhsachvaccine = explode(',', $data->id);
  $danhsachmautin = explode(',', $data->idmautin);
  $danhsachdaxacnhan = [];

  foreach ($danhsachmautin as $thutu => $idmautin) {
    if (empty($danhsachdaxacnhan[$idmautin . $danhsachvaccine[$thutu]])) {
      $danhsachdaxacnhan[$idmautin] = $idmautin . $danhsachvaccine[$thutu];
      $sql = "insert into pet_". PREFIX ."_vaccinenhantin (idvaccine, idmautin, loainhan, thoigian) values($danhsachvaccine[$thutu], $idmautin, 0, $thoigian)";
      $db->query($sql);

      $sql = "select * from pet_". PREFIX ."_vaccine where id = $danhsachvaccine[$thutu]";
      $vaccine = $db->fetch($sql);

      if ($vaccine["onetime"]) {
        $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id = $danhsachvaccine[$thutu]";
        $db->query($sql);
      }
    }
  }

  $result['status'] = 1;
  return $result;
}

function xacnhandanhsachloi() {
  global $db, $data, $result;

  $thoigian = time();
  foreach ($data->danhsach as $dulieu) {
    $sql = "select * from pet_". PREFIX ."_vaccinenhantin where idvaccine in ($dulieu->id) and idmautin in ($dulieu->idmautin)";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_vaccinenhantin (idvaccine, idmautin, loainhan, thoigian) values($dulieu->id, $dulieu->idmautin, 0, $thoigian)";
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

  $sql = "update pet_". PREFIX ."_vaccinemautin set kichhoat = 0 where id = $data->id";
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

function doidienthoai() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_customer set phone = '$data->dienthoai' where id = $data->idkhachhang";
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
  $gioihanbatdau = $batdau - 60 * 60 * 24 * 10; // tiêm trước 10 ngày
  $gioihanketthuc = $ketthuc + 60 * 60 * 24 * 90; // tiêm sau 3 tháng

  $dulieu = [
    'tongnhan' => 0,
    'tongkhach' => 0,
    'tongkhongden' => 0,
    "tile" => 0,
    'loainhan' => [0 => 0, 0, 0, 0], // 0 sms, 1 giao dich, 2 zns
    'quaylai' => [0 => 0, 0, 0, 0], // 0 sms, 1 giao dich, 2 zns
    'danhsachden' => [], 
    'danhsachkhongden' => [],
    'nhomnhantin' => []
  ];

  $sql = "select * from pet_". PREFIX ."_users";
  $dulieunhanvien = $db->obj($sql, 'userid', 'name');

  $sql = "select *, 0 as tick from pet_". PREFIX ."_vaccineloainhom where active = 1 order by name asc";
  $dulieu['nhomnhantin'] = $db->all($sql);
  $danhsachkhach = [];

  if ($data->khoitao) {
    $loctheonhom = explode(',', $data->loctheonhom);
    foreach ($loctheonhom as $idnhom) {
      foreach ($dulieu['nhomnhantin'] as $thutu => $nhom) {
        if ($idnhom == $nhom['id']) $dulieu['nhomnhantin'][$thutu]['tick'] = 1;
      }
    }
  }
  else {
    foreach ($dulieu['nhomnhantin'] as $thutu => $nhom) {
      $dulieu['nhomnhantin'][$thutu]['tick'] = 1;
    }
  }

  if ($data->sieuam) {
    $sql = "select * from pet_". PREFIX ."_vaccinemautin where loainhac = 1 and kichhoat = 1";
    $danhsachmautin = $db->arr($sql, 'id');
    if (!empty($danhsachmautin)) {
      $idmautin = implode(', ', $danhsachmautin);
      $sql = "select * from pet_". PREFIX ."_vaccinenhantin where idmautin in ($idmautin) and (thoigian between $batdau and $ketthuc)";
      $danhsach = $db->all($sql);

      foreach ($danhsach as $nhantin) {
        $dulieu['tongnhan'] ++;
    
        $sql = "select a.customerid, c.name as khachhang, c.phone as dienthoai, a.userid, a.daden, a.number, a.calltime, a.recall, a.status, a.vaccinetime from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_customer c on a.customerid = c.id where a.id = $nhantin[idvaccine]";
        $khachhang = $db->fetch($sql);
        $khachhang['nhanvien'] = $dulieunhanvien[$khachhang['userid']];
        $khachhang['xogiun'] = '';
        $khachhang['vaccinetime'] = '';
        $dulieu["loainhan"][$nhantin["loainhan"]] ++;
        if ($khachhang['status'] == 4) {
          $khachhang['dade'] = date('d/m/Y', $khachhang['calltime']);
          $khachhang['xogiun'] = date('d/m/Y', $khachhang['recall']);
          if (!empty($khachhang['vaccinetime'])) $khachhang['vaccinetime'] = date('d/m/Y', $khachhang['vaccinetime']);
        }
        else $khachhang['dade'] = 'Chưa';
        if ($khachhang['daden']) $dulieu['danhsachden'] []= $khachhang;
        else $dulieu['danhsachkhongden'] []= $khachhang;
        // đã đẻ, đã quay lại, số con, ngày xổ giun, ngày tiêm vaccine
      }
    
      $dulieu['tongkhach'] = count($danhsachkhach);
    }
  }
  else {
    if (!empty($data->loctheonhom)) {
      $sql = "select * from pet_". PREFIX ."_vaccineloai where idnhom in (". $data->loctheonhom .")";
      if (count($danhsachloai = $db->arr($sql, 'id'))) $xtra = " and typeid in (". implode(', ', $danhsachloai) .")";
      else $xtra = " and 0 ";
    }
    else $xtra = "";

    $sql = "select * from pet_". PREFIX ."_vaccine where (calltime between $batdau and $ketthuc) $xtra order by calltime desc";
    $danhsachvaccine = $db->all($sql);

    $danhsachkhongden = [];
    foreach ($danhsachvaccine as $thutu => $vaccine) {
      // lấy loại nhắc cùng nhóm với loại nhắc hiện tại
      $sql = "select * from pet_". PREFIX ."_vaccineloai where id = $vaccine[typeid]";
      $loainhac = $db->fetch($sql);

      $sql = "select * from pet_". PREFIX ."_vaccineloai where idnhom = $loainhac[idnhom]";
      $nhom = $db->arr($sql, "id");

      $sql = "select a.* from pet_". PREFIX ."_customer a inner join pet_". PREFIX ."_pet b on a.id = b.customerid where b.id = $vaccine[petid]";
      $khachhang = $db->fetch($sql);

      // kiểm tra khách đã tiêm vaccine chưa
      // khách tiêm vaccine cùng loại trong 

      // $gioihanbatdau = $vaccine["calltime"] - 60 * 60 * 24 * 10; // tiêm trước 10 ngày
      // $gioihanketthuc = $vaccine["calltime"] + 60 * 60 * 24 * 90; // tiêm sau 3 tháng

      // $sql = "select a.id, a.typeid from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id where b.customerid = $khachhang[id] and (a.cometime between $gioihanbatdau and $gioihanketthuc) and typeid in (". implode(", ", $nhom) .")";

      $khach = [
        "khachhang" => $khachhang["name"],
        "dienthoai" => $khachhang["phone"],
        "loai" => $loainhac["name"],
        "ngayden" => date("d/m/Y", $vaccine["cometime"]),
        "ngaynhac" => date("d/m/Y", $vaccine["calltime"]),
      ];

      if ($vaccine["status"] == 3) {
        $dulieu["danhsachden"] []= $khach;
        if (empty($index)) $index = 1;
      }
      else {
        if (empty($danhsachkhongden[$vaccine["userid"]])) {
          $sql = "select * from pet_". PREFIX ."_users where userid = $vaccine[userid]";
          $nhanvien = $db->fetch($sql);
          $danhsachkhongden[$vaccine["userid"]] = [
            "ten" => $nhanvien["fullname"],
            "danhsach" => [],
            "morong" => 0
          ];
        }
        $danhsachkhongden[$vaccine["userid"]]["danhsach"][]= $khach;
      }

      // kiểm tra đã nhắn tin chưa
      $sql = "select * from pet_". PREFIX ."_vaccinenhantin where idvaccine = $vaccine[id]";
      if (empty($nhantin = $db->fetch($sql))) {
        $nhantin = ["loainhan" => 3];
      }
      else {
        $dulieu["loainhan"][$nhantin["loainhan"]] ++;
      }
      if ($vaccine["status"] == 3) $dulieu["quaylai"][$nhantin["loainhan"]] ++;
      $dulieu["tongkhach"] ++;
    }
    foreach ($danhsachkhongden as $nhanvien) {
      $dulieu["tongkhongden"] += count($nhanvien["danhsach"]);
      $dulieu["danhsachkhongden"] []= $nhanvien;
    }
    if ($dulieu["tongkhach"] <= 0) $dulieu["tile"] = 0;
    else $dulieu["tile"] = round($dulieu["tongkhongden"] * 100 / $dulieu["tongkhach"], 1);


    // $sql = "select * from pet_". PREFIX ."_vaccinenhantin where thoigian between $batdau and $ketthuc";
    // $danhsach = $db->all($sql);
  
    // if (!empty($data->loctheonhom)) {
    //   $sql = "select * from pet_". PREFIX ."_vaccineloai where idnhom in (". $data->loctheonhom .")";
    //   if (count($danhsachloai = $db->arr($sql, 'id'))) $xtra = " and a.typeid in (". implode(', ', $danhsachloai) .")";
    //   else $xtra = " and 0 ";
    // }
    // else $xtra = "";
  
    // $sql = "select * from pet_". PREFIX ."_vaccineloai where active = 1";
    // $dulieuloai = $db->obj($sql, 'id', 'name');
    
    // foreach ($danhsach as $nhantin) {
    //   $dulieu['tongnhan'] ++;
    //   $dulieu["loainhan"][$nhantin["loainhan"]] ++;
  
    //   $sql = "select a.id, b.customerid, c.name as khachhang, c.phone as dienthoai, a.typeid, a.userid from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid  = c.id where a.id = $nhantin[idvaccine] $xtra";
    //   $khachhang = $db->fetch($sql);
    //   if (!empty($khachhang) && empty($danhsachkhach[$khachhang['id']])) {
    //     $danhsachkhach[$khachhang['id']] = 1;
    //     $khachhang['loai'] = $dulieuloai[$khachhang['typeid']];
    //     $khachhang['nhanvien'] = $dulieunhanvien[$khachhang['userid']];
  
    //     $sql = "select a.id, a.typeid from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id where b.customerid = $khachhang[customerid] and (a.time between $gioihanbatdau and $gioihanketthuc) $xtra";
    //     $khach = $db->fetch($sql);
    //     if (empty($khach)) $dulieu['danhsachkhongden'] []= $khachhang;
    //     else {
    //       $dulieu['danhsachden'] []= $khachhang;
    //     }
    //   }
    //   else {
    //     echo "$sql <br>";
    //   }
    // }
    // $dulieu['tongkhach'] = count($danhsachkhach);
  }

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

function dulieuloai() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_spaloai where active = 1";
  return $db->all($sql);
}

function themloai() {
  global $data, $db, $result;
  $data->name = trim($data->name);
  $data->code = trim($data->code);

  if ($data->id) $sql = "update pet_". PREFIX ."_spaloai set name = '$data->name', code = '$data->code' where id = $data->id";
  else $sql = "insert into pet_". PREFIX ."_spaloai (name, code) values('$data->name', '$data->code')";
  $db->query($sql);

  $result['status'] = 1;
  $result['loainhac'] = dulieuloai();
  return $result;
}

function xoaloai() {
  global $data, $db, $result;
  $sql = "update pet_". PREFIX ."_spaloai set active = 0 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['loainhac'] = dulieuloai();
  return $result;
}

function khoitaothongkespa() {
  global $data, $db, $result;

  $dulieu = [
    "tong" => 0,
    "thangtruoc" => 0,
    "chenhlech" => 0,
    "tanggiam" => 0,
    "ngay" => [
      "labels" => [],
      "datasets" => [[
        "label" => "Ngày",
        "data" => [],
        "backgroundColor" => 'pink',
      ]],
    ],
    "thu" => [
      "labels" => ["T2", "T3", "T4", "T5", "T6", "T7", "CN"],
      "datasets" => [[
        "label" => "Thứ",
        "data" => [0, 0, 0, 0, 0, 0, 0],
        "backgroundColor" => 'lightgreen',
      ]],
    ],
    "gio" => [
      "labels" => [],
      "datasets" => [[
        "label" => "Giờ",
        "data" => [],
        "backgroundColor" => 'lightblue',
      ]],
    ],
  ];
  $thoigian = time();
  $dauthangnay = strtotime(date("Y/m/1", $thoigian));

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date("Y/m/1", $thoigian));
  $cuoithang = strtotime(date("Y/m/t", $thoigian)) + 60 * 60 * 24 - 1;
  $tongngay = date("t", $thoigian);
  $thang = date("m", $thoigian);
  $doithu = [0 => 6, 0, 1, 2, 3, 4, 5];

  for ($i = 1; $i <= $tongngay; $i++) { 
    $dulieu["ngay"]["labels"] []= ($i < 10 ? "0$i" : $i) ."/$thang";
    $dulieu["ngay"]["datasets"][0]["data"] []= 0;
  }

  for ($i = 0; $i < 24; $i++) { 
    $dulieu["gio"]["labels"] []= ($i < 10 ? "0$i" : $i) . "h";
    $dulieu["gio"]["datasets"][0]["data"] []= 0;
  }

  if ($dauthangnay == $dauthang) $cuoithangtruoc = strtotime(date("Y/m/d")) - 1;
  else $cuoithangtruoc = $dauthang - 1;
  $dauthangtruoc = strtotime(date("Y/m/1", $cuoithangtruoc));

  $sql = "select * from pet_". PREFIX ."_spadichvu where (thoigian between $dauthangtruoc and $cuoithangtruoc)";
  $danhsach = $db->all($sql);
  $danhsachthangtruoc = [];

  foreach ($danhsach as $spa) {
    $ngay = date('d', $spa["thoigian"]);
    if (empty($danhsachthangtruoc[$spa['idkhach']])) $danhsachthangtruoc[$spa['idkhach']] = [];
    if (empty($danhsachthangtruoc[$spa['idkhach']][$ngay])) $danhsachthangtruoc[$spa['idkhach']][$ngay] = [];
  }
  
  foreach ($danhsachthangtruoc as $idkhach => $dulieukhach) {
    foreach ($dulieukhach as $ngay => $dulieungay) {
      $dulieu["thangtruoc"] ++;
    }
  }

  $sql = "select * from pet_". PREFIX ."_spadichvu where (thoigian between $dauthang and $cuoithang)";
  $danhsach = $db->all($sql);
  $danhsachtonghop = [];

  // tổng hợp dữ liệu, gộp các khách hàng làm nhiều dịch vụ từ các nhân viên khác nhau
  foreach ($danhsach as $spa) {
    $ngay = date('d', $spa["thoigian"]);
    $gio = date('H', $spa["thoigian"]);
    if (empty($danhsachtonghop[$spa['idkhach']])) $danhsachtonghop[$spa['idkhach']] = [];
    if (empty($danhsachtonghop[$spa['idkhach']][$ngay])) $danhsachtonghop[$spa['idkhach']][$ngay] = [];
    if (empty($danhsachtonghop[$spa['idkhach']][$ngay][$gio])) $danhsachtonghop[$spa['idkhach']][$ngay][$gio] = 1;
  }

  // echo json_encode($dulieu); die();

  $dulieuthu = [0 => [], [], [], [], [], [], []];
  foreach ($danhsachtonghop as $idkhach => $dulieukhach) {
    foreach ($dulieukhach as $ngay => $dulieungay) {
      $dulieu["tong"] ++;
      $thoigian = strtotime(date("Y/$thang/$ngay"));
      $ngaythang = date("d/m", $thoigian);
      $thu = date("w", $thoigian);
      if (empty($dulieuthu[$thu][$ngaythang])) $dulieuthu[$thu][$ngaythang] = 0;
      $dulieuthu[$thu][$ngaythang] ++;
      $dulieu["ngay"]["datasets"][0]["data"][intval($ngay) - 1] ++;
    }
  }

  if ($dulieu["tong"] > 0 && $dulieu["thangtruoc"]) {
    $chenhlech = $dulieu["tong"] - $dulieu["thangtruoc"];
    if ($chenhlech > 0) $dulieu["tanggiam"] = 1;
    else $dulieu["tanggiam"] = 0;
    $dulieu["chenhlech"] = round($chenhlech * 100 / $dulieu["thangtruoc"], 1);
  }

  $sql = "select * from pet_". PREFIX ."_spa where time between $dauthang and $cuoithang";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $spa) {
    $gio = date("H", $spa["time"]);
    $dulieu["gio"]["datasets"][0]["data"][intval($gio)] ++;
  }

  foreach ($dulieuthu as $thu => $danhsachdulieu) {
    $tongluot = 0;
    foreach ($danhsachdulieu as $luotkhach) {
      $tongluot += $luotkhach;
    }
    if (!count($danhsachdulieu)) $dulieu["thu"]["datasets"][0]["data"][$doithu[$thu]] = 0;
    else $dulieu["thu"]["datasets"][0]["data"][$doithu[$thu]] = round($tongluot / count($danhsachdulieu));
  }

  $result['status'] = 1;
  $result['data'] = $dulieu;
  return $result;
}

function khoitaozalo() {
  global $db, $data, $result;

  $x = strripos(DIR, "/");
  $y = strripos(substr(DIR, 0, $x), "/");
  $z = substr(DIR, 0, $y);

  $file = file_get_contents($z . "/api/assets/chitietkhachhang.txt");  
  $result['status'] = 1;
  $result['danhsach'] = json_decode($file);
  return $result;
}

function chonzalo() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_users set zalouid = '$data->zalouid' where userid = $data->idnhanvien";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = "Đã lưu thông tin";
  return $result;
}