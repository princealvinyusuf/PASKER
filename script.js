document.addEventListener('DOMContentLoaded', function() {
    const jobForm = document.getElementById('job-form');
    const jobsTableBody = document.querySelector('#jobs-table tbody');
    const cancelEditBtn = document.getElementById('cancel-edit');
    let editing = false;

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
                            <button class="action-btn" onclick='editJob(${JSON.stringify(job)})'>Edit</button>
                            <button class="action-btn" onclick="deleteJob(${job.id})">Delete</button>
                        </td>
                    `;
                    jobsTableBody.appendChild(tr);
                });
            });
    }

    window.editJob = function(job) {
        Object.keys(job).forEach(key => {
            const el = document.getElementById(key);
            if (el) el.value = job[key] || '';
        });
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

    fetchJobs();
}); 