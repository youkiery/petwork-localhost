<?php
function filter() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['time'] = time();
  $result['list'] = getList();

  return $result;
}

function init() {
  global $data, $db, $result;

  $userid = checkuserid();

  $sql = "select * from pet_". PREFIX ."_user_per where module = 'spa' and type = 2 and userid = $userid";
  if (!empty($db->fetch($sql))) {
    $sql = "select a.userid, b.fullname as name from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.module = 'doctor' and a.type = 1";
    $result['doctor'] = $db->all($sql);
  }
  else $result['doctor'] = array();

  $result['status'] = 1;
  $result['time'] = time();
  $result['list'] = getList();
  $result['near'] = nearlist();
  $result['count'] = nearcount();
  return $result;
}

// function auto() {
//   global $data, $db, $result;

//   $data->ctime = $data->ctime / 1000;
//   $today = date('d/m/Y');
//   $ctime = date('d/m/Y', $data->ctime);
  
//   if ($today !== $ctime) {
//     $result['status'] = 1;
//     $result['list'] = getList();
//   }
//   else {
//     $sql = "select id from pet_". PREFIX ."_spa where utime > $data->ctime";
//     $result['status'] = 1;
  
//     if (!empty($db->fetch($sql))) {
//       $result['time'] = time();
//       $result['list'] = getList();
//     }
//   }
  
//   if (empty($result['list'])) $result['list']= array();

//   return $result;
// }

function updatesc() {
  global $data, $db, $result;

  $customerid = checkcustomer($data->phone, $data->name);
  $image = implode(',', $data->image);
  $time = isodatetotime($data->time);
  $ctime = time();
  
  $sql = "update pet_". PREFIX ."_spa_schedule set customerid = $customerid, note = '$data->note', image = '$image', time = $time where id = $data->id";
  $db->query($sql);

  $result['list'] = nearlist();
  $result['count'] = nearcount();
  $result['status'] = 1;
  return $result;
}

function removesc() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_spa_schedule where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa hồ sơ';
  $result['list'] = nearlist();
  $result['c'] = nearcount();
  
  return $result;
}

function toggle() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_config set module = 'spa-deleted' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function removetype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_config set module = 'spa-deleted' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function updatetype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_config set name = '$data->name' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function inserttype() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_config order by value desc limit 1";
  $c = $db->fetch($sql);

  $sql = "insert into pet_". PREFIX ."_config (module, name, value) values('spa', '$data->name', ". (intval($c['value'] + 1)) .")";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function uptype() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_config where id = $data->id";
  $c = $db->fetch($sql);
  $sql = "select * from pet_". PREFIX ."_config where id = $data->id2";
  $c2 = $db->fetch($sql);

  $sql = "update pet_". PREFIX ."_config set value = $c[value] where id = $data->id2";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_config set value = $c2[value] where id = $data->id";
  $db->query($sql);

  $sql = "select id, name, value, alt from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $spa = $db->all($sql);
  $ds = array();

  foreach ($spa as $key => $s) {
    if ($s['alt']) $ds []= $s['id'];
    $spa[$key]['check'] = 0;
  }

  $result['status'] = 1;
  $result['list'] = $spa;
  $result['default'] = $ds;
  return $result;
}

function downtype() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_config where id = $data->id";
  $c = $db->fetch($sql);
  $sql = "select * from pet_". PREFIX ."_config where id = $data->id2";
  $c2 = $db->fetch($sql);

  $sql = "update pet_". PREFIX ."_config set value = $c[value] where id = $data->id2";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_config set value = $c2[value] where id = $data->id";
  $db->query($sql);

  $sql = "select id, name, value, alt from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $spa = $db->all($sql);
  $ds = array();

  foreach ($spa as $key => $s) {
    if ($s['alt']) $ds []= $s['id'];
    $spa[$key]['check'] = 0;
  }

  $result['status'] = 1;
  $result['list'] = $spa;
  $result['default'] = $ds;
  return $result;
}

