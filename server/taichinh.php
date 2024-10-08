<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachthu();
  $result['danhsachchi'] = danhsachchi();
  $result['danhsachloaichi'] = danhsachloaichi();
  $result['danhsachnhacungcap'] = danhsachnhacungcap();
  $result['danhsachncc'] = danhsachnoncc();
  $result['danhsachnaptien'] = danhsachnaptien();
  $result['danhsachtang'] = danhsachtang();
  $result['thongke'] = thongke();
  return $result;
}

function thongke() {
  global $data, $db;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;
  $mathoigian = date('mY', $thoigian);

  $sql = "select sum(naptien) as tong from pet_". PREFIX ."_taichinh_naptien where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongnaptien = intval($db->fetch($sql)['tong']);

  $sql = "select sum(tienmat) as tong from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongtienmat = intval($db->fetch($sql)['tong'] - $tongnaptien);

  $sql = "select sum(nganhang) as tong from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongnganhang = intval($db->fetch($sql)['tong'] + $tongnaptien);

  $sql = "select value as tong from pet_". PREFIX ."_config where module = 'taichinh' and name = 'khachno$mathoigian'";
  if (empty($tongkho = $db->fetch($sql))) $tongkhachno = 0;
  else $tongkhachno = intval($tongkho['tong']);

  $sql = "select value as tong from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongnccno$mathoigian'";
  if (empty($tongnccno = $db->fetch($sql))) $tongnccno = 0;
  else $tongnccno = intval($tongnccno['tong']);

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_noncc where thoigianthem = $dauthang order by id desc";
  $tongnonhacungcap = intval($db->fetch($sql)['tong']);
  $tongkho = laydulieutonkho($thoigian);
 
  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chi where (thoigian between $dauthang and $cuoithang) and loaitien = 0";
  $chitienmat = intval($db->fetch($sql)['tong']);
 
  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chi where (thoigian between $dauthang and $cuoithang) and loaitien = 1";
  $chinganhang = intval($db->fetch($sql)['tong']);

  $sql = "select sum(tongluong - nghiphep) as tong from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $chiluongthuong = intval($db->fetch($sql)['tong']);

  $sql = "select sum(cophan) as tong from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $cophan = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chincc where thoigianthem = $dauthang";
  $chinhacungcap = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chicodinh";
  $chicodinh = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri * soluong) as tong from pet_". PREFIX ."_taichinh_chivattu where thoigian between $dauthang and $cuoithang";
  $chitaisan = intval($db->fetch($sql)['tong']);

  $tongchi = $chitienmat + $chinganhang + $chiluongthuong + $chinhacungcap + $chitaisan + $chicodinh;
  if (empty($tongkho['thangnay'])) $kho = 0;
  else $kho = $tongkho['bandau'] - ($tongkho['thangnay'] + $tongnccno);

  return [
    'tienmat' => number_format($tongtienmat), 
    'naptien' => number_format($tongnaptien), 
    'nganhang' => number_format($tongnganhang), 
    'tongthu' => number_format($tongtienmat + $tongnganhang), 
    'chithuongxuyen' => number_format($chitienmat + $chinganhang),
    'tongnccno' => number_format($tongnccno),
    'chiluongthuong' => number_format($chiluongthuong),
    'chinhacungcap' => number_format($chinhacungcap),
    'chitaisan' => number_format($chitaisan),
    'tongchi' => number_format($tongchi), 
    'cophan' => number_format($cophan), 
    'chicodinh' => number_format($chicodinh), 
    'tongkhachno' => number_format($tongkhachno), 
    'tongnonhacungcap' => number_format($tongnonhacungcap), 
    'tongtaisan' => number_format($tongkho['thangnay'] + $tongnccno), 
    'loinhuan' => number_format($tongtienmat + $tongnganhang - $tongchi + $tongkhachno - $tongnonhacungcap + $kho),
  ];
}

function danhsachnhacungcap() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where kichhoat = 1 order by id asc";
  return $db->all($sql);
}

function danhsachnaptien() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_taichinh_naptien where thoigian between $dauthang and $cuoithang order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $naptien) {
    $danhsach[$thutu]['tongnaptien'] = number_format($naptien["naptien"]);
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $naptien['thoigian']);
  }
  return $danhsach;
}

