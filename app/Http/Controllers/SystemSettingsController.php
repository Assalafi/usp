<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSettingsController extends Controller
{
    /**
     * Display the general settings page
     */
    public function index(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $category = $request->get('category', 'sessions');
        
        $categories = [
            'sessions' => ['icon' => 'fas fa-calendar-alt', 'title' => 'Session Management', 'color' => 'purple'],
            'fees' => ['icon' => 'fas fa-money-bill', 'title' => 'Fee Settings', 'color' => 'success'],
            'security' => ['icon' => 'fas fa-shield-alt', 'title' => 'Security Settings', 'color' => 'danger'],
            'academic' => ['icon' => 'fas fa-graduation-cap', 'title' => 'Academic Settings', 'color' => 'primary'],
            'hostel' => ['icon' => 'fas fa-building', 'title' => 'Hostel Settings', 'color' => 'info'],
            'recruitment' => ['icon' => 'fas fa-user-tie', 'title' => 'Recruitment Portal', 'color' => 'info'],
            'institution' => ['icon' => 'fas fa-university', 'title' => 'Institution Settings', 'color' => 'info'],
            'payment' => ['icon' => 'fas fa-credit-card', 'title' => 'Payment Gateway', 'color' => 'warning'],
            'email' => ['icon' => 'fas fa-envelope', 'title' => 'Email/Notifications', 'color' => 'secondary'],
            'maintenance' => ['icon' => 'fas fa-tools', 'title' => 'Maintenance', 'color' => 'dark'],
        ];

        // For recruitment category, fetch from recruitment API
        if ($category === 'recruitment') {
            $settings = $this->getRecruitmentSettings();
        } else {
            $settings = DB::table('system_settings')
                ->where('category', $category)
                ->orderBy('id')
                ->get();
        }

        $hodStaff = collect();
        $hodManagementUsers = [];
        if ($category === 'academic') {
            $hodStaff = DB::table('staff as s')
                ->leftJoin('department as d', 'd.code', '=', 's.department')
                ->whereRaw('UPPER(s.appointment) = ?', ['HOD'])
                ->where('s.status', '1')
                ->select('s.username', 's.name', 's.department', 'd.title as department_title')
                ->orderBy('s.name')
                ->orderBy('s.username')
                ->get();

            $hodUsersSetting = $settings->firstWhere('key', 'hod_program_course_management_users');
            $hodManagementUsers = json_decode((string) ($hodUsersSetting->value ?? '[]'), true);
            $hodManagementUsers = is_array($hodManagementUsers) ? $hodManagementUsers : [];
        }

        $data = [
            'page' => 'general settings',
            'categories' => $categories,
            'currentCategory' => $category,
            'settings' => $settings,
            'remitaServiceCatalog' => self::getRemitaServiceCatalog(),
            'remitaSplitSettings' => self::getRemitaSplitSettings(),
            'hodStaff' => $hodStaff,
            'hodManagementUsers' => $hodManagementUsers,
        ];

        return view('main', $data);
    }

    /**
     * Update settings
     */
    public static function sanitizeRichText($value): string
    {
        $html = trim((string) $value);
        if ($html === '') {
            return '';
        }

        // These are the only elements needed by the hostel text editor. Strip
        // everything else before rendering the content to students.
        $html = strip_tags($html, '<p><div><br><strong><b><em><i><u><ol><ul><li><blockquote><h2><h3><h4><a>');
        $html = preg_replace('/\s+on[a-z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;

        // Remove arbitrary attributes and keep only safe, external link URLs.
        $html = preg_replace_callback('/<([a-z][a-z0-9]*)\b([^>]*)>/i', function (array $match): string {
            $tag = strtolower($match[1]);
            if ($tag === 'br') {
                return '<br>';
            }
            if ($tag !== 'a') {
                $alignment = '';
                if (preg_match('/\bstyle\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $match[2], $styleMatch)) {
                    $style = $styleMatch[1] ?? $styleMatch[2] ?? $styleMatch[3] ?? '';
                    if (preg_match('/(?:^|;)\s*text-align\s*:\s*(left|center|right|justify)\s*(?:;|$)/i', $style, $alignmentMatch)) {
                        $alignment = strtolower($alignmentMatch[1]);
                    }
                }
                if ($alignment === '' && preg_match('/\balign\s*=\s*(?:"(left|center|right|justify)"|\'(left|center|right|justify)\'|([^\s>]+))/i', $match[2], $alignMatch)) {
                    $alignment = strtolower($alignMatch[1] ?? $alignMatch[2] ?? $alignMatch[3] ?? '');
                }

                return '<' . $tag . ($alignment !== '' ? ' style="text-align: ' . $alignment . ';"' : '') . '>';
            }

            $href = '';
            if (preg_match('/\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $match[2], $hrefMatch)) {
                $href = trim($hrefMatch[1] ?? $hrefMatch[2] ?? $hrefMatch[3] ?? '');
            }

            if ($href !== ''
                && preg_match('/^(?:https?:\/\/|mailto:|\/|#)/i', $href)
                && !preg_match('/^(?:javascript|data|vbscript):/i', $href)) {
                return '<a href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">';
            }

            return '<a>';
        }, $html) ?? $html;

        return trim($html);
    }

    public function update(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $settings = $request->except('_token');

        if (array_key_exists('remita_service_catalog', $settings)) {
            $catalog = self::normaliseRemitaServiceCatalog($settings['remita_service_catalog']);
            if ($catalog === null) {
                return response()->json(['error' => 'The Remita service list is invalid. Refresh the page and try again.'], 422);
            }

            foreach ($catalog as $service) {
                if ($service['active'] && !preg_match('/^\d+$/', $service['service_type_id'])) {
                    return response()->json(['error' => 'Every active Remita service must have a numeric Service Type ID.'], 422);
                }
            }

            $knownKeys = collect($catalog)->pluck('key')->all();
            foreach (self::defaultRemitaServiceCatalog() as $defaultService) {
                if (!in_array($defaultService['key'], $knownKeys, true)) {
                    $catalog[] = $defaultService;
                }
            }

            $this->storeSettingValue('remita_service_catalog', json_encode($catalog, JSON_UNESCAPED_SLASHES));
            unset($settings['remita_service_catalog']);
        }

        if (array_key_exists('remita_split_settings', $settings)) {
            $splitSettings = self::normaliseRemitaSplitSettings($settings['remita_split_settings']);
            if ($splitSettings === null) {
                return response()->json(['error' => 'Check the split settings. The three percentages must total 100, and all active beneficiary accounts need a valid account number and bank code.'], 422);
            }

            $this->storeSettingValue('remita_split_settings', json_encode($splitSettings, JSON_UNESCAPED_SLASHES));
            unset($settings['remita_split_settings']);
        }

        // The HOD programme-course policy is stored as a simple academic
        // setting, while the selected usernames are kept as JSON so the
        // setting page can safely submit multiple staff members.
        if (array_key_exists('hod_program_course_management_mode', $settings)
            || array_key_exists('hod_program_course_management_users', $settings)) {
            $mode = (string) ($settings['hod_program_course_management_mode'] ?? 'view');
            if (!in_array($mode, ['view', 'all', 'selected'], true)) {
                return response()->json(['error' => 'Choose a valid HOD access option.'], 422);
            }

            $selected = $settings['hod_program_course_management_users'] ?? [];
            if (!is_array($selected)) {
                $selected = [$selected];
            }
            $selected = collect($selected)
                ->map(fn ($username) => trim((string) $username))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($mode === 'selected' && empty($selected)) {
                return response()->json(['error' => 'Select at least one Head of Department, or choose another access option.'], 422);
            }

            DB::table('system_settings')->where('key', 'hod_program_course_management_mode')->update([
                'value' => $mode,
                'updated_at' => now(),
            ]);
            DB::table('system_settings')->where('key', 'hod_program_course_management_users')->update([
                'value' => json_encode($selected),
                'updated_at' => now(),
            ]);

            unset($settings['hod_program_course_management_mode'], $settings['hod_program_course_management_users']);
        }

        // Handle recruitment settings via API
        if (isset($settings['recruitment_status']) || isset($settings['recruitment_closed_message'])) {
            $result = $this->updateRecruitmentSettings($settings);
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update recruitment settings. Please try again.'
                ], 500);
            }
            return response()->json([
                'success' => true,
                'message' => 'Recruitment settings updated successfully!'
            ]);
        }
        
        foreach ($settings as $key => $value) {
            if (in_array($key, ['hostel_closed_message', 'hostel_announcement'], true)) {
                $value = self::sanitizeRichText($value);
            }
            DB::table('system_settings')
                ->where('key', $key)
                ->update([
                    'value' => $value,
                    'updated_at' => now()
                ]);
        }

        // Clear settings cache
        Cache::forget('system_settings');

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully!'
        ]);
    }

    /**
     * Get recruitment settings from recruitment portal API
     */
    private function getRecruitmentSettings()
    {
        $apiUrl = 'https://employee.umstad.online/api/settings/recruitment-status';

        try {
            $response = Http::withoutVerifying()->timeout(10)->get($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                $isOpen = $data['data']['recruitment_open'] ?? true;
                $closedMessage = $data['data']['message'] ?? 'The recruitment exercise is currently closed.';

                return collect([
                    (object) [
                        'id' => 1,
                        'key' => 'recruitment_status',
                        'value' => $isOpen ? '1' : '0',
                        'type' => 'boolean',
                        'label' => 'Recruitment Portal Status',
                        'description' => 'Open or close the recruitment portal. When closed, applicants cannot create or submit new applications.',
                    ],
                    (object) [
                        'id' => 2,
                        'key' => 'recruitment_closed_message',
                        'value' => $closedMessage ?? 'The recruitment exercise is currently closed. Please check back later for updates.',
                        'type' => 'text',
                        'label' => 'Closed Message',
                        'description' => 'Message displayed to applicants when recruitment is closed.',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch recruitment settings: ' . $e->getMessage());
        }

        // Fallback defaults if API fails
        return collect([
            (object) [
                'id' => 1,
                'key' => 'recruitment_status',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Recruitment Portal Status',
                'description' => 'Open or close the recruitment portal. When closed, applicants cannot create or submit new applications.',
            ],
            (object) [
                'id' => 2,
                'key' => 'recruitment_closed_message',
                'value' => 'The recruitment exercise is currently closed. Please check back later for updates.',
                'type' => 'text',
                'label' => 'Closed Message',
                'description' => 'Message displayed to applicants when recruitment is closed.',
            ],
        ]);
    }

    /**
     * Update recruitment settings via recruitment portal API
     */
    private function updateRecruitmentSettings(array $settings): bool
    {
        $apiUrl = 'https://employee.umstad.online/api/settings/recruitment-status';
        $apiKey = config('app.recruitment_api_key', env('RECRUITMENT_API_KEY'));

        if (!$apiKey) {
            \Log::error('Recruitment API key not configured');
            return false;
        }

        try {
            $payload = [];
            if (isset($settings['recruitment_status'])) {
                $payload['recruitment_status'] = $settings['recruitment_status'];
            }
            if (isset($settings['recruitment_closed_message'])) {
                $payload['recruitment_closed_message'] = $settings['recruitment_closed_message'];
            }

            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(10)->post($apiUrl, $payload);

            if ($response->successful()) {
                return true;
            }

            \Log::error('Recruitment API update failed: ' . $response->status() . ' - ' . $response->body());
            return false;
        } catch (\Exception $e) {
            \Log::error('Recruitment API error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a single setting
     */
    public function updateSingle(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $key = $request->input('key');
        $value = $request->input('value');

        if (in_array($key, ['hostel_closed_message', 'hostel_announcement'], true)) {
            $value = self::sanitizeRichText($value);
        }

        DB::table('system_settings')
            ->where('key', $key)
            ->update([
                'value' => $value,
                'updated_at' => now()
            ]);

        // Clear settings cache
        Cache::forget('system_settings');

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully!'
        ]);
    }

    /**
     * Get a setting value
     */
    public static function get($key, $default = null)
    {
        $settings = Cache::remember('system_settings', 3600, function () {
            return DB::table('system_settings')->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function defaultRemitaServiceCatalog(): array
    {
        return [
            [
                'key' => 'school_fees',
                'label' => 'School fees',
                'service_type_id' => (string) config('services.remita.school_fees_key', '365039916'),
                'description' => 'Student school-fee invoices.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'hostel',
                'label' => 'Hostel accommodation',
                'service_type_id' => (string) config('services.remita.hostel_key', ''),
                'description' => 'Hostel reservation invoices.',
                'active' => (string) config('services.remita.hostel_key', '') !== '',
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'post_utme',
                'label' => 'Post-UTME',
                'service_type_id' => (string) self::get('remita_putme_service_type', config('services.remita.post_utme_key', '4430731')),
                'description' => 'Post-UTME application payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'id_card',
                'label' => 'Student ID card',
                'service_type_id' => (string) self::get('id_card_service_type', '767553585'),
                'description' => 'Student identity-card payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'change_of_course',
                'label' => 'Change of course',
                'service_type_id' => (string) config('services.remita.change_of_course_key', '4430731'),
                'description' => 'Change-of-course application payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
            [
                'key' => 'inter_university_transfer',
                'label' => 'Inter-university transfer',
                'service_type_id' => (string) config('services.remita.inter_transfer_key', '4430731'),
                'description' => 'Inter-university transfer payments.',
                'active' => true,
                'split_enabled' => false,
                'surcharge' => '0.00',
            ],
        ];
    }

    public static function getRemitaServiceCatalog(): array
    {
        $decoded = json_decode((string) self::get('remita_service_catalog', ''), true);
        $catalog = self::normaliseRemitaServiceCatalog($decoded);
        if ($catalog === null || empty($catalog)) {
            return self::defaultRemitaServiceCatalog();
        }

        $keys = collect($catalog)->pluck('key')->all();
        foreach (self::defaultRemitaServiceCatalog() as $defaultService) {
            if (!in_array($defaultService['key'], $keys, true)) {
                $catalog[] = $defaultService;
            }
        }

        return $catalog;
    }

    public static function getRemitaServiceType(string $key, ?string $fallback = null): string
    {
        $service = collect(self::getRemitaServiceCatalog())->firstWhere('key', $key);
        $configured = trim((string) ($service['service_type_id'] ?? ''));
        if ($configured !== '') {
            return $configured;
        }

        return (string) ($fallback ?? '');
    }

    /**
     * Return the configured additional split charge for one service.
     * A service-level amount takes precedence over the legacy global amount.
     */
    public static function getRemitaServiceSurcharge(string $key, $fallback = 0): float
    {
        $service = collect(self::getRemitaServiceCatalog())->firstWhere('key', $key);
        if (is_array($service) && array_key_exists('surcharge', $service)) {
            return max(0, (float) $service['surcharge']);
        }

        $split = self::getRemitaSplitSettings();
        return max(0, (float) ($split['surcharge'] ?? $fallback));
    }

    public static function getRemitaSplitSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'surcharge' => '0.00',
            'fee_bearer' => 'primary',
            'beneficiaries' => [
                ['key' => 'primary', 'label' => 'University main account', 'account_number' => '', 'bank_code' => '', 'percentage' => 40],
                ['key' => 'additional_1', 'label' => 'Additional account 1', 'account_number' => '', 'bank_code' => '', 'percentage' => 30],
                ['key' => 'additional_2', 'label' => 'Additional account 2', 'account_number' => '', 'bank_code' => '', 'percentage' => 30],
            ],
        ];
        $decoded = json_decode((string) self::get('remita_split_settings', ''), true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        $settings = array_merge($defaults, $decoded);
        $beneficiaries = [];
        foreach ($defaults['beneficiaries'] as $index => $defaultBeneficiary) {
            $beneficiary = is_array($settings['beneficiaries'][$index] ?? null)
                ? array_merge($defaultBeneficiary, $settings['beneficiaries'][$index])
                : $defaultBeneficiary;
            $encrypted = (string) ($beneficiary['account_number'] ?? '');
            if ($encrypted !== '') {
                try {
                    $beneficiary['account_number'] = Crypt::decryptString($encrypted);
                } catch (\Throwable $exception) {
                    // Existing local/test values may still be unencrypted.
                }
            }
            $beneficiaries[] = $beneficiary;
        }
        $settings['beneficiaries'] = $beneficiaries;
        $settings['enabled'] = filter_var($settings['enabled'], FILTER_VALIDATE_BOOLEAN);
        $settings['surcharge'] = number_format(max(0, (float) $settings['surcharge']), 2, '.', '');

        return $settings;
    }

    private static function normaliseRemitaServiceCatalog($value): ?array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value)) {
            return null;
        }

        $normalised = [];
        $seen = [];
        foreach ($value as $service) {
            if (!is_array($service)) {
                continue;
            }
            $key = strtolower(trim((string) ($service['key'] ?? '')));
            $label = trim((string) ($service['label'] ?? ''));
            $serviceTypeId = trim((string) ($service['service_type_id'] ?? ''));
            if ($key === '' || !preg_match('/^[a-z0-9_]+$/', $key) || isset($seen[$key]) || $label === '') {
                continue;
            }
            $seen[$key] = true;
            $normalised[] = [
                'key' => $key,
                'label' => mb_substr($label, 0, 120),
                'service_type_id' => mb_substr($serviceTypeId, 0, 60),
                'description' => mb_substr(trim((string) ($service['description'] ?? '')), 0, 255),
                'active' => filter_var($service['active'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'split_enabled' => filter_var($service['split_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'surcharge' => number_format(max(0, (float) ($service['surcharge'] ?? 0)), 2, '.', ''),
            ];
        }

        return $normalised;
    }

    private static function normaliseRemitaSplitSettings($value): ?array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value)) {
            return null;
        }

        $beneficiaryRows = array_values((array) ($value['beneficiaries'] ?? []));
        if (count($beneficiaryRows) !== 3) {
            return null;
        }

        $beneficiaries = [];
        $percentageTotal = 0.0;
        foreach ($beneficiaryRows as $index => $row) {
            if (!is_array($row)) {
                return null;
            }
            $key = $index === 0 ? 'primary' : 'additional_' . $index;
            $label = trim((string) ($row['label'] ?? '')) ?: ($index === 0 ? 'University main account' : 'Additional account ' . $index);
            $account = preg_replace('/\D+/', '', (string) ($row['account_number'] ?? ''));
            $bankCode = preg_replace('/\D+/', '', (string) ($row['bank_code'] ?? ''));
            $percentage = (float) ($row['percentage'] ?? 0);
            if ($percentage < 0 || $percentage > 100 || $label === '') {
                return null;
            }
            if (filter_var($value['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)
                && (!preg_match('/^\d{6,20}$/', $account) || !preg_match('/^\d{3,12}$/', $bankCode))) {
                return null;
            }
            $percentageTotal += $percentage;
            $beneficiaries[] = [
                'key' => $key,
                'label' => mb_substr($label, 0, 120),
                'account_number' => $account !== '' ? Crypt::encryptString($account) : '',
                'bank_code' => mb_substr($bankCode, 0, 12),
                'percentage' => $percentage,
            ];
        }

        if (abs($percentageTotal - 100) > 0.001) {
            return null;
        }

        return [
            'enabled' => filter_var($value['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'surcharge' => number_format(max(0, (float) ($value['surcharge'] ?? 0)), 2, '.', ''),
            'fee_bearer' => 'primary',
            'beneficiaries' => $beneficiaries,
        ];
    }

    private function storeSettingValue(string $key, string $value): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $value,
                'type' => 'json',
                'category' => 'payment',
                'label' => $key === 'remita_service_catalog' ? 'Remita service types' : 'Remita split-payment settings',
                'description' => 'Managed from Payment Gateway settings.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        Cache::forget('system_settings');
    }

    /**
     * Get session setting helpers
     */
    public static function getSystemSession()
    {
        return self::get('system_session', DB::table('session')->where('status', '1')->value('title'));
    }

    public static function getSchoolFeesSession()
    {
        return self::get('school_fees_session', self::getSystemSession());
    }

    public static function getHostelFeesSession()
    {
        return self::get('hostel_fees_session', self::getSystemSession());
    }

    public static function getPostUtmeSession()
    {
        return self::get('post_utme_session', self::getSystemSession());
    }

    public static function getResultsSession()
    {
        return self::get('results_session', self::getSystemSession());
    }

    public static function getCurrentSession()
    {
        return self::get('system_session', self::getSystemSession());
    }

    /**
     * Get Remita base URL based on mode setting
     */
    public static function getRemitaBaseUrl()
    {
        $mode = self::get('remita_mode', 'demo');
        if ($mode == 'live') {
            return self::get('remita_live_url', 'https://login.remita.net');
        }
        return self::get('remita_demo_url', 'https://demo.remita.net');
    }

    /**
     * Check if Remita is in live mode
     */
    public static function isRemitaLive()
    {
        return self::get('remita_mode', 'demo') == 'live';
    }

    /**
     * Reset settings to default
     */
    public function resetDefaults(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $category = $request->input('category');

        // Default values
        $defaults = [
            'fees' => [
                'change_of_course_fee' => '5000',
                'putme_fee' => '2000',
                'id_card_fee' => '2000',
                'acceptance_fee' => '50000',
                'school_fee_fresh' => '45000',
                'school_fee_returning' => '35000',
                'transcript_fee' => '5000',
                'certificate_verification_fee' => '10000',
            ],
            'security' => [
                'default_student_password' => 'umstad@2026',
                'default_staff_password' => 'staff@2026',
                'password_min_length' => '8',
                'session_timeout' => '120',
            ],
            'hostel' => [
                'hostel_application_status' => '1',
                'hostel_closed_message' => 'Hostel applications are currently closed. Please check back later or contact Student Affairs.',
                'hostel_announcement' => '',
            ],
        ];

        if (isset($defaults[$category])) {
            foreach ($defaults[$category] as $key => $value) {
                DB::table('system_settings')
                    ->where('key', $key)
                    ->update(['value' => $value, 'updated_at' => now()]);
            }
        }

        Cache::forget('system_settings');

        return response()->json([
            'success' => true,
            'message' => 'Settings reset to defaults!'
        ]);
    }

    /**
     * Export settings
     */
    public function export()
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $settings = DB::table('system_settings')->get();
        
        $filename = 'system_settings_' . date('Y-m-d_His') . '.json';
        
        return response()->json($settings)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
