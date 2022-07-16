<?php
function nhanvien() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function danhsachnhanvien() {
  global $db;
  
  $sql = "select * from pet_phc_luong_nhanvien order by ngaylen";
  $list = $db->all($sql);
  $homnay = time();

  foreach ($list as $key => $row) {
    $sql = "select * from pet_phc_users where userid = $row[userid]";
    $user = $db->fetch($sql);
    $sql = "select sum(tietkiem) as tietkiem from pet_phc_luong where userid = $row[userid] and nhantietkiem = 0";
    $luong = $db->fetch($sql);

    $dauthang = strtotime(date('Y/m/1', $row['ngaylen']));

    $list[$key]['ten'] = $user['fullname'];
    $list[$key]['luongcoban'] = number_format($row['luongcoban']);
    $list[$key]['tietkiem'] = number_format($luong['tietkiem']);
    $list[$key]['ngaylen'] = date('d/m/Y', $row['ngaylen']);
    $list[$key]['ngayky'] = date('d/m/Y', $row['ngayky']);
    $list[$key]['hopdong'] = date('d/m/Y', $row['hopdong']);
    $list[$key]['camket'] = date('d/m/Y', $row['camket']);
    $list[$key]['lenluong'] = 0;
    if ($homnay > $dauthang) $list[$key]['lenluong'] = 1;
  }
  
  return $list;
}

function timthemnhanvien() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachtimthemnhanvien();
  return $result;
}

function danhsachtimthemnhanvien() {
  global $db;
  
  $sql = "select * from pet_phc_luong_nhanvien";
  $danhsachidnhanvien = $db->arr($sql, 'userid');
  if (count($danhsachidnhanvien)) $query = "where userid not in (". implode(',', $danhsachidnhanvien) .")";
  else $query = "";
  
  $sql = "select * from pet_phc_users ". $query;
  $users = $db->all($sql);
  
  $list = array();
  foreach ($users as $key => $row) {
    $list []= array(
      'id' => $row['userid'],
      'ten' => $row['fullname'],
      'taikhoan' => $row['username'],
    );
  }
  
  return $list;
}

function themnhanvien() {
  global $data, $db, $result;

  $data->luongcoban = str_replace(',', '', $data->luongcoban);
  if (empty($data->cophan)) $data->cophan = 0;
  $data->ngayky = isodatetotime($data->ngayky);
  $data->hopdong = isodatetotime($data->hopdong);
  $data->camket = isodatetotime($data->camket);
  $ngayky = date('Y-m-d', $data->ngayky);
  $ngaylen = strtotime("+12 months $ngayky");

  $sql = "select * from pet_phc_luong_nhanvien where userid = $data->userid";
  if (empty($nhanvien = $db->fetch($sql))) {
    $sql = "insert into pet_phc_luong_nhanvien (userid, luongcoban, ngayky, ngaylen, hopdong, camket, cophan) values($data->userid, $data->luongcoban, $data->ngayky, $ngaylen, $data->hopdong, $data->camket, $data->cophan)";
    $result['messenger'] = 'Đã thêm nhân viên';
  }
  else {
    $sql = "update pet_phc_luong_nhanvien set luongcoban = $data->luongcoban, ngayky = $data->ngayky, ngaylen = $ngaylen, hopdong = $data->hopdong, camket = $data->camket, cophan = $data->cophan where userid = $data->userid";
    $result['messenger'] = 'Đã cập nhật nhân viên';
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function xoanhanvien() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_luong_nhanvien where userid = $data->userid";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa nhân viên khỏi danh sách';
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function lenluong() {
  global $data, $db, $result;

  $ngaylen = isodatetotime($data->ngaylen);
  $sql = "update pet_phc_luong_nhanvien set ngaylen = $ngaylen where userid = $data->user";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã xác nhận lên lương';
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}
