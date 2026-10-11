<?php

namespace App\Imports;

use App\Models\Alumni;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Converts existing student records to alumni from a one-column workbook.
 *
 * The import intentionally never creates an alumni record from the workbook
 * itself. Every identifier must first resolve to students.username.
 */
class StudentIdsToAlumniImport implements ToCollection
{
    private array $stats = [
        'rows' => 0,
        'converted' => 0,
        'already_alumni' => 0,
        'duplicates' => 0,
        'not_found' => [],
        'invalid' => [],
        'errors' => [],
    ];

    public function collection(Collection $rows): void
    {
        $seen = [];

        foreach ($rows as $index => $row) {
            $values = collect(is_array($row) ? $row : $row->toArray())
                ->map(fn ($value) => trim(str_replace("\u{00A0}", ' ', (string) $value)));
            $nonEmpty = $values->filter(fn ($value) => $value !== '');

            if ($nonEmpty->isEmpty()) {
                continue;
            }

            $identifier = (string) ($values->first() ?? '');
            $header = strtolower(preg_replace('/[^a-z0-9]+/i', '', $identifier));
            if (in_array($header, ['username', 'studentid', 'idno', 'matricno', 'matricnumber', 'studentnumber'], true)) {
                continue;
            }

            $this->stats['rows']++;

            if ($nonEmpty->count() > 1) {
                $this->stats['invalid'][] = 'Row ' . ($index + 1) . ' contains more than one value.';
                continue;
            }

            $username = trim($identifier);
            if ($username === '' || strlen($username) > 255) {
                $this->stats['invalid'][] = 'Row ' . ($index + 1) . ' has an invalid student ID.';
                continue;
            }

            $key = strtolower($username);
            if (isset($seen[$key])) {
                $this->stats['duplicates']++;
                continue;
            }
            $seen[$key] = true;

            $student = Student::whereRaw('LOWER(username) = ?', [$key])->first();
            if (!$student) {
                $this->stats['not_found'][] = $username;
                continue;
            }

            try {
                $wasAlumni = (string) $student->status === '3'
                    || Alumni::where('username', $student->username)->exists();

                DB::transaction(function () use ($student): void {
                    $username = trim((string) $student->username);
                    $fullname = trim((string) ($student->fullname ?: implode(' ', array_filter([
                        $student->first_name,
                        $student->last_name,
                        $student->other_name,
                    ]))));
                    $fullname = $fullname !== '' ? $fullname : $username;

                    $user = null;
                    if (!empty($student->user_id) && (string) $student->user_id !== '0') {
                        $user = User::find($student->user_id);
                    }
                    if (!$user) {
                        $user = User::whereRaw('LOWER(username) = ?', [strtolower($username)])->first();
                    }
                    if (!$user) {
                        $user = User::create([
                            'username' => $username,
                            'name' => $fullname,
                            'gender' => $student->gender,
                            'level' => $student->level,
                            'program' => $student->program,
                            'department' => $student->department,
                            'faculty' => $student->faculty,
                            'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
                            'accType' => 'Alumni',
                            'status' => '1',
                        ]);
                    }

                    $email = trim((string) ($student->contact_email ?: $student->home_email ?: $user->email ?: $username));
                    $phone = trim((string) ($student->contact_phone ?: $student->home_phone ?: $user->phone ?: ''));
                    $existing = Alumni::where('username', $username)->first();

                    Alumni::updateOrCreate(
                        ['username' => $username],
                        [
                            'user_id' => (string) $user->id,
                            'id_no' => $username,
                            'fullname' => $fullname,
                            'email' => $email,
                            'phone' => $phone !== '' ? $phone : null,
                            'gender' => $student->gender ?: $user->gender,
                            'program' => $student->program,
                            'year' => $existing?->year ?: now()->format('Y'),
                            'picture' => ($student->picture && strtolower((string) $student->picture) !== 'none')
                                ? $student->picture
                                : ($existing?->picture ?: 'none'),
                            'password' => $existing?->password ?: Hash::make($username),
                        ]
                    );

                    $student->user_id = (string) $user->id;
                    $student->status = '3';
                    $student->updated_at = now();
                    $student->save();

                    $user->accType = 'Alumni';
                    $user->name = $user->name ?: $fullname;
                    $user->status = $user->status ?: '1';
                    $user->updated_at = now();
                    $user->save();
                });

                if ($wasAlumni) {
                    $this->stats['already_alumni']++;
                } else {
                    $this->stats['converted']++;
                }
            } catch (\Throwable $exception) {
                $this->stats['errors'][] = $username . ': ' . $exception->getMessage();
            }
        }
    }

    public function stats(): array
    {
        return $this->stats;
    }
}
