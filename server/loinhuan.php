<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $userid = checkuserid();
  $result['cauhinh'] = dulieucauhinh();
  $result['luongcoban'] = luongcoban($userid);
  return $result;
}

function khoitaocauhinh() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['cauhinh'] = dulieucauhinh();
  $result['danhsachnhanvien'] = danhsachnhanvien();
  return $result;
}

function danhsachnhanvien() {
  global $db;

  $sql = "select userid, fullname from pet_". PREFIX ."_users where userid in (select userid from pet_". PREFIX ."_user_per where module = 'loinhuan' and type > 0)";
  $danhsach = $db->all($sql);

  $homnay = time();
  foreach ($danhsach as $thutu => $thongtin) {
    $luongnhanvien = thongtinluong($thongtin['userid']);
    $danhsach[$thutu]['luong'] = $luongnhanvien['luong'];
    $danhsach[$thutu]['luongcoban'] = $luongnhanvien['luongcoban'];
    $danhsach[$thutu]['phucap'] = $luongnhanvien['phucap'];
    $danhsach[$thutu]['lenluong'] = $luongnhanvien['lenluong'];
  }
  return $danhsach;
}

function thongtinluong($userid) {
  global $db;

  $homnay = time();
  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $userid";
  if (!empty($nhanvien = $db->fetch($sql))) {
    $dauthanglenluong = strtotime(date('Y/m/1', $nhanvien['lenluong']));
    $sonam = floor(($homnay - $nhanvien['lenluong']) / 365 / 60 / 60 / 24);

    $thongtin['luongcoban'] = $nhanvien['luongcoban'];
    $thongtin['luongcoban2'] = $sonam * 500000 + $nhanvien['luongcoban'];
    $thongtin['luong'] = $thongtin['luongcoban2'] + $nhanvien['phucap'];
    $thongtin['phucap'] = $nhanvien['phucap'];
    $thongtin['lenluong'] = date('d/m/Y', $nhanvien['lenluong']);
  }
  else {
    $thongtin['luong'] = 0;
    $thongtin['luongcoban'] = 0;
    $thongtin['luongcoban2'] = 0;
    $thongtin['phucap'] = 0;
    $thongtin['lenluong'] = 'Chưa lập';
  }
  return $thongtin;
}

