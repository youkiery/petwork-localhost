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
    $luongthang[$key]['tietkiem'] = 0;
    $luongthang[$key]['tongluong'] = 0;
    $luongthang[$key]['nhanvien'] = count($luong);
    foreach ($luong as $chitietluong) {
      $luongthang[$key]['tietkiem'] += $chitietluong['tietkiem'];
      $luongthang[$key]['tongluong'] += $chitietluong['tong'];
    }

    $luongthang[$key]['thang'] = date('m', $row['thoigian']);
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
  
  $sql = "select * from pet_phc_luong_nhanvien order by userid";
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
    $list[$key]['phucap2'] = number_format($row['phucap2']);
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
    $data->phucap2 = str_replace(',', '', $data->phucap2);
    if (empty($data->cophan)) $data->cophan = 0;
    $data->hopdong = isodatetotime($data->hopdong);
    $data->camket = isodatetotime($data->camket);
    $lenluong = strtotime("+12 months ". date('Y-m-d', $data->hopdong));
  
    $sql = "insert into pet_phc_luong_nhanvien (userid, tile, luongcoban, lenluong, hopdong, camket, cophan, phucap, phucap2) values($userid, $data->tile, $data->luongcoban, $lenluong, $data->hopdong, $data->camket, $data->cophan, $data->phucap, $data->phucap2)";
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
  $data->phucap2 = str_replace(',', '', $data->phucap2);
  if (empty($data->cophan)) $data->cophan = 0;
  $data->hopdong = isodatetotime($data->hopdong);
  $data->camket = isodatetotime($data->camket);
  $lenluong = strtotime("+12 months ". date('Y-m-d', $data->hopdong));

  $sql = "select * from pet_phc_luong_nhanvien where userid = $data->id";
  if (empty($nhanvien = $db->fetch($sql))) {
    $sql = "insert into pet_phc_luong_nhanvien (userid, tile, luongcoban, lenluong, hopdong, camket, cophan, phucap, phucap2) values($data->id, $data->tile, $data->luongcoban, $lenluong, $data->hopdong, $data->camket, $data->cophan, $data->phucap, $data->phucap2)";
    $result['messenger'] = 'Đã thêm nhân viên';
  }
  else {
    $sql = "update pet_phc_luong_nhanvien set luongcoban = $data->luongcoban, lenluong = $lenluong, hopdong = $data->hopdong, camket = $data->camket, cophan = $data->cophan, tile = $data->tile, phucap = $data->phucap, phucap2 = $data->phucap2 where userid = $data->id";
    $result['messenger'] = 'Đã cập nhật nhân viên';
  }
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachnhanvien();
  return $result;
}

