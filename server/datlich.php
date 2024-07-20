<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function tonghop() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachtonghop();
  return $result;
}

function danhsachtonghop() {
  global $data, $db, $result;

  $batdau = strtotime(date("Y/m/d"));
  $ketthuc = $batdau + 60 * 60 * 24 - 1;
  $sql = "select a.*, b.phone as dienthoai, b.name as tenkhach from pet_". PREFIX ."_datlich a inner join pet_". PREFIX ."_customer b on a.idkhachdat = b.id where (thoigian between $batdau and $ketthuc) order by ngaydat";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $key => $value) {
    $danhsach[$key]['ngaydat'] = date("d/m/Y H:i", $value["ngaydat"]);
    $danhsach[$key]['thoigian'] = date("d/m/Y H:i", $value["thoigian"]);
    $sql = "select a.id, a.tendanhmuc from pet_". PREFIX ."_danhmuc a inner join pet_". PREFIX ."_datlichchitiet b on a.id = b.iddanhmuc where b.iddatlich = $value[id]";
    $danhsach[$key]["dichvu"] = implode(", ", $db->arr($sql, "tendanhmuc"));
    $danhsach[$key]["iddichvu"] = $db->arr($sql, "id");

    $sql = "select fullname from pet_". PREFIX ."_users where userid = $value[idnhanvien]";
    if (empty($nhanvien = $db->fetch($sql))) {
        $danhsach[$key]["nhanvien"] = "";
        $danhsach[$key]["idnhanvien"] = 0;
    }
    else {
        $danhsach[$key]["nhanvien"] = $nhanvien["fullname"];
    }
  }

  return $danhsach;
}

function henngay() {
  global $data, $db, $result;
  
  $ngayhen = isodatetotime($data->ngayhen);
  $sql = "update pet_". PREFIX ."_datlich set thoigian = $ngayhen where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function khongden() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function khongdentonghop() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachtonghop();
  return $result;
}

function dadendieutri() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 1 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  return $result;
}

function sosanhthoigian($a, $b) {
  return $a["thoigian"] < $b["thoigian"];
}

function danhsachdatlich() {
  global $data, $db, $result;

  if (!empty($data->tukhoa)) {
    $sql = "select a.*, b.phone as dienthoai, b.name as tenkhach from pet_". PREFIX ."_datlich a inner join pet_". PREFIX ."_customer b on a.idkhachdat = b.id where b.name like '%$data->tukhoa%' or b.phone like '%$data->tukhoa%'";
    $danhsach = $db->all($sql);
  }
  else {
    $tungay = strtotime(date("Y/m/d", isodatetotime($data->tungay)));
    $denngay = strtotime(date("Y/m/d", isodatetotime($data->denngay))) + 60 * 60 * 24 - 1;
    $sql = "select a.*, b.phone as dienthoai, b.name as tenkhach from pet_". PREFIX ."_datlich a inner join pet_". PREFIX ."_customer b on a.idkhachdat = b.id where (ngaydat between $tungay and $denngay) or (trangthai = 0 and ngaydat < $tungay)";
    $danhsach = $db->all($sql);
  }

  usort($danhsach, "sosanhthoigian");

  foreach ($danhsach as $key => $value) {
    $danhsach[$key]['ngaydat'] = date("d/m/Y H:i", $value["ngaydat"]);
    $danhsach[$key]['thoigian'] = date("d/m/Y H:i", $value["thoigian"]);
    $sql = "select a.id, a.tendanhmuc from pet_". PREFIX ."_danhmuc a inner join pet_". PREFIX ."_datlichchitiet b on a.id = b.iddanhmuc where b.iddatlich = $value[id]";
    $danhsach[$key]["dichvu"] = implode(", ", $db->arr($sql, "tendanhmuc"));
    $danhsach[$key]["iddichvu"] = $db->arr($sql, "id");
    $sql = "select fullname from pet_". PREFIX ."_users where userid = $value[idnhanvien]";
    if (empty($nhanvien = $db->fetch($sql))) {
        $danhsach[$key]["nhanvien"] = "";
        $danhsach[$key]["idnhanvien"] = 0;
    }
    else {
        $danhsach[$key]["nhanvien"] = $nhanvien["fullname"];
    }
  }

  return $danhsach;
}

function xuatfile() {
  global $data, $db, $result;
    
  $dir = str_replace('/server', '', ROOTDIR);
  include $dir ."/include/PHPExcel/IOFactory.php";
  $file = $dir ."/include/file-mau-dat-lich.xlsx";
  $fileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($fileType);
  $objPHPExcel = $objReader->load($file);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $sql = "select * from pet_". PREFIX ."_datlich where trangthai = 0";
  $danhsach = $db->all($sql);

  $thutu = 1;
  // STT	Ngày đặt	Người đặt	Điện thoại	Chỉ định nhân viên	Dịch vụ	Ghi chú
  foreach ($danhsach as $datlich) {
    $dong = $thutu + 1;
    $sql = "select * from pet_". PREFIX ."_customer where id = $datlich[idkhachdat]";
    if (empty($khachhang = $db->fetch($sql))) $khachhang = ["name" => "", "phone" => ""];

    $sql = "select * from pet_". PREFIX ."_users where userid = $datlich[idnhanvien]";
    if (empty($nhanvien = $db->fetch($sql))) $nhanvien = "";
    else $nhanvien = $nhanvien['fullname'];

    $sql = "select b.tendanhmuc from pet_". PREFIX ."_datlichchitiet a inner join pet_". PREFIX ."_danhmuc b on a.iddanhmuc = b.id and iddatlich = $datlich[id]";
    if (empty($dichvu = $db->arr($sql, "tendanhmuc"))) $dichvu = "";
    else $dichvu = implode(", ", $dichvu);

    $sheet->setCellValue('A' . $dong, $thutu);
    $sheet->setCellValue('B' . $dong, date("d/m/Y H:i", $datlich['ngaydat']));
    $sheet->setCellValue('C' . $dong, $khachhang["name"]);
    $sheet->setCellValue('D' . $dong, $khachhang["phone"]);
    $sheet->setCellValue('E' . $dong, $nhanvien);
    $sheet->setCellValue('F' . $dong, $dichvu);
    $sheet->setCellValue('G' . $dong, $datlich["ghichu"]);
    $thutu ++;
  }

  $thoigian = time();
  $name = "danhsachdatlich-$thoigian.xlsx";
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
  $objWriter->save("$dir/include/export/$name");
  $objPHPExcel->disconnectWorksheets();
  unset($objWriter, $objPHPExcel);

  $result['status'] = 1;
  $result['link'] = 'https://'. $_SERVER['HTTP_HOST']. '/include/export/'. $name;
  return $result;
}
