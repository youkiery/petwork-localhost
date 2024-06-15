<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  $result['homnay'] = danhsachtonghop();
  return $result;
}

function laycauhinh() {
  global $data, $db, $result;
  
  $cauhinh = ["chuongtrinh" => "", "luotdatlich" => 0];
  $sql = "select * from pet_". PREFIX ."_config where name = 'luotdatlich' and module = 'khachhang'";
  if (!empty($luotdatlich = $db->fetch($sql))) {
    $cauhinh["luotdatlich"] = $luotdatlich["value"];
  }
  else $cauhinh["luotdatlich"] = 1;

  $sql = "select * from pet_". PREFIX ."_config where name = 'chuongtrinh' and module = 'khachhang'";
  if (!empty($chuongtrinh = $db->fetch($sql))) {
    $cauhinh["chuongtrinh"] = $chuongtrinh["value"];
  }
  else $cauhinh["chuongtrinh"] = "";

  $sql = "select * from pet_". PREFIX ."_config where name = 'chuongtrinhdichvu' and module = 'khachhang'";
  if (!empty($chuongtrinh = $db->fetch($sql))) {
    $cauhinh["chuongtrinhdichvu"] = $chuongtrinh["value"];
  }
  else $cauhinh["chuongtrinhdichvu"] = "";

  return $cauhinh;
}

