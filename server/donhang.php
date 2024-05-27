<?php
function khoitao() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachdonhang();
  return $result;
}

function danhsachdonhang() {
  global $data, $db, $result;

  $daungay = strtotime(date("Y/m/d"));
  $cuoingay = $daungay + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_hanghoa_donhang where trangthai = 0 or (trangthai between $daungay and $cuoingay) order by id desc";
  $danhsachdonhang = $db->all($sql);

  foreach ($danhsachdonhang as $thutu => $donhang) {
    $danhsachdonhang[$thutu]["thoigian"] = date("d/m/Y H:i", $donhang["thoigian"]);
    $danhsachdonhang[$thutu]["tongtien"] = number_format($donhang["tongtien"]) . "đ";
    $chitietdonhang = [];
    $sql = "select a.*, b.tenhang from pet_". PREFIX ."_hanghoa_donhang_chitiet a inner join pet_". PREFIX ."_hanghoa b on a.idhang = b.id where a.iddonhang = $donhang[id]";
    $danhsachhanghoa = $db->all($sql);
    foreach ($danhsachhanghoa as $hanghoa) {
      $chitietdonhang []= $hanghoa["soluong"] . " x ". $hanghoa["tenhang"] ." = ".  number_format($hanghoa["giaban"]) ."đ";
    }
    $danhsachdonhang[$thutu]["hanghoa"] = $chitietdonhang;
  }
  return $danhsachdonhang;
}

function guihang() {
  global $data, $db, $result;

  $thoigian = time();
  $sql = "update pet_". PREFIX ."_hanghoa_donhang set trangthai = $thoigian where id = $data->id";
  $db->query($sql);

  guithongbaoapp($data->token, $data->idkhach, "Cập nhật đơn hàng", "Đơn hàng quý khách đặt đang trên đường giao, vui lòng chờ 1-3 tiếng nếu là đơn nội thành, 1-3 ngày đối vớI đơn huyện, 1-7 ngày đối với các tỉnh khác");

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
