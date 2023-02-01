function searchFunction() {
    var input, filter, lista, produto, nome, i;
    input = document.getElementById('searchBar');
    filter = input.value.toUpperCase();
    lista = document.getElementById('main-content');
    produto = lista.getElementsByClassName('cliente-databox');

    for (i = 0; i < produto.length; i++) {
        nome = produto[i].getElementsByClassName('categoria-data-nome')[0];
        if (nome.innerHTML.toUpperCase().indexOf(filter) > -1) {
            produto[i].style.display = "";
        } else {
            produto[i].style.display = 'none';
        }
    }
}