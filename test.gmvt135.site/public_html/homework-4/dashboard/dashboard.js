/**
 * dashboard.js
 */

// --- GLOBAL CHART INSTANCES ---
// We attach these to the window object so applyDateFilter can access them.
window.myBrowserChart = null;
window.myLineChart = null;
window.myBarChart = null;

/**
 * DATE FILTER LOGIC
 * Updates all charts and tables based on the selected date range.
 */
window.applyDateFilter = function() {
    const startInput = document.getElementById('startDate') || document.getElementById('startDateEng');
    const endInput = document.getElementById('endDate') || document.getElementById('endDateEng');
    
    if (!startInput || !endInput) return;

    const start = startInput.value;
    const end = endInput.value;


    fetch(`get_filtered_data.php?start=${start}&end=${end}`)
        .then(res => res.json())
        .then(data => {
            console.log("Raw Data Received:", data);

            // 1. Update Performance (Doughnut)
            if (window.myBrowserChart && data.browser && data.browser.labels) {
                window.myBrowserChart.data.labels = data.browser.labels;
                window.myBrowserChart.data.datasets[0].data = data.browser.counts;
                window.myBrowserChart.update();
            }

            // 2. Update Behavior (Line)
            if (window.myLineChart && data.timeline && data.timeline.labels) {
                window.myLineChart.data.labels = data.timeline.labels;
                window.myLineChart.data.datasets[0].data = data.timeline.counts;
                window.myLineChart.update();
            }

            // 3. Update Engagement (Bar Chart + Table)
            if (data.engagement && data.engagement.labels) {
                // Update Bar Chart
                if (window.myBarChart) {
                    window.myBarChart.data.labels = data.engagement.labels;
                    window.myBarChart.data.datasets[0].data = data.engagement.counts;
                    window.myBarChart.update();
                }

                // Update Table with Score Logic
                const tableBody = document.getElementById('engagementTableBody');
                if (tableBody) {
                    const maxCount = Math.max(...data.engagement.counts);

                    tableBody.innerHTML = data.engagement.labels.map((label, index) => {
                        const count = data.engagement.counts[index];
                        const score = maxCount > 0 ? Math.round((count / maxCount) * 100) : 0;
                        
                        let color = "#e91e63"; // Low
                        if (score > 70) color = "#4caf50"; // High
                        else if (score > 30) color = "#ff9800"; // Medium

                        return `
                            <tr>
                                <td><strong>${label}</strong></td>
                                <td>${count.toLocaleString()}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="flex-grow:1; background:#eee; height:8px; border-radius:4px;">
                                            <div style="width:${score}%; background:${color}; height:100%; border-radius:4px;"></div>
                                        </div>
                                        <span>${score}%</span>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            }
        })
        .catch(err => console.error("Filter Error:", err));
};

// 1. Send feedback to the database
window.submitFeedback = function() {
    const textareas = document.querySelectorAll('#feedbackText');
    let message = "";
    textareas.forEach(t => { if(t.value) message = t.value; });

    if (!message) return;

    const formData = new FormData();
    formData.append('message', message);
    
    // --- THE FIX ---
    // Use the variable you verified in the console
    formData.append('username', window.userSession.username);
    formData.append('role', window.userSession.role); // Should be 'super_admin'

    fetch('submit_feedback.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        textareas.forEach(t => t.value = '');
        loadFeedback(); // Refresh the list
    });
};
// 2. Load feedback thread - Simple Forum Version
function loadFeedback() {
    fetch('get_feedback.php')
        .then(res => res.json())
        .then(data => {
            const threads = document.querySelectorAll('#feedbackThread');
            if (threads.length === 0 || !Array.isArray(data)) return;
            
            const htmlContent = data.map(f => {
                // Safety fallbacks to prevent "undefined"
                const user = f.username || "Guest";
                const role = f.role || "Viewer";
                const message = f.message || "";
                const date = f.created_at || "";

                return `
                <div style="border-bottom: 1px solid #eee; padding: 12px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-weight: bold; color: #2a2185; font-size: 0.9rem;">
                            ${user} <span style="font-weight: normal; color: #666; font-size: 0.75rem;">(${role})</span>
                        </span>
                        <span style="font-size: 0.65rem; color: #aaa;">${date}</span>
                    </div>
                    <div style="color: #444; line-height: 1.4;">
                        ${message}
                    </div>
                </div>`;
            }).join('') || '<p style="color: #999; text-align: center; margin-top: 20px;">No discussions yet. Start the conversation!</p>';

            threads.forEach(thread => {
                thread.innerHTML = htmlContent;
            });
        })
        .catch(err => {
            console.error("Forum Load Error:", err);
        });
}

// Run on page load
document.addEventListener('DOMContentLoaded', loadFeedback);



document.addEventListener('DOMContentLoaded', () => {
    console.log("Dashboard Initialized. View:", window.currentView);

    // --- 1. Sidebar Toggle ---
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.toggle-btn');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // --- 2. Chart Loading ---
    try {
        if (window.currentView === 'performance') {
            loadPerformanceChart();
        } else if (window.currentView === 'behavior') {
            loadBehaviorChart();
        } else if (window.currentView === 'engagement') {
            loadEngagementView();
        }
    } catch (error) {
        console.error("Chart Logic Error:", error);
    }
});

/**
 * DATABASE SAVE LOGIC
 */
window.saveReportDefinition = function(reportType, textareaId) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;

    const insightText = textarea.value;
    const formData = new FormData();
    formData.append('report_type', reportType);
    formData.append('content', insightText);

    fetch('save_report.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert("Report saved!");
            const display = document.getElementById('display_insight_' + reportType);
            if (display) display.innerText = insightText;
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => console.error("Save Error:", err));
};

/**
 * ADMIN LOGIC
 */
window.updateUser = function(userId, field, value) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('field', field);
    formData.append('value', value);

    fetch('update_user.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') alert("Update failed: " + data.message);
    })
    .catch(err => console.error("Admin Update Error:", err));
};

// --- CHART DATA FETCHING FUNCTIONS (INITIAL LOAD) ---

function loadPerformanceChart() {
    const ctx = document.getElementById('myBrowserChart'); // Updated ID to match filter logic
    if (!ctx) return;
    fetch('get_analytics.php')
        .then(res => res.json())
        .then(data => {
            window.myBrowserChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{ data: data.counts, backgroundColor: ['#36a2eb','#ff6384','#ffce56','#4bc0c0','#9966ff'] }]
                }
            });
        });
}

function loadBehaviorChart() {
    const ctx = document.getElementById('lineChart');
    if (!ctx) return;
    fetch('get_time_data.php')
        .then(res => res.json())
        .then(data => {
            window.myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{ label: 'Events', data: data.counts, borderColor: '#36a2eb', tension: 0.3 }]
                }
            });
        });
}

function loadEngagementView() {
    fetch('get_engagement.php')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('engagementBarChart');
            if (ctx) {
                window.myBarChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(i => i.event_name),
                        datasets: [{ label: 'Total', data: data.map(i => i.total), backgroundColor: '#36a2eb' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
            
            const tableBody = document.getElementById('engagementTableBody');
            if (tableBody) {
                tableBody.innerHTML = data.map(row => `
                    <tr>
                        <td><strong>${row.event_name}</strong></td>
                        <td>${row.total}</td>
                        <td style="color: ${row.total > 50 ? 'green' : 'orange'}; font-weight:bold;">
                            ${row.total > 50 ? 'High' : 'Medium'}
                        </td>
                    </tr>
                `).join('');
            }
        });
}