function luucauhinh() {
  global $data, $db, $result;
  
  $sql = "select * from pet_". PREFIX ."_config where name = 'luotdatlich' and module = 'khachhang'";
  if (empty($luotdatlich = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('khachhang', 'luotdatlich', '$data->luotdatlich', 0)";
  }
  else {
    $sql = "update pet_". PREFIX ."_config set value = '$data->luotdatlich' where id = $luotdatlich[id]";
  }
  $db->query($sql);

  $sql = "select * from pet_". PREFIX ."_config where name = 'chuongtrinh' and module = 'khachhang'";
  if (empty($chuongtrinh = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('khachhang', 'chuongtrinh', '$data->chuongtrinh', 0)";
  }
  else {
    $sql = "update pet_". PREFIX ."_config set value = '$data->chuongtrinh' where id = $chuongtrinh[id]";
  }
  $db->query($sql);

  $sql = "select * from pet_". PREFIX ."_config where name = 'chuongtrinhdichvu' and module = 'khachhang'";
  if (empty($chuongtrinh = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('khachhang', 'chuongtrinhdichvu', '$data->chuongtrinhdichvu', 0)";
  }
  else {
    $sql = "update pet_". PREFIX ."_config set value = '$data->chuongtrinhdichvu' where id = $chuongtrinh[id]";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = "Đã lưu cấu hình";
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
  $sql = "update pet_". PREFIX ."_datlich set ngaydat = $ngayhen where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  $result['homnay'] = danhsachtonghop();
  return $result;
}

function khongden() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  $result['homnay'] = danhsachtonghop();
  return $result;
}

function khongdentonghop() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachtonghop();
  $result['homnay'] = danhsachtonghop();
  return $result;
}

function dadendieutri() {
  global $data, $db, $result;
  
  $sql = "update pet_". PREFIX ."_datlich set trangthai = 1 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdatlich();
  $result['homnay'] = danhsachtonghop();
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

function khoitaochuyenmon() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  $result['dichvu'] = danhsachdichvu();
  $result['chuyenmon'] = danhsachchuyenmon();
  $result['cauhinh'] = laycauhinh();
  return $result;
}

function themchuyenmonnhanvien() {
  global $data, $db, $result;
  
  if ($data->loai) $sql = "delete from pet_". PREFIX ."_chuyenmon where idnhanvien = $data->idnhanvien and iddanhmuc = $data->idchuyenmon";
  else $sql = "insert into pet_". PREFIX ."_chuyenmon (idnhanvien, iddanhmuc) values($data->idnhanvien, $data->idchuyenmon)";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function themchuyenmondichvu() {
  global $data, $db, $result;
  
  if ($data->loai) $sql = "delete from pet_". PREFIX ."_chuyenmondichvu where idchuyenmon = $data->idchuyenmon and iddichvu = $data->iddichvu";
  else $sql = "insert into pet_". PREFIX ."_chuyenmondichvu (idchuyenmon, iddichvu) values($data->idchuyenmon, $data->iddichvu)";
  $db->query($sql);

  $result['status'] = 1;
  $result['dichvu'] = danhsachdichvu();
  return $result;
}

function themchuyenmon() {
  global $data, $db, $result;
  
  if ($data->idchuyenmon) $sql = "update pet_". PREFIX ."_danhmuc set tendanhmuc = '$data->chuyenmon' where id = $data->idchuyenmon";
  else $sql = "insert into pet_". PREFIX ."_danhmuc (tendanhmuc, loaidanhmuc, vitri, macdinh, thoigian, kichhoat) values('$data->chuyenmon', 2, 0, 0, 0, 1)";
  $db->query($sql);

  $result['status'] = 1;
  $result['chuyenmon'] = danhsachchuyenmon();
  return $result;
}

function xoachuyenmon() {
  global $data, $db, $result;
  
  $sql = "delete from pet_". PREFIX ."_chuyenmondichvu where idchuyenmon = $data->idchuyenmon";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_chuyenmon where iddanhmuc = $data->idchuyenmon";
  $db->query($sql);

  $sql = "delete from pet_". PREFIX ."_danhmuc where id = $data->idchuyenmon";
  $db->query($sql);

  $result['status'] = 1;
  $result['chuyenmon'] = danhsachchuyenmon();
  return $result;
}

function danhsachnhanvien() {
  global $data, $db, $result;
  
  $sql = "select fullname as tennhanvien, userid from pet_". PREFIX ."_users where active = 1 order by userid";
  $danhsachnhanvien = $db->all($sql);

  foreach ($danhsachnhanvien as $key => $nhanvien) {
    $danhsachnhanvien[$key]["chuyenmon"] = [];
    $chuyenmon = [];
    $sql = "select b.id, b.tendanhmuc from pet_". PREFIX ."_chuyenmon a inner join pet_". PREFIX ."_danhmuc b on a.iddanhmuc = b.id and a.idnhanvien = $nhanvien[userid]";
    $danhsachchuyenmon = $db->all($sql);
    foreach ($danhsachchuyenmon as $chuyenmon) {
      $chuyenmon []= $chuyenmon["tendanhmuc"];
      $danhsachnhanvien[$key]["chuyenmon"][$chuyenmon["id"]] = 1;
    }
    $danhsachnhanvien[$key]["danhsach"] = implode(", ", $chuyenmon);
  }

  return $danhsachnhanvien;
}

function danhsachdichvu() {
  global $data, $db, $result;

  $sql = "select id, tendanhmuc as dichvu from pet_". PREFIX ."_danhmuc where loaidanhmuc < 2 order by id desc";
  $danhsachdichvu = $db->all($sql);

  foreach ($danhsachdichvu as $key => $dichvu) {
    $danhsachdichvu[$key]["chuyenmon"] = [];
    $sql = "select * from pet_". PREFIX ."_chuyenmondichvu where iddichvu = $dichvu[id]";
    $danhsachchuyenmon = $db->all($sql);
    foreach ($danhsachchuyenmon as $chuyenmon) {
      $danhsachdichvu[$key]["chuyenmon"][$chuyenmon["idchuyenmon"]] = 1;
    }
  }

  return $danhsachdichvu;
}

function danhsachchuyenmon() {
  global $data, $db, $result;

  $sql = "select tendanhmuc as chuyenmon, id from pet_". PREFIX ."_danhmuc where loaidanhmuc = 2 order by id desc";
  return $db->all($sql);
}

function khoitaodanhgia() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachdanhgia();
  return $result;
}

function danhsachdanhgia() {
  global $data, $db, $result;

  $tungay = strtotime(date("Y/m/d", isodatetotime($data->tungay)));
  $denngay = strtotime(date("Y/m/d", isodatetotime($data->denngay))) + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_danhgia where thoigian between $tungay and $denngay order by thoigian desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $key => $value) {
    $sql = "select * from pet_". PREFIX ."_customer where id = $value[idkhachhang]";
    $khachhang = $db->fetch($sql);

    $danhsach[$key]["tenkhach"] = $khachhang["name"];
    $danhsach[$key]["dienthoai"] = $khachhang["phone"];
    $danhsach[$key]["thoigian"] = date("d/m/Y", $value["thoigian"]);
  }

  return $danhsach;
}

function khoitaochinhanh() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachchinhanh();
  return $result;
}

