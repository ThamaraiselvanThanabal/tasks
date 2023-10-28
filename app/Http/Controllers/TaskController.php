<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    public function createTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:tasks,title',
            'description' => 'required',
            'due_date' => ['required', 'date', 'after_or_equal:' . now()->toDateString()],
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Get the validated 'due_date' directly
        $dueDate = $validator->validated()['due_date'];
    
        $priority = $this->calculateTaskPriority($dueDate);
        // dd($priority);
        // Create the task with the calculated priority
        $task = Task::create([
            'title' => $validator->validated()['title'],
            'description' => $validator->validated()['description'],
            'priority' => $priority,
            'due_date' => $dueDate
            
        ]);
    
        return response()->json(['message' => 'Task created successfully'], 201);
    }
        public function calculateTaskPriority($dueDate)
        {
            $currentDate = now();
            
            $highPriorityThreshold = $currentDate->copy()->addDays(1); // Due within 1 day
            $mediumPriorityThreshold = $currentDate->copy()->addDays(7); // Due within 7 days
    
            if ($dueDate <= $currentDate) {
                return 'High'; // Overdue tasks are high priority
            } elseif ($dueDate <= $highPriorityThreshold) {
                return 'High';
            } elseif ($dueDate <= $mediumPriorityThreshold) {
                return 'Medium';
            } else {
                return 'Low';
            }
        }
            
        public function updateTask(Request $request, $taskId) {
            
            $task = Task::find($taskId);
    
            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }
    
            $task->update($request->all());
    
            if ($task->status === 'Completed') {
                $task->completed_date = now();
                // dd($task);
                $task->save();
            }
    
            return response()->json($task, 200);
        }
    
        public function deleteTask($taskId) {
    
            $task = Task::find($taskId);
    
            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }
    
            $task->delete();
    
            return response()->json(['message' => 'Task deleted'], 200);
        }
    
    
        public function index() {
            $tasks = Task::all();
    
            return response()->json($tasks, 200);
        }
    
        public function assignTask(Request $request, $taskId)
        {
           
            $validator = Validator::make($request->all(),[
                    'userId' => 'required|exists:users,id',
                ]);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
               
                $task = Task::find($taskId);
    
                if (!$task) {
                    return response()->json(['error' => 'Task not found'], 404);
                }
    
                
                $userId = $request->input('userId');
                $user = User::find($userId);
    
                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }
    
            //    dd($user);
                $task->user()->associate($user);
                $task->save();
    
                return response()->json(['message' => 'Task assigned successfully']);
        }
    
        public function getUserAssignedTasks($userId)
        {
            // Query the database to retrieve tasks assigned to the specified user
            $tasks = Task::where('userId', $userId)->get();
    
            return response()->json(['tasks' => $tasks]);
        }
    
        public function setTaskProgress(Request $request, $taskId)
        {
            
            $validator = Validator::make($request->all(),[
                'progress' => 'required|integer|min:0|max:100',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $task = Task::find($taskId);
            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }
            $task->progress = $request->input('progress');
            $task->save();
    
            return response()->json(['message' => 'Task progress updated successfully']);
        }
    
        public function getOverdueTasks()
        {
            $today = now();
            $overdueTasks = Task::where('due_date', '<', $today)->get();
    
            return response()->json(['tasks' => $overdueTasks]);
        }
    
        public function getTasksByStatus($status)
        {
            // dd($status);
            
            $tasks = Task::where('status', $status)->get();
    
            return response()->json(['tasks' => $tasks]);
        }
    
        public function getCompletedTasksByDateRange(Request $request)
        {
            $validator = Validator::make($request->all(),[
                'startDate' => 'required|date',
                'endDate' => 'required|date',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $startDate = date('Y-m-d',strtotime($request->input('startDate')));
            $endDate = date('Y-m-d',strtotime($request->input('endDate')));
    
        
            $completedTasks = Task::where('status', 'Completed')
                ->whereBetween('completed_date', [$startDate, $endDate])
                ->get();
    
            return response()->json(['completedTasks' => $completedTasks]);
        }
    
        public function getTasksStatistics()
        {
           
            $totalTasks = Task::count();
            $completedTasks = Task::where('status', 'Completed')->count();
            $percentageCompleted = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
    
            return response()->json([
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'percentageCompleted' => $percentageCompleted,
            ]);
        }
}
