@php
    $liveCandidates = collect($data ?? [])->map(fn ($row) => (array) $row)->values();
    $livePositions = collect($positions ?? [])->map(fn ($row) => (array) $row)->values();
    $liveStatsPayload = $liveStats ?? [
        'general' => ['accredited' => 0, 'voted' => 0, 'remaining' => 0],
        'faculty' => [],
        'hostel' => [],
    ];
@endphp

<style>
    body.live-wall-open{overflow:hidden}
    .live-wall{position:fixed;inset:0;z-index:1050;display:flex;flex-direction:column;overflow:hidden;background:#071b2b;color:#eaf6ff;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    .live-wall::before{content:"";position:absolute;inset:0;pointer-events:none;background:radial-gradient(circle at 12% 0%,rgba(62,161,228,.2),transparent 32%),radial-gradient(circle at 100% 100%,rgba(34,197,154,.12),transparent 35%)}
    .live-wall-head{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px;padding:15px 24px;border-bottom:1px solid rgba(193,232,252,.15);background:rgba(7,27,43,.88);backdrop-filter:blur(16px)}
    .live-wall-brand{display:flex;align-items:center;gap:12px;min-width:0}.live-wall-brand img{width:40px;height:40px;object-fit:contain;border-radius:10px;background:#fff;padding:4px}.live-wall-brand strong{display:block;color:#fff;font-size:1rem;letter-spacing:.02em}.live-wall-brand small{display:block;margin-top:2px;color:#9bc2d8;font-size:.7rem}
    .live-wall-meta{display:flex;align-items:center;gap:10px}.live-wall-status{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;background:rgba(22,178,117,.17);color:#8af0c6;font-size:.73rem;font-weight:800}.live-wall-status.closed{background:rgba(239,151,63,.16);color:#ffd09c}.live-wall-status i{font-size:.48rem}.live-wall-clock{color:#a9c6d7;font-size:.75rem;font-variant-numeric:tabular-nums}.live-wall-exit{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(194,230,247,.2);border-radius:9px;color:#d8effc;text-decoration:none;font-size:.72rem;font-weight:800}.live-wall-exit:hover{background:rgba(255,255,255,.08);color:#fff}
    .live-wall-grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));grid-template-rows:repeat(2,minmax(0,1fr));gap:14px;min-height:0;flex:1;padding:14px 18px 18px}
    .live-quadrant{display:flex;flex-direction:column;min-width:0;min-height:0;overflow:hidden;border:1px solid rgba(191,227,246,.15);border-radius:18px;background:linear-gradient(155deg,rgba(18,58,83,.9),rgba(7,29,46,.94));box-shadow:0 18px 40px rgba(0,0,0,.2)}.live-quadrant.primary{border-color:rgba(84,186,236,.6);background:linear-gradient(150deg,#0e588d,#082c47)}.live-quadrant.faculty{border-color:rgba(162,133,255,.45)}.live-quadrant.hostel{border-color:rgba(78,212,166,.45)}
    .live-quadrant-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px;border-bottom:1px solid rgba(191,227,246,.13)}.live-quadrant-kicker{display:flex;align-items:center;gap:7px;color:#8fbace;font-size:.64rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.live-quadrant-kicker i{color:#66c8f4}.faculty .live-quadrant-kicker i{color:#b99cff}.hostel .live-quadrant-kicker i{color:#59e0ae}.live-quadrant-head h2{margin:6px 0 0;color:#fff;font-size:clamp(.98rem,1.8vw,1.3rem);line-height:1.2}.live-quadrant-head p{margin:4px 0 0;color:#a6c4d4;font-size:.7rem}.live-slide-count{flex:0 0 auto;padding:5px 8px;border-radius:999px;background:rgba(255,255,255,.09);color:#bdd8e6;font-size:.65rem;font-weight:800}
    .live-quadrant-body{display:flex;flex:1;flex-direction:column;min-height:0;padding:13px 15px}.live-candidate-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;overflow:auto;min-height:0;padding:1px 2px 3px}.live-candidate{display:flex;align-items:center;gap:9px;min-width:0;padding:9px;border:1px solid rgba(195,230,247,.13);border-radius:12px;background:rgba(255,255,255,.055)}.live-rank{display:grid;place-items:center;width:25px;height:25px;flex:0 0 auto;border-radius:8px;background:rgba(110,204,245,.16);color:#b8e8fb;font-size:.67rem;font-weight:900}.live-candidate:first-child .live-rank{background:#f3bf5c;color:#573d00}.live-candidate img,.live-candidate-avatar{width:34px;height:34px;flex:0 0 auto;border-radius:50%;object-fit:cover}.live-candidate-avatar{display:grid;place-items:center;background:#204963;color:#acd7ea}.live-candidate-main{min-width:0;flex:1}.live-candidate-main strong{display:block;overflow:hidden;color:#f5fbff;font-size:.73rem;text-overflow:ellipsis;white-space:nowrap}.live-candidate-main small{display:block;overflow:hidden;margin-top:2px;color:#9cbaca;font-size:.62rem;text-overflow:ellipsis;white-space:nowrap}.live-votes{flex:0 0 auto;text-align:right}.live-votes strong{display:block;color:#9ff0ce;font-size:.9rem;font-variant-numeric:tabular-nums}.live-votes small{display:block;color:#8aa9b9;font-size:.57rem}.live-empty{display:grid;place-items:center;flex:1;min-height:130px;color:#8eafc1;text-align:center;font-size:.78rem}.live-empty i{display:block;margin-bottom:7px;color:#5c89a4;font-size:1.5rem}.live-progress{height:3px;margin-top:11px;overflow:hidden;border-radius:99px;background:rgba(255,255,255,.1)}.live-progress span{display:block;width:35%;height:100%;border-radius:inherit;background:#53c8f1;animation:live-progress 5s linear infinite}.faculty .live-progress span{background:#ad8eff}.hostel .live-progress span{background:#55dcaa}@keyframes live-progress{from{transform:translateX(-110%)}to{transform:translateX(300%)}}
    .live-wall-foot{position:relative;z-index:1;display:flex;justify-content:space-between;gap:12px;padding:8px 22px;color:#789bac;font-size:.63rem}.live-wall-foot strong{color:#a5c6d7}
    @media(max-width:800px){.live-wall-head{padding:11px 13px}.live-wall-brand img{width:34px;height:34px}.live-wall-brand strong{font-size:.84rem}.live-wall-brand small{font-size:.61rem}.live-wall-clock{display:none}.live-wall-exit{padding:7px 8px;font-size:0}.live-wall-exit i{font-size:.8rem}.live-wall-grid{gap:9px;padding:9px}.live-quadrant{border-radius:13px}.live-quadrant-head{padding:10px 11px}.live-quadrant-head h2{font-size:.9rem}.live-quadrant-head p{font-size:.62rem}.live-quadrant-body{padding:9px}.live-candidate-list{grid-template-columns:1fr;gap:6px}.live-candidate{padding:7px}.live-wall-foot{padding:6px 12px;font-size:.58rem}}
    @media(max-width:560px){.live-wall-grid{grid-template-columns:1fr;grid-template-rows:repeat(4,minmax(190px,1fr));overflow:auto}.live-wall{overflow:auto}.live-wall-grid{flex:none}.live-quadrant{min-height:190px}.live-wall-foot{display:none}}
</style>

<style>
    .live-head-main{min-width:0;flex:1}.live-head-side{display:flex;flex:0 0 min(48%,270px);flex-direction:column;align-items:stretch;gap:7px}.live-head-side .live-counts{width:100%;margin:0}.live-head-side .live-slide-count{align-self:flex-end}
    .live-counts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;margin-bottom:10px}.live-counts span{display:flex;align-items:baseline;justify-content:center;gap:5px;padding:6px 5px;border:1px solid rgba(195,230,247,.12);border-radius:9px;background:rgba(255,255,255,.045)}.live-counts strong{color:#dff6ff;font-size:.82rem;font-variant-numeric:tabular-nums}.live-counts small{color:#91b1c1;font-size:.58rem;font-weight:700}.live-counts span:nth-child(2) strong{color:#8af0c6}.live-counts span:nth-child(3) strong{color:#ffd18a}
    .live-candidate-list{grid-template-columns:repeat(auto-fit,minmax(220px,1fr));scrollbar-width:thin;scrollbar-color:#3c86a8 rgba(255,255,255,.08)}
    .live-candidate-list::-webkit-scrollbar{width:6px;height:6px}.live-candidate-list::-webkit-scrollbar-thumb{border-radius:99px;background:#3c86a8}.live-candidate-list::-webkit-scrollbar-track{background:rgba(255,255,255,.06)}
    @media(max-width:800px){.live-candidate-list{grid-template-columns:1fr}}
    @media(max-width:560px){.live-wall-grid{grid-template-rows:repeat(4,minmax(300px,auto));overflow:visible}.live-wall{overflow:auto}.live-wall-grid{flex:none}.live-quadrant{min-height:300px}.live-candidate-list{max-height:230px}.live-head-side{flex-basis:52%;gap:5px}.live-counts{gap:3px}.live-counts span{display:grid;gap:1px;padding:5px 3px;text-align:center}.live-counts strong{font-size:.76rem}.live-wall-foot{display:none}}
</style>

<div class="live-wall" data-live-wall data-feed-url="{{ url('election live/data') }}">
    <header class="live-wall-head">
        <div class="live-wall-brand"><img src="{{ asset('uploads/logo.png') }}" alt="University logo"><div><strong>UNIMAID Student Elections</strong><small>Live results centre · updates automatically</small></div></div>
        <div class="live-wall-meta"><span class="live-wall-status {{ strtoupper((string) DB::table('election_settings')->where('description', 'Election Flag')->value('value')) === 'OPEN' ? '' : 'closed' }}" data-live-status><i class="fas fa-circle"></i><span data-live-status-text>{{ strtoupper((string) DB::table('election_settings')->where('description', 'Election Flag')->value('value')) === 'OPEN' ? 'Voting open' : 'Voting closed' }}</span></span><span class="live-wall-clock" data-live-clock></span><a class="live-wall-exit" href="{{ url('election candidates') }}" title="Exit live wall"><i class="fas fa-arrow-right-from-bracket"></i><span>Exit</span></a></div>
    </header>
    <main class="live-wall-grid">
        <section class="live-quadrant primary" data-live-slot="primary"></section>
        <section class="live-quadrant general" data-live-slot="general"></section>
        <section class="live-quadrant faculty" data-live-slot="faculty"></section>
        <section class="live-quadrant hostel" data-live-slot="hostel"></section>
    </main>
    <footer class="live-wall-foot"><span><strong data-live-last-update>Just now</strong> · vote totals refresh automatically</span><span>Each panel advances every 5 seconds</span></footer>
</div>

<script>
(() => {
    const root = document.querySelector('[data-live-wall]');
    if (!root || root.dataset.ready === '1') return;
    root.dataset.ready = '1';
    document.body.classList.add('live-wall-open');
    const feedUrl = root.dataset.feedUrl;
    const cadenceLabel = root.querySelector('.live-wall-foot span:last-child');
    if (cadenceLabel) cadenceLabel.textContent = 'Each panel advances every 10 seconds';
    const initial = { candidates: @json($liveCandidates), positions: @json($livePositions), stats: @json($liveStatsPayload), status: @json(strtoupper((string) DB::table('election_settings')->where('description', 'Election Flag')->value('value'))) };
    const state = { ...initial, indices: { general: 0, faculty: 0, hostel: 0 } };
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const text = value => String(value ?? '').trim();
    const norm = value => text(value).toLowerCase();
    const number = value => Number(value || 0).toLocaleString();
    const positionsFor = category => (state.positions || []).filter(position => norm(position.category) === norm(category)).sort((a,b) => Number(a.order || 999) - Number(b.order || 999));
    const candidatesFor = category => (state.candidates || []).filter(candidate => norm(candidate.category) === norm(category));
    const candidateCards = candidates => candidates.slice().sort((a,b) => Number(b.vote || 0) - Number(a.vote || 0)).map((candidate, index) => {
        const name = candidate.name || candidate.candidate || 'Candidate';
        const id = candidate.candidate || '';
        const picture = text(candidate.picture);
        const avatar = picture ? `<img src="{{ asset('storage/picture') }}/${encodeURIComponent(picture)}" alt="" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='grid'"><span class="live-candidate-avatar" style="display:none"><i class="fas fa-user"></i></span>` : '<span class="live-candidate-avatar"><i class="fas fa-user"></i></span>';
        return `<article class="live-candidate"><span class="live-rank">${index + 1}</span>${avatar}<div class="live-candidate-main"><strong>${esc(name)}</strong><small>${esc(id)}</small></div><div class="live-votes"><strong>${number(candidate.vote)}</strong><small>votes</small></div></article>`;
    }).join('');
    const groupBy = (rows, keyer, title, subtitle) => {
        const map = new Map();
        rows.forEach(row => { const key = keyer(row); if (!map.has(key)) map.set(key, { key, title: title(row), subtitle: subtitle(row), candidates: [] }); map.get(key).candidates.push(row); });
        return [...map.values()];
    };
    const generalGroups = () => {
        const rows = candidatesFor('General');
        const positions = positionsFor('General');
        const primaryPosition = positions.find(position => Number(position.order) === 1) || positions[0];
        const primaryName = primaryPosition?.position || rows[0]?.position || 'First office';
        const primary = rows.filter(row => norm(row.position) === norm(primaryName));
        const others = rows.filter(row => norm(row.position) !== norm(primaryName));
        const order = new Map(positions.map((position, index) => [norm(position.position), Number(position.order) || index + 10]));
        const groups = groupBy(others, row => norm(row.position), row => row.position || 'General office', row => 'General election');
        groups.sort((a,b) => (order.get(norm(a.title)) || 999) - (order.get(norm(b.title)) || 999));
        return { primaryName, primary, groups };
    };
    const facultyGroups = () => groupBy(candidatesFor('Faculty Rep'), row => `${norm(row.faculty)}|${norm(row.level || row.position)}`, row => row.faculty_title || row.faculty || 'Faculty', row => `${row.level || row.position || 'Level'} - ${row.position || 'Faculty representative'}`);
    const hostelGroups = () => groupBy(candidatesFor('Hostel Rep'), row => `${norm(row.hostel)}|${norm(row.position)}`, row => row.hostel || 'Hostel', row => row.position || 'Hostel representative');
    const legacyPanel = (slot, kicker, icon, title, subtitle, candidates, slide, total, emptyText) => { const node = root.querySelector(`[data-live-slot="${slot}"]`); if (!node) return; node.innerHTML = `<div class="live-quadrant-head"><div><div class="live-quadrant-kicker"><i class="fas ${icon}"></i>${esc(kicker)}</div><h2>${esc(title)}</h2><p>${esc(subtitle)}</p></div>${total > 1 ? `<span class="live-slide-count">${slide + 1} / ${total}</span>` : ''}</div><div class="live-quadrant-body">${candidates.length ? `<div class="live-candidate-list">${candidateCards(candidates)}</div>` : `<div class="live-empty"><div><i class="fas fa-users-slash"></i><div>${esc(emptyText)}</div></div></div>`}<div class="live-progress"><span></span></div></div>`; };
    const statsFor = (section, key = null) => key && state.stats?.[section]?.[key] ? state.stats[section][key] : (section === 'general' ? (state.stats?.general || {}) : {});
    const panel = (slot, kicker, icon, title, subtitle, candidates, slide, total, emptyText, stats = {}) => { const node = root.querySelector(`[data-live-slot="${slot}"]`); if (!node) return; if (!Object.keys(stats).length) stats = slot === 'faculty' ? (state.stats?.faculty?.[state._facultyStatsKey] || {}) : slot === 'hostel' ? (state.stats?.hostel?.[state._hostelStatsKey] || {}) : (state.stats?.general || {}); const accredited = Number(stats.accredited || 0), voted = Number(stats.voted || 0), remaining = Number(stats.remaining ?? Math.max(0, accredited - voted)); node.innerHTML = `<div class="live-quadrant-head"><div class="live-head-main"><div class="live-quadrant-kicker"><i class="fas ${icon}"></i>${esc(kicker)}</div><h2>${esc(title)}</h2><p>${esc(subtitle)}</p></div><div class="live-head-side"><div class="live-counts" aria-label="Voter participation"><span><strong>${number(accredited)}</strong><small>Accredited</small></span><span><strong>${number(voted)}</strong><small>Voted</small></span><span><strong>${number(remaining)}</strong><small>Remaining</small></span></div>${total > 1 ? `<span class="live-slide-count">${slide + 1} / ${total}</span>` : ''}</div></div><div class="live-quadrant-body">${candidates.length ? `<div class="live-candidate-list">${candidateCards(candidates)}</div>` : `<div class="live-empty"><div><i class="fas fa-users-slash"></i><div>${esc(emptyText)}</div></div></div>`}<div class="live-progress"><span></span></div></div>`; };
    const render = () => {
        const general = generalGroups();
        panel('primary', 'General election · office 1', 'fa-trophy', general.primaryName, 'Leading candidates', general.primary, 0, 1, 'No candidates registered for the first office.');
        const generalSlide = general.groups.length ? general.groups[state.indices.general % general.groups.length] : null;
        panel('general', 'General election · rotating offices', 'fa-arrows-rotate', generalSlide?.title || 'General offices', generalSlide?.subtitle || 'Waiting for candidate records', generalSlide?.candidates || [], state.indices.general % Math.max(general.groups.length, 1), general.groups.length, 'No other general offices have candidates yet.');
        const faculties = facultyGroups();
        const facultySlide = faculties.length ? faculties[state.indices.faculty % faculties.length] : null;
        state._facultyStatsKey = facultySlide?.key || '';
        panel('faculty', 'Faculty representatives · rotating', 'fa-building-columns', facultySlide?.title || 'Faculty representatives', facultySlide?.subtitle || 'Waiting for candidate records', facultySlide?.candidates || [], state.indices.faculty % Math.max(faculties.length, 1), faculties.length, 'No faculty representative candidates have been registered.');
        const hostels = hostelGroups();
        const hostelSlide = hostels.length ? hostels[state.indices.hostel % hostels.length] : null;
        state._hostelStatsKey = norm(hostelSlide?.title);
        panel('hostel', 'Hostel representatives · rotating', 'fa-house', hostelSlide?.title || 'Hostel representatives', hostelSlide?.subtitle || 'Waiting for candidate records', hostelSlide?.candidates || [], state.indices.hostel % Math.max(hostels.length, 1), hostels.length, 'No hostel representative candidates have been registered.');
        const status = norm(state.status) === 'open'; const statusNode = root.querySelector('[data-live-status]'); if (statusNode) statusNode.classList.toggle('closed', !status); const statusText = root.querySelector('[data-live-status-text]'); if (statusText) statusText.textContent = status ? 'Voting open' : 'Voting closed';
        const updated = root.querySelector('[data-live-last-update]'); if (updated) updated.textContent = `Updated ${new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', second:'2-digit'})}`;
    };
    const advance = () => { ['general','faculty','hostel'].forEach(slot => state.indices[slot] += 1); render(); };
    const poll = async () => { try { const response = await fetch(`${feedUrl}?_=${Date.now()}`, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, cache:'no-store'}); if (!response.ok) return; const payload = await response.json(); if (Array.isArray(payload.candidates)) state.candidates = payload.candidates; if (Array.isArray(payload.positions)) state.positions = payload.positions; if (payload.stats) state.stats = payload.stats; if (payload.status) state.status = payload.status; render(); } catch (error) { /* keep the last successful live snapshot on transient network errors */ } };
    const clock = root.querySelector('[data-live-clock]'); const tick = () => { if (clock) clock.textContent = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}); }; tick(); setInterval(tick, 1000); render(); setInterval(advance, 10000); setInterval(poll, 10000); 
})();
</script>