function danhsachnoncc() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_taichinh_noncc where thoigianthem = $dauthang order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $noncc) {
    $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where id = $noncc[idnhacungcap]";
    $nhacungcap = $db->fetch($sql);
    $danhsach[$thutu]['ten'] = $nhacungcap['ten'];
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $noncc['thoigian']);
  }
  return $danhsach;
}

function danhsachtang() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_vattutang order by ten asc";
  return $db->all($sql);
}


function danhsachloaichi() {
  global $db, $data;

  $danhsach = [];
  $sql = "select * from pet_". PREFIX ."_taichinh_nhomchi where kichhoat = 1 order by id asc";
  $danhsachnhomchi = $db->all($sql);

  foreach ($danhsachnhomchi as $nhomchi) {
    $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where idnhom = $nhomchi[id] and kichhoat = 1";
    $danhsach []= [
      'id' => $nhomchi['id'],
      'ten' => $nhomchi['ten'],
      'danhsach' => $db->all($sql)
    ];
  }
  return $danhsach;
}

function danhsachchi() {
  global $db, $data;

  $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  // $danhsach = [
  //   0 => [
  //     'lanchi' => 0,
  //     'tongchi' => 0,
  //     'danhsach' => []
  //   ],
  //   [
  //     'lanchi' => 0,
  //     'tongchi' => 0,
  //     'danhsach' => []
  //   ],
  //   [
  //     'lanchi' => 0,
  //     'tongchi' => 0,
  //     'danhsach' => []
  //   ],
  //   []
  // ];

  $danhsachchi = [
    'lanchi' => 0,
    'tongchi' => 0,
    'danhsach' => []
  ];

  $sql = "select * from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang order by thoigian desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $chi) {
    $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where id = $chi[idloaichi]";
    $danhsach[$thutu]['tienchi'] = number_format($chi["giatri"]);
    $danhsach[$thutu]['loaichi'] = $db->fetch($sql)['ten'];
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $chi['thoigian']);
    $danhsachchi["danhsach"] []= $danhsach[$thutu];
  }


  // $sql = "select * from pet_". PREFIX ."_taichinh_chicodinh order by id desc";
  // $chicodinh = $db->all($sql);
  
  // foreach ($chicodinh as $thutu => $thongtin) {
  //   $chicodinh[$thutu]['loaichi'] = layloaichi($thongtin['idloaichi']);
  //   $danhsach[0]['tongchi'] += $thongtin['giatri'];
  // }

  // $danhsach[0]['lanchi'] = count($chicodinh);
  // $danhsach[0]['danhsach'] = $chicodinh;

  // $sql = "select * from pet_". PREFIX ."_taichinh_chivattu where thoigian between $dauthang and $cuoithang order by thoigian desc";
  // $vattu = $db->all($sql);

  // foreach ($vattu as $thutu => $thongtin) {
  //   $vattu[$thutu]['tong'] = $thongtin['soluong'] * $thongtin['giatri'];
  //   $vattu[$thutu]['thoigian'] = date('d/m/Y', $thongtin['thoigian']);
  //   $danhsach[1]['tongchi'] += $thongtin['soluong'] * $thongtin['giatri'];
  // }

  // $danhsach[1]['lanchi'] = count($vattu);
  // $danhsach[1]['danhsach'] = $vattu;

  // $sql = "select * from pet_". PREFIX ."_taichinh_chincc where thoigianthem = $dauthang and $cuoithang order by thoigian desc";
  // $chinhacungcap = $db->all($sql);
  
  // foreach ($chinhacungcap as $thutu => $thongtin) {
  //   $chinhacungcap[$thutu]['nhacungcap'] = laynhacungcap($thongtin['idnhacungcap']);
  //   $chinhacungcap[$thutu]['thoigian'] = date('d/m/Y', $thongtin['thoigian']);
  //   $danhsach[2]['tongchi'] += $thongtin['giatri'];
  // }

  // $danhsach[2]['lanchi'] = count($chinhacungcap);
  // $danhsach[2]['danhsach'] = $chinhacungcap;

  // $sql = "select * from pet_". PREFIX ."_taichinh_nhomchi where kichhoat = 1 order by id asc";
  // $nhomchi = $db->all($sql);

  // foreach ($nhomchi as $thongtin) {
  //   $sql = "select * from pet_". PREFIX ."_taichinh_chi where idloaichi in (select id as idloaichi from pet_". PREFIX ."_taichinh_loaichi where idnhom = $thongtin[id]) and thoigian between $dauthang and $cuoithang order by thoigian desc";
  //   $danhsachchi = $db->all($sql);
  //   $tongchi = 0;

  //   foreach ($danhsachchi as $thutu => $chi) {
  //     $danhsachchi[$thutu]['giatri'] = number_format($chi['giatri']);
  //     $danhsachchi[$thutu]['loaichi'] = layloaichi($chi['idloaichi']);
  //     $danhsachchi[$thutu]['thoigian'] = date('d/m/Y', $chi['thoigian']);
  //     $tongchi += $chi['giatri'];
  //   }
  //   $danhsach[3] []= [
  //     'id' => $thongtin['id'],
  //     'ten' => $thongtin['ten'],
  //     'lanchi' => count($danhsachchi),
  //     'tongchi' => $tongchi,
  //     'danhsach' => $danhsachchi
  //   ];
  // }

  // $sql = "select * from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang order by thoigian desc";
  // $danhsach = $db->all($sql);

  // foreach ($danhsach as $thutu => $chi) {
  //   $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where id = $chi[idloaichi]";
  //   echo("$sql <br>");
  //   $danhsach[$thutu]['loaichi'] = $db->fetch($sql)['ten'];
  //   $danhsach[$thutu]['thoigian'] = date('d/m/Y', $chi['thoigian']);
  // }
  return $danhsachchi;
}

