function generatePDF() {
    const content = document.querySelector('#content');

    const options = {
        margin: 0,
        filename: 'relatorio.pdf',
        image: { type: 'png', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0},
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(options).from(content).save();
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
            <textarea class="form-control" name="atividade${index}" placeholder="Atividade ${index}" rows="3" maxlength="1023" required></textarea>
        </div>
        <div class="col-6">
            <textarea class="form-control" name="comentario${index}" placeholder="Comentário" rows="3" maxlength="1023" required></textarea>
        </div>
    `;
    container.appendChild(newRow);
});

