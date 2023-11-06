<?php
function khoitao() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();
  $sql = "select * from pet_tracnghiem_baithi where idnhanvien = $idnhanvien and nopbai = 0 order by thoigian desc limit 1";
  if (empty($baithi = $db->fetch($sql))) $result['baithicuoi'] = 0;
  else $result['baithicuoi'] = intval($baithi["thoigian"]);

  $result['status'] = 1;
  $result['danhsach'] = danhsachchuyenmuc();
  return $result;
}

function danhsachchuyenmuc() {
  global $data, $db;

  $userid = checkuserid();
  $sql = "select * from pet_tracnghiem_chuyenmuc order by id desc";
  $danhsachchuyenmuc = $db->all($sql);
  
  foreach ($danhsachchuyenmuc as $thutu => $chuyenmuc) {
    $sql = "select * from pet_tracnghiem_baithi where idchuyenmuc = $chuyenmuc[id] and idnhanvien = $userid and nopbai = 1 order by id desc";
    $danhsachbaithi = $db->all($sql);
    $danhsachchuyenmuc[$thutu]["diemgannhat"] = -1;
    $danhsachchuyenmuc[$thutu]["lanthigannhat"] = "không có";
    $danhsachchuyenmuc[$thutu]["diemcaonhat"] = 0;
    $danhsachchuyenmuc[$thutu]["solanthi"] = 0;
    foreach ($danhsachbaithi as $baithi) {
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
      if ($danhsachchuyenmuc[$thutu]["diemgannhat"] < 0) {
        $danhsachchuyenmuc[$thutu]["diemgannhat"] = $tongdiem;
        $danhsachchuyenmuc[$thutu]["lanthigannhat"] = date("d/m/Y H:i", $baithi["thoigian"]);
      }
      if ($danhsachchuyenmuc[$thutu]["diemcaonhat"] < $tongdiem) $danhsachchuyenmuc[$thutu]["diemcaonhat"] = $tongdiem;
      $danhsachchuyenmuc[$thutu]["solanthi"] ++;
    }
    if ($danhsachchuyenmuc[$thutu]["diemgannhat"] < 0) $danhsachchuyenmuc[$thutu]["diemgannhat"] = 0;
  }

  return $danhsachchuyenmuc;
}

function batdauthi() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();
  $thoigian = time();

  $sql = "update pet_tracnghiem_baithi set nopbai = 1 where idnhanvien = $idnhanvien";
  $db->query($sql);

  $sql = "select * from pet_tracnghiem_cauhoi where idchuyenmuc = $data->idchuyenmuc order by rand() limit 10";
  $danhsachcauhoi = $db->all($sql);

  $sql = "insert into pet_tracnghiem_baithi (idnhanvien, idchuyenmuc, thoigian) values($idnhanvien, $data->idchuyenmuc, $thoigian)";
  $idbaithi = $db->insertid($sql);
  
  foreach ($danhsachcauhoi as $thutucauhoi => $cauhoi) {
    $sql = "select * from pet_tracnghiem_cautraloi where idcauhoi = $cauhoi[id] order by rand() limit 4";
    $danhsachcauhoi[$thutucauhoi]["cautraloi"] = $db->all($sql);

    foreach ($danhsachcauhoi[$thutucauhoi]["cautraloi"] as $thutu => $cautraloi) {
      $sql = "insert into pet_tracnghiem_bailam (idbaithi, idcauhoi, idcautraloi, luachon) values($idbaithi, $cauhoi[id], $cautraloi[id], 0)";
      $db->query($sql);
    }
  }
  
  $sql = "select * from pet_tracnghiem_baithi where idnhanvien = $idnhanvien and nopbai = 0 order by thoigian desc limit 1";
  if (empty($baithi = $db->fetch($sql))) $result['baithicuoi'] = 0;
  else $result['baithicuoi'] = intval($baithi["thoigian"]);

  $result["status"] = 1;
  $result["bailam"] = [
    "idbaithi" => $idbaithi,
    "nopbai" => 0, 
    "danhsach" => $danhsachcauhoi,
    "thoigian" => $thoigian
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
    "thoigian" => $baithi["thoigian"]
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

function chitiet() {
  global $data, $db, $result;

  $idnhanvien = checkuserid();
  $sql = "select * from pet_tracnghiem_baithi where idnhanvien = $idnhanvien and idchuyenmuc = $data->idchuyenmuc order by id desc";
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

function capnhatchuyenmuc() {
  global $data, $db, $result;

  // nếu có id, cập nhật tên chuyên muc
  // thêm các câu hỏi vào
  $dulieu = $data->dulieu;
  if ($dulieu->id) {
    $sql = "update pet_tracnghiem_chuyenmuc set tenchuyenmuc = '$dulieu->tenchuyenmuc'";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_tracnghiem_chuyenmuc (tenchuyenmuc) values('$dulieu->tenchuyenmuc')";
    $dulieu->id = $db->insertid($sql);
  }

  foreach ($dulieu->cauhoi as $cauhoi) {
    $sql = "insert into pet_tracnghiem_cauhoi (idchuyenmuc, noidung) values($dulieu->id, '$cauhoi->noidung')";
    $idcauhoi = $db->insertid($sql);

    foreach ($cauhoi->danhsach as $thutu => $cautraloi) {
      if ($thutu == 0) $dapan = 1;
      else $dapan = 0;
      $sql = "insert into pet_tracnghiem_cautraloi (idcauhoi, noidung, dapan) values($idcauhoi, '$cautraloi', $dapan)";
      $db->query($sql);
    }
  }

  $result["status"] = 1;
  $result['danhsach'] = danhsachchuyenmuc();
  return $result;
}
