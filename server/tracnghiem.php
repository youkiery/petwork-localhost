<?php
function khoitao() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();
  $sql = "select * from pet_tracnghiem_baithi where idnhanvien = $idnhanvien and nopbai = 0 order by thoigian desc limit 1";
  if (empty($baithi = $db->fetch($sql))) $result['baithicuoi'] = 0;
  else $result['baithicuoi'] = intval($baithi["thoigian"]);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdethi();
  return $result;
}

function khoitaochuyenmuc() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachchuyenmuc();
  return $result;
}

function khoitaochuyenmucdethi() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['chuyenmuc'] = danhsachchuyenmuc();
  $result['dethi'] = danhsachdethi();
  return $result;
}

function danhsachchuyenmuc() {
  global $data, $db;

  $sql = "select * from pet_tracnghiem_chuyenmuc where hienthi = 1 order by id desc";
  $danhsachchuyenmuc = $db->all($sql);

  foreach ($danhsachchuyenmuc as $thutuchuyemmuc => $chuyenmuc) {
    $sql = "select id from pet_tracnghiem_cauhoi where idchuyenmuc = $chuyenmuc[id] and hienthi = 1";
    $danhsachcauhoi = $db->all($sql);

    $danhsachchuyenmuc[$thutuchuyemmuc]["tongsocau"] = count($danhsachcauhoi);
  }

  return $danhsachchuyenmuc;
}

function danhsachdethi() {
  global $data, $db;

  $userid = checkuserid();
  $sql = "select * from pet_tracnghiem_dethi where hienthi = 1 order by id desc";
  $danhsachdethi = $db->all($sql);

  foreach ($danhsachdethi as $thutudethi => $dethi) {
    $sql = "select b.* from pet_tracnghiem_dethi_chuyenmuc a inner join pet_tracnghiem_chuyenmuc b on a.idchuyenmuc = b.id and a.iddethi = $dethi[id] where b.hienthi = 1";
    $danhsachchuyenmuc = $db->all($sql);
    $danhsachdethi[$thutudethi]["tongchuyenmuc"] = count($danhsachchuyenmuc);
    $danhsachdethi[$thutudethi]["tongsocau"] = 0;
    $danhsachdethi[$thutudethi]["thoigian"] = 0;
    $danhsachdethi[$thutudethi]["socau"] = 0;

    foreach ($danhsachchuyenmuc as $chuyenmuc) {
      $sql = "select id from pet_tracnghiem_cauhoi where idchuyenmuc = $chuyenmuc[id] and hienthi = 1";
      $danhsachcauhoi = $db->all($sql);

      $danhsachdethi[$thutudethi]["tongsocau"] += count($danhsachcauhoi);
      $danhsachdethi[$thutudethi]["thoigian"] += $chuyenmuc["thoigian"];
      $danhsachdethi[$thutudethi]["socau"] += $chuyenmuc["socau"];
    }
  }

  return $danhsachdethi;
}

function batdauthi() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();
  $thoigian = time();

  // nộp tất cả bài chưa hoàn thành
  $sql = "update pet_tracnghiem_baithi set nopbai = 1 where idnhanvien = $idnhanvien";
  $db->query($sql);

  // thêm bài thi mới
  $sql = "insert into pet_tracnghiem_baithi (idnhanvien, iddethi, thoigian) values($idnhanvien, $data->iddethi, $thoigian)";
  $idbaithi = $db->insertid($sql);

  // lấy danh sách câu hỏi từ đề thi
  $sql = "select b.* from pet_tracnghiem_dethi_chuyenmuc a inner join pet_tracnghiem_chuyenmuc b on a.idchuyenmuc = b.id and a.iddethi = $data->iddethi where b.hienthi = 1";
  $danhsachchuyenmuc = $db->all($sql);
  $cauhoidethi = [];

  foreach ($danhsachchuyenmuc as $chuyenmuc) {
    $sql = "select * from pet_tracnghiem_cauhoi where idchuyenmuc = $chuyenmuc[id] and hienthi = 1 order by rand() limit $chuyenmuc[socau]";
    $danhsachcauhoi = $db->all($sql);

    foreach ($danhsachcauhoi as $thutucauhoi => $cauhoi) {
      $sql = "select * from pet_tracnghiem_cautraloi where idcauhoi = $cauhoi[id] order by rand() limit 4";
      $danhsachcauhoi[$thutucauhoi]["cautraloi"] = $db->all($sql);
  
      foreach ($danhsachcauhoi[$thutucauhoi]["cautraloi"] as $thutu => $cautraloi) {
        $sql = "insert into pet_tracnghiem_bailam (idbaithi, idcauhoi, idcautraloi, luachon) values($idbaithi, $cauhoi[id], $cautraloi[id], 0)";
        $db->query($sql);
      }
    }

    $cauhoidethi = array_merge($cauhoidethi, $danhsachcauhoi);
  }

  $sql = "select * from pet_tracnghiem_baithi where idnhanvien = $idnhanvien and nopbai = 0 order by thoigian desc limit 1";
  if (empty($baithi = $db->fetch($sql))) $result['baithicuoi'] = 0;
  else $result['baithicuoi'] = intval($baithi["thoigian"]);

  $result["status"] = 1;
  $result["bailam"] = [
    "idbaithi" => $idbaithi,
    "nopbai" => 0, 
    "danhsach" => $cauhoidethi,
    "han" => $thoigian + $data->thoigian * 60
  ];
  return $result;
}

