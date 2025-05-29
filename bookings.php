<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - BOOKINGS</title>
</head>
<body class="bg-light">

  <?php 
    require('inc/header.php'); 

    if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
      redirect('index.php');
    }
  ?>


  <div class="container">
    <div class="row">

      <div class="col-12 my-5 px-4">
        <h2 class="fw-bold">ĐẶT PHÒNG</h2>
        <div style="font-size: 14px;">
          <a href="index.php" class="text-secondary text-decoration-none">Trang Chủ</a>
          <span class="text-secondary"> > </span>
          <a href="#" class="text-secondary text-decoration-none">Đặt Phòng</a>
        </div>
      </div>

      <?php 
        
        
        $query = "SELECT bo.*, bd.* FROM `booking_order` bo
          INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
          WHERE ((bo.booking_status='booked') 
          OR (bo.booking_status='cancelled')
          OR (bo.booking_status='payment failed')
          OR (bo.booking_status='payment_success')) 
          AND (bo.user_id=?)
          ORDER BY bo.booking_id DESC";

        $result = select($query,[$_SESSION['uId']],'i');

        while($data = mysqli_fetch_assoc($result))
        {
          $date = date("d-m-Y",strtotime($data['datentime']));
          $checkin = date("d-m-Y",strtotime($data['check_in']));
          $checkout = date("d-m-Y",strtotime($data['check_out']));

          $status_bg = "";
          $btn = "";
          
          if($data['booking_status']=='booked')
          {
            $status_bg = "bg-success";
            if($data['arrival']==1)
            {
              $btn="<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Tải PDF</a>";
 
              if($data['rate_review']==0){
                $btn.="<button type='button' onclick='review_room($data[booking_id],$data[room_id])' data-bs-toggle='modal' data-bs-target='#reviewModal' class='btn btn-dark btn-sm shadow-none ms-2'>Đánh Giá</button>";
              }
            }
            else{
              $btn="<button onclick='cancel_booking($data[booking_id])' type='button' class='btn btn-danger btn-sm shadow-none'>Hủy</button>";
            }
          }
          else if($data['booking_status']=='cancelled')
          {
            $status_bg = "bg-danger";

            if($data['refund']==0){
              $btn="<span class='badge bg-primary'>Đang xử lý hoàn tiền!</span>";
            }
            else{
              $btn="<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Tải PDF</a>";
            }
          }
          else if($data['booking_status']=='payment_success')
          {
            $status_bg = "bg-success";
            $btn="<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Tải PDF</a>";

            if($data['rate_review'] == 0){
              $btn.="<button type='button' onclick='review_room($data[booking_id],$data[room_id])' data-bs-toggle='modal' data-bs-target='#reviewModal' class='btn btn-dark btn-sm shadow-none ms-2'>Đánh Giá</button>";
            }
          }
          else
          {
            $status_bg = "bg-warning";
            $btn="<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Tải PDF</a>";
          }

          echo<<<bookings
            <div class='col-md-4 px-4 mb-4'>
              <div class='bg-white p-3 rounded shadow-sm'>
                <h5 class='fw-bold'>$data[room_name]</h5>
                <p>$data[price] / Đêm</p>
                <p>
                  <b>Ngày nhận phòng: </b> $checkin <br>
                  <b>Ngày trả phòng: </b> $checkout
                </p>
                <p>
                  <b>Giá: </b> $data[price] <br>
                  <b>ID đặt phòng: </b> $data[order_id] <br>
                  <b>Ngày đặt: </b> $date
                </p>
                <p>
                  <span class='badge $status_bg'>$data[booking_status]</span>
                </p>
                $btn
              </div>
            </div>
          bookings;

        }

      ?>


    </div>
  </div>


  <div class="modal fade" id="reviewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="review-form">
          <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
              <i class="bi bi-chat-square-heart-fill fs-3 me-2"></i> Đánh giá
            </h5>
            <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Đánh Giá</label>
              <select class="form-select shadow-none" name="rating">
                <option value="5">Tuyệt Vời</option>
                <option value="4">Tốt</option>
                <option value="3">Ok</option>
                <option value="2">Tệ</option>
                <option value="1">Rất tệ</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="form-label">Đánh giá</label>
              <textarea type="password" name="review" rows="3" required class="form-control shadow-none"></textarea>
            </div>
            
            <input type="hidden" name="booking_id">
            <input type="hidden" name="room_id">

            <div class="text-end">
              <button type="submit" class="btn custom-bg text-white shadow-none">CHỌN</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>



  <?php 
    if(isset($_GET['cancel_status'])){
      alert('success','Đã hủy đặt phòng!');
    }  
    else if(isset($_GET['review_status'])){
      alert('success','Cảm ơn đã đánh giá!');
    }  
  ?>

  <?php require('inc/footer.php'); ?>

  <script>
    function cancel_booking(id)
    {
      if(confirm('Bạn có chắc chắn muốn hủy đặt phòng không?'))
      {        
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/cancel_booking.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function(){
          if(this.responseText==1){
            window.location.href="bookings.php?cancel_status=true";
          }
          else{
            alert('error','Cancellation Failed!');
          }
        }

        xhr.send('cancel_booking&id='+id);
      }
    }

    let review_form = document.getElementById('review-form');

    function review_room(bid,rid){
      review_form.elements['booking_id'].value = bid;
      review_form.elements['room_id'].value = rid;
    }

    review_form.addEventListener('submit',function(e){
      e.preventDefault();

      let data = new FormData();

      data.append('review_form','');
      data.append('rating',review_form.elements['rating'].value);
      data.append('review',review_form.elements['review'].value);
      data.append('booking_id',review_form.elements['booking_id'].value);
      data.append('room_id',review_form.elements['room_id'].value);

      let xhr = new XMLHttpRequest();
      xhr.open("POST","ajax/review_room.php",true);

      xhr.onload = function()
      {

        if(this.responseText == 1)
        {
          window.location.href = 'bookings.php?review_status=true';
        }
        else{
          var myModal = document.getElementById('reviewModal');
          var modal = bootstrap.Modal.getInstance(myModal);
          modal.hide();
  
          alert('error',"Đánh giá không success!");
        }
      }

      xhr.send(data);
    })

  </script>


</body>
</html>