function toggletype() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_config set alt = '". (intval(!$data->alt) ? 1 : '') ."' where id = $data->id";
  $db->query($sql);

  $sql = "select id, name, value, alt from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $spa = $db->all($sql);
  $ds = array();

  foreach ($spa as $key => $s) {
    if ($s['alt']) $ds []= $s['id'];
    $spa[$key]['check'] = 0;
  }

  $result['status'] = 1;
  $result['list'] = $spa;
  $result['default'] = $ds;
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_spa where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['time'] = time();
  $result['list'] = getList();
  
  return $result;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_spa set utime = ". time() .", duser = doctorid, status = 1 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['time'] = time();
  $result['list'] = getList();
  
  return $result;
}

function pick() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_". PREFIX ."_spa set duser = $userid where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function picktrans() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_". PREFIX ."_spa set duser = $data->uid where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function called() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_". PREFIX ."_spa set utime = ". time() .", duser = doctorid, status = 2 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 2;
  $result['time'] = time();
  $result['list'] = getList();

  return $result;
}

function returned() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_spa set utime = ". time() .", duser = doctorid, status = 3 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 3;
  $result['time'] = time();
  $result['list'] = getList();

  return $result;
}

function report() {
  global $data, $db, $result;

  $xtra = '';
  if ($data->status == 0) $xtra = 'status = 1,';
  $sql = "update pet_". PREFIX ."_spa set $xtra duser = $data->uid, dimage = '". implode(',', $data->image) ."' where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function coverData($data) {
  global $db;

  $list = array();
  foreach ($data as $key => $row) {
    $sql = "select b.name from pet_". PREFIX ."_spa_row a inner join pet_". PREFIX ."_config b on a.spaid = $row[id] and a.typeid = b.id";
    $service = $db->arr($sql, 'name');
  
    $sql = "select fullname as name from pet_". PREFIX ."_users where userid = $row[duser]";
    $d = $db->fetch($sql);
  
    $image = parseimage($row['image']);
    $dimage = parseimage($row['dimage']);
    $list []= array(
      'id' => $row['id'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'rate' => intval($row['rate']),
      'duser' => (empty($d['name']) ? '' : $d['name']),
      'note' => $row['note'],
      'status' => $row['status'],
      'image' => $image,
      'dimage' => $dimage,
      'hour' => date('H:i', $row['time']),
      'date' => date('d/m/Y', $row['time']),
      'service' => (count($service) ? implode(',', $service) : '-')
    );
  }
  return $list;
}

function search() {
  global $data, $db, $result;

  $sql = "select a.*, b.name, b.phone, c.fullname as user from pet_". PREFIX ."_spa a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.doctorid = c.userid where (b.name like '%$data->keyword%' or b.phone like '%$data->keyword%') order by time desc limit 30";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function rate() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_spa set rate = $data->rate where id = $data->id";
  $db->query($sql);

  $sql = "select a.*, b.name, b.phone, c.fullname as user from pet_". PREFIX ."_spa a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.doctorid = c.userid where (b.name like '%$data->keyword%' or b.phone like '%$data->keyword%') order by time desc limit 30";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function statrate() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_spa set rate = $data->rate where id = $data->id";
  $db->query($sql);

  $data->start = totime($data->start);
  $data->end = totime($data->end) + 60 * 60 * 24 - 1;
  
  $sql = "select a.*, b.name, b.phone, c.fullname as user from pet_". PREFIX ."_spa a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.doctorid = c.userid where (b.name like '%$data->keyword%' or b.phone like '%$data->keyword%') and (a.time between $data->start and $data->end) order by time desc";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function statistic() {
  global $data, $db, $result;

  $data->start = totime($data->start);
  $data->end = totime($data->end) + 60 * 60 * 24 - 1;
  $sql = "select a.*, b.name, b.phone, c.fullname as user from pet_". PREFIX ."_spa a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.doctorid = c.userid where (b.name like '%$data->keyword%' or b.phone like '%$data->keyword%') and (a.time between $data->start and $data->end) order by time desc";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function checkcustomer($phone, $name) {
  global $db;

  $sql = "select * from pet_". PREFIX ."_customer where phone = '$phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_". PREFIX ."_customer set name = '$name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values ('$name', '$phone', '')";
    $customer['id'] = $db->insertid($sql);
  }
  return $customer['id'];
}

function nearchange() {
  global $data, $db, $result;

  $status = intval(!$data->status);
  $sql = "update pet_". PREFIX ."_spa_schedule set status = $status where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = nearlist();
  $result['count'] = nearcount();
  return $result;
}

function near() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = nearlist();
  return $result;
}

function nearcount() {
  global $data, $db, $result;

  // Nhắc hôm nay ngày mai
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 * 2 - 1;

  $sql = "select id from pet_". PREFIX ."_spa_schedule where ((time between $start and $end) and status = 0) or status = 0";
  return $db->count($sql);
}

function nearlist() {
  global $data, $db, $result;

  // Nhắc hôm nay ngày mai
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 * 2 - 1;

  $sql = "select a.*, b.name, b.phone from pet_". PREFIX ."_spa_schedule a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (time between $start and $end) or status = 0 order by time asc, ctime asc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['image'] = parseimage($row['image']);
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['ctime'] = date('d/m/Y', $row['ctime']);
  }

  return $list;
}