function nopbai() {
  global $data, $db, $result;

  $sql = "update pet_tracnghiem_baithi set nopbai = 1 where id = $data->idbaithi";
  $db->query($sql);

  $result["status"] = 1;
  return $result;
}

function xemdiem() {
  global $data, $db, $result;

  $result["status"] = 1;
  $result["diemthi"] = diemthi(); 
  return $result;
}

function diemthi() {
  global $data, $db;
  
  $tongcau = 0;
  $tongdung = 0;
  $sql = "select a.luachon from pet_tracnghiem_bailam a inner join pet_tracnghiem_cautraloi b on a.idcautraloi = b.id and b.dapan = 1 and idbaithi = $data->idbaithi order by a.id desc";
  $danhsachcauhoi = $db->all($sql);

  foreach ($danhsachcauhoi as $cauhoi) {
    $tongcau ++;
    if ($cauhoi["luachon"] == 1) $tongdung ++;
  }
  if ($tongcau) $tongdiem = round($tongdung * 10 / $tongcau, 1);
  else $tongdiem = 0;
  return $tongdiem;
}

function xacnhanthitiep() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();

  $sql = "select * from pet_tracnghiem_baithi where idnhanvien = $idnhanvien order by thoigian desc limit 1";
  $baithi = $db->fetch($sql);

  $sql = "select * from pet_tracnghiem_bailam where idbaithi = $baithi[id] order by id asc";
  $danhsachbailam = $db->all($sql);

  $danhsachcauhoi = [];
  foreach ($danhsachbailam as $bailam) {
    if (empty($danhsachcauhoi[$bailam["idcauhoi"]])) {
      $sql = "select * from pet_tracnghiem_cauhoi where id = $bailam[idcauhoi]";
      $danhsachcauhoi[$bailam["idcauhoi"]] = $db->fetch($sql);
      $danhsachcauhoi[$bailam["idcauhoi"]]["cautraloi"] = [];
    }
    $sql = "select * from pet_tracnghiem_cautraloi where id = $bailam[idcautraloi]";
    $cautraloi = $db->fetch($sql);
    $danhsachcauhoi[$bailam["idcauhoi"]]["cautraloi"] []= $cautraloi;
    if ($bailam["luachon"] == "1") $danhsachcauhoi[$bailam["idcauhoi"]]["luachon"] = count($danhsachcauhoi[$bailam["idcauhoi"]]["cautraloi"]) - 1;
  }

  $danhsach = [];
  foreach ($danhsachcauhoi as $cauhoi) {
    $danhsach []= $cauhoi;
  }

  $result["status"] = 1;
  $result["bailam"] = [
    "idbaithi" => $baithi["id"],
    "nopbai" => intval($baithi["nopbai"]),
    "danhsach" => $danhsach,
    "han" => $baithi["thoigian"] + $data->thoigian * 60,
  ];
  return $result;
}

function chondapan() {
  global $data, $db, $result;

  $sql = "update pet_tracnghiem_bailam set luachon = 0 where idbaithi = $data->idbaithi and idcauhoi = $data->idcauhoi";
  $db->query($sql);

  $sql = "update pet_tracnghiem_bailam set luachon = 1 where idbaithi = $data->idbaithi and idcautraloi = $data->idcautraloi";
  $db->query($sql);

  $result["status"] = 1;
  return $result;
}

