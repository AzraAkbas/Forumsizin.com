
    let aktifFotografIdForReport = null;

    const reportModalEl = document.getElementById('reportModal');
    reportModalEl.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        aktifFotografIdForReport = button ? button.getAttribute('data-fotograf-id') : null;

        const radios = reportModalEl.querySelectorAll('input[name="reportReason"]');
        radios.forEach(radio => radio.checked = false);
    });

    document.getElementById("submitReport").addEventListener("click", function () {
        const selectedReason = document.querySelector('input[name="reportReason"]:checked');
        if (!selectedReason) {
            alert("Lütfen bir neden seçin.");
            return;
        }

        if (!aktifFotografIdForReport) {
            alert("Bildirmek istediğiniz fotoğraf seçilmedi.");
            return;
        }

        fetch("../islemler php/bildir.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `reason=${encodeURIComponent(selectedReason.value)}&fotograf_id=${encodeURIComponent(aktifFotografIdForReport)}`
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success' || data.success) {
                    const modalInstance = bootstrap.Modal.getInstance(reportModalEl);
                    modalInstance.hide();
                }
            })
    });