function xoaluong() {
  global $data, $db, $result;

  $sql = "delete from pet_phc_luong where id = $data->id";
  $db->query($sql);

  $sql = "delete from pet_phc_luong_thuchi where luongid = $data->id";
  $db->query($sql);

  $sql = "delete from pet_phc_luong_tra where luongid = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachluong();
  $result['messenger'] = 'Đã xóa chốt lương';
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

// $result['dulieu'] = array(
//   'nhanvien' => $dulieuluong,
//   'thuchi' => $data->thuchi,
//   'tongchi' => number_format($tongthuchi),
//   'tongdoanhthu' => number_format($tongdoanhthu),
//   'tongloinhuan' => number_format($tongloinhuan),
//   'tongnhanvien' => number_format($tongluong),
//   'tongcodong' => number_format($laisauthuchi),
// );

// 'userid' => $nhanvien['userid'],
// 'tennhanvien' => $nhanvien['tennhanvien'],
// 'luongcung' => number_format($luongcung),
// 'luongthuong' => number_format($luongthuong),
// 'tilethuong' => number_format($tilethuong),
// 'doanhthu' => number_format($doanhthu),
// 'loinhuan' => number_format($loinhuan),
// 'cophan' => $nhanvien['cophan'],
// 'tile' => $nhanvien['tile'],
// 'tietkiem' => number_format($tietkiem),
// 'nghiphep' => number_format($nghiphep),
// 'luongcophan' => 0,
// 'tongluong' => 0,
// 'thucnhan' => 0,
// 'phucap' => $nhanvien['phucap'],
// 'luongphucap' => 0,


function chotluongthang() {
  global $data, $db, $result;

  chotluong();
  $thoigian = isodatetotime($data->thoigian);
  $sql = "update pet_phc_luong set trangthai = 1, thoigian = $thoigian where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã chốt lương tháng';
  $result['danhsach'] = danhsachluong();
  $result['chitiet'] = chitietluongthang();
  return $result;
}

function chitiet() {
  global $db, $result;

  $result['status'] = 1;
  $result['chitiet'] = chitietluongthang();
  return $result;
}

function chitietluongthang() {
  global $db, $data;
  // lấy ngày cần xem
  $thoigian = $data->thoigian;
  $thangnay = date('m', $thoigian);
  $namnay = date('Y', $thoigian);
  $denngay = strtotime(date($namnay .'/'. ($thangnay + 1) .'/1'));
  if ($thangnay == 1) {
    // nếu tháng này là tháng 1 thì lấy từ tháng 2 năm trước đến năm nay
    $tungay = strtotime(($namnay - 1) . '/2/1');
  }
  else {
    // nếu không lấy từ tháng 2 đến tháng này
    $tungay = strtotime(date($namnay .'/2/1'));
  }

  $sql = "select * from pet_phc_luong where (thoigian between $tungay and $denngay) order by id desc";
  $danhsach = $db->all($sql);
  
  $thutu = 0;
  $chitietnhanvien = array();
  foreach ($danhsach as $luong) {
    // chỉ tính lương tháng này
    $sql = "select * from pet_phc_luong_tra where luongid = $luong[id]";
    $danhsachnhanvien = $db->all($sql);
    foreach ($danhsachnhanvien as $nhanvien) {
      if (!$thutu) {
        $sql = "select fullname from pet_phc_users where userid = $nhanvien[userid]";
        $nguoidung = $db->fetch($sql);

        
        if (empty($chitietnhanvien[$nhanvien['userid']])) {
          $chitietnhanvien[$nhanvien['userid']] = array(
            'ngaylap' => date('d/m/Y', $luong['thoigian']),
            'tennhanvien' => $nguoidung['fullname'],
            'doanhthu' => $nhanvien['doanhthu'],
            'luongcung' => $nhanvien['luong'],
            'luongthuong' => $nhanvien['thuong'],
            'luongphucap' => $nhanvien['luongphucap'],
            'nghiphep' => $nhanvien['nghiphep'],
            'tietkiem' => $nhanvien['tietkiem'],
            'tongluong' => $nhanvien['tong'],
            'thucnhan' => $nhanvien['thucnhan'],
            'tongtietkiem' => 0,
            'tongcophan' => 0,
            'dstietkiem' => array(),
            'dscophan' => array(),
          );
        }
        for ($i = 0; $i < 12; $i++) { 
          $chitietnhanvien[$nhanvien['userid']]['dstietkiem'][$i] = 0;
          $chitietnhanvien[$nhanvien['userid']]['dscophan'][$i] = 0;
        }
      }
      if (!empty($chitietnhanvien[$nhanvien['userid']])) {
        $m = date('m', $luong['thoigian']);
        if ($m == 1) $m = 11;
        else $m -= 2;
        $chitietnhanvien[$nhanvien['userid']]['dstietkiem'][$m] = $nhanvien['tietkiem'];
        $chitietnhanvien[$nhanvien['userid']]['dscophan'][$m] = $nhanvien['cophan'];
      }
    }
    $thutu ++;
  }

  foreach ($chitietnhanvien as $userid => $nhanvien) {
    foreach ($chitietnhanvien[$userid]['dstietkiem'] as $tietkiem) {
      $chitietnhanvien[$userid]['tongtietkiem'] += $tietkiem;
    }
    foreach ($chitietnhanvien[$userid]['dscophan'] as $cophan) {
      $chitietnhanvien[$userid]['tongcophan'] += $cophan;
    }
  }

  $chitiet = array();
  foreach ($chitietnhanvien as $nhanvien) {
    $chitiet []= $nhanvien;
  }

  return $chitiet;
}

function dulieuluong() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['dulieu'] = dulieuluongtheoid();
  return $result;
}

