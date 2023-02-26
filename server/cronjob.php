<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
include_once(DIR . '/include/config.php');
include_once(DIR . '/server/db.php');

define('PREFIX', $config['prefix']);
$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);
$sql = "select a.*, b.type, b.time as rtime, b.list from pet_". PREFIX ."_work a inner join pet_". PREFIX ."_work_repeat b on a.id = b.workid and b.status = 1";
$danhsachcongviec = $db->all($sql);
$today = strtotime(date('Y/m/d')) + 60 * 60 * 24;
// $today = strtotime('2023/02/13');
$endday = $today + 60 * 60 * 24 - 1;

// $log = time();
// error_log($log."\n", 3, DIR .'/include/log/' . time() . '.log');

foreach ($danhsachcongviec as $key => $congviec) {
  // switch type
  $expire = 0;
  $workdid = 0;
  if ($congviec['expiretime'] > 0) $expire = $today + $congviec['expiretime'] - $congviec['createtime'];
  switch ($congviec['type']) {
    case '1':
      // split list
      // nếu ngày hôm nay trong split type thì thêm
      $list = explode(',', $congviec['list']);
      $day = strval(date('N', $today)) - 1;
      
      if ($list[$day] == '1') {
        $sql = "insert into pet_". PREFIX ."_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($congviec[userid], $congviec[departid], '$congviec[title]', '$congviec[content]', '$congviec[file]', $today, $today, $expire, $today, 0)";
        $workid = $db->insertid($sql);
      }
      break;
    case '2':
      // tính ex của tháng này
      // nếu ngày hôm nay = ex thì thêm
      $time = strtotime(date('Y', $today) .'/'. date('m', $today) .'/'. date('d', $congviec['rtime']));
      if ($time >= $today && $time <= $endday) {
        $sql = "insert into pet_". PREFIX ."_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($congviec[userid], $congviec[departid], '$congviec[title]', '$congviec[content]', '$congviec[file]', $today, $today, $expire, $today, 0)";
        $workid = $db->insertid($sql);
      }
      break;
    case '3':
      // tính ex của năm
      // nếu ngày hôm nay = ex thì thêm
      $time = strtotime(date('Y', $today) .'/'. date('m', $congviec['rtime']) .'/'. date('d', $congviec['rtime']));
      if ($time >= $today && $time <= $endday) {
        $sql = "insert into pet_". PREFIX ."_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($congviec[userid], $congviec[departid], '$congviec[title]', '$congviec[content]', '$congviec[file]', $today, $today, $expire, $today, 0)";
        $workid = $db->insertid($sql);
      }
      break;
  }
  if ($workid) {
    $sql = "select * from pet_". PREFIX ."_work_follow where workid = $congviec[id]";
    $dstheodoi = $db->arr($sql, 'userid');
      
    foreach ($dstheodoi as $nhanvien) {
      $sql = "insert into pet_". PREFIX ."_work_follow (workid, userid) values($workid, $nhanvien)";
      $db->query($sql);
    }
     
    $sql = "select * from pet_". PREFIX ."_work_assign where workid = $congviec[id]";
    $dstheodoi = $db->arr($sql, 'userid');
      
    foreach ($dstheodoi as $nhanvien) {
      $sql = "insert into pet_". PREFIX ."_work_assign (workid, userid) values($workid, $nhanvien)";
      $db->query($sql);
    }
  }
}
