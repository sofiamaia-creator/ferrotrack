
const tabela = document.getElementById("tabelaUsuarios");

let usuariosSalvos = JSON.parse(localStorage.getItem("usuarios")) || [];

usuariosSalvos.forEach(usuario => {

    tabela.innerHTML += `
        <tr class="linha-usuario">

            <td class="usuario">
                <div>
                    <strong>${usuario.nome}</strong>
                    <small>${usuario.email}</small>
                </div>
            </td>

            <td>
                <span class="cargo">${usuario.cargo}</span>
            </td>

            <td>
                <span class="${usuario.status.toLowerCase()}">
                    ${usuario.status}
                </span>
            </td>

            <td>
                <select class="permissao-select">
                    <option>Visualizar Relatórios</option>
                </select>
            </td>

        </tr>
    `;

});



const btnBuscar = document.getElementById("btnBuscar");
const campoBusca = document.getElementById("buscarUsuario");

btnBuscar.addEventListener("click", function () {

    const pesquisa = campoBusca.value.toLowerCase().trim();

    const usuarios = document.querySelectorAll(".linha-usuario");

    usuarios.forEach(function (usuario) {

        const nome = usuario
            .querySelector("strong")
            .textContent
            .toLowerCase();

        if (nome.includes(pesquisa) || pesquisa === "") {
            usuario.style.display = "";
        } else {
            usuario.style.display = "none";
        }

    });

});



const btnBloquear = document.getElementById("btnBloquear");
const campoBloqueio = document.getElementById("nomeBloquear");

btnBloquear.addEventListener("click", function () {

    const pesquisa = campoBloqueio.value.toLowerCase().trim();

    if (pesquisa === "") {
        alert("Digite o nome do usuário.");
        return;
    }

    let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

    let encontrado = false;

    usuarios.forEach(function (usuario) {

        if (usuario.nome.toLowerCase().includes(pesquisa)) {

            usuario.status = "Bloqueado";
            encontrado = true;

        }

    });

    localStorage.setItem("usuarios", JSON.stringify(usuarios));

    if (encontrado) {
        alert("Usuário bloqueado com sucesso!");
        location.reload();
    } else {
        alert("Usuário não encontrado!");
    }

});