function laynhacungcap($idnhacungcap) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where id = $idnhacungcap";
  $loaichi = $db->fetch($sql);
  return $loaichi['ten'];
}

function layloaichi($idloaichi) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where id = $idloaichi";
  $loaichi = $db->fetch($sql);
  return $loaichi['ten'];
}

function tongtien() {
  global $db;

  $sql = "select sum(giatri * soluong) as tong from pet_". PREFIX ."_taichinh_chivattu";
  $tongtien = $db->fetch($sql)['tong'];
  $sql = "select sum(tile) as tong from pet_". PREFIX ."_taichinh_chivattu";
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
  $sql = "select * from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by thoigian desc";
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
  $result['thongke'] = thongke();
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
  $result['thongke'] = thongke();
  return $result;
}

function themchi() {
  global $db, $data, $result;

  // switch ($data->dulieu->loaichi) {
  //   case '0':
  //     // chi cố định
  //     // kiểm tra loại chi với idnhom = 0
  //     $dulieu = $data->dulieu->dulieu[0];
  //     $giatri = purenumber($dulieu->giatri);
  //     $idloaichi = kiemtraloaichi($dulieu->loaichi);
  //     if ($data->dulieu->id) $sql = "update pet_". PREFIX ."_taichinh_chicodinh set idloaichi = $idloaichi, giatri = $giatri where id = ". $data->dulieu->id;
  //     else $sql = "insert into pet_". PREFIX ."_taichinh_chicodinh (idloaichi, giatri) values($idloaichi, $giatri)";
  //     $db->query($sql);
  //   break;
  //   case '-1':
  //     // chi vật tư
  //     $dulieu = $data->dulieu->dulieu[1];
  //     $dulieu->giatri = purenumber($dulieu->giatri);
  //     $dulieu->soluong = purenumber($dulieu->soluong);
    
  //     if ($data->dulieu->id) {
  //       $sql = "update pet_". PREFIX ."_taichinh_chivattu set ten = '$dulieu->ten', donvi = '$dulieu->donvi', soluong = $dulieu->soluong, thoigian = $thoigian, giatri = $dulieu->giatri, ghichu = '$dulieu->ghichu' where id = ". $data->dulieu->id;
  //       $db->query($sql);
  //     }
  //     else {
  //       $sql = "insert into pet_". PREFIX ."_taichinh_chivattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$dulieu->ten', '$dulieu->donvi', $dulieu->soluong, $thoigian, $dulieu->giatri, '0', '$dulieu->ghichu')";
  //       $db->query($sql);
  
  //       $sql = "insert into pet_". PREFIX ."_vattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$dulieu->ten', '$dulieu->donvi', $dulieu->soluong, $thoigian, $dulieu->giatri, '0', '$dulieu->ghichu')";
  //       $idvattu = $db->insertid($sql);

  //       foreach ($dulieu->danhdau as $idtang => $giatri) {
  //         if ($giatri) {
  //           $sql = "insert into pet_". PREFIX ."_vattunoitang (idvattu, idtang) values($idvattu, $idtang)";
  //           $db->query($sql);
  //         }
  //       }
  //     }

  //   break;
  //   case '-2':
  //     // chi nhà cung cấp
  //     $dulieu = $data->dulieu->dulieu[2];
  //     $giatri = purenumber($dulieu->giatri);
  //     $dauthang = strtotime(date('Y/m/1', $thoigian));

  //     if ($data->dulieu->id) {
  //       $sql = "update pet_". PREFIX ."_taichinh_chincc set idnhacungcap = $dulieu->idnhacungcap, noidung = '$dulieu->noidung', giatri = $giatri, thoigian = $thoigian, ghichu = '$dulieu->ghichu'";
  //       $db->query($sql);
  //     }
  //     else {
  //       $sql = "insert into pet_". PREFIX ."_taichinh_chincc (idnhacungcap, noidung, giatri, thoigian, thoigianthem, ghichu) values($dulieu->idnhacungcap, '$dulieu->noidung', $giatri, $thoigian, $dauthang, '$dulieu->ghichu')";
  //       $db->query($sql);
  //     }
  //   break;
  //   default:
  //     // chi khác
  //     $dulieu = $data->dulieu->dulieu[3];
  //     $giatri = purenumber($dulieu->giatri);
  //     if ($data->dulieu->id) {
  //       $sql = "update pet_". PREFIX ."_taichinh_chi set idloaichi = $dulieu->idloaichi, giatri = $giatri, thoigian = $thoigian, ghichu = '$dulieu->ghichu'";
  //       $db->query($sql);
  //     }
  //     else {
  //       $sql = "insert into pet_". PREFIX ."_taichinh_chi (idloaichi, giatri, thoigian, ghichu) values($dulieu->idloaichi, $giatri, $thoigian, '$dulieu->ghichu')";
  //       $data->id = $db->insertid($sql);
  //     }
  // }

  $dulieu = $data->dulieu;
  $thoigian = isodatetotime($dulieu->thoigian);
  $idloaichi = kiemtraloaichi($dulieu->loaichi);
  $giatri = purenumber($dulieu->tienchi);

  // idloaichi, loaitien, giatri, ghichu, thoigian, codinh
  if ($dulieu->id) {
    $sql = "update pet_". PREFIX ."_taichinh_chi set idloaichi = $idloaichi, loaitien = $dulieu->loaitien, giatri = '$giatri', thoigian = $thoigian where id = $dulieu->id";
  }
  else {
    $sql = "insert into pet_". PREFIX ."_taichinh_chi (idloaichi, loaitien, giatri, ghichu, thoigian, codinh) values($idloaichi, $dulieu->loaitien, $giatri, '', $thoigian, 0)";
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachchi();
  $result['thongke'] = thongke();
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

function themloaichivattu() {
  global $db, $data, $result;

  $sql = "insert into pet_". PREFIX ."_vattu_loaichi (loaichi) values('$data->ten')";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['loaichivattu'] = loaichivattu();
  return $result;
}

function xoaloaichivattu() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_vattu_loaichi where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['loaichivattu'] = loaichivattu();
  return $result;
}

function loaichivattu() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_vattu_loaichi order by id desc";
  return $db->all($sql);
}

