<?php

function khoitao() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachcongviec();
  $result['danhmuc'] = danhsachdanhmuc();
  $result['nhanvien'] = danhsachnhanvien();
  return $result;
}

function khoitaolaplai() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachlaplai();
  return $result;
}

function danhsachlaplai() {
  global $db, $data, $result;

  // lấy danh sách công việc lặp lại do người dùng tạo
  $userid = checkuserid();
  $sql = "select a.*, b.type, b.time as rtime, b.list from pet_phc_work a inner join pet_phc_work_repeat b on a.id = b.workid and a.userid = $userid order by id desc";
  $danhsachcongviec = $db->all($sql);
  $time = time();

  foreach ($danhsachcongviec as $thutu => $congviec) {
    $sql = "select a.userid, b.fullname from pet_phc_work_follow a inner join pet_phc_users b on a.userid = b.userid where a.workid = $congviec[id]";
    $danhsachcongviec[$thutu]['follow'] = $db->obj($sql, 'userid', 'fullname');
    $danhsachcongviec[$thutu]['text'] = implode(', ', $db->arr($sql, 'fullname'));

    $danhsachcongviec[$thutu]['time'] = date('d/m/Y', $congviec['time']);
    $danhsachcongviec[$thutu]['createtime'] = date('d/m/Y', $congviec['createtime']);
    $danhsachcongviec[$thutu]['file'] = parseimage($congviec['file']);
    $danhsachcongviec[$thutu]['expiretime'] = date('d/m/Y', $congviec['expiretime']);
    $danhsachcongviec[$thutu]['expire'] = 0;
    $danhmuc = '';
    if ($congviec['departid'] > 0) {
      $sql = "select * from pet_phc_work_depart where id = $congviec[departid]";
      if (!empty($depart = $db->fetch($sql))) $danhmuc = $depart['name'];
    }

    $danhsachcongviec[$thutu]['danhmuc'] = $danhmuc;

    if (empty($congviec['expiretime'])) {
      $danhsachcongviec[$thutu]['expiretime'] = '';
    }
    else if ($congviec['status'] > 0 && $congviec['updatetime'] > $congviec['expiretime']) $danhsachcongviec[$thutu]['expire'] = 1;
    else if ($congviec['status'] == 0 && $time > $congviec['expiretime']) $danhsachcongviec[$thutu]['expire'] = 1;
  }

  return $danhsachcongviec;
}

