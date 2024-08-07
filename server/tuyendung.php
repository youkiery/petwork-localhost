<?php
function khoitao() {
  global $data, $db, $result;
  
  $cauhinh = ["thongbao" => [], "camon" => "", "phongvan" => "", "hopdong" => 0];
  $sql = "select * from pet_cauhinh where tenbien = 'camontuyendung'";
  if (!empty($camontuyendung = $db->fetch($sql))) $cauhinh["camon"] = $camontuyendung["giatri"];

  $sql = "select * from pet_cauhinh where tenbien = 'thoigianhopdong'";
  if (!empty($thoigianhopdong = $db->fetch($sql))) $cauhinh["hopdong"] = $thoigianhopdong["giatri"];

  $result['status'] = 1;
  $result['tuyendung'] = danhsachcauhinhtuyendung();
  $result['cauhinh'] = $cauhinh;  
  $result['danhsach'] = danhsachtuyendung();
  $result['chinhanh'] = danhsachchinhanh();
  return $result;
}

function danhsachtuyendung() {
  global $data, $db, $result;

  $loc = $data->loc;
  $xtra = [];
  if ($loc->chinhanh) $xtra []= "chinhanh = ". $loc->chinhanh;
  if ($loc->vitri) $xtra []= "idtuyendung = ". $loc->vitri;
  if (count($xtra)) $xtra = "and ". implode(", ", $xtra);
  else $xtra = "";

  $sql = "select * from pet_chinhanh where tiento <> ''";
  $dulieuchinhanh = $db->obj($sql, "id", "tenchinhanh");

  $danhsach = [[], [], [], []];
  $sql = "select * from pet_tuyendung_hoso where trangthai < 4 $xtra order by id asc";
  $danhsachtuyendung = $db->all($sql);

  $sql = "select * from pet_cauhinh where tenbien = 'thoigianhopdong'";
  $hopdong = 0;
  $thoigian = time();
  if (!empty($thoigianhopdong = $db->fetch($sql))) $hopdong = $thoigian + $thoigianhopdong["giatri"] * 60 * 60 * 24;

  foreach ($danhsachtuyendung as $thutu => $tuyendung) {
    $danhsachtuyendung[$thutu]["hethopdong"] = 0;
    $danhsachtuyendung[$thutu]["chinhanh"] = $dulieuchinhanh[$tuyendung["chinhanh"]];

    $sql = "select * from pet_tuyendung where id = $tuyendung[idtuyendung]";
    $dulieutuyendung = $db->fetch($sql);

    if ($tuyendung["trangthai"] == 3) {
      if ($tuyendung["thoigian"] < $hopdong) $danhsachtuyendung[$thutu]["hethopdong"] = 1;

      $sql = "select * from pet_chinhanh where id = $tuyendung[idchinhanh]";
      if ($chinhanh = $db->fetch($sql)) {
        $sql = "select username from pet_". $chinhanh["tiento"] ."_users where userid = $tuyendung[idnhanvien]";
        if ($nhanvien = $db->fetch($sql)) $danhsachtuyendung[$thutu]["taikhoan"] = $nhanvien["username"];
      }
    }

    $sql = "select * from pet_tuyendung_tailieu where idhoso = $tuyendung[id] and loai = 0";
    $danhsachtuyendung[$thutu]["tailieu"] = $db->all($sql);

    $sql = "select * from pet_tuyendung_tailieu where idhoso = $tuyendung[id] and loai = 1";
    $danhsachtuyendung[$thutu]["hopdong"] = $db->all($sql);

    $danhsachtuyendung[$thutu]["vitri"] = $dulieutuyendung["vitri"];
    $danhsachtuyendung[$thutu]["thoigian"] = date("d/m/Y", $tuyendung["thoigian"]);

    $danhsach[$tuyendung["trangthai"]] []= $danhsachtuyendung[$thutu];
  }

  return $danhsach;
}

function khoitaoluutru() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['luutru'] = danhsachluutru();
  return $result;
}

