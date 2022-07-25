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
  $dencamket = time() + 30 * 24 * 60 * 60;

  $sql = "select * from pet_phc_luong_nhanvien where lenluong < $dauthanglenluong";
  $tong += $db->count($sql);

  $sql = "select * from pet_phc_luong_nhanvien where camket < $dencamket";
  $tong += $db->count($sql);

  return $tong;
}

function danhsachnhanvien() {
  global $db;
  
  $sql = "select * from pet_phc_luong_nhanvien order by id asc";
  $list = $db->all($sql);
  $homnay = time();

  foreach ($list as $key => $row) {
    $sql = "select * from pet_phc_users where userid = $row[userid]";
    $user = $db->fetch($sql);
    $sql = "select sum(tietkiem) as tietkiem from pet_phc_luong_tra where userid = $row[userid] and nhantietkiem = 0";
    $luong = $db->fetch($sql);

    $dauthanglenluong = strtotime(date('Y/m/1', $row['lenluong']));
    $denhopdong = $row['hopdong'] - 90 * 24 * 60 * 60;
    $dencamket = $row['camket'] - 90 * 24 * 60 * 60;
    $sonam = floor(($homnay - $row['hopdong']) / 365 / 60 / 60 / 24);
    $luonghientai = number_format($sonam * 500000 + $row['luongcoban']);

    $list[$key]['sonam'] = $sonam;
    $list[$key]['luonghientai'] = $luonghientai;
    $list[$key]['ten'] = $user['fullname'];
    $list[$key]['luongcoban'] = number_format($row['luongcoban']);
    $list[$key]['tietkiem'] = number_format($luong['tietkiem']);
    $list[$key]['lenluong'] = date('d/m/Y', $row['lenluong']);
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
      'gianluoc' => lower($row['fullname']),
      'taikhoan' => $row['username'],
    );
  }
  
  return $list;
}

function themnhanvien2() {
  global $data, $db, $result;

  $username = mb_strtolower($data->username);
  $password = $data->password;

  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  $sql = 'select userid, password from `pet_phc_users` where LOWER(username) = "'. $username .'"';
  if (!empty($user = $db->fetch($sql))) $result['messenger'] = 'Tên người dùng đã tồn tại';
  else {
    $time = time();
    $sql = "insert into pet_phc_users (username, name, fullname, password, photo, regdate, active) values ('$data->username', '', '$data->fullname', '". $crypt->hash_password($data->password) ."', '', $time, 1)";
    $userid = $db->insertid($sql);
    
    $data->luongcoban = str_replace(',', '', $data->luongcoban);
    if (empty($data->cophan)) $data->cophan = 0;
    $data->hopdong = isodatetotime($data->hopdong);
    $data->camket = isodatetotime($data->camket);
    $lenluong = strtotime("+12 months ". date('Y-m-d', $data->hopdong));
  
    $sql = "insert into pet_phc_luong_nhanvien (userid, tile, luongcoban, lenluong, hopdong, camket, cophan, phucap) values($userid, $data->tile, $data->luongcoban, $lenluong, $data->hopdong, $data->camket, $data->cophan, $data->phucap)";
    $result['messenger'] = 'Đã thêm nhân viên';
    $db->query($sql);
  
    $result['status'] = 1;
    $result['danhsach'] = danhsachnhanvien();
  }

  return $result;
}