function ketquathi() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();
  if ($idnhanvien == 1 || $idnhanvien == 5) {
    $sql = "select a.*, fullname as nguoithi from pet_tracnghiem_baithi a inner join pet_". PREFIX ."_users b on a.idnhanvien = b.userid where nopbai = 1 order by id desc limit 10 offset ". ($data->trang - 1) * 10;
  }
  else {
    $sql = "select a.*, fullname as nguoithi from pet_tracnghiem_baithi a inner join pet_". PREFIX ."_users b on a.idnhanvien = b.userid where idnhanvien = $idnhanvien and nopbai = 1 order by id desc limit 10 offset ". ($data->trang - 1) * 10;
  }
  $danhsachbaithi = $db->all($sql);

  foreach ($danhsachbaithi as $thutu => $baithi) {
    $tongcau = 0;
    $tongdung = 0;
    $sql = "select a.luachon from pet_tracnghiem_bailam a inner join pet_tracnghiem_cautraloi b on a.idcautraloi = b.id and b.dapan = 1 and idbaithi = $baithi[id] order by a.id desc";
    $danhsachcauhoi = $db->all($sql);
  
    foreach ($danhsachcauhoi as $cauhoi) {
      $tongcau ++;
      if ($cauhoi["luachon"] == 1) $tongdung ++;
    }
    if ($tongcau) $tongdiem = round($tongdung * 10 / $tongcau, 1);
    else $tongdiem = 0;
    $danhsachbaithi[$thutu]["diemthi"] = $tongdiem;
    $danhsachbaithi[$thutu]["ngaythi"] = date("d/m/Y H:i", $baithi["thoigian"]);
  }

  $result["status"] = 1;
  $result["danhsach"] = $danhsachbaithi;
  return $result;
}

function chitiet() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();
  $sql = "select * from pet_tracnghiem_baithi where nopbai = 1 and idnhanvien = $idnhanvien and idchuyenmuc = $data->idchuyenmuc order by id desc";
  $danhsachbaithi = $db->all($sql);

  foreach ($danhsachbaithi as $thutu => $baithi) {
    $tongcau = 0;
    $tongdung = 0;
    $sql = "select a.luachon from pet_tracnghiem_bailam a inner join pet_tracnghiem_cautraloi b on a.idcautraloi = b.id and b.dapan = 1 and idbaithi = $baithi[id] order by a.id desc";
    $danhsachcauhoi = $db->all($sql);
  
    foreach ($danhsachcauhoi as $cauhoi) {
      $tongcau ++;
      if ($cauhoi["luachon"] == 1) $tongdung ++;
    }
    if ($tongcau) $tongdiem = round($tongdung * 10 / $tongcau, 1);
    else $tongdiem = 0;
    $danhsachbaithi[$thutu]["diemthi"] = $tongdiem;
    $danhsachbaithi[$thutu]["ngaythi"] = date("d/m/Y H:i", $baithi["thoigian"]);
  }

  $result["status"] = 1;
  $result["danhsach"] = $danhsachbaithi;
  return $result;
}

function chitietbaithi() {
  global $data, $db, $result;

  $sql = "select * from pet_tracnghiem_baithi where id = $data->idbaithi order by thoigian desc limit 1";
  $baithi = $db->fetch($sql);

  $sql = "select * from pet_tracnghiem_bailam where idbaithi = $baithi[id] order by id asc";
  $danhsachbailam = $db->all($sql);

  $danhsachcauhoi = [];
  foreach ($danhsachbailam as $bailam) {
    if (empty($danhsachcauhoi[$bailam["idcauhoi"]])) {
      $sql = "select * from pet_tracnghiem_cauhoi where id = $bailam[idcauhoi]";
      $danhsachcauhoi[$bailam["idcauhoi"]] = $db->fetch($sql);
      $danhsachcauhoi[$bailam["idcauhoi"]]["cautraloi"] = [];
    }
    $sql = "select * from pet_tracnghiem_cautraloi where id = $bailam[idcautraloi]";
    $cautraloi = $db->fetch($sql);
    $danhsachcauhoi[$bailam["idcauhoi"]]["cautraloi"] []= $cautraloi;
    if ($bailam["luachon"] == "1") $danhsachcauhoi[$bailam["idcauhoi"]]["luachon"] = count($danhsachcauhoi[$bailam["idcauhoi"]]["cautraloi"]) - 1;
  }

  $danhsach = [];
  foreach ($danhsachcauhoi as $cauhoi) {
    $danhsach []= $cauhoi;
  }

  $result["status"] = 1;
  $result["bailam"] = [
    "idbaithi" => $baithi["id"],
    "nopbai" => intval($baithi["nopbai"]),
    "danhsach" => $danhsach,
    "thoigian" => $baithi["thoigian"]
  ];
  return $result;
}

