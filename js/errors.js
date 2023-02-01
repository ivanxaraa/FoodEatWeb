

function InputError(text) {
    
      if (text.length < 8 && text.length >0) {
        
        document.getElementById("input-password").style.border =
          "1px solid var(--corErro1)";
        document.getElementById("input-password").style.backgroundColor =
          "var(--corErro2)";
      } else {
        
        document.getElementById("input-password").style.border = "none";
        document.getElementById("input-password").style.backgroundColor =
          "#fafafa";
      }
      
  
}
