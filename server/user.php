<?php
include_once(DIR . '/include/Encryption.php');
$sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
$crypt = new NukeViet\Core\Encryption($sitekey);

function getinitdata($userid, $tiento) {
  global $db;
  $admin = 0;
  if ($userid == 1 || $userid == 5) $admin = 1;
  else {
    $sql = "select * from pet_". $tiento ."_user_per where userid = $userid and module = 'admin' and type = 1";
    if (!empty($db->fetch($sql))) $admin = 1;
  }

  $sql = "select username, name, fullname from pet_". $tiento ."_users where userid = $userid";
  $userinfo = $db->fetch($sql);

  $sql = "select userid, fullname as name, fullname, username from pet_". $tiento ."_users where userid in (select userid from pet_". $tiento ."_user_per where module = 'doctor' and type = 1)";
  $doctor = $db->all($sql);

  $sql = "select * from pet_". $tiento ."_vaccineloai where active = 1";
  $type = $db->all($sql);

  $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
  $sql = "select * from pet_". $tiento ."_usg where status < 7 and recall < $lim and userid = $userid and utemp = 0";
  $uc = $db->count($sql);
  $sql = "select * from pet_". $tiento ."_usg where status = 9 and userid = $userid";
  $ut = $db->count($sql);

  $sql = "select * from pet_". $tiento ."_vaccine where status < 2 and recall < $lim and userid = $userid and utemp = 0";
  $vc = $db->count($sql);
  $sql = "select * from pet_". $tiento ."_vaccine where status = 5 and userid = $userid";
  $vt = $db->count($sql);

  $sql = "select * from pet_". $tiento ."_danhmuc where loaidanhmuc = 0 and kichhoat = 1 order by vitri asc";
  $danhsachspa = $db->all($sql);
  $ds = [];

  foreach ($danhsachspa as $key => $spa) {
    if ($spa['macdinh']) $ds []= $spa['id'];
  }

  $sql = "select * from pet_". $tiento ."_config where module = 'docs' and name = '$userid'";
  if (empty($docs = $db->fetch($sql))) {
    $sql = "insert into pet_". $tiento ."_config (module, name, value) values ('docs', $userid, '')";
    $db->query($sql);
    $docs['value'] = '';
  }
  $sql = "select * from pet_". $tiento ."_config where module = 'docscover' and name = '$userid'";
  if (empty($docscover = $db->fetch($sql))) {
    $sql = "insert into pet_". $tiento ."_config (module, name, value) values ('docscover', $userid, '')";
    $db->query($sql);
    $docscover['value'] = '';
  }
  if (!strlen($docs['value'])) $docs = array();
  else $docs = explode(', ', $docs['value']);

  $sql = "select id, name from pet_". $tiento ."_config where module = 'usg'";
  $usgcode = $db->all($sql);

  $homnay = date('d/m');
  $ngaymai = date('d/m', time() + 60 * 60 * 24);

  $sql = "select birthday, fullname from pet_". $tiento ."_users where birthday > 0";
  $danhsachsinhnhat = $db->all($sql);
  $sinhnhat = [];
  foreach ($danhsachsinhnhat as $key => $ngaysinh) {
    $ngaythangsinh = date('d/m', $ngaysinh['birthday']);
    if ($ngaythangsinh == $homnay || $ngaythangsinh == $ngaymai) $sinhnhat []= ['fullname' => $ngaysinh['fullname'], 'birthday' => date('d/m/Y', $ngaysinh['birthday'])];
  }

  $sql = "select * from pet_". $tiento ."_config where module = 'config' and name = 'chotlich'";
  $chotlich = $db->fetch($sql);
  if (empty($chotlich)) $chotlich = "0";
  else $chotlich = $chotlich['value'];

  $config = [
    "servername" => "localhost",
    "username" => "root",
    "password" => "",
    "database" => "thanhxuanpet",
  ];

  $daungay = strtotime(date("Y/m/d"));
  $cuoingay = $daungay + 60 * 60 * 24 - 1;
  $sql = "select id from pet_". $tiento ."_datlich where trangthai = 0 and ngaydat < $cuoingay";
  $datlich = $db->count($sql);

  $sql = "select id from pet_". $tiento ."_datlich where trangthai = 0 and (thoigian between $daungay and $cuoingay)";
  $datlich2 = $db->count($sql);

  $sql = "select id from pet_". $tiento ."_danhgia where (thoigian between $daungay and $cuoingay)";
  $datlich3 = $db->count($sql);
  
  $sql = "select id from pet_". $tiento ."_hanghoa_donhang where trangthai = 0";
  $donhang = $db->count($sql);

  return array(
    'month' => array('start' => date('Y-m-01'), 'end' => date('Y-m-t')),
    'prefix' => $tiento,
    'branch' => "",
    'userid' => $userid,
    'chotlich' => $chotlich,
    'donhang' => $donhang,
    'datlich' => $datlich,
    'datlich2' => $datlich2,
    'datlich3' => $datlich3,
    'username' => $userinfo['username'],
    'name' => $userinfo['name'],
    'fullname' => $userinfo['fullname'],
    'admin' => $admin,
    'doctor' => $doctor,
    'type' => $type,
    'spa' => $danhsachspa,
    'birthday' => $sinhnhat,
    'usgcode' => $usgcode,
    'today' => date('d/m/Y'),
    'next' => date('d/m/Y', time() + 60 * 60 * 24 * 21),
    'usg' => $uc + $ut,
    'vaccine' => $vc + $vt,
    'default' => array(
      'spa' => $ds,
      'docs' => $docs,
      'docscover' => $docscover['value']
    )
  );
}

