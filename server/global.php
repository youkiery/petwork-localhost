<?php
function checkuserid() {
  global $db, $data;

  $sql = "select * from pet_phc_session where session = '$data->session'";
  $user = $db->fetch($sql);
  return $user['userid'];
}

function parseimage($image) {
  $image = explode(',', $image);
  $l = array();
  foreach ($image as $key => $value) {
    if (!empty($value)) $l []= trim($value);
  }
  return $l;
}

function purenumber($number) {
  $n = '';
  $number = strval($number);
  $arr = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
  $l = strlen($number);
  for ($i = 0; $i < $l; $i++) {
    $value = $number[$i]; 
    if (in_array($value, $arr) != false) $n = $n . $value;
  }
  return intval($n);
}

function getcustomer($id) {
  global $db;

  $sql = "select id, name, phone from pet_phc_customer where id = $id";
  return $db->fetch($sql);
}

function checkUserById($userid) {
  global $db;

  $sql = "select * from pet_phc_users where userid = $userid";
  return $db->fetch($sql);
}

function totime($date) {
  if (preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/", $date, $m)) {
    $date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
  }
  else return false;
  return $date;
}

function isodatetotime($iso) {
  // 2021-10-28T00:00:00.000Z
  $dates = explode('T', $iso);
  $timestring = str_replace('-', '/', $dates[0]);
  return strtotime($timestring);
}

function lower($str) {
  $str = mb_strtolower($str);
  $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
  $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
  $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
  $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
  $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
  $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
  $str = preg_replace("/(đ)/", 'd', $str);
  return $str;
}

function alias($str) {
  $str = mb_strtolower($str);
  $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
  $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
  $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
  $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
  $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
  $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
  $str = preg_replace("/(đ)/", 'd', $str);
  $str = str_replace('%0d%0a', '', $str);
  $str = trim(str_replace(' ', '', $str));
  return $str;
}

function randomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}