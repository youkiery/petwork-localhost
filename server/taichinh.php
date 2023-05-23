<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachthu();
  $result['danhsachchi'] = danhsachchi();
  $result['danhsachloaichi'] = danhsachloaichi();
  $result['danhsachnhacungcap'] = danhsachnhacungcap();
  $result['danhsachncc'] = danhsachnoncc();
  return $result;
}

function danhsachnhacungcap() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where kichhoat = 1 order by id asc";
  return $db->all($sql);
}

function danhsachnoncc() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_taichinh_noncc where thoigian between $dauthang and $cuoithang order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $noncc) {
    $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where id = $noncc[idnhacungcap]";
    $nhacungcap = $db->fetch($sql);
    $danhsach[$thutu]['ten'] = $nhacungcap['ten'];
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $noncc['thoigian']);
  }
  return $danhsach;
}

function danhsachloaichi() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where kichhoat = 1 order by id asc";
  return $db->all($sql);
}

function danhsachchi() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $chi) {
    $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where id = $chi[idloaichi]";
    $danhsach[$thutu]['loaichi'] = $db->fetch($sql)['ten'];
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $chi['thoigian']);
  }
  return $danhsach;
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

function danhsachthu() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $taichinh) {
    $sql = "select * from pet_". PREFIX ."_taichinh_hinhanh where idtaichinh = $taichinh[id] and loai = 0 order by id desc";
    $danhsach[$thutu]['hinhanh'] = $db->arr($sql, 'hinhanh');
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $taichinh['thoigian']);
  }
  return $danhsach;
}

function xoathu() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_taichinh_thu where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachthu();
  return $result;
}

