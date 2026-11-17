<style>
    .hod-support-page {
        --hs-ink: #18324f;
        --hs-muted: #6f8197;
        --hs-blue: #1677c8;
        --hs-blue-dark: #0f5da2;
        --hs-soft: #eef6ff;
        --hs-border: #dce8f3;
        color: var(--hs-ink);
    }

    .hod-support-page .hs-hero {
        border: 0;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(135deg, #0d5e9f 0%, #218bd2 56%, #48afe8 100%);
        box-shadow: 0 14px 34px rgba(13, 94, 159, .18);
        color: #fff;
    }

    .hod-support-page .hs-hero-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 28px 30px;
    }

    .hod-support-page .hs-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: rgba(255, 255, 255, .78);
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .hod-support-page .hs-hero h2 {
        margin: 0 0 8px;
        color: #fff;
        font-size: clamp(1.45rem, 3vw, 2.15rem);
        font-weight: 800;
    }

    .hod-support-page .hs-hero p {
        max-width: 700px;
        margin: 0;
        color: rgba(255, 255, 255, .88);
        font-size: .98rem;
        line-height: 1.6;
    }

    .hod-support-page .hs-hero-icon {
        display: grid;
        width: 76px;
        height: 76px;
        flex: 0 0 76px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 22px;
        background: rgba(255, 255, 255, .16);
        color: #fff;
        font-size: 2rem;
    }

    .hod-support-page .hs-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin: 18px 0;
    }

    .hod-support-page .hs-stat,
    .hod-support-page .hs-panel {
        border: 1px solid var(--hs-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(29, 67, 106, .06);
    }

    .hod-support-page .hs-stat {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 100px;
        padding: 17px;
    }

    .hod-support-page .hs-stat-icon {
        display: grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        border-radius: 14px;
        background: var(--hs-soft);
        color: var(--hs-blue);
        font-size: 1.15rem;
    }

    .hod-support-page .hs-stat strong {
        display: block;
        font-size: 1.45rem;
        line-height: 1.1;
    }

    .hod-support-page .hs-stat span {
        color: var(--hs-muted);
        font-size: .82rem;
    }

    .hod-support-page .hs-panel {
        overflow: hidden;
        margin-bottom: 18px;
    }

    .hod-support-page .hs-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 19px 22px;
        border-bottom: 1px solid #edf2f7;
    }

    .hod-support-page .hs-panel-head h3 {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 800;
    }

    .hod-support-page .hs-panel-head p {
        margin: 4px 0 0;
        color: var(--hs-muted);
        font-size: .84rem;
    }

    .hod-support-page .hs-current {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 22px;
        background: linear-gradient(180deg, #f9fcff 0%, #f1f8ff 100%);
    }

    .hod-support-page .hs-person {
        display: flex;
        align-items: center;
        min-width: 0;
        gap: 13px;
    }

    .hod-support-page .hs-avatar {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        place-items: center;
        overflow: hidden;
        border-radius: 15px;
        background: #dceeff;
        color: var(--hs-blue);
        font-size: 1.15rem;
        font-weight: 800;
    }

    .hod-support-page .hs-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hod-support-page .hs-person strong,
    .hod-support-page .hs-person small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hod-support-page .hs-person strong { font-size: .98rem; }
    .hod-support-page .hs-person small { margin-top: 3px; color: var(--hs-muted); }

    .hod-support-page .hs-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 999px;
        background: #e4f7ec;
        color: #147a42;
        font-size: .73rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .hod-support-page .hs-panel-body { padding: 18px 22px; }

    .hod-support-page .hs-search {
        position: relative;
        max-width: 420px;
        margin-bottom: 14px;
    }

    .hod-support-page .hs-search i {
        position: absolute;
        top: 50%;
        left: 14px;
        color: #8da1b4;
        transform: translateY(-50%);
    }

    .hod-support-page .hs-search input {
        height: 43px;
        padding-left: 40px;
        border-color: var(--hs-border);
        border-radius: 12px;
    }

    .hod-support-page .hs-table-wrap { overflow-x: auto; }

    .hod-support-page table { width: 100%; min-width: 690px; border-collapse: separate; border-spacing: 0; }
    .hod-support-page thead th {
        padding: 11px 13px;
        border-bottom: 1px solid var(--hs-border);
        background: #f7fbff;
        color: #60758a;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .hod-support-page tbody td { padding: 13px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
    .hod-support-page tbody tr:last-child td { border-bottom: 0; }
    .hod-support-page .hs-staff-name { font-weight: 750; }
    .hod-support-page .hs-muted { color: var(--hs-muted); font-size: .82rem; }
    .hod-support-page .hs-responsibility { color: #466078; font-size: .82rem; }
    .hod-support-page .hs-btn { border-radius: 10px; font-weight: 700; }
    .hod-support-page .hs-btn:disabled { cursor: not-allowed; opacity: .56; }
    .hod-support-page .hs-empty { padding: 38px 20px; text-align: center; color: var(--hs-muted); }
    .hod-support-page .hs-empty i { display: block; margin-bottom: 10px; color: #9bb2c9; font-size: 2rem; }

    @media (max-width: 767px) {
        .hod-support-page .hs-hero-inner { align-items: flex-start; padding: 23px 20px; }
        .hod-support-page .hs-hero-icon { width: 54px; height: 54px; flex-basis: 54px; border-radius: 16px; font-size: 1.35rem; }
        .hod-support-page .hs-stats { grid-template-columns: 1fr; gap: 10px; }
        .hod-support-page .hs-stat { min-height: 78px; }
        .hod-support-page .hs-panel-head,
        .hod-support-page .hs-current { align-items: flex-start; flex-direction: column; padding: 17px; }
        .hod-support-page .hs-panel-body { padding: 15px 17px; }
        .hod-support-page .hs-current .btn { width: 100%; }

        /* On small screens each staff member becomes a readable card. This
           avoids sideways scrolling and keeps the action easy to reach. */
        .hod-support-page .hs-table-wrap { overflow: visible; }
        .hod-support-page .hs-table-wrap table { display: block; min-width: 0; }
        .hod-support-page .hs-table-wrap thead { display: none; }
        .hod-support-page .hs-table-wrap tbody { display: grid; gap: 12px; }
        .hod-support-page .hs-table-wrap tbody tr {
            display: grid;
            gap: 10px;
            padding: 14px;
            border: 1px solid var(--hs-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(29, 67, 106, .06);
        }
        .hod-support-page .hs-table-wrap tbody td {
            display: block;
            padding: 0;
            border: 0;
            text-align: left !important;
        }
        .hod-support-page .hs-table-wrap tbody td[data-label]::before {
            display: block;
            margin-bottom: 4px;
            color: #8294a8;
            content: attr(data-label);
            font-size: .66rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
        }
        .hod-support-page .hs-table-wrap tbody td[data-label="Action"] {
            padding-top: 10px;
            border-top: 1px solid #edf2f7;
        }
        .hod-support-page .hs-table-wrap tbody td[data-label="Action"] .btn { width: 100%; }
        .hod-support-page .hs-table-wrap .hs-person { width: 100%; }
        .hod-support-page .hs-table-wrap .hs-person strong,
        .hod-support-page .hs-table-wrap .hs-person small { white-space: normal; }
    }
</style>

<div class="main-body hod-support-page">
    <div class="page-wrapper">
        <div class="row">
            <div class="col-12">
                <section class="hs-hero">
                    <div class="hs-hero-inner">
                        <div>
                            <div class="hs-kicker"><i class="fas fa-users"></i> Department support</div>
                            <h2>Choose your department support officer</h2>
                            <p>Select one staff member to help coordinate departmental work. Only one person can hold this responsibility at a time.</p>
                        </div>
                        <div class="hs-hero-icon" aria-hidden="true"><i class="fas fa-user-check"></i></div>
                    </div>
                </section>
            </div>
        </div>

        <div class="hs-stats" aria-label="Department support summary">
            <div class="hs-stat"><span class="hs-stat-icon"><i class="fas fa-users"></i></span><div><strong>{{ $staffMembers->count() }}</strong><span>Staff in {{ $department->title }}</span></div></div>
            <div class="hs-stat"><span class="hs-stat-icon"><i class="fas fa-user-tie"></i></span><div><strong>{{ $dso ? '1' : '0' }}</strong><span>Support officer appointed</span></div></div>
            <div class="hs-stat"><span class="hs-stat-icon"><i class="fas fa-user-plus"></i></span><div><strong>{{ $freeCount }}</strong><span>Staff available to choose</span></div></div>
        </div>

        <section class="hs-panel">
            <div class="hs-panel-head">
                <div>
                    <h3>Current support officer</h3>
                    <p>{{ $dso ? 'This person currently holds the department support responsibility.' : 'No support officer has been chosen yet.' }}</p>
                </div>
                @if ($dso)
                    <span class="hs-badge"><i class="fas fa-check-circle"></i> Active</span>
                @endif
            </div>
            @if ($dso)
                <div class="hs-current">
                    <div class="hs-person">
                        <span class="hs-avatar">
                            @if (!empty($dso->picture))
                                <img src="{{ asset('storage/picture/' . $dso->picture) }}" alt="">
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                        </span>
                        <span>
                            <strong>{{ $dso->name ?: 'Unnamed staff member' }}</strong>
                            <small>{{ $dso->username }}{{ $dso->designation ? ' · ' . $dso->designation : '' }}</small>
                        </span>
                    </div>
                    <form method="POST" action="{{ url('/hod staff roles/remove') }}" class="js-hs-form">
                        @csrf
                        <input type="hidden" name="staff_id" value="{{ $dso->id }}">
                        <button class="btn btn-outline-danger hs-btn" type="submit" data-confirm-action="Remove" data-confirm="Remove this responsibility from this staff member?">
                            <i class="fas fa-user-minus me-1"></i> Remove responsibility
                        </button>
                    </form>
                </div>
            @else
                <div class="hs-empty"><i class="fas fa-user-clock"></i><div>No staff member has been selected yet.</div><small>Choose someone below to assign the responsibility.</small></div>
            @endif
        </section>

        <section class="hs-panel">
            <div class="hs-panel-head">
                <div>
                    <h3>Staff in your department</h3>
                    <p>Staff with another responsibility cannot be assigned a second one.</p>
                </div>
            </div>
            <div class="hs-panel-body">
                <div class="hs-search">
                    <i class="fas fa-search"></i>
                    <input type="search" class="form-control" id="hsStaffSearch" placeholder="Search by name or staff number" autocomplete="off">
                </div>
                <div class="hs-table-wrap">
                    <table>
                        <thead><tr><th>Staff member</th><th>Staff category</th><th>Current responsibility</th><th class="text-end">Action</th></tr></thead>
                        <tbody id="hsStaffRows">
                            @forelse ($staffMembers as $staff)
                                @php
                                    $responsibility = strtoupper(trim((string) ($staff->appointment ?? '')));
                                    $isFree = in_array($responsibility, ['', 'STAFF'], true);
                                    $isDso = $responsibility === 'DSO';
                                @endphp
                                <tr data-search="{{ strtolower(($staff->name ?? '') . ' ' . ($staff->username ?? '') . ' ' . ($staff->staff_category ?? '')) }}">
                                    <td>
                                        <div class="hs-person">
                                            <span class="hs-avatar">
                                                @if (!empty($staff->picture))
                                                    <img src="{{ asset('storage/picture/' . $staff->picture) }}" alt="">
                                                @else
                                                    <i class="fas fa-user"></i>
                                                @endif
                                            </span>
                                            <span><strong class="hs-staff-name">{{ $staff->name ?: 'Unnamed staff member' }}</strong><small class="hs-muted">{{ $staff->username }}{{ $staff->designation ? ' · ' . $staff->designation : '' }}</small></span>
                                        </div>
                                    </td>
                                    <td class="hs-muted" data-label="Staff category">{{ $staff->staff_category ?: 'Staff member' }}</td>
                                    <td class="hs-responsibility" data-label="Current responsibility">
                                        @if ($isDso)
                                            <span class="hs-badge"><i class="fas fa-check-circle"></i> Department support officer</span>
                                        @elseif ($isFree)
                                            No special responsibility
                                        @else
                                            {{ ucwords(strtolower((string) $staff->appointment)) }}
                                        @endif
                                    </td>
                                    <td class="text-end" data-label="Action">
                                        @if ($isFree && !$dso)
                                            <form method="POST" action="{{ url('/hod staff roles/assign') }}" class="d-inline js-hs-form">
                                                @csrf
                                                <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                                <button class="btn btn-primary btn-sm hs-btn" type="submit" data-confirm-action="Assign" data-confirm="Assign this staff member as your department support officer?">
                                                    <i class="fas fa-user-check me-1"></i> Assign
                                                </button>
                                            </form>
                                        @elseif ($isDso)
                                            <span class="hs-muted">Currently selected</span>
                                        @elseif ($isFree)
                                            <button class="btn btn-light btn-sm hs-btn" type="button" disabled>One position filled</button>
                                        @else
                                            <span class="hs-muted">Already assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="hs-empty"><i class="fas fa-users-slash"></i><div>No active staff were found in your department.</div></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="hsNoMatches" class="hs-empty d-none"><i class="fas fa-search"></i><div>No staff match your search.</div></div>
            </div>
        </section>
    </div>
</div>

<script>
    (function () {
        const search = document.getElementById('hsStaffSearch');
        const rows = Array.from(document.querySelectorAll('#hsStaffRows tr[data-search]'));
        const noMatches = document.getElementById('hsNoMatches');

        if (search) {
            search.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                let visible = 0;
                rows.forEach(function (row) {
                    const match = !query || row.dataset.search.includes(query);
                    row.classList.toggle('d-none', !match);
                    if (match) visible++;
                });
                if (noMatches) noMatches.classList.toggle('d-none', visible !== 0 || rows.length === 0);
            });
        }

        document.querySelectorAll('.js-hs-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const button = form.querySelector('button[type="submit"]');
                const message = button && button.dataset.confirm;
                const actionLabel = (button && button.dataset.confirmAction) || 'Continue';
                const continueSubmit = function () {
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Saving...';
                    }
                    form.submit();
                };

                if (!message) {
                    continueSubmit();
                    event.preventDefault();
                    return;
                }

                event.preventDefault();

                if (typeof swal === 'function') {
                    swal({
                        title: actionLabel === 'Remove' ? 'Remove responsibility?' : 'Confirm assignment',
                        text: message,
                        icon: 'warning',
                        buttons: {
                            cancel: {
                                text: 'Keep it',
                                value: null,
                                visible: true,
                                closeModal: true
                            },
                            confirm: {
                                text: actionLabel,
                                value: true,
                                visible: true,
                                className: 'swal-button--danger',
                                closeModal: true
                            }
                        },
                        dangerMode: true
                    }).then(function (confirmed) {
                        if (confirmed) continueSubmit();
                    });
                    return;
                }

                // Safe fallback if the shared alert script is unavailable.
                if (window.confirm(message)) {
                    continueSubmit();
                }
            });
        });
    })();
</script>
