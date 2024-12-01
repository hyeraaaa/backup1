document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success' || urlParams.get('status') === 'update') {
        const isUpdate = urlParams.get('status') === 'update';

        const successMessage = isUpdate ? 'Announcement updated successfully!' : 'Announcement posted successfully!';

        const modalHtml = `
    <div class="modal success-modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center">
                <div class="modal-body p-4">
                    <div class="mb-2">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="modal-title mb-1" id="successModalLabel">Success</h6>
                    <p class="small mb-3">${successMessage}</p>
                    <button type="button" class="btn btn-success btn-sm w-100" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();

        setTimeout(() => {
            successModal.hide();
        }, 2000);

        window.history.replaceState({}, document.title, window.location.pathname);
    }
});