<?php

// post
function khoitao() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['dulieu'] = dulieuvattu();
  $result['danhsachtang'] = danhsachtang();
  $result['danhsachnhanvien'] = danhsachnhanvien();
  $result['tongtien'] = tongtien();
  return $result;
}

function tongtien() {
  global $db;

  $sql = "select sum(giatri * soluong) as tong from pet_". PREFIX ."_vattu";
  $tongtien = $db->fetch($sql)['tong'];
  $sql = "select sum(tile) as tong from pet_". PREFIX ."_vattu";
  $tongtile = $db->fetch($sql)['tong'];
  $tongtien = $tongtien * (1 + $tongtile / 100);
  return $tongtien;
}

function khoitaocophan() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['cophan'] = dulieucophan();
  return $result;
}

function import() {
  global $data, $db, $result, $_FILES;

  $raw = $_FILES['file']['tmp_name'];
  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
  $file_name = $name . "-". time() . ".". $ext;

  include DIR .'include/PHPExcel/IOFactory.php';
  $inputFileType = PHPExcel_IOFactory::identify($raw);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($raw);
  $sheet = $objPHPExcel->getSheet(0); 

  $x = [];
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }

  $highestRow = $sheet->getHighestRow(); 

  for ($i = 2; $i <= $highestRow; $i ++) { 
    $dulieutam = [];
    // 0  , 1    , 2      , 3       , 4     , 5     , 6
    // ten, donvi, soluong, thoigian, giatri, ghichu, thuoctang
    for ($j = 0; $j <= 6; $j ++) { 
      $dulieutam []= $sheet->getCell($xr[$j] . $i)->getValue();
    }
    $dulieutam[2] = intval($dulieutam[2]);
    $dulieutam[3] = totime($dulieutam[3]);
    $dulieutam[4] = purenumber($dulieutam[4]);
    if (empty($dulieutam[0])) break;
    else if ($dulieutam[2] == 0) break;
    $sql = "insert into pet_". PREFIX ."_vattu (ten, donvi, soluong, thoigian, giatri, ghichu) values('$dulieutam[0]', '$dulieutam[1]', $dulieutam[2], $dulieutam[3], $dulieutam[4], '$dulieutam[5]')";
    $idvattu = $db->insertid($sql);

    // thuộc tầng
    $thuoctang = explode(',', $dulieutam[6]);
    foreach ($thuoctang as $tang) {
      $tang = trim($tang);
      $sql = "select * from pet_". PREFIX ."_vattutang where ten = '$tang'";
      if (empty($dulieutang = $db->fetch($sql))) {
        $sql = "insert into pet_". PREFIX ."_vattutang (ten) values('$tang')";
        $dulieutang = ['id' => $db->insertid($sql)];
      }
      $sql = "insert into pet_". PREFIX ."_vattunoitang (idvattu, idtang) values($idvattu, $dulieutang[id])";
      $db->query($sql);
    }
  }

  $result['messenger'] = "Đã tải lên";
  $result['dulieu'] = dulieuvattu();
  return $result;
}

function xoavattu() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_vattu where id = $data->idvattu";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['dulieu'] = dulieuvattu();
  $result['tongtien'] = tongtien();
  return $result;
}

function chivattu() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_vattu where id = $data->idvattu";
  $vattu = $db->fetch($sql);

  $sql = "insert into pet_". PREFIX ."_taichinh_chivattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$vattu[ten]', '$vattu[donvi]', $vattu[soluong], $vattu[thoigian], $vattu[giatri], 0, '$vattu[ghichu]')";
  $idchi = $db->insertid($sql);

  $sql = "update pet_".PREFIX ."_vattu set idchi = $idchi where id = $data->idvattu";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['dulieu'] = dulieuvattu();
  $result['tongtien'] = tongtien();
  return $result;
}

function xoacophan() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_cophan where id = $data->idgiaodich";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['cophan'] = dulieucophan();
  return $result;
}

function themtang() {
  global $db, $data, $result;

  $sql = "insert into pet_". PREFIX ."_vattutang (ten) values('$data->ten')";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsachtang'] = danhsachtang();
  return $result;
}

