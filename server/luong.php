<?php
$dir = str_replace('/server', '', ROOTDIR);
include $dir .'/include/PHPExcel/IOFactory.php';

$x = array();
$xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
foreach ($xr as $key => $value) {
  $x[$value] = $key;
}

function luong() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['chuy'] = chuy();
  $result['danhsach'] = danhsachluong();
  return $result;
}

function nhanvien() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function danhsachluong() {
  global $db;

  $sql = "select * from pet_phc_luong order by id desc limit 20";
  $luongthang = $db->all($sql);
  
  foreach ($luongthang as $key => $row) {
    $sql = "select * from pet_phc_luong_tra where luongid = $row[id]";
    $luong = $db->all($sql);
    $luongthang['key']['tietkiem'] = 0;
    $luongthang['key']['tongluong'] = 0;
    foreach ($luong as $chitietluong) {
      $luongthang['key']['tietkiem'] += $chitietluong['tietkiem'];
      $luongthang['key']['tongluong'] += $chitietluong['tong'];
    }

    $luongthang['key']['thang'] = date('m', $row['thoigian']);
  }
  return $luongthang;
}

function chuy() {
  global $db;

  // đếm danh sách hết hạn lên lương
  // đếm danh sách hết hạn hợp đồng
  // đếm danh sách hết hạn cam kết
  $tong = 0;
  $dauthanglenluong = strtotime(date('Y/m/1'));
  $denhopdong = time() + 30 * 24 * 60 * 60;
  $dencamket = time() + 30 * 24 * 60 * 60;

  $sql = "select * from pet_phc_luong_nhanvien where lenluong < $dauthanglenluong";
  $tong += $db->count($sql);

  $sql = "select * from pet_phc_luong_nhanvien where hopdong < $denhopdong";
  $tong += $db->count($sql);

  $sql = "select * from pet_phc_luong_nhanvien where camket < $dencamket";
  $tong += $db->count($sql);

  return $tong;
}

