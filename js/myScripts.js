// Verificar se o tema está salvo no localStorage
if (localStorage.getItem('theme')) {
    document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme'))
    changeThemeIcon()
} else {
    localStorage.setItem('theme', 'light')
    changeThemeIcon()
}

// Adicionar evento de clique no botão de troca de tema
document.getElementById('theme-toggler').addEventListener('click', () => {
    document.documentElement.setAttribute('data-bs-theme', document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark')
    localStorage.setItem('theme', document.documentElement.getAttribute('data-bs-theme'))
    changeThemeIcon()
})

// Função para trocar o ícone do botão de troca de tema
function changeThemeIcon() {
    document.getElementById('theme-toggler').innerHTML = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>'
}

function generatePDF() {
    const content = document.querySelector('#content')

    const options = {
        margin: 0,
        filename: 'relatorio.pdf',
        image: { type: 'png', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0},
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }

    html2pdf().set(options).from(content).save()
}

document.getElementById('add-atividade').addEventListener('click', function() {
    var container = document.getElementById('atividades-container');
    var index = container.children.length + 1;

    if (index > 10) {
        alert('Você só pode adicionar até 10 atividades.');
        return;
    }

    var newRow = document.createElement('div');
    newRow.className = 'row mb-2';
    newRow.innerHTML = `
        <div class="col-6">
            <textarea class="form-control" name="atividade${index}" placeholder="Atividade ${index}" rows="3" maxlength="1023"></textarea>
        </div>
        <div class="col-6">
            <textarea class="form-control" name="comentario${index}" placeholder="Comentário" rows="3" maxlength="1023"></textarea>
        </div>
    `;
    container.appendChild(newRow);
});

document.getElementById('add-atividade-edit').addEventListener('click', function() {

    var container = document.getElementById('atividades-container-edit');
    var index = container.children.length + 1;

    if (index > 10) {
        alert('Você só pode adicionar até 10 atividades.');
        return;
    }

    var newRow = document.createElement('div');
    newRow.className = 'row mb-2';
    newRow.innerHTML = `
        <div class="col-6">
            <textarea class="form-control" name="atividade${index}_edit" placeholder="Atividade ${index}" rows="3" maxlength="1023"></textarea>
        </div>
        <div class="col-6">
            <textarea class="form-control" name="comentario${index}_edit" placeholder="Comentário" rows="3" maxlength="1023"></textarea>
        </div>
    `;
    container.appendChild(newRow);
});

document.getElementById('add-atividade-final').addEventListener('click', function() {
    var container = document.getElementById('atividades-container-final');
    var index = container.children.length + 1;

    if (index > 10) {
        alert('Você só pode adicionar até 10 atividades.');
        return;
    }

    var newRow = document.createElement('div');
    newRow.className = 'row mb-2';
    newRow.innerHTML = `
        <div class="col-4">
            <textarea class="form-control" name="atividade${index}_final" placeholder="Atividade ${index}" rows="3" maxlength="1023"></textarea>
        </div>
        <div class="col-4">
            <textarea class="form-control" name="resumo${index}_final" placeholder="Resumo da Atividade ${index}" rows="3" maxlength="1023"></textarea>
        </div>
        <div class="col-4">
            <textarea class="form-control" name="disciplina${index}_final" placeholder="Disciplina Relacionada ${index}" rows="3" maxlength="1023"></textarea>
        </div>
    `;
    container.appendChild(newRow);
});

document.getElementById('add-atividade-final-edit').addEventListener('click', function() {
    var container = document.getElementById('atividades-container-final-edit');
    var index = container.children.length + 1;

    if (index > 10) {
        alert('Você só pode adicionar até 10 atividades.');
        return;
    }

    var newRow = document.createElement('div');
    newRow.className = 'row mb-2';
    newRow.innerHTML = `
        <div class="col-4">
            <textarea class="form-control" name="atividade${index}_final_edit" placeholder="Atividade ${index}" rows="3" maxlength="1023"></textarea>
        </div>
        <div class="col-4">
            <textarea class="form-control" name="resumo${index}_final_edit" placeholder="Resumo da Atividade ${index}" rows="3" maxlength="1023"></textarea>
        </div>
        <div class="col-4">
            <textarea class="form-control" name="disciplina${index}_final_edit" placeholder="Disciplina Relacionada ${index}" rows="3" maxlength="1023"></textarea>
        </div>
    `;
    container.appendChild(newRow);
});