function dulieuluongtheoid() {
  global $data, $db, $result;

  $dulieu = array(
    'nhanvien' => array(),
    'thuchi' => array(),
    'ngaynghi' => array(),
    'tongdoanhthu' => 0,
    'tongloinhuan' => 0,
    'tongchi' => 0,
    'tongnhanvien' => 0,
    'tongcodong' => 0,
    'thoigian' => 0,
  );

  $sql = "select * from pet_phc_luong where id = $data->id";
  $luong = $db->fetch($sql);

  $sql = "select * from pet_phc_luong_tra where luongid = $data->id order by userid";
  $danhsachnhanvien = $db->all($sql);

  $sql = "select * from pet_phc_luong_tienchi where luongid = $data->id";
  $danhsachthuchi = $db->all($sql);

  foreach ($danhsachnhanvien as $nhanvien) {
    $sql = "select fullname as tennhanvien from pet_phc_users where userid = $nhanvien[userid]";
    $nguoidung = $db->fetch($sql);

    $dulieu['nhanvien'] []= array(
      'userid' => $nhanvien['userid'],
      'tennhanvien' => $nguoidung['tennhanvien'],
      'luongcung' => number_format($nhanvien['luong']),
      'luongthuong' => number_format($nhanvien['thuong']),
      'tilethuong' => number_format($nhanvien['tilethuong']),
      'doanhthu' => number_format($nhanvien['doanhthu']),
      'loinhuan' => number_format($nhanvien['loinhuan']),
      'cophan' => $nhanvien['tilecophan'],
      'tile' => $nhanvien['tile'],
      'tietkiem' => number_format($nhanvien['tietkiem']),
      'ngaynghi' => $nhanvien['ngaynghi'],
      'nghiphep' => number_format($nhanvien['nghiphep']),
      'luongcophan' => number_format($nhanvien['cophan']),
      'tongluong' => number_format($nhanvien['tong']),
      'thucnhan' => number_format($nhanvien['thucnhan']),
      'phucap' => $nhanvien['phucap'],
      'phucap2' => $nhanvien['phucap2'],
      'luongphucap' => number_format($nhanvien['luongphucap']),
    );

    $dulieu['ngaynghi'] []= array(
      'tennhanvien' => $nguoidung['tennhanvien'],
      'ngaynghi' => $nhanvien['ngaynghi']
    );
  }

  foreach ($danhsachthuchi as $thuchi) {
    $dulieu['thuchi'] []= array(
      'loaichi' => $thuchi['mucchi'],
      'tienchi' => number_format($thuchi['tienchi']),
    );
  }

  $dulieu['id'] = $data->id;
  $dulieu['tongdoanhthu'] = number_format($luong['doanhthu']);
  $dulieu['tongloinhuan'] = number_format($luong['loinhuan']);
  $dulieu['tongchi'] = number_format($luong['tienchi']);
  $dulieu['tongnhanvien'] = number_format($luong['luongnhanvien']);
  $dulieu['tongcodong'] = number_format($luong['lairong']);
  $dulieu['thoigian'] = $luong['thoigian'];
  return $dulieu;
}

