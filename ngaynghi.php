<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
include_once(DIR. '/include/config.php');
include_once(DIR. '/include/db.php');

$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

$filename = "include/001_AGLog.txt";
$handle = fopen($filename, "r");
$nhanvien = [
  "2" => "29", // quoc
  "8" => "5", // khanh
  "13" => "39", // vi
  "15" => "74", // than
  "16" => "76", // diu
  "17" => "79", // thanh
  "18" => "19", // bich
  "19" => "80", // thu
  "20" => "82", // tam
  "22" => "85", // minh
  "23" => "91", // nho huong
  "24" => "90", // chanh
  "0" => "71", // nhung
  "0" => "20", // huong lon
  "0" => "18", // nana
];

$thoigiandulieu = "2023/07/01";
$dauthang = strtotime($thoigiandulieu);
$cuoithang = strtotime(date("Y/m/t", $dauthang)) + 60 * 60 * 24;
$sql = "select * from pet_test_row where (time between $dauthang and $cuoithang) and type > 1";
$danhsach = $db->all($sql);
$lichchamcong = [];

// lấy dữ liệu chấm công
// foreach nhân viên tạo dữ liệu lịch nghỉ
// foreach dữ liệu lịch nghỉ
// $thoigianchamcong = [
//   "06" => "07",
//   "07" => "07",
//   "08" => "09",
//   "09" => "09",
//   "10" => "11",
//   "11" => "11",
//   "12" => "11",
//   "13" => "13",
//   "17" => "17",
//   "18" => "19",
//   "19" => "19",
// ];
// $sosanh = [
//   "07" => "nhohon",  // vào trước thì lấy
//   "09" => "nhohon",  // vào trước thì lấy
//   "11" => "doangia", // trước 11h30 thì lấy nhỏ hơn, sau 11h30 thì lấy lớn hơn
//   "13" => "nhohon", // vào trước thì lấy
//   "17" => "lonhon", // ra sau thì lấy
//   "19" => "lonhon", // ra sau thì lấy
// ];

// thời gian vào 7h ra 11h30, đúng ca sáng
// thời gian vào 13h15 ra 17h30, đúng ca chiều
// thời gian vào 9h ra 18h30, trực trưa
// thời gian vào 7h ra 18h30, nana về sớm
// thời gian vào 11h ra 19h, khánh

$danhsachca = [
  [0 => 7 * 60 * 60, 11 * 60 * 60 + 30 * 60], // 07h00 - 11h30
  [0 => 13 * 60 * 60 + 15 * 60, 17 * 60 * 60 + 30 * 60], // 13h15 - 17h30
  [0 => 9 * 60 * 60, 18 * 60 * 60 + 30 * 60], // 09h00 - 18h30
  [0 => 11 * 60 * 60, 19 * 60 * 60], // 11h00 - 19h
];
$danhsachthoigian = [7 * 60 * 60, 11 * 60 * 60 + 30 * 60, 11 * 60 * 60, 9 * 60 * 60, 13 * 60 * 60 + 15 * 60, 17 * 60 * 60 + 30 * 60, 18 * 60 * 60 + 30 * 60, 19 * 60 * 60];
$bongio = 4 * 60 * 60;
$motgio = 30 * 60;

$dulieuchamcong = [];
$ketquachamcong = [];
$dulieutre = [];
if ($handle) {
  while (($line = fgets($handle)) !== false) {
    $data = explode("\t", $line);
    if (isset($nhanvien[intval($data[2])])) {
      $idnhanvien = $nhanvien[intval($data[2])];
      $chuoithoigian = $data[6];
      $ngaygio = strtotime("$chuoithoigian");
      if (empty($dulieuchamcong[$idnhanvien])) $dulieuchamcong[$idnhanvien] = [];
      $ngay = date("d/m", $ngaygio);
      $thoigian = $ngaygio - strtotime(date("Y/m/d", $ngaygio));
      if (empty($dulieuchamcong[$idnhanvien][$ngay])) $dulieuchamcong[$idnhanvien][$ngay] = [];
      $dulieuchamcong[$idnhanvien][$ngay] []= $thoigian;
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
            $capthoigian []= [
              $thoigian[$i], $thoigian[$j], 0, -1
            ];
            foreach ($danhsachca as $thutuca => $capsosanh) {
              if (abs($thoigian[$i] - $capsosanh[0]) < $motgio) {
                if (abs($thoigian[$j] - $capsosanh[1]) < $motgio) {
                  $capthoigian[count($capthoigian) - 1][2] = abs($thoigian[$i] - $capsosanh[0]) + abs($thoigian[$j] - $capsosanh[1]);
                  $capthoigian[count($capthoigian) - 1][3] = $thutuca;
                }
              }
            }
          }
        }
      }

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
            if (abs($ngaygio - $capsosanh[0]) < $motgio || abs($ngaygio - $capsosanh[1]) < $motgio) {
              if (abs($ngaygio - $capsosanh[0]) < $motgio) $mocsosanh []= [
                $ngaygio, 0, $thutuca, $ngaygio - $capsosanh[0]
              ];
              if (abs($ngaygio - $capsosanh[1]) < $motgio) $mocsosanh []= [
                0, $ngaygio, $thutuca, $capsosanh[1] - $ngaygio
              ];
            }
          }
        }
      }

      // if ($idnhanvien == 5) {
      //   echo "$ngay: ". json_encode($mocsosanh) . "<br>";
      // }
      usort($mocsosanh, "sosanhcapdo");
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
  
  $dulieutre = [];
  foreach ($ketquachamcong as $idnhanvien => $dulieu) {
    $dulieutre[$idnhanvien] = 0;
    // if ($idnhanvien == 5) {
      foreach ($dulieu as $ngay => $thoigian) {
        foreach ($thoigian as $capthoigian) {
          $capsosanh = $danhsachca[$capthoigian[2]];
          if ($capthoigian[0] > 0 && ($capthoigian[0] - $capsosanh[0] > 0)) {
            $dulieutre[$idnhanvien] += $capthoigian[0] - $capsosanh[0];
            // echo "vào: $ngay ". chuyendoi($capthoigian[0]) ."<br>";
          }
          if ($capthoigian[1] > 0 && ($capsosanh[1] - $capthoigian[1] > 0)) {
            $dulieutre[$idnhanvien] += $capsosanh[1] - $capthoigian[1];
            // echo "ra: $ngay ". chuyendoi($capthoigian[1]) ."<br>";
          }
        }
      }
      $dulieutre[$idnhanvien] = floor($dulieutre[$idnhanvien] / 60);
    // }
  }

  echo json_encode($dulieutre); die();

  foreach ($ketquachamcong as $id => $dulieu) {
    foreach ($dulieu as $ngay => $danhsach) {
      foreach ($danhsach as $key => $thoigian) {
        $ketquachamcong[$id][$ngay][$key][0] = chuyendoi($thoigian[0]);
        $ketquachamcong[$id][$ngay][$key][1] = chuyendoi($thoigian[1]);
      }
    }
  }
  echo json_encode($ketquachamcong); die();

  fclose($handle);
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
// nếu không có lịch nghỉ và không có dữ liệu quét vân tay => tính là hôm đó nghỉ