function schedule() {
  global $data, $db, $result;

  $customerid = checkcustomer($data->phone, $data->name);
  $image = implode(',', $data->image);
  $time = isodatetotime($data->time);
  $ctime = time();
  
  $sql = "insert into pet_". PREFIX ."_spa_schedule (customerid, note, image, time) values($customerid, '$data->note', '$image', $time)";
  $db->query($sql);

  $result['list'] = nearlist();
  $result['count'] = nearcount();
  $result['status'] = 1;
  
  return $result;
}

function insert() {
  global $data, $db, $result;

  $customerid = checkcustomer($data->phone, $data->name);
  $customer2id = checkcustomer($data->phone2, $data->name2);
  
  $userid = checkuserid();
  $data->treat = intval($data->treat);
  if ($data->did) $duser = $userid;
  else $duser = 0;
  
  $sql = "insert into pet_". PREFIX ."_spa (customerid, customerid2, doctorid, note, time, utime, weight, image, treat, duser, number) values($customerid, $customer2id, $userid, '$data->note', '" . time() . "', '" . time() . "', $data->weight, '". implode(',', $data->image)."', 0, $duser, $data->number)";
  $id = $db->insertid($sql);

  if (!$data->treat) {
    foreach ($data->option as $value) {
      $sql = "insert into pet_". PREFIX ."_spa_row (spaid, typeid) values($id, $value)";
      $db->query($sql);
    }
  }
  
  $result['time'] = time();
  $result['list'] = getList();
  $result['status'] = 1;
  return $result;
}

function kiemtrachitiet($dulieu) {
  global $db;

  $chitiet = [];
  $sql = "select * from pet_". PREFIX ."_spaloai";
  $dulieuloaispa = $db->obj($sql, 'id', 'name');

  $sql = "select * from pet_". PREFIX ."_spanhanvien";
  $dulieunhanvien = $db->obj($sql, 'id', 'ten');
  // liệt kê những ngày khách đến, làm dịch vụ gì, ai làm
  foreach ($dulieu as $thongtin) {
    $ngay = date('d/m/Y', $thongtin['thoigian']);
    if (empty($chitiet[$ngay])) $chitiet[$ngay] = [];
    if (empty($chitiet[$ngay][$thongtin['idnhanvien']])) $chitiet[$ngay][$thongtin['idnhanvien']] = [];
    $chitiet[$ngay][$thongtin['idnhanvien']] []= $dulieuloaispa[$thongtin['idloai']];
  }

  $danhsach = [];
  foreach ($chitiet as $ngay => $thongtin) {
    foreach ($thongtin as $idnhanvien => $dichvu) {
      $danhsach []= ['nhanvien' => $dulieunhanvien[$idnhanvien], 'dichvu' => implode(', ', $dichvu), 'ngay' => $ngay];
    }
  }

  return $danhsach;
}

