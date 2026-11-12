<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Support\PortalPermission;

class PagesController extends Controller
{
    //
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace("create ", "", $contents);
        $contents = str_replace("upload ", "", $contents);
        $contents = str_replace("download ", "", $contents);
        $contents = str_replace("update ", "", $contents);
        $contents = str_replace("delete ", "", $contents);
        $this->page = $contents;
        $this->table = str_replace(" ", "_", $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $main = trim((string) $req->input('main', ''));
        $rows = DB::table($this->table)
            ->when($main !== '', fn ($query) => $query->where('main', 'like', '%' . $main . '%'))
            ->orderBy('main_order')->orderBy('sub_order')->orderBy('id')
            ->get();
        $groups = $rows->groupBy('main')->map(function ($items) {
            $first = $items->first();
            return (object) [
                'main' => $first->main,
                'main_order' => $first->main_order,
                'page_count' => $items->count(),
                'action_count' => $items->sum(function ($item) {
                    return count(array_filter(array_map('trim', explode(',', (string) $item->action))));
                }),
            ];
        })->values();
        $registeredGroups = $this->navigationGroups();
        foreach ($registeredGroups as $position => $registeredGroup) {
            if ($main !== '' && stripos($registeredGroup, $main) === false) {
                continue;
            }
            if (!$groups->contains(fn ($group) => strcasecmp($group->main, $registeredGroup) === 0)) {
                $groups->push((object) [
                    'main' => $registeredGroup,
                    'main_order' => $position + 1,
                    'page_count' => 0,
                    'action_count' => 0,
                ]);
            }
        }
        $data['data'] = $groups->sortBy('main_order')->values();
        $data['pageRows'] = $rows;
        $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
        $data['pageStats'] = [
            'groups' => $this->navigationGroups()->count(),
            'pages' => DB::table($this->table)->count(),
            'links' => DB::table($this->table)->whereNotNull('link')->where('link', '!=', '')->count(),
        ];
        $data['pageFilter'] = $main;
        $data['navigationGroups'] = $registeredGroups;
        $data['availableActions'] = $this->availableActions($rows);
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main',$data);
    }

