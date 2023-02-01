  /*===== COLLAPSE MENU  =====*/ 
  const linkCollapse = document.getElementsByClassName('pedido-container');
  var i
  
  for(i=0;i<linkCollapse.length;i++){
    linkCollapse[i].addEventListener('click', function(){
      const collapseMenu = this.nextElementSibling;
      collapseMenu.classList.toggle('openPedido');
      
    })
  }
  
  
function closeSec(){ 
  if(document.getElementById("content").style.display == "none"){
    document.getElementById("content").style.display = "block"; 
    document.getElementById("icon-rotate").style.rotate = "0deg";   
  }else{
    document.getElementById("content").style.display = "none";
    document.getElementById("icon-rotate").style.rotate = "180deg";
  } 
}

function closeSec2(){   
  if(document.getElementById("content2").style.display == "none"){
    document.getElementById("content2").style.display = "block"; 
    document.getElementById("icon-rotate2").style.rotate = "0deg";   
  }else{
    document.getElementById("content2").style.display = "none";
    document.getElementById("icon-rotate2").style.rotate = "180deg";
  }
}


//Number Euro
const numbersEuro = document.querySelectorAll('.numEuro');
function countUpEuro(number) {
  let current = 0;
  const target = number.dataset.target;
  const interval = target/70; // Increase the number by 10 every 10 milliseconds

  const timer = setInterval(function() {
    current += interval;
    if (current > target) {
      current = target;
    }
    number.innerHTML = current.toFixed(2) + " €";

    if (current >= target) {
      clearInterval(timer);
    }
  }, 10);
}
numbersEuro.forEach(countUpEuro);


//Number Normal
const numbers = document.querySelectorAll('.num');
function countUp(number) {
  let current = 0;
  const target = number.dataset.target;
  const interval = target/70; // Increase the number by 10 every 10 milliseconds

  const timer = setInterval(function() {
    current += interval;
    if (current > target) {
      current = target;
    }
    number.innerHTML = Math.round(current);

    if (current >= target) {
      clearInterval(timer);
    }
  }, 10);
}
numbers.forEach(countUp);

