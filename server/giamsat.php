<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

  $result['status'] = 1;
  $result['danhsachgiamsat'] = danhsachgiamsat($data->idchinhanh);
  $result['danhsachchinhanh'] = $danhsachchinhanh;
  $result['chinhanh'] = $data->idchinhanh;
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
        if (abs($thoigiandau) < abs($thoigiancuoi)) $dasudung[$dau] = 1;
        else $dasudung[$cuoi] = 1;
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
      "thoigianhoadon" => (empty($hoadon) ? "" : date("d/m/Y H:i", $hoadon["thoigian"])),
      "khachhang" => (empty($hoadon) ? "" : date("d/m/Y H:i", $hoadon["thoigian"])),
    ];

    $dulieu []= $checkin;
  }

  return $dulieu;
}
