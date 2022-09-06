<?php
function check() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_users where session = '$data->session'";

  if (empty($user = $db->fetch($sql))) return true;
  return false;
}

function notify() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_phc_notify set status = 1 where userid = $userid and status = 0";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function session() {
  global $data, $db, $result;

  $sql = "select * from pet_phc_session where session = '$data->sess'";
  $user = $db->fetch($sql);
  $time = time();
  $end = $time - 60 * 60 * 24 * 7; // 7 days

  if (!empty($user)) {
    if ($user['time'] > $end) {
      $result['status'] = 1;
      $sql = "update pet_phc_session set time = $time where id = $user[id]";
      $db->query($sql);
      $result['data'] = getinitdata($user['userid']);
      $result['config'] = permission($user['userid']);
      $result['site'] = getsiteconfig();
    }
    else {
      $sql = "delete from pet_phc_session where id = $user[id]";
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
  
  $sql = "select * from pet_phc_xray where doctorid = $userid or pos = 1";
  $c = $db->all($sql);

  $count = 0;
  foreach ($c as $row) {
    $sql = "select * from pet_phc_xray_read where side = 0 and userid = $userid and postid = $row[id]";
    if (empty($r = $db->fetch($sql))) {
      $sql = "insert into pet_phc_xray_read (side, userid, postid, time) values(0, $userid, $row[id], 0)";
      $db->query($sql);
      $r = array('time' => 0);
    }

    $sql = "select id from pet_phc_xray_chat where postid = $row[id] and side = 1 and time > $r[time]";
    $count += intval($db->count($sql));
  }
  
  return $count;
}

function getinitdata($userid) {
  global $db;
  $admin = 0;
  if ($userid == 1 || $userid == 5) $admin = 1;
  else {
    $sql = "select * from pet_phc_user_per where userid = $userid and module = 'admin' and type = 1";
    if (!empty($db->fetch($sql))) $admin = 1;
  }

  $sql = "select username, name, fullname from pet_phc_users where userid = $userid";
  $userinfo = $db->fetch($sql);

  $sql = "select userid, fullname as name, fullname, username from pet_phc_users where userid in (select userid from pet_phc_user_per where module = 'doctor' and type = 1)";
  $doctor = $db->all($sql);

  $sql = "select * from pet_phc_type where active = 1";
  $type = $db->all($sql);

  $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
  $sql = "select * from pet_phc_usg where status < 7 and recall < $lim and userid = $userid and utemp = 0";
  $uc = $db->count($sql);
  $sql = "select * from pet_phc_usg where status = 9 and userid = $userid";
  $ut = $db->count($sql);

  $sql = "select * from pet_phc_vaccine where status < 2 and recall < $lim and userid = $userid and utemp = 0";
  $vc = $db->count($sql);
  $sql = "select * from pet_phc_vaccine where status = 5 and userid = $userid";
  $vt = $db->count($sql);

  $sql = "select id, name, value, alt from pet_phc_config where module = 'spa' order by value asc";
  $spa = $db->all($sql);
  $ds = array();

  foreach ($spa as $key => $s) {
    if ($s['alt']) $ds []= $s['id'];
    $spa[$key]['check'] = 0;
  }

  $sql = "select * from pet_phc_config where module = 'docs' and name = '$userid'";
  if (empty($docs = $db->fetch($sql))) {
    $sql = "insert into pet_phc_config (module, name, value) values ('docs', $userid, '')";
    $db->query($sql);
    $docs['value'] = '';
  }
  $sql = "select * from pet_phc_config where module = 'docscover' and name = '$userid'";
  if (empty($docscover = $db->fetch($sql))) {
    $sql = "insert into pet_phc_config (module, name, value) values ('docscover', $userid, '')";
    $db->query($sql);
    $docscover['value'] = '';
  }
  if (!strlen($docs['value'])) $docs = array();
  else $docs = explode(', ', $docs['value']);

  $sql = "select id, name from pet_phc_config where module = 'usg'";
  $usgcode = $db->all($sql);

  return array(
    'month' => array('start' => date('Y-m-01'), 'end' => date('Y-m-t')),
    'userid' => $userid,
    'username' => $userinfo['username'],
    'name' => $userinfo['name'],
    'fullname' => $userinfo['fullname'],
    'admin' => $admin,
    'doctor' => $doctor,
    'type' => $type,
    'spa' => $spa,
    'his' => getHisChatCount($userid),
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

  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  $sql = 'select userid, password from `pet_phc_users` where active = 1 and LOWER(username) = "'. $username .'"';
  if (empty($user = $db->fetch($sql))) $result['messenger'] = 'Người dùng không tồn tại';
  else if (!$crypt->validate_password($password, $user['password'])) $result['messenger'] = 'Sai mật khẩu';
  else {
    $session = randomString();
    $time = time();
    $sql = "insert into pet_phc_session (userid, session, time) values($user[userid], '$session', $time)";
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

  $sql = "delete from pet_phc_session where session = '$data->session'";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function signin() {
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
    $sql = "insert into pet_phc_users (username, name, fullname, password, photo, regdate) values ('$data->username', '$data->name', '$data->fullname', '". $crypt->hash_password($data->password) ."', '', $time)";
    $userid = $db->insertid($sql);
    
    $session = randomString();
    $time = time();
    $sql = "insert into pet_phc_session (userid, session, time) values($userid, '$session', $time)";
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
  $sql = "update pet_phc_users set name = '$data->name' where userid = $userid";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function password() {
  global $data, $db, $result;
  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  if (empty($data->old)) $result['messenger'] = 'Mật khẩu cũ trống';
  else if (empty($data->new)) $result['messenger'] = 'Mật khẩu mới trống';
  else {
    $userid = checkuserid();
    $sql = 'select password from `pet_phc_users` where userid = '. $userid;
    $user_info = $db->fetch($sql);
    if (empty($user_info)) $result['messenger'] = 'Người dùng không tồn tại';
    else if (!$crypt->validate_password($data->old, $user_info['password'])) $result['messenger'] = 'Sai mật khẩu cũ';
    else {
      $password = $crypt->hash_password($data->new, '{SSHA512}');
      $sql = 'update `pet_phc_users` set password = "'. $password .'" where userid = '. $userid;
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
  $sql = "select * from pet_phc_xray where insult = 0";
  $result['data']['his'] = $db->count($sql);
  $sql = "select * from pet_phc_kaizen where active = 1 and done = 0";
  $result['data']['kaizen'] = $db->count($sql);
  $sql = "select * from pet_phc_xray_row where sinhhoa < 0";
  $result['data']['profile'] = $db->count($sql);
  $sql = "select * from pet_phc_xray_row where sinhly < 0";
  $result['data']['physical'] = $db->count($sql);
  $sql = "select * from pet_phc_xray_row where xquang < 0";
  $result['data']['xquang'] = $db->count($sql);
  $sql = "select * from pet_phc_xray_row where sieuam < 0";
  $result['data']['sieuam'] = $db->count($sql);
  $sql = "select * from pet_phc_exam where status = 0";
  $result['data']['other'] = $db->count($sql);
  $result['status'] = 1;

  return $result;
}

function permission($userid) {
  global $data, $db;

  if ($userid == 1) {
    return array(
      'spa' => 2,
      'vaccine' => 2,
      'schedule' => 2,
      'item' => 2,
      'kaizen' => 2,
      'drug' => 2,
      'price' => 2,
      'ride' => 2,
      'profile' => 2,
      'physical' => 2,
      'his' => 2,
      'sieuam' => 2,
      'xquang' => 2,
      'transport' => 2,
      'excel' => 2,
      'hotel' => 2,
      'other' => 2,
      'luong' => 2,
      'accounting' => 2,
      'work' => 2,
    );
  }
  else {
    $sql = "select name, 0 as per from pet_phc_config where module = 'setting'";
    $c = $db->obj($sql, 'name', 'per');
  
    $sql = "select * from pet_phc_user_per where userid = $userid";
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
  $sql = "select * from pet_phc_config where module = 'site'";
  $site = $db->obj($sql, 'name', 'value');
  foreach ($arr as $key) {
    if (empty($site[$key])) $site[$key] = '';
  }
  return $site;
}
