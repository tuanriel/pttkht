function get_bookings(search='')
{
  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/confirm_payment.php",true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function(){
    document.getElementById('table-data').innerHTML = this.responseText;
  }

  xhr.send('get_bookings&search='+search);
}

  let confirm_payment_form = document.getElementById('confirm_payment_form'); 

function confirm_payment(id){
  confirm_payment_form.elements['booking_id'].value = id;
}

confirm_payment_form.addEventListener('submit', function(e){
  e.preventDefault();

  let data = new FormData();
  data.append('booking_id', confirm_payment_form.elements['booking_id'].value);
  data.append('trans_amt', confirm_payment_form.elements['trans_amt'].value);
  data.append('confirm_payment', '');

  let xhr = new XMLHttpRequest();
  xhr.open("POST", "ajax/confirm_payment.php", true);

  xhr.onload = function(){
    var myModal = document.getElementById('confirm-payment');
    var modal = bootstrap.Modal.getInstance(myModal);
    modal.hide();

    if(this.responseText == 1){
      alert('success', 'Payment Confirmed!');
      confirm_payment_form.reset();
      get_bookings();
    } else {
      alert('error', 'Server Error!');
    }
  }

  xhr.send(data);
});


function cancel_booking(id) 
{
  if(confirm("Are you sure, you want to cancel this booking?"))
  {
    let data = new FormData();
    data.append('booking_id',id);
    data.append('cancel_booking','');

    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/confirm_payment.php",true);

    xhr.onload = function()
    {
      if(this.responseText == 1){
        alert('success','Booking Cancelled!');
        get_bookings();
      }
      else{
        alert('error','Server Down!');
      }
    }

    xhr.send(data);
  }
}

window.onload = function(){
  get_bookings();
}