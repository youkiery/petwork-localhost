<?php
  $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'chotlich'";
  $chotlich = $db->fetch($sql)['value'];

  function init() {
    global $data, $db, $result, $chotlich;

    $result['status'] = 1;
    $result['chedochotlich'] = $chotlich;
    $result['data'] = getList($data);
    $result['list'] = getScheduleUser();
    $result['except'] = getExcept();
    $result['quangay'] = getoverload();
    $result['dachotlich'] = kiemtrachotlich();
    $result['dadangky'] = kiemtradadangky();
    $result['nghichunhat'] = nghichunhat();
    $result['cauhinh'] = cauhinhlich();
    $result['dangky'] = dangkylich();
    $result['thangnay'] = thangnay();
    return $result;
  }

  function thangnay() {
    global $db, $data;

    $thoigian = isodatetotime($data->time);
    $homnay = time();
    $dauthang = strtotime(date('Y/m/1', $thoigian));
    $cuoithang = strtotime(date('Y/m', $thoigian) .'/'. date('t', $thoigian));

    if ($homnay >= $dauthang && $homnay <= $cuoithang) return true;
    return false;
  }

  function cauhinhlich() {
    global $db;

    $cauhinh = ['dangkythem' => '0', 'huydangky' => 0];

    $sql = "select * from pet_". PREFIX ."_config where name = 'dangkythem'";
    if (!empty($dangkythem = $db->fetch($sql))) $cauhinh['dangkythem'] = $dangkythem['value'];

    $sql = "select * from pet_". PREFIX ."_config where name = 'huydangky'";
    if (!empty($huydangky = $db->fetch($sql))) $cauhinh['huydangky'] = $huydangky['value'];

    return $cauhinh;
  }

  function dangkylich() {
    global $db, $data;

    $thoigian = isodatetotime($data->time);
    $dangky = ['dangkythem' => '0', 'huydangky' => 0];

    $userid = checkuserid();
    $dauthang = strtotime(date('Y/m/1', $thoigian));
    $cuoithang = strtotime(date('Y/m', $thoigian) .'/'. date('t', $thoigian));

    $sql = "select * from pet_". PREFIX ."_thaydoilich where (ngaythaydoi between $dauthang and $cuoithang) and loaithaydoi = 0 and userid = $userid group by userid, ngaythaydoi, buoithaydoi, loaithaydoi";
    $dangky['dangkythem'] = $db->count($sql);

    $sql = "select * from pet_". PREFIX ."_thaydoilich where (ngaythaydoi between $dauthang and $cuoithang) and loaithaydoi = 1 and userid = $userid group by userid, ngaythaydoi, buoithaydoi, loaithaydoi";
    $dangky['huydangky'] = $db->count($sql);
    return $dangky;
  }

  function nghichunhat() {
    global $db, $data;

    $userid = checkuserid();
    $thoigian = isodatetotime($data->time);;
    $motngay = 60 * 60 * 24;
    $mottuan = 7 * $motngay;
    $dauthangnay = strtotime(date('Y/m/1', $thoigian));
    $cuoithangnay = strtotime(date('Y/m', $thoigian) .'/'. date('t', $thoigian));
    $ngaychunhat = $dauthangnay + (7 - date('w', $dauthangnay)) * $motngay;

    $sql = "select count(*) as soluong from pet_". PREFIX."_users a inner join pet_". PREFIX."_user_per b on a.userid = b.userid and b.module = 'manager' and b.type > 0";
    $songoaile = $db->fetch($sql)['soluong'];

    $sql = "select count(*) as soluong from pet_". PREFIX."_users a inner join pet_". PREFIX."_user_per b on a.userid = b.userid and b.module = 'schedule' and b.type > 0";
    $sonhanvien = $db->fetch($sql)['soluong'] - $songoaile;

    // danhsach: [ {ngay: {sang: chophep = true, chieu: khongchophep = false}} ]
    // tìm danh sách ngày chủ nhật trong tháng
    // chạy danh sách, tìm kiếm trong khoảng thời gian đó có đăng ký được không
    // trả về danh sách
    $nghichunhat = [];
    while ($ngaychunhat <= $cuoithangnay) {
      $songaynghi = 0;
      $xtra = [];
      $xtra []= "time = ". $ngaychunhat;
      for ($i = 1; $i <= $sonhanvien; $i++) { 
        $xtra []= "time = ". ($ngaychunhat - $i * $mottuan);
        $xtra []= "time = ". ($ngaychunhat + $i * $mottuan);
      }

      $sql = "select * from pet_". PREFIX ."_row where user_id = $userid and (". implode(' or ', $xtra) .") and type >= 2";
      $songaynghi += $db->count($sql);

      $nghichunhat[date('d/m', $ngaychunhat)] = $songaynghi;
      $ngaychunhat += $mottuan;
    }
    return $nghichunhat;
  }

  function kiemtradadangky() {
    global $data, $db, $result, $chotlich;

    $thoigian = isodatetotime($data->time);
    $batdau = strtotime(date('Y/m/1', $thoigian));
    $ketthuc = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
    $userid = checkuserid();
    // từ $time lấy dữ liệu tháng này
    $sql = "select * from pet_". PREFIX ."_row where user_id = $userid and (time between $batdau and $ketthuc) and type > 1";
    $danhsach = $db->all($sql);
  
    return count($danhsach);
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

    if (isset($data->batdau) && !empty($data->batdau)) {
      $batdau = isodatetotime($data->batdau);
      $ketthuc = isodatetotime($data->ketthuc) + 60 * 60 * 24 - 1;
    }
    else {
      $thoigian = isodatetotime($data->time);
      $batdau = strtotime(date('Y/m/1', $thoigian));
      $ketthuc = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
    }
    
    $sql = "select * from pet_". PREFIX ."_luong_chotlich where (batdau between $batdau and $ketthuc) or (ketthuc between $batdau and $ketthuc)";
    if ($chotlich = $db->fetch($sql)) return ['batdau' => date('d/m/Y', $chotlich['batdau']), 'ketthuc' => date('d/m/Y', $chotlich['ketthuc'])];
    return [];
  }

  function kiemtrakhoalich() {
    global $db;

    $sql = "select * from pet_". PREFIX ."_config where name = 'khoathoigian'";
    $khoathoigian = $db->fetch($sql);

    if (empty($khoathoigian['value']) || $khoathoigian['value'] == '0') return true;
    else {
      $sql = "select * from pet_". PREFIX ."_config where name = 'thoigianmo'";
      $thoigianmo = $db->fetch($sql)['value'];
      $sql = "select * from pet_". PREFIX ."_config where name = 'thoigiandong'";
      $thoigiandong = $db->fetch($sql)['value'];
      $baygio = time();
      // nếu baygio nằm giữa thì true
      if ($baygio >= strtotime(date('Y/m/d') . ' ' . date('H:i', $thoigianmo)) && $baygio <= strtotime(date('Y/m/d') . ' ' . date('H:i', $thoigiandong))) return true;
    }
    return false;
  }

  function giokhoalich() {
    global $db;
    $sql = "select * from pet_". PREFIX ."_config where name = 'thoigianmo'";
    $thoigianmo = $db->fetch($sql)['value'];

    $sql = "select * from pet_". PREFIX ."_config where name = 'thoigiandong'";
    $thoigiandong = $db->fetch($sql)['value'];

    return date('H:i', $thoigianmo) . ' - ' . date('H:i', $thoigiandong);
  }

  function themthaydoi($userid, $thoigian, $buoi, $hanhdong) {
    global $db;

    if ($hanhdong == 'insert') $loaithaydoi = 0;
    else $loaithaydoi = 1;
    $homnay = time();
    $sql = "insert into pet_". PREFIX ."_thaydoilich (userid, ngaythaydoi, buoithaydoi, loaithaydoi, thoigian) values($userid, $thoigian, $buoi, $loaithaydoi, $homnay)";
    $db->query($sql);
  }

  function userreg() {
    global $data, $db, $result, $chotlich;

    $starttime = strtotime(date('Y/m/1', isodatetotime($data->time)));
    $aday = 60 * 60 * 24;

    if (!kiemtrakhoalich()) {
      $result['messenger'] = 'Đã quá giờ đăng ký lịch ('. giokhoalich() .')';
    }
    else {
      // kiểm tra nếu thay đổi trong tháng
      $thangnay = thangnay();

      foreach ($data->list as $v) {
        $time = $starttime + $v->order * $aday;
        insert($v->uid, $time, $v->type, $v->action);
        if ($thangnay) themthaydoi($v->uid, $time, $v->type, $v->action);
      }
  
      $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'chotlich'";
      $chotlich = $db->fetch($sql)['value'];
      
      $result['status'] = 1;
      $result['messenger'] = 'Đã đăng ký lịch';
      $result['data'] = getList($data);
      $result['quangay'] = getoverload();
      $result['dadangky'] = kiemtradadangky();
      $result['nghichunhat'] = nghichunhat();
    }
    return $result;
  }

  function managerreg() {
    global $data, $db, $result, $chotlich;
    
    $starttime = strtotime(date('Y/m/1', isodatetotime($data->time)));
    $aday = 60 * 60 * 24;
    
    if (!kiemtrakhoalich()) {
      $result['messenger'] = 'Đã quá giờ đăng ký lịch ('. giokhoalich() .')';
    }
    else {
      foreach ($data->list as $v) {
        $time = $starttime + $v->order * $aday;
        insert($v->uid, $time, $data->state * 2 + $v->type, $v->action);
      }

      $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'chotlich'";
      $chotlich = $db->fetch($sql)['value'];

      $result['status'] = 1;
      $result['messenger'] = 'Đã đăng ký lịch';
      $result['data'] = getList($data);
      $result['quangay'] = getoverload();
      $result['dadangky'] = kiemtradadangky();
      $result['nghichunhat'] = nghichunhat();
    }
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
    global $db, $chotlich;

    if (getRole() > 1) return managerData();
    return userData();
  }

  function userData() {
    global $db, $data, $chotlich;
    $ngaymai = strtotime(date('Y/m/d')) + 60 * 60 * 24 - 1;

    $thoigian = isodatetotime($data->time);
    $starttime = strtotime(date('Y/m/1', $thoigian));
    $endtime = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
    $daysofmonth = date('t', $thoigian);
    $time = strtotime(date('Y/m/t'));
    $homnay = strtotime(date("Y/m/d"));

    $sql = "select b.fullname from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'manager' and type = 1";
    $danhsachngoaile = $db->arr($sql, 'fullname');

    $userid = checkuserid();
    $sql = "select * from pet_". PREFIX ."_user_per where module = 'manager' and userid = $userid";
    if (empty($p = $db->fetch($sql))) $p = array('type' => '0');
    $sql = "select * from pet_". PREFIX ."_config where module = 'config' and name = 'schedule-config'";
    if (empty($cauhinh = $db->fetch($sql))) {
      $cauhinh = json_decode(json_encode([
        ['thu' => '', 'gioihan' => 0, 'phat' => 0],
        ['thu' => 'T2', 'gioihan' => 3, 'phat' => 0],
        ['thu' => 'T3', 'gioihan' => 3, 'phat' => 0],
        ['thu' => 'T4', 'gioihan' => 3, 'phat' => 0],
        ['thu' => 'T5', 'gioihan' => 3, 'phat' => 0],
        ['thu' => 'T6', 'gioihan' => 3, 'phat' => 0],
        ['thu' => 'T7', 'gioihan' => 2, 'phat' => 1],
        ['thu' => 'CN', 'gioihan' => 1, 'phat' => 1],
      ]));
    }
    else $cauhinh = json_decode($cauhinh['value']);

    for ($i = 0; $i < $daysofmonth; $i++) { 
      $ct = $starttime + 60 * 60 * 24 * $i;
      $ce = $ct + 60 * 60 * 24 - 1;
      $temp = array(
        'thaydoi' => $ngaymai < $ct,
        'date' => date('d/m', $ct),
        'day' => $cauhinh[date('N', $ct)]->thu,
        'list' => array()
      );
      for ($j = 0; $j < 4; $j++) {
        // lấy danh sách nhân viên đăng ký
        $sql = "select b.fullname from pet_". PREFIX ."_row a inner join pet_". PREFIX ."_users b on a.user_id = b.userid where (a.time between $ct and $ce) and type = $j";
        $nhanviendangky = $db->arr($sql, 'fullname');
        // foreach ($l as $key => $u) {
        //   $hoten = explode(' ', $u);
        //   $l[$key] = (count($hoten) > 1 ? $hoten[count($hoten) - 2] .' '. $hoten[count($hoten) - 1] : (count($hoten) ? $hoten[count($hoten) - 1] : ''));
        // }
        $temp['list'] []= array(
          'name' =>  implode(', ', $nhanviendangky),
          'color' =>  'green',
        );

        // $homnay = hôm nay
        // $ct = đầu ngày
        // $starttime = đầu tháng
        if ($ct == $homnay && $j > 1) {
          // hôm nay
          // nếu đã đăng ký lịch, kiểm tra thời gian buổi sáng > 7, buổi chiều > 13 thì không cho đăng ký
          $hientai = time() - $homnay;
          $gio = $hientai / 60 / 60;
          $check = false;

          if (strpos($temp['list'][$j]['name'], $data->name) !== false) $check = true;

          if ($j == 2 && $gio < 7) if ($check) $temp['list'][$j]['color'] = 'orange';
          else $temp['list'][$j]['color'] = 'green';
          else $temp['list'][$j]['color'] = 'gray';

          if ($j == 3 && $gio < 13) if ($check) $temp['list'][$j]['color'] = 'orange';
          else $temp['list'][$j]['color'] = 'green';
          else $temp['list'][$j]['color'] = 'gray';
        }
        else if ($ct > $homnay) {
          // đăng ký lịch từ ngày hôm sau
          if (strpos($temp['list'][$j]['name'], $data->name) !== false) {
            $temp['list'][$j]['color'] = 'orange'; // hiển thị đã đăng ký 
          }
        } 
        else {
          if (strpos($temp['list'][$j]['name'], $data->name) !== false) $temp['list'][$j]['color'] = 'blue'; // hiển thị đã đăng ký 
          else $temp['list'][$j]['color'] = 'gray';
        }
      }
      $dat []= $temp;
    }
    return $dat;
  }

  function managerData() {
    global $db, $data, $chotlich;
    
    $thoigian = isodatetotime($data->time);
    if ($chotlich == '1') {
      $batdau = strtotime(date('Y/m/1', $thoigian));
      $ketthuc = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
      $ngaytrongthang = date('t', $thoigian);
      $time = strtotime(date('Y/m/t'));
    }
    else {
      // xem 
      $date = date('N', $thoigian) - 1;
      $batdau = strtotime(date('Y/m/d', $thoigian - $date * 60 * 60 * 24));
      $ketthuc = $batdau + 60 * 60 * 24 - 1;
      $ngaytrongthang = 7;
      $time = strtotime(date('Y/m/d', time() + (7 - date('N')) * 60 * 60 * 24));
    }

    $dulieu = array(
      'ngay' => array(),
      'thu' => array(),
      'buoi' => array(),
      'dangky' => array()
    );
    
    $sql = "select b.userid, b.fullname from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'schedule' and type > 0 and a.userid <> 1";
    $danhsachnhanvien = $db->all($sql);

    $convert = ['', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    for ($i = 1; $i <= $ngaytrongthang; $i++) { 
      $thoigian = $batdau + ($i - 1) * 60 * 60 * 24;
      $dulieu['ngay'] []= date('d/m', $thoigian);
      $dulieu['thu'] []= $convert[date('N', $thoigian)];
      if ($data->state == 0) {
        $dulieu['buoi'] []= 'BV';
        $dulieu['buoi'] []= 'LB';
      }
      else {
        $dulieu['buoi'] []= 'S';
        $dulieu['buoi'] []= 'C';
      }
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
    $batdau = isodatetotime($data->batdau);
    $ketthuc = isodatetotime($data->ketthuc) + 60 * 60 * 24 - 1;
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
            // nếu là t7 cn, người nghỉ thứ 2, +1, thứ 3 + 2
            $dulieu[$nhanvien['userid']]['nghiphat'] += 1 + $thutuphat;
            $dulieu[$nhanvien['userid']]['tongnghi'] += 1 + $thutuphat;
          }
        }
      }
    }

    foreach ($tongngaynghi as $userid => $ngaynghi) {
      $nghilo = ($ngaynghi - 12) / 2;
      $heso = round(1 + $nghilo);
      $dulieu[$userid]['nghiphat2'] = ($nghilo > 0 ? $nghilo * $heso : 0);
    }

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

    $batdau = isodatetotime($data->batdau);
    $ketthuc = isodatetotime($data->ketthuc);

    $sql = "delete from pet_". PREFIX ."_luong_chotlich where (batdau between $batdau and $ketthuc) or (ketthuc between $batdau and $ketthuc)";
    $db->query($sql);
    foreach ($data->danhsach as $thutu => $thongtin) {
      $sql = "insert into pet_". PREFIX ."_luong_chotlich (idnhanvien, nghilo, batdau, ketthuc) values($thongtin->userid, $thongtin->nghilo, $batdau, $ketthuc)";
        $db->query($sql);
    }

    $result['status'] = 1;
    $result['dachotlich'] = kiemtrachotlich();
    $result['messenger'] = 'Đã chốt chốt lịch nghỉ';
    return $result;
  }
  
  function getoverload() {
    global $data, $db, $chotlich;

    $thoigian = isodatetotime($data->time);
    if ($chotlich == '1') {
      $batdau = strtotime(date('Y/m/1', $thoigian));
      $ketthuc = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
      $ngaytrongthang = date('t', $thoigian);
      $time = strtotime(date('Y/m/t'));
    }
    else {
      // xem 
      $date = date('N', $thoigian) - 1;
      $batdau = strtotime(date('Y/m/d', $thoigian - $date * 60 * 60 * 24));
      $ketthuc = $batdau + 60 * 60 * 24 - 1;
      $ngaytrongthang = 7;
      $time = strtotime(date('Y/m/d', time() + (7 - date('N')) * 60 * 60 * 24));
    }

    $sql = "select b.fullname, a.userid from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where module = 'schedule' and type > 0";
    $danhsachnhanvien = $db->all($sql);
    $danhsach = array();
    $danhsachchuasapxep = array();

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
        $ngay = date('d', $thoigian);
        $thutungay = date('N', $thoigian);
        $gioihanngay = $config[$thutungay]->gioihan;
        foreach ($dsdk as $thutu => $userid) {
          if ($thutu >= $gioihanngay) {
            $thutuphat = $thutu - $gioihanngay;
            $nghiphat = (2 + $thutuphat) / 2;
            if (empty($danhsachchuasapxep[$ngay])) $danhsachchuasapxep[$ngay] = [];
            $danhsachchuasapxep[$ngay] []= [
              'nguoidung' => $nguoidung[$userid],
              'nghiphat' => $nghiphat,
              'buoi' => $dulieubuoi[$buoi],
              'ngaythang' => $ngaythang
            ]; 
          }
        }
      }
    }

    ksort($danhsachchuasapxep);

    foreach ($danhsachchuasapxep as $ngay) {
      foreach ($ngay as $dulieu) {
        $danhsach []= $dulieu['nguoidung'] ." +$dulieu[nghiphat] nghỉ phạt vào buổi $dulieu[buoi] ngày $dulieu[ngaythang]";
      }
    }

    // tính tổng đăng ký tháng này
    // từ $time lấy dữ liệu tháng này

    $thoigian = isodatetotime($data->time);
    $batdau = strtotime(date('Y/m/1', $thoigian));
    $ketthuc = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
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
