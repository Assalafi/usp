<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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

        $data = [
            'page' => 'general settings',
            'categories' => $categories,
            'currentCategory' => $category,
            'settings' => $settings,
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
