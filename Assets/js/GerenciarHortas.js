
function toggleModal(id) {
    const m = document.getElementById(id);
    m.style.display = m.style.display === 'block' ? 'none' : 'block';
}
window.onclick = e => { if (e.target.classList.contains('modal')) e.target.style.display = 'none'; }

function adicionarCanteiroInputs(idHorta) {
    const container = document.getElementById('canteiros-container-' + idHorta);
    const novoGrupo = document.createElement('div');
    novoGrupo.className = 'canteiro-group mb-3';
    novoGrupo.innerHTML = `
            <div class="mb-3">
                <label>Cultura</label>
                <input type="text" name="cultura[]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Data de plantio</label>
                <input type="date" name="data_plantio[]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Data de colheita prevista</label>
                <input type="date" name="data_colheita[]" class="form-control" required>
            </div>
        `;
    container.appendChild(novoGrupo);
}
