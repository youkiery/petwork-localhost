<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachhanghoa();
  $result['canhbao'] = demcanhbao();
  return $result;
}

function demcanhbao() {
  global $data, $db, $result;

  $canhbao = 0;

  $sql = "select * from pet_". PREFIX ."_hanghoa where soluong < gioihan";
  $canhbao += $db->count($sql);

  $gioihan = time() - 60 * 60 * 24 * 3;
  $sql = "select * from pet_". PREFIX ."_hanghoathaydoi where thoigian > $gioihan";
  $canhbao += $db->count($sql);

  return $canhbao;
}

function khoitaocanhbao() {
  global $data, $db, $result;

  $danhsach = [];
  $sql = "select * from pet_". PREFIX ."_hanghoa where soluong < gioihan";
  $canhbao = $db->all($sql);

  foreach ($canhbao as $key => $hanghoa) {
    $canhbao[$key]["giaban"] = number_format($hanghoa["giaban"]);
    $canhbao[$key]["thongtin"] = "Hàng sắp hết";
    $danhsach []= $canhbao[$key];
  }

  $gioihan = time() - 60 * 60 * 24 * 3;
  $sql = "select b.tenhang, b.soluong, a.giadau, a.giacuoi from pet_". PREFIX ."_hanghoathaydoi a inner join pet_". PREFIX ."_hanghoa b on a.idhang = b.id where thoigian > $gioihan";
  $canhbao = $db->all($sql);

  foreach ($canhbao as $key => $hanghoa) {
    $canhbao[$key]["giaban"] = number_format($hanghoa["giadau"]) . " => ". number_format($hanghoa["giacuoi"]);
    $canhbao[$key]["thongtin"] = "Thay đổi giá";
    $danhsach []= $canhbao[$key];
  }

  $result['status'] = 1;
  $result['danhsach'] = $danhsach;
  return $result;
}

function danhsachhanghoa() {
  global $data, $db, $result;

  $tukhoa = $data->tukhoa;
  $sql = "select * from pet_". PREFIX ."_hanghoa where tenhang like '%$tukhoa%' order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $hanghoa) {
    if ($hanghoa["hinhanh"] == "") $hinhanh = [];
    else $hinhanh = explode(",", $hanghoa["hinhanh"]);
    $danhsach[$thutu]["image"] = $hinhanh;
    $danhsach[$thutu]["giaban"] = number_format($hanghoa["giaban"]);

    $sql = "select mahang, chuyendoi from pet_". PREFIX ."_hanghoathanhphan where idhang = $hanghoa[id]";
    $danhsach[$thutu]["chuyendoi"] = $db->all($sql);
  }

  return $danhsach;
}