    public function createAction(Request $req)
    {
        abort_unless(session()->has('log') && session('accType') === 'Admin', 403);
        $validated = $req->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_-]*$/i'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);
        if (!Schema::hasTable('navigation_actions')) {
            return redirect()->back()->with('error', 'Action registry is not available yet. Run the latest migrations.');
        }
        DB::table('navigation_actions')->updateOrInsert(
            ['name' => strtolower(trim($validated['name']))],
            ['label' => trim($validated['label'] ?? '') ?: ucfirst(str_replace(['-', '_'], ' ', $validated['name'])), 'created_by' => (string) session('username'), 'updated_at' => now(), 'created_at' => now()]
        );
        return redirect()->back()->with('success', 'Action is now available on Pages and Rolls.');
    }

    public function syncActions(Request $req)
    {
        abort_unless(session()->has('log') && session('accType') === 'Admin', 403);

        if (!Schema::hasTable('navigation_actions')) {
            return redirect()->back()->with('error', 'Action registry is not available yet. Run the latest migrations.');
        }

        $labels = PortalPermission::actionLabels();
        $tokens = collect(PortalPermission::knownActions())
            ->merge(DB::table('pages')->pluck('action'))
            ->merge(DB::table('rolls')->pluck('action'))
            ->flatMap(fn ($value) => array_map([PortalPermission::class, 'normalizeAction'], explode(',', (string) $value)))
            ->filter()->unique()->values();

        foreach ($tokens as $token) {
            DB::table('navigation_actions')->updateOrInsert(
                ['name' => $token],
                [
                    'label' => $labels[$token] ?? ucfirst(str_replace('_', ' ', $token)),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        PortalPermission::flush();

        return redirect()->back()->with('success', $tokens->count() . ' actions are now available for page assignments.');
    }

    public function syncGroups(Request $req)
    {
        abort_unless(session()->has('log') && session('accType') === 'Admin', 403);

        $navPath = base_path('resources/views/nav.blade.php');
        $source = is_file($navPath) ? file_get_contents($navPath) : '';
        preg_match_all('/<li class="nav-item pcoded-hasmenu[^>]*>[\s\S]*?<span class="pcoded-mtext">\s*([^<]+)\s*<\/span>/', $source, $matches);
        $codeGroups = collect($matches[1] ?? [])->map(fn ($name) => trim(preg_replace('/\s+/', ' ', $name)))->filter()->unique()->values();
        $existing = DB::table('pages')->select('main', DB::raw('MIN(main_order) as sort_order'))->groupBy('main')->orderBy('sort_order')->get();
        $codePosition = $codeGroups->flip();
        // The pages table is the authoritative permission catalogue. The nav
        // file supplies the current menu order, while stale navigation-only
        // names are deliberately not added to the permission registry.
        $groups = $existing->sortBy(function ($row) use ($codePosition) {
            return $codePosition->get($row->main, 100000 + (int) $row->sort_order);
        })->pluck('main')->values();

        DB::transaction(function () use ($groups) {
            DB::table('navigation_groups')->whereNotIn('name', $groups->all())->delete();
            foreach ($groups as $index => $group) {
                $values = ['sort_order' => $index + 1, 'source' => 'resources/views/nav.blade.php', 'updated_at' => now()];
                if (DB::table('navigation_groups')->where('name', $group)->exists()) {
                    DB::table('navigation_groups')->where('name', $group)->update($values);
                } else {
                    DB::table('navigation_groups')->insert($values + ['name' => $group, 'created_at' => now()]);
                }
            }
        });

        PortalPermission::flush();

        return redirect()->back()->with('success', $groups->count() . ' navigation groups are now available for page assignment.');
    }

    private function navigationGroups()
    {
        $pageGroups = DB::table('pages')->distinct()->orderBy('main_order')->pluck('main');
        if (!Schema::hasTable('navigation_groups')) {
            return $pageGroups->filter()->unique()->values();
        }
        $registered = DB::table('navigation_groups')->whereIn('name', $pageGroups->all())->orderBy('sort_order')->orderBy('name')->pluck('name');
        return $registered->merge($pageGroups)->filter()->unique()->values();
    }

    private function availableActions($rows = null)
    {
        $actions = collect(PortalPermission::knownActions());
        if (Schema::hasTable('navigation_actions')) {
            $actions = $actions->merge(DB::table('navigation_actions')->orderBy('label')->pluck('name')->map([PortalPermission::class, 'normalizeAction']));
        }
        foreach (($rows ?: DB::table('pages')->select('action')->get()) as $row) {
            foreach (array_filter(array_map('trim', explode(',', (string) $row->action))) as $action) {
                $actions->push(PortalPermission::normalizeAction($action));
            }
        }
        return $actions->filter()->unique()->values();
    }
    
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}


        try {
            DB::beginTransaction();

            $pages = $req->input('page', []);
            $link = $req->input('link');
            $sub_order = $req->input('sub_order');
            $main = $req->input('main');
            $main_order = $req->input('main_order');
            $action = $req->input('action');

            foreach ($pages as $key => $page) {
                if (trim((string) $page) === '') {
                    continue;
                }
                DB::table('pages')->insert([
                    'main' => $main,
                    'main_order' => $main_order,
                    'page' => $page,
                    'link' => $link[$key],
                    'sub_order' => $sub_order[$key],
                    'action' => PortalPermission::normalizeActionList($action[$key] ?? 'view'),
                ]);
            }

            DB::commit();
            PortalPermission::flush();
            return redirect()->back()->with('success', 'Record Created!!!');
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e;
            die;
            return redirect()->back()->with('error', 'Error storing data. '.$e);
        }
        
    }
    
    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $ids = $req->input('id', []);
        if (is_array($ids)) {
            foreach ($ids as $key => $id) {
                if (!$id) {
                    continue;
                }
                DB::table($this->table)->where('id', $id)->update([
                    'main' => strtoupper(trim((string) ($req->input('main') ?: $req->input('main.'.$key, '')))),
                    'main_order' => (int) ($req->input('main_order') ?: $req->input('main_order.'.$key, 0)),
                    'page' => strtoupper(trim((string) ($req->input('page')[$key] ?? ''))),
                    'link' => trim((string) ($req->input('link')[$key] ?? '')),
                    'sub_order' => (int) ($req->input('sub_order')[$key] ?? 0),
                    'action' => PortalPermission::normalizeActionList($req->input('action')[$key] ?? 'view'),
                ]);
            }
            $pages = $req->input('page', []);
            $links = $req->input('link', []);
            $subOrders = $req->input('sub_order', []);
            $actions = $req->input('action', []);
            foreach ($pages as $key => $page) {
                if (isset($ids[$key]) || trim((string) $page) === '') {
                    continue;
                }
                DB::table($this->table)->insert([
                    'main' => strtoupper(trim((string) ($req->input('main') ?: ''))),
                    'main_order' => (int) $req->input('main_order', 0),
                    'page' => strtoupper(trim((string) $page)),
                    'link' => trim((string) ($links[$key] ?? '')),
                    'sub_order' => (int) ($subOrders[$key] ?? 0),
                    'action' => PortalPermission::normalizeActionList($actions[$key] ?? 'view'),
                ]);
            }
        } else {
            $id = $req->input('id');
            $payload = $req->except(['id', '_token']);
            if (array_key_exists('action', $payload)) {
                $payload['action'] = PortalPermission::normalizeActionList($payload['action']);
            }
            DB::table($this->table)->where('id', $id)->update($payload);
        }

        PortalPermission::flush();
        return redirect()->back()->with('success', 'Record Updated!!!');
    }
    
    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        PortalPermission::flush();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
