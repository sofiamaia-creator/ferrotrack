document.getElementById("btnSalvar").addEventListener("click", function () {

    const nome = document.getElementById("nome").value;
    const email = document.getElementById("email").value;
    const telefone = document.getElementById("telefone").value;
    const usuario = document.getElementById("usuario").value;
    const senha = document.getElementById("senha").value;
    const cargo = document.getElementById("cargo").value;
    const status = document.getElementById("status").value;

    if (
        nome === "" ||
        email === "" ||
        telefone === "" ||
        usuario === "" ||
        senha === ""
    ) {
        alert("Preencha todos os campos!");
        return;
    }

    let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

    usuarios.push({
        nome: nome,
        email: email,
        telefone: telefone,
        usuario: usuario,
        cargo: cargo,
        status: status
    });

    localStorage.setItem("usuarios", JSON.stringify(usuarios));

    alert("Usuário salvo com sucesso!");

    window.location.href = "gerenciamentoUsuario.html";

});