function xoa() {
  global $db, $data, $result;

  $sql = "delete from pet_phc_work where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa công việc';
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function xoalaplai() {
  global $db, $data, $result;

  $sql = "delete from pet_phc_work_repeat where workid = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa công việc';
  $result['laplai'] = danhsachlaplai();
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
    $sql = "insert into pet_phc_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($userid, $data->departid, '$data->title', '$data->content', '$file', $time, $createtime, $expiretime, $time, 0)";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_phc_work set departid = $data->departid, title = '$data->title', content = '$data->content', file = '$file', time = $time, createtime = $createtime, expiretime = $expiretime where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_phc_work_follow where workid = $data->id";
  $db->query($sql);
  $sql = "delete from pet_phc_work_assign where workid = $data->id";
  $db->query($sql);
  $sql = "delete from pet_phc_work_repeat where workid = $data->id";
  $db->query($sql);

  foreach ($data->follow->list as $nhanvien) {
    if ($nhanvien->value == 'true') {
      $sql = "insert into pet_phc_work_follow (workid, userid) values($data->id, $nhanvien->userid)";
      $db->query($sql);
    } 
  }
  foreach ($data->assign->list as $nhanvien) {
    if ($nhanvien->value == 'true') {
      $sql = "insert into pet_phc_work_assign (workid, userid) values($data->id, $nhanvien->userid)";
      $db->query($sql);
    } 
  }

  // repeat
  $sql = "delete from pet_phc_work_repeat where workid = $data->id";
  $db->query($sql);

  if (isset($repeat)) {
    $repeat = $data->repeat;
    if ($repeat->type) {
      $repeat->time = isodatetotime($repeat->time);
      if (empty($repeat->time)) $repeat->time = 0;
      $list = array();
      $repeat->list = implode(',', $repeat->list);
      $sql = "insert into pet_phc_work_repeat (workid, type, time, list) values ($data->id, $repeat->type, $repeat->time, '$repeat->list')";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  if ($data->laplai) $result['laplai'] = danhsachlaplai();
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function themnhanvien() {
  global $db, $data, $result;

  if (isset($data->child)) $parent = $data->child;
  else $parent = 0;

  if (!isset($data->id)) {
    $sql = "insert into pet_phc_work_depart (name, parentid) values('$data->name', $parent)";
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
  $result['id'] = strval($data->id);
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

  $sql = "select * from pet_phc_work_depart where active = 1 and parentid = 0 order by name asc";
  $danhsach = $db->all($sql);
  $list = array();

  foreach ($danhsach as $thutu => $danhmuc) {
    $sql = "select a.userid, b.fullname from pet_phc_work_depart_user a inner join pet_phc_users b on a.userid = b.userid where a.departid = $danhmuc[id]";
    $danhsach[$thutu]['list'] = $db->obj($sql, 'userid', 'fullname');
    $danhsach[$thutu]['text'] = implode(', ', $db->arr($sql, 'fullname'));
    $sql = "select * from pet_phc_work_depart where active = 1 and parentid = $danhmuc[id] order by name asc";

    $child = $db->all($sql);
    if (count($child)) {
      $danhsach[$thutu]['child'] = $child;

      foreach ($danhsach[$thutu]['child'] as $thutuchild => $c) {
        $sql = "select a.userid, b.fullname from pet_phc_work_depart_user a inner join pet_phc_users b on a.userid = b.userid where a.departid = $c[id]";
        $danhsach[$thutu]['child'][$thutuchild]['list'] = $db->obj($sql, 'userid', 'fullname');
        $danhsach[$thutu]['child'][$thutuchild]['text'] = implode(', ', $db->arr($sql, 'fullname'));
      }
    }
    else $danhsach[$thutu]['child'] = array(array('id' => 0, 'name' => 'Không có danh mục con'));
    $list []= $danhsach[$thutu];
    if (count($child)) {
      foreach ($child as $tt => $c) {
        $child[$tt]['name'] = $danhmuc['name'].'/'. $c['name'];
        $list []= $child[$tt];
      }
    }
  }

  return $list;
}

function danhsachcongviec() {
  global $db, $data, $result;

  $filter = $data->filter;
  $thoigian = strtotime(date('Y/m/d'));

  $xtra = array();
  if (!empty($filter->tukhoa)) $xtra []= "(title like '%$filter->tukhoa%' or content like '%$filter->tukhoa%')";
  if (!empty($filter->danhmuc)) $xtra []= "departid = $filter->danhmuc";
  switch ($filter->denhan) {
    case '0':
      $xtra []= '1';
      break;
    case '1':
      // gần hạn
      $gioihan = strtotime(date('Y/m/d')) - 7 * 60 * 60 * 24;
      $xtra []= "(expiretime > 0 and expiretime < $gioihan)";
      break;
    case '2':
      // quá hạn
      $xtra []= "expiretime > $thoigian";
      break;
  }
  $hoanthanh = array(0 => '1', 'status = 0', 'status > 0');
  $xtra [] = $hoanthanh[$filter->hoanthanh];

  if (!empty($xtra)) $xtra = 'and '. implode(' and ', $xtra);
  else $xtra = '';
  $time = time();

  $userid = checkuserid();
  $homnay = strtotime(date('Y/m/d')) - 1;
  switch ($data->chedo) {
    case '0':
      // công việc, lấy userid = userid, depart in (user depart), workid in assign
      $sql = "select a.id from pet_phc_work_depart a inner join pet_phc_work_depart_user b on a.id = b.departid where b.userid = $userid";
      $chuyenmuc = $db->arr($sql, 'id');
      if (count($chuyenmuc)) $xtra2 = 'or departid in ('. implode(', ', $chuyenmuc) .')';
      else $xtra2 = '';
      $sql = "select * from pet_phc_work where ((userid = $userid $xtra2) or id in (select workid as id from pet_phc_work_assign where userid = $userid)) $xtra order by time desc, updatetime desc limit 50";
      $danhsachcongviec = $db->all($sql);
      break;
    case '1':
      // workin in follow
      $sql = "select * from pet_phc_work where id in (select workid as id from pet_phc_work_follow where userid = $userid) $xtra order by time desc, updatetime desc limit 50";
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
    $danhsachcongviec[$thutu]['file'] = parseimage($congviec['file']);
    $danhsachcongviec[$thutu]['expiretime'] = date('d/m/Y', $congviec['expiretime']);
    $danhsachcongviec[$thutu]['expire'] = 0;
    $danhmuc = '';
    if ($congviec['departid'] > 0) {
      $sql = "select * from pet_phc_work_depart where id = $congviec[departid]";
      if (!empty($depart = $db->fetch($sql))) {
        $danhmuc = $depart['name'];        
        if ($depart['parentid']) {
          $sql = "select * from pet_phc_work_depart where id = $depart[parentid]";
          if (!empty($depart = $db->fetch($sql))) {
            $danhmuc = $depart['name'] .'/'. $danhmuc;
          }
        }
      }
    }

    $danhsachcongviec[$thutu]['danhmuc'] = $danhmuc;
    // nếu chế độ = 0, kiểm tra userid = $userid, type = 2, nếu không type = 1, còn không type = 0
    if ($data->chedo == '0') {
      if ($congviec['userid'] == $userid) $type = 2;
      else $type = 1;
    }
    else $type = 0;
    $danhsachcongviec[$thutu]['type'] = $type; // 0, chỉ xem, 1, hoàn thành, 2, duyệt

    if (empty($congviec['expiretime'])) {
      $danhsachcongviec[$thutu]['expiretime'] = '';
    }
    else if ($congviec['status'] > 0 && $congviec['updatetime'] > $congviec['expiretime']) $danhsachcongviec[$thutu]['expire'] = 1;
    else if ($congviec['status'] == 0 && $time > $congviec['expiretime']) $danhsachcongviec[$thutu]['expire'] = 1;
  }

  $danhsachtam = array(0 => array(), array());
  foreach ($danhsachcongviec as $key => $value) {
    if ($value['status'] == 0) $danhsachtam[0] []= $value;
    else $danhsachtam[1] []= $value;
  }

  return $danhsachtam;
}

function chuyentrangthai() {
  global $db, $data, $result;

  $arr = array(0 => 1, 2);
  $status = $arr[$data->status];
  $utime = time();
  if ($status == 2) $time = 0;
  else $time = time();
  $sql = "update pet_phc_work set status = $status, updatetime = $utime, time = $time where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['trangthai'] = $status;
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function laythongtin() {
  global $db, $data, $result;

  $sql = "select * from pet_phc_work where id = $data->id";
  $congviec = $db->fetch($sql);

  $sql = "select * from pet_phc_users where active = 1";
  $nhanvien = $db->obj($sql, 'userid');

  $sql = "select * from pet_phc_work_follow where workid = $data->id";
  $theodoi = $db->all($sql);
  $follow = array('text' => array(), 'list' => array());
  foreach ($theodoi as $dulieu) {
    $follow['text'] []= $nhanvien[$dulieu['userid']]['fullname'];
  }
  foreach ($nhanvien as $dulieu) {
    $value = false;
    foreach ($theodoi as $dulieutheodoi) {
      if ($dulieutheodoi['userid'] == $dulieu['userid']) $value = true;
    }
    $follow['list'] []= array(
      'value' => $value,
      'userid' => $dulieu['userid'],
      'name' => $dulieu['fullname']
    );
  }

  $sql = "select * from pet_phc_work_assign where workid = $data->id";
  $giaoviec = $db->all($sql);
  $assign = array('text' => array(), 'list' => array());
  foreach ($giaoviec as $dulieu) {
    $assign['text'] []= $nhanvien[$dulieu['userid']]['fullname'];
  }
  foreach ($nhanvien as $dulieu) {
    $value = false;
    foreach ($giaoviec as $dulieugiaoviec) {
      if ($dulieugiaoviec['userid'] == $dulieu['userid']) $value = true;
    }
    $assign['list'] []= array(
      'value' => $value,
      'userid' => $dulieu['userid'],
      'name' => $dulieu['fullname']
    );
  }

  $sql = "select * from pet_phc_work_repeat where workid = $data->id";
  if (empty($laplai = $db->fetch($sql))) $repeat = array(
    'type' => '0',
    'time' => '',
    'list' => array(0, 0, 0, 0, 0, 0, 0),
  );
  else $repeat = array(
    'type' => $laplai['type'],
    'time' => date('d-m-Y', $laplai['time']),
    'list' => explode(',', $laplai['list']),
  );

  foreach ($repeat['list'] as $key => $value) {
    $repeat['list'][$key] = intval($value);
  }

  $dulieu = array(
    'id' => $congviec['id'],
    'title' => $congviec['title'],
    'content' => $congviec['content'],
    'departid' => $congviec['departid'],
    'image' => parseimage($congviec['file']),
    'create' => date('Y-m-d', $congviec['createtime']),
    'expire' => $congviec['expiretime'] > 0 ? date('Y-m-d', $congviec['expiretime']) : '',
    'follow' => $follow,
    'assign' => $assign,
    'repeat' => $repeat
  );

  $result['status'] = 1;
  $result['dulieu'] = $dulieu;
  return $result;
}

function laybinhluan() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['binhluan'] = danhsachbinhluan();
  return $result;
}

function xoabinhluan() {
  global $db, $data, $result;

  $sql = "delete from pet_phc_work_comment where id = $data->commentid";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa bình luận';
  $result['binhluan'] = danhsachbinhluan();
  return $result;
}

function thongke() {
  global $db, $data, $result;

  $batdau = isodatetotime($data->batdau);
  $ketthuc = isodatetotime($data->ketthuc);
  $time = time();
  $sql = "select userid, fullname as ten from pet_phc_users where active = 1 order by userid asc";
  $danhsachnhanvien = $db->all($sql);

  foreach ($danhsachnhanvien as $key => $nhanvien) {
    $danhsachnhanvien[$key]['tong'] = 0;
    $danhsachnhanvien[$key]['hoanthanh'] = 0;
    $danhsachnhanvien[$key]['conlai'] = 0;
    $danhsachnhanvien[$key]['hoanthanhquahan'] = 0;
    $danhsachnhanvien[$key]['conlaiquahan'] = 0;
    $sql = "select * from pet_phc_work where ((createtime between $batdau and $ketthuc) or (createtime < $batdau and status = 0)) and userid = $nhanvien[userid]";
    $danhsachcongviec = $db->all($sql);
    foreach ($danhsachcongviec as $congviec) {
      $danhsachnhanvien[$key]['tong'] ++;
      if ($congviec['status'] > 0) {
        $danhsachnhanvien[$key] ['hoanthanh'] ++;
        if ($congviec['expiretime'] > 0 && $congviec['updatetime'] >= $congviec['expiretime']) $danhsachnhanvien[$key] ['hoanthanhquahan'] ++;
      }
      else {
        $danhsachnhanvien[$key] ['conlai'] ++;
        if ($congviec['expiretime'] > 0 && $time >= $congviec['expiretime']) $danhsachnhanvien[$key] ['conlaiquahan'] ++;
      } 
    }
    if ($danhsachnhanvien[$key]['tong'] > 0) {
      $danhsachnhanvien[$key]['phantramhoanthanh'] = round($danhsachnhanvien[$key]['hoanthanh'] * 100 / $danhsachnhanvien[$key]['tong'], 2);
      $danhsachnhanvien[$key]['phantramconlai'] = round($danhsachnhanvien[$key]['conlai'] * 100 / $danhsachnhanvien[$key]['tong'], 2);
      $danhsachnhanvien[$key]['phantramhoanthanhquahan'] = round($danhsachnhanvien[$key]['hoanthanhquahan'] * 100 / $danhsachnhanvien[$key]['tong'], 2);
      $danhsachnhanvien[$key]['phantramconlaiquahan'] = round($danhsachnhanvien[$key]['conlaiquahan'] * 100 / $danhsachnhanvien[$key]['tong'], 2);
    }
  }

  $result['status'] = 1;
  $result['nhanvien'] = $danhsachnhanvien;
  return $result;
}

function nhantin() {
  global $db, $data, $result;

  $userid = checkuserid();
  $time = time();
  if (empty($data->commentid)) $sql = "insert into pet_phc_work_comment (workid, userid, comment, file, time) values($data->workid, $userid, '$data->chat', '$data->image', $time)";
  else $sql = "update pet_phc_work_comment set comment = '$data->chat', file = '$data->image' where id = $data->commentid";
  $db->query($sql);

  $result['status'] = 1;
  $result['binhluan'] = danhsachbinhluan();
  return $result;
}

function danhsachbinhluan() {
  global $db, $data, $result;

  $sql = "select a.*, b.fullname from pet_phc_work_comment a inner join pet_phc_users b on a.userid = b.userid where a.workid = $data->id order by time asc";
  $danhsachbinhluan = $db->all($sql);
  foreach ($danhsachbinhluan as $key => $binhluan) {
    $danhsachbinhluan[$key]['time'] = date('d/m/Y h:i', $binhluan['time']);
  }

  return $danhsachbinhluan;
}