function luungaynghi() {
  global $data, $db, $result;

  $thoigian = isodatetotime($data->thoigian);
+
  $sql = "update pet_phc_luong set thoigian = $thoigian where id = $data->id";
  $db->query($sql);

  foreach ($data->ngaynghi as $tt => $nhanvien) {
    $luongcung = str_replace(',', '', $data->nhanvien[$tt]->luongcung);
    $luongthuong = str_replace(',', '', $data->nhanvien[$tt]->luongthuong);
    if ($luongthuong < 0) $luongthuong = 0;
    $luongphucap = str_replace(',', '', $data->nhanvien[$tt]->luongphucap);
    $tietkiem = str_replace(',', '', $data->nhanvien[$tt]->tietkiem);
    $userid = str_replace(',', '', $data->nhanvien[$tt]->userid);
    $nghiphep = $nhanvien->ngaynghi * $luongcung / 30;
    $tongluong = $luongcung + $luongthuong + $luongphucap - $nghiphep;
    $thucnhan = $luongcung + $luongthuong + $luongphucap - $nghiphep - $tietkiem;

    $sql = "update pet_phc_luong_tra set ngaynghi = $nhanvien->ngaynghi, nghiphep = $nghiphep, tong = $tongluong, thucnhan = $thucnhan where luongid = $data->id and userid = $userid";
    $db->query($sql);
  }

  $result['dulieu'] = dulieuluongtheoid();
  $result['danhsach'] = danhsachluong();
  $result['messenger'] = 'Đã lưu ngày nghỉ';
  $result['status'] = 1;
  return $result;
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
  $sql = "select a.userid, a.fullname as tennhanvien, b.luongcoban, b.hopdong, b.tile, b.cophan, b.phucap, b.phucap2 from pet_phc_users a inner join pet_phc_luong_nhanvien b on a.userid = b.userid";
  $danhsachnhanvien = $db->all($sql);
  $nhanvien = array();
  $homnay = time();
  // chạy dữ liệu chi
  foreach ($data->thuchi as $thuchi) {
    $tongthuchi += intval(str_replace(',', '', $thuchi['tienchi']));
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
      'ngaynghi' => $ngaynghi[$tennhanvien],
      'nghiphep' => number_format($nghiphep),
      'luongcophan' => 0,
      'tongluong' => 0,
      'thucnhan' => 0,
      'phucap' => $nhanvien['phucap'],
      'phucap2' => $nhanvien['phucap2'],
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
    $luongphucap = $laisauthuchi * $nhanvien['phucap'] / 100 + $nhanvien['phucap2'];
    // $luongphucap = ($luongphucap > 0 ? $luongphucap : 0);
    $dulieuluong[$thutu]['luongphucap'] = number_format($luongphucap);
    $dulieuluong[$thutu]['luongcophan'] = number_format($luongcophan);
    $dulieuluong[$thutu]['tongluong'] = number_format($luongcung + $luongthuong + $luongphucap - $nghiphep);
    $dulieuluong[$thutu]['thucnhan'] = number_format($luongcung + $luongthuong + $luongphucap - $nghiphep - $tietkiem);
  }

  // cập nhật csdl
  
  // đóng gói dữ liệu trả về
  $data = json_decode(json_encode(array(
    'id' => ($data->id ? $data->id : 0),
    'nhanvien' => $dulieuluong,
    'thuchi' => $data->thuchi,
    'thoigian' => $data->thoigian,
    'tongchi' => number_format($tongthuchi),
    'tongdoanhthu' => number_format($tongdoanhthu),
    'tongloinhuan' => number_format($tongloinhuan),
    'tongnhanvien' => number_format($tongluong),
    'tongcodong' => number_format($laisauthuchi),
  )));
  $data->id = chotluong();
  $result['dulieu'] = dulieuluongtheoid();
  $result['danhsach'] = danhsachluong();
  $result['tinnhan'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}

function chotluong() {
  global $data, $db;
    
  // cập nhật thông tin lên csdl
  $thoigian = isodatetotime($data->thoigian);
  $tongdoanhthu = str_replace(',', '', $data->tongdoanhthu);
  $tongloinhuan = str_replace(',', '', $data->tongloinhuan);
  $tongchi = str_replace(',', '', $data->tongchi);
  $tongnhanvien = str_replace(',', '', $data->tongnhanvien);
  $tongcodong = str_replace(',', '', $data->tongcodong);

  // nếu có id, cập nhật, nếu không thêm chốt lương
  if (!empty($id = $data->id)) {
    $sql = "update pet_phc_luong set doanhthu = $tongdoanhthu, loinhuan = $tongloinhuan, tienchi = $tongchi, luongnhanvien = $tongnhanvien, lairong = $tongcodong, thoigian = $thoigian where id = $id";
    $db->query($sql);

    // xóa thu chi để thêm lại
    $sql = "delete from pet_phc_luong_tienchi where luongid = $id";
    $db->query($sql);

    // xóa lương nhân viên để thêm lại
    $sql = "delete from pet_phc_luong_tra where luongid = $id";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_phc_luong (doanhthu, loinhuan, tienchi, luongnhanvien, lairong, thoigian, trangthai) values($tongdoanhthu, $tongloinhuan, $tongchi, $tongnhanvien, $tongcodong, $thoigian, 0)";
    $id = $db->insertid($sql);
  }

  // thêm thu chi
  foreach ($data->thuchi as $thuchi) {
    $tienchi = str_replace(',', '', $thuchi->tienchi);
    $sql = "insert into pet_phc_luong_tienchi (luongid, mucchi, tienchi) values($id, '$thuchi->loaichi', $tienchi)";
    $db->query($sql);
  }

  // thêm chốt lương nhân viên
  foreach ($data->nhanvien as $nhanvien) {
    $doanhthu = str_replace(',', '', $nhanvien->doanhthu);
    $loinhuan = str_replace(',', '', $nhanvien->loinhuan);
    $luongcung = str_replace(',', '', $nhanvien->luongcung);
    $luongthuong = str_replace(',', '', $nhanvien->luongthuong);
    $tilethuong = str_replace(',', '', $nhanvien->tilethuong);
    $luongphucap = str_replace(',', '', $nhanvien->luongphucap);
    $nghiphep = str_replace(',', '', $nhanvien->nghiphep);
    $tietkiem = str_replace(',', '', $nhanvien->tietkiem);
    $luongcophan = str_replace(',', '', $nhanvien->luongcophan);
    $tongluong = str_replace(',', '', $nhanvien->tongluong);
    $thucnhan = str_replace(',', '', $nhanvien->thucnhan);
    $phucap2 = str_replace(',', '', $nhanvien->phucap2);
    $sql = "insert into pet_phc_luong_tra (userid, luongid, doanhthu, loinhuan, luong, tile, tilethuong, thuong, luongphucap, phucap2, phucap, ngaynghi, nghiphep, tietkiem, nhantietkiem, tilecophan, cophan, tong, thucnhan) values($nhanvien->userid, $id, $doanhthu, $loinhuan, $luongcung, $nhanvien->tile, $tilethuong, $luongthuong, $luongphucap, $phucap2, $nhanvien->phucap, $nhanvien->ngaynghi, $nghiphep, $tietkiem, 0, $nhanvien->cophan, $luongcophan, $tongluong, $thucnhan)";
    $db->query($sql);
  }

  return $id;
}

function capnhatnhanvien() {
  global $data, $db;

  $data->phucap2 = str_replace(',', '', $data->phucap2);
  $sql = "update pet_phc_luong_nhanvien set tile = '$data->tile', phucap = $data->phucap, phucap2 = $data->phucap2 where userid = $data->userid";
  $db->query($sql);

  $sql = "select * from pet_phc_luong where id = $data->id";
  $luong = $db->fetch($sql);
  
  $sql = "select * from pet_phc_luong_tra where userid = $data->userid and luongid = $data->id";
  $nhanvien = $db->fetch($sql);
  
  $tilethuong = $nhanvien['loinhuan'] * $data->tile / 100;
  $luongphucap = $data->phucap2 + $luong['lairong'] * $data->phucap / 100;
  $sql = "update pet_phc_luong_tra set tile = $data->tile, tilethuong = $tilethuong, phucap = $data->phucap, phucap2 = $data->phucap2, luongphucap = $luongphucap where userid = $data->userid and luongid = $data->id";
  $db->query($sql);

  $result['dulieu'] = dulieuluongtheoid();
  $result['tinnhan'] = 'Đã cập nhật lương';
  $result['status'] = 1;
  return $result;
}

function nhanvienthemtay() {
  global $data, $db;

  $sql = "select b.userid, b.fullname as tennhanvien from pet_phc_luong_nhanvien a inner join pet_phc_users b on a.userid = b.userid order by a.userid";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $nhanvien) {
    $danhsach[$thutu]['doanhthu'] = 0;
    $danhsach[$thutu]['luong'] = 0;
    $danhsach[$thutu]['luongthuong'] = 0;
    $danhsach[$thutu]['phucap'] = 0;
    $danhsach[$thutu]['nghiphep'] = 0;
    $danhsach[$thutu]['tietkiem'] = 0;
    $danhsach[$thutu]['cophan'] = 0;
    $danhsach[$thutu]['tongluong'] = 0;
    $danhsach[$thutu]['thucnhan'] = 0;
  }

  $result['danhsach'] = array(
    'thoigian' => date('d-m-Y'),
    'danhsach' => $danhsach
  );
  $result['status'] = 1;
  return $result;
}

