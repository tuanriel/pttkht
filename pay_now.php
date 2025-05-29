<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

date_default_timezone_set("Asia/Kolkata");
session_start();

// Kiểm tra đăng nhập
if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
    redirect('index.php');
}

// Nếu người dùng bấm nút 'Đặt phòng'
if (isset($_POST['pay_now'])) {
    $frm_data = filteration($_POST);
    $user_id = $_SESSION['uId'];
    $room = $_SESSION['room'];

    // Ghi dữ liệu vào bảng booking_order
    $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`, `booking_status`) 
               VALUES (?, ?, ?, ?, ?)";

    insert($query1, [
        $user_id,
        $room['id'],
        $frm_data['checkin'],
        $frm_data['checkout'],
        'booked'
    ], 'issss');

    $booking_id = mysqli_insert_id($con); // Lấy booking_id vừa tạo

    // Ghi vào bảng booking_details
    $query2 = "INSERT INTO `booking_details`
        (`booking_id`, `room_name`, `price`, `total_pay`, `user_name`, `phonenum`, `address`) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    insert($query2, [
        $booking_id,
        $room['name'],
        $room['price'],
        $room['price'], // tổng tiền bằng giá phòng (1 đêm), bạn có thể nhân số đêm nếu cần
        $frm_data['name'],
        $frm_data['phonenum'],
        $frm_data['address']
    ], 'issssss');

    // Xóa thông tin phòng đã đặt trong session
    unset($_SESSION['room']);

    // Chuyển đến trang lịch sử đặt phòng
    redirect('bookings.php');
}
?>
