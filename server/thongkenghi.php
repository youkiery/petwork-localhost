<?php
function khoitao() {
  global $data, $db, $result;
  
  $danhsachngay = [];
  
  $tungay = isodatetotime($data->tungay);
  $denngay = isodatetotime($data->denngay);
  while ($tungay <= $denngay) {
    $danhsachngay []= date("d/m", $tungay);
    $tungay += 60 * 60 * 24;
  }

  $result['status'] = 1;
  $result['danhsach'] = dulieuthongke();
  $result['danhsachngay'] = $danhsachngay;
  $result['dachon'] = danhsachdachon();
  return $result;
}

function khoitaocauhinh() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['cauhinh'] = danhsachcauhinh();
  return $result;
}

function danhsachcauhinh() {
  global $data, $db, $result;
  
  $sql = "select * from pet_". PREFIX ."_lichchamcongca order by id";
  $cauhinh = $db->all($sql);
  foreach ($cauhinh as $key => $giatri) {
    $cauhinh[$key]["giovao"] = date("Y-m-d") . "T". date("H:i:s", $giatri["giovao"]) . ".000+07:00";
    $cauhinh[$key]["giora"] = date("Y-m-d") . "T". date("H:i:s", $giatri["giora"]) . ".000+07:00";
  }
  return $cauhinh;
}

function xoacatruc() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_lichchamcongca where id = $data->id";
  $db->query($sql);

  foreach ($data->cauhinh as $dulieu) {
    $giovao = isodatetotimev2($dulieu->giovao);
    $giora = isodatetotimev2($dulieu->giora);
    if ($dulieu->id) $sql = "update pet_". PREFIX ."_lichchamcongca set giovao = $giovao, giora = $giora where id = $dulieu->id";
    else $sql = "insert into pet_". PREFIX ."_lichchamcongca (giovao, giora) values ($giovao, $giora)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['cauhinh'] = danhsachcauhinh();
  return $result;
}

function luucatruc() {
  global $data, $db, $result;
  
  foreach ($data->cauhinh as $dulieu) {
    $giovao = isodatetotimev2($dulieu->giovao);
    $giora = isodatetotimev2($dulieu->giora);
    if ($dulieu->id) $sql = "update pet_". PREFIX ."_lichchamcongca set giovao = $giovao, giora = $giora where id = $dulieu->id";
    else $sql = "insert into pet_". PREFIX ."_lichchamcongca (giovao, giora) values ($giovao, $giora)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['cauhinh'] = danhsachcauhinh();
  return $result;
}

function xacnhandachon() {
  global $data, $db, $result;
  
  $tungay = isodatetotime($data->tungay);
  $denngay = isodatetotime($data->denngay) + 60 * 60 * 24 - 1;
  $nam = date("Y", $tungay);
  $sql = "delete from pet_". PREFIX ."_lichchamcongdaccach where thoigian between $tungay and $denngay";
  $db->query($sql);

  foreach ($data->dulieu as $idnhanvien => $dulieu) {
    if ($dulieu) {
      foreach ($dulieu as $ngay => $giatri) {
        $datestring = explode("/", $ngay);
        $thoigian = strtotime("$nam/$datestring[1]/$datestring[0]");
        if ($giatri) {
          $sql = "insert into pet_". PREFIX ."_lichchamcongdaccach (idnhanvien, thoigian) values($idnhanvien, $thoigian)";
          $db->query($sql);
        }
      }
    }
  }

  $result['status'] = 1;
  $result['dachon'] = danhsachdachon();
  $result['danhsach'] = dulieuthongke();
  return $result;
}

function danhsachdachon() {
  global $data, $db, $result;
  
  $tungay = isodatetotime($data->tungay);
  $denngay = isodatetotime($data->denngay) + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_lichchamcongdaccach where thoigian between $tungay and $denngay";
  $danhsach = $db->all($sql);
  $dulieu = [];

  foreach ($danhsach as $thongtin) {
    $ngay = date("d/m", $thongtin["thoigian"]);
    if (empty($dulieu[$thongtin["idnhanvien"]])) $dulieu[$thongtin["idnhanvien"]] = [];
    if (empty($dulieu[$thongtin["idnhanvien"]][$ngay])) $dulieu[$thongtin["idnhanvien"]][$ngay] = 1;
  }

  return $dulieu;
}

function dulieuthongke() {
  global $data, $db, $result;

  $tungay = isodatetotime($data->tungay) * 1000;
  $denngay = (isodatetotime($data->denngay) + 60 * 60 * 24) * 1000 - 1;

  $sql = "select a.time as thoigian, b.userid as idnhanvien from hanet_khachhang a inner join pet_". PREFIX ."_users b on a.personID = b.faceid where a.personType = 0 and (a.time between $tungay and $denngay)";
  $danhsach = $db->all($sql);

  $danhsachca = [];
  $sql = "select * from pet_". PREFIX ."_lichchamcongca order by id";
  $danhsachlichcong = $db->all($sql);
  foreach ($danhsachlichcong as $lichcong) {
    $danhsachca []= [
      $lichcong["giovao"] - strtotime(date("Y/m/d", $lichcong["giovao"])),
      $lichcong["giora"] - strtotime(date("Y/m/d", $lichcong["giora"])),
    ];
  }

  $dulieuchamcong = [];
  $ketquachamcong = [];

  $danhsach []= [
    "idnhanvien" => $idnhanvien,
    "tennhanvien" => count($hoten) ? $hoten[count($hoten) - 1] : '',
    "dulieu" => $ketquachamcong[$idnhanvien],
    "tongtre" => $tongtre,
    "tongkhongcham" => $tongkhongcham, // không chấm theo buổi
    "tongkhongcong" => $tongkhongcong, // không chấm theo ngày
  ];

  return $danhsach;
}

function chuyendoiphut($thoigian) {
  $phut = floor($thoigian / 60);
  $giay = floor($thoigian - $phut * 60);
  $phutgiay = [];
  if ($phut) $phutgiay []= $phut ."'";
  if ($giay) $phutgiay []= $giay ."s";
  return implode("", $phutgiay);
}

function chuyendoi($thoigian) {
  $x = strtotime(date("Y/m/d"));
  return date("H:i", $x + $thoigian);
}

function sosanhhoply($a, $b) {
  return $a[2] > $b[2];
}

function sosanhcapdo($a, $b) {
  return $a[3] > $b[3];
}

function tailen() {
  global $data, $db, $result, $_FILES;
  
  $filename = $_FILES['file']['tmp_name'];
  $handle = fopen($filename, "r");

  $sql = "select * from pet_". PREFIX ."_users where idvantay > 0";
  $nhanvien = $db->obj($sql, "idvantay", "userid");
  
  
  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $data = explode("\t", $line);
      if (isset($nhanvien[intval($data[2])])) {
        $idnhanvien = $nhanvien[intval($data[2])];
        $thoigian = strtotime($data[6]);
        if (empty($thoigian)) continue;

        $sql = "select * from pet_". PREFIX ."_lichchamcong where idnhanvien = $idnhanvien and thoigian = $thoigian";
        if (empty($db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_lichchamcong (idnhanvien, thoigian) values($idnhanvien, $thoigian)";
          $db->query($sql);
        }
      }
    }
  }
  $result['status'] = 1;
  $result['messenger'] = "Đã tải dữ liệu lên";
  return $result;
} 