function chitietdulieu() {
  global $db, $data, $result;

  $dauthangnay = strtotime(date('Y/m/1', isodatetotime($data->dauthang)));
  $cuoithangnay = strtotime(date('Y/m/t', isodatetotime($data->cuoithang))) + 60 * 60 * 24 - 1;
  $cuoithangtruoc = $dauthangnay - 1;
  $thangtruoc = 2 * date('m', $dauthangnay) - date('m', $cuoithangnay) - 1;
  if ($thangtruoc < 0) {
    $thangtruoc += 12;
  }
  $dauthangtruoc = strtotime(date('Y', $dauthangnay) . '/'. $thangtruoc . '/1');

  $chitiet = ['thangnay' => [], 'thangtruoc' => []];

  // danh sách khách tháng này
  $sql = "select * from pet_". PREFIX ."_spadichvu where (thoigian between $dauthangnay and $cuoithangnay) and idkhach = $data->idkhach order by thoigian desc";
  $chitiet['thangnay'] = kiemtrachitiet($db->all($sql));

  // danh sách khách tháng trước
  $sql = "select * from pet_". PREFIX ."_spadichvu where (thoigian between $dauthangtruoc and $cuoithangtruoc) and idkhach = $data->idkhach order by thoigian desc";
  $chitiet['thangtruoc'] = kiemtrachitiet($db->all($sql));

  $result['chitiet'] = $chitiet;
  $result['status'] = 1;
  return $result;
}

function chitietthongke($dulieu) {
  global $db;

  $chitiet = [];
	// tổng số lần họ tới
	// trung bình số lần tới
	// tổng tiền dịch vụ
  /**
    idkhach: {
      tenkhach
      dienthoai
      tonglan
      trungbinhlan
      tongtien
    } 
  */
  foreach ($dulieu as $thongtin) {
    if (empty($chitiet[$thongtin['idkhach']])) {
      $sql = "select id, name as tenkhach, phone as dienthoai from pet_". PREFIX ."_customer where id = $thongtin[idkhach]";
      $khach = $db->fetch($sql);

      $chitiet[$thongtin['idkhach']] = [
        'idkhach' => $khach['id'],
        'tenkhach' => $khach['tenkhach'],
        'dienthoai' => $khach['dienthoai'],
        'tonglan' => 0,
        'tongtien' => 0,
        'ngay' => []
      ];
    }
    $ngay = date('dmY', $thongtin['thoigian']);
    if (empty($chitiet[$thongtin['idkhach']]['ngay'][$ngay])) $chitiet[$thongtin['idkhach']]['ngay'][$ngay] = 1;
    $chitiet[$thongtin['idkhach']]['tongtien'] += $thongtin['tongtien'];
  }

  foreach ($chitiet as $idkhach => $thongtin) {
    $chitiet[$idkhach]['tonglan'] = count($thongtin['ngay']);
    unset($chitiet[$idkhach]['ngay']);
  }

  return $chitiet;
}

function sosanhtonglan($a, $b) {
  return $a['tonglan'] < $b['tonglan'];
}

