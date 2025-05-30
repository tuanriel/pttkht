<?php 

  require('../inc/db_config.php');
  require('../inc/essentials.php');
  adminLogin();

  if(isset($_POST['get_bookings']))
  {
    $frm_data = filteration($_POST);

    $query = "SELECT bo.*, bd.* FROM `booking_order` bo
      INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
      WHERE (bo.order_id LIKE ? OR bd.phonenum LIKE ? OR bd.user_name LIKE ?) 
      AND (bo.booking_status=? AND bo.arrival=?) ORDER BY bo.booking_id ASC";

    $res = select($query,["%$frm_data[search]%","%$frm_data[search]%","%$frm_data[search]%","booked",1],'sssss');
    
    $i=1;
    $table_data = "";

    if(mysqli_num_rows($res)==0){
      echo"<b>No Data Found!</b>";
      exit;
    }

    while($data = mysqli_fetch_assoc($res))
    {
      $date = date("d-m-Y",strtotime($data['datentime']));
      $checkin = date("d-m-Y",strtotime($data['check_in']));
      $checkout = date("d-m-Y",strtotime($data['check_out']));

      $table_data .="
  <tr>
    <td>$i</td>
    <td>
      <span class='badge bg-primary'>
        Order ID: $data[order_id]
      </span>
      <br>
      <b>Name:</b> $data[user_name]
      <br>
      <b>Phone No:</b> $data[phonenum]
    </td>
    <td>
      <b>Room:</b> $data[room_name]
      <br>
      <b>Price:</b> $data[price] vnd
    </td>
    <td>
      <b>Check-in:</b> $checkin
      <br>
      <b>Check-out:</b> $checkout
      <br>
      <b>Paid:</b> $data[trans_amt] vnd
      <br>
      <b>Date:</b> $date
    </td>
    <td>
      <button type='button' onclick='confirm_payment($data[booking_id])'
        class='btn btn-success btn-sm fw-bold shadow-none mb-2'
        data-bs-toggle='modal' data-bs-target='#confirm-payment'>
        <i class='bi bi-cash'></i> Confirm Payment
      </button>
      <br>
      <button type='button' onclick='cancel_booking($data[booking_id])'
        class='btn btn-outline-danger btn-sm fw-bold shadow-none'>
        <i class='bi bi-trash'></i> Cancel Booking
      </button>
    </td>
  </tr>
";


      $i++;
    }

    echo $table_data;
  }

  // if(isset($_POST['confirm_payment']))
  // {
  //   $frm_data = filteration($_POST);

  //   $query = "UPDATE `booking_order` bo INNER JOIN `booking_details` bd
  //     ON bo.book ing_id = bd.booking_id
  //     SET bo.booking_status = ?, bo.rate_review = ?
  //     WHERE bo.booking_id = ?";

  //   $values = ["payment_success",0,$frm_data['booking_id']];

  //   $res = update($query,$values,'sii'); // it will update 2 rows so it will return 2

  //   echo ($res >= 1) ? 1 : 0;
  // }
  if (isset($_POST['confirm_payment'])) {
  $frm_data = filteration($_POST);

  $query = "UPDATE booking_order SET booking_status = ?, trans_amt = ? WHERE booking_id = ?";
  $values = ['payment_success', $frm_data['trans_amt'], $frm_data['booking_id']];

  $res = update($query, $values, 'sii');

  echo ($res == 1) ? 1 : 0;
}


  if(isset($_POST['cancel_booking']))
  {
    $frm_data = filteration($_POST);
    
    $query = "UPDATE `booking_order` SET `booking_status`=?, `refund`=? WHERE `booking_id`=?";
    $values = ['cancelled',0,$frm_data['booking_id']];
    $res = update($query,$values,'sii');

    echo $res;
  }

?>