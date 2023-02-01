

function ativarCor(id) {  
  const navLinks = document.querySelectorAll(".menu-chip");
  navLinks.forEach((link) => {
    link.classList.remove("active");
  });
  document.getElementById(id).classList.add("active");
}



$(document).ready(function() {
  $('.menu-chip').click(function() {
      var tipoPedido = this.id;       
      $.ajax({
          url: 'pedidos.php',
          type: 'GET',
          data: {
              tipoPedido: tipoPedido
          },
          success: function(response) {
              console.log(response);
              $(".main-content").load("pedidos-content.php", function() {
                  toggleCollapseMenu();
              });
          }
      });
  });
});