function themnonhacungcap() {
  global $db, $data, $result;

  $dulieu = $data->dulieu;
  $thoigian = isodatetotime($dulieu->thoigian);
  $dauthang = isodatetotime(date('Y/m/1', $thoigian));
  $giatri = purenumber($dulieu->giatri);

  if (empty($dulieu->id)) {
    $sql = "insert into pet_". PREFIX ."_taichinh_noncc (idnhacungcap, noidung, giatri, thoigian, thoigianthem, ghichu) values($dulieu->idnhacungcap, '$dulieu->noidung', $giatri, $thoigian, $dauthang, '$dulieu->ghichu')";
    $data->id = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_taichinh_noncc set idnhacungcap = $dulieu->idnhacungcap, noidung = '$dulieu->noidung', giatri = $giatri, thoigian = $thoigian, ghichu = '$dulieu->ghichu' where id = $dulieu->id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachnoncc();
  $result['thongke'] = thongke();
  return $result;
}

function xoachi() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_taichinh_chi where id = $data->id";
  // switch ($data->loaichi) {
  //   case '0':
  //     $sql = "delete from pet_". PREFIX ."_taichinh_chicodinh where id = $data->id";
  //     break;
  //   case '1':
  //     $sql = "delete from pet_". PREFIX ."_taichinh_chivattu where id = $data->id";
  //     break;
  //   case '2':
  //     $sql = "delete from pet_". PREFIX ."_taichinh_chincc where id = $data->id";
  //     break;
  // }
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachchi();
  $result['thongke'] = thongke();
  return $result;
}