function xoachinhanh() {
  global $data, $db, $result;
  
  $sql = "delete from pet_chinhanh where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachchinhanh();
  return $result;
}

function danhsachchinhanh() {
  global $data, $db, $result;

  $sql = "select * from pet_chinhanh order by id";
  $danhsachchinhanh = $db->all($sql);

  foreach ($danhsachchinhanh as $thutu => $chinhanh) {
    $sql = "select * from pet_banggia where idchinhanh = $chinhanh[id]";
    $danhsachchinhanh[$thutu]["banggia"] = $db->arr($sql, "hinhanh");
  }
  
  return $danhsachchinhanh;
}

function thembanggia() {
  global $data, $db, $result;

  $danhsachdathem = [];
  foreach ($data->image as $hinhanh) {
    if (empty($hinhanh)) continue;
    
    $sql = "select * from pet_banggia where idchinhanh = $data->id and hinhanh = '$hinhanh'";
    if (empty($banggia = $db->fetch($sql))) {
      $sql = "insert into pet_banggia (idchinhanh, hinhanh) values($data->id, '$hinhanh')";
      $danhsachdathem []= $db->insertid($sql);
    }
    else {
      $danhsachdathem []= $banggia["id"];
    }
  }

  if (count($danhsachdathem)) {
    $sql = "delete from pet_banggia where idchinhanh = $data->id and id not in (". implode(", ", $danhsachdathem) .")";
  }
  else {
    $sql = "delete from pet_banggia where idchinhanh = $data->id";
  }
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function khoitaokhachvip() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['thongke'] = danhsachkhachvip();
  return $result;
}

function khoitaotintuc() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['dichvu'] = laydichvu();
  $result['thietbi'] = laythietbi();
  $result['daotao'] = laydaotao();
  $result['tintuc'] = laytintuc();
  return $result;
}

function laydichvu() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_dichvu where loai = 0 order by id desc";
  $danhsachdichvu = $db->all($sql);

  foreach ($danhsachdichvu as $thutu => $dichvu) {
    $danhsachdichvu[$thutu]["loai"] = intval($dichvu["loai"]);
  }

  return $danhsachdichvu;
}

function laythietbi() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_dichvu where loai = 1 order by id desc";
  $danhsachdichvu = $db->all($sql);

  foreach ($danhsachdichvu as $thutu => $dichvu) {
    $danhsachdichvu[$thutu]["loai"] = intval($dichvu["loai"]);
  }

  return $danhsachdichvu;
}

function themdichvu() {
  global $data, $db, $result;

  if ($data->id) {
    $sql = "update pet_". PREFIX ."_dichvu set tendichvu = '$data->tendichvu', noidung = '$data->noidung', loai = '$data->loai', hinhanh = '$data->image' where id = $data->id"; 
  }
  else {
    $sql = "insert into pet_". PREFIX ."_dichvu (tendichvu, noidung, loai, hinhanh) values('$data->tendichvu', '$data->noidung', '$data->loai', '$data->image')";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['dichvu'] = laydichvu();
  $result['thietbi'] = laythietbi();
  return $result;
}

function xoadichvu() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_dichvu where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['dichvu'] = laydichvu();
  $result['thietbi'] = laythietbi();
  return $result;
}

