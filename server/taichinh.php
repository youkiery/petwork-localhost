<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachthu();
  $result['danhsachchi'] = danhsachchi();
  $result['danhsachloaichi'] = danhsachloaichi();
  $result['danhsachnhacungcap'] = danhsachnhacungcap();
  $result['danhsachncc'] = danhsachnoncc();
  $result['thongke'] = thongke();
  return $result;
}

function thongke() {
  global $data, $db;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  $mathoigian = date('mY', $thoigian);

  $sql = "select sum(tienmat + nganhang) as tong from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongthu = $db->fetch($sql)['tong'];

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chi where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongchi = $db->fetch($sql)['tong'];

  $sql = "select value as tong from pet_". PREFIX ."_config where module = 'taichinh' and name = 'khachno$mathoigian'";
  if (empty($tongkho = $db->fetch($sql))) $tongkhachno = 0;
  else $tongkhachno = $tongkho['tong'];

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_noncc where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongnonhacungcap = $db->fetch($sql)['tong'];

  $tongkho = laydulieutonkho();
  
  return [
    'tongthu' => number_format($tongthu), 
    'tongchi' => number_format($tongchi), 
    'tongkhachno' => number_format($tongkhachno), 
    'tongnonhacungcap' => number_format($tongnonhacungcap), 
    'tongtaisan' => number_format($tongkho['thangnay']), 
    'loinhuan' => number_format($tongthu - $tongchi + $tongkhachno - $tongnonhacungcap + $tongkho['bandau'] - $tongkho['thangnay']),
  ];
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

  $sql = "select * from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang order by thoigian desc";
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

function xoachicodinh() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_taichinh_chicodinh where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['chicodinh'] = laychicodinh();
  return $result;
}

function luuchicodinh() {
  global $db, $data, $result;

  // kiểm tra loại chi
  foreach ($data->chicodinh as $thutu => $loaichi) {
    if (!(empty($loaichi->loaichi) || empty($loaichi->giatri))) {
      $idloaichi = kiemtraloaichi($loaichi->loaichi);
      $giatri = purenumber($loaichi->giatri);
      if ($loaichi->id) $sql = "update pet_". PREFIX ."_taichinh_chicodinh set giatri = $giatri, idloaichi = $idloaichi where id = $loaichi->id";
      else $sql = "insert into pet_". PREFIX ."_taichinh_chicodinh (idloaichi, giatri) values($idloaichi, $giatri)";
      $db->query($sql);
    }
  }
  
  $result['status'] = 1;
  $result['chicodinh'] = laychicodinh();
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

  $tonkho = laydulieutonkho();
  $result['status'] = 1;
  $result['chicodinh'] = laychicodinh();
  $result['cauhinh'] = laycauhinhimport();
  $result['tonkho'] = [
    'bandau' => number_format($tonkho['bandau']),
    'thangnay' => number_format($tonkho['thangnay']),
  ];
  return $result;
}

function laychicodinh() {
  global $data, $db;

  $sql = "select a.*, b.ten as loaichi from pet_". PREFIX ."_taichinh_chicodinh a inner join pet_". PREFIX ."_taichinh_loaichi b on a.idloaichi = b.id";
  $danhsach = $db->all($sql);
  foreach ($danhsach as $thutu => $loaichi) {
    $danhsach[$thutu]['giatri'] = number_format($loaichi['giatri']);
  }
  return $danhsach;
}

function laydulieutonkho() {
  global $data, $db;

  $thoigian = date('mY');
  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkho'";
  if (empty($tongkho = $db->fetch($sql))) $tongkho = ['value' => 0];

  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkho$thoigian'";
  if (empty($tongkhohientai = $db->fetch($sql))) $tongkhohientai = ['value' => 0];
  return [
    'bandau' => $tongkho['value'],
    'thangnay' => $tongkhohientai['value']
  ];
}

