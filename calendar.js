document.addEventListener('DOMContentLoaded', function () {
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Function to open workout modal
    window.openWorkoutModal = function (day, index) {
        document.getElementById('selected-day').textContent = day;
        document.getElementById('day-index').value = index;
        document.getElementById('workout-modal').style.display = 'block';

        // Fetch user's workouts and populate the dropdown
        fetch('calendar_functions.php?action=get_user_workouts')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('workout-select');
                    select.innerHTML = '';
                    data.workouts.forEach(workout => {
                        const option = document.createElement('option');
                        option.value = workout.workout_id;
                        option.textContent = workout.workout_name;
                        select.appendChild(option);
                    });
                } else {
                    alert('Error fetching workouts: ' + data.message);
                }
            });
    };

    // Function to delete a workout from the calendar
    window.deleteWorkoutFromCalendar = function (workoutId, dayIndex) {
        if (confirm("Are you sure you want to delete this workout from the calendar?")) {
            // Create AJAX request to delete workout
            fetch('calendar_functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=delete_calendar_workout&workout_id=${workoutId}&day_index=${dayIndex}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page to reflect changes
                    location.reload();
                } else {
                    alert('Error deleting workout: ' + data.message);
                }
            });
        }
    };

    // Close modal when clicking on X
    document.querySelector('.close').onclick = function () {
        document.getElementById('workout-modal').style.display = 'none';
    };

    // Close modal when clicking outside of it
    window.onclick = function (event) {
        const modal = document.getElementById('workout-modal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Handle workout form submission
    document.getElementById('workout-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const dayIndex = document.getElementById('day-index').value;
        const workoutId = document.getElementById('workout-select').value;

        // Create AJAX request to save workout
        fetch('calendar_functions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=add_calendar_workout&day_index=${dayIndex}&workout_id=${workoutId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page to reflect changes
                    location.reload();
                } else {
                    alert('Error saving workout: ' + data.message);
                }
            });
    });
});