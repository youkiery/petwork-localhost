<?php
function khoitao() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  $result['nhanvien'] = danhsachnhanvien();
  $result['hanghoa'] = danhsachhanghoa();
  return $result;
}

function khoitaodonhang() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  $result['nhanvien'] = danhsachnhanvien();
  return $result;
}

function khoitaohanghoa() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['hanghoa'] = danhsachhanghoa();
  return $result;
}

function danhsachhanghoa() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_hanghoa where tenhang like '%$data->tukhoa%' and kichhoat = 1 order by id desc limit 10 offset ". ($data->trang - 1) * 10;
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $hanghoa) {
    if ($hanghoa["hinhanh"] == "") $hinhanh = [];
    else $hinhanh = explode(",", $hanghoa["hinhanh"]);
    $danhsach[$thutu]["image"] = $hinhanh;
    $danhsach[$thutu]["giaban"] = number_format($hanghoa["giaban"]);

    $sql = "select mahang, chuyendoi from pet_". PREFIX ."_hanghoathanhphan where idhang = $hanghoa[id]";
    $danhsach[$thutu]["chuyendoi"] = $db->all($sql);
  }

  return $danhsach;
}

function danhsachnhanvien() {
  global $data, $db, $result;
  
  $sql = "select a.userid, a.fullname as name from pet_". PREFIX ."_users a inner join pet_". PREFIX ."_user_per b on a.userid = b.userid where b.module = 'spa' and b.type > 0";
  return $db->all($sql);
}

function danhsachdonhang() {
  global $data, $db, $result;

  $batdau = isodatetotime($data->batdau);
  $ketthuc = isodatetotime($data->ketthuc) + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_hanghoa_donhang where trangthai < 2 or (thoigian between $batdau and $ketthuc) order by id desc";
  $danhsachdonhang = $db->all($sql);
	$trangthai = [0 => "Chưa xác nhận", "Chờ giao hàng", "Đã giao", "Đã huỷ"];

  foreach ($danhsachdonhang as $thutu => $donhang) {
    $danhsachdonhang[$thutu]["thoigian"] = date("d/m/Y H:i", $donhang["thoigian"]);
    $danhsachdonhang[$thutu]["tongtien"] = number_format($donhang["tongtien"]) . "đ";
    $chitietdonhang = [];
    $sql = "select a.*, b.tenhang from pet_". PREFIX ."_hanghoa_donhang_chitiet a inner join pet_". PREFIX ."_hanghoa b on a.idhang = b.id where a.iddonhang = $donhang[id]";
    $danhsachhanghoa = $db->all($sql);
    foreach ($danhsachhanghoa as $hanghoa) {
      $chitietdonhang []= $hanghoa["soluong"] . " x ". $hanghoa["tenhang"] ." = ".  number_format($hanghoa["giaban"] * $hanghoa["soluong"]) ."đ";
    }
    $danhsachdonhang[$thutu]["hanghoa"] = $chitietdonhang;
    $danhsachdonhang[$thutu]["nhanvien"] = "Chưa chọn";
    if (!empty($donhang["idnhanvien"])) {
      $sql = "select * from pet_". PREFIX ."_users where userid = $donhang[idnhanvien]";
      if (!empty($nhanvien = $db->fetch($sql))) {
        $danhsachdonhang[$thutu]["nhanvien"] = $nhanvien["fullname"];
      }
    }
    $danhsachdonhang[$thutu]["tinhtrang"] = $trangthai[$donhang["trangthai"]];
  }
  return $danhsachdonhang;
}

function guihang() {
  global $data, $db, $result;

  $thoigian = time();
  $idnhanvien = checkuserid();
  $sql = "update pet_". PREFIX ."_hanghoa_donhang set trangthai = 1, idnhanvien = $idnhanvien where id = $data->id";
  $db->query($sql);

  guithongbaoapp($data->idkhach, "Cập nhật đơn hàng", "Đơn hàng quý khách đặt đang trên đường giao, vui lòng chờ 1-3 tiếng nếu là đơn nội thành, 1-3 ngày đối vớI đơn huyện, 1-7 ngày đối với các tỉnh khác");

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  return $result;
}

function hoanthanh() {
  global $data, $db, $result;

  $thoigian = time();
  $idnhanvien = checkuserid();
  $sql = "update pet_". PREFIX ."_hanghoa_donhang set trangthai = 2, idnhanvien = $idnhanvien where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  return $result;
}

function xacnhannhieu() {
  global $data, $db, $result;

  $thoigian = time();
  foreach ($data->danhsach as $iddonhang) {
    $sql = "select trangthai from pet_". PREFIX ."_hanghoa_donhang where id = $iddonhang";
    if (empty($db->fetch($sql)["trangthai"])) {
      // gửi thông báo lần đầu
      guithongbaoapp($data->idkhach, "Cập nhật đơn hàng", "Đơn hàng quý khách đặt đang trên đường giao, vui lòng chờ 1-3 tiếng nếu là đơn nội thành, 1-3 ngày đối vớI đơn huyện, 1-7 ngày đối với các tỉnh khác");
    }

    $sql = "update pet_". PREFIX ."_hanghoa_donhang set trangthai = 1, idnhanvien = $data->uid where id = $iddonhang";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  return $result;
}

function hoanthanhnhieu() {
  global $data, $db, $result;

  $thoigian = time();
  foreach ($data->danhsach as $iddonhang) {
    $sql = "update pet_". PREFIX ."_hanghoa_donhang set trangthai = 2, idnhanvien = $data->uid where id = $iddonhang";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  return $result;
}

function xoahang() {
  global $data, $db, $result;

  $thoigian = time();
  $sql = "update pet_". PREFIX ."_hanghoa_donhang set trangthai = $thoigian where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  return $result;
}