function luukhothangnay() {
  global $data, $db, $result;

  $bandau = purenumber($data->tonkho->bandau);
  $thangnay = purenumber($data->tonkho->thangnay);
  $thoigian = date('mY');
  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkho'";
  if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('taichinh', 'tongkho', $bandau, 1)";
  else $sql = "update pet_". PREFIX ."_config set value = $bandau where module = 'taichinh' and name = 'tongkho'";
  $db->query($sql);

  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkho$thoigian'";
  if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('taichinh', 'tongkho$thoigian', $thangnay, 1)";
  else $sql = "update pet_". PREFIX ."_config set value = $thangnay where module = 'taichinh' and name = 'tongkho$thoigian'";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
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
    case '1':
      $result = importno();
      break;
    case '2':
      $result = importncc();
      break;
    default:
      $result = importchi();
  }
  return $result;
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
      'nhacungcap' => 'A2',
      'noidung' => 'B2',
      'giatri' => 'C2',
      'thoigian' => 'D2',
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
  global $db, $result;

  $dulieu = docdulieu();
  $error = [];
  $dathem = 0;
  $tongcong = count($dulieu);
  // 'loaichi' không có loại chi bỏ qua
  // 'tienchi' không có tiền chi, hoặc tiền chi = 0, bỏ qua
  // 'thoigian' thời gian không đúng định dạng, bỏ qua
  foreach ($dulieu as $thutu => $thongtin) {
    $kiemtra = false;
    if (!empty($thongtin['loaichi'] && !empty($thongtin['tienchi']))) {
      $ngaygio = explode(' ', $thongtin['thoigian']);
      $ngay = explode('/', $ngaygio[0]);
      $gio = $ngaygio[1];
      $thoigian = strtotime("$ngay[2]/$ngay[1]/$ngay[0] $gio");
      if ($thoigian) {
        $idloaichi = kiemtraloaichi($thongtin['loaichi']);
        $giatri = abs($thongtin['tienchi']);
        $sql = "select * from pet_". PREFIX ."_taichinh_chi where idloaichi = $idloaichi and thoigian = $thoigian and giatri = $giatri";
        if (empty($db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_taichinh_chi (idloaichi, giatri, ghichu, thoigian) values($idloaichi, $giatri, '', $thoigian)";
          $db->query($sql);
          $dathem ++;
          $kiemtra = true;
        }
      }
    }
    if (!$kiemtra) $error []= "Lỗi: dòng $thutu loại chi $thongtin[loaichi] ". number_format($thongtin['tienchi']) ." lúc $thongtin[thoigian]";
  }
  $result['status'] = 1;
  $result['messenger'] = "Đã thêm $dathem/$tongcong mục chi";
  $result['error'] = $error;
  return $result;
}

function kiemtraloaichi($loaichi) {
  global $db;
  
  $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where ten = '$loaichi'";
  if ($dulieuloaichi = $db->fetch($sql)) return $dulieuloaichi['id'];

  $sql = "insert into pet_". PREFIX ."_taichinh_loaichi (ten, kichhoat) values('$loaichi', 1)";
  return $db->insertid($sql);
}

function importno() {
  global $db, $result;

  $dulieu = docdulieu();
  $tongno = 0;
  $thoigian = date('mY');
  $error = [];
  // 'nhacungcap' nếu không có nhà cung cấp, bỏ qua
  // 'noidung' 
  // 'giatri' nếu không có giá trị, bỏ qua
  // 'thoigian' nếu thời gian bị sai, bỏ qua
  foreach ($dulieu as $thutu => $thongtin) {
    if (!empty($thongtin['tienno'])) {
      $tienno = abs($thongtin['tienno']);
      $tongno += $tienno;
    }
  }

  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'khachno$thoigian'";
  if (empty($cauhinh = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('taichinh', 'khachno$thoigian', $tongno, 0)";
  }
  else $sql = "update pet_". PREFIX ."_config set value = $tongno where id = $cauhinh[id]";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = "Tổng nợ khách hàng ". number_format($tongno);
  $result['error'] = $error;
  return $result;
}

function importncc() {
  global $db, $result;

  $dulieu = docdulieu();
  $error = [];
  $dathem = 0;
  $tongcong = count($dulieu);
  $tongno = 0;
  // 'nhacungcap' => 'A2',
  // 'noidung' => 'B2',
  // 'giatri' => 'C2',
  // 'thoigian' => 'D2',
  foreach ($dulieu as $thutu => $thongtin) {
    $kiemtra = false;
    if (!empty($thongtin['nhacungcap'] && !empty($thongtin['giatri']))) {
      $ngaygio = explode(' ', $thongtin['thoigian']);
      $ngay = explode('/', $ngaygio[0]);
      $gio = $ngaygio[1];
      $thoigian = strtotime("$ngay[2]/$ngay[1]/$ngay[0] $gio");
      if ($thoigian) {
        $idnhacungcap = kiemtranhacungcap($thongtin['nhacungcap']);
        $giatri = purenumber($thongtin['giatri']);
        $tongno += $giatri;
        $sql = "select * from pet_". PREFIX ."_taichinh_noncc where idnhacungcap = $idnhacungcap and thoigian = $thoigian and giatri = $giatri and noidung = '$thongtin[noidung]'";
        if (empty($db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_taichinh_noncc (idnhacungcap, noidung, giatri, thanhtoan, thoigian, ghichu) values($idnhacungcap, '$thongtin[giatri]', $giatri, $giatri, $thoigian, '')";
          $db->query($sql);
          $dathem ++;
          $kiemtra = true;
        }
      }
    }
    if (!$kiemtra) $error []= "Lỗi: dòng $thutu Nhà cung cấp $thongtin[nhacungcap] ". number_format($thongtin['giatri']) ." lúc $thongtin[thoigian]";
  }

  $result['status'] = 1;
  $result['messenger'] = "Tổng nợ nhà cung cấp ". number_format($tongno);
  $result['error'] = $error;
  return $result;
}

function kiemtranhacungcap($nhacungcap) {
  global $db;
  $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where ten = '$nhacungcap'";
  if (empty($ncc = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_taichinh_nhacungcap (ten) values('$nhacungcap')";
    $ncc = ['id' => $db->insertid];
  }
  return $ncc['id'];
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
      'nhacungcap' => 'A2',
      'noidung' => 'B2',
      'giatri' => 'C2',
      'thoigian' => 'D2',
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