function themtay() {
  global $data, $db;

  $thoigian = isodatetotime($data->thoigian);
  $sql = "insert into pet_phc_luong (doanhthu, loinhuan, tienchi, luongnhanvien, lairong, thoigian, trangthai) values(0, 0, 0, 0, 0, $thoigian, 1)";
  $id = $db->insertid($sql);

  foreach ($data->danhsach as $danhsach) {
    $doanhthu = str_replace(',', '', $danhsach->doanhthu);
    $luong = str_replace(',', '', $danhsach->luong);
    $luongthuong = str_replace(',', '', $danhsach->luongthuong);
    $thuong = $luongthuong;
    if ($thuong < 0) $thuong = 0;
    $phucap = str_replace(',', '', $danhsach->phucap);
    $nghiphep = str_replace(',', '', $danhsach->nghiphep);
    $tietkiem = str_replace(',', '', $danhsach->tietkiem);
    $cophan = str_replace(',', '', $danhsach->cophan);
    $tongluong = str_replace(',', '', $danhsach->tongluong);
    $thucnhan = str_replace(',', '', $danhsach->thucnhan);
    
    $sql = "insert into pet_phc_luong_tra (userid, luongid, doanhthu, loinhuan, luong, tile, tilethuong, thuong, luongphucap, phucap2, phucap, ngaynghi, nghiphep, tietkiem, nhantietkiem, tilecophan, cophan, tong, thucnhan) values($danhsach->userid, $id, $doanhthu, 0, $luong, 0, $luongthuong, $thuong, $phucap, 0, 0, 0, $nghiphep, $tietkiem, 0, 0, $cophan, $tongluong, $thucnhan)";
    $db->query($sql);
  }

  $result['danhsach'] = danhsachluong();
  $result['status'] = 1;
  return $result;
}

