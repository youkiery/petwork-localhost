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

  $sql = "select * from pet_". PREFIX ."_lichchamcong where thoigian between $tungay and $denngay";
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
  // $danhsachca = [
  //   [0 => 7 * 60 * 60, 11 * 60 * 60 + 30 * 60], // 07h00 - 11h30
  //   [0 => 13 * 60 * 60 + 15 * 60, 17 * 60 * 60 + 30 * 60], // 13h15 - 17h30
  //   [0 => 9 * 60 * 60, 18 * 60 * 60 + 30 * 60], // 09h00 - 18h30
  //   [0 => 11 * 60 * 60, 19 * 60 * 60], // 11h00 - 19h
  //   [0 => 7 * 60 * 60, 17 * 60 * 60 + 30 * 60], // 07h00 - 17h30
  // ];
  $bongio = 4 * 60 * 60;
  $motgio = 60 * 60;
  $namphut = 5 * 60;
  $nam = date("Y", $tungay);

  $dulieuchamcong = [];
  $ketquachamcong = [];
  foreach ($danhsach as $chamcong) {
    if (empty($dulieuchamcong[$chamcong["idnhanvien"]])) $dulieuchamcong[$chamcong["idnhanvien"]] = [];
    $ngay = date("d/m", $chamcong["thoigian"]);
    $thoigian = $chamcong["thoigian"] - strtotime(date("Y/m/d", $chamcong["thoigian"]));
    if (empty($dulieuchamcong[$chamcong["idnhanvien"]][$ngay])) $dulieuchamcong[$chamcong["idnhanvien"]][$ngay] = [];
    $dulieuchamcong[$chamcong["idnhanvien"]][$ngay] []= $thoigian;
  }

  foreach ($dulieuchamcong as $idnhanvien => $dulieu) {
    $i = $tungay;
    while ($i <= $denngay) {
      $ngay = date("d/m", $i);
      if (empty($dulieuchamcong[$idnhanvien][$ngay])) $dulieuchamcong[$idnhanvien][$ngay] = [];
      $i += 60 * 60 * 24;
    }
  }

  foreach ($dulieuchamcong as $idnhanvien => $dulieu) {
    $ketquachamcong[$idnhanvien] = [];
    foreach ($dulieu as $ngay => $thoigian) {
      $ketquachamcong[$idnhanvien][$ngay] = [];
      // tạo các cặp số
      $capthoigian = [];
      $l = count($thoigian);
      for ($i = 0; $i < $l - 1; $i++) { 
        for ($j = $i + 1; $j < $l; $j++) { 
          if (abs($thoigian[$i] - $thoigian[$j]) > $bongio) {
            // so sánh các cặp số với bảng chấm công, cái nào khớp nhất thì lấy cái đo
            foreach ($danhsachca as $thutuca => $capsosanh) {
              $capthoigian []= [
                $thoigian[$i], $thoigian[$j], abs($thoigian[$i] - $capsosanh[0]) + abs($thoigian[$j] - $capsosanh[1]), $thutuca
              ];
            }
          }
        }
      }
      
      // if ($idnhanvien == 91) {
      //   echo json_encode($capthoigian) . "<br>";
      // }

      // cắc cặp còn lại kiểm tra cái nào hợp lý nhất thì lấy
      usort($capthoigian, "sosanhhoply");
      $dasudung = [];
      for ($i = 0; $i < count($capthoigian); $i++) { 
        if ($capthoigian[$i][3] >= 0 && in_array($capthoigian[$i][0], $dasudung) == false && in_array($capthoigian[$i][1], $dasudung) == false) {
          $ketquachamcong[$idnhanvien][$ngay] []= [
            $capthoigian[$i][0],
            $capthoigian[$i][1],
            $capthoigian[$i][3]
          ];

          $dasudung []= $capthoigian[$i][0];
          $dasudung []= $capthoigian[$i][1];
        }
      }
      // kiểm tra các cặp thời gian những cái nào thừa để riêng
      // lược bỏ nếu nằm trong khoản lược bỏ
      arsort($thoigian);
      $mocsosanh = [];
      foreach ($thoigian as $ngaygio) {
        $kiemtra = true;
        foreach ($ketquachamcong[$idnhanvien][$ngay] as $capsosanh) {
          if ($ngaygio >= $capsosanh[0] && $ngaygio <= $capsosanh[1]) $kiemtra = false;
        }
        if ($kiemtra) {
          foreach ($danhsachca as $thutuca => $capsosanh) {
            if (abs($ngaygio - $capsosanh[0]) < $motgio) $mocsosanh []= [
              $ngaygio, 0, $thutuca, $ngaygio - $capsosanh[0]
            ];
            if (abs($ngaygio - $capsosanh[1]) < $motgio) $mocsosanh []= [
              0, $ngaygio, $thutuca, $capsosanh[1] - $ngaygio
            ];
          }
        }
      }

      usort($mocsosanh, "sosanhcapdo");
      // if ($idnhanvien == 5) {
      //   echo "$ngay: ". json_encode($mocsosanh);
      //   echo "<br>";
      //   // die();
      // }
      foreach ($mocsosanh as $dulieuthoigian) {
        if (!empty($dulieuthoigian)) {
          $ketquachamcong[$idnhanvien][$ngay] []= [
            $dulieuthoigian[0],
            $dulieuthoigian[1],
            $dulieuthoigian[2],
          ];
          break;
        }
      }
    }
  }

  // $dulieutre = [];
  // foreach ($ketquachamcong as $idnhanvien => $dulieu) {
  //   $dulieutre[$idnhanvien] = 0;
  //   foreach ($dulieu as $ngay => $thoigian) {
  //     foreach ($thoigian as $capthoigian) {
  //       $capsosanh = $danhsachca[$capthoigian[2]];
  //       if ($capthoigian[0] > 0 && ($capthoigian[0] - $capsosanh[0] > 0)) {
  //         $dulieutre[$idnhanvien] += $capthoigian[0] - $capsosanh[0];
  //       }
  //       if ($capthoigian[1] > 0 && ($capsosanh[1] - $capthoigian[1] > 0)) {
  //         $dulieutre[$idnhanvien] += $capsosanh[1] - $capthoigian[1];
  //       }
  //     }
  //   }
  //   $dulieutre[$idnhanvien] = floor($dulieutre[$idnhanvien] / 60);
  // }

  // foreach ($ketquachamcong as $id => $dulieu) {
  //   foreach ($dulieu as $ngay => $danhsach) {
  //     foreach ($danhsach as $key => $thoigian) {
  //       $ketquachamcong[$id][$ngay][$key][0] = chuyendoi($thoigian[0]);
  //       $ketquachamcong[$id][$ngay][$key][1] = chuyendoi($thoigian[1]);
  //     }
  //   }
  // }

  // echo json_encode($ketquachamcong);die();

  $sql = "select id, hoten from pet_nhanvien";
  $danhsachnhanvien = $db->obj($sql, "id", "hoten");

  // chuyển đổi kết quả chấm công thành dạng có thể đọc được
  // kiểm tra ngày đó có bao 
  $danhsach = [];
  foreach ($ketquachamcong as $idnhanvien => $dulieu) {
    if (!isset($danhsachnhanvien[$idnhanvien])) continue;
    $hoten = explode(" ", $danhsachnhanvien[$idnhanvien]);
    // 0: xanh: chấm công đầy đủ
    // 1: xám: nhân viên nghỉ ngày đó
    // 2: vàng: chỉ chấm công vào hoặc ra nhưng vẫn đúng giờ
    // 3: cam: hôm đó có vào trễ hoặc ra sớm
    // 4: đỏ: không chấm công
    $tongtre = 0;
    $tongkhongcham = 0;
    $tongkhongcong = 0;
    foreach ($dulieu as $ngay => $danhsachchamcong) {
      $ketqua = 0; // mặc định là đầy đủ
      $tre = 0;
      $ngaythang = explode("/", $ngay);
      $daungay = strtotime("$nam/$ngaythang[1]/$ngaythang[0]");
      $cuoingay = $daungay + 60 * 60 * 24 - 1;
      $sql = "select * from pet_". PREFIX ."_lichchamcongdaccach where (thoigian between $daungay and $cuoingay) and idnhanvien = $idnhanvien";
      $daccach = $db->fetch($sql);
      // if (!empty($daccach)) echo "$sql <br>";
      if (!count($danhsachchamcong)) {
        $ketqua = 4;
        $sql = "select * from pet_". PREFIX ."_row where (time between $daungay and $cuoingay) and user_id = $idnhanvien and type > 1";
        if (!empty($db->fetch($sql))) $ketqua = 1;
        else if (empty($daccach)) $tongkhongcong ++;
      }
      else {
        $khongcham = false;
        foreach ($danhsachchamcong as $key => $chamcong) {
          $mocsosanh = $danhsachca[$chamcong[2]];
          if ($chamcong[0] == 0 && $ketqua == 0) $ketqua = 2;
          if ($chamcong[1] == 0 && $ketqua == 0) $ketqua = 2;
          if ($chamcong[0] == 0 || $chamcong[1] == 0) $khongcham = true;
          if ($chamcong[0] > 0 && ($mocsosanh[0] < ($chamcong[0] - $namphut))) {
            $ketqua = 3;
            $tre += ($chamcong[0] - $namphut) - $mocsosanh[0];
          }
          if ($chamcong[1] > 0 && ($mocsosanh[1] > ($chamcong[1] + $namphut))) {
            $ketqua = 3;
            $tre += $mocsosanh[1] - ($chamcong[1] + $namphut);
          }
        }
        if (empty($daccach)) {
          if ($khongcham) $tongkhongcham ++;
          $tongtre += $tre;
        }
      }
      $ketquachamcong[$idnhanvien][$ngay] = [
        0 => $ketqua,
        chuyendoiphut($tre),
      ];
    }

    $danhsach []= [
      "idnhanvien" => $idnhanvien,
      "tennhanvien" => count($hoten) ? $hoten[count($hoten) - 1] : '',
      "dulieu" => $ketquachamcong[$idnhanvien],
      "tongtre" => $tongtre,
      "tongkhongcham" => $tongkhongcham, // không chấm theo buổi
      "tongkhongcong" => $tongkhongcong, // không chấm theo ngày
    ];
  }

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