function capnhattang() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_vattutang set ten = '$data->ten' where id = $data->idtang";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsachtang'] = danhsachtang();
  return $result;
}

function xoatang() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_vattutang where id = $data->idtang";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsachtang'] = danhsachtang();
  return $result;
}

function themcophan() {
  global $db, $data, $result;

  $dulieu = $data->dulieu;
  $sql = "delete from pet_". PREFIX ."_cophan where idnhanvien = $dulieu->idnhanvien";
  $db->query($sql);
  foreach ($dulieu->giaodich as $giaodich) {
    $giaodich->giatri = purenumber($giaodich->giatri);
    $giaodich->thanhtoan = intval($giaodich->thanhtoan);
    $sql = "insert into pet_". PREFIX ."_cophan (idnhanvien, tile, giatri, ghichu, thanhtoan) values('$dulieu->idnhanvien', $giaodich->tile, $giaodich->giatri, '$giaodich->ghichu', $giaodich->thanhtoan)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['cophan'] = dulieucophan();
  return $result;
}

function themvattu() {
  global $db, $data, $result;

  $dulieu = $data->dulieu;
  $dulieu->giatri = purenumber($dulieu->giatri);
  $dulieu->soluong = purenumber($dulieu->soluong);
  $dulieu->thoigian = isodatetotime($dulieu->thoigian);

  if ($dulieu->id) {
    $sql = "update pet_". PREFIX ."_vattu set ten = '$dulieu->ten', donvi = '$dulieu->donvi', soluong = $dulieu->soluong, thoigian = $dulieu->thoigian, giatri = $dulieu->giatri, tile = '$dulieu->tile', ghichu = '$dulieu->ghichu' where id = $dulieu->id";
    $db->query($sql);

    // thêm cái không có
    // xóa cái không tồn tại
    $danhsach = [];
    foreach ($dulieu->thuoctang as $idtang => $giatri) {
      $danhsach []= $idtang;
      $sql = "select * from pet_". PREFIX ."_vattunoitang where idvattu = $dulieu->id and idtang = $idtang";
      if (empty($db->fetch($sql))) {
        $sql = "insert into pet_". PREFIX ."_vattunoitang (idvattu, idtang) values($dulieu->id, $idtang)";
        $db->query($sql);
      }
    }
    if (!count($danhsach)) $sql = "delete from pet_". PREFIX ."_vattunoitang where idvattu = $dulieu->id";
    else $sql = "delete from pet_". PREFIX ."_vattunoitang where idvattu = $dulieu->id and idtang not in (". implode(', ', $danhsach) .")";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_vattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$dulieu->ten', '$dulieu->donvi', $dulieu->soluong, $dulieu->thoigian, $dulieu->giatri, '$dulieu->tile', '$dulieu->ghichu')";
    $dulieu->id = $db->insertid($sql);
    foreach ($dulieu->thuoctang as $idtang => $giatri) {
      $sql = "insert into pet_". PREFIX ."_vattunoitang (idvattu, idtang) values($dulieu->id, $idtang)";
      $db->query($sql);
    }
  }

  if ($dulieu->chivattu) {
    $sql = "insert into pet_". PREFIX ."_taichinh_chivattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$dulieu->ten', '$dulieu->donvi', $dulieu->soluong, $dulieu->thoigian, $dulieu->giatri, '0', '$dulieu->ghichu')";
    $idchi = $db->insertid($sql);

    $sql = "update pet_".PREFIX ."_vattu set idchi = $idchi where id = $dulieu->id";
    $db->query($sql);
  }

  
  $result['status'] = 1;
  $result['dulieu'] = dulieuvattu();
  $result['tongtien'] = tongtien();
  return $result;
}

// chức năng

function dulieucophan() {
  global $db, $data;
  $dulieu = [
    'tile' => 0,
    'giatri' => 0,
    'danhsach' => []
  ];

  $sql = "select * from pet_". PREFIX ."_user_per where module = 'vattu' and type = '2' and userid = $data->idnguoidung";
  if (empty($db->fetch($sql)) && $data->idnguoidung != 1) $kiemtra = 0;
  else {
    $kiemtra = 1;
    $sql = "select sum(giatri) as tong from pet_". PREFIX ."_cophan";
    $dulieu['giatri'] = $db->fetch($sql)['tong'];
    $sql = "select sum(tile) as tong from pet_". PREFIX ."_cophan";
    $dulieu['tile'] = $db->fetch($sql)['tong'];
  }

  $sql = "select * from pet_". PREFIX ."_cophan order by id asc";
  $danhsachtam = $db->all($sql);
  $danhsach = [];
  $tongtien = tongtien();

  foreach ($danhsachtam as $thutu => $giaodich) {
    if ($kiemtra || (!$kiemtra && $data->idnguoidung == $giaodich['idnhanvien'])) {
      if (empty($danhsach[$giaodich['idnhanvien']])) {
        $sql = "select hoten from pet_nhanvien where id = $giaodich[idnhanvien]";
        $nhanvien = $db->fetch($sql);
        $danhsach[$giaodich['idnhanvien']] = [
          'idnhanvien' => $giaodich['idnhanvien'],
          'nguoimua' => (empty($nhanvien['hoten']) ? '' : $nhanvien['hoten']),
          'tile' => 0,
          'hientai' => 0,
          'giatri' => 0,
          'giaodich' => []
        ];
      }
      $danhsach[$giaodich['idnhanvien']]['tile'] += $giaodich['tile'];
      $danhsach[$giaodich['idnhanvien']]['giatri'] += ($giaodich["thanhtoan"] ? $giaodich['giatri'] : 0);
      $danhsach[$giaodich['idnhanvien']]['giaodich'] []= [
        'id' => $giaodich['id'],
        'giatri' => number_format($giaodich['giatri']),
        'tile' => $giaodich['tile'],
        'thanhtoan' => intval($giaodich['thanhtoan']),
        'ghichu' => $giaodich['ghichu'],
      ];
    }
  }

  foreach ($danhsach as $nhanvien) {
    $dulieu['danhsach'] []= [
      'idnhanvien' => $nhanvien['idnhanvien'],
      'nguoimua' => $nhanvien['nguoimua'],
      'tile' => $nhanvien['tile'],
      'hientai' => number_format($nhanvien['tile'] * $tongtien / 100),
      'giatri' => number_format($nhanvien['giatri']),
      'giaodich' => $nhanvien['giaodich']
    ];
  }

  return $dulieu;
}

function dulieuvattu() {
  global $db, $data, $result;
  
  $dulieu = [
    'tongsoluong' => 0,
    'tongvattu' => 0,
    'tonggiatri' => 0,
    'danhsach' => []
  ];

  // nếu có tầng thì lọc
  // nếu không để toàn bô
  if (count($data->loctang) && !empty($data->loctang[0])) {
    $xtra = "where id in (select idvattu as id from pet_". PREFIX ."_vattunoitang where idtang in (". implode(', ', $data->loctang) ."))";
  
    $sql = "select * from pet_". PREFIX ."_vattutang where id in (". implode(', ', $data->loctang) .") order by id desc";
  }
  else {
    $xtra = "";
    $sql = "select * from pet_". PREFIX ."_vattutang order by id desc";
  }
  $danhsachtang = $db->all($sql);

  $sql = "select * from pet_". PREFIX . "_vattu $xtra order by thoigian desc";
  $danhsachvattu = $db->all($sql);

  $sql = "select sum(giatri * soluong) as tong from pet_". PREFIX . "_vattu";
  $tongtien = $db->fetch($sql)['tong'];

  foreach ($danhsachvattu as $thutu => $vattu) {
    $dulieu['tongvattu'] ++;
    $dulieu['tongsoluong'] += $vattu['soluong'];
    $dulieu['tonggiatri'] += $vattu['giatri'];
    $danhsachvattu[$thutu]['thuoctang'] = danhsachlienkettang($vattu['id']);
    $danhsachvattu[$thutu]['tang'] = dulieulienkettang($vattu['id']);
    $danhsachvattu[$thutu]['giatri'] = number_format($vattu['giatri']);
    $danhsachvattu[$thutu]['phantram'] = number_format($vattu['tile'] * $tongtien / 100);
    $danhsachvattu[$thutu]['tongtien'] = number_format($vattu['soluong'] * $vattu['giatri'] + $vattu['tile'] * $tongtien / 100);
    $danhsachvattu[$thutu]['thoigian'] = date('d/m/Y', $vattu['thoigian']);
    $sql = "select * from pet_". PREFIX ."_taichinh_chivattu where id = $vattu[idchi]";
    if (empty($db->fetch($sql))) {
      $danhsachvattu[$thutu]['chivattu'] = false;
      $danhsachvattu[$thutu]['idchi'] = 0;
    }
    else {
      $danhsachvattu[$thutu]['chivattu'] = true;
    }
  }
  $dulieu['danhsach'] = $danhsachvattu;

  $dulieu['tongsoluong'] = number_format($dulieu['tongsoluong']);
  $dulieu['tongvattu'] = number_format($dulieu['tongvattu']);
  $dulieu['tonggiatri'] = number_format($dulieu['tonggiatri']);
  
  return $dulieu;
}

function danhsachnhanvien() {
  global $db, $data;

  $sql = "select id, nhanvien as name from pet_nhanvien where kichhoat = 1 order by hoten asc";
  return $db->all($sql);
}

function danhsachtang() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_vattutang order by ten asc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $tang) {
    $sql = "select b.* from pet_". PREFIX ."_vattunoitang a inner join pet_". PREFIX ."_vattu b on a.idvattu = b.id and a.idtang = $tang[id]";
    $danhsachvattu = $db->all($sql);
    $danhsach[$thutu]['vattu'] = 0;
    $danhsach[$thutu]['soluong'] = 0;
    $danhsach[$thutu]['giatri'] = 0;
    foreach ($danhsachvattu as $vattu) {
      $danhsach[$thutu]['vattu'] ++;
      $danhsach[$thutu]['soluong'] += $vattu['soluong'];
      $danhsach[$thutu]['giatri'] += $vattu['giatri'];
    }
    $danhsach[$thutu]['vattu'] = number_format($danhsach[$thutu]['vattu']);
    $danhsach[$thutu]['soluong'] = number_format($danhsach[$thutu]['soluong']);
    $danhsach[$thutu]['giatri'] = number_format($danhsach[$thutu]['giatri']);
  }
  return $danhsach;
}