function laydaotao() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_daotao order by id desc";
  return $db->all($sql);
}

function themdaotao() {
  global $data, $db, $result;

  if ($data->id) {
    $sql = "update pet_". PREFIX ."_daotao set hocvien = '$data->hocvien', khoahoc = '$data->khoahoc', hinhanh = '$data->image' where id = $data->id"; 
  }
  else {
    $sql = "insert into pet_". PREFIX ."_daotao (hocvien, khoahoc, hinhanh) values('$data->hocvien', '$data->khoahoc', '$data->image')";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['daotao'] = laydaotao();
  return $result;
}

function xoadaotao() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_daotao where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['daotao'] = laydaotao();
  return $result;
}

function laytintuc() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_chuongtrinh order by id desc";
  return $db->all($sql);
}

function themtintuc() {
  global $data, $db, $result;

  if ($data->id) {
    $sql = "update pet_". PREFIX ."_chuongtrinh set tieude = '$data->tieude', mota = '$data->mota', noidung = '$data->noidung', hinhanh = '$data->image' where id = $data->id"; 
  }
  else {
    $thoigian = time();
    $sql = "insert into pet_". PREFIX ."_chuongtrinh (tieude, mota, noidung, hinhanh, thoigian) values('$data->tieude', '$data->mota', '$data->noidung', '$data->image', $thoigian)";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['tintuc'] = laytintuc();
  return $result;
}

function xoatintuc() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_daotao where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['tintuc'] = laytintuc();
  return $result;
}

function xoakhachvip() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_khachvip where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['thongke'] = danhsachkhachvip();
  return $result;
}

