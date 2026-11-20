(() => {
    'use strict';
    const root = document.getElementById('result-approval');
    if (!root) return;
    const find = selector => root.querySelector(selector);
    const dialog = find('#ra-decision-dialog');
    const form = find('#ra-decision-form');
    const confirm = find('#ra-confirm-decision');
    const feedback = find('#ra-decision-feedback');
    const progress = find('.ra-progress-area');
    let busy = false;
    let completed = false;
    let opener = null;

    function openDialog(element, button) {
        opener = button;
        element.showModal();
    }
    root.querySelectorAll('dialog').forEach(element => {
        element.addEventListener('cancel', event => {
            if (busy && element === dialog) event.preventDefault();
        });
        element.addEventListener('close', () => {
            if (opener) opener.focus();
            if (element === dialog && completed) location.reload();
        });
    });
    root.addEventListener('click', event => {
        const close = event.target.closest('[data-close-dialog]');
        if (close && !busy) close.closest('dialog').close();
        const department = event.target.closest('[data-department-code]');
        if (department) {
            form.reset();
            completed = false;
            find('#ra-decision-dept').value = department.dataset.departmentCode;
            find('#ra-decision-department').textContent = department.dataset.departmentTitle;
            find('#ra-decision-count').textContent = Number(department.dataset.total).toLocaleString() + ' course batches';
            feedback.hidden = true;
            progress.hidden = true;
            find('#ra-refresh-list').hidden = true;
            if (confirm) { confirm.hidden = false; confirm.disabled = false; confirm.textContent = 'Confirm decision'; }
            openDialog(dialog, department);
        }
        const remark = event.target.closest('[data-comment]');
        if (remark) {
            find('#ra-comment-title').textContent = remark.dataset.course + ' — Remarks';
            find('#ra-comment-text').textContent = remark.dataset.comment;
            openDialog(find('#ra-comment-dialog'), remark);
        }
        const adjust = event.target.closest('[data-adjust]');
        if (adjust) {
            const data = JSON.parse(adjust.dataset.adjust);
            const marks = find('#ra-marks-dialog');
            marks.querySelector('form').reset();
            ['code', 'session', 'semester'].forEach(key => { marks.querySelector('[name="' + key + '"]').value = data[key]; });
            find('#ra-marks-course').textContent = [data.code, data.session, data.semester].join(' · ');
            openDialog(marks, adjust);
        }
    });
    const departmentSearch = find('#ra-department-search');
    if (departmentSearch) departmentSearch.addEventListener('input', () => {
        let visible = 0;
        const term = departmentSearch.value.trim().toLowerCase();
        root.querySelectorAll('[data-department-code]').forEach(button => {
            button.hidden = !(button.dataset.departmentTitle + ' ' + button.dataset.departmentCode).toLowerCase().includes(term);
            if (!button.hidden) visible++;
        });
        find('#ra-no-department').hidden = visible > 0;
    });

    // Rebuild dependent lists from their original options, keeping stored
    // selections on initial load and clearing only incompatible selections.
    const faculty = find('#ra-faculty');
    const department = find('#ra-department');
    const programme = find('#ra-program');
    const departmentOptions = Array.from(department.options).map(option => option.cloneNode(true));
    const programmeOptions = Array.from(programme.options).map(option => option.cloneNode(true));
    function populate(select, options, predicate) {
        const selected = select.value;
        select.replaceChildren(...options.filter(option => !option.value || predicate(option)).map(option => option.cloneNode(true)));
        if (Array.from(select.options).some(option => option.value === selected)) select.value = selected;
        else select.value = '';
    }
    function cascade() {
        populate(department, departmentOptions, option => !faculty.value || option.dataset.faculty === faculty.value);
        populate(programme, programmeOptions, option => (!faculty.value || option.dataset.faculty === faculty.value) && (!department.value || option.dataset.department === department.value));
    }
    faculty.addEventListener('change', cascade);
    department.addEventListener('change', cascade);
    cascade();

    function setBusy(value) {
        busy = value;
        dialog.querySelectorAll('[data-close-dialog], [name="status"]').forEach(element => { element.disabled = value; });
        if (confirm) {
            confirm.disabled = value;
            if (value) confirm.innerHTML = '<span class="ra-spin" aria-hidden="true"></span> Processing';
            else confirm.textContent = 'Confirm decision';
        }
    }
    async function request(url, options = {}) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 60000);
        try {
            const response = await fetch(url, {credentials: 'same-origin', cache: 'no-store', ...options, signal: controller.signal,
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(options.headers || {})}});
            const data = await response.json().catch(() => null);
            if (response.redirected || response.status === 401 || response.status === 419) throw new Error('Your session has expired. Sign in again and reopen the approval page.');
            if (!response.ok || !data || data.status === 'error') {
                const validation = data && data.errors ? Object.values(data.errors).flat().join(' ') : '';
                throw new Error(validation || (data && data.message) || 'The decision could not be completed. Refresh the list to check the remaining results before trying again.');
            }
            return data;
        } finally { clearTimeout(timer); }
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || !form.reportValidity()) return;
        const payload = new FormData(form);
        setBusy(true);
        feedback.hidden = true;
        progress.hidden = false;
        const bar = progress.querySelector('[role="progressbar"]');
        bar.firstElementChild.style.width = '0%';
        bar.setAttribute('aria-valuenow', '0');
        find('#ra-progress-text').textContent = 'Preparing results…';
        try {
            let data = await request(form.action, {method: 'POST', body: payload});
            while (true) {
                const percent = data.status === 'complete' ? 100 : Math.max(0, Math.min(100, Number(data.progress) || 0));
                bar.firstElementChild.style.width = percent + '%';
                bar.setAttribute('aria-valuenow', String(percent));
                find('#ra-progress-text').textContent = percent + '% complete';
                if (data.status === 'complete') break;
                if (!data.nextBatchUrl) throw new Error('Processing paused. Refresh the list to check the remaining results.');
                const next = new URL(data.nextBatchUrl, location.href);
                if (next.origin !== location.origin) throw new Error('The next approval step could not be opened. Refresh the page and try again.');
                await new Promise(resolve => setTimeout(resolve, 250));
                data = await request(next.href);
            }
            completed = true;
            feedback.className = 'ra-notice ra-notice-success';
            feedback.textContent = data.message || 'Your decision has been recorded. Return to the list to continue your review.';
            feedback.hidden = false;
            if (confirm) confirm.hidden = true;
            find('#ra-refresh-list').hidden = false;
        } catch (error) {
            feedback.className = 'ra-notice ra-notice-error';
            feedback.textContent = error.name === 'AbortError'
                ? 'The request took longer than expected. Refresh the list to check progress before trying again.'
                : error.message;
            feedback.hidden = false;
            find('#ra-refresh-list').hidden = false;
        } finally { setBusy(false); }
    });
    find('#ra-refresh-list').addEventListener('click', () => location.reload());
    window.addEventListener('beforeunload', event => { if (busy) { event.preventDefault(); event.returnValue = ''; } });
    window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });
})();
