const btnMenu = document.getElementById("btnMenu");
const menu = document.getElementById("menu");
const conteudo = document.querySelector(".conteudo");

btnMenu.addEventListener("click", function () {
    menu.classList.toggle("ativo");
    conteudo.classList.toggle("deslocado");
});