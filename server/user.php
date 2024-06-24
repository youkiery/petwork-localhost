<?php

function kiemtradangnhap() {
  global $data, $db, $result;

  $hientai = time();
  $giahan = $hientai + 60 * 60 * 24 * 7; // gia hạn phiên 7 ngày
  if (empty($data->phiendangnhap)) {
    $thongtindangnhap = $data->thongtindangnhap;
    $taikhoan = mb_strtolower($thongtindangnhap->taikhoan);
    $matkhau = $thongtindangnhap->matkhau;

    include_once(DIR . '/include/Encryption.php');
    $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
    $crypt = new NukeViet\Core\Encryption($sitekey);

    $sql = "select id, matkhau from pet_nhanvien where kichhoat = 1 and LOWER(taikhoan) = '$taikhoan'";
    if (empty($nhanvien = $db->fetch($sql))) $result['messenger'] = 'Người dùng không tồn tại';
    else if (!$crypt->validate_password($matkhau, $nhanvien['matkhau'])) $result['messenger'] = 'Sai mật khẩu';
    else {
      $phien = randomString();
      $sql = "insert into pet_nhanvien_phiendangnhap (idnhanvien, phien, thoigian) values($nhanvien[id], '$phien', $giahan)";
      $db->query($sql);
      return laydulieudangnhap($nhanvien["id"], $phien);
    }
  }
  else {
    $sql = "select * from pet_nhanvien_phiendangnhap where phien = '$data->phiendangnhap'";
    $phien = $db->fetch($sql);
    $result['messenger'] = "Phiên đăng nhập hết hạn";
    if (!empty($phien)) {
      $sql = "select * from pet_nhanvien where id = $phien[idnhanvien]";
      $nhanvien = $db->fetch($sql);

      if (!empty($nhanvien) && $phien["thoigian"] > $hientai) {
        $sql = "update pet_nhanvien_phiendangnhap set thoigian = $giahan where id = $phien[id]";
        $db->query($sql);
        return laydulieudangnhap($nhanvien["id"], $data->phiendangnhap);
      }
      else {
        $sql = "delete from pet_nhanvien_phiendangnhap where idnhanvien = $nhanvien[id] and thoigian < $hientai";
        $db->query($sql);
      }
    }
  }
  return $result;
}

function laydulieudangnhap($idnhanvien, $phien) {
  global $data, $db, $result;

  // nếu chưa có tiền tố thì hiển thị danh sách chi nhánh và phân quyền
  // nếu không lấy dữ liệu đăng nhập
  if ($idnhanvien == 1 || $idnhanvien == 5) {
    $sql = "select * from pet_chinhanh where tiento <> ''";
  }
  else {
    $sql = "select * from pet_chinhanh where id in (select idchinhanh as id from pet_nhanvien_phanquyen where idnhanvien = $idnhanvien group by idchinhanh) and tiento <> ''";
  }
  $danhsachchinhanh = $db->all($sql);
  $kiemtra = true;
  foreach ($danhsachchinhanh as $thutu => $chinhanh) {
    if ($chinhanh["id"] == $data->idchinhanh) {
      $kiemtra = false;
      break;
    }
  }
  if ($kiemtra) $data->idchinhanh = 0;

  if (empty($data->idchinhanh)) {
    $chucvu = 0;
    if ($idnhanvien == 1 || $idnhanvien == 5) $chucvu = 3;

    return [
      "status" => 1,
      "chucvu" => $chucvu,
      "chinhanh" => $danhsachchinhanh,
      "phiendangnhap" => $phien,
      "phanquyen" => layphanquyen($idnhanvien),
    ];
  }
  return [
    "status" => 1,
    "phiendangnhap" => $phien,
    "chinhanh" => $danhsachchinhanh,
    "phanquyen" => layphanquyen($idnhanvien, $data->idchinhanh),
    "thongbao" => laythongbao($idnhanvien, $data->idchinhanh),
    "thongtin" => laythongtin($idnhanvien),
  ];
}

