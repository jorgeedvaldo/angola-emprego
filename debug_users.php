$users = App\Models\User::all();
foreach($users as $u) {
    echo "User: " . $u->name . " (" .$u->email . ")\n";
    $enrolled = $u->courses()->count();
    $pivot = DB::table('lesson_user')->where('user_id', $u->id)->whereNotNull('completed_at')->count();
    echo "Enrolled: $enrolled, Completed Lessons (Raw): $pivot\n";
    
    if ($enrolled > 0) {
        $completedAttr = $u->completed_courses;
        echo "Attribute Count: " . $completedAttr->count() . "\n";
    }
}
