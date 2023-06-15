<?php

function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachthu();
  $result['danhsachchi'] = danhsachchi();
  $result['danhsachloaichi'] = danhsachloaichi();
  $result['danhsachnhacungcap'] = danhsachnhacungcap();
  $result['danhsachncc'] = danhsachnoncc();
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

  $sql = "select sum(tienmat) as tong from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongtienmat = $db->fetch($sql)['tong'];

  $sql = "select sum(nganhang) as tong from pet_". PREFIX ."_taichinh_thu where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongnganhang = $db->fetch($sql)['tong'];

  $sql = "select value as tong from pet_". PREFIX ."_config where module = 'taichinh' and name = 'khachno$mathoigian'";
  if (empty($tongkho = $db->fetch($sql))) $tongkhachno = 0;
  else $tongkhachno = $tongkho['tong'];

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_noncc where (thoigian between $dauthang and $cuoithang) order by id desc";
  $tongnonhacungcap = $db->fetch($sql)['tong'];
  $tongkho = laydulieutonkho();
 
  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang";
  $chithuongxuyen = intval($db->fetch($sql)['tong']);

  $sql = "select sum(tongluong) as tong from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $chiluongthuong = intval($db->fetch($sql)['tong']);

  $sql = "select sum(cophan) as tong from pet_". PREFIX ."_luong_dulieu where thoigian between $dauthang and $cuoithang";
  $cophan = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chincc where thoigian between $dauthang and $cuoithang";
  $chinhacungcap = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri) as tong from pet_". PREFIX ."_taichinh_chicodinh";
  $chicodinh = intval($db->fetch($sql)['tong']);

  $sql = "select sum(giatri * soluong) as tong from pet_". PREFIX ."_taichinh_chivattu where thoigian between $dauthang and $cuoithang";
  $chitaisan = intval($db->fetch($sql)['tong']);

  $tongchi = $chithuongxuyen + $chiluongthuong + $chinhacungcap + $chitaisan + $chicodinh;
  if (empty($tongkho['thangnay'])) $kho = 0;
  else $kho = $tongkho['bandau'] - $tongkho['thangnay'];

  // var_dump($tongthu);
  // var_dump($tongchi);
  //   var_dump($tongkhachno);
  //     var_dump($tongnonhacungcap);
  //     var_dump($kho);
  return [
    'tienmat' => number_format($tongtienmat), 
    'nganhang' => number_format($tongnganhang), 
    'tongthu' => number_format($tongtienmat + $tongnganhang), 
    'chithuongxuyen' => number_format($chithuongxuyen),
    'chiluongthuong' => number_format($chiluongthuong),
    'chinhacungcap' => number_format($chinhacungcap),
    'chitaisan' => number_format($chitaisan),
    'tongchi' => number_format($tongchi), 
    'cophan' => number_format($cophan), 
    'chicodinh' => number_format($chicodinh), 
    'tongkhachno' => number_format($tongkhachno), 
    'tongnonhacungcap' => number_format($tongnonhacungcap), 
    'tongtaisan' => number_format($tongkho['thangnay']), 
    'loinhuan' => number_format($tongtienmat + $tongnganhang - $tongchi + $tongkhachno - $tongnonhacungcap + $kho),
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

  $danhsach = [
    0 => [
      'lanchi' => 0,
      'tongchi' => 0,
      'danhsach' => []
    ],
    [
      'lanchi' => 0,
      'tongchi' => 0,
      'danhsach' => []
    ],
    [
      'lanchi' => 0,
      'tongchi' => 0,
      'danhsach' => []
    ],
    []
  ];

  $sql = "select * from pet_". PREFIX ."_taichinh_chicodinh order by id desc";
  $chicodinh = $db->all($sql);
  
  foreach ($chicodinh as $thutu => $thongtin) {
    $chicodinh[$thutu]['loaichi'] = layloaichi($thongtin['idloaichi']);
    $danhsach[0]['tongchi'] += $thongtin['giatri'];
  }

  $danhsach[0]['lanchi'] = count($chicodinh);
  $danhsach[0]['danhsach'] = $chicodinh;

  $sql = "select * from pet_". PREFIX ."_taichinh_chivattu where thoigian between $dauthang and $cuoithang order by thoigian desc";
  $vattu = $db->all($sql);

  foreach ($vattu as $thutu => $thongtin) {
    $vattu[$thutu]['tong'] = $thongtin['soluong'] * $thongtin['giatri'];
    $vattu[$thutu]['thoigian'] = date('d/m/Y', $thongtin['thoigian']);
    $danhsach[1]['tongchi'] += $thongtin['giatri'];
  }

  $danhsach[1]['lanchi'] = count($vattu);
  $danhsach[1]['danhsach'] = $vattu;

  $sql = "select * from pet_". PREFIX ."_taichinh_chincc where thoigian between $dauthang and $cuoithang order by thoigian desc";
  $chinhacungcap = $db->all($sql);
  
  foreach ($chinhacungcap as $thutu => $thongtin) {
    $chinhacungcap[$thutu]['nhacungcap'] = laynhacungcap($thongtin['idnhacungcap']);
    $chinhacungcap[$thutu]['thoigian'] = date('d/m/Y', $thongtin['thoigian']);
    $danhsach[2]['tongchi'] += $thongtin['giatri'];
  }

  $danhsach[2]['lanchi'] = count($chinhacungcap);
  $danhsach[2]['danhsach'] = $chinhacungcap;

  $sql = "select * from pet_". PREFIX ."_taichinh_nhomchi where kichhoat = 1 order by id asc";
  $nhomchi = $db->all($sql);

  foreach ($nhomchi as $thongtin) {
    $sql = "select * from pet_". PREFIX ."_taichinh_chi where idloaichi in (select id as idloaichi from pet_". PREFIX ."_taichinh_loaichi where idnhom = $thongtin[id]) and thoigian between $dauthang and $cuoithang order by thoigian desc";
    $danhsachchi = $db->all($sql);
    $tongchi = 0;

    foreach ($danhsachchi as $thutu => $chi) {
      $danhsachchi[$thutu]['giatri'] = number_format($chi['giatri']);
      $danhsachchi[$thutu]['loaichi'] = layloaichi($chi['idloaichi']);
      $danhsachchi[$thutu]['thoigian'] = date('d/m/Y', $chi['thoigian']);
      $tongchi += $chi['giatri'];
    }
    $danhsach[3] []= [
      'id' => $thongtin['id'],
      'ten' => $thongtin['ten'],
      'lanchi' => count($danhsachchi),
      'tongchi' => $tongchi,
      'danhsach' => $danhsachchi
    ];
  }

  // $sql = "select * from pet_". PREFIX ."_taichinh_chi where thoigian between $dauthang and $cuoithang order by thoigian desc";
  // $danhsach = $db->all($sql);

  // foreach ($danhsach as $thutu => $chi) {
  //   $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where id = $chi[idloaichi]";
  //   echo("$sql <br>");
  //   $danhsach[$thutu]['loaichi'] = $db->fetch($sql)['ten'];
  //   $danhsach[$thutu]['thoigian'] = date('d/m/Y', $chi['thoigian']);
  // }
  return $danhsach;
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

  $thoigian = isodatetotime($data->dulieu->thoigian);

  switch ($data->dulieu->loaichi) {
    case '0':
      // chi cố định
      // kiểm tra loại chi với idnhom = 0
      $dulieu = $data->dulieu->dulieu[0];
      $giatri = purenumber($dulieu->giatri);
      $idloaichi = kiemtraloaichi($dulieu->loaichi);
      if ($data->dulieu->id) $sql = "update pet_". PREFIX ."_taichinh_chicodinh set idloaichi = $idloaichi, giatri = $giatri where id = ". $data->dulieu->id;
      else $sql = "insert into pet_". PREFIX ."_taichinh_chicodinh (idloaichi, giatri) values($idloaichi, $giatri)";
      $db->query($sql);
    break;
    case '-1':
      // chi vật tư
      $dulieu = $data->dulieu->dulieu[1];
      $dulieu->giatri = purenumber($dulieu->giatri);
      $dulieu->soluong = purenumber($dulieu->soluong);
    
      if ($data->dulieu->id) {
        $sql = "update pet_". PREFIX ."_taichinh_chivattu set ten = '$dulieu->ten', donvi = '$dulieu->donvi', soluong = $dulieu->soluong, thoigian = $thoigian, giatri = $dulieu->giatri, ghichu = '$dulieu->ghichu' where id = ". $data->dulieu->id;
        $db->query($sql);
      }
      else {
        $sql = "insert into pet_". PREFIX ."_taichinh_chivattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$dulieu->ten', '$dulieu->donvi', $dulieu->soluong, $thoigian, $dulieu->giatri, '0', '$dulieu->ghichu')";
        $db->query($sql);
  
        $sql = "insert into pet_". PREFIX ."_vattu (ten, donvi, soluong, thoigian, giatri, tile, ghichu) values('$dulieu->ten', '$dulieu->donvi', $dulieu->soluong, $thoigian, $dulieu->giatri, '0', '$dulieu->ghichu')";
        $idvattu = $db->insertid($sql);

        foreach ($dulieu->danhdau as $idtang => $giatri) {
          if ($giatri) {
            $sql = "insert into pet_". PREFIX ."_vattunoitang (idvattu, idtang) values($idvattu, $idtang)";
            $db->query($sql);
          }
        }
      }

    break;
    case '-2':
      // chi nhà cung cấp
      $dulieu = $data->dulieu->dulieu[2];
      $giatri = purenumber($dulieu->giatri);

      if ($data->dulieu->id) {
        $sql = "update pet_". PREFIX ."_taichinh_chincc set idnhacungcap = $dulieu->idnhacungcap, noidung = '$dulieu->noidung', giatri = $giatri, thoigian = $thoigian, ghichu = '$dulieu->ghichu'";
        $db->query($sql);
      }
      else {
        $sql = "insert into pet_". PREFIX ."_taichinh_chincc (idnhacungcap, noidung, giatri, thoigian, ghichu) values($dulieu->idnhacungcap, '$dulieu->noidung', $giatri, $thoigian, '$dulieu->ghichu')";
        $db->query($sql);
      }
    break;
    default:
      // chi khác
      $dulieu = $data->dulieu->dulieu[3];
      $giatri = purenumber($dulieu->giatri);
      if ($data->dulieu->id) {
        $sql = "update pet_". PREFIX ."_taichinh_chi set idloaichi = $dulieu->idloaichi, giatri = $giatri, thoigian = $thoigian, ghichu = '$dulieu->ghichu'";
        $db->query($sql);
      }
      else {
        $sql = "insert into pet_". PREFIX ."_taichinh_chi (idloaichi, giatri, thoigian, ghichu) values($dulieu->idloaichi, $giatri, $thoigian, '$dulieu->ghichu')";
        $data->id = $db->insertid($sql);
      }
  }

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

function themnonhacungcap() {
  global $db, $data, $result;

  $dulieu = $data->dulieu;
  $thoigian = isodatetotime($dulieu->thoigian);
  $giatri = purenumber($dulieu->giatri);

  if (empty($dulieu->id)) {
    $sql = "insert into pet_". PREFIX ."_taichinh_noncc (idnhacungcap, noidung, giatri, thoigian, ghichu) values($dulieu->idnhacungcap, '$dulieu->noidung', $giatri, $thoigian, '$dulieu->ghichu')";
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
  switch ($data->loaichi) {
    case '0':
      $sql = "delete from pet_". PREFIX ."_taichinh_chicodinh where id = $data->id";
      break;
    case '1':
      $sql = "delete from pet_". PREFIX ."_taichinh_chivattu where id = $data->id";
      break;
    case '2':
      $sql = "delete from pet_". PREFIX ."_taichinh_chincc where id = $data->id";
      break;
  }
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
  $thoigian = date('mY', isodatetotime($data->thoigian));
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
      'thanhtoan' => 'C2',
      'noncc' => 'D2',
      'thoigian' => 'E2',
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
      if (empty($ngaygio[1])) $ngaygio[1] = "00:00:00";
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

function kiemtraloaichi($loaichi, $idnhom = 0) {
  global $db;
  
  $sql = "select * from pet_". PREFIX ."_taichinh_loaichi where ten = '$loaichi' and idnhom = $idnhom";
  if ($dulieuloaichi = $db->fetch($sql)) return $dulieuloaichi['id'];

  $sql = "insert into pet_". PREFIX ."_taichinh_loaichi (ten, idnhom, kichhoat) values('$loaichi', $idnhom, 1)";
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
  $tongthanhtoan = 0;
  // 'nhacungcap' nếu không có nhà cung cấp, bỏ qua
  // 'noidung' 
  // 'thanhtoan' nếu không có giá trị, bỏ qua
  // 'noncc' nếu không có giá trị, bỏ qua
  // 'thoigian' nếu thời gian bị sai, bỏ qua
  foreach ($dulieu as $thutu => $thongtin) {
    $kiemtra = false;
    if (!empty($thongtin['nhacungcap'])) {
      $ngay = explode('/', $thongtin['thoigian']);
      $thoigian = strtotime("$ngay[2]/$ngay[1]/$ngay[0]");
      // idnhacungcap	noidung	giatri	thoigian	ghichu
      $idnhacungcap = kiemtranhacungcap($thongtin['nhacungcap']);
      $table = '';
      if (!empty($thongtin['thanhtoan'])) {
        $giatri = purenumber($thongtin['thanhtoan']);
        $tongthanhtoan += $giatri;
        $table = 'chincc';
      }
      else if (!empty($thongtin['noncc'])) {
        $giatri = purenumber($thongtin['noncc']);
        $tongno += $giatri;
        $table = 'noncc';
      }

      if (!empty($table) && !empty($thoigian)) {
        $sql = "select * from pet_". PREFIX ."_taichinh_$table where idnhacungcap = $idnhacungcap and giatri = $giatri and thoigian = $thoigian";
        if (empty($taichinh = $db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_taichinh_$table (idnhacungcap, noidung, giatri, thoigian, ghichu) values($idnhacungcap, '$thongtin[noidung]', $giatri, $thoigian, '')";
          if (!empty($thongtin['noncc'])) echo "$sql <br>";        
        }
        else {
          $sql = "update pet_". PREFIX ."_taichinh_$table set idnhacungcap = $idnhacungcap, giatri = $giatri, thoigian = $thoigian, noidung = '$thongtin[noidung]' where id = $taichinh[id]";
        }
        $db->query($sql);
        $kiemtra = true;
      }
    }
    if (!$kiemtra) $error []= "Lỗi: dòng $thutu nhà cungcấp $thongtin[nhacungcap] nội dung $thongtin[noidung] lúc $thongtin[thoigian]";
  }

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
      'thanhtoan' => 'C2',
      'noncc' => 'D2',
      'thoigian' => 'E2',
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
