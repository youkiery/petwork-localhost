<?php
$dir = str_replace('/server', '', ROOTDIR);
include $dir .'/include/PHPExcel/IOFactory.php';

$x = array();
$xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
foreach ($xr as $key => $value) {
  $x[$value] = $key;
}

function note() {
  global $db, $data, $result;

  $sql = "update pet_phc_account set note = '$data->note' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
} 

function init() {
  global $db, $data, $result;

  $result['data'] = array('kiot' => array(), 'vietcom' => array());
  $sql = "select * from pet_phc_config where module = 'kiot'";
  $query = $db->query($sql);

  while ($row = $query->fetch_assoc()) {
    $result['data']['kiot'][$row['name']] = $row['value'];
  }
  
  $sql = "select * from pet_phc_config where module = 'vietcom'";
  $query = $db->query($sql);

  while ($row = $query->fetch_assoc()) {
    $result['data']['vietcom'][$row['name']] = $row['value'];
  }
  $result['status'] = 1;
  return $result;
}

function parseAllKey($data) {
  $data['content'] = parseKey($data['content']);
  $data['time'] = parseKey($data['time']);
  $data['money'] = parseKey($data['money']);
  return $data;
} 

function parseKey($text) {
  $keyA = $text[0];
  $keyB = substr($text, 1);
  return array('a' => $keyA, 'b' => $keyB);
}

function save() {
  global $db, $data, $result;

  $sql = "select * from pet_phc_config where module = '$data->module' and name = '$data->name'";
  if (empty($db->fetch($sql))) {
    $sql = "insert into pet_phc_config (module, name, value) values('$data->module', '$data->name', '$data->value')";
  }
  else {
    $sql = "update pet_phc_config set value = '$data->value' where module = '$data->module' and name = '$data->name'";
  }
  $db->query($sql);
  $result['status'] = 1;
  return $result;
}

function excel() {
  global $data, $db, $result, $dir, $x, $xr, $_FILES;

  // $des = $dir ."export/DanhSachChiTietHoaDon_KV09102021-222822-523-1633793524.xlsx";
  $name1 = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) ."-". time() .".". pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name2 = pathinfo($_FILES['file2']['name'], PATHINFO_FILENAME) ."-". time() .".". pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION);
  $kiotkey = parseAllKey($data->kiot);
  $vietkey = parseAllKey($data->vietcom);
  $vietcomtemp = getData($_FILES['file2'], $name2, $vietkey);
  $kiottemp = getData($_FILES['file'], $name1, $kiotkey);

  // kiểm tra thời gian
  $res = array(
    'on' => 1,
    'pair' => array(),
    'kiot' => array(),
    'vietcom' => array()
  );

  // echo json_encode($kiottemp);die();
  // echo "<br>";
//   echo json_encode($vietcomtemp);die();
  $kiot = array();
  $total = array('vietcom' => 0, 'kiot' => 0, 'subtract');
  foreach ($kiottemp as $row) {
    $check = false;

    foreach ($vietcomtemp as $key => $value) {
      if ($value['money'] == $row['money']) {
        $check = true;
        $total['kiot'] += intval($row['money']);
        $total['vietcom'] += intval($row['money']);
        $res['pair'] []= array('money' => $row['money'], 'kiot' => $row['content'], 'vietcom' => $value['content']);
        unset($vietcomtemp[$key]);
        break;
      }
    }
    if (!$check) {
      $total['kiot'] += intval($row['money']);
      $res['kiot'] []= array('money' => $row['money'], 'kiot' => $row['content'], 'vietcom' => '');
    }
  }

  foreach ($vietcomtemp as $row) {
    $total['vietcom'] += intval($row['money']);
    $res['vietcom'] []= array('money' => $row['money'], 'vietcom' => $row['content'], 'kiot' => '');
  }

  if (file_exists("$dir/include/export/$name1")) {
    unlink("$dir/include/export/$name1");
  }
  if (file_exists("$dir/include/export/$name2")) {
    unlink("$dir/include/export/$name2");
  }

  $time = time();
  $total['subtract'] = $total['kiot'] - $total['vietcom'];
  $content = addslashes(json_encode(array(
    'data' => $res,
    'total' => $total
  ), JSON_UNESCAPED_UNICODE));
  $sql = "insert into pet_phc_account (time, content, note) values ($time, '$content', '')";
  $id = $db->insertid($sql);

  $result['id'] = $id;
  $result['data'] = $res;
  $result['total'] = $total;
  $result['messenger'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}

function remove() {
  global $db, $data, $result;

  $sql = "delete from pet_phc_account where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getOld();
  return $result;
}

function old() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['list'] = getOld();
  return $result;
}

function getOld() {
  global $data, $db;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_phc_account where time between $data->start and $data->end order by id desc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['content'] = str_replace('\n', ' ', $row['content']);
  }

  return $list;
}

function getData($file, $tar, $key) {
  global $x, $xr, $dir;
  $raw = $file['tmp_name'];
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  $name = pathinfo($file['name'], PATHINFO_FILENAME);
  $des = "$dir/include/export/$tar";

  move_uploaded_file($raw, $des);

  $inputFileType = PHPExcel_IOFactory::identify($des);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $excel = array();
  for ($j = intval($key['content']['b']); $j <= $highestRow; $j ++) {
    $temp = array();
    $check = false;
    foreach ($key as $name => $data) {
      if ($name == 'money') {
        $val = str_replace(',', '', $sheet->getCell($data['a'] . $j)->getValue());
        $temp[$name] = $val;
      }
      else {
        $val = $sheet->getCell($data['a'] . $j)->getValue();
        if ($name == 'time' && (empty($val) || $val == 'Tổng số')) $check = true;
        $temp[$name] = $val;
      }
    }
    if ($check) break;
    if (!empty($temp['money'])) $excel []= $temp;
  }

  return $excel;
}