function danhsachnhanvien() {
  global $db;
  
  $sql = "select * from pet_phc_luong_nhanvien order by lenluong";
  $list = $db->all($sql);
  $homnay = time();

  foreach ($list as $key => $row) {
    $sql = "select * from pet_phc_users where userid = $row[userid]";
    $user = $db->fetch($sql);
    $sql = "select sum(tietkiem) as tietkiem from pet_phc_luong_tra where userid = $row[userid] and nhantietkiem = 0";
    $luong = $db->fetch($sql);

    $dauthanglenluong = strtotime(date('Y/m/1', $row['lenluong']));
    $denhopdong = $row['hopdong'] - 30 * 24 * 60 * 60;
    $dencamket = $row['camket'] - 30 * 24 * 60 * 60;

    $list[$key]['ten'] = $user['fullname'];
    $list[$key]['luongcoban'] = number_format($row['luongcoban']);
    $list[$key]['tietkiem'] = number_format($luong['tietkiem']);
    $list[$key]['lenluong'] = date('d/m/Y', $row['lenluong']);
    $list[$key]['ngayky'] = date('d/m/Y', $row['ngayky']);
    $list[$key]['hopdong'] = date('d/m/Y', $row['hopdong']);
    $list[$key]['camket'] = date('d/m/Y', $row['camket']);
    $list[$key]['denlenluong'] = 0;
    $list[$key]['denhopdong'] = 0;
    $list[$key]['dencamket'] = 0;
    if ($homnay > $dauthanglenluong) $list[$key]['denlenluong'] = 1;
    if ($homnay > $denhopdong) $list[$key]['denhopdong'] = 1;
    if ($homnay > $dencamket) $list[$key]['dencamket'] = 1;
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
  $lenluong = strtotime("+12 months $ngayky");

  $sql = "select * from pet_phc_luong_nhanvien where userid = $data->userid";
  if (empty($nhanvien = $db->fetch($sql))) {
    $sql = "insert into pet_phc_luong_nhanvien (userid, luongcoban, ngayky, lenluong, hopdong, camket, cophan) values($data->userid, $data->luongcoban, $data->ngayky, $lenluong, $data->hopdong, $data->camket, $data->cophan)";
    $result['messenger'] = 'Đã thêm nhân viên';
  }
  else {
    $sql = "update pet_phc_luong_nhanvien set luongcoban = $data->luongcoban, ngayky = $data->ngayky, lenluong = $lenluong, hopdong = $data->hopdong, camket = $data->camket, cophan = $data->cophan where userid = $data->userid";
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

  if (isset($data->lenluong)) {
    $lenluong = isodatetotime($data->lenluong);
    $sql = "update pet_phc_luong_nhanvien set lenluong = $lenluong where userid = $data->userid";
    $result['messenger'] = 'Đã xác nhận lên lương';
  }
  else if (isset($data->hopdong)) {
    $hopdong = isodatetotime($data->hopdong);
    $sql = "update pet_phc_luong_nhanvien set hopdong = $hopdong where userid = $data->userid";
    $result['messenger'] = 'Đã xác nhận hạn hợp đồng';
  }
  else if (isset($data->camket)) {
    $camket = isodatetotime($data->camket);
    $sql = "update pet_phc_luong_nhanvien set camket = $camket where userid = $data->userid";
    $result['messenger'] = 'Đã xác nhận hạn cam kết';
  }
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function khoitaoluongthang() {
  global $data, $db, $result;

  $excel = array('nhanvien' => 'A1', 'doanhthu' => 'B1');
  $sql = "select * from pet_phc_config where module = 'luongthang'";
  $cauhinh = $db->obj($sql, 'name', 'value');
  if (isset($cauhinh['nhanvien'])) $excel['nhanvien'] = $cauhinh['nhanvien'];
  if (isset($cauhinh['doanhthu'])) $excel['doanhthu'] = $cauhinh['doanhthu'];

  $result['status'] = 1;
  $result['excel'] = $excel;
  return $result;
}

function luuexcel() {
  global $data, $db, $result;

  foreach ($data->excel as $name => $value) {
    $sql = "select * from pet_phc_config where module = 'luongthang' and name = '$name'";
    if (!empty($cauhinh = $db->fetch($sql))) $sql = "update pet_phc_config set value = '$value' where id = $cauhinh[id]";
    else $sql = "insert into pet_phc_config (module, name, value, alt) values('luongthang', '$name', '$value', 0)";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  return $result;
}

function layso($chuoi) {
  return intval(preg_replace('/[^0-9]/', '', $chuoi));
}
function laychu($chuoi) {
  return preg_replace('/[^A-Z]/', '', $chuoi);
}

function excelluongthang() {
  global $data, $db, $result, $dir, $x, $xr, $_FILES;

  // lấy dữ liệu từ file
  $file = $_FILES['salary']['tmp_name'];
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $doanhthunhanvien = array();
  $so = layso($data->excel['nhanvien']);
  $chunhanvien = laychu(strtoupper($data->excel['nhanvien']));
  $chudoanhthu = laychu(strtoupper($data->excel['doanhthu']));
  $tongdoanhthu = 0;
  for ($j = $so; $j <= $highestRow; $j ++) {
    $nhanvien = $sheet->getCell($chunhanvien . $j)->getValue();
    $doanhthu = layso($sheet->getCell($chudoanhthu . $j)->getValue());
    if (!empty($nhanvien)) $doanhthunhanvien[mb_strtolower($nhanvien)] = $doanhthu;
    $tongdoanhthu += $doanhthu;
  }

  // lấy danh sách nhân viên
  $sql = "select a.userid, a.fullname from pet_phc_users a inner join pet_phc_luong_nhanvien b on a.userid = b.userid";
  $danhsachnhanvien = $db->obj($sql, 'fullname', 'userid');
  $nhanvien = array();
  // chạy dữ liệu doanh thu, thêm vào 
  $dulieudoanhthu = array();
  foreach ($danhsachnhanvien as $tennhanvien => $userid) {
    if (isset($doanhthunhanvien[mb_strtolower($tennhanvien)])) $doanhthu = $doanhthunhanvien[mb_strtolower($tennhanvien)];
    else $doanhthu = 0;
    $dulieudoanhthu []= array(
      'userid' => $userid,
      'nhanvien' => $tennhanvien,
      'doanhthu' => $doanhthu,
      'ngaynghi' => 0
    );
  }

  $tienchi = 0;
  // cập nhật csdl
  if (empty($data->id)) {
    // thêm lần đầu
    $sql = "insert into pet_phc_luong (doanhthu, tienchi, loinhuan, thoigian, trangthai) values($tongdoanhthu, 0, $tongdoanhthu, $time, 0)";
    $id = $db->insertid($sql);
  }
  else {
    $sql = "select * from pet_phc_luong where id = $data->id";
    // kiếm tra có trên csdl hay không
    if (!empty($luong = $db->fetch($sql))) {
      // nếu có, lấy ra cập nhật lại
      // lấy tổng thu chi
      $id = $luong['id'];
      $sql = "select sum(tienchi) from pet_phc_luong_tienchi where luongid = $id";
      $tienchi = $db->fetch($sql);
      $loinhuan = $tongdoanhthu - $thuchi;
      $sql = "update pet_phc_luong set doanhthu = $tongdoanhthu, tienchi = $tienchi, loinhuan = $loinhuan where id = $id";
      $db->query($sql);
    }
    else {
    // nếu không, thêm lần đầu
      $sql = "insert into pet_phc_luong (doanhthu, chi, loinhuan, thoigian, trangthai) values($tongdoanhthu, 0, $tongdoanhthu, $time, 0)";
      $id = $db->insertid($sql);
    }
  }
  // đóng gói dữ liệu trả về
  $result['id'] = $luong['id'];
  $result['dulieu'] = array(
    'nhanvien' => $dulieudoanhthu,
    'tongdoanhthu' => $tongdoanhthu,
    'tienchi' => $tienchi
  );
  $result['tinnhan'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}