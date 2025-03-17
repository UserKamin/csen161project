document.addEventListener('DOMContentLoaded', function () {
    const addWorkoutButton = document.getElementById('addWorkoutButton');

    // Add new workout plan
    addWorkoutButton.addEventListener('click', function () {
        const workoutName = prompt("Enter the name of your new workout plan:");
        if (workoutName) {
            // Send AJAX request to add workout plan
            fetch('add_workout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ workoutName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh the page to show the new workout plan
                } else {
                    alert("Failed to add workout plan.");
                }
            });
        }
    });
});

// Delete exercise from workout
function deleteExerciseFromWorkout(workoutId, exerciseId) {
    if (confirm("Are you sure you want to delete this exercise from the workout?")) {
        // Send AJAX request to delete exercise from workout
        fetch('delete_exercise_from_workout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ workoutId, exerciseId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh the page to reflect the deletion
            } else {
                alert("Failed to delete exercise from workout.");
            }
        });
    }
}

// Delete workout plan
function deleteWorkout(workoutId) {
    if (confirm("Are you sure you want to delete this workout plan?")) {
        // Send AJAX request to delete workout plan
        fetch('delete_workout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ workoutId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh the page to reflect the deletion
            } else {
                alert("Failed to delete workout plan.");
            }
        });
    }
}