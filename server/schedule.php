<?php
  function init() {
    global $data, $db, $result;
    $data->time /= 1000;
    
    $result['status'] = 1;
    $result['data'] = getList($data);
    $result['quangay'] = getoverload();
    $result['list'] = getScheduleUser();
    $result['except'] = getExcept();
    return $result;
  }

  function getoverload() {
    global $data, $db;


    $sql = "select b.fullname, a.userid from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where module = 'schedule' and type > 0";
    $danhsachnhanvien = $db->all($sql);
    $danhsach = array();
    $batdau = date("N", $data->time) == 1 ? strtotime(date("Y-m-d", $data->time)) : strtotime(date("Y-m-d", strtotime('last monday', $data->time)));
    $ketthuc = $batdau + 60 * 60 * 24 * 7 - 1;

    $gioihan = array(
      0 => 1, 2, 2, 2, 2, 2, 1, // Bắt đầu bằng chủ nhật, kết thúc bằng thứ 7
    );

    $sql = "select b.userid from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where module = 'manager' and type = 1";
    $danhsachngoaile = $db->arr($sql, 'userid');
    if (empty($danhsachngoaile)) $ngoaile = '0';
    else $ngoaile = implode(', ', $danhsachngoaile);

    $sql = "select * from pet_phc_users";
    $nguoidung = $db->obj($sql, 'userid', 'fullname');

    $sql = "select type, user_id as userid, time, reg_time from pet_phc_row where user_id not in ($ngoaile) and type > 1 and (time between $batdau and $ketthuc) order by reg_time asc";
    $danhsachdangky = $db->all($sql);

    $dulieu = array(
      2 => array( array(), array(), array(), array(), array(), array(), array(), array() ),
      array( array(), array(), array(), array(), array(), array(), array(), array() )
    );

    foreach ($danhsachdangky as $dangky) {
      $dulieu[$dangky['type']][date('w', $dangky['time'])] []= $dangky['userid'];
    }
    $dulieubuoi = array(2 => 'sáng', 'chiều');

    foreach ($dulieu as $buoi => $ngay) {
      foreach ($ngay as $thutungay => $dsdk) {
        $ngaythang = date('d/m/Y', ($thutungay == 0 ? 6 : $thutungay - 1) * 60 * 60 * 24 + $batdau);
        foreach ($dsdk as $thutu => $userid) {
          if ($thutu >= $gioihan[$thutungay]) {
            if ($gioihan[$thutungay] == 1) $nghiphat = 1 + $thutu - $gioihan[$thutungay];
            else $nghiphat = 1;
            $danhsach []= $nguoidung[$userid] ." +$nghiphat nghỉ phạt vào buổi $dulieubuoi[$buoi] ngày $ngaythang";
          }
        }
      }
    }

    return $danhsach;
  }  

  function userreg() {
    global $data, $db, $result;

    $data->time /= 1000;
    $starttime = (date("N", $data->time) == 1 ? strtotime(date("Y-m-d", $data->time)) : strtotime(date("Y-m-d", strtotime('last monday', $data->time))));
    $aday = 60 * 60 * 24;
    
    foreach ($data->list as $v) {
      $time = $starttime + $v->order * $aday;
      insert($v->uid, $time, $v->type, $v->action);
    }
    
    $result['status'] = 1;
    $result['messenger'] = 'Đã đăng ký lịch';
    $result['data'] = getList($data);
    $result['quangay'] = getoverload();
    return $result;
  }

  function managerreg() {
    global $data, $db, $result;
    
    $data->time /= 1000;
    
    $starttime = (date("N", $data->time) == 1 ? strtotime(date("Y-m-d", $data->time)) : strtotime(date("Y-m-d", strtotime('last monday', $data->time))));
    $aday = 60 * 60 * 24;
    
    foreach ($data->list as $v) {
      $time = $starttime + $v->order * $aday;
      insert($v->uid, $time, $data->state * 2 + $v->type, $v->action);
    }
    
    $result['status'] = 1;
    $result['messenger'] = 'Đã đăng ký lịch';
    $result['data'] = getList($data);
    $result['quangay'] = getoverload();
    return $result;
  }

  function getRole() {
    global $db;

    $userid = checkuserid();
    $sql = "select * from pet_phc_user_per where userid = $userid and module = 'schedule'";
    $role = $db->fetch($sql);
    return $role['type'];
  }

  function getList($data) {
    global $db;

    if (getRole() > 1) return managerData();
    return userData();
  }

  function userData() {
    global $db, $data;
    $datearr = array(
      1 => 'Thứ 2',
      'Thứ 3',
      'Thứ 4',
      'Thứ 5',
      'Thứ 6',
      'Thứ 7',
      'CN',
    );
    $checkprevent = array(
      1 => 2, 2, 2, 2, 2, 1, 1    
    );
    $dat = array();
    
    $starttime = date("N", $data->time) == 1 ? strtotime(date("Y-m-d", $data->time)) : strtotime(date("Y-m-d", strtotime('last monday', $data->time)));
    $endtime = $starttime + 60 * 60 * 24 * 7 - 1;
    $time = strtotime(date('Y/m/d')) + (8 - date("N")) * 60 * 60 * 24 - 1;

    $userid = checkuserid();
    $sql = "select * from pet_phc_user_per where module = 'manager' and userid = $userid";
    if (empty($p = $db->fetch($sql))) $p = array('type' => '0');

    for ($i = 0; $i < 7; $i++) { 
      $ct = $starttime + 60 * 60 * 24 * $i;
      $ce = $starttime + 60 * 60 * 24 * ($i + 1) - 1;
      $temp = array(
        'day' => $datearr[date('N', $ct)],
        'date' => date('d/m', $ct),
        'list' => array()
      );
      for ($j = 0; $j < 4; $j++) {
        // lấy danh sách nhân viên đăng ký
        $sql = "select b.fullname as name from pet_phc_row a inner join pet_phc_users b on a.user_id = b.userid where (a.time between $ct and $ce) and type = $j";
        $l = $db->arr($sql, 'name');
        $temp['list'] []= array(
          'name' =>  implode(',', $l),
          'color' =>  'green',
        );

        if ($ct < $time) $temp['list'][$j]['color'] = 'gray'; // nếu thời gian quá tuần, chặn đăng ký
        else if (strpos($temp['list'][$j]['name'], $data->name) !== false) $temp['list'][$j]['color'] = 'orange'; // nếu có tên người dùng, cho hủy đăng ký
        else {
          // kiểm tra nếu nhân viên thuộc diện cá biệt, bỏ qua
          if ($p['type'] == '0' && $j > 1) {
            if ($checkprevent[date('N', $ct)] <= count($l)) $temp['list'][$j]['color'] = 'gray';
          }
        }
      }
      $dat []= $temp;
    }
    return $dat;
  }

  function managerData() {
    global $db, $data;
    $dat = array(
      'list' => array(),
      'day' => array()
    );
    
    $starttime = date("N", $data->time) == 1 ? strtotime(date("Y-m-d", $data->time)) : strtotime(date("Y-m-d", strtotime('last monday', $data->time)));
    $endtime = $starttime + 60 * 60 * 24 * 7 - 1;
    $time = time();

    $sql = "select b.userid, b.fullname as name from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where module = 'schedule' and type > 0";
    $ul = $db->all($sql);

    for ($i = 0; $i < 7; $i++) { 
      $ct = $starttime + 60 * 60 * 24 * $i;
      $dat['day'] []= date('d/m', $ct);
    }

    // thay đổi type
    $x = $data->state * 2;
    $y = $data->state * 2 + 1;

    foreach ($ul as $u) {
      $temp = array(
        'name' => $u['name'],
        'uid' => $u['userid'],
        'list' => array()
      );
      for ($i = 0; $i < 7; $i++) { 
        $temp['list'] []= 'green';
        $temp['list'] []= 'green';
      }
      $sql = "select * from pet_phc_row where user_id = $u[userid] and (type = $x or type = $y) and (time between $starttime and $endtime)";
      $rl = $db->all($sql);

      foreach ($rl as $r) {
        $temp['list'][(date("N", $r['time']) - 1) * 2 + ($r['type'] - 2 * $data->state)] = 'red';
        // echo date("N", $r['time']) . ": ". $r['type']. "<br>";
      }

      $dat['list'] []= $temp;
    }

    return $dat;
  }

  function getScheduleById($id) {
    global $db;
    $sql = 'select * from pet_phc_row where id = '. $id;
    if (!empty($row = $db->fetch($sql))) return $row;
    return array();
  }

  function insert($userid, $time, $type, $action) {
    global $db, $result;
    $start = $time;
    $end = $start + 60 * 60 * 24 - 1;

    if ($action == 'insert') {
      $sql = "select * from pet_phc_row where user_id = $userid and type = $type and (time between $start and $end)";
      $r = $db->fetch($sql);
      if (empty($r)) {
        $sql = 'insert into pet_phc_row (user_id, type, time, reg_time) values('. $userid .', '. $type .', '. $time .', '. time() .')';
        $db->query($sql);
      }
    }
    else {
      $sql = "delete from pet_phc_row where type = $type and user_id = $userid and (time between $start and $end)";
      $db->query($sql);
    }
  }

  function getScheduleUser() {
    global $db;

    $sql = "select b.userid, b.fullname as name from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where module = 'doctor' and type = 1";
    return $db->arr($sql, 'name');
  }

  function getExcept() {
    global $db;

    $sql = "select b.userid, b.fullname as name from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where module = 'except' and type = 1";
    return $db->arr($sql, 'name');
  }

  function xemchotlich() {
    global $db, $data, $result;

    $sql = "select b.fullname, a.userid from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where module = 'schedule' and type > 0";
    $danhsachnhanvien = $db->all($sql);
    $danhsach = array();
    $dulieu = array();
    $batdau = isodatetotime($data->batdau);
    $ketthuc = isodatetotime($data->ketthuc);
    // $tuan = floor(($ketthuc - $batdau + 1) / 7 / 24 / 60 / 60);
    $tuan = floor(((($ketthuc - $batdau) / 24 / 60 / 60) + 1) / 7);

    $gioihan = array(
      0 => 1, 2, 2, 2, 2, 2, 1, // Bắt đầu bằng chủ nhật, kết thúc bằng thứ 7
    );

    $sql = "select b.userid from pet_phc_user_per a inner join pet_phc_users b on a.userid = b.userid where module = 'manager' and type = 1";
    $danhsachngoaile = $db->arr($sql, 'userid');
    if (empty($danhsachngoaile)) $ngoaile = '0';
    else $ngoaile = implode(', ', $danhsachngoaile);

    foreach ($danhsachnhanvien as $nhanvien) {
      $sql = "select id, type, user_id as userid, time, reg_time from pet_phc_row where user_id = $nhanvien[userid] and (time between $batdau and $ketthuc) and type > 1";
      $lichnghi = $db->all($sql);

      $dulieu[$nhanvien['userid']] = array(
        'userid' => $nhanvien['userid'],
        'tennhanvien' => $nhanvien['fullname'],
        'nghi' => 0,
        'nghiphat' => 0,
        'tongnghi' => 0,
        'nghilo' => 0,
      );

      foreach ($lichnghi as $nghi) {
        $dulieu[$nhanvien['userid']]['nghi'] ++;
        $daungay = strtotime(date('Y/m/d', $nghi['time']));
        $cuoingay = $daungay + 24 * 60 * 60 - 1;
        $dangky = $nghi['reg_time'];
        $ngaytrongtuan = date('w', $nghi['time']);
        $gioihanngay = $gioihan[$ngaytrongtuan];
        // tìm trong type cùng ngày (trừ except) có vượt limit không
        $sql = "select user_id as userid, reg_time from pet_phc_row where user_id not in ($ngoaile) and type = $nghi[type] and (time between $daungay and $cuoingay) order by reg_time asc";
        $ngaynghi = $db->all($sql);
        foreach ($ngaynghi as $key => $row) {
          if ($row['userid'] == $nghi['userid'] && ($key >= $gioihanngay)) {
            $dulieu[$nhanvien['userid']]['nghiphat'] ++;
            $dulieu[$nhanvien['userid']]['tongnghi'] ++;
            if ($gioihanngay == 1) {
              // nếu là t7 cn, người nghỉ thứ 2, +1, thứ 3 + 2
              $dulieu[$nhanvien['userid']]['nghiphat'] += $key - $gioihanngay;
              $dulieu[$nhanvien['userid']]['tongnghi'] += $key - $gioihanngay;
            }
          }
        }
      }
    }

    foreach ($dulieu as $thutu => $thongtin) {
      $dulieu[$thutu]['nghi'] = $thongtin['nghi'] / 2;
      $dulieu[$thutu]['nghiphat'] = $thongtin['nghiphat'] / 2;
      $dulieu[$thutu]['tongnghi'] = $dulieu[$thutu]['nghi'] + $dulieu[$thutu]['nghiphat'];
      $nghilo = $dulieu[$thutu]['tongnghi'] - $tuan;
      if ($nghilo < 0) $nghilo = 0;
      $dulieu[$thutu]['nghilo'] = $nghilo;
      $danhsach []= $dulieu[$thutu];
    }
  //   echo json_encode($danhsach);die();

    $result['status'] = 1;
    $result['danhsach'] = $danhsach;
    return $result;
  }
