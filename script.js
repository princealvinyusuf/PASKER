document.addEventListener('DOMContentLoaded', function() {
    const jobForm = document.getElementById('job-form');
    const jobsTableBody = document.querySelector('#jobs-table tbody');
    const cancelEditBtn = document.getElementById('cancel-edit');
    let editing = false;
    const bulkInput = document.getElementById('bulk-upload-input');
    const bulkBtn = document.getElementById('bulk-upload-btn');
    const bulkStatus = document.getElementById('bulk-upload-status');

    function fetchJobs() {
        let searchParam = '';
        const searchInput = document.getElementById('search-input');
        if (searchInput && searchInput.value.trim() !== '') {
            searchParam = '&search=' + encodeURIComponent(searchInput.value.trim());
        }
        const uidInput = document.getElementById('uid');
        if (uidInput && uidInput.value.trim() !== '') {
            searchParam += '&uid=' + encodeURIComponent(uidInput.value.trim());
        }
        fetch('jobs.php?' + searchParam.replace(/^&/, ''))
            .then(res => res.json())
            .then(data => {
                const jobs = Array.isArray(data) ? data : (data.jobs || []);
                jobsTableBody.innerHTML = '';
                // Dashboard card elements
                // const totalJobsEl = document.getElementById('total-jobs');
                // const openJobsEl = document.getElementById('open-jobs');
                // const closedJobsEl = document.getElementById('closed-jobs');
                // Count jobs
                let total = jobs.length;
                let open = 0;
                let closed = 0;
                const today = new Date();
                jobs.forEach(job => {
                    // Render table row
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <button class="btn btn-outline-primary btn-sm me-1" onclick='editJob(${JSON.stringify(job)})'><i class="bi bi-pencil"></i> Edit</button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteJob(${job.id})"><i class="bi bi-trash"></i> Delete</button>
                        </td>
                        <td>${job.uid || ''}</td>
                        <td>${job.title || ''}</td>
                        <td>${job.company_name || ''}</td>
                        <td>${job.location || ''}</td>
                        <td>${job.employment_type || ''}</td>
                        <td>${job.experience_level || ''}</td>
                        <td>${job.salary || ''}</td>
                        <td>${job.application_deadline || ''}</td>
                        <td>${job.created_at || ''}</td>
                    `;
                    jobsTableBody.appendChild(tr);
                    // Count open/closed
                    if (job.application_deadline) {
                        const deadline = new Date(job.application_deadline);
                        // Set time to end of day for deadline
                        deadline.setHours(23,59,59,999);
                        if (deadline >= today) {
                            open++;
                        } else {
                            closed++;
                        }
                    } else {
                        closed++;
                    }
                });
                // Update dashboard cards (REMOVED, now only fetchJobCounts does this)
                // if (totalJobsEl) totalJobsEl.textContent = total;
                // if (openJobsEl) openJobsEl.textContent = open;
                // if (closedJobsEl) closedJobsEl.textContent = closed;
            });
    }

    function fetchJobCounts() {
        fetch('jobs.php?counts=1')
            .then(res => res.json())
            .then(counts => {
                const totalJobsEl = document.getElementById('total-jobs');
                const openJobsEl = document.getElementById('open-jobs');
                const closedJobsEl = document.getElementById('closed-jobs');
                if (totalJobsEl) totalJobsEl.textContent = counts.total;
                if (openJobsEl) openJobsEl.textContent = counts.open;
                if (closedJobsEl) closedJobsEl.textContent = counts.closed;
            });
    }

    window.editJob = function(job) {
        Object.keys(job).forEach(key => {
            const el = document.getElementById(key);
            if (el) el.value = job[key] || '';
        });
        // Explicitly set the job-id hidden field
        document.getElementById('job-id').value = job.id;
        editing = true;
        cancelEditBtn.style.display = 'inline-block';
    };

    window.deleteJob = function(id) {
        if (confirm('Delete this job?')) {
            fetch('jobs.php?id=' + id, { method: 'DELETE' })
                .then(res => res.json())
                .then(() => fetchJobs());
        }
    };

    jobForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {};
        Array.from(jobForm.elements).forEach(el => {
            if (el.id && el.type !== 'submit' && el.type !== 'button') {
                data[el.id] = el.value;
            }
        });
        if (editing) {
            fetch('jobs.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(() => {
                editing = false;
                jobForm.reset();
                cancelEditBtn.style.display = 'none';
                fetchJobs();
            });
        } else {
            fetch('jobs.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(() => {
                jobForm.reset();
                fetchJobs();
            });
        }
    });

    cancelEditBtn.addEventListener('click', function() {
        editing = false;
        jobForm.reset();
        cancelEditBtn.style.display = 'none';
    });

    bulkBtn.addEventListener('click', function() {
        const file = bulkInput.files[0];
        if (!file) {
            bulkStatus.textContent = 'Please select an Excel (.xlsx or .csv) file.';
            bulkStatus.classList.remove('text-success');
            bulkStatus.classList.add('text-danger');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            let data = new Uint8Array(e.target.result);
            let workbook = XLSX.read(data, {type: 'array'});
            let sheet = workbook.Sheets[workbook.SheetNames[0]];
            let jobs = XLSX.utils.sheet_to_json(sheet);
            if (!Array.isArray(jobs) || jobs.length === 0) {
                bulkStatus.textContent = 'No jobs found in the file.';
                bulkStatus.classList.remove('text-success');
                bulkStatus.classList.add('text-danger');
                return;
            }
            // Send jobs array to backend
            fetch('jobs.php?bulk=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ jobs })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    bulkStatus.textContent = `Successfully uploaded ${result.count || jobs.length} jobs.`;
                    bulkStatus.classList.remove('text-danger');
                    bulkStatus.classList.add('text-success');
                    fetchJobs();
                } else {
                    bulkStatus.textContent = result.error || 'Bulk upload failed.';
                    bulkStatus.classList.remove('text-success');
                    bulkStatus.classList.add('text-danger');
                }
            })
            .catch(() => {
                bulkStatus.textContent = 'Bulk upload failed.';
                bulkStatus.classList.remove('text-success');
                bulkStatus.classList.add('text-danger');
            });
        };
        reader.readAsArrayBuffer(file);
    });

    // Export All Job Data
    const exportBtn = document.getElementById('export-all-jobs-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const exportStatus = document.getElementById('export-status');
            exportStatus.textContent = 'Preparing export...';
            if (typeof XLSX === 'undefined') {
                exportStatus.textContent = 'Export failed: XLSX library not loaded.';
                console.error('XLSX is not defined. Make sure xlsx.full.min.js is loaded before script.js');
                return;
            }
            fetch('jobs.php?export=1')
                .then(res => res.json())
                .then(jobs => {
                    if (!Array.isArray(jobs) || jobs.length === 0) {
                        exportStatus.textContent = 'No job data to export.';
                        return;
                    }
                    // Remove internal fields if needed
                    const jobsForExport = jobs.map(({id, ...rest}) => rest);
                    const ws = XLSX.utils.json_to_sheet(jobsForExport);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Jobs');
                    XLSX.writeFile(wb, 'all_jobs.xlsx');
                    exportStatus.textContent = 'Export successful!';
                })
                .catch((err) => {
                    exportStatus.textContent = 'Export failed.';
                    console.error('Export error:', err);
                });
        });
    }

    fetchJobCounts();
    if (!window.location.pathname.endsWith('index.html')) {
        fetchJobs();
    }

    // Inject shared navigation bar
    function loadNavbar(activePage) {
        fetch('navbar.html')
            .then(res => res.text())
            .then(html => {
                const navDiv = document.getElementById('navbar-placeholder');
                if (navDiv) {
                    navDiv.innerHTML = html;
                    // Set active class
                    if (activePage) {
                        const navId = {
                            'index.html': 'nav-dashboard',
                            'jobs.html': 'nav-jobs',
                            'cron_settings.php': 'nav-settings'
                        }[activePage];
                        if (navId) {
                            document.getElementById(navId)?.classList.add('active');
                            document.getElementById(navId)?.setAttribute('aria-current', 'page');
                        }
                    }
                }
            });
    }
}); 