function themthu() {
  global $db, $data, $result;

  $hinhanh = $data->hinhanh;
  $thoigian = isodatetotime($data->thoigian);
  $tienmat = purenumber($data->tienmat);
  $nganhang = purenumber($data->nganhang);

  if (empty($data->id)) {
    $sql = "insert into pet_". PREFIX ."_taichinh_thu (tienmat, nganhang, thoigian) values($tienmat, $nganhang, $thoigian)";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_taichinh_thu set tienmat = $tienmat, nganhang = $nganhang, thoigian = $thoigian where id = $data->id";
    $db->query($sql);
  }

  $sql = "delete from pet_". PREFIX ."_taichinh_hinhanh where idtaichinh = $data->id and loai = 0";
  $db->query($sql);

  foreach ($hinhanh as $url) {
    $sql = "insert into pet_". PREFIX ."_taichinh_hinhanh (idtaichinh, hinhanh, loai) values($data->id, '$url', 0)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachthu();
  return $result;
}

function themchi() {
  global $db, $data, $result;

  $dulieu = $data->dulieu;
  $thoigian = isodatetotime($dulieu->thoigian);
  $giatri = purenumber($dulieu->giatri);

  if (empty($dulieu->id)) {
    $sql = "insert into pet_". PREFIX ."_taichinh_chi (idloaichi, giatri, thoigian, ghichu) values($dulieu->idloaichi, $giatri, $thoigian, '$dulieu->ghichu')";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_taichinh_thu set idloaichi = $dulieu->idloaichi, giatri = $giatri, thoigian = $thoigian, ghichu = '$dulieu->ghichu' where id = $dulieu->id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachchi();
  return $result;
}

function themnonhacungcap() {
  global $db, $data, $result;

  $dulieu = $data->dulieu;
  $thoigian = isodatetotime($dulieu->thoigian);
  $giatri = purenumber($dulieu->giatri);
  $thanhtoan = purenumber($dulieu->thanhtoan);

  if (empty($dulieu->id)) {
    $sql = "insert into pet_". PREFIX ."_taichinh_noncc (idnhacungcap, noidung, giatri, thanhtoan, thoigian, ghichu) values($dulieu->idnhacungcap, '$dulieu->noidung', $giatri, $thanhtoan, $thoigian, '$dulieu->ghichu')";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_taichinh_noncc set idnhacungcap = $dulieu->idnhacungcap, giatri = $giatri, thanhtoan = $thanhtoan, thoigian = $thoigian, ghichu = '$dulieu->ghichu' where id = $dulieu->id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachnoncc();
  return $result;
}

function xoachi() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_taichinh_chi where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachchi();
  return $result;
}

function xoanoncc() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_taichinh_noncc where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachnoncc();
  return $result;
}

function themloaichi() {
  global $db, $data, $result;
  
  $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where ten = '$data->ten'";
  if (empty($db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_taichinh_loaichi (ten) values('$data->ten')";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachloaichi();
  return $result;
}

function themnhacungcap() {
  global $db, $data, $result;
  
  $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where ten = '$data->ten'";
  if (empty($db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_taichinh_nhacungcap (ten) values('$data->ten')";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhacungcap();
  return $result;
}

function xoanhacungcap() {
  global $db, $data, $result;
  
  $sql = "update pet_". PREFIX ."_taichinh_nhacungcap set kichhoat = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhacungcap();
  return $result;
}

function xoaloaichi() {
  global $db, $data, $result;
  
  $sql = "update pet_". PREFIX ."_taichinh_loaichi set kichhoat = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachloaichi();
  return $result;
}

function khoitaoimport() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['cauhinh'] = laycauhinhimport();
  return $result;
}

function luucauhinh() {
  global $data, $db, $result;

  foreach ($data->cauhinh as $ten => $giatri) {
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$ten$data->loai'";
    if (empty($cauhinh = $db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('loinhuan', '$ten$data->loai', '$giatri', 0)";
    else $sql = "update pet_". PREFIX ."_config set value = '$giatri' where id = $cauhinh[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  return $result;
}

function import() {
  global $data, $db, $result;
  
  switch ($data->loai) {
    case '0':
      importchi();
      break;
    case '0':
      importno();
      break;
    case '0':
      importkho();
      break;
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã import dữ liệu';
}

function layso($chuoi) {
  return intval(preg_replace('/[^0-9]/', '', $chuoi));
}
function laychu($chuoi) {
  return preg_replace('/[^A-Z]/', '', $chuoi);
}

function docdulieu() {
  global $db, $data, $_FILES;
  $dir = str_replace('/server', '', ROOTDIR);
  include $dir .'/include/PHPExcel/IOFactory.php';

  $file = $_FILES['file']['tmp_name'];
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);
  $sheet = $objPHPExcel->getSheet(0); 
  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $cauhinh = [
    0 => [
      'loaichi' => 'A2',
      'tienchi' => 'B2',
      'thoigian' => 'C2'
    ],
    [
      'dienthoai' => 'A2',
      'khachhang' => 'B2',
      'tienno' => 'C2',
    ],
    [
      'mahang' => 'A2',
      'soluong' => 'B2',
      'giatien' => 'C2',
    ],
  ];

  $truong = [];
  foreach ($cauhinh[$data->loai] as $tenbien => $giatri) {
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$tenbien$data->loai'";
    if (empty($config = $db->fetch($sql))) $truong[$tenbien] = [
      'giatri' => $giatri,
      'chu' => laychu($giatri),
    ];
    else $truong[$tenbien] = [
      'giatri' => $config['value'],
      'chu' => laychu($config['value']),
    ];
    $so = layso($truong[$tenbien]['giatri']);
  }

  $dulieu = array();
  for ($j = $so; $j <= $highestRow; $j ++) {
    $dulieutam = [];
    foreach ($truong as $tenbien => $thongtin) {
      $dulieutam[$tenbien] = $sheet->getCell($thongtin['chu'] . $j)->getValue();
    }
    $dulieu []= $dulieutam;
  }
  return $dulieu;
}

function importchi() {
  global $db;

  $dulieu = docdulieu();
  echo json_encode($dulieu);die();
}

function importno() {

  $dulieu = docdulieu();
  echo json_encode($dulieu);die();
}

function importkho() {
  
  $dulieu = docdulieu();
  echo json_encode($dulieu);die();
}

function laycauhinhimport() {
  global $db;

  $cauhinh = [
    0 => [
      'loaichi' => 'A2',
      'tienchi' => 'B2',
      'thoigian' => 'C2'
    ],
    [
      'dienthoai' => 'A2',
      'khachhang' => 'B2',
      'tienno' => 'C2',
    ],
    [
      'mahang' => 'A2',
      'soluong' => 'B2',
      'giatien' => 'C2',
    ],
  ];

  foreach ($cauhinh as $loai => $dulieu) {
    foreach ($dulieu as $tenbien => $giatri) {
      $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = '$tenbien$loai'";
      $dulieucauhinh = $db->fetch($sql);
      if (isset($dulieucauhinh['value']) && !empty($dulieucauhinh['value'])) $cauhinh[$loai][$tenbien] = $dulieucauhinh['value'];
    }
  }
  return $cauhinh;
}