function capnhatdethi() {
  global $data, $db, $result;

  $dulieu = $data->dulieu;
  if ($dulieu->id) {
    $sql = "update pet_tracnghiem_dethi set tendethi = '$dulieu->tendethi' where id = $dulieu->id";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_tracnghiem_dethi (tendethi, hienthi) values('$dulieu->tendethi', 1)";
    $dulieu->id = $db->insertid($sql);
  }

  $danhsachchuyenmuc = [];
  foreach ($dulieu->danhsachchuyenmuc as $idchuyenmuc => $giatri) {
    if ($giatri) {
      $sql = "select id from pet_tracnghiem_dethi_chuyenmuc where idchuyenmuc = $idchuyenmuc and iddethi = $dulieu->id";
      if (empty($db->fetch($sql))) {
        $sql = "insert into pet_tracnghiem_dethi_chuyenmuc (idchuyenmuc, iddethi) values($idchuyenmuc, $dulieu->id)";
        $db->query($sql);
      }

      $danhsachchuyenmuc[]= $idchuyenmuc;
    }
  }

  if (count($danhsachchuyenmuc)) {
    $sql = "delete from pet_tracnghiem_dethi_chuyenmuc where iddethi = $dulieu->id and idchuyenmuc not in (". implode(", ", $danhsachchuyenmuc) .")";
  }
  else {
    $sql = "delete from pet_tracnghiem_dethi_chuyenmuc where iddethi = $dulieu->id";
  }
  $db->query($sql);

  $result["status"] = 1;
  $result['danhsach'] = danhsachdethi();
  return $result;
}

function capnhatchuyenmuc() {
  global $data, $db, $result;

  // nếu có id, cập nhật tên chuyên muc
  // thêm các câu hỏi vào
  $dulieu = $data->dulieu;
  $thoigian = intval($dulieu->thoigian);
  if ($dulieu->id) {
    $sql = "update pet_tracnghiem_chuyenmuc set nhanvien = '$dulieu->nhanvien', tenchuyenmuc = '$dulieu->tenchuyenmuc', socau = $dulieu->socau, thoigian = $thoigian where id = $dulieu->id";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_tracnghiem_chuyenmuc (tenchuyenmuc, nhanvien, socau, thoigian) values('$dulieu->tenchuyenmuc', '$dulieu->nhanvien', $dulieu->socau, $dulieu->thoigian)";
    $dulieu->id = $db->insertid($sql);
  }

  $danhsachidcauhoi = [];
  foreach ($dulieu->cauhoi as $cauhoi) {
    if ($cauhoi->id) {
      $sql = "update pet_tracnghiem_cauhoi set noidung = '$cauhoi->noidung', hinhanh = '$cauhoi->hinhanh' where id = $cauhoi->id";
      $db->query($sql);
    }
    else {
      $sql = "insert into pet_tracnghiem_cauhoi (idchuyenmuc, noidung, hinhanh) values($dulieu->id, '$cauhoi->noidung', '$cauhoi->hinhanh')";
      $cauhoi->id = $db->insertid($sql);
    }
    $danhsachidcauhoi []= $cauhoi->id;

    foreach ($cauhoi->danhsach as $thutu => $cautraloi) {
      if ($cautraloi->id) $sql = "update pet_tracnghiem_cautraloi set noidung = '$cautraloi->noidung' where id = $cautraloi->id";
      else $sql = "insert into pet_tracnghiem_cautraloi (idcauhoi, noidung, dapan) values($cauhoi->id, '$cautraloi->noidung', $cautraloi->dapan)";
      $db->query($sql);
    }
  }

  if (!empty($danhsachidcauhoi)) {
    $sql = "update pet_tracnghiem_cauhoi set hienthi = 0 where idchuyenmuc = $dulieu->id and id not in (". implode(", ", $danhsachidcauhoi) .")";
    $db->query($sql);
  }

  $result["status"] = 1;
  $result['danhsach'] = danhsachchuyenmuc();
  return $result;
}