function danhsachluutru() {
  global $data, $db, $result;

  $danhsach = [];
  $sql = "select * from pet_tuyendung_hoso where trangthai = 4 order by id desc";
  $danhsachtuyendung = $db->all($sql);

  foreach ($danhsachtuyendung as $thutu => $tuyendung) {
    $sql = "select * from pet_tuyendung where id = $tuyendung[idtuyendung]";
    $dulieutuyendung = $db->fetch($sql);

    $sql = "select * from pet_tuyendung_tailieu where idhoso = $tuyendung[id] and loai = 0";
    $danhsachtuyendung[$thutu]["tailieu"] = $db->all($sql);

    $sql = "select * from pet_tuyendung_tailieu where idhoso = $tuyendung[id] and loai = 1";
    $danhsachtuyendung[$thutu]["hopdong"] = $db->all($sql);

    $danhsachtuyendung[$thutu]["vitri"] = $dulieutuyendung["vitri"];
    $danhsachtuyendung[$thutu]["thoigian"] = date("d/m/Y", $tuyendung["thoigian"]);

    $danhsach []= $danhsachtuyendung[$thutu];
  }

  return $danhsach;
}

function danhsachchinhanh() {
  global $data, $db, $result;

  $sql = "select * from pet_chinhanh where tiento <> '' order by id";
  return $db->all($sql);
}

function danhsachcauhinhtuyendung() {
  global $data, $db, $result;

  $sql = "select * from pet_tuyendung where kichhoat = 1 order by id desc";
  return $db->all($sql);
}

function capnhattuyendung() {
  global $data, $db, $result;

  if ($data->id) {
    $sql = "update pet_tuyendung set vitri = '$data->vitri', yeucau = '$data->yeucau', mucluong = '$data->mucluong' where id = $data->id";
  }
  else {
    $sql = "insert into pet_tuyendung (vitri, yeucau, mucluong) values('$data->vitri', '$data->yeucau', '$data->mucluong')";
  }
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachcauhinhtuyendung();
  return $result;
}

function luucauhinh() {
  global $data, $db, $result;

  $sql = "select * from pet_cauhinh where tenbien = 'camontuyendung'";
  if ($camon = $db->fetch($sql)) $sql = "update pet_cauhinh set giatri = '$data->camon' where id = $camon[id]";
  else $sql = "insert into pet_cauhinh (tenbien, giatri) values('camontuyendung', '$data->camon')";
  $db->query($sql);

  $sql = "select * from pet_cauhinh where tenbien = 'thoigianhopdong'";
  if ($hopdong = $db->fetch($sql)) $sql = "update pet_cauhinh set giatri = '$data->hopdong' where id = $hopdong[id]";
  else $sql = "insert into pet_cauhinh (tenbien, giatri) values('thoigianhopdong', '$data->hopdong')";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = "Đã lưu cấu hình";
  return $result;
}

function xoatuyendung() {
  global $data, $db, $result;

  $sql = "update pet_tuyendung set kichhoat = 0 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachcauhinhtuyendung();
  return $result;
}

function tuchoi() {
  global $data, $db, $result;

  $sql = "update pet_tuyendung_hoso set trangthai = 1 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachtuyendung();
  return $result;
}

function thuviec() {
  global $data, $db, $result;

  $sql = "update pet_tuyendung_hoso set trangthai = 2 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachtuyendung();
  return $result;
}

function hopdong() {
  global $data, $db, $result;

  $sql = "update pet_tuyendung_hoso set trangthai = 3 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachtuyendung();
  return $result;
}

function choduyet() {
  global $data, $db, $result;

  $sql = "update pet_tuyendung_hoso set trangthai = 0 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachtuyendung();
  return $result;
}

function luutru() {
  global $data, $db, $result;

  $thoigian = time();
  $sql = "update pet_tuyendung_hoso set trangthai = 4, thoigian = $thoigian where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachtuyendung();
  return $result;
}

function chuyenvephongvan() {
  global $data, $db, $result;

  $thoigian = time();
  $sql = "update pet_tuyendung_hoso set trangthai = 1, thoigian = $thoigian where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['luutru'] = danhsachluutru();
  $result['danhsach'] = danhsachtuyendung();
  return $result;
}

