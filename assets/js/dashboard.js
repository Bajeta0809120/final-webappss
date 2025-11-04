// dashboard.js - comprehensive dashboard functionality with all system data
(function() {
    'use strict';

    // Initialize all dashboard elements
    const totalStudentsEl = document.getElementById('totalStudents');
    const totalProgramsEl = document.getElementById('totalPrograms');
    const totalSectionsEl = document.getElementById('totalSections');
    const todayAttendanceEl = document.getElementById('todayAttendance');
    const totalAbsencesEl = document.getElementById('totalAbsences');

    // Table elements
    const programsTableBody = document.getElementById('programsTableBody');
    const sectionsTableBody = document.getElementById('sectionsTableBody');
    const studentsTableBody = document.getElementById('studentsTableBody');
    const attendanceTableBody = document.getElementById('attendanceTableBody');

    // Set loading states
    const setLoadingStates = () => {
        if (totalStudentsEl) totalStudentsEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        if (totalProgramsEl) totalProgramsEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        if (totalSectionsEl) totalSectionsEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        if (todayAttendanceEl) todayAttendanceEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        if (totalAbsencesEl) totalAbsencesEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
    };

    setLoadingStates();

    // Fetch and display attendance summary
    const loadAttendanceSummary = () => {
        fetch('api/attendance_summary.php')
            .then(resp => resp.json())
            .then(data => {
                if (data && data.success && data.data) {
                    const d = data.data;
                    if (totalStudentsEl) totalStudentsEl.innerHTML = `<i class="bi bi-people-fill"></i> ${d.total_students}`;
                    if (totalProgramsEl) totalProgramsEl.innerHTML = `<i class="bi bi-building"></i> ${d.total_programs}`;
                    if (totalSectionsEl) totalSectionsEl.innerHTML = `<i class="bi bi-diagram-2"></i> ${d.total_sections}`;
                    if (todayAttendanceEl) todayAttendanceEl.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${d.todays_present} present`;
                    if (totalAbsencesEl) totalAbsencesEl.innerHTML = `<i class="bi bi-x-circle-fill"></i> ${d.total_absences} total`;
                } else {
                    if (totalStudentsEl) totalStudentsEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> N/A';
                    if (totalProgramsEl) totalProgramsEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> N/A';
                    if (totalSectionsEl) totalSectionsEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> N/A';
                    if (todayAttendanceEl) totalAttendanceEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> N/A';
                    if (totalAbsencesEl) totalAbsencesEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> N/A';
                }
            })
            .catch(() => {
                if (totalStudentsEl) totalStudentsEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error';
                if (totalProgramsEl) totalProgramsEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error';
                if (totalSectionsEl) totalSectionsEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error';
                if (todayAttendanceEl) todayAttendanceEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error';
                if (totalAbsencesEl) totalAbsencesEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error';
            });
    };

    // Load programs data
    const loadPrograms = () => {
        fetch('api/read.php?type=programs')
            .then(resp => resp.json())
            .then(data => {
                if (data && data.success && data.data) {
                    const programs = data.data;

                    if (programsTableBody) {
                        programsTableBody.innerHTML = programs.map(program => `
                            <tr>
                                <td>${program.id}</td>
                                <td>${program.name}</td>
                                <td>${program.sections_count || 0}</td>
                                <td>${program.students_count || 0}</td>
                            </tr>
                        `).join('');
                    }
                } else {
                    if (programsTableBody) programsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No programs found</td></tr>';
                }
            })
            .catch(() => {
                if (programsTableBody) programsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Error loading programs</td></tr>';
            });
    };

    // Load sections data
    const loadSections = () => {
        fetch('api/read.php?type=sections')
            .then(resp => resp.json())
            .then(data => {
                if (data && data.success && data.data) {
                    const sections = data.data;

                    if (sectionsTableBody) {
                        sectionsTableBody.innerHTML = sections.map(section => `
                            <tr>
                                <td>${section.id}</td>
                                <td>${section.name}</td>
                                <td>${section.program_name || 'N/A'}</td>
                                <td>${section.students_count || 0}</td>
                            </tr>
                        `).join('');
                    }
                } else {
                    if (sectionsTableBody) sectionsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No sections found</td></tr>';
                }
            })
            .catch(() => {
                if (sectionsTableBody) sectionsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Error loading sections</td></tr>';
            });
    };

    // Load students data
    const loadStudents = () => {
        fetch('api/read.php?type=students')
            .then(resp => resp.json())
            .then(data => {
                if (data && data.success && data.data) {
                    const students = data.data;

                    if (studentsTableBody) {
                        studentsTableBody.innerHTML = students.map(student => `
                            <tr>
                                <td>${student.id}</td>
                                <td>${student.student_id}</td>
                                <td>${student.name}</td>
                                <td>${student.program_name || 'N/A'}</td>
                                <td>${student.section_name || 'N/A'}</td>
                                <td>
                                    <span class="badge ${student.today_attendance === 'present' ? 'bg-success' : student.today_attendance === 'absent' ? 'bg-danger' : 'bg-secondary'}">
                                        ${student.today_attendance || 'Not recorded'}
                                    </span>
                                </td>
                            </tr>
                        `).join('');
                    }
                } else {
                    if (studentsTableBody) studentsTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No students found</td></tr>';
                }
            })
            .catch(() => {
                if (studentsTableBody) studentsTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Error loading students</td></tr>';
            });
    };

    // Load today's attendance details
    const loadTodayAttendance = () => {
        fetch('api/read.php?type=attendance_today')
            .then(resp => resp.json())
            .then(data => {
                if (data && data.success && data.data) {
                    const attendance = data.data;

                    if (attendanceTableBody) {
                        attendanceTableBody.innerHTML = attendance.map(record => `
                            <tr>
                                <td>${record.student_id}</td>
                                <td>${record.student_name}</td>
                                <td>${record.program_name || 'N/A'}</td>
                                <td>${record.section_name || 'N/A'}</td>
                                <td>
                                    <span class="badge ${record.status === '1' ? 'bg-success' : 'bg-danger'}">
                                        ${record.status === '1' ? 'Present' : 'Absent'}
                                    </span>
                                </td>
                                <td>${record.created_at ? new Date(record.created_at).toLocaleTimeString() : 'N/A'}</td>
                            </tr>
                        `).join('');
                    }
                } else {
                    if (attendanceTableBody) attendanceTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No attendance records for today</td></tr>';
                }
            })
            .catch(() => {
                if (attendanceTableBody) attendanceTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Error loading attendance</td></tr>';
            });
    };

    // Load all data
    loadAttendanceSummary();
    loadPrograms();
    loadSections();
    loadStudents();
    loadTodayAttendance();
})();

// Chart rendering for program distribution
(function() {
    'use strict';
    const ctx = document.getElementById('programChart');
    if (!ctx) return;

    fetch('api/attendance_summary.php')
        .then(resp => resp.json())
        .then(data => {
            if (!data || !data.success || !data.data || !data.data.program_distribution) return;
            const distribution = data.data.program_distribution;
            const labels = distribution.map(item => item.name);
            const counts = distribution.map(item => item.count);

            const chart = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 205, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 205, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = counts.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Program chart load error', err);
        });
})();

// Chart rendering for section distribution
(function() {
    'use strict';
    const ctx = document.getElementById('sectionChart');
    if (!ctx) return;

    fetch('api/attendance_summary.php')
        .then(resp => resp.json())
        .then(data => {
            if (!data || !data.success || !data.data || !data.data.section_distribution) return;
            const distribution = data.data.section_distribution;
            const labels = distribution.map(item => item.name);
            const counts = distribution.map(item => item.count);

            const chart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Students',
                        data: counts,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Students: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Section chart load error', err);
        });
})();

// Chart rendering for attendance trend
(function() {
    'use strict';
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    fetch('api/attendance_trend.php')
        .then(resp => resp.json())
        .then(json => {
            if (!json || !json.success || !json.data) return;
            const labels = json.data.labels.map(d => new Date(d).toLocaleDateString());
            const present = json.data.present;
            const absent = json.data.absent;

            const chart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Present',
                            data: present,
                            borderColor: 'rgba(40, 167, 69, 1)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(40, 167, 69, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                        },
                        {
                            label: 'Absent',
                            data: absent,
                            borderColor: 'rgba(220, 53, 69, 1)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(220, 53, 69, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                title: function(context) {
                                    return 'Date: ' + context[0].label;
                                },
                                label: function(context) {
                                    const total = present[context.dataIndex] + absent[context.dataIndex];
                                    const percentage = total > 0 ? Math.round((context.parsed.y / total) * 100) : 0;
                                    return context.dataset.label + ': ' + context.parsed.y + ' (' + percentage + '%)';
                                },
                                footer: function(context) {
                                    const index = context[0].dataIndex;
                                    const total = present[index] + absent[index];
                                    return 'Total: ' + total + ' students';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            display: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Students',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        })
        .catch(err => {
            // silently ignore chart errors
            console.error('Chart load error', err);
        });
})();

// Chart rendering for today's attendance status
(function() {
    'use strict';
    const ctx = document.getElementById('todayStatusChart');
    if (!ctx) return;

    fetch('api/attendance_summary.php')
        .then(resp => resp.json())
        .then(data => {
            if (!data || !data.success || !data.data) return;
            const d = data.data;
            const present = d.todays_present || 0;
            const absent = d.todays_absent || 0;

            const chart = new Chart(ctx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [present, absent],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = present + absent;
                                    const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Today status chart load error', err);
        });
})();
