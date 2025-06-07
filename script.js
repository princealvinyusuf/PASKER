document.addEventListener('DOMContentLoaded', function() {
    const jobForm = document.getElementById('job-form');
    const jobsTableBody = document.querySelector('#jobs-table tbody');

    function fetchJobs() {
        fetch('jobs.php')
            .then(res => res.json())
            .then(jobs => {
                jobsTableBody.innerHTML = '';
                jobs.forEach(job => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${job.title || ''}</td>
                        <td>${job.company_name || ''}</td>
                        <td>${job.location || ''}</td>
                        <td>${job.employment_type || ''}</td>
                        <td>${job.experience_level || ''}</td>
                        <td>${job.salary || ''}</td>
                        <td>${job.application_deadline || ''}</td>
                        <td>${job.created_at || ''}</td>
                        <td>
                            <button class="action-btn" onclick="deleteJob(${job.id})">Delete</button>
                        </td>
                    `;
                    jobsTableBody.appendChild(tr);
                });
            });
    }

    window.deleteJob = function(id) {
        if (confirm('Delete this job?')) {
            fetch('jobs.php?id=' + id, { method: 'DELETE' })
                .then(res => res.json())
                .then(() => fetchJobs());
        }
    };

    // Add job (main form)
    jobForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {};
        Array.from(jobForm.elements).forEach(el => {
            if (el.id && el.type !== 'submit' && el.type !== 'button') {
                data[el.id] = el.value;
            }
        });
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
    });

    // Chevron icon toggle for more fields
    var moreFields = document.getElementById('moreFields');
    var chevronIcon = document.getElementById('chevron-icon');
    if (moreFields && chevronIcon) {
        moreFields.addEventListener('show.bs.collapse', function () {
            chevronIcon.classList.remove('bi-chevron-down');
            chevronIcon.classList.add('bi-chevron-up');
        });
        moreFields.addEventListener('hide.bs.collapse', function () {
            chevronIcon.classList.remove('bi-chevron-up');
            chevronIcon.classList.add('bi-chevron-down');
        });
    }

    fetchJobs();
}); 