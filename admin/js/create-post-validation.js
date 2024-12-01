document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');

    const isUpdate = form.dataset.action === 'update';

    if (isUpdate) {
        submitBtn.setAttribute('data-action', 'update');
    } else {
        submitBtn.setAttribute('data-action', 'post');
    }

    function showErrorModal(message) {
        const modalHtml = `
        <div class="modal error-modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content text-center">
                    <div class="modal-body p-4">
                        <div class="mb-2">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="modal-title mb-1" id="errorModalLabel">Error</h6>
                        <p class="small mb-3">${message}</p>
                        <button type="button" class="btn btn-danger btn-sm w-100" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>`;
        const existingModal = document.getElementById('errorModal');
        if (existingModal) {
            existingModal.remove();
        }

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    }

    function showSuccessModal(isUpdate) {
        const redirectUrl = isUpdate ? '../admin.php?status=update' : '../admin.php?status=success';
        window.location.href = redirectUrl;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const title = document.getElementById('title').value.trim().toLowerCase();
        const description = document.getElementById('description').value.trim().toLowerCase();
        const imageInput = document.getElementById('image');
        const tagsSelected = validateTags();

        // Validate title
        if (!title) {
            showErrorModal('Please enter a title for the announcement');
            return;
        }

        // Validate description
        if (!description) {
            showErrorModal('Please enter a description for the announcement');
            return;
        }

        // Check for bad words in title and description
        if (containsBadWords(title) || containsBadWords(description)) {
            showErrorModal('Please remove inappropriate words from the title or description');
            return;
        }

        // Validate image only if one is selected
        if (imageInput.files && imageInput.files[0]) {
            const file = imageInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showErrorModal('Please select a valid image file (JPG, PNG, or GIF)');
                return;
            }

            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                showErrorModal('Image size should not exceed 5MB');
                return;
            }
        }

        // Validate tags
        if (!tagsSelected) {
            showErrorModal('Please select at least one tag for the announcement');
            return;
        }

        showLoadingState();
        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const isUpdate = this.dataset.action === 'update';
                showSuccessModal(isUpdate);
            } else {
                showErrorModal('An error occurred while posting the announcement');
            }
        } catch (error) {
            showErrorModal('An error occurred while posting the announcement');
        }
    });

    function validateTags() {
        const yearLevels = document.querySelectorAll('input[name="year_level[]"]:checked');
        const departments = document.querySelectorAll('input[name="department[]"]:checked');
        const courses = document.querySelectorAll('input[name="course[]"]:checked');

        return yearLevels.length > 0 || departments.length > 0 || courses.length > 0;
    }

    function showLoadingState() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Posting...';
    }

    function containsBadWords(input) {
        // Convert input to lowercase
        input = input.toLowerCase();
    
        // Common variations and leetspeak replacements
        input = input
            .replace(/0/g, 'o')
            .replace(/1/g, 'i')
            .replace(/3/g, 'e')
            .replace(/4/g, 'a')
            .replace(/5/g, 's')
            .replace(/7/g, 't')
            .replace(/@/g, 'a')
            .replace(/\$/g, 's')
            .replace(/x/g, 'ks');
    
        const inappropriatePatterns = [
            // English patterns
            /\b(sex|sexy)\b/i,
            /\b(fuck|f+u+c+k+|fck|f\*+)\b/i,
            /\b(shit|sh\*t|sh1t)\b/i,
            /\b(pussy|puss)\b/i,
            /\b(dick|d1ck|d\*ck)\b/i,
            /\b(ass|@ss)\b/i,
            /\b(bitch|b\*tch)\b/i,
            /\b(porn|p0rn)\b/i,
            /\b(nude|nudes)\b/i,
            /\b(tite|tight)\b/i,
            /\b(puke|vomit)\b/i,
            /\b(bastard|b@stard)\b/i,
            /\b(slut|sl\*t)\b/i,
    
            // Filipino patterns
            /\b(kantot|kantut|k@ntot|k@ntut|kantutan)\b/i,
            /\b(putang\s*ina|tang\s*ina|tangina|putangina|pt|tng\s*ina)\b/i,
            /\b(hindot|hindut|hindutan)\b/i,
            /\b(pakantot|pakantut)\b/i,
            /\b(patayin|papatayin|mamatay)\b/i,
            /\b(hayop|hayup|hayupak)\b/i,
            /\b(gago|g@go|g@g0|bobo|b0b0|tanga|tang@)\b/i,
            /\b(puta|pota|p0ta|pokpok)\b/i,
            /\b(kupal|kopal|kupal|tamod|bayag)\b/i,
            /\b(tite|titi|oten|ratbu)\b/i,
            /\b(burat|burnik|puke|puki)\b/i,
            /\b(ulol|olol|ul0l|0l0l)\b/i,
            /\b(leche|letse|l3ch3)\b/i,
            /\b(inamo|inamo|inam0)\b/i,
    
            // Common variations and combinations
            /\b(p[u\*]t[a\*]\s*[i\*][n\*][a\*])\b/i,
            /([\.\_\-\*\@\#\$]{2,})/
        ];
    
        // Check for inappropriate patterns
        for (const pattern of inappropriatePatterns) {
            if (pattern.test(input)) {
                return true;
            }
        }
    
        // Check for repeated characters (potential obfuscation)
        if (/(.)\1{4,}/.test(input)) {
            return true;
        }
    
        return false;
    }
});
