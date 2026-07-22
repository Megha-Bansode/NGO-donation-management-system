document.addEventListener('DOMContentLoaded', () => {
    /* ---------------------------------
       1. SIDEBAR & NAVIGATION LOGIC
    -----------------------------------*/
    const sidebar = document.getElementById('volunteer-sidebar');
    const toggleBtn = document.getElementById('mobile-sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close-btn');
    const overlay = document.getElementById('sidebar-overlay');
    
    function openSidebar() {
        if(sidebar) sidebar.classList.add('open');
        if(overlay) overlay.classList.add('active');
    }

    function closeSidebar() {
        if(sidebar) sidebar.classList.remove('open');
        if(overlay) overlay.classList.remove('active');
    }

    if(toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if(closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if(overlay) overlay.addEventListener('click', closeSidebar);


    /* ---------------------------------
       2. EVENT APPLICATION LOGIC
    -----------------------------------*/
    const eventFilterBtn = document.getElementById('filter-btn');
    if(eventFilterBtn) {
        const resetBtn = document.getElementById('reset-filter-btn');
        const searchInput = document.getElementById('event-search');
        const categorySelect = document.getElementById('event-category');
        const locationSelect = document.getElementById('event-location');
        const statusSelect = document.getElementById('event-status');
        const eventCards = document.querySelectorAll('.event-card');
        const emptyState = document.getElementById('empty-state');
        const eventsGrid = document.getElementById('events-grid');

        function filterEvents() {
            const searchTerm = searchInput.value.toLowerCase();
            const category = categorySelect.value;
            const location = locationSelect.value;
            const status = statusSelect.value;

            let visibleCount = 0;

            eventCards.forEach(card => {
                const title = card.querySelector('h3').innerText.toLowerCase();
                const desc = card.querySelector('.event-desc').innerText.toLowerCase();
                const matchesSearch = title.includes(searchTerm) || desc.includes(searchTerm);
                
                const matchesCategory = (category === 'all' || card.dataset.category === category);
                const matchesLocation = (location === 'all' || card.dataset.location === location);
                const matchesStatus = (status === 'all' || card.dataset.status === status);

                if (matchesSearch && matchesCategory && matchesLocation && matchesStatus) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if(eventsGrid && emptyState) {
                eventsGrid.style.display = visibleCount === 0 ? 'none' : 'grid';
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        eventFilterBtn.addEventListener('click', filterEvents);
        searchInput.addEventListener('keyup', filterEvents);
        categorySelect.addEventListener('change', filterEvents);
        locationSelect.addEventListener('change', filterEvents);
        statusSelect.addEventListener('change', filterEvents);

        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            categorySelect.value = 'all';
            locationSelect.value = 'all';
            const dateInput = document.getElementById('event-date');
            if(dateInput) dateInput.value = '';
            statusSelect.value = 'all';
            filterEvents();
        });
    }

    /* ---------------------------------
       3. ASSIGNED TASKS LOGIC
    -----------------------------------*/
    const taskFilterBtn = document.getElementById('task-filter-btn');
    if (taskFilterBtn) {
        const taskResetBtn = document.getElementById('task-reset-btn');
        const searchInput = document.getElementById('task-search');
        const statusSelect = document.getElementById('task-status');
        const prioritySelect = document.getElementById('task-priority');
        const sortSelect = document.getElementById('task-sort');
        const taskRows = Array.from(document.querySelectorAll('.task-row'));
        const tbody = document.getElementById('task-tbody');
        const emptyState = document.getElementById('task-empty-state');
        const table = document.getElementById('tasks-table');

        function filterAndSortTasks() {
            const searchTerm = searchInput.value.toLowerCase();
            const status = statusSelect.value;
            const priority = prioritySelect.value;
            const sort = sortSelect.value;
            let visibleRows = [];

            taskRows.forEach(row => {
                const taskName = row.querySelector('.task-name').innerText.toLowerCase();
                const eventName = row.children[2].innerText.toLowerCase();
                const matchesSearch = taskName.includes(searchTerm) || eventName.includes(searchTerm);
                const matchesStatus = (status === 'all' || row.dataset.status === status);
                const matchesPriority = (priority === 'all' || row.dataset.priority === priority);

                if (matchesSearch && matchesStatus && matchesPriority) {
                    row.style.display = 'table-row';
                    visibleRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });

            visibleRows.sort((a, b) => {
                if (sort === 'deadline') return new Date(a.dataset.deadline) - new Date(b.dataset.deadline);
                if (sort === 'priority') {
                    const p = { 'high': 1, 'medium': 2, 'low': 3 };
                    return p[a.dataset.priority] - p[b.dataset.priority];
                }
                if (sort === 'newest') return new Date(b.dataset.assigned) - new Date(a.dataset.assigned);
                if (sort === 'oldest') return new Date(a.dataset.assigned) - new Date(b.dataset.assigned);
                return 0;
            });

            visibleRows.forEach(row => tbody.appendChild(row));

            if(table && emptyState) {
                table.style.display = visibleRows.length === 0 ? 'none' : 'table';
                emptyState.style.display = visibleRows.length === 0 ? 'block' : 'none';
            }
        }

        taskFilterBtn.addEventListener('click', filterAndSortTasks);
        searchInput.addEventListener('keyup', filterAndSortTasks);
        statusSelect.addEventListener('change', filterAndSortTasks);
        prioritySelect.addEventListener('change', filterAndSortTasks);
        sortSelect.addEventListener('change', filterAndSortTasks);

        taskResetBtn.addEventListener('click', () => {
            searchInput.value = '';
            statusSelect.value = 'all';
            prioritySelect.value = 'all';
            sortSelect.value = 'deadline';
            filterAndSortTasks();
        });
    }

    /* ---------------------------------
       4. WORK STATUS LOGIC
    -----------------------------------*/
    const workStatusForm = document.getElementById('work-status-form');
    if (workStatusForm) {
        const progressSlider = document.getElementById('progress-slider');
        const progressValDisplay = document.getElementById('progress-val-display');
        const progressCircle = document.getElementById('progress-circle');
        const progressInnerVal = document.getElementById('progress-inner-val');
        const successAlert = document.getElementById('status-success-alert');
        const closeAlertBtn = document.querySelector('.close-alert');
        const cancelBtn = document.getElementById('cancel-btn');

        function updateProgress() {
            const val = progressSlider.value;
            progressValDisplay.innerText = val + '%';
            progressInnerVal.innerText = val + '%';
            progressCircle.style.background = `conic-gradient(var(--primary-color) ${val * 3.6}deg, #E2E8F0 0deg)`;
        }

        progressSlider.addEventListener('input', updateProgress);
        updateProgress();

        workStatusForm.addEventListener('submit', (e) => {
            e.preventDefault();
            successAlert.style.display = 'flex';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        if (closeAlertBtn) {
            closeAlertBtn.addEventListener('click', () => successAlert.style.display = 'none');
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                workStatusForm.reset();
                updateProgress();
                successAlert.style.display = 'none';
                window.location.href = 'tasks.php';
            });
        }
    }

    /* ---------------------------------
       5. ATTENDANCE LOGIC
    -----------------------------------*/
    const attFilterBtn = document.getElementById('att-filter-btn');
    if (attFilterBtn) {
        const attResetBtn = document.getElementById('att-reset-btn');
        const searchInput = document.getElementById('att-search');
        const statusSelect = document.getElementById('att-status');
        const monthSelect = document.getElementById('att-month');
        const yearSelect = document.getElementById('att-year');
        const attRows = Array.from(document.querySelectorAll('.att-row'));
        const tbody = document.getElementById('att-tbody');
        const emptyState = document.getElementById('att-empty-state');
        const table = document.getElementById('att-table');
        const progressCircle = document.getElementById('att-progress-circle');

        if (progressCircle) {
            setTimeout(() => {
                progressCircle.style.background = 'conic-gradient(var(--success-color, #10B981) 331.2deg, #E2E8F0 0deg)';
            }, 500);
        }

        function filterAttendance() {
            const searchTerm = searchInput.value.toLowerCase();
            const status = statusSelect.value;
            const month = monthSelect.value;
            const year = yearSelect.value;
            let visibleCount = 0;

            attRows.forEach(row => {
                const eventName = row.querySelector('.att-event-name').innerText.toLowerCase();
                const matchesSearch = eventName.includes(searchTerm);
                const matchesStatus = (status === 'all' || row.dataset.status === status);
                const matchesMonth = (month === 'all' || row.dataset.month === month);
                const matchesYear = (year === 'all' || row.dataset.year === year);

                if (matchesSearch && matchesStatus && matchesMonth && matchesYear) {
                    row.style.display = 'table-row';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if(table && emptyState) {
                table.style.display = visibleCount === 0 ? 'none' : 'table';
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        attFilterBtn.addEventListener('click', filterAttendance);
        searchInput.addEventListener('keyup', filterAttendance);
        statusSelect.addEventListener('change', filterAttendance);
        monthSelect.addEventListener('change', filterAttendance);
        yearSelect.addEventListener('change', filterAttendance);

        attResetBtn.addEventListener('click', () => {
            searchInput.value = '';
            statusSelect.value = 'all';
            monthSelect.value = 'all';
            yearSelect.value = 'all';
            filterAttendance();
        });
    }

    /* ---------------------------------
       6. BACKEND API INTEGRATION
    -----------------------------------*/
    const API_BASE = '/NGO-donation-management-system/api/';

    // Dashboard Data Fetching
    if(document.getElementById('kpi-tasks')) {
        fetch(API_BASE + 'volunteer_dashboard')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('kpi-tasks').innerText = data.data.tasks_completed;
                    document.getElementById('kpi-events').innerText = data.data.active_campaigns;
                    document.getElementById('kpi-hours').innerText = data.data.hours_contributed;
                    document.getElementById('kpi-completed').innerText = parseInt(data.data.tasks_completed) + parseInt(data.data.events_attended);
                }
            });
    }

    // Dashboard Events Fetching
    if(document.getElementById('dashboard-events-tbody')) {
        fetch(API_BASE + 'volunteer_events')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const tbody = document.getElementById('dashboard-events-tbody');
                    tbody.innerHTML = '';
                    data.data.slice(0, 5).forEach(event => {
                        let statusClass = event.status === 'upcoming' ? 'badge-upcoming' : (event.status === 'ongoing' ? 'badge-ongoing' : 'badge-completed');
                        tbody.innerHTML += `
                            <tr>
                                <td>${event.title}</td>
                                <td>${event.event_date}</td>
                                <td>${event.location}</td>
                                <td><span class="badge ${statusClass}" style="text-transform:capitalize;">${event.status}</span></td>
                            </tr>
                        `;
                    });
                }
            });
    }

    // Dashboard Tasks Fetching
    if(document.getElementById('dashboard-tasks-tbody')) {
        fetch(API_BASE + 'volunteer_tasks')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const tbody = document.getElementById('dashboard-tasks-tbody');
                    tbody.innerHTML = '';
                    data.data.slice(0, 5).forEach(task => {
                        let priorityClass = task.priority === 'high' ? 'badge-high' : (task.priority === 'medium' ? 'badge-medium' : 'badge-low');
                        let statusClass = task.status === 'pending' ? 'badge-pending' : (task.status === 'in_progress' ? 'badge-progress' : 'badge-completed');
                        tbody.innerHTML += `
                            <tr>
                                <td>${task.title}</td>
                                <td>${task.deadline.split(' ')[0]}</td>
                                <td><span class="badge ${priorityClass}" style="text-transform:capitalize;">${task.priority}</span></td>
                                <td><span class="badge ${statusClass}" style="text-transform:capitalize;">${task.status.replace('_', ' ')}</span></td>
                            </tr>
                        `;
                    });
                }
            });
    }

    // Registration Form Fetching
    const registerForm = document.getElementById('volunteer-register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            
            // Build JSON payload manually from FormData
            const payload = {};
            formData.forEach((value, key) => {
                if(key === 'skills[]') {
                    if(!payload.skills) payload.skills = [];
                    payload.skills.push(value);
                } else {
                    payload[key] = value;
                }
            });

            fetch(API_BASE + 'volunteer_register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('registration-success').style.display = 'block';
                    document.getElementById('registration-error').style.display = 'none';
                    registerForm.reset();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    document.getElementById('registration-error-text').innerText = data.message || 'An error occurred.';
                    document.getElementById('registration-error').style.display = 'block';
                    document.getElementById('registration-success').style.display = 'none';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('registration-error-text').innerText = 'Network error occurred.';
                document.getElementById('registration-error').style.display = 'block';
                document.getElementById('registration-success').style.display = 'none';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }
});
