<?php
$thietbi = [1 => "20832", "20832", "20832", "20832", "20832"];

function khoitao() {
  global $data, $db, $result;
  
  $sql = "select * from pet_chinhanh where tiento <> '' order by id";
  $danhsachchinhanh = $db->all($sql);

  if (empty($data->idchinhanh)) {
    $sql = "select * from pet_chinhanh where tiento = '$data->tiento'";
    $chinhanh = $db->fetch($sql);
    $data->idchinhanh = $chinhanh["id"];
  }

  foreach ($danhsachchinhanh as $thutu => $chinhanh) {
    $sql = "select * from pet_cauhinh where tenbien = 'cauhinhgiamsat-$chinhanh[id]'";
    if (empty($cauhinh = $db->fetch($sql))) $cauhinh = ["giatri" => ""];
    $danhsachchinhanh[$thutu]["cauhinh"] = $cauhinh["giatri"];
  }

  $result['status'] = 1;
  $result['danhsachgiamsat'] = danhsachgiamsat($data->idchinhanh);
  $result['danhsachchinhanh'] = $danhsachchinhanh;
  $result['chinhanh'] = $data->idchinhanh;
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

function danhsachgiamsat($idchinhanh) {
  global $data, $db, $result, $thietbi;

  $batdau = strtotime($data->thoigian);
  $ketthuc = $batdau + 60 * 60 * 24 - 1;
  $batdau2 = $batdau * 1000;
  $ketthuc2 = $ketthuc * 1000;
  $idthietbi = $thietbi[$idchinhanh];

  $sql = "select *, -1 as lienket from hanet_khachhang where data_type = 'log' and personType > 0 and (time between $batdau2 and $ketthuc2) and placeID = $idthietbi order by time";
  $danhsachkhachhang = $db->all($sql);
  $tongkhachhang = count($danhsachkhachhang);
  
  $sql = "select *, -1 as lienket from hanet_hoadon where (thoigian between $batdau and $ketthuc) order by thoigian";
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
    $checkin = [
      "avatar" => $khachhang["avatar"],
      "thoigiancheckin" => date("d/m/Y H:i", $thoigian),
      "khachhangcheckin" => $khachhang["personName"],
      "thoigianhoadon" => (empty($hoadon) ? "" : date("d/m/Y H:i", $hoadon["thoigian"])),
      "khachhang" => (empty($hoadon) ? "" : $hoadon["khachhang"]),
    ];

    $dulieu []= $checkin;
  }

  return $dulieu;
}