function themkhachvip() {
  global $data, $db, $result;
  
  $dulieu = $data->dulieu;
  $hanghoa = intval($dulieu->hanghoa);
  $spa = intval($dulieu->spa);
  $sql = "select * from pet_". PREFIX ."_customer where phone = '$dulieu->dienthoai'";
  if (empty($khachhang = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$dulieu->khachhang', '$dulieu->dienthoai', '')";
    $idkhachhang = $db->insertid($sql);
    $sql = "insert into pet_". PREFIX ."_khachvip (idkhachhang, spa, hanghoa) values('$idkhachhang', '$spa', '$hanghoa')";
    $db->query($sql);
  }
  else {
    $sql = "select * from pet_". PREFIX ."_khachvip where idkhachhang = $khachhang[id]";
    if (empty($khachvip = $db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_khachvip (idkhachhang, spa, hanghoa) values('$khachhang[id]', '$spa', '$hanghoa')";
      $db->query($sql);
    }
    else {
      $sql = "update pet_". PREFIX ."_khachvip set spa = $spa, hanghoa = $hanghoa where idkhachhang = $khachhang[id]";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['thongke'] = danhsachkhachvip();
  return $result;
}

function danhsachkhachvip() {
  global $data, $db, $result;

  $sql = "select b.id, b.name as khachhang, b.phone as dienthoai, 0 as doanhso, 0 as thangtruoc, 0 as tile from pet_". PREFIX ."_khachvip a inner join pet_". PREFIX ."_customer b on a.idkhachhang = b.id order by b.name asc";
  $danhsach = $db->all($sql);
  $batdauthangnay = strtotime(date("Y/m/1", isodatetotime($data->thoigian)));
  $ketthucthangnay = strtotime(date("Y/m/t", isodatetotime($data->thoigian))) + 60 * 60 * 24 - 1;
  $ketthucthangtruoc = $batdauthangnay - 1;
  $batdauthangtruoc = strtotime(date("Y/m/1", isodatetotime($ketthucthangtruoc)));
  // $phanloai = [0 => "hanghoa", "spa"];

  foreach ($danhsach as $thutu => $khachhang) {
    $sql = "select * from pet_". PREFIX ."_khachvip_doanhso where (thoigian between $batdauthangnay and $ketthucthangnay) and idkhachhang = $khachhang[id]";
    $danhsokhachhang = $db->all($sql);
    foreach ($danhsokhachhang as $doanhso) {
      $danhsach[$thutu]["doanhso"] += $doanhso["doanhso"];
    }

    $sql = "select * from pet_". PREFIX ."_khachvip_doanhso where (thoigian between $batdauthangtruoc and $ketthucthangtruoc) and idkhachhang = $khachhang[id]";
    $danhsokhachhang = $db->all($sql);
    foreach ($danhsokhachhang as $doanhso) {
      $danhsach[$thutu]["thangtruoc"] += $doanhso["doanhso"];
    }

    // nếu tháng này = 0
    // nếu tháng trước = 0
    // nếu 2 tháng đều có doanh số
    if ($danhsach[$thutu]["doanhso"] == 0) $danhsach[$thutu]["tile"] = -100;
    else if ($danhsach[$thutu]["thangtruoc"] == 0) $danhsach[$thutu]["tile"] = 1000;
    else {
      $chenhlech = $danhsach[$thutu]["doanhso"] - $danhsach[$thutu]["thangtruoc"];
      $danhsach[$thutu]["tile"] = round($chenhlech * 100 / $danhsach[$thutu]["thangtruoc"], 2);
    }
    $danhsach[$thutu]["doanhso"] = number_format($danhsach[$thutu]["doanhso"]);
    $danhsach[$thutu]["thangtruoc"] = number_format($danhsach[$thutu]["thangtruoc"]);
  }

  usort($danhsach, "sosanh");

  return $danhsach;
}

function sosanh($a, $b) {
  return $a["tile"] > $b["tile"];
}

function themchinhanh() {
  global $data, $db, $result;
  
  if (!empty($data->id)) {
    $sql = "update pet_chinhanh set tenchinhanh = '$data->tenchinhanh', diachi = '$data->diachi', dienthoai = '$data->dienthoai', hinhanh = '$data->image' where id = $data->id";
  }
  else {
    $sql = "insert into pet_chinhanh (tenchinhanh, diachi, dienthoai, hinhanh) values('$data->tenchinhanh', '$data->diachi', '$data->dienthoai', '$data->image')";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachchinhanh();
  return $result;
}

function khoitaogopy() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachgopy();
  return $result;
}

function danhsachgopy() {
  global $data, $db, $result;
  
  $sql = "select * from pet_gopy order by thoigian limit 10 offset ". ($data->trang - 1) * 10;
  $danhsachgopy = $db->all($sql);

  foreach ($danhsachgopy as $thutu => $gopy) {
    $danhsachgopy[$thutu]["thoigian"] = date("d/m/Y", $gopy["thoigian"]);
  }
  return $danhsachgopy;
}

function khoitaothongke() {
  global $data, $db, $result;

  $batdau = isodatetotime($data->batdau);
  $ketthuc = isodatetotime($data->ketthuc);

  $dulieu = [
    "danhgia" => [
      "labels" => ["1*", "2*", "3*", "4*", "5*"],
      "datasets" => [[
        "label" => "Tông đánh giá",
        "data" => [0, 0, 0, 0, 0],
        "backgroundColor" => 'pink',
      ]],
    ],
    "nhanvien" => [
      "labels" => [],
      "datasets" => [[
        "label" => "Đánh giá nhân viên",
        "data" => [],
        "backgroundColor" => 'pink',
      ]],
    ],
  ];

  $sql = "select * from pet_". PREFIX ."_danhgia a inner join pet_". PREFIX ."_users b on a.idnhanvien = b.userid and (a.thoigian between $batdau and $ketthuc)";
  $danhsach = $db->all($sql);

  $chuyendoi = [];
  $bodem = [];
  foreach ($danhsach as $danhgia) {
    if ($danhgia["muchailong"] <= 0) $danhgia["muchailong"] = 1;
    $dulieu["danhgia"]["datasets"][0]["data"][$danhgia["muchailong"] - 1] ++;
    if (!isset($chuyendoi[$danhgia["idnhanvien"]])) {
      $chuyendoi[$danhgia["idnhanvien"]] = count($dulieu["nhanvien"]["datasets"][0]["data"]);
      $dulieu["nhanvien"]["labels"][]= $danhgia["fullname"];
      $dulieu["nhanvien"]["datasets"][0]["data"][]= 0;
      $bodem[] = 0;
    }
    $dulieu["nhanvien"]["datasets"][0]["data"][$chuyendoi[$danhgia["idnhanvien"]]] += $danhgia["muchailong"];
    $bodem[$chuyendoi[$danhgia["idnhanvien"]]] ++;
  }


  foreach ($dulieu["nhanvien"]["datasets"][0]["data"] as $thutu => $muchailong) {
    $dulieu["nhanvien"]["datasets"][0]["data"][$thutu] = round($muchailong / $bodem[$thutu], 1);
  }
  
  $result['status'] = 1;
  $result['dulieu'] = $dulieu;
  $result['danhsach'] = danhsachgopy();
  return $result;
}

function khoitaovaccine() {
  global $data, $db, $result;
 
  // danh sách khách yêu cầu
  // danh sách khách đã tiêm năm trước nhưng năm nay chưa tiêm
    // danh sách vaccine những năm trước
    // nếu năm nay chưa có thì thêm vào bảng
  $homnay = time();
  $namtruoc = strtotime("last year");
  $namtruocnua = strtotime("-2 years");
  $danhsachkhachhang = [];
  $danhsach = [];

  $sql = "select id from pet_". PREFIX ."_vaccineloainhom where name like '%vaccine%'";
  $danhsachloai = implode(", ", $db->arr($sql, "id"));
  // nếu chưa tiêm loại thì bỏ qua
  if (!empty($danhsachloai)) {
    // lấy danh sách khách tiêm năm trước
    $sql = "select petid, calltime from pet_". PREFIX ."_vaccine where (calltime < $namtruoc) and typeid in (". $danhsachloai .") group by petid order by calltime desc";
    $danhsachvaccine = $db->all($sql);
  
    foreach ($danhsachvaccine as $vaccine) {
      $sql = "select * from pet_". PREFIX ."_pet where id = $vaccine[petid]";
      $thucung = $db->fetch($sql);

      if (empty($thucung["customerid"])) continue;

      $sql = "select * from pet_". PREFIX ."_pet where customerid = $thucung[customerid]";
      $danhsachthucung = implode(", ", $db->arr($sql, "id"));

      // nếu khách hàng đã kiểm tra thì bỏ qua
      if (!empty($danhsachkhachhang[$thucung["customerid"]])) continue;

      $danhsachkhachhang[$thucung["customerid"]] = 1; // đánh dấu đã kiểm tra
      $sql = "select petid from pet_". PREFIX ."_vaccine where (cometime >= $namtruoc) and typeid in (". $danhsachloai .") and petid in (". $danhsachthucung .") group by petid";
      if (!empty($db->fetch($sql))) continue; // nếu năm nay đã tiêm thì bỏ qua
      // nếu không thì lấy ngày gần nhất tiêm phòng, thông tin khách hàng
      $tiemcuoi = date("d/m/Y", $vaccine["calltime"]);
      $sql = "select * from pet_". PREFIX ."_customer where id = $thucung[customerid]";
      $khachhang = $db->fetch($sql);
      $danhsach []= [
        "khachhang" => $khachhang["name"],
        "dienthoai" => $khachhang["phone"],
        "tiemcuoi" => $tiemcuoi
      ];
    }
  }

  $result['status'] = 1;
  $result['danhsach'] = $danhsach;
  return $result;
}