function themchuongtrinh() {
  global $data, $db, $result, $_FILES;

  $thoigian = time();
  $sql = "insert pet_". PREFIX ."_nhomtin (tennhom, mautin, thoigian) values('$data->tennhom', '$data->mautin', $thoigian)";
  $idnhomtin = $db->insertid($sql);
  
  foreach ($data->danhsach as $idkhachhang) {
    $sql = "select * from pet_". PREFIX ."_nhomtinlienket where idnhomtin = $idnhomtin and idkhachhang = $idkhachhang";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_nhomtinlienket (idnhomtin, idkhachhang) values($idnhomtin, $idkhachhang)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  return $result;
}

function thongke() {
  global $data, $db, $result;

  // lấy từ trong danh sách dịch vụ
  // thống kê số lượng dịch vụ theo khách hàng
  // khách hàng: tổng dịch vụ, trung bình dịch vụ mỗi ngày, số ngày, tổng tiền
  $dauthangnay = strtotime(date('Y/m/1', isodatetotime($data->dauthang)));
  $cuoithangnay = strtotime(date('Y/m/t', isodatetotime($data->cuoithang))) + 60 * 60 * 24 - 1;
  $cuoithangtruoc = $dauthangnay - 1;
  $thangtruoc = 2 * date('m', $dauthangnay) - date('m', $cuoithangnay) - 1;
  if ($thangtruoc < 0) {
    $thangtruoc += 12;
  }
  $dauthangtruoc = strtotime(date('Y', $dauthangnay) . '/'. $thangtruoc . '/1');

  // danh sách khách tháng này
  $sql = "select * from pet_". PREFIX ."_spadichvu where thoigian between $dauthangnay and $cuoithangnay";
  $thangnay = chitietthongke($db->all($sql));

  // danh sách khách tháng trước
  $sql = "select * from pet_". PREFIX ."_spadichvu where thoigian between $dauthangtruoc and $cuoithangtruoc";
  $thangtruoc = chitietthongke($db->all($sql));

  $dulieu = [];
  // so sánh tháng này và tháng trước
  // for tháng trước, so với tháng này nếu có idkhach, thực hiện so sánh
  // nếu không, báo tháng này không đến
  foreach ($thangtruoc as $idkhach => $thongtin) {
    if (!empty($thangnay[$idkhach])) {
      $tam = $thangnay[$idkhach];
      $tam['tongtien'] = number_format($tam['tongtien']);
      $tam['thangnay'] = $tam['tonglan'];
      $tam['thangtruoc'] = $thongtin['tonglan'];
      $dulieu []= $tam;
      unset($thangnay[$idkhach]);
    }
    else {
      $tam = $thangtruoc[$idkhach];
      $tam['tongtien'] = number_format($tam['tongtien']);
      $tam['thangnay'] = 0;
      $tam['thangtruoc'] = $thongtin['tonglan'];
      $dulieu []= $tam;
    }
  }
  foreach ($thangnay as $idkhach => $thongtin) {
    $tam = $thangnay[$idkhach];
    $tam['tongtien'] = number_format($tam['tongtien']);
    $tam['thangnay'] = $thongtin['tonglan'];
    $tam['thangtruoc'] = 0;
    $dulieu []= $tam;
  }

  usort($dulieu, "sosanhtonglan");

  // $sql = "select * from pet_". PREFIX ."_spadichvu where (thoigian between $batdau and $ketthuc)";
  // $danhsach = $db->all($sql);
  // $dulieu = [];
  // $thongke = [];
  // $tongngay = floor(($ketthuc - $batdau) / 60 / 60 / 24);

  // foreach ($danhsach as $dichvu) {
  //   if (empty($dulieu[$dichvu['idkhach']])) {
  //     $sql = "select * from pet_". PREFIX ."_customer where id = $dichvu[idkhach]";
  //     if (empty($khach = $db->fetch($sql))) $khach = ['name' => '', 'phone' => ''];
  //     $dulieu[$dichvu['idkhach']] = [
  //       'khachhang' => $khach['name'],
  //       'dienthoai' => $khach['phone'],
  //       'tongden' => 0
  //       'thangtruoc' => 0
  //       'tongtien' => 0
  //       'trungbinhdichvu' => 0
  //       'trungbinhtongtien' => 0
  //       'songay' => 0,
  //       'danhsach' => [],
  //     ];
  //   }
  //   $ngaythang = date('d/m/Y', $dichvu['thoigian']);
  //   if (empty($dulieu[$dichvu['idkhach']]['danhsach'][$ngaythang])) $dulieu[$dichvu['idkhach']]['danhsach'][$ngaythang] = [
  //     'tongdichvu' => 0,
  //     'tongtien' => 0
  //   ];
  //   $dulieu[$dichvu['idkhach']]['danhsach'][$ngaythang]['tongdichvu'] += $dichvu['soluong'];
  //   $dulieu[$dichvu['idkhach']]['danhsach'][$ngaythang]['tongtien'] += $dichvu['tongtien'];
  //   $dulieu[$dichvu['idkhach']]['tongdichvu'] += $dichvu['soluong'];
  //   $dulieu[$dichvu['idkhach']]['tongtien'] += $dichvu['tongtien'];
  // }

  // $thongke = [];
  // foreach ($dulieu as $idkhach => $khachhang) {
  //   $songay = count($khachhang['danhsach']);
  //   $dulieu[$idkhach]['songay'] = $songay;
  //   $dulieu[$idkhach]['trungbinhlan'] = $khachhang['tongdichvu'] / $tongngay;
  //   $dulieu[$idkhach]['trungbinhtien'] = number_format($khachhang['tongtien'] / $songay);
  //   $dulieu[$idkhach]['tongtien'] = number_format($khachhang['tongtien']);
  //   $thongke []= $dulieu[$idkhach];
  // }

  $result['thongke'] = $dulieu;
  $result['status'] = 1;
  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_". PREFIX ."_customer set name = '$data->name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values ('$data->name', '$data->phone', '')";
    $customer['id'] = $db->insertid($sql);
  }
  
  $customer2 = array('id' => 0);
  if (!empty($data->phone2) && !empty($data->name2)) {
    $sql = "select * from pet_". PREFIX ."_customer where phone = '$data->phone2'";
    if (!empty($customer2 = $db->fetch($sql))) {
      $sql = "update pet_". PREFIX ."_customer set name = '$data->name2' where id = $customer2[id]";
      $db->query($sql);
    }
    else {
      $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values ('$data->name2', '$data->phone2', '')";
      $customer2['id'] = $db->insertid($sql);
    }
  }
  
  $userid = checkuserid();
  $data->treat = intval($data->treat);

  $sql = "update pet_". PREFIX ."_spa set customerid = $customer[id], customerid2 = $customer2[id], doctorid = $userid, note = '$data->note', image = '". implode(',', $data->image)."', weight = $data->weight, utime = ". time() .", luser = $userid, ltime = ". time() .", number = $data->number where id = $data->id";
  $db->query($sql);  
  
  $sql = "delete from pet_". PREFIX ."_spa_row where spaid = $data->id";
  $db->query($sql);
  
  foreach ($data->option as $value) {
    $sql = "insert into pet_". PREFIX ."_spa_row (spaid, typeid) values($data->id, $value)";
    $db->query($sql);
  }
  
  $result['time'] = time();
  $result['list'] = getList();
  $result['status'] = 1;
  return $result;
}

