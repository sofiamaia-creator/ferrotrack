// CARREGA OS SENSORES SALVOS

let sensores = JSON.parse(localStorage.getItem("sensores")) || [];

const selectSensor = document.getElementById("sensor");

sensores.forEach(sensor => {

    let opcao = document.createElement("option");

    opcao.value = sensor.sensor;
    opcao.textContent = sensor.sensor;

    selectSensor.appendChild(opcao);

});


function adicionarSensor() {

    let valor = document.getElementById("novoSensor").value.trim();

    if(valor === ""){
        alert("Digite o nome do sensor!");
        return;
    }

    let opcao = document.createElement("option");

    opcao.value = valor;
    opcao.textContent = valor;

    document.getElementById("sensor").appendChild(opcao);

    document.getElementById("novoSensor").value = "";
}


function adicionarTipo() {

    let valor = document.getElementById("novoTipo").value.trim();

    if(valor === ""){
        alert("Digite o tipo!");
        return;
    }

    let opcao = document.createElement("option");

    opcao.value = valor;
    opcao.textContent = valor;

    document.getElementById("tipo").appendChild(opcao);

    document.getElementById("novoTipo").value = "";
}


function adicionarLocal() {

    let valor = document.getElementById("novoLocal").value.trim();

    if(valor === ""){
        alert("Digite o local!");
        return;
    }

    let opcao = document.createElement("option");

    opcao.value = valor;
    opcao.textContent = valor;

    document.getElementById("local").appendChild(opcao);

    document.getElementById("novoLocal").value = "";
}



document.getElementById("btnSalvar").addEventListener("click", function() {

    const sensor = document.getElementById("sensor").value;
    const tipo = document.getElementById("tipo").value;
    const local = document.getElementById("local").value;
    const data = document.getElementById("data").value;
    const descricao = document.getElementById("descricao").value;

    if(sensor === "" || tipo === "" || local === ""){

        alert("Preencha todos os campos obrigatórios!");
        return;
    }

    let sensores = JSON.parse(localStorage.getItem("sensores")) || [];

    sensores.push({
        sensor: sensor,
        tipo: tipo,
        local: local,
        data: data,
        descricao: descricao
    });

    localStorage.setItem("sensores", JSON.stringify(sensores));

    alert("Sensor salvo com sucesso!");

});