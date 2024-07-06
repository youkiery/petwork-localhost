<?php

function remove() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  removeData();
  $result['messenger'] = 'Đã xóa giải pháp';
  $result['list'] = initList();
  
  return $result;
}

function insert() {
  global $db, $data, $result;
  
  insertData();
  
  $result['status'] = 1;
  $result['list'] = initList();
  $result['messenger'] = 'Đã thêm giải pháp';

  return $result;
}

function init() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['list'] = initList();
   
  return $result;
}

function edit() {
  global $db, $data, $result;

  $result['status'] = 1;
  updateData();
  $result['list'] = initList();

  return $result;
}

function done() {
  global $db, $data, $result;

  checkData();
  $result['status'] = 1;
  $result['messenger'] = 'Đã hoàn thành';
  $result['list'] = initList();

  return $result;
}

function initList() {
  global $db, $data;

  $filter = $data->filter;
  $filter->endtime = isodatetotime($filter->endtime) + 60 * 60 * 24 - 1;
  $filter->starttime = isodatetotime($filter->starttime);

  $list = array(
    'done' => array(),
    'undone' => array(),
  );
  $xtra = array();
  $role = checkRole();

  if (!empty($filter->keyword)) $xtra []= '((result like "%'. $filter->keyword .'%") or (solution like "%'. $filter->keyword .'%") or (problem like "%'. $filter->keyword .'%") or (noidungtugiac like "%'. $filter->keyword .'%") or (noidungdongdoi like "%'. $filter->keyword .'%"))';
  if ($role < 2) $xtra []= 'userid = ' . $data->idnguoidung;
  if (count($xtra)) $xtra = ' and ' . implode(' and ', $xtra);
  else $xtra = '';
  
  $sql = "select * from pet_". PREFIX ."_kaizen where active = 1 $xtra and done = 0 order by edit_time desc";
  $danhsach = $db->all($sql);
  
  foreach ($danhsach as $row) {
    $user = laydulieunhanvien($row['userid']);
    $image = parseimage($row['image']);
    $hinhanhdongdoi = parseimage($row['hinhanhdongdoi']);
    $hinhanhtugiac = parseimage($row['hinhanhtugiac']);
    $data = array(
      'id' => $row['id'],
      'name' => $user['hoten'],
      'done' => intval($row['done']),
      'problem' => $row['problem'],
      'solution' => $row['solution'],
      'result' => $row['result'],
      'image' => $image,
      'noidungdongdoi' => $row['noidungdongdoi'],
      'noidungtugiac' => $row['noidungtugiac'],
      'hinhanhdongdoi' => $hinhanhdongdoi,
      'hinhanhtugiac' => $hinhanhtugiac,
      'time' => date('d/m/Y', $row['edit_time'])
    );
    $list['undone'] []= $data;
  }
  
  $sql = "select * from pet_". PREFIX ."_kaizen where active = 1 $xtra and done = 1 and (edit_time between $filter->starttime and  $filter->endtime) order by edit_time desc";
  $query = $db->query($sql);
  
  while ($row = $query->fetch_assoc()) {
    $user = laydulieunhanvien($row['userid']);
    $image = parseimage($row['image']);
    $hinhanhdongdoi = parseimage($row['hinhanhdongdoi']);
    $hinhanhtugiac = parseimage($row['hinhanhtugiac']);
    $data = array(
      'id' => $row['id'],
      'name' => $user['hoten'],
      'done' => intval($row['done']),
      'problem' => $row['problem'],
      'solution' => $row['solution'],
      'result' => $row['result'],
      'image' => $image,
      'noidungdongdoi' => $row['noidungdongdoi'],
      'noidungtugiac' => $row['noidungtugiac'],
      'hinhanhdongdoi' => $hinhanhdongdoi,
      'hinhanhtugiac' => $hinhanhtugiac,
      'time' => date('d/m/Y', $row['edit_time'])
    );
    $list['done'] []= $data;
  }
  return $list;    
}

function getKaizenById($id) {
  global $db;
  $sql = "select * from pet_". PREFIX ."_kaizen where id = $id";
  $query = $db->query($sql);
  return $query->fetch_assoc();
}

function insertData() {
  global $db, $data;

  $time = time();
  $image = implode(',', $data->image);
  $hinhanhtugiac = implode(',', $data->hinhanhtugiac);
  $hinhanhdongdoi = implode(',', $data->hinhanhdongdoi);

  $sql = "insert into pet_". PREFIX ."_kaizen (userid, problem, solution, result, post_time, edit_time, image, noidungdongdoi, hinhanhdongdoi, noidungtugiac, hinhanhtugiac) values($data->idnguoidung, '$data->problem', '$data->solution', '$data->result', $time, $time, '$image', '$data->noidungdongdoi', '$hinhanhdongdoi', '$data->noidungtugiac', '$hinhanhtugiac')";
  $db->query($sql);
}

function updateData() {
  global $db, $data;

  $time = time();
  $image = implode(',', $data->image);
  $hinhanhtugiac = implode(',', $data->hinhanhtugiac);
  $hinhanhdongdoi = implode(',', $data->hinhanhdongdoi);

  $sql = "update pet_". PREFIX ."_kaizen set problem = '$data->problem', solution = '$data->solution', result = '$data->result', edit_time = $time, image = '$image', noidungdongdoi = '$data->noidungdongdoi', noidungtugiac = '$data->noidungtugiac', hinhanhdongdoi = '$hinhanhdongdoi', hinhanhtugiac = '$hinhanhtugiac' where id = $data->id";
  $db->query($sql);
}

function removeData() {
  global $db, $data;
  $sql = "update pet_". PREFIX ."_kaizen set active = 0 where id = $data->id";
  $db->query($sql);
}

function checkData() {
  global $db, $data;
  $time = time();
  $sql = "update pet_". PREFIX ."_kaizen set done = 1, edit_time = $time where id = $data->id";
  $db->query($sql);
}

function checkRole() {
  global $db, $data;

  $sql = "select * from pet_nhanvien_phanquyen where chucnang = 'kaizen' and idnhanvien = $data->idnguoidung and idchinhanh = $data->idchinhanh";
  if (!empty($p = $db->fetch($sql))) return $p['vaitro'];
  return 0;
}
