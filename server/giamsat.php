<?php
function khoitao() {
  global $data, $db, $result;
  
  if (!empty($data->idthietbi)) {
    // nếu đã có, kiểm tra có quyền chưa, chưa có quyền thì unset idthietbi
    $sql = "select * from hanet_phanquyenthietbi where idnhanvien = $data->userid and tiento = '$data->tiento' and idthietbi = $data->idthietbi";
    if (empty($db->fetch($sql))) $data->idthietbi = 0;
  }

  if (empty($data->idthietbi)) {
    // nếu chưa có, chọn cái đầu tiên
    $sql = "select * from hanet_phanquyenthietbi where idnhanvien = $data->userid and tiento = '$data->tiento' order by idthietbi";
    $thietbi = $db->fetch($sql);
    if (empty($thietbi)) {
      // chưa có phân quyền, thông báo và kết thúc
      $result['messenger'] = "Chưa được phân quyền, vui lòng liên hệ quản trị viên";
      return $result;
    }
    $data->idthietbi = $thietbi["id"];
  }

  $sql = "select * from pet_chinhanh where tiento <> '' order by id asc";
  $danhsachchinhanh = $db->all($sql);

  $sql = "select * from hanet_phanquyenthietbi where idnhanvien = $data->userid and tiento = '$data->tiento' order by idthietbi asc";
  $danhsachphanquyen = $db->all($sql);

  foreach ($danhsachphanquyen as $thutu => $chinhanh) {
    $sql = "select * from hanet_thietbi where id = $chinhanh[idthietbi]";
    $thietbi = $db->fetch($sql);

    $danhsachphanquyen[$thutu]["tenthietbi"] = $thietbi["tenthietbi"];
  }

  $result['status'] = 1;
  $result['danhsachgiamsat'] = danhsachgiamsat($data->idthietbi);
  $result['danhsachchinhanh'] = $danhsachchinhanh;
  $result['danhsachphanquyen'] = $danhsachphanquyen;
  $result['danhsachthietbi'] = danhsachthietbi();
  $result['idthietbi'] = $data->idthietbi;
  return $result;
}

function danhsachthietbi() {
  global $db;
  
  $sql = "select * from hanet_thietbi order by id desc";
  $danhsachthietbi = $db->all($sql);
  $chuyendoi = ["test" => "daklak", "nhatrang" => "nhatrang", "nhatrang3" => "nhatrang 3", "nhatrang4" => "nhatrang 5"];

  foreach ($danhsachthietbi as $thutu => $thietbi) {
    $danhsachthietbi[$thutu]["chinhanh"] = [];
    $sql = "select idnhanvien, tiento from hanet_phanquyenthietbi where idthietbi = $thietbi[id]";
    $danhsachnhanvien = $db->all($sql);

    $sql = "select id, tenchinhanh from pet_chinhanh where tiento = '$thietbi[tiento]'";
    $dulieuchinhanh = $db->fetch($sql);

    $danhsachthietbi[$thutu]["tenchinhanh"] = $dulieuchinhanh["tenchinhanh"];

    foreach ($danhsachnhanvien as $nhanvien) {
      $bangnhanvien = "pet_". $nhanvien["tiento"] ."_users";
      $sql = "select fullname, userid from $bangnhanvien where userid = $nhanvien[idnhanvien]";
      $dulieunhanvien = $db->fetch($sql);
      $danhsachthietbi[$thutu]["chinhanh"] []= ["idnhanvien" => $dulieunhanvien["userid"], "nhanvien" => $dulieunhanvien["fullname"], "hauto" => $chuyendoi[$nhanvien["tiento"]]];
    }
  }
  return $danhsachthietbi;
}

function themnhanvienthietbi() {
  global $data, $db, $result;

  $sql = "select * from pet_chinhanh where tiento <> ''";
  $danhsachchinhanh = $db->all($sql);
  $chuyendoi = ["test" => "daklak", "nhatrang" => "nhatrang", "nhatrang3" => "nhatrang 3", "nhatrang4" => "nhatrang 5"];
  $danhsach = [];

  foreach ($danhsachchinhanh as $chinhanh) {
    $tiento = $chinhanh["tiento"];
    $bangnhanvien = "pet_". $tiento . "_users";
    $sql = "select '$tiento' as tiento, userid, fullname from $bangnhanvien where userid not in (select idnhanvien as userid from hanet_phanquyenthietbi where idthietbi = $data->idthietbi and tiento = '$tiento' and active = 1)";
    $danhsach = array_merge($danhsach, $db->all($sql));    
  }

  foreach ($danhsach as $thutu => $nhanvien) {
    $danhsach[$thutu]["hauto"] = $chuyendoi[$nhanvien["tiento"]];
    $danhsach[$thutu]["chon"] = 0;
  }

  $result['status'] = 1;
  $result['danhsach'] = $danhsach;
  return $result;
}