function capnhat() {
  global $data, $db, $result;
 
  $data->image = implode(",", $data->image);
  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX ."_hanghoa (tenhang, loaihang, giaban, gianhap, gioihan soluong, hinhanh, gioithieu, donvi) values('', '$data->tenhang', 0, '$data->giaban', 0, $data->gioihan, 0, '$data->image', '$data->gioithieu', '$data->donvi')";
    $data->id = $data->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_hanghoa set tenhang = '$data->tenhang', giaban = '$data->giaban', hinhanh = '$data->image', gioithieu = '$data->gioithieu', donvi = '$data->donvi' where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_hanghoathanhphan where idhang = $data->id";
  $db->query($sql);

  foreach ($data->chuyendoi as $chuyendoi) {
    if (empty($chuyendoi->mahang)) continue;
    $sql = "insert into pet_". PREFIX ."_hanghoathanhphan (idhang, mahang, chuyendoi) values($data->id, '$chuyendoi->mahang', $chuyendoi->chuyendoi)";
    $db->query($sql);
  }

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
    'ĐVT' => '',
    'Hình ảnh (url1,url2...)' => ''
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
    // 4 => 'Đơn vị'
    // 5 => 'Hình ảnh'
    $sql = "select * from pet_". PREFIX ."_hanghoathanhphan";
    $danhsachchuyendoi = $db->all($sql);
    $danhsachmahang = [];
    $danhsachhanghoa = [];

    foreach ($danhsachchuyendoi as $chuyendoi) {
      if (empty($danhsachmahang[$chuyendoi["mahang"]])) $danhsachmahang[$chuyendoi["mahang"]] = [];
      $danhsachmahang[$chuyendoi["mahang"]] []= ["idhang" => $chuyendoi["idhang"], "chuyendoi" => $chuyendoi["chuyendoi"]];
    }

    $danhsachdulieu = array_merge($dulieubenhvien, $dulieukho);

    foreach ($danhsachdulieu as $hanghoa) {
      if (!empty($danhsachmahang[$hanghoa[0]])) {
        foreach ($danhsachmahang[$hanghoa[0]] as $chuyendoi) {
          if (empty($danhsachhanghoa[$chuyendoi["idhang"]])) $danhsachhanghoa[$chuyendoi["idhang"]] = ["soluong" => 0, "giaban" => 0];
          if ($danhsachhanghoa[$chuyendoi["idhang"]]["giaban"] < $hanghoa[2]) $danhsachhanghoa[$chuyendoi["idhang"]]["giaban"] = $hanghoa[2];
          $danhsachhanghoa[$chuyendoi["idhang"]]["soluong"] += $hanghoa[3] * $chuyendoi["chuyendoi"];
        }
      }
    }
    
    $thoigian = time();
    foreach ($danhsachhanghoa as $idhang => $thongtin) {
      // lấy dữ liệu hàng hoá, kiểm tra nếu thay đổi giá bán thì thêm vào bản
      // cập nhật giá bán, số lượng
      $sql = "select * from pet_". PREFIX ."_hanghoa where id = $idhang";
      $dulieuhanghoa = $db->fetch($sql);
      if ($dulieuhanghoa['giaban'] != $thongtin["giaban"]) {
        $sql = "insert into pet_". PREFIX ."_hanghoathaydoi (idhang, giadau, giacuoi, thoigian) values($idhang, $dulieuhanghoa[giaban], $thongtin[giaban], $thoigian)";
        $db->query($sql);
      }
      $sql = "update pet_". PREFIX ."_hanghoa set soluong = $thongtin[soluong], giaban = $thongtin[giaban] where id = $idhang";
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
        $sql = "insert into pet_". PREFIX ."_hanghoa (gioihan, tenhang, loaihang, giaban, gianhap, soluong, hinhanh, gioithieu, donvi) values('0', '$hanghoa[1]', 0, '$hanghoa[2]', 0, $hanghoa[3], '', '', '')";
        $id = $db->insertid($sql);
        $them ++;

        $sql = "insert into pet_". PREFIX ."_hanghoathanhphan (idhang, mahang, chuyendoi) values($id, '$hanghoa[0]', 1)";
        $db->query($sql);
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

function khoitaohansudung() {
  global $data, $db, $result, $res;

  $result["status"] = 1;
  $result["danhsach"] = danhsachhansudung();
  return $result;
}

function danhsachhansudung() {
  global $data, $db, $result, $res;

  $hansudung = time() + 60 * 60 * 24 * 90;
  $sql = "select * from pet_". PREFIX ."_hansudung where trangthai = 0 order by hansudung asc";
  $danhsach = $db->all($sql);
  
  foreach ($danhsach as $thutu => $handung) {
    if ($handung["hansudung"] <= $hansudung) $danhsach[$thutu]["quahan"] = 1;
    else $danhsach[$thutu]["quahan"] = 0;
    $danhsach[$thutu]["hansudung"] = date("d/m/Y", $handung["hansudung"]);
  }
  return $danhsach;
}

function themhansudung() {
  global $data, $db, $result, $res;

  $hansudung = isodatetotime($data->hansudung);
  $sql = "insert into pet_". PREFIX ."_hansudung (tenhang, soluong, hansudung) values('$data->tenhang', $data->soluong, $hansudung)";
  $db->query($sql);
  
  $result["status"] = 1;
  $result["danhsach"] = danhsachhansudung();
  return $result;
}

function xoahansudung() {
  global $data, $db, $result, $res;

  $sql = "update pet_". PREFIX ."_hansudung set trangthai = 1 where id = $data->id";
  $db->query($sql);
  
  $result["status"] = 1;
  $result["danhsach"] = danhsachhansudung();
  return $result;
}
