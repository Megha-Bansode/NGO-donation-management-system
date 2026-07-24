/**
 * DONOR MODULE — Enhancement JavaScript
 * Author: Radhika | Scope: donor_*.php pages only
 * Dependencies: dashboard.js (loads first)
 */

document.addEventListener('DOMContentLoaded', () => {

    const page = document.body.getAttribute('data-donor-page');

    /* ================================================================
       GLOBAL HELPERS
    ================================================================ */

    /**
     * Convert absolute date string to a human-readable relative time.
     */
    function relativeTime(dateStr) {
        const date = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(date.getTime())) return dateStr;
        const diff = Math.floor((Date.now() - date.getTime()) / 1000);
        if (diff < 60)   return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
        if (diff < 604800) return Math.floor(diff / 86400) + ' day' + (Math.floor(diff / 86400) > 1 ? 's' : '') + ' ago';
        return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    /**
     * Auto-dismiss donor alerts after a delay.
     */
    function autoDismissAlerts(selector, delay) {
        document.querySelectorAll(selector).forEach(el => {
            setTimeout(() => {
                el.classList.add('dismissing');
                setTimeout(() => { el.remove(); }, 500);
            }, delay);
        });
    }

    // Auto-dismiss success alerts (5 s) and error alerts (8 s)
    autoDismissAlerts('.donor-alert-success', 5000);
    autoDismissAlerts('.donor-alert-error', 8000);

    /* ================================================================
       ANIMATED PROGRESS BARS
       Set CSS var --progress-target on .donor-progress-fill elements.
    ================================================================ */
    document.querySelectorAll('.donor-progress-fill, .donor-progress-fill-lg').forEach(el => {
        const target = el.getAttribute('data-progress') || '0';
        el.style.setProperty('--progress-target', target + '%');
        el.style.width = target + '%';
    });

    /* ================================================================
       PAGE: DASHBOARD
    ================================================================ */
    if (page === 'dashboard') {
        // Nothing extra needed — dashboard.js handles counters
    }

    /* ================================================================
       PAGE: CAMPAIGNS
    ================================================================ */
    if (page === 'campaigns') {
        const searchInput  = document.getElementById('campaignSearch');
        const catFilter    = document.getElementById('campaignCatFilter');
        const cards        = Array.from(document.querySelectorAll('.campaign-card-wrapper'));
        const resultCount  = document.getElementById('campaignResultCount');

        function filterCampaigns() {
            const q   = searchInput  ? searchInput.value.toLowerCase().trim()  : '';
            const cat = catFilter    ? catFilter.value.toLowerCase().trim()     : '';
            let visible = 0;
            cards.forEach(wrapper => {
                const title = (wrapper.getAttribute('data-title') || '').toLowerCase();
                const catName = (wrapper.getAttribute('data-cat') || '').toLowerCase();
                const matchQ   = !q   || title.includes(q);
                const matchCat = !cat || catName === cat;
                const show = matchQ && matchCat;
                wrapper.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (resultCount) {
                resultCount.textContent = visible + ' campaign' + (visible !== 1 ? 's' : '') + ' found';
            }
            // Show no-results message
            const noResult = document.getElementById('campaignNoResults');
            if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
        }

        if (searchInput) searchInput.addEventListener('input', filterCampaigns);
        if (catFilter)   catFilter.addEventListener('change', filterCampaigns);
    }

    /* ================================================================
       PAGE: DONATIONS (search, filter, pagination)
    ================================================================ */
    if (page === 'donations') {
        const searchInput  = document.getElementById('donationSearch');
        const statusFilter = document.getElementById('donationStatusFilter');
        const dateFilter   = document.getElementById('donationDateFilter');
        const tableBody    = document.getElementById('donationsTableBody');
        const paginationEl = document.getElementById('donationPagination');
        const pageInfoEl   = document.getElementById('donationPageInfo');

        const PAGE_SIZE = 10;
        let currentPage = 1;
        let filteredRows = [];

        function getAllRows() {
            return tableBody ? Array.from(tableBody.querySelectorAll('tr[data-row]')) : [];
        }

        function filterDonations() {
            const q      = searchInput  ? searchInput.value.toLowerCase().trim()   : '';
            const status = statusFilter ? statusFilter.value.toLowerCase().trim()  : '';
            const period = dateFilter   ? dateFilter.value                          : '';
            const now    = new Date();

            filteredRows = getAllRows().filter(row => {
                const txn      = (row.getAttribute('data-txn')    || '').toLowerCase();
                const campaign = (row.getAttribute('data-campaign')|| '').toLowerCase();
                const st       = (row.getAttribute('data-status') || '').toLowerCase();
                const dateStr  = row.getAttribute('data-date')    || '';
                const rowDate  = dateStr ? new Date(dateStr) : null;

                const matchQ      = !q      || txn.includes(q) || campaign.includes(q);
                const matchStatus = !status || st === status;
                let matchDate     = true;
                if (period === '30' && rowDate) {
                    matchDate = (now - rowDate) / 86400000 <= 30;
                } else if (period === '90' && rowDate) {
                    matchDate = (now - rowDate) / 86400000 <= 90;
                } else if (period === '365' && rowDate) {
                    matchDate = (now - rowDate) / 86400000 <= 365;
                }
                return matchQ && matchStatus && matchDate;
            });

            currentPage = 1;
            renderPage();
        }

        function renderPage() {
            const all = getAllRows();
            // Hide all
            all.forEach(r => r.style.display = 'none');

            const start = (currentPage - 1) * PAGE_SIZE;
            const end   = start + PAGE_SIZE;
            const paginated = filteredRows.slice(start, end);
            paginated.forEach(r => r.style.display = '');

            // No results
            const noResult = document.getElementById('donationsNoResults');
            if (noResult) noResult.style.display = filteredRows.length === 0 ? '' : 'none';

            renderPagination();
        }

        function renderPagination() {
            if (!paginationEl) return;
            const totalPages = Math.ceil(filteredRows.length / PAGE_SIZE);
            paginationEl.innerHTML = '';

            if (pageInfoEl) {
                const start = Math.min((currentPage - 1) * PAGE_SIZE + 1, filteredRows.length);
                const end   = Math.min(currentPage * PAGE_SIZE, filteredRows.length);
                pageInfoEl.textContent = filteredRows.length > 0
                    ? `Showing ${start}–${end} of ${filteredRows.length} donation${filteredRows.length !== 1 ? 's' : ''}`
                    : 'No donations match your filters.';
            }

            if (totalPages <= 1) return;

            // Prev button
            const prev = createPageBtn('‹', currentPage === 1);
            prev.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderPage(); } });
            paginationEl.appendChild(prev);

            // Page number buttons
            for (let i = 1; i <= totalPages; i++) {
                const btn = createPageBtn(String(i), false);
                if (i === currentPage) btn.classList.add('active');
                btn.addEventListener('click', () => { currentPage = i; renderPage(); });
                paginationEl.appendChild(btn);
            }

            // Next button
            const next = createPageBtn('›', currentPage === totalPages);
            next.addEventListener('click', () => { if (currentPage < totalPages) { currentPage++; renderPage(); } });
            paginationEl.appendChild(next);
        }

        function createPageBtn(label, disabled) {
            const btn = document.createElement('button');
            btn.className = 'donor-page-btn' + (disabled ? ' disabled' : '');
            btn.textContent = label;
            btn.disabled = disabled;
            return btn;
        }

        if (searchInput)  searchInput.addEventListener('input',  filterDonations);
        if (statusFilter) statusFilter.addEventListener('change', filterDonations);
        if (dateFilter)   dateFilter.addEventListener('change',   filterDonations);

        // Initial render
        filteredRows = getAllRows();
        renderPage();
    }

    /* ================================================================
       PAGE: NOTIFICATIONS
    ================================================================ */
    if (page === 'notifications') {
        // Relative timestamps
        document.querySelectorAll('[data-notif-date]').forEach(el => {
            const raw = el.getAttribute('data-notif-date');
            if (raw) el.textContent = relativeTime(raw);
        });

        // Tab filtering
        const tabs = document.querySelectorAll('.notif-tab');
        const items = document.querySelectorAll('.notif-item');
        const noResultMsg = document.getElementById('notifNoResults');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const filter = tab.getAttribute('data-filter');
                let visible = 0;
                items.forEach(item => {
                    const isUnread = item.classList.contains('unread');
                    let show = false;
                    if (filter === 'all')    show = true;
                    if (filter === 'unread') show = isUnread;
                    if (filter === 'read')   show = !isUnread;
                    item.classList.toggle('notif-hidden', !show);
                    if (show) visible++;
                });
                if (noResultMsg) noResultMsg.style.display = visible === 0 ? 'block' : 'none';
            });
        });

        // Mark-as-read smooth animation
        document.querySelectorAll('.notif-mark-read-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const item = btn.closest('.notif-item');
                if (item) {
                    item.classList.add('fading-out');
                    setTimeout(() => { window.location.href = btn.href; }, 350);
                }
            });
        });
    }

    /* ================================================================
       PAGE: PROFILE
    ================================================================ */
    if (page === 'profile') {
        // Character counter for bio
        const bioArea   = document.getElementById('bio');
        const charCount = document.getElementById('bioCharCount');
        const MAX_BIO   = 500;

        if (bioArea && charCount) {
            function updateCharCount() {
                const len = bioArea.value.length;
                charCount.textContent = len + ' / ' + MAX_BIO + ' characters';
                charCount.classList.toggle('over-limit', len > MAX_BIO);
            }
            bioArea.addEventListener('input', updateCharCount);
            updateCharCount();
        }

        // Phone validation (Indian numbers, optional)
        const phoneInput = document.getElementById('phone');
        const phoneError = document.getElementById('phoneError');
        const PHONE_REGEX = /^[6-9]\d{9}$/;

        if (phoneInput) {
            phoneInput.addEventListener('blur', () => {
                const val = phoneInput.value.trim();
                if (phoneError) {
                    if (val && !PHONE_REGEX.test(val)) {
                        phoneError.style.display = 'flex';
                    } else {
                        phoneError.style.display = 'none';
                    }
                }
            });
        }

        // Block form submit if phone is invalid
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', e => {
                const val = phoneInput ? phoneInput.value.trim() : '';
                if (val && !PHONE_REGEX.test(val)) {
                    e.preventDefault();
                    if (phoneError) phoneError.style.display = 'flex';
                    phoneInput.focus();
                }
                const bio = bioArea ? bioArea.value : '';
                if (bio.length > MAX_BIO) {
                    e.preventDefault();
                    if (charCount) charCount.classList.add('over-limit');
                    bioArea.focus();
                }
            });
        }
    }

    /* ================================================================
       PAGE: DONATE (inline validation)
    ================================================================ */
    if (page === 'donate') {
        const form         = document.getElementById('donationForm');
        const presetInput  = document.getElementById('presetAmount');
        const customInput  = document.getElementById('customAmountInput');
        const errorBox     = document.getElementById('donationFormError');
        const submitBtn    = document.getElementById('submitBtn');

        function showDonationError(msg) {
            if (!errorBox) return;
            errorBox.style.display = 'flex';
            errorBox.querySelector('.error-text').textContent = msg;
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function hideDonationError() {
            if (errorBox) errorBox.style.display = 'none';
        }

        if (form) {
            form.addEventListener('submit', e => {
                hideDonationError();
                const pVal  = parseFloat(presetInput ? presetInput.value : '');
                const cVal  = parseFloat(customInput ? customInput.value  : '');
                const amount = (!isNaN(pVal) && pVal > 0) ? pVal : cVal;

                if (!amount || amount <= 0) {
                    e.preventDefault();
                    showDonationError('Please select or enter a valid donation amount.');
                    return;
                }
                if (amount < 1) {
                    e.preventDefault();
                    showDonationError('Minimum donation amount is ₹1.');
                    return;
                }
                if (amount > 1000000) {
                    e.preventDefault();
                    showDonationError('Maximum donation amount is ₹10,00,000. Please contact us for larger donations.');
                    return;
                }
                const paymentSelected = document.querySelector('input[name="payment_method"]:checked');
                if (!paymentSelected) {
                    e.preventDefault();
                    showDonationError('Please select a payment method before continuing.');
                    return;
                }
                // All valid — disable button to prevent duplicate submission
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.style.opacity = '0.75';
                    submitBtn.style.pointerEvents = 'none';
                }
            });
        }
    }

});