function capnhatnhanvien() {
  global $db, $data, $result;

  $data->luongcoban = purenumber($data->luongcoban);
  $data->phucap = purenumber($data->phucap);
  $lenluong = isodatetotime($data->lenluong);
  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $data->userid";
  if (empty($luong = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_luong_nhanvien (userid, tile, luongcoban, lenluong, hopdong, camket, cophan, phucap, phucap2) values($data->userid, 0, $data->luongcoban, $lenluong, 0, 0, 0, $data->phucap, 0)";
  }
  else $sql = "update pet_". PREFIX ."_luong_nhanvien set luongcoban = $data->luongcoban, lenluong = $lenluong, phucap = $data->phucap where userid = $data->userid";
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function khoitaotinhluong() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachtinhluong();
  return $result;
}

function danhsachtinhluong() {
  global $db, $data;

    // kiểm tra lương tháng này
  // hiển thị danh sách tính lương

  $sql = "select userid, fullname as nhanvien from pet_". PREFIX ."_users where active = 1";
  $danhsach = $db->all($sql);

  $dauthang = strtotime(date('Y/m/1'));
  $cuoithang = strtotime(date('Y/m/t')) + 60 * 60 * 24 - 1;
  foreach ($danhsach as $thutu => $thongtin) {
    $sql = "select * from pet_". PREFIX ."_luong_dulieu where idnhanvien = $thongtin[userid] and (thoigian between $dauthang and $cuoithang)";
    if (empty($nhanvien = $db->fetch($sql))) {
      $danhsach[$thutu]['doanhso'] = 0;
      $danhsach[$thutu]['luongcoban'] = 0;
      $danhsach[$thutu]['thuong'] = 0;
      $danhsach[$thutu]['phucap'] = 0;
      $danhsach[$thutu]['nghiphep'] = 0;
      $danhsach[$thutu]['tietkiem'] = 0;
      $danhsach[$thutu]['tongluong'] = 0;
      $danhsach[$thutu]['thucnhan'] = 0;
      $danhsach[$thutu]['cophan'] = 0;
    }
    else {
      $danhsach[$thutu]['luongcoban'] = $nhanvien['luongcoban'];
      $danhsach[$thutu]['phucap'] = $nhanvien['phucap'];
      $danhsach[$thutu]['doanhso'] = $nhanvien['doanhso'];
      $danhsach[$thutu]['nghiphep'] = $nhanvien['nghiphep'];
      $danhsach[$thutu]['thuong'] = $nhanvien['thuong'];
      $danhsach[$thutu]['tietkiem'] = $nhanvien['tietkiem'];
      $danhsach[$thutu]['tongluong'] = $nhanvien['tongluong'];
      $danhsach[$thutu]['thucnhan'] = $nhanvien['thucnhan'];
      $danhsach[$thutu]['cophan'] = $nhanvien['cophan'];
    }
  }
  return $danhsach;
}

function layso($chuoi) {
  return intval(preg_replace('/[^0-9]/', '', $chuoi));
}
function laychu($chuoi) {
  return preg_replace('/[^A-Z]/', '', $chuoi);
}

function docdulieu($file) {
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $doanhthunhanvien = array();
  $excel = ['nhanvien' => 'B12', 'doanhthu' => 'I12'];
  $so = layso($excel['nhanvien']);
  $chunhanvien = laychu(strtoupper($excel['nhanvien']));
  $chudoanhthu = laychu(strtoupper($excel['doanhthu']));
  for ($j = $so; $j <= $highestRow; $j ++) {
    $nhanvien = $sheet->getCell($chunhanvien . $j)->getValue();
    $doanhthu = layso($sheet->getCell($chudoanhthu . $j)->getValue());
    if (!empty($nhanvien)) {
      $doanhthunhanvien[$nhanvien] = $doanhthu;
    }
  }
  return $doanhthunhanvien;
}

function tinhluong() {
  global $data, $db, $result;

  $dir = str_replace('/server', '', ROOTDIR);
  include $dir .'/include/PHPExcel/IOFactory.php';

  $x = array();
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }
  
  $dulieushop = docdulieu($_FILES['shop']['tmp_name']);
  $dulieubenhvien = docdulieu($_FILES['benhvien']['tmp_name']);

  $cauhinh = dulieucauhinh();
  // loinhuanspa
  // loinhuandieutri
  // chietkhauspa
  // chietkhaudieutri

  // tính tổng doanh thu, lương, thưởng, phụ cấp nghỉ phép
  $sql = "select * from pet_". PREFIX ."_users where active = 1";
  $dulieunhanvien = $db->obj($sql, 'fullname', 'userid');

  $danhsach = [];
  foreach ($dulieunhanvien as $ten => $idnhanvien) {
    $danhsach[$idnhanvien] = ['tile' => 0, 'doanhso' => 0];
  }

  foreach ($dulieushop as $nhanvien => $doanhthu) {
    if (!empty($dulieunhanvien[$nhanvien])) {
      $idnhanvien = $dulieunhanvien[$nhanvien];
      if (empty($danhsach[$idnhanvien])) $danhsach[$idnhanvien] = ['tile' => 0, 'doanhso' => 0];
      $danhsach[$idnhanvien]['doanhso'] += intval($doanhthu);
      $danhsach[$idnhanvien]['tile'] += intval(intval($doanhthu) * $cauhinh['loinhuanspa'] * $cauhinh['chietkhauspa'] / 100000);
    } 
  }
  foreach ($dulieubenhvien as $nhanvien => $doanhthu) {
    if (!empty($dulieunhanvien[$nhanvien])) {
      $idnhanvien = $dulieunhanvien[$nhanvien];
      if (empty($danhsach[$idnhanvien])) $danhsach[$idnhanvien] = ['tile' => 0, 'doanhso' => 0];
      $danhsach[$idnhanvien]['doanhso'] += intval($doanhthu);
      $danhsach[$idnhanvien]['tile'] += intval(intval($doanhthu) * $cauhinh['loinhuandieutri'] * $cauhinh['chietkhaudieutri'] / 100000);
    } 
  }

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1'));
  $cuoithang = strtotime(date('Y/m/t')) + 60 * 60 * 24 - 1;
  // tính thưởng
  $sql = "delete from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $db->query($sql);
  $danhsachnhanvien = [];

  foreach ($danhsach as $idnhanvien => $thongtin) {
    $luongnhanvien = thongtinluong($idnhanvien);
    $thuong = $thongtin['tile'] > $luongnhanvien['luongcoban2'] ? $thongtin['tile'] - $luongnhanvien['luongcoban2'] : 0;
    $tongluong = $luongnhanvien['luongcoban2'] + $luongnhanvien['phucap'] + $thuong;
    $tietkiem = intval($tongluong * 15 / 100);
    $thucnhan = $tongluong - $tietkiem;
    $sql = "insert into pet_". PREFIX ."_luong_dulieu (idnhanvien, luongcoban, phucap, doanhso, nghiphep, thuong, tietkiem, tongluong, thucnhan, cophan, thoigian) values($idnhanvien, $luongnhanvien[luongcoban2], $luongnhanvien[phucap], $thongtin[doanhso], 0, $thuong, $tietkiem, $tongluong, $thucnhan, 0, $thoigian)";
    $db->query($sql);
    // $danhsachnhanvien []= [
    //   'idnhanvien' => $idnhanvien,
    //   'luongcoban' => $luongnhanvien['luongcoban2'],
    //   'phucap' => $luongnhanvien['phucap'],
    //   'doanhso' => $thongtin['doanhso'],
    //   'nghiphep' => 0,
    //   'thuong' => $thuong,
    //   'tietkiem' => $tietkiem,
    //   'tongluong' => $tongluong,
    //   'thucnhan' => $tongluong - $tietkiem,
    //   'cophan' => 0,
    // ];
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã tải dữ liệu lên thành công';
  $result['danhsach'] = danhsachtinhluong();
  return $result;
}

function luongcoban($userid) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $userid";
  $luong = $db->fetch($sql);

  if (empty($luong)) return 0;

  $homnay = time();
  $dauthanglenluong = strtotime(date('Y/m/1', $luong['lenluong']));
  $sonam = floor(($homnay - $luong['lenluong']) / 365 / 60 / 60 / 24);

  return $sonam * 500000 + $luong['luongcoban'] + $luong['phucap'];
}

function dulieucauhinh() {
  global $db;
  $cauhinh = [
    'loinhuanspa' => 0,
    'loinhuandieutri' => 0,
    'chietkhauspa' => 0,
    'chietkhaudieutri' => 0,
  ];

  foreach ($cauhinh as $bien => $giatri) {
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$bien'";
    $truongcauhinh = $db->fetch($sql);
    if (!empty($truongcauhinh)) $cauhinh[$bien] = $truongcauhinh['value'];
  }
  return $cauhinh;
}

function luucauhinh() {
  global $data, $db, $result;
  
  foreach ($data->cauhinh as $bien => $giatri) {
    $giatri = floatval($giatri);
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$bien'";
    if (!empty($truongcauhinh = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_config set value = '$giatri' where id = $truongcauhinh[id]";
    else $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('loinhuan', '$bien', '$giatri', 0)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  $result['cauhinh'] = dulieucauhinh();
  return $result;
}
