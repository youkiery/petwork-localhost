<?php
define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
include_once(ROOTDIR . '/include/config.php');
include_once(ROOTDIR . '/server/db.php');
include_once(ROOTDIR . '/server/global.php');
$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);
$userid = checkuserid();
$sql = "select a.*, b.type, b.time as rtime, b.list from pet_phc_work a inner join pet_phc_work_repeat b on a.id = b.workid and b.status = 1";
$danhsachcongviec = $db->all($sql);
$today = time();
$endday = $today + 60 * 60 * 24 - 1;

foreach ($danhsachcongviec as $key => $congviec) {
  // switch type
  $expire = 0;
  if ($congviec['expiretime'] > 0) $expire = $today + $congviec['expiretime'] - $congviec['createtime'];
  switch ($congviec['type']) {
    case '1':
      // split list
      // nếu ngày hôm nay trong split type thì thêm
      $list = explode(',', $congviec['list']);
      $day = strval(date('w', $today));
      if (in_array($day, $list) != false) {
        $sql = "insert into pet_phc_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($congviec[userid], $congviec[departid], '$congviec[title]', '$congviec[content]', '$congviec[file]', $today, $today, $expire, $today, 0)";
        $db->query($sql);
      }
      break;
    case '2':
      // tính ex của tháng này
      // nếu ngày hôm nay = ex thì thêm
      $time = strtotime(date('Y', $today) .'/'. date('m', $today) .'/'. date('d', $congviec['rtime']));
      if ($time >= $today && $time <= $endday) {
        $sql = "insert into pet_phc_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($congviec[userid], $congviec[departid], '$congviec[title]', '$congviec[content]', '$congviec[file]', $today, $today, $expire, $today, 0)";
        $db->query($sql);
      }
      break;
    case '3':
      // tính ex của năm
      // nếu ngày hôm nay = ex thì thêm
      $time = strtotime(date('Y', $today) .'/'. date('m', $congviec['rtime']) .'/'. date('d', $congviec['rtime']));
      if ($time >= $today && $time <= $endday) {
        $sql = "insert into pet_phc_work (userid, departid, title, content, file, time, createtime, expiretime, updatetime, status) values($congviec[userid], $congviec[departid], '$congviec[title]', '$congviec[content]', '$congviec[file]', $today, $today, $expire, $today, 0)";
        $db->query($sql);
      }
      break;
  }
  
}
