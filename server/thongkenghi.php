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

  $tungay = isodatetotime($data->tungay);
  $denngay = isodatetotime($data->denngay) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_user_per where module = 'schedule' and type > 0 group by userid";
  $danhsachnhanvien = $db->all($sql);

  $dulieuchamcong = [];
  $ketquachamcong = [];

  // chạy từng ngày, nếu nhân viên nghỉ thì bỏ qua, nếu không thì kiểm tra nhân viên đã vào chưa
  // nếu nhân viên vào lúc 7h thì ra lúc 11h30 hoặc 17h30 hoặc 19h
  // nếu nhân viên vào lúc 11h thì ra lúc 19h
  // nếu nhân viên vào lúc 13h thì ra lúc 17h30
  $homnay = strtotime(date("Y/m/d"));

  while ($tungay < $denngay) {
    $cuoingay = $tungay + 60 * 60 * 24 - 1;
    foreach ($danhsachnhanvien as $nhanvien) {
      if (empty($dulieuchamcong[$nhanvien["userid"]])) {
        $sql = "select * from pet_". PREFIX ."_users where userid = $nhanvien[userid]";
        $dulieunhanvien = $db->fetch($sql);
        $dulieuchamcong[$nhanvien["userid"]] = [
          "userid" => $dulieunhanvien["userid"],
          "faceid" => $dulieunhanvien["faceid"],
          "hoten" => $dulieunhanvien["fullname"],
          "chenhlech" => 0,
          "chamcong" => []
        ];
      }
      $faceid = $dulieuchamcong[$nhanvien["userid"]]["faceid"];
      $sql = "select type, 1 as value from pet_". PREFIX ."_row where type > 1 and user_id = $nhanvien[userid] and (time between $tungay and $cuoingay)";
      $ngaynghi = $db->obj($sql, "type", "value");

      if ($tungay >= $homnay || (isset($ngaynghi[2]) && isset($ngaynghi[3]))) {
        $dulieuchamcong[$nhanvien["userid"]]["chamcong"] []= ["vao" => 0, "ra" => 0, "chenhlech" => 0, "mau" => "gray"];
        continue;
      }
      if (isset($ngaynghi[2])) {
        // nghỉ sáng, lấy lịch từ 13h15 - 17h30
        $vao = ($tungay + 13 * 60 * 60 + 15 * 60) * 1000;
        $ra = ($tungay + 17 * 60 * 60 + 30 * 60) * 1000;

        $chamcong = kiemtrachamcong($faceid, $vao, $ra);
        $dulieuchamcong[$nhanvien["userid"]]["chenhlech"] += $chamcong["chenhlech"];
        $dulieuchamcong[$nhanvien["userid"]]["chamcong"] []= $chamcong;
        continue;
      }
      if (isset($ngaynghi[3])) {
        // nghỉ chiều, lấy lịch từ 07h - 11h30 
        $vao = ($tungay + 7 * 60 * 60) * 1000;
        $ra = ($tungay + 11 * 60 * 60 + 30) * 1000;

        $chamcong = kiemtrachamcong($faceid, $vao, $ra);
        $dulieuchamcong[$nhanvien["userid"]]["chenhlech"] += $chamcong["chenhlech"];
        $dulieuchamcong[$nhanvien["userid"]]["chamcong"] []= kiemtrachamcong($faceid, $vao, $ra);
        continue;
      }
      // không nghỉ
      // nếu đến lúc 07h thì về lúc 17h30
      // nếu đến lúc 9h thì về lúc 19h
      // nếu đến lúc 11h thì về lúc 19h
      $chamcong = [0 => [], [], []];
      $chenhlech = [];
      $vao = ($tungay + 7 * 60 * 60) * 1000;
      $ra = ($tungay + 17 * 60 * 60 + 30) * 1000;
      $chamcong[0] = kiemtrachamcong($faceid, $vao, $ra);
      $chenhlech[$chamcong[0]["chenhlech"]] = 0;

      $vao = ($tungay + 9 * 60 * 60) * 1000;
      $ra = ($tungay + 19 * 60 * 60) * 1000;
      $chamcong[1] = kiemtrachamcong($faceid, $vao, $ra);
      $chenhlech[$chamcong[1]["chenhlech"]] = 1;

      $vao = ($tungay + 11 * 60 * 60) * 1000;
      $ra = ($tungay + 19 * 60 * 60 + 30) * 1000;
      $chamcong[2] = kiemtrachamcong($faceid, $vao, $ra);
      $chenhlech[$chamcong[2]["chenhlech"]] = 2;

      ksort($chenhlech);
      
      $chonchamcong = $chamcong[$chenhlech[array_key_first($chenhlech)]];
      $dulieuchamcong[$nhanvien["userid"]]["chenhlech"] += $chonchamcong["chenhlech"];
      $dulieuchamcong[$nhanvien["userid"]]["chamcong"] []= $chonchamcong;
        // echo date("d.m.Y", $tungay) . " 4 <br>";
    }

    $tungay += 60 * 60 * 24;
  }

  sort($dulieuchamcong);
  return $dulieuchamcong;
}