function danhsachlienkettang($idvattu) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vattunoitang where idvattu = $idvattu";
  $danhsachketnoi = $db->all($sql);
  $danhsach = [];
  foreach ($danhsachketnoi as $ketnoi) {
    $sql = "select * from pet_". PREFIX ."_vattutang where id = $ketnoi[idtang]";
    $tang = $db->fetch($sql);
    $danhsach []= $tang['ten'];
  }
  if (count($danhsach)) return implode(', ', $danhsach);
  else return 'Không';
}

function dulieulienkettang($idvattu) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vattunoitang where idvattu = $idvattu";
  $danhsachketnoi = $db->all($sql);
  $danhsach = [];
  foreach ($danhsachketnoi as $ketnoi) {
    $sql = "select * from pet_". PREFIX ."_vattutang where id = $ketnoi[idtang]";
    $tang = $db->fetch($sql);
    $danhsach[$tang['id']] = 1;
  }
  return $danhsach;
}

function xuatfile() {
  global $data, $db, $result, $x, $xr;
    
  $dir = str_replace('/server', '', ROOTDIR);
  include "$dir/include/PHPExcel/IOFactory.php";
  $file = "$dir/include/file-mau-vat-tu.xlsx";
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);

  $i = 2;
  $giatri = [0 => 'thutu', 'thoigian', 'ten', 'donvi', 'thuoctang', 'ghichu', 'soluong', 'giatri', 'tongtien'];
  $chuyendoi = [0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
  foreach ($data->danhsach as $tang) {
    $tang->thutu = $i - 1;
    foreach ($giatri as $thutu => $tengiatri) {
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($chuyendoi[$thutu] . $i, $tang->{$tengiatri});
    }
    $i ++;
  }

  $time = time();
  $filename = "$dir/include/export/file-mau-vat-tu-$time.xlsx";
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
  $objWriter->save($filename);
  $objPHPExcel->disconnectWorksheets();

  $result['status'] = 1;
  $result['link'] = "/include/export/file-mau-vat-tu-$time.xlsx";
  return $result;
}