function work() {
  global $data, $db, $result;

  $start = strtotime(date('Y/m/d'));
  $end = strtotime(date('Y/m/d')) + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_spa where time between $start and $end";
  $list = $db->all($sql);
  $data = getuserobj();
  foreach ($list as $row) {
    // kiêm tra xong chưa, nếu rồi đẩy vào data
    // nễu chưa đẩy vào 0
    $row['customer'] = getcustomer($row['customerid']);
    $sql = "select b.name from pet_". PREFIX ."_spa_row a inner join pet_". PREFIX ."_config b on a.spaid = $row[id] and a.typeid = b.id";
    $row['option'] = implode(',', $db->arr($sql, 'name'));
    $row['image'] = parseimage($row['image']);

    if (!empty($row['duser'])) {
      $data[$row['duser']]['list'] []= $row;
      $data[$row['duser']]['total'] += $row['number'];
    }
    else {
      $data[0]['list'] []= $row;
      $data[0]['total'] += $row['number'];
    }
  }

  $content = array();
  foreach ($data as $id => $row) {
    $content[] = $row;
  }

  $result['list'] = $content;
  $result['status'] = 1;
  return $result;
}

function getuserobj() {
  global $db;

  $sql = "select a.userid, a.fullname as name from pet_". PREFIX ."_users a inner join pet_". PREFIX ."_user_per b on a.userid = b.userid and b.module = 'doctor'";
  $list = $db->all($sql);

  $data = array();
  $data[0] = array(
    'userid' => 0,
    'name' => 'Chưa làm',
    'total' => 0,
    'list' => array()
  );
  foreach ($list as $key => $value) {
    $data[$value['userid']] = array(
      'userid' => $value['userid'],
      'name' => $value['name'],
      'total' => 0,
      'list' => array()
    );
  }
  return $data;
}

