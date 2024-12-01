<!-- Tags Modal -->
<div class="modal fade" id="tagsModal" tabindex="-1" aria-labelledby="tagsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="tagsModalLabel">
                    <i class="bi bi-tags-fill me-2"></i>Tags
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Year Levels -->
                <div class="filter-section">
                    <h6 class="filter-title">
                        <i class="bi bi-mortarboard me-2"></i>Year Levels
                    </h6>

                    <label class="filter-chip mb-2">
                        <input type="checkbox" onclick="toggleSectionCheckboxes(this, 'year_level')">
                        <span>Select All</span>
                    </label>
                    <div class="filter-options">
                        <label class="filter-chip">
                            <input type="checkbox" name="year_level[]" value="1st Year">
                            <span>1st Year</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="year_level[]" value="2nd Year">
                            <span>2nd Year</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="year_level[]" value="3rd Year">
                            <span>3rd Year</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="year_level[]" value="4th Year">
                            <span>4th Year</span>
                        </label>
                    </div>
                </div>

                <!-- Departments -->
                <div class="filter-section">
                    <h6 class="filter-title">
                        <i class="bi bi-building mt-0 me-2"></i>Departments
                    </h6>
                    <label class="filter-chip mb-2">
                        <input type="checkbox" onclick="toggleSectionCheckboxes(this, 'department')">
                        <span>Select All</span>
                    </label>
                    <div class="filter-options">
                        <label class="filter-chip">
                            <input type="checkbox" name="department[]" value="CICS">
                            <span>CICS</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="department[]" value="CABE">
                            <span>CABE</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="department[]" value="CAS">
                            <span>CAS</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="department[]" value="CIT">
                            <span>CIT</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="department[]" value="CTE">
                            <span>CTE</span>
                        </label>
                        <label class="filter-chip">
                            <input type="checkbox" name="department[]" value="CE">
                            <span>CE</span>
                        </label>
                    </div>
                </div>

                <!-- Courses -->
                <div class="filter-section">
                    <h6 class="filter-title">
                        <i class="bi bi-book me-2"></i>Courses
                    </h6>
                    <label class="filter-chip mb-2">
                        <input type="checkbox" onclick="toggleSectionCheckboxes(this, 'course')">
                        <span>Select All</span>
                    </label>
                    <div class="filter-options">
                        <label class="filter-chip" title="Bachelor of Science in Business Accounting">
                            <input type="checkbox" name="course[]" value="BSBA">
                            <span>BSBA</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Science in Management Accounting">
                            <input type="checkbox" name="course[]" value="BSMA">
                            <span>BSMA</span>
                        </label>
                        <!-- Add all other courses following the same pattern -->
                        <label class="filter-chip" title="Bachelor of Science in Psychology">
                            <input type="checkbox" name="course[]" value="BSP">
                            <span>BSP</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Arts in Communication">
                            <input type="checkbox" name="course[]" value="BAC">
                            <span>BAC</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Science in Industrial Engineering">
                            <input type="checkbox" name="course[]" value="BSIE">
                            <span>BSIE</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Industrial Technology - Computer Technology">
                            <input type="checkbox" name="course[]" value="BSIT-CE">
                            <span>BSIT-CE</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Industrial Technology - Electrical Technology">
                            <input type="checkbox" name="course[]" value="BSIT-Electrical">
                            <span>BSIT-E</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Industrial Technology - Electronics Technology">
                            <input type="checkbox" name="course[]" value="BSIT-Electronic">
                            <span>BSIT-ET</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Industrial Technology - ICT">
                            <input type="checkbox" name="course[]" value="BSIT-ICT">
                            <span>BSIT-ICT</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Science in Information Technology">
                            <input type="checkbox" name="course[]" value="BSIT">
                            <span>BSIT</span>
                        </label>
                        <label class="filter-chip" title="Bachelor of Secondary Education">
                            <input type="checkbox" name="course[]" value="BSE">
                            <span>BSE</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSectionCheckboxes(selectAllCheckbox, section) {
        const checkboxes = document.querySelectorAll(`input[name="${section}[]"]`);
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
</script>