function xoanoncc() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_taichinh_noncc where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachnoncc();
  $result['thongke'] = thongke();
  return $result;
}

function themloaichi() {
  global $db, $data, $result;
  
  $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where ten = '$data->ten' and idnhom = $data->nhomchi";
  if (empty($db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_taichinh_loaichi (ten, idnhom) values('$data->ten', $data->nhomchi)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachloaichi();
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

function themnhomchi() {
  global $db, $data, $result;
  
  $sql = "insert into pet_". PREFIX ."_taichinh_nhomchi (ten) values('$data->ten')";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachloaichi();
  return $result;
}

function xoanhomchi() {
  global $db, $data, $result;
  
  $sql = "update pet_". PREFIX ."_taichinh_nhomchi set kichhoat = 0 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachloaichi();
  return $result;
}

function khoitaoimport() {
  global $data, $db, $result;

  $tonkho = laydulieutonkho();
  $result['status'] = 1;
  $result['loaichivattu'] = loaichivattu();
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

  $thoigian = date('mY', isodatetotime($data->thoigian));
  $thangtruoc = date('mY', strtotime('first day of last month', isodatetotime($data->thoigian)));
  // lấy 
  $sql2 = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkho$thangtruoc'";
  $sql3 = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkhodau$thoigian'";
  if (empty($tongkho = $db->fetch($sql3)) && empty($tongkho = $db->fetch($sql2))) $tongkho = ['value' => 0];

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
  $thoigian = date('mY', isodatetotime($data->thoigian));
  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongkhodau$thoigian'";
  if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('taichinh', 'tongkhodau$thoigian', $bandau, 1)";
  else $sql = "update pet_". PREFIX ."_config set value = $bandau where module = 'taichinh' and name = 'tongkhodau$thoigian'";
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
      'thoigian' => 'C2',
      'ghichu' => 'D2',
    ],
    [
      'dienthoai' => 'A2',
      'khachhang' => 'B2',
      'tienno' => 'C2',
    ],
    [
      'nhacungcap' => 'A2',
      'noidung' => 'B2',
      'thanhtoan' => 'C2',
      'noncc' => 'D2',
      'thoigian' => 'E2',
      'dathanhtoan' => 'F2',
      'danhaphang' => 'G2',
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
  global $db, $data, $result;

  $dulieu = docdulieu();
  $error = [];
  $dathem = 0;
  $tongcong = count($dulieu);

  $sql = "select * from pet_". PREFIX ."_vattu_loaichi";
  $danhsachloaichi = $db->arr($sql, 'loaichi');
  // danh sách chi vật tư, nếu loạii chi trong nhóm chi vật tư thì không thêm vào chi mà chỉ thêm vào vật tư 
  if (!isset($data->thoigian)) $thoigian = time();
  else $thoigian = isodatetotime($data->thoigian);
  $dauthang = strtotime(date("Y/m/1", $thoigian));
  $cuoithang = strtotime(date("Y/m/t", $thoigian)) + 60 * 60 * 24 - 1;
  $sql = "delete from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang";
  $db->query($sql);

  foreach ($dulieu as $thutu => $thongtin) {
    $kiemtra = false;
    if (!empty($thongtin['loaichi'] && !empty($thongtin['tienchi']))) {
      $ngaygio = explode(' ', $thongtin['thoigian']);
      $ngay = explode('/', $ngaygio[0]);
      if (empty($ngaygio[1])) $ngaygio[1] = "00:00:00";
      $gio = $ngaygio[1];
      $thoigian = strtotime("$ngay[2]/$ngay[1]/$ngay[0] $gio");
      if ($thoigian) {
        $giatri = abs($thongtin['tienchi']);
        if (in_array($thongtin['loaichi'], $danhsachloaichi) !== false) {
          $sql = "select * from pet_". PREFIX ."_vattu where ten = '$thongtin[ghichu]' and giatri = $giatri";
          if (empty($db->fetch($sql))) {
            $sql = "insert into pet_". PREFIX ."_vattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$thongtin[ghichu]', '', 1, $thoigian, $giatri, 0, '')";
            $db->query($sql);

            $sql = "insert into pet_". PREFIX ."_taichinh_chivattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$thongtin[ghichu]', '', 1, $thoigian, $giatri, 0, '')";
            $db->query($sql);
          }
        }
        else {
          $idloaichi = kiemtraloaichi($thongtin['loaichi'], 5);
          $sql = "insert into pet_". PREFIX ."_taichinh_chi (idloaichi, giatri, ghichu, thoigian) values($idloaichi, $giatri, '', $thoigian)";
          $db->query($sql);

          $dathem ++;
          $kiemtra = true;
        }
      }
    }
    if (!$kiemtra) $error []= "Lỗi: dòng $thutu loại chi $thongtin[loaichi] ". number_format(intval($thongtin['tienchi'])) ." lúc $thongtin[thoigian]";
  }
  $result['status'] = 1;
  $result['messenger'] = "Đã thêm $dathem/$tongcong mục chi";
  $result['error'] = $error;
  return $result;
}

function kiemtraloaichi($loaichi, $idnhom = 0) {
  global $db;
  
  if ($idnhom == 5) {
    $sql = "select * from pet_". PREFIX ."_taichinh_nhomchi where id = 5";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_taichinh_nhomchi (id, ten, kichhoat) values(5, 'Chi thường xuyên', 1)";
      $db->query($sql);
    }
  }

  $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where ten = '$loaichi' and idnhom = $idnhom";
  if ($dulieuloaichi = $db->fetch($sql)) return $dulieuloaichi['id'];

  $sql = "insert into pet_". PREFIX ."_taichinh_loaichi (ten, idnhom, kichhoat) values('$loaichi', $idnhom, 1)";
  return $db->insertid($sql);
}

function importno() {
  global $db, $data, $result;

  if (!isset($data->thoigian)) $thoigian = time();
  else $thoigian = isodatetotime($data->thoigian);
  $dulieu = docdulieu();
  $tongno = 0;
  $mathoigian = date('mY', $thoigian);
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

  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'khachno$mathoigian'";
  if (empty($cauhinh = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('taichinh', 'khachno$mathoigian', $tongno, 0)";
  }
  else $sql = "update pet_". PREFIX ."_config set value = $tongno where id = $cauhinh[id]";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = "Tổng nợ khách hàng ". number_format($tongno);
  $result['error'] = $error;
  return $result;
}

function importncc() {
  global $db, $result, $data;

  $dulieu = docdulieu();
  $error = [];
  $dathem = 0;
  $tongcong = count($dulieu);
  $tongno = 0;
  $tongthanhtoan = 0;
  $tongnccno = 0;
  if (!isset($data->thoigian)) $thoigian = time();
  else $thoigian = isodatetotime($data->thoigian);
  
  $dauthang = strtotime(date('Y/m/1', $thoigian));
  $cuoithang = strtotime(date('Y/m/t', $thoigian)) + 60 * 60 * 24 - 1;

  $sql = "delete from pet_". PREFIX ."_taichinh_chincc where thoigianthem between $dauthang and $cuoithang";
  $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_taichinh_noncc where thoigianthem between $dauthang and $cuoithang";
  $db->query($sql);

  // nếu các mục 
  foreach ($dulieu as $thutu => $thongtin) {
    $kiemtra = false;
    if (!empty($thongtin['nhacungcap'])) {
      $ngay = explode('/', $thongtin['thoigian']);
      if (count($ngay) >= 3) {
        $ngaythang = strtotime("$ngay[2]/$ngay[1]/$ngay[0]");
        $idnhacungcap = kiemtranhacungcap($thongtin['nhacungcap']);
      }
      else $ngaythang = 0;

      if (!empty($ngaythang)) {
        if (!empty($thongtin['thanhtoan'])) {
          $table = "chincc";
          $giatri = purenumber($thongtin['thanhtoan']);
          $sql = "insert into pet_". PREFIX ."_taichinh_$table (idnhacungcap, noidung, giatri, thoigian, thoigianthem, ghichu) values($idnhacungcap, '$thongtin[noidung]', $giatri, $ngaythang, $dauthang, '')";
          $db->query($sql);
          
          $tongthanhtoan += $giatri;
          // kiểm tra đã nhập hàng chưa, nếu chưa thì + vào tồn kho
          if ($ngaythang >= $dauthang && $ngaythang <= $cuoithang && mb_strtolower($thongtin['danhaphang']) == 'chưa') {
            $tongnccno += $giatri;
          }
        }

        if (!empty($thongtin['noncc'])) {
          $table = "noncc";
          $giatri = purenumber($thongtin['noncc']);
          $sql = "insert into pet_". PREFIX ."_taichinh_$table (idnhacungcap, noidung, giatri, thoigian, thoigianthem, ghichu) values($idnhacungcap, '$thongtin[noidung]', $giatri, $thoigian, $dauthang, '')";
          $db->query($sql);
          $tongno += $giatri;
        }

        $kiemtra = true;
      }
    }
    
    if (!$kiemtra) $error []= "Lỗi: dòng $thutu nhà cungcấp $thongtin[nhacungcap] nội dung $thongtin[noidung] lúc $thongtin[thoigian]";
  }

  $mathoigian = date('mY', $thoigian);
  $sql = "select * from pet_". PREFIX ."_config where module = 'taichinh' and name = 'tongnccno$mathoigian'";
  if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('taichinh', 'tongnccno$mathoigian', $tongnccno, 1)";
  else $sql = "update pet_". PREFIX ."_config set value = $tongnccno where module = 'taichinh' and name = 'tongnccno$mathoigian'";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = "Tổng nợ nhà cung cấp ". number_format($tongno) .", tổng thanh toán ". number_format($tongthanhtoan);
  $result['error'] = $error;
  return $result;
}

function kiemtranhacungcap($nhacungcap) {
  global $db;
  $sql = "select * from pet_". PREFIX ."_taichinh_nhacungcap where ten = '$nhacungcap'";
  if (empty($ncc = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_taichinh_nhacungcap (ten) values('$nhacungcap')";
    $ncc = ['id' => $db->insertid($sql)];
  }
  return $ncc['id'];
}

function laycauhinhimport() {
  global $db;

  $cauhinh = [
    0 => [
      'loaichi' => 'A2',
      'tienchi' => 'B2',
      'thoigian' => 'C2',
      'ghichu' => 'D2'
    ],
    [
      'dienthoai' => 'A2',
      'khachhang' => 'B2',
      'tienno' => 'C2',
    ],
    [
      'nhacungcap' => 'A2',
      'noidung' => 'B2',
      'thanhtoan' => 'C2',
      'noncc' => 'D2',
      'thoigian' => 'E2',
      'dathanhtoan' => 'F2',
      'danhaphang' => 'G2',
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

function luucauhinhdoanhthu() {
  global $data, $db, $result;
  
  foreach ($data->cauhinh as $bien => $giatri) {
    $sql = "select * from pet_". PREFIX ."_config where module = 'loinhuan' and name = 'doanhthu$bien'";
    if (!empty($truongcauhinh = $db->fetch($sql))) $sql = "update pet_". PREFIX ."_config set value = '$giatri' where id = $truongcauhinh[id]";
    else $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('loinhuan', 'doanhthu$bien', '$giatri', 0)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  return $result;
}

function themnaptien() {
  global $data, $db, $result, $res;

  $thoigian = isodatetotime($data->thoigiannap);
  if (isset($data->id)) {
    $sql = "update pet_". PREFIX ."_taichinh_naptien set naptien = $data->naptien, thoigian = $thoigian where id = $data->id";
  }
  else {
    $sql = "insert into pet_". PREFIX ."_taichinh_naptien (naptien, thoigian) values($data->naptien, $thoigian)";
  }
  $db->query($sql);
  $result['status'] = 1;
  $result['thongke'] = thongke();
  $result['danhsachnaptien'] = danhsachnaptien();
  return $result;
}

function xoanap() {
  global $data, $db, $result, $res;

  $sql = "delete from pet_". PREFIX ."_taichinh_naptien where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['thongke'] = thongke();
  $result['danhsachnaptien'] = danhsachnaptien();
  return $result;
}
