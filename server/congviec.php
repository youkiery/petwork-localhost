<?php

function khoitao() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  $result['danhsach'] = danhsachcongviec();
  $result['nhanvien'] = danhsachnhanvien();
  return $result;
}

function danhsachcongviec() {
  global $db, $data, $result;

  $timkiem = $data->timkiem;
  $thoigian = strtotime(date('Y/m/d'));

  $xtra = array();
  if (!empty($timkiem->tukhoa)) $xtra []= "(title like '%$timkiem->tukhoa%' or content like '%$timkiem->tukhoa%')";
  if (!empty($timkiem->danhmuc)) {
    $sql = "select * from pet_". PREFIX ."_work_depart where parentid = $timkiem->danhmuc";
    $danhmuc = $db->arr($sql, 'id');
    $xtra []= "(departid = $timkiem->danhmuc". (count($danhmuc) ? ' or departid in (' .implode(', ', $danhmuc). ')' : '') . ")";
  }
  switch ($timkiem->denhan) {
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
  $hoanthanh = array(0 => 'status = 0', 'status > 0', "1");
  $xtra [] = $hoanthanh[$timkiem->hoanthanh];

  if (!empty($xtra)) $xtra = 'and '. implode(' and ', $xtra);
  else $xtra = '';

  // lấy danh sách danh mục
  $danhmuc = danhsachcaydanhmuc();
  $danhmuc[]= ["id" => 0, "child" => [], "name" => "Chưa phân loại"];
  foreach ($danhmuc as $thutu => $chitietdanhmuc) {
    $soluong = 0;
    if (count($chitietdanhmuc["child"])) {
      foreach ($chitietdanhmuc["child"] as $tt => $c) {
        $danhmuc[$thutu]["child"][$tt]["danhsach"] = chitietcongviec($c, $xtra);
        $danhmuc[$thutu]["child"][$tt]["hienthi"] = 0;
        $soluong += count($danhmuc[$thutu]["child"][$tt]["danhsach"]);
        $danhmuc[$thutu]["child"][$tt]["soluong"] = $soluong;
      }
    }
    $danhmuc[$thutu]["danhsach"] = chitietcongviec($chitietdanhmuc, $xtra);
    $danhmuc[$thutu]["hienthi"] = 0;
    $danhmuc[$thutu]["soluong"] = $soluong + count($danhmuc[$thutu]["danhsach"]);
  }

  return $danhmuc;
}

function chitietcongviec($danhmuc, $xtra) {
  global $db;

  // kiểm tra nhân viên có phải chủ danh mục hoặc admin không?
  // nếu có thì hiển thị toàn bộ
  // kiểm tra nhân viên có trong danh mục hay không
  // nếu có thì lấy danh sách
  // nếu không thì kiểm tra phòng ban đó có danh sách công việc hay không
  $thoigian = time();
  $idnhanvien = checkuserid();
  $level = 0;
  $sql = "select * from pet_". PREFIX ."_user_per where userid = $idnhanvien and module = 'work' and type = 2";
  if (!empty($db->fetch($sql))) $level = 2;
  else {
    $sql = "select * from pet_". PREFIX ."_work_depart_user where departid = $danhmuc[id] and userid = $idnhanvien";
    $thongtindanhmuc = $db->fetch($sql);
    if (!empty($thongtindanhmuc)) {
      $level = 1;
    }
  }

  if ($level) {
    $sql = "select * from pet_". PREFIX ."_work where departid = $danhmuc[id] $xtra order by createtime desc";
    $danhsachcongviec = $db->all($sql);
  }
  else {
    $sql = "select * from pet_". PREFIX ."_work where (userid = $idnhanvien or ((id in (select workid as id from pet_". PREFIX ."_work_assign where userid = $idnhanvien) or id in (select workid as id from pet_". PREFIX ."_work_follow where userid = $idnhanvien)))) and departid = $danhmuc[id] $xtra order by createtime desc";
    $danhsachcongviec = $db->all($sql);
  }

  foreach ($danhsachcongviec as $thutu => $congviec) {
    $sql = "select a.userid, b.fullname from pet_". PREFIX ."_work_follow a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.workid = $congviec[id]";
    $danhsachcongviec[$thutu]['follow'] = $db->obj($sql, 'userid', 'fullname');
    $danhsachcongviec[$thutu]['followtext'] = implode(', ', $db->arr($sql, 'fullname'));
    $sql = "select a.userid, b.fullname from pet_". PREFIX ."_work_assign a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.workid = $congviec[id]";
    $danhsachcongviec[$thutu]['assign'] = $db->obj($sql, 'userid', 'fullname');
    $assign = implode(', ', $db->arr($sql, 'fullname'));
    $danhsachcongviec[$thutu]['assigntext'] = (strlen($assign) ? $assign : "Chưa chỉ định");

    $sql = "select fullname from pet_". PREFIX ."_users where userid = $congviec[userid]";
    $danhsachcongviec[$thutu]['usertext'] = $db->fetch($sql)['fullname'];

    $danhsachcongviec[$thutu]['time'] = date('d/m/Y', $congviec['time']);
    $danhsachcongviec[$thutu]['createtime'] = date('d/m/Y', $congviec['createtime']);
    $danhsachcongviec[$thutu]['file'] = parseimage($congviec['file']);
    $danhsachcongviec[$thutu]['expiretime'] = date('d/m/Y', $congviec['expiretime']);
    $danhsachcongviec[$thutu]['expire'] = 0;
    $danhmuc = '';
    if ($congviec['departid'] > 0) {
      $sql = "select * from pet_". PREFIX ."_work_depart where id = $congviec[departid]";
      if (!empty($depart = $db->fetch($sql))) {
        $danhmuc = $depart['name'];        
        if ($depart['parentid']) {
          $sql = "select * from pet_". PREFIX ."_work_depart where id = $depart[parentid]";
          if (!empty($depart = $db->fetch($sql))) {
            $danhmuc = $depart['name'] .'/'. $danhmuc;
          }
        }
      }
    }

    $danhsachcongviec[$thutu]['danhmuc'] = $danhmuc;
    // nếu chế độ = 0, kiểm tra userid = $userid, type = 2, nếu không type = 1, còn không type = 0
    if ($idnhanvien == 1 || $level == 2) $type = 2;
    else if ($congviec['userid'] == $idnhanvien) $type = 1;
    else if (!empty($danhsachcongviec[$thutu]['assign'][$idnhanvien])) $type = 1;
    else $type = 0;
    $danhsachcongviec[$thutu]['type'] = $type; // 0, chỉ xem, 1, hoàn thành, 2, duyệt

    if (empty($congviec['expiretime'])) {
      $danhsachcongviec[$thutu]['expiretime'] = '';
    }
    else if ($congviec['status'] > 0 && $congviec['updatetime'] > $congviec['expiretime']) $danhsachcongviec[$thutu]['expire'] = 1;
    else if ($congviec['status'] == 0 && $thoigian > $congviec['expiretime']) $danhsachcongviec[$thutu]['expire'] = 1;
  }
  return $danhsachcongviec;
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
  $sql = "select a.*, b.type, b.time as rtime, b.list from pet_". PREFIX ."_work a inner join pet_". PREFIX ."_work_repeat b on a.id = b.workid and a.userid = $userid order by id desc";
  $danhsachcongviec = $db->all($sql);
  $time = time();

  foreach ($danhsachcongviec as $thutu => $congviec) {
    $sql = "select a.userid, b.fullname from pet_". PREFIX ."_work_follow a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.workid = $congviec[id]";
    $danhsachcongviec[$thutu]['follow'] = $db->obj($sql, 'userid', 'fullname');
    $danhsachcongviec[$thutu]['text'] = implode(', ', $db->arr($sql, 'fullname'));

    $danhsachcongviec[$thutu]['time'] = date('d/m/Y', $congviec['time']);
    $danhsachcongviec[$thutu]['createtime'] = date('d/m/Y', $congviec['createtime']);
    $danhsachcongviec[$thutu]['file'] = parseimage($congviec['file']);
    $danhsachcongviec[$thutu]['expiretime'] = date('d/m/Y', $congviec['expiretime']);
    $danhsachcongviec[$thutu]['expire'] = 0;
    $danhmuc = '';
    if ($congviec['departid'] > 0) {
      $sql = "select * from pet_". PREFIX ."_work_depart where id = $congviec[departid]";
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

  $sql = "delete from pet_". PREFIX ."_work where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa công việc';
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function xoalaplai() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_work_repeat where workid = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa công việc';
  $result['laplai'] = danhsachlaplai();
  return $result;
}

function danhsachnhanvien() {
  global $db, $data;

  $sql = "select userid, fullname from pet_". PREFIX ."_users where active = 1";
  return $db->all($sql);
}

// userid, departid, title, content, file, time, createtime, expiretime, status
function themcongviec() {
  global $db, $data, $result;

  $userid = checkuserid();
  $file = implode(',', $data->image);
  $time = time();
  $createtime = isodatetotime($data->create);
  $expiretime = isodatetotime($data->expire);
  if (empty($expiretime)) $expiretime = 0;

  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX ."_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($userid, $data->departid, '$data->title', '$data->content', '$file', $time, $createtime, $expiretime, $time, 0)";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_work set departid = $data->departid, title = '$data->title', content = '$data->content', file = '$file', time = $time, expiretime = $expiretime where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_work_follow where workid = $data->id";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_work_assign where workid = $data->id";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_work_repeat where workid = $data->id";
  $db->query($sql);

  foreach ($data->follow->list as $nhanvien) {
    if ($nhanvien->value == 'true') {
      $sql = "insert into pet_". PREFIX ."_work_follow (workid, userid) values($data->id, $nhanvien->userid)";
      $db->query($sql);
    } 
  }
  foreach ($data->assign->list as $nhanvien) {
    if ($nhanvien->value == 'true') {
      $sql = "insert into pet_". PREFIX ."_work_assign (workid, userid) values($data->id, $nhanvien->userid)";
      $db->query($sql);
    } 
  }

  // repeat
  $sql = "delete from pet_". PREFIX ."_work_repeat where workid = $data->id";
  $db->query($sql);

  if (isset($data->repeat)) {
    $repeat = $data->repeat;
    if ($repeat->type) {
      $repeat->time = isodatetotime($repeat->time);
      if (empty($repeat->time)) $repeat->time = 0;
      if (count($repeat->list)) {
        foreach ($data->repeat->list as $thutu => $giatri) {
          if ($giatri) $data->repeat->list[$thutu] = 1;
          else $data->repeat->list[$thutu] = 0;
        }
      }
      $list = array();
      $repeat->list = implode(',', $repeat->list);
      $sql = "insert into pet_". PREFIX ."_work_repeat (workid, type, time, list) values ($data->id, $repeat->type, $repeat->time, '$repeat->list')";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  if (isset($data->laplai)) $result['laplai'] = danhsachlaplai();
  if (isset($data->chitiet)) $result['chitiet'] = laythongtintheoid();
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function themnhanvien() {
  global $db, $data, $result;

  if (isset($data->child)) $parent = $data->child;
  else $parent = 0;

  if (!isset($data->id)) {
    $sql = "insert into pet_". PREFIX ."_work_depart (name, parentid) values('$data->name', $parent)";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_work_depart set name = '$data->name' where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_work_depart_user where departid = $data->id";
  $db->query($sql);

  foreach ($data->list as $nhanvien) {
    if ($nhanvien->value == 'true') {
      $sql = "insert into pet_". PREFIX ."_work_depart_user (departid, userid) values($data->id, $nhanvien->userid)";
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
  
  $sql = "update pet_". PREFIX ."_work_depart set active = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhmuc'] = danhsachdanhmuc();
  return $result;
}

function danhsachcaydanhmuc() {
  global $db, $data, $result;

  $sql = "select id, name from pet_". PREFIX ."_work_depart where active = 1 and parentid = 0 order by name asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $danhmuc) {
    $sql = "select id, name from pet_". PREFIX ."_work_depart where active = 1 and parentid = $danhmuc[id] order by name asc";
    $child = $db->all($sql);
    if (count($child)) {
      $danhsach[$thutu]['child'] = $child;
    }
    else $danhsach[$thutu]['child'] = [];
  }

  return $danhsach;
}

function danhsachdanhmuc() {
  global $db, $data, $result;

  $sql = "select id, name, name as route, parentid from pet_". PREFIX ."_work_depart where active = 1 and parentid = 0 order by name asc";
  $danhsach = $db->all($sql);
  $danhsachcon = [];

  foreach ($danhsach as $thutu => $danhmuc) {
    $danhsach[$thutu]["child"] = 0;
    $danhsachcon []= $danhsach[$thutu];
    $sql = "select id, name, parentid from pet_". PREFIX ."_work_depart where active = 1 and parentid = $danhmuc[id] order by name asc";
    $child = $db->all($sql);
    foreach ($child as $tt => $c) {
      $child[$tt]['child'] = 1;
      $child[$tt]['route'] = '⊦ '. $c['name'];
      $danhsachcon []= $child[$tt];
    }
  }

  $sql = "select fullname as name, userid from pet_". PREFIX ."_users where active = 1";
  $danhsachnhanvien = $db->all($sql);

  foreach ($danhsachcon as $thutu => $danhmuc) {
    $sql = "select userid from pet_". PREFIX ."_work_depart_user where departid = $danhmuc[id]";
    $nhanviendanhmuc = $db->arr($sql, "userid");
    $danhsachcon[$thutu]["nhanvien"] = json_decode(json_encode($danhsachnhanvien));
    foreach ($danhsachcon[$thutu]["nhanvien"] as $thutunhanvien => $nhanvien) {
      $danhsachcon[$thutu]["nhanvien"][$thutunhanvien]->value = 0;
      foreach ($nhanviendanhmuc as $userid) {
        if ($nhanvien->userid == $userid) {
          $danhsachcon[$thutu]["nhanvien"][$thutunhanvien]->value = 1;
        }
      }
    }
  }

  return $danhsachcon;
}

function capnhatdanhmuc() {
  global $db, $data, $result;

  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX ."_work_depart (name, parentid) values('$data->name', $data->parentid)";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_work_depart set name = '$data->name' where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_work_depart_user where departid = $data->id";
  $db->query($sql);

  foreach ($data->list as $nhanvien) {
    if ($nhanvien->value) {
      $sql = "insert into pet_". PREFIX ."_work_depart_user (departid, userid) values($data->id, $nhanvien->userid)";
      $db->query($sql);
    }
  }

  $result["status"] = 1;
  $result["danhmuc"] = danhsachdanhmuc();
  return $result;
}

function chuyentrangthai() {
  global $db, $data, $result;

  $arr = array(0 => 1, 2);
  $status = $arr[$data->status];
  $utime = time();
  if ($status == 2) $time = 0;
  else $time = time();
  $sql = "update pet_". PREFIX ."_work set status = $status, updatetime = $utime, time = $time where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['trangthai'] = $status;
  $result['danhsach'] = danhsachcongviec();
  return $result;
}

function laythongtin() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['dulieu'] = laythongtintheoid();
  return $result;
}

function laythongtintheoid() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_work where id = $data->id";
  $congviec = $db->fetch($sql);

  $sql = "select * from pet_". PREFIX ."_users where active = 1";
  $nhanvien = $db->obj($sql, 'userid');

  $sql = "select a.userid, b.fullname from pet_". PREFIX ."_work_follow a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.workid = $data->id";
  $followtext = implode(', ', $db->arr($sql, 'fullname'));
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

  $sql = "select a.userid, b.fullname from pet_". PREFIX ."_work_assign a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.workid = $data->id";
  $assigntext = implode(', ', $db->arr($sql, 'fullname'));
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

  $sql = "select * from pet_". PREFIX ."_work_repeat where workid = $data->id";
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

  $danhmuc = '';
  if ($congviec['departid'] > 0) {
    $sql = "select * from pet_". PREFIX ."_work_depart where id = $congviec[departid]";
    if (!empty($depart = $db->fetch($sql))) {
      $danhmuc = $depart['name'];        
      if ($depart['parentid']) {
        $sql = "select * from pet_". PREFIX ."_work_depart where id = $depart[parentid]";
        if (!empty($depart = $db->fetch($sql))) {
          $danhmuc = $depart['name'] .'/'. $danhmuc;
        }
      }
    }
  }
  $sql = "select fullname from pet_". PREFIX ."_users where userid = $congviec[userid]";
  $usertext = $db->fetch($sql)['fullname'];

  $dulieu = array(
    'id' => $congviec['id'],
    'title' => $congviec['title'],
    'content' => $congviec['content'],
    'departid' => $congviec['departid'],
    'image' => parseimage($congviec['file']),
    'create' => date('Y-m-d', $congviec['createtime']),
    'createtime' => date('d/m/Y', $congviec['createtime']),
    'expiretime' => date('d/m/Y', $congviec['expiretime']),
    'file' => parseimage($congviec['file']),
    'expire' => $congviec['expiretime'] > 0 ? date('Y-m-d', $congviec['expiretime']) : '',
    'follow' => $follow,
    'assign' => $assign,
    'status' => $congviec['status'],
    'repeat' => $repeat,
    'danhmuc' => $danhmuc,
    'usertext' => $usertext,
    'type' => 1,
    'followtext' => $followtext,
    'assigntext' => $assigntext,
  );

  return $dulieu;
}


function laybinhluan() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['binhluan'] = danhsachbinhluan();
  return $result;
}

function xoabinhluan() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_work_comment where id = $data->commentid";
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
  $sql = "select userid, fullname as ten from pet_". PREFIX ."_users where active = 1 order by userid asc";
  $danhsachnhanvien = $db->all($sql);

  foreach ($danhsachnhanvien as $key => $nhanvien) {
    $danhsachnhanvien[$key]['tong'] = 0;
    $danhsachnhanvien[$key]['hoanthanh'] = 0;
    $danhsachnhanvien[$key]['conlai'] = 0;
    $danhsachnhanvien[$key]['hoanthanhquahan'] = 0;
    $danhsachnhanvien[$key]['conlaiquahan'] = 0;
    $sql = "select * from pet_". PREFIX ."_work where ((createtime between $batdau and $ketthuc) or (createtime < $batdau and status = 0)) and userid = $nhanvien[userid]";
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
  if (!count($data->image) && strlen($data->chat)) {
    if (empty($data->commentid)) $sql = "insert into pet_". PREFIX ."_work_comment (workid, userid, comment, file, time) values($data->id  , $userid, '$data->chat', '', $time)";
    else $sql = "update pet_". PREFIX ."_work_comment set comment = '$data->chat', file = '' where id = $data->commentid";
    $data->chat = '';
    $db->query($sql);
  }
  else {
    foreach ($data->image as $image) {
      if (empty($data->commentid)) $sql = "insert into pet_". PREFIX ."_work_comment (workid, userid, comment, file, time) values($data->id  , $userid, '$data->chat', '$image', $time)";
      else $sql = "update pet_". PREFIX ."_work_comment set comment = '$data->chat', file = '$image' where id = $data->commentid";
      $data->chat = '';
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['binhluan'] = danhsachbinhluan();
  return $result;
}

function danhsachbinhluan() {
  global $db, $data, $result;

  $sql = "select a.*, b.fullname from pet_". PREFIX ."_work_comment a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.workid = $data->id order by time asc";
  $danhsachbinhluan = $db->all($sql);
  foreach ($danhsachbinhluan as $key => $binhluan) {
    $danhsachbinhluan[$key]['time'] = date('d/m/Y h:i', $binhluan['time']);
  }

  return $danhsachbinhluan;
}