function kyhopdong() {
  global $data, $db, $result;

  $danhsach = [0 => "hợp đồng", "cam kết nội quy", "cam kết đào tạo"];
  if ($data->idnhanvien) {
    $thoigian = isodatetotime($data->thoigian);
    $sql = "update pet_tuyendung_hoso set thoigian = $thoigian where id = $data->id";
    $db->query($sql);

    foreach ($danhsach as $thutu => $ten) {
      $nam = date("Y");
      $duongdan = $data->tailieu[$thutu];
      $sql = "insert into pet_tuyendung_tailieu (idhoso, duongdan, ten, loai) values($data->id, '$duongdan', '$ten $nam', 1)";
      $db->query($sql);
    }
  }
  else {
    include_once(DIR . '/include/Encryption.php');
    $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
    $crypt = new NukeViet\Core\Encryption($sitekey);
    $birthday = 0;
    $data->taikhoan = mb_strtolower($data->taikhoan);
  
    $sql = "select * from pet_chinhanh where id = $data->idchinhanh";
    $chinhanh = $db->fetch($sql);
  
    $sql = "select userid, password from `pet_". $chinhanh["tiento"] ."_users` where LOWER(username) = '$data->taikhoan'";
    if (!empty($user = $db->fetch($sql))) $result['messenger'] = 'Tên người dùng đã tồn tại';
    else {
      $time = time();
      $sql = "insert into pet_". $chinhanh["tiento"] ."_users (username, idvantay, name, fullname, password, photo, regdate, birthday, active) values ('$data->taikhoan', 0, '$data->hoten', '$data->hoten', '". $crypt->hash_password($data->matkhau) ."', '', $time, $birthday, 1)";
      $idnhanvien = $db->insertid($sql);
  
      $thoigian = isodatetotime($data->thoigian);
      $sql = "update pet_tuyendung_hoso set trangthai = 3, idnhanvien = $idnhanvien, idchinhanh = $data->idchinhanh, thoigian = $thoigian where id = $data->id";
      $db->query($sql);
  
      foreach ($danhsach as $thutu => $ten) {
        $nam = date("Y");
        $duongdan = $data->tailieu[$thutu];
        $sql = "insert into pet_tuyendung_tailieu (idhoso, duongdan, ten, loai) values($data->id, '$duongdan', '$ten $nam', 1)";
        $db->query($sql);
      }
      
      $result['status'] = 1;
      $result['danhsach'] = danhsachtuyendung();
    }
  }
  
  return $result;
}

function xoahoso() {
  global $data, $db, $result;

  $sql = "delete from pet_tuyendung_hoso where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachtuyendung();
  return $result;
}

function nophoso() {
	global $result, $db, $data, $tiento;

	$dulieu = $data;
	if ($dulieu->id) {
		$sql = "update pet_tuyendung_hoso set hoten = '$dulieu->hoten', dienthoai = '$dulieu->dienthoai', diachi = '$dulieu->diachi', ghichu = '$dulieu->ghichu', chinhanh = $dulieu->chinhanh where id = $data->id";
		$db->query($sql);
	}
	else {
		$thoigian = time();
		$sql = "insert into pet_tuyendung_hoso (idtuyendung, token, hoten, diachi, dienthoai, thoigian, trangthai, chinhanh, ghichu) values($dulieu->idtuyendung, '$data->token', '$dulieu->hoten', '$dulieu->diachi', '$dulieu->dienthoai', $thoigian, 0, $dulieu->chinhanh, '$dulieu->ghichu')";
		$dulieu->id = $db->insertid($sql);
	}

	$sql = "delete from pet_tuyendung_tailieu where idhoso = $dulieu->id";
	$db->query($sql);

	foreach ($dulieu->tailieu as $tailieu) {
		$sql = "insert into pet_tuyendung_tailieu (idhoso, duongdan, ten) values($dulieu->id, '$tailieu->file', '$tailieu->ten')";
		$db->query($sql);
	}
	$result["status"] = 1;
	$result["danhsach"] = danhsachtuyendung();
  return $result;
}