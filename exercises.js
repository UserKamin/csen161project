document.addEventListener('DOMContentLoaded', function () {
    const muscleGroupFilter = document.getElementById('muscleGroupFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const searchBar = document.getElementById('searchBar');
    const exercises = document.querySelectorAll('.exercise-card');

    function filterExercises() {
        const selectedMuscleGroup = muscleGroupFilter.value.toLowerCase();
        const selectedDifficulty = difficultyFilter.value.toLowerCase();
        const searchText = searchBar.value.toLowerCase();

        exercises.forEach(exercise => {
            const muscleGroups = exercise.getAttribute('data-muscle-groups');
            const difficulty = exercise.getAttribute('data-difficulty');
            const name = exercise.querySelector('h2').textContent.toLowerCase();

            const matchesMuscleGroup = selectedMuscleGroup === '' || muscleGroups.includes(selectedMuscleGroup);
            const matchesDifficulty = selectedDifficulty === '' || difficulty === selectedDifficulty;
            const matchesSearch = name.includes(searchText);

            if (matchesMuscleGroup && matchesDifficulty && matchesSearch) {
                exercise.style.display = 'block';
            } else {
                exercise.style.display = 'none';
            }
        });
    }

    muscleGroupFilter.addEventListener('change', filterExercises);
    difficultyFilter.addEventListener('change', filterExercises);
    searchBar.addEventListener('input', filterExercises);

        // Show the workout plans modal
    window.showWorkoutPlansModal = function (exerciseId) {
        const modal = document.getElementById('workoutPlansModal');
        modal.style.display = 'flex';
        modal.setAttribute('data-exercise-id', exerciseId);
    };

    // Close the workout plans modal
    window.closeWorkoutPlansModal = function () {
        const modal = document.getElementById('workoutPlansModal');
        modal.style.display = 'none';
    };

    // Add exercise to workout plan
    window.addExerciseToWorkout = function (workoutId) {
        const modal = document.getElementById('workoutPlansModal');
        const exerciseId = modal.getAttribute('data-exercise-id'); // Retrieve the exercise ID
        fetch('workout_planner_functions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                functionName: 'addExerciseToWorkout', // Name of the function to call
                workoutId: workoutId, // Additional arguments
                exerciseId: exerciseId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Exercise added to workout plan!');
                closeWorkoutPlansModal();
                location.reload(); // Refresh the page to reflect the deletion
            } else {
                alert('Failed to add exercise to workout plan.');
            }
        });
    };
});