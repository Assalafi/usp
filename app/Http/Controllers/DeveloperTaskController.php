<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeveloperTaskController extends Controller
{
    private function username(): string
    {
        return (string) (session('username') ?: session('id_number') ?: 'unknown');
    }

    private function displayName(): string
    {
        return (string) (session('name') ?: session('fullname') ?: session('username') ?: 'Portal user');
    }

    private function isAdmin(): bool
    {
        return strtoupper((string) session('accType')) === 'ADMIN';
    }

    private function isDeveloper(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $appointment = strtoupper(trim((string) session('appointment')));
        return session('accType') === 'Staff' && in_array($appointment, ['DEVELOPER', 'DEVELOPMENT', 'IT', 'ICT'], true);
    }

    private function canView($task): bool
    {
        return $this->isAdmin()
            || (string) ($task->assigned_to ?? '') === $this->username()
            || (string) ($task->created_by ?? '') === $this->username();
    }

    private function requireAdmin(): void
    {
        abort_unless($this->isAdmin(), 403, 'Only administrators can perform this task action.');
    }

    private function requireTaskAccess($task): void
    {
        abort_unless($this->canView($task), 403, 'You do not have access to this task.');
    }

    public function index(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $query = DB::table('developer_tasks')
            ->leftJoin('users as assignee', 'assignee.username', '=', 'developer_tasks.assigned_to')
            ->select('developer_tasks.*', 'assignee.name as assignee_name')
            ->when(!$this->isAdmin(), function ($q) {
                $q->where(function ($inner) {
                    $inner->where('developer_tasks.assigned_to', $this->username())
                        ->orWhere('developer_tasks.created_by', $this->username());
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('developer_tasks.status', (string) $request->input('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('developer_tasks.priority', (string) $request->input('priority')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = trim((string) $request->input('q'));
                $q->where(function ($inner) use ($search) {
                    $inner->where('developer_tasks.title', 'like', '%' . $search . '%')
                        ->orWhere('developer_tasks.description', 'like', '%' . $search . '%')
                        ->orWhere('developer_tasks.assigned_to', 'like', '%' . $search . '%');
                });
            });

        $tasks = $query->orderByRaw("FIELD(developer_tasks.status, 'changes_requested', 'assigned', 'in_progress', 'review', 'confirmed')")
            ->orderByDesc('developer_tasks.updated_at')
            ->paginate(15)
            ->withQueryString();

        $statsQuery = DB::table('developer_tasks');
        if (!$this->isAdmin() && !$this->isDeveloper()) {
            $statsQuery->where(function ($q) {
                $q->where('assigned_to', $this->username())->orWhere('created_by', $this->username());
            });
        }

        $developers = $this->isAdmin()
            ? DB::table('users')->leftJoin('staff', 'staff.username', '=', 'users.username')
                ->whereIn('users.accType', ['Staff', 'Admin'])
                ->where('users.status', '!=', '0')
                ->select('users.username', DB::raw('COALESCE(NULLIF(staff.name, ""), NULLIF(users.name, ""), users.username) as display_name'), 'staff.appointment')
                ->orderBy('display_name')
                ->get()
            : collect();

        return view('main', [
            'page' => 'developer tasks',
            'title' => 'Developer task board',
            'taskData' => $tasks,
            'taskStats' => [
                'total' => (clone $statsQuery)->count(),
                'open' => (clone $statsQuery)->whereIn('status', ['assigned', 'in_progress', 'changes_requested'])->count(),
                'review' => (clone $statsQuery)->where('status', 'review')->count(),
                'confirmed' => (clone $statsQuery)->where('status', 'confirmed')->count(),
            ],
            'taskFilters' => $request->only(['status', 'priority', 'q']),
            'taskDevelopers' => $developers,
            'isTaskAdmin' => $this->isAdmin(),
        ]);
    }

    public function create()
    {
        $this->requireAdmin();

        $developers = DB::table('users')->leftJoin('staff', 'staff.username', '=', 'users.username')
            ->whereIn('users.accType', ['Staff', 'Admin'])
            ->where('users.status', '!=', '0')
            ->select('users.username', DB::raw('COALESCE(NULLIF(staff.name, ""), NULLIF(users.name, ""), users.username) as display_name'), 'staff.appointment')
            ->orderBy('display_name')->get();

        return view('main', [
            'page' => 'developer task create',
            'title' => 'Assign developer task',
            'taskDevelopers' => $developers,
        ]);
    }

    public function store(Request $request)
    {
        $this->requireAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg,zip,txt'],
            'attachment_descriptions' => ['nullable', 'array', 'max:10'],
            'attachment_descriptions.*' => ['nullable', 'string', 'max:255'],
        ]);

        $taskId = DB::transaction(function () use ($validated, $request) {
            $id = DB::table('developer_tasks')->insertGetId([
                'title' => trim($validated['title']),
                'description' => SystemSettingsController::sanitizeRichText($validated['description']),
                'priority' => $validated['priority'],
                'status' => 'assigned',
                'assigned_to' => $validated['assigned_to'] ?: null,
                'created_by' => $this->username(),
                'due_date' => $validated['due_date'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recordUpdate($id, 'assignment', null, 'Task assigned and added to the developer board.');
            $this->storeFiles($request, $id, null);
            return $id;
        });

        return redirect()->route('developer.tasks.show', $taskId)->with('success', 'Task assigned successfully.');
    }

    public function show(int $id)
    {
        $task = DB::table('developer_tasks')->where('id', $id)->first();
        abort_if(!$task, 404, 'Task not found.');
        $this->requireTaskAccess($task);

        $updates = DB::table('developer_task_updates')->where('task_id', $id)->orderBy('created_at')->get();
        $files = DB::table('developer_task_files')->where('task_id', $id)->orderByDesc('created_at')->get();

        return view('main', [
            'page' => 'developer task show',
            'title' => $task->title,
            'task' => $task,
            'taskUpdates' => $updates,
            'taskFiles' => $files,
            'isTaskAdmin' => $this->isAdmin(),
            'canTaskWork' => $this->isAdmin() || (string) $task->assigned_to === $this->username(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->requireAdmin();
        $task = DB::table('developer_tasks')->where('id', $id)->first();
        abort_if(!$task, 404, 'Task not found.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg,zip,txt'],
            'attachment_descriptions' => ['nullable', 'array', 'max:10'],
            'attachment_descriptions.*' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request, $id, $task) {
            DB::table('developer_tasks')->where('id', $id)->update([
                'title' => trim($validated['title']),
                'description' => SystemSettingsController::sanitizeRichText($validated['description']),
                'priority' => $validated['priority'],
                'assigned_to' => $validated['assigned_to'] ?: null,
                'due_date' => $validated['due_date'] ?? null,
                'updated_at' => now(),
            ]);
            $this->recordUpdate($id, 'assignment', null, 'Task details or assignment were updated by the administrator.');
            $this->storeFiles($request, $id, null);
        });

        return back()->with('success', 'Task details updated.');
    }

    public function addUpdate(Request $request, int $id)
    {
        $task = DB::table('developer_tasks')->where('id', $id)->first();
        abort_if(!$task, 404, 'Task not found.');
        $this->requireTaskAccess($task);

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'status_to' => ['required', 'in:assigned,in_progress,review'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg,zip,txt'],
            'attachment_descriptions' => ['nullable', 'array', 'max:10'],
            'attachment_descriptions.*' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request, $id) {
            $updateId = $this->recordUpdate($id, 'progress', $validated['status_to'], $validated['body']);
            DB::table('developer_tasks')->where('id', $id)->update([
                'status' => $validated['status_to'],
                'updated_at' => now(),
            ]);
            $this->storeFiles($request, $id, $updateId);
        });

        return back()->with('success', 'Task update recorded.');
    }

    public function review(Request $request, int $id)
    {
        $this->requireAdmin();
        $task = DB::table('developer_tasks')->where('id', $id)->first();
        abort_if(!$task, 404, 'Task not found.');

        $validated = $request->validate([
            'decision' => ['required', 'in:confirm,recommend'],
            'note' => ['nullable', 'string'],
        ]);

        $status = $validated['decision'] === 'confirm' ? 'confirmed' : 'changes_requested';
        $body = trim((string) ($validated['note'] ?? ''));
        if ($body === '') {
            $body = $status === 'confirmed' ? 'Task confirmed by the administrator.' : 'Please review the task and make the requested changes.';
        }

        DB::transaction(function () use ($validated, $id, $status, $body) {
            $this->recordUpdate($id, $validated['decision'] === 'confirm' ? 'confirmation' : 'recommendation', $status, $body);
            DB::table('developer_tasks')->where('id', $id)->update([
                'status' => $status,
                'confirmed_at' => $status === 'confirmed' ? now() : null,
                'confirmed_by' => $status === 'confirmed' ? $this->username() : null,
                'confirmation_note' => $status === 'confirmed' ? $body : null,
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', $status === 'confirmed' ? 'Task confirmed.' : 'Recommendation sent to the developer.');
    }

    public function downloadFile(int $id, int $fileId)
    {
        $task = DB::table('developer_tasks')->where('id', $id)->first();
        abort_if(!$task, 404, 'Task not found.');
        $this->requireTaskAccess($task);

        $file = DB::table('developer_task_files')->where(['id' => $fileId, 'task_id' => $id])->first();
        abort_if(!$file || !Storage::disk('public')->exists($file->path), 404, 'Attachment not found.');

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    private function recordUpdate(int $taskId, string $type, ?string $statusTo, string $body): int
    {
        return DB::table('developer_task_updates')->insertGetId([
            'task_id' => $taskId,
            'user_username' => $this->username(),
            'user_name' => $this->displayName(),
            'type' => $type,
            'status_to' => $statusTo,
            'body' => SystemSettingsController::sanitizeRichText($body),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function storeFiles(Request $request, int $taskId, ?int $updateId): void
    {
        $files = $request->file('attachments', []);
        if (!$files) {
            return;
        }

        $descriptions = $request->input('attachment_descriptions', []);
        foreach ($files as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $name = $file->getClientOriginalName();
            $storedName = Str::uuid() . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
            $path = $file->storeAs('developer-tasks/' . $taskId, $storedName, 'public');
            DB::table('developer_task_files')->insert([
                'task_id' => $taskId,
                'update_id' => $updateId,
                'uploaded_by' => $this->username(),
                'original_name' => $name,
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'description' => trim((string) ($descriptions[$index] ?? '')) ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