function getList() {
  global $data, $db;

  $filter = $data->filter;
  $start = isodatetotime($filter->start);
  $end = isodatetotime($filter->end) + 60 * 60 * 24 - 1;
  $sql = "select a.*, b.name, b.phone, c.fullname as user from pet_". PREFIX ."_spa a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.doctorid = c.userid where (time between $start and $end) and status < 3 order by utime desc";
  $spa = $db->all($sql);
  
  $sql = "select a.*, b.name, b.phone, c.fullname as user from pet_". PREFIX ."_spa a inner join pet_". PREFIX ."_customer b on a.customerid = b.id inner join pet_". PREFIX ."_users c on a.doctorid = c.userid where (time between $start and $end) and status = 3 order by utime desc";
  $spa = array_merge($spa, $db->all($sql));

  $sql = "select * from pet_". PREFIX ."_config where module = 'spa'";
  $option_list = $db->obj($sql, 'name');

  $time = strtotime(date('Y/m/d')) + 60 * 60 * 24 - 1;

  $list = array();
  foreach ($spa as $row) {
    $sql = "select b.name from pet_". PREFIX ."_spa_row a inner join pet_". PREFIX ."_config b on a.spaid = $row[id] and a.typeid = b.id";
    $service = $db->arr($sql, 'name');

    $sql = "select b.id from pet_". PREFIX ."_spa_row a inner join pet_". PREFIX ."_config b on a.spaid = $row[id] and a.typeid = b.id";
    $option = $db->arr($sql, 'id');

    $sql = "select name, phone from pet_". PREFIX ."_customer where id = $row[customerid2]";
    $c = $db->fetch($sql);

    $sql = "select fullname as name from pet_". PREFIX ."_users where userid = $row[luser]";
    $u = $db->fetch($sql);

    $sql = "select fullname as name from pet_". PREFIX ."_users where userid = $row[duser]";
    $d = $db->fetch($sql);

    $sql = "select * from pet_". PREFIX ."_spa_schedule where customerid = $row[customerid] and time > $time";
    $near = $db->count($sql);

    $image = parseimage($row['image']);
    $dimage = parseimage($row['dimage']);
    $list []= array(
      'id' => $row['id'],
      'near' => $near,
      'number' => $row['number'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'name2' => (empty($c['name']) ? '' : $c['name']),
      'phone2' => (empty($c['phone']) ? '' : $c['phone']),
      'user' => $row['user'],
      'note' => $row['note'],
      'ltime' => (empty($u['name']) ? '' : date('d/m H:i', $row['ltime'])),
      'luser' => (empty($u['name']) ? '' : $u['name']),
      'duser' => (empty($d['name']) ? '' : $d['name']),
      'duserid' => $row['duser'],
      'status' => $row['status'],
      'treat' => $row['treat'],
      'weight' => $row['weight'],
      'image' => $image,
      'dimage' => $dimage,
      'ftime' => date('d/m/Y', $row['time']),
      'time' => date('H:i', $row['time']),
      'option' => $option,
      'service' => (count($service) ? implode(',', $service) : '-')
    );
  }

  return $list;
}

function gettypelist() {
  global $db;

  $sql = "select id, name, value, alt from pet_". PREFIX ."_config where module = 'spa' order by value asc";
  $spa = $db->all($sql);

  foreach ($spa as $key => $s) {
    $spa[$key]['check'] = 0;
  }

  return $spa;
}
