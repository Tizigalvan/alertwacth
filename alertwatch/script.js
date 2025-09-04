document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("reminderForm");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const name = document.getElementById("medName").value;
            const time = document.getElementById("medTime").value;
            const freq = document.getElementById("medFreq").value;

            alert(`Guardado: ${name} a las ${time}, ${freq}`);
            window.location.href = "medicinas.php";
        });
    }
});
function connect() {
    alert("Intentando conectar al monitor cardíaco...");
    // Aquí podrías agregar lógica real para conexión si hay backend
}
