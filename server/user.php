<?php
function check() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_users where session = '$data->session'";

  if (empty($user = $db->fetch($sql))) return true;
  return false;
}

function session() {
  global $data, $db, $result;

  $sql = "select a.* from pet_". PREFIX ."_session a inner join pet_". PREFIX ."_users b on a.userid = b.userid where b.active = 1 and a.session = '$data->sess'";
  $user = $db->fetch($sql);
  $time = time();
  $end = $time - 60 * 60 * 24 * 7; // 7 days

  if (!empty($user)) {
    if ($user['time'] > $end) {
      $result['status'] = 1;
      $sql = "update pet_". PREFIX ."_session set time = $time where id = $user[id]";
      $db->query($sql);
      $result['data'] = khoitaodulieu($user['userid']);
      $result['badge'] = khoitaobadge($user['userid']);
      $result['phanquyen'] = permission($user['userid']);
      $result['site'] = getsiteconfig();
    }
    else {
      $sql = "delete from pet_". PREFIX ."_session where id = $user[id]";
      $db->query($sql);
      $result['messenger'] = "Phiên đăng nhập hết hạn";
    }
  }
  else {
    $result['messenger'] = "Phiên đăng nhập hết hạn";
  }
  return $result;
}

function getHisChatCount($userid) {
  global $db;
  
  $sql = "select * from pet_". PREFIX ."_xray where doctorid = $userid or pos = 1";
  $c = $db->all($sql);

  $count = 0;
  foreach ($c as $row) {
    $sql = "select * from pet_". PREFIX ."_xray_read where side = 0 and userid = $userid and postid = $row[id]";
    if (empty($r = $db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_xray_read (side, userid, postid, time) values(0, $userid, $row[id], 0)";
      $db->query($sql);
      $r = array('time' => 0);
    }

    $sql = "select id from pet_". PREFIX ."_xray_chat where postid = $row[id] and side = 1 and time > $r[time]";
    $count += intval($db->count($sql));
  }
  
  return $count;
}

function khoitaodulieu($userid) {
  global $db;

  $sql = "select username, name, fullname from pet_". PREFIX ."_users where userid = $userid";
  $userinfo = $db->fetch($sql);

  $homnay = date('d/m');
  $ngaymai = date('d/m', time() + 60 * 60 * 24);

  return array(
    'month' => array('start' => date('Y-m-01'), 'end' => date('Y-m-t')),
    'today' => date('d/m/Y'),
    'next' => date('d/m/Y', time() + 60 * 60 * 24 * 21),
    'userid' => $userid,
    'username' => $userinfo['username'],
    'fullname' => $userinfo['fullname'],
  );
}

function login() {
  global $data, $db, $result;
  $username = mb_strtolower($data->username);
  $password = $data->password;

  include_once(DIR . '/include/Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  $sql = "select userid, password from `pet_". PREFIX ."_users` where active = 1 and LOWER(username) = '$username'";
  if (empty($user = $db->fetch($sql))) $result['messenger'] = 'Người dùng không tồn tại';
  else if (!$crypt->validate_password($password, $user['password'])) $result['messenger'] = 'Sai mật khẩu';
  else {
    $session = randomString();
    $time = time();
    $sql = "insert into pet_". PREFIX ."_session (userid, session, time) values($user[userid], '$session', $time)";
    $db->query($sql);
    $result['status'] = 1;
    $result['session'] = $session;
    $result['data'] = khoitaodulieu($user['userid']);
    $result['badge'] = khoitaobadge($user['userid']);
    $result['phanquyen'] = permission($user['userid']);
    $result['site'] = getsiteconfig();
  }
  return $result;
}

function khoitaobadge($userid) {
  global $data, $db, $result;

  $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
  $sql = "select * from pet_". PREFIX ."_usg where status < 7 and recall < $lim and userid = $userid and utemp = 0";
  $uc = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_usg where status = 9 and userid = $userid";
  $ut = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_vaccine where status < 2 and recall < $lim and userid = $userid and utemp = 0";
  $vc = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_vaccine where status = 5 and userid = $userid";
  $vt = $db->count($sql);

  $daungay = strtotime(date("Y/m/d"));
  $cuoingay = $daungay + 60 * 60 * 24 - 1;
  $sql = "select id from pet_". PREFIX ."_datlich where trangthai = 0 and ngaydat < $cuoingay";
  $datlich = $db->count($sql);

  $sql = "select id from pet_". PREFIX ."_datlich where trangthai = 0 and (thoigian between $daungay and $cuoingay)";
  $datlichhomnay = $db->count($sql);

  $sql = "select id from pet_". PREFIX ."_danhgia where (thoigian between $daungay and $cuoingay)";
  $danhgia = $db->count($sql);
  
  $sql = "select id from pet_". PREFIX ."_hanghoa_donhang where trangthai = 0";
  $donhang = $db->count($sql);
  
  $sql = "select * from pet_". PREFIX ."_xray where insult = 0";
  $dieutri = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_kaizen where active = 1 and done = 0";
  $kaizen = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_xray_row where sinhhoa < 0 or sinhly < 0";
  $xetnghiem = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_exam where status = 0";
  $xetnghiemkhac = $db->count($sql);

  $hansudung = time() + 60 * 60 * 24 * 90;
  $sql = "select * from pet_". PREFIX ."_hansudung where trangthai = 0 and hansudung <= $hansudung";
  $result['data']['hsd'] = $db->count($sql);


  return [
    "donhang" => $donhang,
    "datlich" => $datlich,
    "datlichhomnay" => $datlichhomnay,
    "danhgia" => $danhgia,
    "vaccine" => $vc + $vt,
    "sieuam" => $uc + $ut,
    "dieutri" => $dieutri,
    "kaizen" => $kaizen,
    "xetnghiem" => $xetnghiem,
    "xetnghiemkhac" => $xetnghiemkhac,
  ];
}

function logout() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_session where session = '$data->session'";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function signin() {
  global $data, $db, $result;
  $username = mb_strtolower($data->username);
  $password = $data->password;

  include_once(DIR . '/include/Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  $sql = "select userid, password from `pet_". PREFIX ."_users` where LOWER(username) = '$username'";
  if (!empty($user = $db->fetch($sql))) $result['messenger'] = 'Tên người dùng đã tồn tại';
  else {
    $time = time();
    $sql = "insert into pet_". PREFIX ."_users (username, name, fullname, password, photo, regdate, active) values ('$data->username', '$data->name', '$data->fullname', '". $crypt->hash_password($data->password) ."', '', $time, 1)";
    $userid = $db->insertid($sql);
    
    $session = randomString();
    $time = time();
    $sql = "insert into pet_". PREFIX ."_session (userid, session, time) values($userid, '$session', $time)";
    $db->query($sql);

    $result['status'] = 1;
    $result['session'] = $session;
    $result['data'] = khoitaodulieu($userid);
    $result['phanquyen'] = permission($userid);
    $result['site'] = getsiteconfig();
  }
  return $result;
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

function permission($userid) {
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
    'donhang' => 0,
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

  if ($userid == 1 || $userid == 5) {
    $c["chucvu"] = 3; // 3: quản trị, 2: quản lý, 1: nhân viên
    foreach ($c as $key => $value) {
      $c[$key] = 2;
    }
    return $c;
  }
  else {
    $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid";
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
  $sql = "select * from pet_". PREFIX ."_config where module = 'site'";
  $site = $db->obj($sql, 'name', 'value');
  foreach ($arr as $key) {
    if (empty($site[$key])) $site[$key] = '';
  }
  return $site;
}