function themnhanvien() {
  global $data, $db, $result;

  $data->luongcoban = str_replace(',', '', $data->luongcoban);
  if (empty($data->cophan)) $data->cophan = 0;
  $data->hopdong = isodatetotime($data->hopdong);
  $data->camket = isodatetotime($data->camket);
  $lenluong = strtotime("+12 months ". date('Y-m-d', $data->hopdong));

  $sql = "select * from pet_phc_luong_nhanvien where userid = $data->id";
  if (empty($nhanvien = $db->fetch($sql))) {
    $sql = "insert into pet_phc_luong_nhanvien (userid, tile, luongcoban, lenluong, hopdong, camket, cophan, phucap) values($data->id, $data->tile, $data->luongcoban, $lenluong, $data->hopdong, $data->camket, $data->cophan, $data->phucap)";
    $result['messenger'] = 'Đã thêm nhân viên';
  }
  else {
    $sql = "update pet_phc_luong_nhanvien set luongcoban = $data->luongcoban, lenluong = $lenluong, hopdong = $data->hopdong, camket = $data->camket, cophan = $data->cophan, tile = $data->tile, phucap = $data->phucap where userid = $data->id";
    $result['messenger'] = 'Đã cập nhật nhân viên';
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function xoanhanvien() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_luong_nhanvien where userid = $data->id";
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

  $excel = array('nhanvien' => 'A1', 'doanhthu' => 'B1', 'loinhuan' => 'C1');
  $sql = "select * from pet_phc_config where module = 'luongthang'";
  $cauhinh = $db->obj($sql, 'name', 'value');
  if (isset($cauhinh['nhanvien'])) $excel['nhanvien'] = $cauhinh['nhanvien'];
  if (isset($cauhinh['doanhthu'])) $excel['doanhthu'] = $cauhinh['doanhthu'];
  if (isset($cauhinh['loinhuan'])) $excel['loinhuan'] = $cauhinh['loinhuan'];

  $result['status'] = 1;
  $result['danhsachnhanvien'] = danhsachnhanvien();
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
  $chuloinhuan = laychu(strtoupper($data->excel['loinhuan']));
  $tongdoanhthu  = 0;
  $tongloinhuan = 0;
  $tongthuchi = 0;
  for ($j = $so; $j <= $highestRow; $j ++) {
    $nhanvien = $sheet->getCell($chunhanvien . $j)->getValue();
    $doanhthu = layso($sheet->getCell($chudoanhthu . $j)->getValue());
    $loinhuan = layso($sheet->getCell($chuloinhuan . $j)->getValue());
    if (!empty($nhanvien)) {
      $doanhthunhanvien[alias($nhanvien)] = array('doanhthu' => $doanhthu, 'loinhuan' => $loinhuan);
    }
  }

  foreach ($doanhthunhanvien as $dulieu) {
    $tongdoanhthu += $dulieu['doanhthu'];
    $tongloinhuan += $dulieu['loinhuan'];
  }

  // lấy danh sách nhân viên
  $sql = "select a.userid, a.fullname as tennhanvien, b.luongcoban, b.hopdong, b.tile, b.cophan, b.phucap from pet_phc_users a inner join pet_phc_luong_nhanvien b on a.userid = b.userid";
  $danhsachnhanvien = $db->all($sql);
  $nhanvien = array();
  $homnay = time();
  // chạy dữ liệu chi
  foreach ($data->thuchi as $tienchi) {
    $tongthuchi += str_replace(',', '', $tienchi);
  }

  $ngaynghi = array();
  foreach ($data->ngaynghi as $tennhanvien => $songay) {
    $ngaynghi[alias($tennhanvien)] = $songay;
  }

  // chạy dữ liệu doanh thu, thêm vào 
  $dulieuluong = array();
  $tongluong = 0;
  foreach ($danhsachnhanvien as $nhanvien) {
    $luongcung = $nhanvien['luongcoban'] + 500000 * floor(($homnay - $nhanvien['hopdong']) / 365 / 60 / 60 / 24);
    $tennhanvien = alias($nhanvien['tennhanvien']);
    
    if (isset($doanhthunhanvien[$tennhanvien])) {
      $doanhthu = $doanhthunhanvien[$tennhanvien]['doanhthu'];
      $loinhuan = $doanhthunhanvien[$tennhanvien]['loinhuan'];
    }
    else {
      $loinhuan = 0;
      $doanhthu = 0;
    }

    if (isset($ngaynghi[$tennhanvien])) $nghiphep = $ngaynghi[$tennhanvien] * $luongcung / 30;
    else $nghiphep = 0;
    $tilethuong = $loinhuan * $nhanvien['tile'] / 100;
    $luongthuong = $tilethuong - $luongcung;
    if ($luongthuong > $luongcung * 0.3) $tietkiem = $luongthuong / 2;
    else $tietkiem = $luongcung * 0.15;
    $tongluong += $luongcung + ($luongthuong > 0 ? $luongthuong : 0) - $nghiphep;
    $dulieuluong []= array(
      'userid' => $nhanvien['userid'],
      'tennhanvien' => $nhanvien['tennhanvien'],
      'luongcung' => number_format($luongcung),
      'luongthuong' => number_format($luongthuong),
      'tilethuong' => number_format($tilethuong),
      'doanhthu' => number_format($doanhthu),
      'loinhuan' => number_format($loinhuan),
      'cophan' => $nhanvien['cophan'],
      'tile' => $nhanvien['tile'],
      'tietkiem' => number_format($tietkiem),
      'nghiphep' => number_format($nghiphep),
      'luongcophan' => 0,
      'tongluong' => 0,
      'thucnhan' => 0,
      'phucap' => $nhanvien['phucap'],
      'luongphucap' => 0,
    );
  }

  // tính lãi sau lương và tiền chi
  $laisauthuchi = $tongloinhuan - $tongthuchi - $tongluong;
  foreach ($dulieuluong as $thutu => $nhanvien) {
    $luongcung = str_replace(',', '', $nhanvien['luongcung']);
    $luongthuong = str_replace(',', '', $nhanvien['luongthuong']);
    $luongthuong = ($luongthuong > 0 ? $luongthuong : 0);
    $nghiphep = str_replace(',', '', $nhanvien['nghiphep']);
    $tietkiem = str_replace(',', '', $nhanvien['tietkiem']);
    $luongcophan = $laisauthuchi * $nhanvien['cophan'] / 100;
    // $luongcophan = ($luongcophan > 0 ? $luongcophan : 0);
    $luongphucap = $laisauthuchi * $nhanvien['phucap'] / 100;
    // $luongphucap = ($luongphucap > 0 ? $luongphucap : 0);
    $dulieuluong[$thutu]['luongphucap'] = number_format($luongphucap);
    $dulieuluong[$thutu]['luongcophan'] = number_format($luongcophan);
    $dulieuluong[$thutu]['tongluong'] = number_format($luongcung + $luongthuong + $phucap + $luongcophan + $luongphucap - $nghiphep);
    $dulieuluong[$thutu]['thucnhan'] = number_format($luongcung + $luongthuong + $phucap + $luongcophan + $luongphucap - $nghiphep - $tietkiem);
  }

  // cập nhật thông tin lên csdl
  
  // kiểm tra data có id chưa
    // nếu chưa, thêm vào phc_luong, thêm thông tin thu chi
    // nếu rồi cập nhật, cập nhật thông tin thu chi
  // kiểm tra nhân viên có trong phc_tra chưa
    // nếu chưa thì thêm
    // nếu rồi thì cập nhật

  // đóng gói dữ liệu trả về
  $result['dulieu'] = array(
    'nhanvien' => $dulieuluong,
    'tongchi' => number_format($tongthuchi),
    'tongdoanhthu' => number_format($tongdoanhthu),
    'tongloinhuan' => number_format($tongloinhuan),
    'tongnhanvien' => number_format($tongluong),
    'tongcodong' => number_format($laisauthuchi),
  );
  $result['tinnhan'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}
