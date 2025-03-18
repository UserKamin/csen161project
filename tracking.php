<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}
require_once 'db_config.php';

// Create tracking table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tracking (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        date TEXT NOT NULL,
        weight REAL,
        calories INTEGER,
        steps INTEGER,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )");
} catch (PDOException $e) {
    // Just continue if there's an error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Tracking</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="nav.css">
    <style>
        .tracking-container {
            margin-top: 30px;
        }
        
        .chart-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .data-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .edit-btn, .delete-btn, .save-btn, .cancel-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }
        
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        
        .save-btn {
            background-color: #2196F3;
            color: white;
        }
        
        .cancel-btn {
            background-color: #9e9e9e;
            color: white;
        }
        
        .chart-tabs {
            display: flex;
            margin-bottom: 15px;
        }
        
        .chart-tab {
            padding: 10px 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .chart-tab.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        
        .editable-row input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">Workout Planner</h1>
            <ul class="nav-menu">
                <li class="nav-item"><a href="home.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="exercises.php" class="nav-link">Exercises</a></li>
                <li class="nav-item"><a href="workouts.php" class="nav-link">Workouts</a></li>
                <li class="nav-item"><a href="calendar.php" class="nav-link">Calendar</a></li>
                <li class="nav-item"><a href="tracking.php" class="nav-link active">Tracking</a></li>
                <li class="nav-item"><a href="auth.php?action=logout" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <h2>Health Tracking</h2>
        
        <div class="tracking-container">
            <!-- Chart Section -->
            <div class="chart-container">
                <div class="chart-tabs">
                    <div class="chart-tab active" data-type="weight">Weight</div>
                    <div class="chart-tab" data-type="calories">Calories</div>
                    <div class="chart-tab" data-type="steps">Steps</div>
                </div>
                <canvas id="trackingChart"></canvas>
            </div>
            
            <!-- Form Section -->
            <div class="form-container">
                <h3>Add New Entry</h3>
                <form id="tracking-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tracking-date">Date</label>
                            <input type="date" id="tracking-date" name="date" required>
                        </div>
                        <div class="form-group">
                            <label for="tracking-weight">Weight (kg)</label>
                            <input type="number" id="tracking-weight" name="weight" step="0.1" min="0" placeholder="e.g., 70.5">
                        </div>
                        <div class="form-group">
                            <label for="tracking-calories">Calories</label>
                            <input type="number" id="tracking-calories" name="calories" min="0" placeholder="e.g., 2000">
                        </div>
                        <div class="form-group">
                            <label for="tracking-steps">Steps</label>
                            <input type="number" id="tracking-steps" name="steps" min="0" placeholder="e.g., 10000">
                        </div>
                    </div>
                    <button type="submit" class="btn">Save Entry</button>
                </form>
            </div>
            
            <!-- Data Table Section -->
            <div class="data-container">
                <h3>Your Tracking History</h3>
                <div id="tracking-data">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Weight (kg)</th>
                                <th>Calories</th>
                                <th>Steps</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tracking-table-body">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Global variables
        let trackingData = [];
        let currentChart = null;
        let currentChartType = 'weight';
        
        // Function to load tracking data
        function loadTrackingData() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'tracking_functions.php?action=get_tracking_data', true);
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            trackingData = response.data;
                            renderTrackingTable();
                            renderChart(currentChartType);
                        } else {
                            alert('Error loading tracking data: ' + response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', this.responseText);
                        alert('Error loading tracking data: Invalid response from server');
                    }
                }
            };
            
            xhr.send();
        }
        
        // Function to get today's date in California time zone (Pacific Time)
        function getCaliforniaDate() {
            // Create a date object for the current time
            const now = new Date();
            
            // Get the date in Pacific Time (PT)
            const options = { timeZone: 'America/Los_Angeles' };
            const ptDateString = now.toLocaleDateString('en-US', options);
            
            // Parse the date string back into a Date object
            const [month, day, year] = ptDateString.split('/');
            
            // Format as YYYY-MM-DD for the date input
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }
        
        // Function to format date for display
        function formatDateForDisplay(dateString) {
            const date = new Date(dateString + 'T00:00:00');
            return date.toLocaleDateString();
        }
        
        // Function to render tracking table
        function renderTrackingTable() {
            const tableBody = document.getElementById('tracking-table-body');
            tableBody.innerHTML = '';
            
            // Sort data by date (newest first)
            const sortedData = [...trackingData].sort((a, b) => new Date(b.date) - new Date(a.date));
            
            if (sortedData.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="5" style="text-align: center;">No tracking data available. Add your first entry above.</td>';
                tableBody.appendChild(row);
                return;
            }
            
            sortedData.forEach(entry => {
                const row = document.createElement('tr');
                row.dataset.id = entry.id;
                
                // Format date for display
                const formattedDate = formatDateForDisplay(entry.date);
                
                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>${entry.weight !== null ? entry.weight : '-'}</td>
                    <td>${entry.calories !== null ? entry.calories : '-'}</td>
                    <td>${entry.steps !== null ? entry.steps : '-'}</td>
                    <td>
                        <button class="edit-btn" onclick="editEntry(${entry.id})">Edit</button>
                        <button class="delete-btn" onclick="deleteEntry(${entry.id})">Delete</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }
        
        // Function to render chart
        function renderChart(type) {
            currentChartType = type;
            
            // Update active tab
            document.querySelectorAll('.chart-tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.dataset.type === type) {
                    tab.classList.add('active');
                }
            });
            
            // Sort data by date (oldest first for chart)
            const sortedData = [...trackingData].sort((a, b) => new Date(a.date) - new Date(b.date));
            
            if (sortedData.length === 0) {
                // If no data, destroy chart if it exists
                if (currentChart) {
                    currentChart.destroy();
                    currentChart = null;
                }
                return;
            }
            
            // Prepare data for chart
            const labels = sortedData.map(entry => formatDateForDisplay(entry.date));
            
            let dataValues = [];
            let label = '';
            let borderColor = '';
            let backgroundColor = '';
            
            switch (type) {
                case 'weight':
                    dataValues = sortedData.map(entry => entry.weight);
                    label = 'Weight (kg)';
                    borderColor = 'rgb(75, 192, 192)';
                    backgroundColor = 'rgba(75, 192, 192, 0.2)';
                    break;
                case 'calories':
                    dataValues = sortedData.map(entry => entry.calories);
                    label = 'Calories';
                    borderColor = 'rgb(255, 99, 132)';
                    backgroundColor = 'rgba(255, 99, 132, 0.2)';
                    break;
                case 'steps':
                    dataValues = sortedData.map(entry => entry.steps);
                    label = 'Steps';
                    borderColor = 'rgb(54, 162, 235)';
                    backgroundColor = 'rgba(54, 162, 235, 0.2)';
                    break;
            }
            
            // Destroy previous chart if it exists
            if (currentChart) {
                currentChart.destroy();
            }
            
            // Create new chart
            const ctx = document.getElementById('trackingChart').getContext('2d');
            currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: dataValues,
                        borderColor: borderColor,
                        backgroundColor: backgroundColor,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        }
        
        // Function to edit entry
        function editEntry(id) {
            const entry = trackingData.find(item => parseInt(item.id) === id);
            if (!entry) return;
            
            const row = document.querySelector(`tr[data-id="${id}"]`);
            
            row.classList.add('editable-row');
            row.innerHTML = `
                <td><input type="date" value="${entry.date}" id="edit-date-${id}"></td>
                <td><input type="number" value="${entry.weight || ''}" step="0.1" min="0" id="edit-weight-${id}" placeholder="e.g., 70.5"></td>
                <td><input type="number" value="${entry.calories || ''}" min="0" id="edit-calories-${id}" placeholder="e.g., 2000"></td>
                <td><input type="number" value="${entry.steps || ''}" min="0" id="edit-steps-${id}" placeholder="e.g., 10000"></td>
                <td>
                    <button class="save-btn" onclick="saveEditedEntry(${id})">Save</button>
                    <button class="cancel-btn" onclick="cancelEdit(${id})">Cancel</button>
                </td>
            `;
        }
        
        // Function to save edited entry
        function saveEditedEntry(id) {
            const date = document.getElementById(`edit-date-${id}`).value;
            const weight = document.getElementById(`edit-weight-${id}`).value;
            const calories = document.getElementById(`edit-calories-${id}`).value;
            const steps = document.getElementById(`edit-steps-${id}`).value;
            
            if (!date) {
                alert('Date is required');
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'update_entry');
            formData.append('id', id);
            formData.append('date', date);
            formData.append('weight', weight);
            formData.append('calories', calories);
            formData.append('steps', steps);
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'tracking_functions.php', true);
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        console.log('Server response:', this.responseText);
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            loadTrackingData(); // Reload data
                        } else {
                            alert('Error updating entry: ' + response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', this.responseText);
                        alert('Error updating entry: Invalid response from server');
                    }
                } else {
                    alert('Error: Server returned status ' + this.status);
                }
            };
            
            xhr.onerror = function() {
                alert('Request error occurred');
            };
            
            xhr.send(formData);
        }
        
        // Function to cancel edit
        function cancelEdit(id) {
            loadTrackingData(); // Reload data to reset the table
        }
        
        // Function to delete entry
        function deleteEntry(id) {
            if (confirm('Are you sure you want to delete this entry?')) {
                // Create form data
                const formData = new FormData();
                formData.append('action', 'delete_entry');
                formData.append('id', id);
                
                // Send AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'tracking_functions.php', true);
                
                xhr.onload = function() {
                    if (this.status === 200) {
                        try {
                            console.log('Server response:', this.responseText);
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                loadTrackingData(); // Reload data
                            } else {
                                alert('Error deleting entry: ' + response.message);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', this.responseText);
                            alert('Error deleting entry: Invalid response from server');
                        }
                    } else {
                        alert('Error: Server returned status ' + this.status);
                    }
                };
                
                xhr.onerror = function() {
                    alert('Request error occurred');
                };
                
                xhr.send(formData);
            }
        }
        
        // Event listener for form submission
        document.getElementById('tracking-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const date = document.getElementById('tracking-date').value;
            const weight = document.getElementById('tracking-weight').value;
            const calories = document.getElementById('tracking-calories').value;
            const steps = document.getElementById('tracking-steps').value;
            
            if (!date) {
                alert('Date is required');
                return;
            }
            
            if (!weight && !calories && !steps) {
                alert('At least one of Weight, Calories, or Steps must be provided');
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'add_entry');
            formData.append('date', date);
            formData.append('weight', weight);
            formData.append('calories', calories);
            formData.append('steps', steps);
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'tracking_functions.php', true);
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            // Clear form
                            document.getElementById('tracking-form').reset();
                            // Set date to today in California time
                            document.getElementById('tracking-date').value = getCaliforniaDate();
                            // Reload data
                            loadTrackingData();
                        } else {
                            alert('Error adding entry: ' + response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', this.responseText);
                        alert('Error adding entry: Invalid response from server');
                    }
                } else {
                    alert('Error: Server returned status ' + this.status);
                }
            };
            
            xhr.onerror = function() {
                alert('Request error occurred');
            };
            
            xhr.send(formData);
        });
        
        // Event listeners for chart tabs
        document.querySelectorAll('.chart-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                renderChart(this.dataset.type);
            });
        });
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set date input to today's date in California time
            document.getElementById('tracking-date').value = getCaliforniaDate();
            
            // Load tracking data
            loadTrackingData();
        });
    </script>
</body>
</html>