function themphanquyenthietbi() {
  global $data, $db, $result;

  foreach ($data->danhsach as $nhanvien) {
    $idnhanvien = $nhanvien->idnhanvien;
    $tiento = $nhanvien->tiento;
    $sql = "select * from hanet_phanquyenthietbi where idnhanvien = $idnhanvien and tiento = '$tiento' and idthietbi = $data->idthietbi";
    if (empty($db->fetch($sql))) {
      $sql = "insert into hanet_phanquyenthietbi (idnhanvien, tiento, idthietbi) values($idnhanvien, '$tiento', $data->idthietbi)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachthietbi();
  return $result;
}

function xoanhanvienthietbi() {
  global $data, $db, $result;

  $sql = "delete from hanet_phanquyenthietbi where idnhanvien = $data->idnhanvien and tiento = '$data->tiento' and idthietbi = $data->idthietbi";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachthietbi();
  return $result;
}

function xoathietbi() {
  global $data, $db, $result;

  $sql = "delete from hanet_phanquyenthietbi where idthietbi = $data->idthietbi";
  $db->query($sql);
  $sql = "delete from hanet_thietbi where id = $data->idthietbi";
  $db->query($sql);
  // lấy cấu hình thiết bị
  $sql = "select * from hanet_thietbi order by id desc";
  $danhsachthietbi = $db->all($sql);

  foreach ($danhsachthietbi as $thutu => $thietbi) {
    $sql = "select idchinhanh, 1 as giatri from hanet_phanquyenthietbi where idthietbi = $thietbi[id]";
    $danhsachthietbi[$thutu]["chinhanh"] = $db->obj($sql, "idchinhanh", "giatri");
  }

  $result['status'] = 1;
  $result['danhsachthietbi'] = $danhsachthietbi;
  return $result;
}

function thaydoiphanquyen() {
  global $data, $db, $result;

  $sql = "select * from hanet_phanquyenthietbi where idthietbi = $data->idthietbi and idchinhanh = $data->idchinhanh";
  if (empty($phanquyen = $db->fetch($sql))) {
    $sql = "insert into hanet_phanquyenthietbi (idthietbi, idchinhanh) values($data->idthietbi, $data->idchinhanh)";
  }
  else {
    $sql = "delete from hanet_phanquyenthietbi where id = $phanquyen[id]";
  }
  $db->query($sql);

  // lấy cấu hình thiết bị
  $sql = "select * from hanet_thietbi order by id desc";
  $danhsachthietbi = $db->all($sql);

  foreach ($danhsachthietbi as $thutu => $thietbi) {
    $sql = "select idchinhanh, 1 as giatri from hanet_phanquyenthietbi where idthietbi = $thietbi[id]";
    $danhsachthietbi[$thutu]["chinhanh"] = $db->obj($sql, "idchinhanh", "giatri");
  }

  $result['status'] = 1;
  $result['danhsachthietbi'] = $danhsachthietbi;
  return $result;
}

function khoitaoidthietbi() {
  global $data, $db, $result;

  $sql = "select deviceID, deviceName from hanet_khachhang where data_type = 'device'";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $thietbi) {
    $sql = "select * from hanet_thietbi where idthietbi = '$thietbi[deviceID]'";
    if (!empty($db->fetch($sql))) unset($danhsach[$thutu]);
  }

  sort($danhsach);

  $result['status'] = 1;
  $result['thietbi'] = $danhsach;
  return $result;
}

function khoitaothongke() {
  global $data, $db, $result;
  
  $thongke = [
    "tongcheckin" => 0,
    "tongkhachla" => 0,
    "tongkhachquen" => 0,
    "tongkhonghoadon" => 0,
    "tonghoadon" => 0,
  ];

  $batdau = strtotime($data->batdau);
  $ketthuc = strtotime($data->ketthuc) + 60 * 60 * 24 - 1;
  $batdau2 = $batdau * 1000;
  $ketthuc2 = $ketthuc * 1000;
  $idchinhanh = $data->idchinhanh;

  $sql = "select * from pet_cauhinh where tenbien = 'cauhinhgiamsat-$idchinhanh'";
  $thietbi = $db->fetch($sql);
  if (empty($thietbi)) return [];
  $idthietbi = $thietbi["giatri"];

  $sql = "select personName, time, avatar, -1 as lienket from hanet_khachhang where data_type = 'log' and personType > 0 and (time between $batdau2 and $ketthuc2) and deviceID = '$idthietbi' order by time";
  $danhsachkhachhang = $db->all($sql);
  $tongkhachhang = count($danhsachkhachhang);
  $thongke["tongcheckin"] = $tongkhachhang;
  
  $sql = "select id, trangthai, dienthoai, khachhang, thoigian, -1 as lienket from pet_". PREFIX ."_hoadon where (thoigian between $batdau and $ketthuc) order by thoigian";
  $danhsachhoadon = $db->all($sql);
  $thongke["tonghoadon"] = count($danhsachhoadon);
  $dasudung = [];
  $dau = 0;
  $cuoi = 1;

  // // nếu nhận diện được khách hàng thì gắn vào hóa đơn
  foreach ($danhsachkhachhang as $thutukhachhang => $khachhang) {
    if (empty($khachhang["personName"])) {
      $thongke["tongkhachla"] ++;
      continue;
    }
    if (strlen($khachhang["personName"]) < 5) {
      $thongke["tongkhachla"] ++;
      continue;
    }
    $dienthoaikhach = substr($khachhang["personName"], -5);
    $thongke["tongkhachquen"] ++;

    foreach ($danhsachhoadon as $thutuhoadon => $hoadon) {
      if ($hoadon["lienket"] >= 0) continue;
      if (empty($hoadon["dienthoai"])) continue;
      if (strlen($hoadon["dienthoai"]) < 5) continue;

      if (strpos($hoadon["dienthoai"], $dienthoaikhach) != false) {
        $danhsachkhachhang[$thutukhachhang]["lienket"] = $thutuhoadon;
        $danhsachhoadon[$thutuhoadon]["lienket"] = $thutukhachhang;
        break;
      }
    }
  }
  
  foreach ($danhsachhoadon as $thutu => $hoadon) {
    if ($hoadon["lienket"] >= 0) continue;
    $kiemtra = false;
    while (!$kiemtra && $cuoi < $tongkhachhang) {
      if ($danhsachkhachhang[$dau]["lienket"] >= 0) {
        $dau ++;
        $cuoi ++;
        continue;
      }
      if ($danhsachkhachhang[$cuoi]["lienket"] >= 0) {
        $cuoi ++;
        continue;
      }
      $thoigiandau = $hoadon["thoigian"] - floor($danhsachkhachhang[$dau]["time"] / 1000);
      $thoigiancuoi = $hoadon["thoigian"] - floor($danhsachkhachhang[$cuoi]["time"] / 1000);
      if (($thoigiandau < 0 && $thoigiancuoi < 0) || ($thoigiandau * $thoigiancuoi <= 0)) {
        $thutusudung = $cuoi;
        if (abs($thoigiandau) < abs($thoigiancuoi)) $thutusudung = $dau;
        $danhsachkhachhang[$thutusudung]["lienket"] = $thutu;
        $danhsachhoadon[$thutu]["lienket"] = $thutusudung;
        $kiemtra = true;
      }
      $dau ++;
      $cuoi ++;
    }
  }

  $result['status'] = 1;
  $result['thongke'] = $thongke;
  return $result;
}

function luucauhinh() {
  global $data, $db, $result;
  
  foreach ($data->chinhanh as $chinhanh) {
    $idchinhanh = $chinhanh->id;
    $giatri = $chinhanh->cauhinh;
    $sql = "select * from pet_cauhinh where tenbien = 'cauhinhgiamsat-$idchinhanh'";
    if (empty($cauhinh = $db->fetch($sql))) {
      $sql = "insert into pet_cauhinh (tenbien, giatri) values('cauhinhgiamsat-$idchinhanh', '$giatri')";
      $db->query($sql);
    }
    else {
      $sql = "update pet_cauhinh set giatri = '$giatri' where tenbien = 'cauhinhgiamsat-$idchinhanh'";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['messenger'] = "Đã lưu cấu hình";
  return $result;
}

function danhsachgiamsat($idthietbi) {
  global $data, $db, $result, $tiento;

  $batdau = strtotime($data->thoigian);
  $ketthuc = $batdau + 60 * 60 * 24 - 1;
  $batdau2 = $batdau * 1000;
  $ketthuc2 = $ketthuc * 1000;

  $sql = "select * from hanet_thietbi where id = $idthietbi";
  $thietbi = $db->fetch($sql);

  $sql = "select personName, time, avatar, -1 as lienket from hanet_khachhang where data_type = 'log' and personType > 0 and (time between $batdau2 and $ketthuc2) and deviceID = '$thietbi[idthietbi]' order by time";
  $danhsachkhachhang = $db->all($sql);
  $tongkhachhang = count($danhsachkhachhang);
  
  $sql = "select id, trangthai, dienthoai, khachhang, thoigian, -1 as lienket from pet_". $thietbi["tiento"] ."_hoadon where (thoigian between $batdau and $ketthuc) order by thoigian";
  $danhsachhoadon = $db->all($sql);
  $dasudung = [];
  $dau = 0;
  $cuoi = 1;

  // // nếu nhận diện được khách hàng thì gắn vào hóa đơn
  foreach ($danhsachkhachhang as $thutukhachhang => $khachhang) {
    if (empty($khachhang["personName"])) continue;
    if (strlen($khachhang["personName"]) < 5) continue;
    $dienthoaikhach = substr($khachhang["personName"], -5);

    foreach ($danhsachhoadon as $thutuhoadon => $hoadon) {
      if ($hoadon["lienket"] >= 0) continue;
      if (empty($hoadon["dienthoai"])) continue;
      if (strlen($hoadon["dienthoai"]) < 5) continue;

      if (strpos($hoadon["dienthoai"], $dienthoaikhach) != false) {
        $danhsachkhachhang[$thutukhachhang]["lienket"] = $thutuhoadon;
        $danhsachhoadon[$thutuhoadon]["lienket"] = $thutukhachhang;
        break;
      }
    }
  }
  
  foreach ($danhsachhoadon as $thutu => $hoadon) {
    if ($hoadon["lienket"] >= 0) continue;
    $kiemtra = false;
    while (!$kiemtra && $cuoi < $tongkhachhang) {
      if ($danhsachkhachhang[$dau]["lienket"] >= 0) {
        $dau ++;
        $cuoi ++;
        continue;
      }
      if ($danhsachkhachhang[$cuoi]["lienket"] >= 0) {
        $cuoi ++;
        continue;
      }
      $thoigiandau = $hoadon["thoigian"] - floor($danhsachkhachhang[$dau]["time"] / 1000);
      $thoigiancuoi = $hoadon["thoigian"] - floor($danhsachkhachhang[$cuoi]["time"] / 1000);
      if (($thoigiandau < 0 && $thoigiancuoi < 0) || ($thoigiandau * $thoigiancuoi <= 0)) {
        $thutusudung = $cuoi;
        if (abs($thoigiandau) < abs($thoigiancuoi)) $thutusudung = $dau;
        $danhsachkhachhang[$thutusudung]["lienket"] = $thutu;
        $danhsachhoadon[$thutu]["lienket"] = $thutusudung;
        $kiemtra = true;
      }
      $dau ++;
      $cuoi ++;
    }
  }

  $dulieu = [];
  foreach ($danhsachkhachhang as $thutu => $khachhang) {
    $thoigian = floor($khachhang["time"] / 1000);
    $hoadon = [];
    if ($khachhang["lienket"] >= 0) {
      $hoadon = $danhsachhoadon[$khachhang["lienket"]];
    }
    else {
      $hoadon = [
        "id" => "0",
        "trangthai" => "0",
        "khachhang" => "",
        "dienthoai" => "",
        "thoigian" => ""
      ];
    }
    $checkin = [
      "id" => $hoadon["id"],
      "trangthai" => $hoadon["trangthai"],
      "avatar" => $khachhang["avatar"],
      "thoigiancheckin" => date("d/m/Y H:i", $thoigian),
      "khachhangcheckin" => $khachhang["personName"],
      "thoigianhoadon" => (empty($hoadon["thoigian"]) ? "" : date("d/m/Y H:i", $hoadon["thoigian"])),
      "khachhang" => (empty($hoadon) ? "" : $hoadon["khachhang"]),
      "dienthoai" => (empty($hoadon) ? "" : $hoadon["dienthoai"]),
    ];

    $dulieu []= $checkin;
  }

  usort($dulieu, "sosanh");

  return $dulieu;
}

function sosanh($a, $b) {
  return $a["thoigiancheckin"] < $b["thoigiancheckin"];
}

function themthietbi() {
  global $db, $data, $result;
  
  $dulieu = $data->dulieu;
  if ($dulieu->id) {
    $sql = "update hanet_thietbi set idthietbi = '$dulieu->idthietbi', tenthietbi = '$dulieu->tenthietbi', tiento = '$dulieu->tiento' where id = $dulieu->id";
  }
  else {
    $sql = "insert hanet_thietbi (idthietbi, tenthietbi, tiento) values('$dulieu->idthietbi', '$dulieu->tenthietbi', '$dulieu->tiento')";
  }
  $db->query($sql);
  
  $result["status"] = 1;
  $result["danhsach"] = danhsachthietbi();
  return $result;
}

function themfaceid() {
  global $data, $db, $result;

  $img = "../include/export/". time() . ".jpg";
  file_put_contents($img, file_get_contents($data->hinhanh));

  $url = resizeImageWithPadding($img);
  $diachifile = "../../token.txt";
  $docfile = fopen($diachifile, "r");
  $refreshtoken = fgets($docfile);
  fclose($docfile);
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://oauth.hanet.com/token?refresh_token=$refreshtoken&client_id=fd4614703837312d072b62e11fff663d&grant_type=refresh_token&client_secret=def13afd2297e47a49dc8049814c5b51");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  
  $headers = array();
  $headers[] = 'Content-Type: application/x-www-form-urlencoded';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  
  $res = curl_exec($ch);
  $token = json_decode($res);
  $accesstoken = $token->access_token;
  
  $docfile = fopen($diachifile, "w");
  fwrite($docfile, $token->refresh_token);
  fclose($docfile);
  $name = $data->khachhang ." - ". substr($data->dienthoai, -6);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://partner.hanet.ai/person/registerByUrl');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'token' => $accesstoken,
    'name' => $name,
    'url' => $url,
    'placeID' => '27599',
    'type' => '1'
  ]));
  
  $headers = [];
  $headers[] = 'Content-Type: application/x-www-form-urlencoded';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);

  $res = curl_exec($ch);
  if ($res->returnCode == 1) {
    $sql = "select * from pet_". PREFIX ."_hoadon set trangthai = 1 where id = $data->id";
    $db->query($sql);

    $result['status'] = 1;
    $result['messenger'] = "Đã thêm faceid";
    $result['danhsachgiamsat'] = danhsachgiamsat($data->idchinhanh);
  }
  else {
    $result['messenger'] = "Đã có lỗi trong quá trình tải lên";
  }

  return $result;
}

