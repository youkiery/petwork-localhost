<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $userid = checkuserid();
  $result['tilespa'] = laytilespa($userid);
  $result['tilebanhang'] = laytilebanhang2($userid);
  $result['heluong'] = layheluong($userid);
  $result['dulieunhanvien'] = dulieunhanvien($userid);
  $result['luongcoban'] = luongcoban($userid);
  $result['phucap'] = phucap($userid);
  $result['luong'] = luongthangnay($userid);
  return $result;
}

function khoitaonhaplieu() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = laydanhsachnhaplieu();
  return $result;
}

function capnhatnhaplieu() {
  global $data, $db, $result;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  foreach ($data->danhsachnhanvien as $thutu => $nhanvien) {
    $tietkiem = str_replace(',', '', $nhanvien->tietkiem);
    $cophan = str_replace(',', '', $nhanvien->cophan);
    $sql = "select * from pet_". PREFIX ."_luong_dulieu where (thoigian between $dauthang and $cuoithang) and idnhanvien = $nhanvien->idnhanvien";
    if (empty($dulieu = $db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_luong_dulieu (idnhanvien, luongcoban, phucap, doanhsobanhang, doanhsospa, nghiphep, thuong, tietkiem, tongluong, thucnhan, cophan, thoigian) values($nhanvien->idnhanvien, 0, 0, 0, 0, 0, 0, $tietkiem, 0, 0, $cophan, $thoigian)";
    else $sql = "update pet_". PREFIX ."_luong_dulieu set tietkiem = $tietkiem, cophan = $cophan where id = $dulieu[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = laydanhsachnhaplieu();
  $result['messenger'] = "Đã lưu dữ liệu";
  return $result;
}

function laydanhsachnhaplieu() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  // tìm kiếm trong csdl xem tháng đó đã chốt chưa, nếu chưa thì lấy danh sách trống để nhập
  $sql = "select a.idnhanvien, a.tietkiem, a.cophan, b.fullname as ten from pet_". PREFIX ."_luong_dulieu a inner join pet_". PREFIX ."_users b on a.idnhanvien = b.userid where a.thoigian between $dauthang and $cuoithang order by b.fullname asc";
  $danhsach = $db->all($sql);
  if (!count($danhsach)) {
    $sql = "select userid as idnhanvien, fullname as ten, 0 as tietkiem, 0 as cophan from pet_". PREFIX ."_users where userid in (select userid from pet_". PREFIX ."_user_per where module = 'loinhuan' and type > 0)";
    $danhsach = $db->all($sql);
  }
  foreach ($danhsach as $thutu => $thongtin) {
    $danhsach[$thutu]['tietkiem'] = number_format($thongtin['tietkiem']);
    $danhsach[$thutu]['cophan'] = number_format($thongtin['cophan']);
  } 
  return $danhsach;
}

function layheluong($idnhanvien) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $idnhanvien";
  $luong = $db->fetch($sql);
  if (empty($luong)) return 0;
  return $luong['heluong'];
}

function luongthangnay($userid) {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  $dulieu = [];

  $sql = "select * from pet_". PREFIX ."_luong_dulieu where idnhanvien = $userid and (thoigian between $dauthang and $cuoithang)";
  if (empty($nhanvien = $db->fetch($sql))) {
    $dulieu['doanhsobanhang'] = 0;
    $dulieu['doanhsospa'] = 0;
    $dulieu['doanhso'] = 0;
    $dulieu['luongcoban'] = 0;
    $dulieu['thuong'] = 0;
    $dulieu['phucap'] = 0;
    $dulieu['nghiphep'] = 0;
    $dulieu['tietkiem'] = 0;
    $dulieu['tongluong'] = 0;
    $dulieu['thucnhan'] = 0;
    $dulieu['cophan'] = 0;
    $dulieu['thoigian'] = 'Tháng này chưa có dữ liệu';
  }
  else {
    $dulieu['luongcoban'] = $nhanvien['luongcoban'];
    $dulieu['phucap'] = $nhanvien['phucap'];
    $dulieu['doanhsobanhang'] = $nhanvien['doanhsobanhang'];
    $dulieu['doanhsospa'] = $nhanvien['doanhsospa'];
    $dulieu['doanhso'] = $nhanvien['doanhsobanhang'] + $nhanvien['doanhsospa'];
    $dulieu['nghiphep'] = $nhanvien['nghiphep'];
    $dulieu['thuong'] = $nhanvien['thuong'];
    $dulieu['tietkiem'] = $nhanvien['tietkiem'];
    $dulieu['tongluong'] = $nhanvien['tongluong'];
    $dulieu['thucnhan'] = $nhanvien['thucnhan'];
    $dulieu['cophan'] = $nhanvien['cophan'];
    $dulieu['thoigian'] = date('d/m/Y H:i:s', $nhanvien['thoigianthem']);
  }
  return $dulieu;
}

function dulieuluong($idnhanvien, $thoigian) {
  global $db;
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_luong_dulieu where (thoigian between $dauthang and $cuoithang) and idnhanvien = $idnhanvien";
  $luong = $db->fetch($sql);
  if (empty($luong)) {
    $luong = [
      'doanhsospa' => 0,
      'doanhsobanhang' => 0,
      'luongcoban' => 0,
      'thuong' => 0,
      'phucap' => 0,
      'nghiphep' => 0,
      'tietkiem' => 0,
      'cophan' => 0,
      'tongluong' => 0,
      'thucnhan' => 0,
    ];
  }
  return $luong;
}

function dulieunhanvien($userid) {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $sql = "select * from pet_". PREFIX ."_user_per where module = 'loinhuan' and type = 2 and userid = $userid";
  if (empty($quyen = $db->fetch($sql)) && $userid != 1) {
    $sql = "select * from pet_". PREFIX ."_users where userid = $userid";
  }
  else {
    $sql = "select * from pet_". PREFIX ."_users where userid in (select userid from pet_". PREFIX ."_user_per where module = 'loinhuan' and type > 0)";
  }
  $danhsachnhanvien = $db->all($sql);

  foreach ($danhsachnhanvien as $nhanvien) {
    $userid = $nhanvien['userid'];
    $tongtietkiem = 0;
    $tongcophan = 0;
    $danhsachtamtietkiem = [];
    $danhsachtamcophan = [];
    $danhsachtietkiem = [];
    $danhsachcophan = [];
  
    // nếu tháng này <= 2 thì lấy từ tháng 2 năm nay đến tháng 3 năm ngoái
    // nếu tháng này > 2 thì lấy từ tháng 2 đến tháng này
    $thangnay = date('m', $thoigian);
    if ($thangnay <= 2) $tiep = strtotime(date((date('Y', $thoigian) - 1) .'/3/1'));
    else $tiep = strtotime(date(date('Y', $thoigian) .'/3/1'));
  
    for ($i = 0; $i < 12; $i++) { 
      $luong = dulieuluong($userid, $tiep);
      $tongtietkiem += $luong['tietkiem'];
      $tongcophan += $luong['cophan'];
      $danhsachtamtietkiem[date('m', $tiep)] = number_format($luong['tietkiem']);
      $danhsachtamcophan[date('m', $tiep)] = number_format($luong['cophan']);
      $tiep = strtotime('first day of next month', $tiep);
    }
  
    ksort($danhsachtamcophan);
    ksort($danhsachtamtietkiem);
  
    foreach ($danhsachtamcophan as $thutu => $giatri) {
      $danhsachtietkiem[] = $danhsachtamtietkiem[$thutu];
      $danhsachcophan[] = $danhsachtamcophan[$thutu];
    }
  
    $luong = dulieuluong($userid, $thoigian);
    $dulieu = [
      'ngaybatdau' => date('01/m/Y', $thoigian),
      'ngayketthuc' => date('t/m/Y', $thoigian),
      'hoten' => $nhanvien['fullname'],
      'doanhthu' => number_format($luong['doanhsospa'] + $luong['doanhsobanhang']),
      'luongcoban' => number_format($luong['luongcoban']),
      'thuong' => number_format($luong['thuong']),
      'phucap' => number_format($luong['phucap']),
      'nghiphep' => number_format($luong['nghiphep']),
      'tietkiem' => number_format($luong['tietkiem']),
      'tongluong' => number_format($luong['tongluong']),
      'thucnhan' => number_format($luong['thucnhan']),
      'tongtietkiem' => number_format($tongtietkiem),
      'danhsachtietkiem' => $danhsachtietkiem,
      'tongcophan' => number_format($tongcophan),
      'danhsachcophan' => $danhsachcophan,
    ];
    $danhsach[]= $dulieu;
  }

  return $danhsach;
}

function khoitaocauhinh() {
  global $data, $db, $result;
  
  $userid = checkuserid();
  $result['status'] = 1;
  $result['tilespa'] = laytilespa($userid);
  $result['tilebanhang'] = laytilebanhang($userid);
  $result['danhsachnhanvien'] = danhsachnhanvien();
  return $result;
}

function laytilebanhang($idnhanvien) {
  global $data, $db;

  $sql = "select * from pet_". PREFIX ."_taichinh_tilebanhang where idnhanvien = $idnhanvien order by khoang asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $value) {
    $danhsach[$thutu]['khoang'] = number_format($value['khoang']);
    $danhsach[$thutu]['tien'] = number_format($value['tien']);
  }
  return $danhsach;
}

