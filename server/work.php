<?php

function khoitao() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachcongviec();
  $result['danhmuc'] = danhsachdanhmuc();
  $result['nhanvien'] = danhsachnhanvien();
  return $result;
}

function danhsachnhanvien() {
  global $db, $data;

  $sql = "select userid, fullname from pet_phc_users where active = 1";
  return $db->all($sql);
}

// userid, departid, title, content, file, time, createtime, expiretime, status
function themcongviec() {
  global $db, $data, $result;

  $userid = checkuserid();
  $data->content = nl2br($data->content);
  $file = implode(',', $data->image);
  $time = time();
  $createtime = isodatetotime($data->create);
  $expiretime = isodatetotime($data->expire);
  if (empty($expiretime)) $expiretime = 0;

  if (empty($data->id)) {
    $sql = "insert into pet_phc_work (userid, departid, title, content, file, time, createtime, expiretime, status) values($userid, $data->departid, '$data->title', '$data->content', '$file', $time, $createtime, $expiretime, 0)";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_phc_work set departid = $data->departid, title = '$data->title', content = '$data->content', file = '$file', time = $time, createtime = $data->createtime, expiretime = $data->expiretime where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_phc_work_follow where workid = $data->id";
  $db->query($sql);

  if (isset($data->follow)) {
    foreach ($data->follow as $nhanvien) {
      if ($nhanvien->value == 'true') {
        $sql = "insert into pet_phc_work_follow (workid, userid) values($data->id, $nhanvien->userid)";
        $db->query($sql);
      } 
    }
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function themnhanvien() {
  global $db, $data, $result;

  if (!isset($data->id)) {
    $sql = "insert into pet_phc_work_depart (name) values('$data->name')";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_phc_work_depart set name = '$data->name' where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_phc_work_depart_user where departid = $data->id";
  $db->query($sql);

  foreach ($data->list as $nhanvien) {
    if ($nhanvien->value == 'true') {
      $sql = "insert into pet_phc_work_depart_user (departid, userid) values($data->id, $nhanvien->userid)";
      $db->query($sql);
    } 
  }

  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  return $result;
}

function danhsach() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function danhmuc() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  return $result;
}

function xoadanhmuc() {
  global $db, $data, $result;
  
  $sql = "update pet_phc_work_depart set active = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  return $result;
}

function danhsachdanhmuc() {
  global $db, $data, $result;

  $sql = "select * from pet_phc_work_depart where active = 1 order by name asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $danhmuc) {
    $sql = "select a.userid, b.fullname from pet_phc_work_depart_user a inner join pet_phc_users b on a.userid = b.userid where a.departid = $danhmuc[id]";
    $danhsach[$thutu]['list'] = $db->obj($sql, 'userid', 'fullname');
    $danhsach[$thutu]['text'] = implode(', ', $db->arr($sql, 'fullname'));
  }

  return $danhsach;
}

function parsefile($file) {
  $file = explode(',', $file);
  $l = array();
  foreach ($file as $content) {
    if (strlen($content)) $l []= $content;
  }
  return $l;
}

// work: id, userid, title, content, time
// sequence: id, workid, overlap
// follow: id, workid, userid
// file: id, url
// comment: id, workid, comment
function danhsachcongviec() {
  global $db, $data, $result;

  $userid = checkuserid();
  $homnay = strtotime(date('Y/m/d')) - 1;
  switch ($data->chedo) {
    case '0':
      // công việc, lấy userid = userid, depart in (user depart)
      $sql = "select a.id from pet_phc_work_depart a inner join pet_phc_work_depart_user b on a.id = b.departid where b.userid = $userid";
      $chuyenmuc = $db->arr($sql, 'id');
      if (count($chuyenmuc)) $xtra = 'or departid in ('. implode(', ', $chuyenmuc) .')';
      else $xtra = '';
      $sql = "select * from pet_phc_work where (userid = $userid $xtra) order by time desc limit 50";
      $danhsachcongviec = $db->all($sql);

      break;
    case '1':
      // follow
      $sql = "select * from pet_phc_work where id in (select workid as id from pet_phc_work_follow where userid = $userid) order by time desc limit 50";
      $danhsachcongviec = $db->all($sql);
      break;
  }
  // userid trực thuộc department
  // tìm xem phòng ban trực thuộc, nếu là nhân viên, chỉ xem công việc, nếu là quản lý, được xem toàn bộ
  // tìm trong follow

  foreach ($danhsachcongviec as $thutu => $congviec) {
    $sql = "select a.userid, b.fullname from pet_phc_work_follow a inner join pet_phc_users b on a.userid = b.userid where a.workid = $congviec[id]";
    $danhsachcongviec[$thutu]['follow'] = $db->obj($sql, 'userid', 'fullname');
    $danhsachcongviec[$thutu]['text'] = implode(', ', $db->arr($sql, 'fullname'));

    $danhsachcongviec[$thutu]['time'] = date('d/m/Y', $congviec['time']);
    $danhsachcongviec[$thutu]['createtime'] = date('d/m/Y', $congviec['createtime']);
    $danhsachcongviec[$thutu]['file'] = parsefile($congviec['file']);
    $danhsachcongviec[$thutu]['expiretime'] = date('d/m/Y', $congviec['expiretime']);
    $danhsachcongviec[$thutu]['expire'] = 0;
    if (empty($congviec['expiretime'])) {
      $danhsachcongviec[$thutu]['expiretime'] = '';
    }
    else if ($homnay > $congviec['expiretime']) $danhsachcongviec[$thutu]['expire'] = 1;
  }

  return $danhsachcongviec;
}

function chuyentrangthai() {
  global $db, $data, $result;

  $arr = array(0 => 1, 2);
  $status = $arr[$data->status];
  $sql = "update pet_phc_work set status = $status where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachcongviec();
  return $result;
}