function dulieuchuyenmuc() {
  global $data, $db, $result;

  $sql = "select * from pet_tracnghiem_chuyenmuc where id = $data->idchuyenmuc";
  $chuyenmuc = $db->fetch($sql);

  $sql = "select id, noidung, hinhanh from pet_tracnghiem_cauhoi where idchuyenmuc = $data->idchuyenmuc and hienthi = 1 order by id asc";
  $danhsachcauhoi = $db->all($sql);

  foreach ($danhsachcauhoi as $thutu => $cauhoi) {
    $sql = "select id, noidung, dapan from pet_tracnghiem_cautraloi where idcauhoi = $cauhoi[id] order by id asc";
    $danhsachcauhoi[$thutu]["danhsach"] = $db->all($sql);
  }

  $result["status"] = 1;
  $result['chuyenmuc'] = ["id" => $data->idchuyenmuc, "tenchuyenmuc" => $chuyenmuc["tenchuyenmuc"], "socau" => $chuyenmuc["socau"], "thoigian" => $chuyenmuc["thoigian"], "cauhoi" => $danhsachcauhoi];
  return $result;
}

function dulieudethi() {
  global $data, $db, $result;

  $sql = "select * from pet_tracnghiem_dethi where id = $data->iddethi";
  $dethi = $db->fetch($sql);

  $sql = "select idchuyenmuc, 1 as kiemtra from pet_tracnghiem_dethi_chuyenmuc a inner join pet_tracnghiem_chuyenmuc b on a.idchuyenmuc = b.id where b.hienthi = 1";
  $danhsachchuyenmuc = $db->obj($sql, "idchuyenmuc", "kiemtra");

  $result["status"] = 1;
  $result['dethi'] = ["id" => $data->iddethi, "tendethi" => $dethi["tendethi"], "danhsachchuyenmuc" => $danhsachchuyenmuc];
  return $result;
}

function xoachuyenmuc() {
  global $data, $db, $result;

  $sql = "update pet_tracnghiem_chuyenmuc set hienthi = 0 where id = $data->idchuyenmuc";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachchuyenmuc();
  return $result;
}

function xoadethi() {
  global $data, $db, $result;

  $sql = "update pet_tracnghiem_dethi set hienthi = 0 where id = $data->iddethi";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdethi();
  return $result;
}

// function hoiphucchuyenmuc() {
//   global $data, $db, $result;

//   $sql = "update pet_tracnghiem_chuyenmuc set hienthi = 1 where id = $data->idchuyenmuc";
//   $db->query($sql);

//   $result['status'] = 1;
//   $result['danhsach'] = danhsachchuyenmuc();
//   return $result;
// }

function tailendanhsach() {
  global $data, $db, $result, $_FILES;

  // đọc dữ liệu từ file excel chuyển thành dữ liệu 
  $raw = $_FILES['file']['tmp_name'];
  include DIR .'include/PHPExcel/IOFactory.php';
  $inputFileType = PHPExcel_IOFactory::identify($raw);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($raw);
  $sheet = $objPHPExcel->getSheet(0); 
  $dongcuoi = $sheet->getHighestRow(); 

  $danhsachcauhoi = array();
  for ($i = 2; $i <= $dongcuoi; $i ++) {
    $danhsachcauhoi []= [
      "id" => 0,
      "noidung" => $sheet->getCell("A$i")->getValue(),
      "danhsach" => [
        ["id" => 0, "dapan" => 1, "noidung" => $sheet->getCell("B$i")->getValue()],
        ["id" => 0, "dapan" => 0, "noidung" => $sheet->getCell("C$i")->getValue()],
        ["id" => 0, "dapan" => 0, "noidung" => $sheet->getCell("D$i")->getValue()],
        ["id" => 0, "dapan" => 0, "noidung" => $sheet->getCell("E$i")->getValue()],
      ]
    ];
  }

  $result['status'] = 1;
  $result['cauhoi'] = $danhsachcauhoi;
  return $result;
}