function laytilebanhang2($idnhanvien) {
  global $data, $db;

  $sql = "select * from pet_". PREFIX ."_taichinh_tilebanhang where idnhanvien = $idnhanvien order by khoang asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $value) {
    $danhsach[$thutu]['khoang'] = $value['khoang'];
    $danhsach[$thutu]['tien'] = $value['tien'];
  }
  return $danhsach;
}

function laytilespa($idnhanvien) {
  global $db;
  $cauhinh = [
    'loinhuanbanhang' => 0,
    'loinhuanspa' => 0,
    'chietkhaubanhang' => 0,
    'chietkhauspa' => 0,
  ];

  $sql = "select * from pet_". PREFIX ."_taichinh_tilespa where idnhanvien = $idnhanvien";
  if (!empty($tilespa = $db->fetch($sql))) {
    $cauhinh['loinhuanbanhang'] = number_format($tilespa['loinhuanbanhang']);
    $cauhinh['loinhuanspa'] = number_format($tilespa['loinhuanspa']);
    $cauhinh['chietkhaubanhang'] = number_format($tilespa['chietkhaubanhang']);
    $cauhinh['chietkhauspa'] = number_format($tilespa['chietkhauspa']);
  }
  return $cauhinh;
}

function luutile() {
  global $data, $db, $result;

  $danhsachtile = $data->tile;
  foreach ($danhsachtile as $thutu => $tile) {
    $khoang = purenumber($tile->khoang);
    $tien = purenumber($tile->tien);
    if ($tile->id) $sql = "update pet_". PREFIX ."_taichinh_tilebanhang set khoang = $khoang, tien = $tien where id = $tile->id";
    else $sql = "insert into pet_". PREFIX ."_taichinh_tilebanhang (khoang, tien, idnhanvien) values($khoang, $tien, 0)";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu tỉ lệ';
  $result['tile'] = laytilebanhang(0);
  return $result;
}

function xoatile() {
  global $data, $db, $result;
 
  $sql = "delete from pet_". PREFIX ."_taichinh_tilebanhang where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['tile'] = laytilebanhang(0);
  return $result;
}

function thaydoigiatri() {
  global $db, $data, $result;

  $daogiatri = intval(!$data->giatri);
  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $data->userid";
  if (empty($luong = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_luong_nhanvien (userid, luongcoban, kyhopdong, lenluong, phucap, tiletietkiem, tamnghi, heluong) values($data->userid, 0, 0, 0, 0, 0, 0, 0)";
    $db->query($sql);
  }

  $sql = "update pet_". PREFIX ."_luong_nhanvien set $data->bien = $daogiatri where userid = $data->userid";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsachnhanvien'] = danhsachnhanvien();
  return $result;
}

function danhsachnhanvien() {
  global $db;

  $sql = "select userid, fullname from pet_". PREFIX ."_users where userid in (select userid from pet_". PREFIX ."_user_per where module = 'loinhuan' and type > 0)";
  $danhsach = $db->all($sql);

  $homnay = time();
  foreach ($danhsach as $thutu => $thongtin) {
    $luongnhanvien = thongtinluong($thongtin['userid'], $homnay);
    $danhsach[$thutu]['luong'] = $luongnhanvien['luong'];
    $danhsach[$thutu]['luongcoban'] = $luongnhanvien['luongcoban'];
    $danhsach[$thutu]['luonghientai'] = $luongnhanvien['luonghientai'];
    $danhsach[$thutu]['lenluong'] = $luongnhanvien['lenluong'];
    $danhsach[$thutu]['phucap'] = $luongnhanvien['phucap'];
    $danhsach[$thutu]['kyhopdong'] = $luongnhanvien['kyhopdong'];
    $danhsach[$thutu]['tiletietkiem'] = $luongnhanvien['tiletietkiem'];
    $danhsach[$thutu]['tamnghi'] = $luongnhanvien['tamnghi'];
    $danhsach[$thutu]['heluong'] = $luongnhanvien['heluong'];
    $danhsach[$thutu]['tilethuong'] = laytilethuong($thongtin['userid'], $luongnhanvien['heluong']);
  }
  return $danhsach;
}

function laytilethuong($idnhanvien, $heluong) {
  global $db;

  $tile = [
    0 => [
      'loinhuanbanhang' => 0,
      'loinhuanspa' => 0,
      'chietkhaubanhang' => 0,
      'chietkhauspa' => 0,
    ],
    []
  ];
  if (intval($heluong)) {
    $sql = "select * from pet_". PREFIX ."_taichinh_tilebanhang where idnhanvien = $idnhanvien order by khoang asc";
    $tile[1] = $db->all($sql);
    foreach ($tile[1] as $thutu => $tilebanhang) {
      $tile[1][$thutu]['khoang'] = number_format($tilebanhang['khoang']);
      $tile[1][$thutu]['tien'] = number_format($tilebanhang['tien']);
    }
  }
  else {
    $sql = "select * from pet_". PREFIX ."_taichinh_tilespa where idnhanvien = $idnhanvien";
    $tilespa = $db->fetch($sql);
    if (!empty($tilespa)) {
      $tile[0] = [
        'loinhuanbanhang' => $tilespa['loinhuanbanhang'],
        'loinhuanspa' => $tilespa['loinhuanspa'],
        'chietkhaubanhang' => $tilespa['chietkhaubanhang'],
        'chietkhauspa' => $tilespa['chietkhauspa'],
      ];
    }
  }
  return $tile;
}

function thongtinluong($userid, $thoigian) {
  global $db;

  $homnay = strtotime(date('Y/m/1', $thoigian));
  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $userid";
  $thongtin = [
    'luong' => 0,
    'luongcoban' => 0,
    'luongcoban2' => 0,
    'phucap' => 0,
    'lenluong' => 0,
    'luonghientai' => 0,
    'tiletietkiem' => 0,
    'tamnghi' => 0,
    'heluong' => 0,
    'kyhopdong' => 'Chưa lập',
  ];
  if (!empty($nhanvien = $db->fetch($sql))) {
    $ngaylenluong = strtotime(date('Y/m/1', $nhanvien['kyhopdong']));
    $sonam = floor(($homnay - $ngaylenluong) / 365 / 60 / 60 / 24);

    $thongtin['luongcoban'] = $nhanvien['luongcoban'];
    $thongtin['luonghientai'] = $nhanvien['luongcoban'] + $sonam * $nhanvien['lenluong'];
    $thongtin['luongcoban2'] = $sonam * $nhanvien['lenluong'] + $nhanvien['luongcoban'];
    $thongtin['luong'] = $thongtin['luongcoban2'] + $nhanvien['phucap'];
    $thongtin['lenluong'] = $nhanvien['lenluong'];
    $thongtin['phucap'] = $nhanvien['phucap'];
    $thongtin['tiletietkiem'] = $nhanvien['tiletietkiem'];
    $thongtin['tamnghi'] = $nhanvien['tamnghi'];
    $thongtin['heluong'] = $nhanvien['heluong'];
    $thongtin['kyhopdong'] = date('d/m/Y', $nhanvien['kyhopdong']);
  }
  return $thongtin;
}

function capnhatnhanvien() {
  global $db, $data, $result;

  $data->luongcoban = purenumber($data->luongcoban);
  $data->phucap = purenumber($data->phucap);
  $data->lenluong = purenumber($data->lenluong);
  $data->tiletietkiem = floatval($data->tiletietkiem);
  $kyhopdong = isodatetotime($data->kyhopdong);
  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $data->userid";
  if (empty($luong = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_luong_nhanvien (userid, luongcoban, kyhopdong, lenluong, phucap, tiletietkiem, tamnghi, heluong) values($data->userid, $data->luongcoban, $kyhopdong, $data->lenluong, $data->phucap, $data->tiletietkiem, $data->tamnghi, $data->heluong)";
  }
  else $sql = "update pet_". PREFIX ."_luong_nhanvien set luongcoban = $data->luongcoban, kyhopdong = $kyhopdong, lenluong = $data->lenluong, phucap = $data->phucap, tiletietkiem = $data->tiletietkiem, tamnghi = $data->tamnghi, heluong = $data->heluong where userid = $data->userid";
  $db->query($sql);

  if ($data->heluong) {
    // banhang
    $sql = "delete from pet_". PREFIX ."_taichinh_tilebanhang where userid = $data->userid";
    $db->query($sql);

    foreach ($data->tilethuong[1] as $thutu => $tile) {
      $khoang = purenumber($tile->khoang);
      $tien = purenumber($tile->tien);
      $sql = "insert into pet_". PREFIX ."_taichinh_tilebanhang (khoang, tien, idnhanvien) values($khoang, $tien, $data->userid)";
      $db->query($sql);
    }
  }
  else {
    // spa
    $sql = "select * from pet_". PREFIX ."_taichinh_tilespa where idnhanvien = $data->userid";
    $tilespa = $db->fetch($sql);
    $tilethuong = $data->tilethuong[0];
    if (empty($tilespa)) {
      $sql = "insert into pet_". PREFIX ."_taichinh_tilespa (idnhanvien, loinhuanspa, loinhuanbanhang, chietkhauspa, chietkhaubanhang) values($data->userid, $tilethuong->loinhuanspa, $tilethuong->loinhuanbanhang, $tilethuong->chietkhauspa, $tilethuong->chietkhaubanhang)";
    }
    else {
      $sql = "update pet_". PREFIX ."_taichinh_tilespa set loinhuanspa = $tilethuong->loinhuanspa, loinhuanbanhang = $tilethuong->loinhuanbanhang, chietkhauspa = $tilethuong->chietkhauspa, chietkhaubanhang = $tilethuong->chietkhaubanhang where id = $tilespa[id]";
    }
    $db->query($sql);
  }
  
  $result['status'] = 1;
  return $result;
}

function khoitaotinhluong() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachtinhluong();
  $result['thongke'] = thongketinhluong();
  return $result;
}

function thongketinhluong() {
  global $data, $db;

  $thongke = [
    'luongnhanvien' => 0,
    'tietkiem' => 0,
    'thucnhan' => 0,
    'cophan' => 0,
  ];
  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $dulieu) {
    $thongke['luongnhanvien'] += $dulieu['tongluong'];
    $thongke['tietkiem'] += $dulieu['tietkiem'];
    $thongke['thucnhan'] += $dulieu['thucnhan'];
    $thongke['cophan'] += $dulieu['cophan'];
  }

  return $thongke;
}

function danhsachtinhluong() {
  global $db, $data;

  // kiểm tra lương tháng này
  // hiển thị danh sách tính lương
  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1'));
  $cuoithang = strtotime(date('Y/m/t')) + 60 * 60 * 24 - 1;

  $sql = "select userid, fullname as nhanvien from pet_". PREFIX ."_users where active = 1 and (userid in (select userid from pet_". PREFIX ."_user_per where module = 'loinhuan' and type > 0) or userid in (select idnhanvien as userid from pet_". PREFIX ."_luong_dulieu where (thoigian between $dauthang and $cuoithang)))";
  $danhsach = $db->all($sql);

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  foreach ($danhsach as $thutu => $thongtin) {
    $sql = "select * from pet_". PREFIX ."_luong_dulieu where idnhanvien = $thongtin[userid] and (thoigian between $dauthang and $cuoithang)";
    if (empty($nhanvien = $db->fetch($sql))) {
      $danhsach[$thutu]['doanhsobanhang'] = 0;
      $danhsach[$thutu]['doanhsospa'] = 0;
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
      $danhsach[$thutu]['doanhsobanhang'] = $nhanvien['doanhsobanhang'];
      $danhsach[$thutu]['doanhsospa'] = $nhanvien['doanhsospa'];
      $danhsach[$thutu]['doanhso'] = $nhanvien['doanhsobanhang'] + $nhanvien['doanhsospa'];
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

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1'));
  $cuoithang = strtotime(date('Y/m/t')) + 60 * 60 * 24 - 1;

  // tính tổng doanh thu, lương, thưởng, phụ cấp nghỉ phép
  $sql = "select * from pet_". PREFIX ."_users where active = 1 and userid in (select userid from pet_". PREFIX ."_user_per where module = 'loinhuan' and type > 0)";
  $dulieunhanvien = $db->obj($sql, 'fullname', 'userid');

  $danhsach = [];
  foreach ($dulieunhanvien as $ten => $idnhanvien) {
    $sql = "select * from pet_". PREFIX ."_luong_chotlich where batdau >= $dauthang and ketthuc <= $cuoithang and idnhanvien = $idnhanvien";
    
    if (empty($nghiphep = $db->fetch($sql))) $nghiphep = 0;
    else $nghiphep = $nghiphep['nghilo'];
    $danhsach[$idnhanvien] = ['tile' => 0, 'doanhso' => 0, 'doanhsobanhang' => 0, 'doanhsospa' => 0, 'nghiphep' => $nghiphep, 'cophan' => laytilecophan($idnhanvien), 'tilespa' => laytilespa($idnhanvien), 'tilebanhang' => laytilebanhang2($idnhanvien)];
  }

  foreach ($dulieushop as $nhanvien => $doanhthu) {
    if (!empty($dulieunhanvien[$nhanvien])) {
      $idnhanvien = $dulieunhanvien[$nhanvien];
      if (!empty($danhsach[$idnhanvien])) {
        $danhsach[$idnhanvien]['doanhsobanhang'] += intval($doanhthu);
        $danhsach[$idnhanvien]['doanhso'] += intval($doanhthu);
      }
    } 
  }
  foreach ($dulieubenhvien as $nhanvien => $doanhthu) {
    if (!empty($dulieunhanvien[$nhanvien])) {
      $idnhanvien = $dulieunhanvien[$nhanvien];
      if (!empty($danhsach[$idnhanvien])) {
        $danhsach[$idnhanvien]['doanhsospa'] += intval($doanhthu);
        $danhsach[$idnhanvien]['doanhso'] += intval($doanhthu);
      }
    } 
  }

  // tính thưởng
  $sql = "delete from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $db->query($sql);
  $danhsachnhanvien = [];

  foreach ($danhsach as $idnhanvien => $thongtin) {
    $luongnhanvien = thongtinluong($idnhanvien, $thoigian);
    if ($luongnhanvien['tamnghi'] > 0) {
      $thuong = 0;
      $tongluong = 0;
      $nghiphep = 0;
      $tietkiem = 0;
      $thucnhan = 0;
    }
    else {
      if ($luongnhanvien['heluong'] > 0) {
        $thuong = 0;
        foreach ($thongtin['tilebanhang'] as $tile) {
          if ($thongtin['doanhso'] >= $tile['khoang']) $thuong = $tile['tien'];
        }
      }
      else {
        $tiledoanhthu = $thongtin['doanhsobanhang'] * $thongtin['tilespa']['loinhuanbanhang'] * $thongtin['tilespa']['chietkhaubanhang'] / 100000 + $thongtin['doanhsospa'] * $thongtin['tilespa']['loinhuanspa'] * $thongtin['tilespa']['chietkhauspa'] / 100000;
        $thuong = ($tiledoanhthu > $luongnhanvien['luongcoban2'] ? $tiledoanhthu - $luongnhanvien['luongcoban2'] : 0);
      }

      $tongluong = $luongnhanvien['luongcoban2'] + $luongnhanvien['phucap'] + $thuong;
      $nghiphep = $tongluong * $thongtin['nghiphep'] / 60;
      $tietkiem = intval($tongluong * $luongnhanvien['tiletietkiem'] / 100);
      $thucnhan = $tongluong - $tietkiem - $nghiphep;
    }
    
    $sql = "insert into pet_". PREFIX ."_luong_dulieu (idnhanvien, luongcoban, phucap, doanhsobanhang, doanhsospa, nghiphep, thuong, tietkiem, tongluong, thucnhan, cophan, thoigian, thoigianthem) values($idnhanvien, $luongnhanvien[luongcoban2], $luongnhanvien[phucap], $thongtin[doanhsobanhang], $thongtin[doanhsospa], $nghiphep, $thuong, $tietkiem, $tongluong, $thucnhan, 0, $thoigian, ". time() .")";
    $db->query($sql);
  }

  // $sql = "delete from pet_". PREFIX ."_taichinh_chi where codinh = 1 and (thoigian between $dauthang and $cuoithang)";
  // $db->query($sql);

  // $sql = "select * from pet_". PREFIX ."_taichinh_chicodinh";
  // $danhsachchicodinh = $db->all($sql);

  // foreach ($danhsachchicodinh as $chicodinh) {
  //   $sql = "insert into pet_". PREFIX ."_taichinh_chi (idloaichi, giatri, thoigian, codinh) values($chicodinh[idloaichi], $chicodinh[giatri], ". time() .", 1)";
  //   $db->query($sql);
  // }

  // tính cổ phần, cập nhật
  // tính lợi nhuận thuần = lợi nhuận - tổng lương nhân viên
  $loinhuanthuan = tinhloinhuan();
  foreach ($danhsach as $idnhanvien => $thongtin) {
    if ($thongtin['cophan'] > 0) {
      $cophan = intval($thongtin['cophan'] * $loinhuanthuan / 100);
      $sql = "update pet_". PREFIX ."_luong_dulieu set cophan = $cophan where idnhanvien = $idnhanvien and (thoigian between $dauthang and $cuoithang)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã tải dữ liệu lên thành công';
  $result['danhsach'] = danhsachtinhluong();
  $result['thongke'] = thongketinhluong();
  return $result;
}

function tinhloinhuan() {
  global $data, $db;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  $mathoigian = date('mY', $thoigian);

  $sql = "select sum(tienmat + nganhang) as tong from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongthu = $db->fetch($sql)['tong'];

  $sql = "select value as tong from pet_". PREFIX ."_config where module = 'taichinh' and name = 'khachno$mathoigian'";
  if (empty($tongkho = $db->fetch($sql))) $tongkhachno = 0;
  else $tongkhachno = $tongkho['tong'];

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_noncc where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongnonhacungcap = $db->fetch($sql)['tong'];
  $tongkho = laydulieutonkho();
 
  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang";
  $chithuongxuyen = intval($db->fetch($sql)['tong']);

  $sql = "select sum(tongluong) as tong from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $chiluongthuong = intval($db->fetch($sql)['tong']);

  $sql = "select sum(cophan) as tong from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $cophan = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chincc where thoigian between $dauthang and $cuoithang";
  $chinhacungcap = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chicodinh";
  $chicodinh = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri * soluong) as tong from pet_". PREFIX ."_taichinh_chivattu where thoigian between $dauthang and $cuoithang";
  $chitaisan = intval($db->fetch($sql)['tong']);

  $tongchi = $chithuongxuyen + $chiluongthuong + $chinhacungcap + $chitaisan + $chicodinh;
  if (empty($tongkho['thangnay'])) $kho = 0;
  else $kho = $tongkho['bandau'] - $tongkho['thangnay'];

  return $tongthu - $tongchi + $tongkhachno - $tongnonhacungcap + $kho;
}

function laydulieutonkho() {
  global $data, $db;

  $thoigian = date('mY');
  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkho'";
  if (empty($tongkho = $db->fetch($sql))) $tongkho = ['value' => 0];

  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkho$thoigian'";
  if (empty($tongkhohientai = $db->fetch($sql))) $tongkhohientai = ['value' => 0];
  return [
    'bandau' => $tongkho['value'],
    'thangnay' => $tongkhohientai['value']
  ];
}

function laytilecophan($idnhanvien) {
  global $db;

  $sql = "select sum(tile) as tong from pet_". PREFIX ."_cophan where idnhanvien = $idnhanvien";
  if (empty($cophan = $db->fetch($sql))) return 0;
  return $cophan['tong'];
}

function phucap($userid) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $userid";
  $luong = $db->fetch($sql);

  if (empty($luong)) return 0;
  return $luong['phucap'];
}

function luongcoban($userid) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_luong_nhanvien where userid = $userid";
  $luong = $db->fetch($sql);

  if (empty($luong)) return 0;
  $homnay = time();
  $dauthanglenluong = strtotime(date('Y/m/1', $luong['kyhopdong']));
  $sonam = floor(($homnay - $dauthanglenluong) / 365 / 60 / 60 / 24);

  return $sonam * $luong['lenluong'] + $luong['luongcoban'];
}

function luucauhinh() {
  global $data, $db, $result;
  
  $userid = $checkuserid;
  foreach ($data->cauhinh as $bien => $giatri) {
    $giatri = floatval($giatri);
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$bien'";
    if (!empty($truongcauhinh = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_config set value = '$giatri' where id = $truongcauhinh[id]";
    else $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('loinhuan', '$bien', '$giatri', 0)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  $result['cauhinh'] = laytilespa($userid);
  return $result;
}