function sosanh() {
  global $data, $db;

  $sql = "select b.userid, b.fullname as tennhanvien from pet_phc_luong_nhanvien a inner join pet_phc_users b on a.userid = b.userid order by a.userid";
  $danhsachnhanvien = $db->all($sql);

  $thoigian = isodatetotime($data->thoigian);
  $thangnay = date('m', $thoigian);
  $namnay = date('Y', $thoigian);
  $denngay = strtotime(date($namnay .'/'. ($thangnay + 1) .'/1'));
  if ($thangnay == 1) {
    // nếu tháng này là tháng 1 thì lấy từ tháng 2 năm trước đến năm nay
    $tungay = strtotime(($namnay - 1) . '/2/1');
  }
  else {
    // nếu không lấy từ tháng 2 đến tháng này
    $tungay = strtotime(date($namnay .'/2/1'));
  }

  foreach ($danhsachnhanvien as $thutu => $nhanvien) {
    $danhsachnhanvien[$thutu]['danhsach'] = array('luongcoban' => array(), 'luongthuong' => array(), 'luongphucap' => array());
    for ($i = 0; $i < 12; $i++) { 
      $danhsachnhanvien[$thutu]['danhsach']['luongcoban'][$i] = 0;
      $danhsachnhanvien[$thutu]['danhsach']['luongthuong'][$i] = 0;
      $danhsachnhanvien[$thutu]['danhsach']['luongphucap'][$i] = 0;
    }

    $sql = "select a.thoigian, b.* from pet_phc_luong a inner join pet_phc_luong_tra b on a.id = b.luongid where b.userid = $nhanvien[userid] and (a.thoigian between $tungay and $denngay) order by id desc";
    $danhsachluong = $db->all($sql);

    foreach ($danhsachluong as $luong) {
      $ngay1 = intval(date('m', $luong['thoigian']));
      if ($ngay1 == 1) $ngay = $ngay1 + 10;
      else $ngay = $ngay1 - 2;

      $danhsachnhanvien[$thutu]['danhsach']['luongcoban'][$ngay] = number_format($luong['luong']);
      $danhsachnhanvien[$thutu]['danhsach']['luongthuong'][$ngay] = number_format($luong['tilethuong']);
      $danhsachnhanvien[$thutu]['danhsach']['luongphucap'][$ngay] = number_format($luong['luongphucap']);
    }
  }
  
  $result['status'] = 1;
  $result['danhsach'] = $danhsachnhanvien;
  return $result;
}