function login() {
  global $data, $db, $result, $crypt;
  $data->taikhoan = mb_strtolower($data->taikhoan);

  $sql = "select * from pet_chinhanh where tiento <> '' order by id";
  $danhsachchinhanh = $db->all($sql);

  if (isset($data->tiento) && !empty($data->tiento)) {
    kiemtradangnhap($data->taikhoan, $data->matkhau, $data->tiento);
  }
  else {
    foreach ($danhsachchinhanh as $chinhanh) {
      kiemtradangnhap($data->taikhoan, $data->matkhau, $chinhanh["tiento"]);
      if ($result["status"]) return $result;
    }
  }
  return $result;
}

function kiemtradangnhap($taikhoan, $matkhau, $tiento) {
  global $db, $result, $crypt;
  $sql = "select userid, password from `pet_". $tiento ."_users` where active = 1 and LOWER(username) = '$taikhoan'";
  if (empty($user = $db->fetch($sql))) $result['messenger'] = 'Người dùng không tồn tại';
  else if (!$crypt->validate_password($matkhau, $user['password'])) $result['messenger'] = 'Sai mật khẩu';
  else {
    $result['status'] = 1;
    $result['data'] = getinitdata($user['userid'], $tiento);
    $result['config'] = permission($user['userid'], $tiento);
    $result['site'] = getsiteconfig();
    $result['chinhanh'] = chinhanhphanquyen($taikhoan, $matkhau);
    $result['tiento'] = $tiento;
  }
}

function chinhanhphanquyen($taikhoan, $matkhau) {
  global $db, $crypt;

  $sql = "select * from pet_chinhanh where tiento <> '' order by id";
  $danhsachchinhanh = $db->all($sql);

  $danhsach = [];
  foreach ($danhsachchinhanh as $chinhanh) {
    $sql = "select userid, password from `pet_". $chinhanh["tiento"] ."_users` where active = 1 and LOWER(username) = '$taikhoan'";
    if (empty($user = $db->fetch($sql))) continue;
    else if (!$crypt->validate_password($matkhau, $user['password'])) continue;
    $danhsach []= $chinhanh;
  }
  return $danhsach;
}

function changename() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_". PREFIX ."_users set name = '$data->name' where userid = $userid";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function password() {
  global $data, $db, $result;
  include_once(DIR . '/include/Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  if (empty($data->old)) $result['messenger'] = 'Mật khẩu cũ trống';
  else if (empty($data->new)) $result['messenger'] = 'Mật khẩu mới trống';
  else {
    $userid = checkuserid();
    $sql = "select password from `pet_". PREFIX ."_users` where userid = $userid";
    $user_info = $db->fetch($sql);
    if (empty($user_info)) $result['messenger'] = 'Người dùng không tồn tại';
    else if (!$crypt->validate_password($data->old, $user_info['password'])) $result['messenger'] = 'Sai mật khẩu cũ';
    else {
      $password = $crypt->hash_password($data->new, '{SSHA512}');
      $sql = "update `pet_". PREFIX ."_users` set password = '$password' where userid = $userid";
      $db->query($sql);
      $result['status'] = 1;
      $result['messenger'] = 'Đã đổi mật khẩu';
    }
  }
  return $result;
}

function badge() {
  global $data, $db, $result;

  $result['data'] = array(
    'his' => 0, 'kaizen' => 0, 'profile' => 0, 'physical' => 0, 'xquang' => 0, 'sieuam' => 0, 'other' => 0, 'hsd' => 0, 'init' => true
  );
  $sql = "select * from pet_". PREFIX ."_xray where insult = 0";
  $result['data']['his'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_kaizen where active = 1 and done = 0";
  $result['data']['kaizen'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_xray_row where sinhhoa < 0 or sinhly < 0";
  $result['data']['xetnghiem'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_exam where status = 0";
  $result['data']['other'] = $db->count($sql);
  $result['status'] = 1;

  $hansudung = time() + 60 * 60 * 24 * 90;
  $sql = "select * from pet_". PREFIX ."_hansudung where trangthai = 0 and hansudung <= $hansudung";
  $result['data']['hsd'] = $db->count($sql);

  return $result;
}

function permission($userid, $tiento) {
  global $data, $db;

  $c = [
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
    'giamsat' => 0,
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

  if ($userid == 1) {
    foreach ($c as $key => $value) {
      $c[$key] = 2;
    }
    return $c;
  }
  else {
    $sql = "select * from pet_". $tiento ."_user_per where userid = $userid";
    $query = $db->query($sql);
    
    while ($row = $query->fetch_assoc()) {
      $c[$row['module']] = intval($row['type']);
    }
    return $c;
  }
}

function getsiteconfig() {
  global $data, $db;

  $arr = array('title', 'logo');
  foreach ($arr as $key) {
    $site[$key] = '';
  }
  return $site;
}