function laythongtin($idnhanvien) {
  global $db;

  $sql = "select * from pet_nhanvien where id = $idnhanvien";
  $nhanvien = $db->fetch($sql);

  $homnay = date('d/m');
  $ngaymai = date('d/m', time() + 60 * 60 * 24);

  return array(
    'month' => array('start' => date('Y-m-01'), 'end' => date('Y-m-t')),
    'today' => date('d/m/Y'),
    'next' => date('d/m/Y', time() + 60 * 60 * 24 * 21),
    'idnhanvien' => $idnhanvien,
    'taikhoan' => $nhanvien['taikhoan'],
    'hoten' => $nhanvien['hoten'],
  );
}

// function login() {
//   global $data, $db, $result;
//     $result['status'] = 1;
//     $result['session'] = $session;
//     $result['data'] = khoitaodulieu($user['userid']);
//     $result['badge'] = khoitaobadge($user['userid']);
//     $result['phanquyen'] = layphanquyen($user['userid']);
//     $result['site'] = getsiteconfig();
//   return $result;
// }

function laythongbao($userid) {
  global $data, $db, $result;

  $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
  $sql = "select * from pet_". PREFIX ."_usg where status < 7 and recall < $lim and userid = $userid and utemp = 0";
  $uc = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_usg where status = 9 and userid = $userid";
  $ut = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_vaccine where status < 2 and recall < $lim and userid = $userid and utemp = 0";
  $vc = $db->count($sql);
  $sql = "select * from pet_". PREFIX ."_vaccine where status = 5 and userid = $userid";
  $vt = $db->count($sql);

  $daungay = strtotime(date("Y/m/d"));
  $cuoingay = $daungay + 60 * 60 * 24 - 1;
  $sql = "select id from pet_". PREFIX ."_datlich where trangthai = 0 and ngaydat < $cuoingay";
  $datlich = $db->count($sql);

  $sql = "select id from pet_". PREFIX ."_datlich where trangthai = 0 and (thoigian between $daungay and $cuoingay)";
  $datlichhomnay = $db->count($sql);

  $sql = "select id from pet_". PREFIX ."_danhgia where (thoigian between $daungay and $cuoingay)";
  $danhgia = $db->count($sql);
  
  $sql = "select id from pet_". PREFIX ."_hanghoa_donhang where trangthai = 0";
  $donhang = $db->count($sql);
  
  $sql = "select * from pet_". PREFIX ."_xray where insult = 0";
  $dieutri = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_kaizen where active = 1 and done = 0";
  $kaizen = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_xray_row where sinhhoa < 0 or sinhly < 0";
  $xetnghiem = $db->count($sql);

  $sql = "select * from pet_". PREFIX ."_exam where status = 0";
  $xetnghiemkhac = $db->count($sql);

  $hansudung = time() + 60 * 60 * 24 * 90;
  $sql = "select * from pet_". PREFIX ."_hansudung where trangthai = 0 and hansudung <= $hansudung";
  $result['data']['hsd'] = $db->count($sql);


  return [
    "donhang" => $donhang,
    "datlich" => $datlich,
    "datlichhomnay" => $datlichhomnay,
    "danhgia" => $danhgia,
    "vaccine" => $vc + $vt,
    "sieuam" => $uc + $ut,
    "dieutri" => $dieutri,
    "kaizen" => $kaizen,
    "xetnghiem" => $xetnghiem,
    "xetnghiemkhac" => $xetnghiemkhac,
  ];
}

function logout() {
  global $data, $db, $result;

  $sql = "delete from pet_nhanvien_phiendangnhap where phien = '$data->phiendangnhap'";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function changename() {
  global $data, $db, $result;

  $sql = "update pet_nhanvien set hoten = '$data->name' where id = $data->idnguoidung";
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
    $sql = "select matkhau from `pet_nhanvien` where id = $data->idnguoidung";
    $nguoidung = $db->fetch($sql);
    if (empty($nguoidung)) $result['messenger'] = 'Người dùng không tồn tại';
    else if (!$crypt->validate_password($data->old, $nguoidung['matkhau'])) $result['messenger'] = 'Sai mật khẩu cũ';
    else {
      $password = $crypt->hash_password($data->new, '{SSHA512}');
      $sql = "update `pet_nhanvien` set matkhau = '$password' where id = $data->idnguoidung";
      $db->query($sql);
      $result['status'] = 1;
      $result['messenger'] = 'Đã đổi mật khẩu';
    }
  }
  return $result;
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
