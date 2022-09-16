<?php
  function init() {
    global $data, $db, $result;
    $data->time /= 1000;

    $result['status'] = 1;
    $result['data'] = getList($data);
    $result['list'] = getScheduleUser();
    $result['except'] = getExcept();
    $result['quangay'] = getoverload();
    $result['dachotlich'] = kiemtrachotlich();
    $result['dadangky'] = kiemtradadangky();
    return $result;
  }

  function kiemtradadangky() {
    global $data, $db, $result;

    // từ $time lấy dữ liệu tháng này
    $userid = checkuserid();
    $batdau = strtotime(date('Y/m/1', $data->time));
    $ketthuc = strtotime(date('Y/m/t', $data->time)) + 60 * 60 * 24 - 1;
    $sql = "select * from pet_". PREFIX ."_row where user_id = $userid and (time between $batdau and $ketthuc) and type > 1";
    $danhsach = $db->all($sql);

    return 8 - count($danhsach);
  }

  function thongkedangky() {
    global $data, $db, $result;

    $start = isodatetotime($data->start);
    $end = isodatetotime($data->end);
    $sql = "select a.time, a.reg_time, a.reg_type, a.type, b.fullname from pet_". PREFIX ."_row_log a inner join pet_". PREFIX ."_users b on a.userid = b.userid where time between $start and $end order by id desc";
    $danhsachdangky = $db->all($sql);
    $danhsach = [];
    $rev = [0 => 'đăng ký', 'hủy đăng ký'];
    $rev2 = [0 => 'trực bệnh viện', 'trực lưu bệnh', 'nghỉ sáng', 'nghỉ chiều'];

    foreach ($danhsachdangky as $key => $dangky) {
      $reg = $rev[$dangky['reg_type']];
      $reg2 = $rev2[$dangky['type']];
      $regtime = date('d/m/Y H:i:s', $dangky['reg_time']);
      $time = date('d/m/Y', $dangky['time']);
      $danhsach []= "$dangky[fullname] $reg $reg2 ngày $time lúc $regtime";
    }

    $result['status'] = 1;
    $result['danhsach'] = $danhsach;
    return $result;
  }

  function kiemtrachotlich() {
    global $data, $db;

    $time = $data->time;
    $ngaybatdau = strtotime(date('Y', $time) .'/'. date('m', $time) .'/1');
    $ngayketthuc = strtotime(date('Y', $time) .'/'. (date('m', $time) + 1) .'/1') - 1;

    $sql = "select thoigian from pet_". PREFIX ."_luong where thoigian between $ngaybatdau and $ngayketthuc";
    $danhsach = $db->arr($sql, 'thoigian');

    foreach ($danhsach as $key => $value) {
      $danhsach[$key] = date('d/m/Y', $value);
    }
    return $danhsach;
  }

  function userreg() {
    global $data, $db, $result;

    $data->time /= 1000;
    $starttime = strtotime(date('Y/m/1', $data->time));
    $aday = 60 * 60 * 24;
    
    foreach ($data->list as $v) {
      $time = $starttime + $v->order * $aday;
      insert($v->uid, $time, $v->type, $v->action);
    }
    
    $result['status'] = 1;
    $result['messenger'] = 'Đã đăng ký lịch';
    $result['data'] = getList($data);
    $result['quangay'] = getoverload();
    $result['dadangky'] = kiemtradadangky();
    return $result;
  }

  function managerreg() {
    global $data, $db, $result;
    
    $data->time /= 1000;
    
    $starttime = strtotime(date('Y/m/1', $data->time));
    $aday = 60 * 60 * 24;
    
    foreach ($data->list as $v) {
      $time = $starttime + $v->order * $aday;
      insert($v->uid, $time, $data->state * 2 + $v->type, $v->action);
    }
    
    $result['status'] = 1;
    $result['messenger'] = 'Đã đăng ký lịch';
    $result['data'] = getList($data);
    $result['quangay'] = getoverload();
    $result['dadangky'] = kiemtradadangky();
    return $result;
  }

  function getRole() {
    global $db;

    $userid = checkuserid();
    $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'schedule'";
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
    
    $starttime = strtotime(date('Y/m/1', $data->time));
    $endtime = strtotime(date('Y/m/t', $data->time)) + 60 * 60 * 24 - 1;
    $daysofmonth = date('t', $data->time);
    $time = strtotime(date('Y/m/t'));

    $userid = checkuserid();
    $sql = "select * from pet_". PREFIX ."_user_per where module = 'manager' and userid = $userid";
    if (empty($p = $db->fetch($sql))) $p = array('type' => '0');
    $checkprevent = [0, 3, 3, 3, 3, 3, 2, 1];
    $convert = ['', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    for ($i = 0; $i < $daysofmonth; $i++) { 
      $ct = $starttime + 60 * 60 * 24 * $i;
      $ce = $ct + 60 * 60 * 24 - 1;
      $temp = array(
        'date' => date('d/m', $ct),
        'day' => $convert[date('N', $ct)],
        'list' => array()
      );
      for ($j = 0; $j < 4; $j++) {
        // lấy danh sách nhân viên đăng ký
        $sql = "select b.fullname from pet_". PREFIX ."_row a inner join pet_". PREFIX ."_users b on a.user_id = b.userid where (a.time between $ct and $ce) and type = $j";
        $l = $db->arr($sql, 'fullname');
        // foreach ($l as $key => $u) {
        //   $hoten = explode(' ', $u);
        //   $l[$key] = (count($hoten) > 1 ? $hoten[count($hoten) - 2] .' '. $hoten[count($hoten) - 1] : (count($hoten) ? $hoten[count($hoten) - 1] : ''));
        // }
        $temp['list'] []= array(
          'name' =>  implode(', ', $l),
          'color' =>  'green',
        );

        if ($ct <= $time) {
          if ($ct >= $starttime && $j < 2) $temp['list'][$j]['color'] = 'green';
          else $temp['list'][$j]['color'] = 'gray'; // nếu thời gian quá tuần, chặn đăng ký
          if (strpos($temp['list'][$j]['name'], $data->name) !== false) {
            if ($ct >= $starttime && $j < 2) $temp['list'][$j]['color'] = 'orange'; // hiển thị đã đăng ký 
            else $temp['list'][$j]['color'] = 'cyan'; // hiển thị đã đăng ký
          }
        } 
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
    $dulieu = array(
      'ngay' => array(),
      'thu' => array(),
      'buoi' => array(),
      'dangky' => array()
    );
    
    $batdau = strtotime(date('Y/m/1', $data->time));
    $ketthuc = strtotime(date('Y/m/t', $data->time)) + 60 * 60 * 24 - 1;
    $ngaytrongthang = date('t', $data->time);
    $time = strtotime(date('Y/m/t'));

    $sql = "select b.userid, b.fullname from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'schedule' and type > 0 and a.userid <> 1";
    $danhsachnhanvien = $db->all($sql);

    $convert = ['', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    for ($i = 1; $i <= $ngaytrongthang; $i++) { 
      $thoigian = $batdau + ($i - 1) * 60 * 60 * 24;
      $dulieu['ngay'] []= date('d/m', $thoigian);
      $dulieu['thu'] []= $convert[date('N', $thoigian)];
      $dulieu['buoi'] []= 'S';
      $dulieu['buoi'] []= 'C';
    }

    // thay đổi type
    $x = $data->state * 2;
    $y = $data->state * 2 + 1;

    foreach ($danhsachnhanvien as $nhanvien) {
      $hoten = explode(' ', $nhanvien['fullname']);
      $tam = array(
        'nhanvien' => count($hoten) ? $hoten[count($hoten) - 1] : '',
        'uid' => $nhanvien['userid'],
        'danhsach' => array()
      );
      for ($i = 1; $i <= $ngaytrongthang; $i++) { 
        $tam['danhsach'] []= 'green';
        $tam['danhsach'] []= 'green';
      }

      $sql = "select * from pet_". PREFIX ."_row where user_id = $nhanvien[userid] and (type = $x or type = $y) and (time between $batdau and $ketthuc)";
      $danhsachdangky = $db->all($sql);

      foreach ($danhsachdangky as $dangky) {
        $tam['danhsach'][(date("d", $dangky['time']) - 1) * 2 + ($dangky['type'] - 2 * $data->state)] = 'red';
      }

      $dulieu['dangky'] []= $tam;
    }

    return $dulieu;
  }

  function getScheduleById($id) {
    global $db;
    $sql = "select * from pet_". PREFIX ."_row where id = $id";
    if (!empty($row = $db->fetch($sql))) return $row;
    return array();
  }

  function insert($userid, $time, $type, $action) {
    global $db, $result;
    $start = $time;
    $end = $start + 60 * 60 * 24 - 1;
    $ctime = time();

    if ($action == 'insert') {
      $sql = "select * from pet_". PREFIX ."_row where user_id = $userid and type = $type and (time between $start and $end)";
      $r = $db->fetch($sql);
      if (empty($r)) {
        $sql = "insert into pet_". PREFIX ."_row (user_id, type, time, reg_time) values($userid, $type, $time, $ctime)";
        $db->query($sql);
      }
      $sql = "insert into pet_". PREFIX ."_row_log (userid, type, time, reg_time, reg_type) values($userid, $type, $time, $ctime, 0)";
      $db->query($sql);
    }
    else {
      $sql = "delete from pet_". PREFIX ."_row where type = $type and user_id = $userid and (time between $start and $end)";
      $db->query($sql);
      $sql = "insert into pet_". PREFIX ."_row_log (userid, type, time, reg_time, reg_type) values($userid, $type, $time, $ctime, 1)";
      $db->query($sql);
    }
  }

  function getScheduleUser() {
    global $db;

    $sql = "select b.userid, b.fullname as name from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'doctor' and type = 1";
    return $db->arr($sql, 'name');
  }

  function getExcept() {
    global $db;

    $sql = "select b.userid, b.fullname as name from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'except' and type = 1";
    return $db->arr($sql, 'name');
  }

  function xemchotlich() {
    global $db, $data, $result;

    $sql = "select b.fullname, a.userid from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'schedule' and type > 0 and a.userid <> 1";
    $danhsachnhanvien = $db->all($sql);
    $danhsach = array();
    $dulieu = array();
    $thoigian = $data->time / 1000;
    $batdau = strtotime(date('Y/m/1', $thoigian));
    $ketthuc = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
    $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'schedule-config'";
    $config = $db->fetch($sql);
    $config = json_decode($config['value']);  

    $sql = "select b.userid from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'manager' and type = 1";
    $danhsachngoaile = $db->arr($sql, 'userid');
    if (empty($danhsachngoaile)) $ngoaile = '0';
    else $ngoaile = implode(', ', $danhsachngoaile);
    $tongngaynghi = [];

    foreach ($danhsachnhanvien as $nhanvien) {
      $sql = "select id, type, user_id as userid, time, reg_time from pet_". PREFIX ."_row where user_id = $nhanvien[userid] and (time between $batdau and $ketthuc) and type > 1";
      $lichnghi = $db->all($sql);
      $tongngaynghi[$nhanvien['userid']] = 0;

      $dulieu[$nhanvien['userid']] = array(
        'userid' => $nhanvien['userid'],
        'tennhanvien' => $nhanvien['fullname'],
        'nghi' => 0,
        'nghiphat' => 0,
        'nghiphat2' => 0,
        'tongnghi' => 0,
        'nghilo' => 0,
      );

      foreach ($lichnghi as $nghi) {
        $tongngaynghi[$nhanvien['userid']] ++;
        $dulieu[$nhanvien['userid']]['nghi'] ++;
        $daungay = strtotime(date('Y/m/d', $nghi['time']));
        $cuoingay = $daungay + 24 * 60 * 60 - 1;
        $dangky = $nghi['reg_time'];
        $ngaytrongtuan = date('N', $nghi['time']);
        $gioihanngay = $config[$ngaytrongtuan]->gioihan;
        // tìm trong type cùng ngày (trừ except) có vượt limit không
        $sql = "select user_id as userid, reg_time from pet_". PREFIX ."_row where user_id not in ($ngoaile) and type = $nghi[type] and (time between $daungay and $cuoingay) order by reg_time asc";
        $ngaynghi = $db->all($sql);
        foreach ($ngaynghi as $thutungay => $row) {
          if ($row['userid'] == $nghi['userid'] && ($thutungay >= $gioihanngay)) {
            $dulieu[$nhanvien['userid']]['nghiphat'] ++;
            $dulieu[$nhanvien['userid']]['tongnghi'] ++;
            $thutuphat = $thutungay - $gioihanngay;
            if ($config[$ngaytrongtuan]->phat) {
              // nếu là t7 cn, người nghỉ thứ 2, +1, thứ 3 + 2
              $dulieu[$nhanvien['userid']]['nghiphat'] += $thutuphat;
              $dulieu[$nhanvien['userid']]['tongnghi'] += $thutuphat;
            }
          }
        }
      }
    }

    // foreach ($tongngaynghi as $userid => $ngaynghi) {
    //   $dulieu[$userid]['nghiphat2'] = ($ngaynghi > 12 ? ($ngaynghi - 12) / 2 : 0);
    // }

    foreach ($dulieu as $thutu => $thongtin) {
      $dulieu[$thutu]['nghi'] = $thongtin['nghi'] / 2;
      $dulieu[$thutu]['nghiphat'] = $thongtin['nghiphat'] / 2;
      $dulieu[$thutu]['tongnghi'] = $dulieu[$thutu]['nghi'] + $dulieu[$thutu]['nghiphat'] + $dulieu[$thutu]['nghiphat2'];
      $nghilo = $dulieu[$thutu]['tongnghi'] - 4;
      if ($nghilo < 0) $nghilo = 0;
      $dulieu[$thutu]['nghilo'] = $nghilo;
      $danhsach []= $dulieu[$thutu];
    }

    $result['status'] = 1;
    $result['danhsach'] = $danhsach;
    return $result;
  }

  function chotlich() {
    global $db, $data, $result;

    $sql = "select b.fullname, a.userid from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'schedule' and type > 0 and a.userid <> 1";
    $thoigian = $data->time / 1000;

    $danhsachnhanvien = $db->all($sql);
    $danhsach = array();
    $dulieu = array();
    $batdau = strtotime(date('Y/m/1', $thoigian));
    $ketthuc = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
    $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'schedule-config'";
    $config = $db->fetch($sql);
    $config = json_decode($config['value']);  

    $sql = "select b.userid from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'manager' and type = 1";
    $danhsachngoaile = $db->arr($sql, 'userid');
    if (empty($danhsachngoaile)) $ngoaile = '0';
    else $ngoaile = implode(', ', $danhsachngoaile);
    $tongngaynghi = [];

    $danhsachid = array();
    foreach ($danhsachnhanvien as $nhanvien) {
      $danhsachid []= $nhanvien['userid'];
      $sql = "select id, type, user_id as userid, time, reg_time from pet_". PREFIX ."_row where user_id = $nhanvien[userid] and (time between $batdau and $ketthuc) and type > 1";
      $lichnghi = $db->all($sql);
      $tongngaynghi[$nhanvien['userid']] = 0;

      $dulieu[$nhanvien['userid']] = array(
        'userid' => $nhanvien['userid'],
        'tennhanvien' => $nhanvien['fullname'],
        'nghi' => 0,
        'nghiphat' => 0,
        'nghiphat2' => 0,
        'tongnghi' => 0,
        'nghilo' => 0,
      );

      foreach ($lichnghi as $nghi) {
        $tongngaynghi[$nhanvien['userid']] ++;
        $dulieu[$nhanvien['userid']]['nghi'] ++;
        $daungay = strtotime(date('Y/m/d', $nghi['time']));
        $cuoingay = $daungay + 24 * 60 * 60 - 1;
        $dangky = $nghi['reg_time'];
        $ngaytrongtuan = date('N', $nghi['time']);
        $gioihanngay = $config[$ngaytrongtuan]->gioihan;
        // tìm trong type cùng ngày (trừ except) có vượt limit không
        $sql = "select user_id as userid, reg_time from pet_". PREFIX ."_row where user_id not in ($ngoaile) and type = $nghi[type] and (time between $daungay and $cuoingay) order by reg_time asc";
        $ngaynghi = $db->all($sql);
        foreach ($ngaynghi as $thutungay => $row) {
          if ($row['userid'] == $nghi['userid'] && ($thutungay >= $gioihanngay)) {
            $dulieu[$nhanvien['userid']]['nghiphat'] ++;
            $dulieu[$nhanvien['userid']]['tongnghi'] ++;
            $thutuphat = $thutungay - $gioihanngay;
            if ($config[$ngaytrongtuan]->phat) {
              // nếu là t7 cn, người nghỉ thứ 2, +1, thứ 3 + 2
              $dulieu[$nhanvien['userid']]['nghiphat'] += $thutuphat;
              $dulieu[$nhanvien['userid']]['tongnghi'] += $thutuphat;
            }
          }
        }
      }
    }

    // foreach ($tongngaynghi as $userid => $ngaynghi) {
    //   $dulieu[$userid]['nghiphat2'] = ($ngaynghi > 12 ? ($ngaynghi - 12) / 2 : 0);
    // }

    foreach ($dulieu as $thutu => $thongtin) {
      $dulieu[$thutu]['nghi'] = $thongtin['nghi'] / 2;
      $dulieu[$thutu]['nghiphat'] = $thongtin['nghiphat'] / 2;
      $dulieu[$thutu]['tongnghi'] = $dulieu[$thutu]['nghi'] + $dulieu[$thutu]['nghiphat'] + $dulieu[$thutu]['nghiphat2'];
      $nghilo = $dulieu[$thutu]['tongnghi'] - 4;
      if ($nghilo < 0) $nghilo = 0;
      $dulieu[$thutu]['nghilo'] = $nghilo;
      $danhsach []= $dulieu[$thutu];
    }

    $tongnhanvien = count($danhsach);

    $sql = "insert into pet_". PREFIX ."_luong (doanhthu, loinhuan, tienchi, luongnhanvien, lairong, thoigian, trangthai) values(0, 0, 0, $tongnhanvien, 0, $thoigian, 0)";
    $id = $db->insertid($sql);

    $sql = "select value as loaichi, alt as tienchi from pet_". PREFIX ."_config where module = 'luong' and name = 'tienchi'";
    $danhmucchi = $db->all($sql);
    
    foreach ($danhmucchi as $tienchi) {
      $sql = "insert into pet_". PREFIX ."_luong_tienchi (luongid, mucchi, tienchi) values($id, '$tienchi[loaichi]', $tienchi[tienchi])";
      $db->query($sql);
    }

    $sql = "select userid, 0 as nghilo from pet_". PREFIX ."_luong_nhanvien where userid not in (". implode(', ', $danhsachid) .")";
    if (count($nhanvien = $db->all($sql))) {
      $danhsach = array_merge($danhsach, $nhanvien);
    }

    foreach ($danhsach as $nhanvien) {
      $sql = "insert into pet_". PREFIX ."_luong_tra (userid, luongid, doanhthu, loinhuan, luong, tile, tilethuong, thuong, luongphucap, phucap2, phucap, ngaynghi, nghiphep, tietkiem, nhantietkiem, tilecophan, cophan, tong, thucnhan) values($nhanvien[userid], $id, 0, 0, 0, 0, 0, 0, 0, 0, 0, $nhanvien[nghilo], 0, 0, 0, 0, 0, 0, 0)";
      $db->query($sql);
    }

    $result['status'] = 1;
    $result['dachotlich'] = kiemtrachotlich();
    $result['messenger'] = 'Đã chốt chốt lịch nghỉ';
    return $result;
  }
  
  function getoverload() {
    global $data, $db;

    $sql = "select b.fullname, a.userid from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'schedule' and type > 0";
    $danhsachnhanvien = $db->all($sql);
    $danhsach = array();

    $batdau = strtotime(date('Y/m/1', $data->time));
    $ketthuc = strtotime(date('Y/m/t', $data->time)) + 60 * 60 * 24 - 1;
    $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'schedule-config'";
    $config = $db->fetch($sql);
    $config = json_decode($config['value']);  

    $sql = "select b.userid from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'manager' and type = 1";
    $danhsachngoaile = $db->arr($sql, 'userid');
    if (empty($danhsachngoaile)) $ngoaile = '0';
    else $ngoaile = implode(', ', $danhsachngoaile);

    $sql = "select * from pet_". PREFIX ."_users";
    $nguoidung = $db->obj($sql, 'userid', 'fullname');

    $sql = "select type, user_id as userid, time, reg_time from pet_". PREFIX ."_row where user_id not in ($ngoaile) and type > 1 and (time between $batdau and $ketthuc) order by reg_time asc";
    $danhsachdangky = $db->all($sql);

    $dulieu = [[], []];
    foreach ($danhsachdangky as $dangky) {
      if (empty($dulieu[$dangky['type'] - 2][date('d', $dangky['time'])])) $dulieu[$dangky['type'] - 2][date('d', $dangky['time'])] = [];
      $dulieu[$dangky['type'] - 2][date('d', $dangky['time'])] []= $dangky['userid'];
    }
    $dulieubuoi = array('sáng', 'chiều');

    foreach ($dulieu as $buoi => $ngay) {
      foreach ($ngay as $songay => $dsdk) {
        $thoigian = ($songay - 1) * 60 * 60 * 24 + $batdau;
        $ngaythang = date('d/m/Y', $thoigian);
        $thutungay = date('N', $thoigian);
        $gioihanngay = $config[$thutungay]->gioihan;
        foreach ($dsdk as $thutu => $userid) {
          if ($thutu >= $gioihanngay) {
            if ($config[$thutungay]->phat == 1) {
              $thutuphat = $thutu - $gioihanngay;
              $nghiphat = (1 + $thutuphat) / 2;
            }
            else $nghiphat = 0.5;
            $danhsach []= $nguoidung[$userid] ." +$nghiphat nghỉ phạt vào buổi $dulieubuoi[$buoi] ngày $ngaythang";
          }
        }
      }
    }

    // tính tổng đăng ký tháng này
    // từ $time lấy dữ liệu tháng này
    $batdau = strtotime(date('Y/m/1', $data->time));
    $ketthuc = strtotime(date('Y/m/t', $data->time)) + 60 * 60 * 24 - 1;
    $sql = "select * from pet_". PREFIX ."_row where (time between $batdau and $ketthuc) and type > 1";
    $danhsachdangky = $db->all($sql);
    $dulieu = [];
    foreach ($danhsachdangky as $key => $dangky) {
      if (empty($dulieu[$dangky['user_id']])) $dulieu[$dangky['user_id']] = 0;
      $dulieu[$dangky['user_id']] ++;
    }
    foreach ($dulieu as $userid => $ngaynghi) {
      if ($ngaynghi > 12) {
        $quangay = ($ngaynghi - 12) / 2;
        $sql = "select fullname from pet_". PREFIX ."_users where userid = $userid";
        $nhanvien = $db->fetch($sql);
        $danhsach []= "$nhanvien[fullname] nghỉ quá $quangay ngày"; 
      }
    }

    return $danhsach;
  }  
