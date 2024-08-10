<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

include_once('./include/db.php');

$db = new database('localhost', 'root', '', 'thanhxuanpet');
$daungay = strtotime("2024-8-6");
$daungayt = $daungay * 1000;
$cuoingay = $daungay + 60 * 60 * 24 - 1;
$cuoingayt = $cuoingay * 1000;

$sql = "select * from hanet_khachhang where data_type = 'log' and personType > 0 and time between $daungayt and $cuoingayt order by time";
$danhsachkhachhang = $db->all($sql);
$tongkhachhang = count($danhsachkhachhang);

$sql = "select * from hanet_hoadon where (thoigian between $daungay and $cuoingay) order by thoigian";
$danhsachhoadon = $db->all($sql);
$dasudung = [];
$dau = 0;
$cuoi = 1;

foreach ($danhsachhoadon as $thutu => $hoadon) {
  // so sánh theo khách hàng hóa đơn, hiện chưa có thì bỏ qua
  // so sánh thời gian, tìm thời gian hóa đơn gần với thời gian checkin nhất

  $kiemtra = false;
  while (!$kiemtra && $cuoi < $tongkhachhang) {
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

$dau = 0;
foreach ($danhsachkhachhang as $thutu => $khachhang) {
  $thoigian = floor($khachhang["time"] / 1000);
  echo date("d/m/Y H:i", $thoigian);
  if (isset($dasudung[$thutu])) {
    echo " ". date("d/m/Y H:i", $danhsachhoadon[$dau]["thoigian"]);
    $dau ++;
  }
  echo "<br>";
}