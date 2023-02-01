
document.querySelector(".menu-chip").classList.add("active");


var counts = 1;


function openMenu() {
  document.getElementById("navMenu").style.height = "100%";
}

function closeMenu() {
  document.getElementById("navMenu").style.height = "0%";
}

//O Abrir popup está no menu-content

var closeBtns = document.querySelectorAll(".overlay-fechar");

closeBtns.forEach(function (btn) {
  btn.onclick = function () {    
    var modal = (btn.closest(".overlay").style.display = "none");
  };
});

let cart = [];

function OpenSideCart(modal) {
  document.querySelector(".menu-content").style.width = "72%";
  document.querySelector(".sidecart").style.width = "430px";
  document.getElementById(modal).style.display = "none";
}


//MOBILE
function closeModal(modal){
  document.getElementById(modal).style.display = "none";
}

function OpenSideCartMobile(modal){
  document.querySelector(".menu-content").style.width = "72%";
  document.querySelector(".sidecart").style.width = "100%";  
}

//FIM MOBILE

function CloseSideCart() {
  document.querySelector(".menu-content").style.width = "100%";
  document.querySelector(".sidecart").style.width = "0px";
}


//Alterar Quantidade
$(document).ready(function() {	
	$(".menos").click(function() {
    if(counts > 1){
		  counts = counts - 1;
    }		
		$(".counts").text(counts);
	});
  $(".mais").click(function() {	
    if(counts < 100){
      counts = counts + 1;
    }
		$(".counts").text(counts);
	});

});


//Mudar categoria acho
$(document).ready(function() {
  $('.menu-chip').click(function() {       
    var rest_id = $(".menu-chip").attr("data-rest");        
    var category = this.id;    
      $.ajax({
          url: 'landing.php',
          type: 'GET',
          data: {
            rest_id: rest_id,
            category: category
          },
          success: function(response) {
            console.log(response);
            $(".menu-content").load("menu-content.php");
          }
      });
  });
});


//Adicionar item ao cart (btn do motal)
$(document).ready(function () {
  $(".modal-adicionar-pc-btn").click(function () {
    var rest_id = $(".modal-adicionar-pc-btn").attr("data-rest");
    var mesa_id = $(".modal-adicionar-pc-btn").attr("data-mesa");
    $.ajax({
      url: "landing.php?rest_id=" + rest_id + "&mesa=" + mesa_id,
      type: "POST",
      data: {
        id: this.id,
        quant: counts,
      },
      success: function (response) {        
        $(".sidecart").load("sidecart.php");
      },
    });
  });
});





