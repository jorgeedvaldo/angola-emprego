$user = App\Models\User::where('email', 'edysj@example.com')->first() ?? App\Models\User::first();
echo "User: " . $user->name . " (ID: " . $user->id . ")\n";

$courses = $user->courses;
echo "Enrolled Courses: " . $courses->count() . "\n";

foreach($courses as $course) {
    echo "--------------------------------------------------\n";
    echo "Checking Course: " . $course->title . " (ID: " . $course->id . ")\n";
    
    $totalLessons = $course->lessons()->count();
    echo "Total Lessons in Course: " . $totalLessons . "\n";
    
    $lessonIds = $course->lessons()->pluck('id');
    // echo "Lesson IDs: " . $lessonIds->implode(',') . "\n";
    
    $completedLessonsQuery = $user->lessons()
        ->whereIn('lesson_id', $lessonIds)
        ->whereNotNull('completed_at');
        
    $completedLessonsCount = $completedLessonsQuery->count();
    echo "User Completed Lessons: " . $completedLessonsCount . "\n";
    
    $isComplete = $completedLessonsCount === $totalLessons && $totalLessons > 0;
    echo "Is Complete? " . ($isComplete ? 'YES' : 'NO') . "\n";
}

echo "--------------------------------------------------\n";
echo "Accessing via attribute:\n";
$completed_via_attr = $user->completed_courses;
echo "Completed count: " . $completed_via_attr->count() . "\n";
foreach($completed_via_attr as $c) {
    echo "- " . $c->title . "\n";
}