function kiemtrachamcong($faceid, $vao, $ra) {
  global $db;

  $dulieu = ["vao" => 0, "ra" => 0, "chenhlech" => 0, "mau" => "gray"];
  $sql = "select * from pet_cauhinh where tenbien = 'cauhinhgiamsat-$data->idchinhanh'";
  $thietbi = $db->fetch($sql);
  if (empty($thietbi)) return $dulieu;
  $idthietbi = $thietbi["giatri"];

  // kiểm tra xem nhân viên đó vào ra như thế nào, trả về thời gian lệch gần nhất
  $daungay = strtotime(date("Y/m/d", $vao)) * 1000;
  $cuoingay = $daungay * 24 * 24 * 60 * 1000 - 1;
  $sql = "select * from hanet_khachhang where placeID = $idthietbi and personType = 0 and personID = $faceid and (time between $daungay and $vao) order by time desc limit 1";
  $chamvaotruoc = $db->fetch($sql);

  $sql = "select * from hanet_khachhang where placeID = $idthietbi and personType = 0 and personID = $faceid and (time between $vao and $ra) order by time asc limit 1";
  $chamvaosau = $db->fetch($sql);

  if (!empty($chamvaotruoc) && ($vao - $chamvaotruoc["time"]) < 60 * 60 * 1000) { // chấm vào trước 1 giờ mới hợp lệ
    $dulieu["vao"] = $chamvaotruoc["time"];
  }
  else if (!empty($chamvaosau) && ($chamvaosau["time"] - $vao) < 60 * 60 * 4 * 1000) {
    $dulieu["chenhlech"] += $chamvaosau["time"] - $vao;
    $dulieu["vao"] = $chamvaosau["time"];
  }
  else $dulieu["vao"] = 0;

  $sql = "select * from hanet_khachhang where placeID = $idthietbi and personType = 0 and personID = $faceid and (time between $vao and $ra) order by time desc limit 1";
  $chamratruoc = $db->fetch($sql);

  $sql = "select * from hanet_khachhang where placeID = $idthietbi and personType = 0 and personID = $faceid and (time between $ra and $cuoingay) order by time asc limit 1";
  $chamrasau = $db->fetch($sql);

  if (!empty($chamrasau) && ($chamrasau["time"] - $ra) < 60 * 60 * 1000) { // chấm ra trước 1 giờ mới hợp lệ
    $dulieu["ra"] = $chamrasau["time"];
  }
  else if (!empty($chamratruoc) && ($ra - $chamratruoc["time"]) < 60 * 60 * 4 * 1000) {
    $dulieu["chenhlech"] += $ra - $chamratruoc["time"];
    $dulieu["ra"] = $chamratruoc["time"];
  }
  else $dulieu["ra"] = 0;

  if (!empty($dulieu["vao"]) && !empty($dulieu["ra"]) && empty($dulieu["chenhlech"])) $dulieu["mau"] = "green";
  else if (empty($dulieu["vao"]) && empty($dulieu["ra"])) $dulieu["mau"] = "red";
  else if (empty($dulieu["vao"]) || empty($dulieu["ra"]) || !empty($dulieu["chenhlech"])) $dulieu["mau"] = "orange";

  return $dulieu;  
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

function dulieuchamcong() {
  global $data, $db;

  $daungay = strtotime(date("Y/m/d")) * 1000;
  $cuoingay = $daungay + 60 * 60 * 24 * 1000 - 1;

  $dulieu = [
    "hinhanh" => "",
    "thoigianvao" => "",
    "thoigianra" => ""
  ];

  $sql = "select * from pet_cauhinh where tenbien = 'cauhinhgiamsat-$data->idchinhanh'";
  $thietbi = $db->fetch($sql);
  if (empty($thietbi)) return $dulieu;
  $idthietbi = $thietbi["giatri"];

  $sql = "select * from pet_". PREFIX ."_users where userid = $data->userid";
  $nhanvien = $db->fetch($sql);

  $sql = "select * from hanet_khachhang where placeID = $idthietbi and personId = $nhanvien[faceid] and (time between $daungay and $cuoingay) order by time desc limit 1";
  $chamcongcuoi = $db->fetch($sql);

  if (!empty($chamcongcuoi)) {
    $dulieu["hinhanh"] = $chamcongcuoi["avatar"];
    $dulieu["thoigianra"] = date("H:i:s", floor($chamcongcuoi["time"] / 1000));
  }

  $sql = "select * from hanet_khachhang where placeID = $idthietbi and personId = $nhanvien[faceid] and (time between $daungay and $cuoingay) order by time asc limit 1";
  $chamcongcuoi = $db->fetch($sql);

  if (!empty($chamcongcuoi)) {
    $dulieu["thoigianvao"] = date("H:i:s", floor($chamcongcuoi["time"] / 1000));
  }

  return $dulieu;
}

function kiemtrachamconghomnay() {
  global $data, $db, $result, $_FILES;
 
  $result['status'] = 1;
  $result['dulieu'] = dulieuchamcong();
  return $result;
} 

function khoitaochamcong() {
  global $data, $db, $result;
  
  $daungay = isodatetotime($data->thoigian) * 1000;
  $cuoingay = ($daungay + 60 * 60 * 24 * 1000) - 1;
  
  $sql = "select * from pet_cauhinh where tenbien = 'cauhinhgiamsat-$data->idchinhanh'";
  $thietbi = $db->fetch($sql);
  if (empty($thietbi)) return ["status" => 1, "danhsach" => [], "dulieu" => []];
  $idthietbi = $thietbi["giatri"];

  $sql = "select * from pet_". PREFIX ."_users where userid = $data->userid";
  $nhanvien = $db->fetch($sql);

  $sql = "select time as thoigian from hanet_khachhang where personId = $nhanvien[faceid] and (time between $daungay and $cuoingay) order by time asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $khachhang) {
    $danhsach[$thutu]["thoigian"] = date("H:i:s", floor($khachhang["thoigian"] / 1000));
  }

  $result['status'] = 1;
  $result['danhsach'] = $danhsach;
  $result['dulieu'] = dulieuchamcong();
  return $result;
}
