<?php
function check() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_users where session = '$data->session'";

  if (empty($user = $db->fetch($sql))) return true;
  return false;
}

function notify() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_". PREFIX ."_notify set status = 1 where userid = $userid and status = 0";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
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
      $result['data'] = getinitdata($user['userid']);
      $result['config'] = permission($user['userid']);
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

function getinitdata($userid) {
  global $db;
  $admin = 0;
  if ($userid == 1 || $userid == 5) $admin = 1;
  else {
    $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'admin' and type = 1";
    if (!empty($db->fetch($sql))) $admin = 1;
  }

  $sql = "select username, name, fullname from pet_". PREFIX ."_users where userid = $userid";
  $userinfo = $db->fetch($sql);

  $sql = "select userid, fullname as name, fullname, username from pet_". PREFIX ."_users where userid in (select userid from pet_". PREFIX ."_user_per where module = 'doctor' and type = 1)";
  $doctor = $db->all($sql);

  $sql = "select * from pet_". PREFIX ."_vaccineloai where active = 1";
  $type = $db->all($sql);

  $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
  $sql = "select * from pet_". PREFIX ."_usg where status < 7 and recall < $lim and userid = $userid and utemp = 0";
  $uc = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_usg where status = 9 and userid = $userid";
  $ut = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_vaccine where status < 2 and recall < $lim and userid = $userid and utemp = 0";
  $vc = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_vaccine where status = 5 and userid = $userid";
  $vt = $db->count($sql);

  $sql = "select id, name, value, alt from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $spa = $db->all($sql);
  $ds = array();

  foreach ($spa as $key => $s) {
    if ($s['alt']) $ds []= $s['id'];
    $spa[$key]['check'] = 0;
  }

  $sql = "select * from pet_". PREFIX ."_config where module = 'docs' and name = '$userid'";
  if (empty($docs = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value) values ('docs', $userid, '')";
    $db->query($sql);
    $docs['value'] = '';
  }
  $sql = "select * from pet_". PREFIX ."_config where module = 'docscover' and name = '$userid'";
  if (empty($docscover = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value) values ('docscover', $userid, '')";
    $db->query($sql);
    $docscover['value'] = '';
  }
  if (!strlen($docs['value'])) $docs = array();
  else $docs = explode(', ', $docs['value']);

  $sql = "select id, name from pet_". PREFIX ."_config where module = 'usg'";
  $usgcode = $db->all($sql);

  $homnay = date('d/m');
  $ngaymai = date('d/m', time() + 60 * 60 * 24);

  $sql = "select birthday, fullname from pet_". PREFIX ."_users where birthday > 0";
  $danhsachsinhnhat = $db->all($sql);
  $sinhnhat = [];
  foreach ($danhsachsinhnhat as $key => $ngaysinh) {
    $ngaythangsinh = date('d/m', $ngaysinh['birthday']);
    if ($ngaythangsinh == $homnay || $ngaythangsinh == $ngaymai) $sinhnhat []= ['fullname' => $ngaysinh['fullname'], 'birthday' => date('d/m/Y', $ngaysinh['birthday'])];
  }

  $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'chotlich'";
  $chotlich = $db->fetch($sql);
  if (empty($chotlich)) $chotlich = "0";
  else $chotlich = $chotlich['value'];

  return array(
    'month' => array('start' => date('Y-m-01'), 'end' => date('Y-m-t')),
    'prefix' => PREFIX,
    'branch' => BRANCH,
    'userid' => $userid,
    'chotlich' => $chotlich,
    'username' => $userinfo['username'],
    'name' => $userinfo['name'],
    'fullname' => $userinfo['fullname'],
    'admin' => $admin,
    'doctor' => $doctor,
    'type' => $type,
    'spa' => $spa,
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
    $result['data'] = getinitdata($user['userid']);
    $result['config'] = permission($user['userid']);
    $result['site'] = getsiteconfig();
  }
  return $result;
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
    $sql = "insert into pet_". PREFIX ."_users (username, name, fullname, password, photo, regdate) values ('$data->username', '$data->name', '$data->fullname', '". $crypt->hash_password($data->password) ."', '', $time)";
    $userid = $db->insertid($sql);
    
    $session = randomString();
    $time = time();
    $sql = "insert into pet_". PREFIX ."_session (userid, session, time) values($userid, '$session', $time)";
    $db->query($sql);

    $result['status'] = 1;
    $result['session'] = $session;
    $result['data'] = getinitdata($userid);
    $result['config'] = permission($userid);
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

function badge() {
  global $data, $db, $result;

  $result['data'] = array(
    'his' => 0, 'kaizen' => 0, 'profile' => 0, 'physical' => 0, 'xquang' => 0, 'sieuam' => 0, 'other' => 0, 'init' => true
  );
  $sql = "select * from pet_". PREFIX ."_xray where insult = 0";
  $result['data']['his'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_kaizen where active = 1 and done = 0";
  $result['data']['kaizen'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_xray_row where sinhhoa < 0";
  $result['data']['profile'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_xray_row where sinhly < 0";
  $result['data']['physical'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_xray_row where xquang < 0";
  $result['data']['xquang'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_xray_row where sieuam < 0";
  $result['data']['sieuam'] = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_exam where status = 0";
  $result['data']['other'] = $db->count($sql);
  $result['status'] = 1;

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
    'work' => 0,
    'nhantin' => 0,
  ];

  if ($userid == 1) {
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
