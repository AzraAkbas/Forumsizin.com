
    const yorumReportModal = new bootstrap.Modal(document.getElementById('yorumReportModal'));
    let currentYorumId = null;

    document.body.addEventListener('click', (e) => {
        if (e.target.classList.contains('yorum-bildir-btn')) {
            currentYorumId = e.target.getAttribute('data-yorum-id');
            document.getElementById('reportYorumId').value = currentYorumId;
            yorumReportModal.show();
        }
    });
document.getElementById('submitYorumReport').addEventListener('click', () => {
    const form = document.getElementById('yorumReportForm');
    const formData = new FormData(form);
    formData.append('yorum_id', currentYorumId);

    if (!formData.get('reportReason')) {
        alert('Lütfen bir neden seçin.');
        return;
    }

    fetch('../islemler php/yorum_bildir.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            form.reset();              
            yorumReportModal.hide();
        }
    })
    .catch(err => {
        alert('Bir hata oluştu.');
        console.error(err);
    });
});