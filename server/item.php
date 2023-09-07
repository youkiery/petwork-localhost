<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachhanghoa();
  return $result;
}

function danhsachhanghoa() {
  global $data, $db, $result;

  $tukhoa = $data->tukhoa;
  $sql = "select * from pet_". PREFIX ."_hanghoa where tenhang like '%$tukhoa%' order by id desc limit 20";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $hanghoa) {
    if ($hanghoa["hinhanh"] == "") $hinhanh = [];
    else $hinhanh = explode(",", $hanghoa["hinhanh"]);
    $danhsach[$thutu]["image"] = $hinhanh;
    $danhsach[$thutu]["giaban"] = number_format($hanghoa["giaban"]);
  }

  return $danhsach;
}

function capnhat() {
  global $data, $db, $result;
 
  $data->image = implode(",", $data->image);
  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX ."_hanghoa (mahang, tenhang, loaihang, giaban, gianhap, soluong, hinhanh, gioithieu, donvi) values('', '$data->tenhang', 0, '$data->giaban', 0, 0, '$data->image', '$data->gioithieu', '$data->donvi')";
  }
  else {
    $sql = "update pet_". PREFIX ."_hanghoa set tenhang = '$data->tenhang', giaban = '$data->giaban', hinhanh = '$data->image', gioithieu = '$data->gioithieu', donvi = '$data->donvi' where id = $data->id";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachhanghoa();
  return $result;
}

function xoahang() {
  global $data, $db, $result;
 
  $sql = "update pet_". PREFIX ."_hanghoa set kichhoat = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachhanghoa();
  return $result;
}

function khoitaomahang() {
  global $data, $db, $result;

  $sql = "select id from pet_". PREFIX ."_hanghoa order by id desc limit 1";
  $hang = $db->fetch($sql);
  $mahang = fillzero(($hang['id'] ? $hang['id'] : 0) + 1);
  return "SP$mahang";
}

function fillzero($number) {
  $number = strval($number);
  $zerolength = 8 - strlen($number);
  for ($i = 0; $i < $zerolength; $i++) { 
    $number = '0' . $number;
  }
  return $number;
}

function docdulieufile($file) {
  $x = array();
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }

  include_once DIR .'include/PHPExcel/IOFactory.php';
    
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);
  
  $sheet = $objPHPExcel->getSheet(0); 
  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $col = array(
    'Mã hàng' => '',
    'Tên hàng' => '',
    'Giá bán' => '',
    'Tồn kho' => '',
  );

  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $val = $sheet->getCell($xr[$j] . '1')->getValue();
    foreach ($col as $key => $value) {
      if ($key == $val) $col[$key] = $j;
    }
  }

  $kiemtra = false;
  foreach ($col as $tencot => $macot) {
    if (empty($macot)) {
      $kiemtra = true;
      break;
    }
  }

  if ($kiemtra) return false;

  $exdata = array();
  for ($i = 2; $i <= $highestRow; $i ++) { 
    $temp = array();
    foreach ($col as $key => $j) {
      $val = $sheet->getCell($xr[$j] . $i)->getValue();
      $temp []= $val;
    }
    $exdata []= $temp;
  }
  return $exdata;
}

function excel() {
  global $data, $db, $result, $_FILES, $res;

  $dulieubenhvien = docdulieufile($_FILES['file']['tmp_name']);
  $dulieukho = docdulieufile($_FILES['file2']['tmp_name']);

  if (!$dulieubenhvien || !$dulieukho) $result["messenger"] = "Định dạng file không chứa các trường 'Mã hàng', 'Tên hàng', 'Giá bán', 'Bệnh viện', 'Tồn kho'";
  else {
    // gộp số lượng 2 bên
    // chuyển đổi số lượng nếu cần

    // 0 => 'Mã hàng'
    // 1 => 'Tên hàng'
    // 2 => 'Giá bán'
    // 3 => 'Tồn kho'
    $sql = "select mahang, 1 as kiemtra from pet_". PREFIX ."_hanghoa where kichhoat = 1";
    $danhsachhang = $db->obj($sql, "mahang", "kiemtra");
    $danhsachimport = [];

    foreach ($dulieubenhvien as $hanghoa) {
      if (!empty($danhsachhang[$hanghoa[0]])) $danhsachimport[$hanghoa[0]] = $hanghoa;
    }

    foreach ($dulieukho as $hanghoa) {
      if (!empty($danhsachhang[$hanghoa[0]])) {
        if (empty($danhsachimport[$hanghoa[0]])) $danhsachimport[$hanghoa[0]] = $hanghoa;
        else $danhsachimport[$hanghoa[0]][3] += $hanghoa[3];
      }
    }
    
    $thoigian = time();
    foreach ($danhsachimport as $hanghoa) {
      // lấy dữ liệu hàng hoá, kiểm tra nếu thay đổi giá bán thì thêm vào bản
      // cập nhật giá bán, số lượng
      $sql = "select * from pet_". PREFIX ."_hanghoa where mahang = '$hanghoa[0]'";
      $dulieuhanghoa = $db->fetch($sql);
      if ($dulieuhanghoa['giaban'] != $hanghoa[2]) {
        $sql = "insert into pet_". PREFIX ."_hanghoathaydoi (idhang, giadau, giacuoi, thoigian) values($dulieuhanghoa[id], $dulieuhanghoa[giaban], $hanghoa[2], $thoigian)";
        $db->query($sql);
      }
      $sql = "update pet_". PREFIX ."_hanghoa set soluong = $hanghoa[3], giaban = $hanghoa[2] where mahang = '$hanghoa[0]'";
      $db->query($sql);
    }
  
    $result['status'] = 1;
    $result['messenger'] = "Đã cập nhật số lượng kho";
  }

  return $result;
}

function filer() {
  global $data, $db, $result, $_FILES, $res;

  $dulieufile = docdulieufile($_FILES['file']['tmp_name']);

  // 0 => 'Mã hàng'
  // 1 => 'Tên hàng'
  // 2 => 'Giá bán'
  // 3 => 'Tồn kho'
  if (!$dulieufile) $result["messenger"] = "Định dạng file không chứa các trường 'Mã hàng', 'Tên hàng', 'Giá bán', 'Bệnh viện', 'Tồn kho'";
  else {
    $tong = count($dulieufile);
    $them = 0;
    foreach ($dulieufile as $hanghoa) {
      $sql = "select * from pet_". PREFIX ."_hanghoa where mahang = '$hanghoa[0]'";
      if (empty($db->fetch($sql))) {
        $sql = "insert into pet_". PREFIX ."_hanghoa (mahang, tenhang, loaihang, giaban, gianhap, soluong, hinhanh, gioithieu, donvi) values('$hanghoa[0]', '$hanghoa[1]', 0, '$hanghoa[2]', 0, $hanghoa[3], '', '', '')";
        $db->query($sql);
        $them ++;
      }
    }
  
    $result['status'] = 1;
    $result['messenger'] = "Đã import $them/$tong hàng hoá";
  }

  return $result;
}

function timhangthanhphan() {
  global $data, $db, $result, $res;

  $sql = "select id, mahang, tenhang from pet_". PREFIX ."_hanghoa where tenhang like '%$data->tukhoa%' or mahang like '%$data->tukhoa%' order by tenhang limit 20";

  $result["status"] = 1;
  $result["danhsach"] = $db->all($sql);
  return $result;
}