function resizeImageWithPadding($sourceImage) {
  global $_SERVER;
  // Get original image dimensions
  list($width, $height) = getimagesize($sourceImage);

  // Calculate the aspect ratio
  $originalAspect = $width / $height;
  $targetAspect = 1280 / 736;

  // Determine new dimensions
  if ($originalAspect >= $targetAspect) {
      // If the original image is wider
      $newWidth = 1280;
      $newHeight = intval(1280 / $originalAspect);
  } else {
      // If the original image is taller
      $newHeight = 736;
      $newWidth = intval(736 * $originalAspect);
  }

  // Create the new image canvas with target dimensions
  $destination = imagecreatetruecolor(1280, 736);

  // Set the background color to white
  $white = imagecolorallocate($destination, 255, 255, 255);
  imagefill($destination, 0, 0, $white);

  $source = imagecreatefromjpeg($sourceImage);
  $thoigian = time();
  $output = "../include/export/o$thoigian.jpg";
  // Calculate position to center the image on the canvas
  $xPos = (1280 - $newWidth) / 2;
  $yPos = (736 - $newHeight) / 2;

  // Copy and resize the original image into the center of the new canvas
  imagecopyresampled($destination, $source, $xPos, $yPos, 0, 0, $newWidth, $newHeight, $width, $height);
  imagejpeg($destination, $output);

  // Free up memory
  imagedestroy($source);
  imagedestroy($destination);
  return "https://". $_SERVER["SERVER_NAME"] . "/include/export/o$thoigian.jpg";
}
