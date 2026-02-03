<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
$token = $_COOKIE['token'];
$token = decryptToken($token, $master_key);
$token = json_decode(json_encode($token));
$id = (is_object($token) && property_exists($token, 'id')) ? $token->id : null;
$username = (is_object($token) && property_exists($token, 'username')) ? $token->username : null;
$role_type = (is_object($token) && property_exists($token, 'role_type')) ? $token->role_type : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Counters | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
    <style>
        .table-counters thead th { position:sticky; top:0; background:#fff; z-index:2; }
        .table-counters tbody tr:hover { background:rgba(0,0,0,0.02); }
    </style>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>
    <div class="min-h-screen pt-24 pb-32 flex justify-center px-4">
        <div class="w-full max-w-5xl">
            <div id="logOutNotify" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="bg-white shadow rounded-full px-6 py-2 mb-4">
                <nav class="text-sm">
                    <a href="/public/admin" class="text-gray-700 hover:text-[rgb(255,110,55)]">Dashboard</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-500">Counters</span>
                </nav>
            </div>
            <div class="bg-white shadow rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h5 class="text-lg font-semibold">Counters</h5>
                        <div class="text-sm text-gray-500">Assigned counters and staff</div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500">Per page</span>
                        <select id="countersPerPage" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4 flex flex-wrap gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchCounterRegistered" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" placeholder="Search username">
                            </div>
                        </div>
                        <select id="counter-filter-availability" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="none">Any Availability</option>
                            <option value="Available">Available</option>
                            <option value="Assigned">Assigned</option>
                            <option value="Offline">Offline</option>
                        </select>
                        <select id="counter-filter-priority" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="none">All Priority</option>
                            <option value="Y">Priority</option>
                            <option value="N">Normal</option>
                        </select>
                        <div class="flex gap-2 ml-auto">
                            <button id="btn-add-counter" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><span class="font-bold">+</span> Add</button>
                            <button id="btnExportCountersCsv" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Export CSV</button>
                            <button id="btnRefreshCounters" class="px-4 py-2 bg-[rgb(255,110,55)] text-white rounded-lg text-sm hover:bg-[rgb(230,60,20)]">Refresh</button>
                        </div>
                    </div>
                    <div id="cards-counters-registered" class="space-y-3"></div>
                    <nav class="mt-4">
                        <ul id="countersPagination" class="flex justify-center gap-1"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Counter Modal -->
    <div id="addCounterModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('addCounterModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-auto">
            <form id="frmAddCounter">
                <div class="bg-[rgb(255,110,55)] text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h5 class="font-bold">Add Counter</h5>
                        <div class="text-sm opacity-80">Assign an employee and set the counter number</div>
                    </div>
                    <button type="button" onclick="closeModal('addCounterModal')" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>
                <div class="p-6">
                    <div id="addCounterAlert" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded-lg"><span id="addCounterAlertMsg"></span></div>
                    <input type="hidden" name="counter_no_add" id="counter_no_add">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2">
                            <label class="block text-sm text-gray-600 mb-1">Search employees</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-search"></i></span>
                                <input type="text" id="addSearchUsername" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" placeholder="Search Username">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Priority Lane</label>
                            <select name="transaction-filter-priority-add" id="transaction-filter-priority-add" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="N">No</option>
                                <option value="Y">Yes</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Counter Number</label>
                            <input type="number" name="counter_no_add_visible" id="counter_no_add_visible" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="#" min="1">
                        </div>
                    </div>
                    <label class="block text-sm text-gray-600 mb-2">Employees Available</label>
                    <div class="border border-gray-200 rounded-lg p-3 max-h-64 overflow-auto">
                        <div id="cards-add-counter-available" class="space-y-2"></div>
                    </div>
                    <nav class="mt-3"><ul id="addCounterPagination" class="flex justify-center gap-1"></ul></nav>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('addCounterModal')" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" id="btnAddCounterSubmit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Add Counter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Counter Modal -->
    <div id="updateCounterModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('updateCounterModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-auto">
            <form id="frmUpdateCounter">
                <div class="bg-[rgb(255,110,55)] text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h5 class="font-bold">Update Counter</h5>
                        <div class="text-sm opacity-80">Edit assignment, number and priority</div>
                    </div>
                    <div class="text-xl font-bold" id="updateCounterDisplay">#<span></span></div>
                </div>
                <div class="p-6">
                    <div id="updateCounterAlert" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded-lg"><span id="updateCounterAlertMsg"></span></div>
                    <input type="hidden" name="update_counter_no" id="update_counter_no">
                    <input type="hidden" name="update_id" id="update_id">
                    <div class="hidden"><span id="updateCounterUsername"></span><span id="updateCounterNumber"></span></div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2">
                            <label class="block text-sm text-gray-600 mb-1">Search employees</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-search"></i></span>
                                <input type="text" id="updateSearchUsername" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" placeholder="Search Username">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Priority Lane</label>
                            <select name="transaction-filter-priority-update" id="transaction-filter-priority-update" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="N">No</option>
                                <option value="Y">Yes</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Counter Number</label>
                            <input type="number" name="counter_no_update_visible" id="counter_no_update_visible" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="#" min="1">
                        </div>
                    </div>
                    <label class="block text-sm text-gray-600 mb-2">Employees Available</label>
                    <div class="border border-gray-200 rounded-lg p-3 max-h-64 overflow-auto">
                        <div id="cards-update-counter-available" class="space-y-2"></div>
                    </div>
                    <nav class="mt-3"><ul id="updateCounterPagination" class="flex justify-center gap-1"></ul></nav>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('updateCounterModal')" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" id="btnUpdateCounterSubmit" class="px-4 py-2 bg-[rgb(255,110,55)] text-white rounded-lg hover:bg-[rgb(230,60,20)]">Update Counter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Counter Modal -->
    <div id="deleteCounterModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('deleteCounterModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md">
            <form id="frmDeleteCounter">
                <div class="bg-red-600 text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h5 class="font-bold">Delete Counter</h5>
                        <div class="text-sm opacity-80">This action cannot be undone</div>
                    </div>
                    <span class="font-bold">#<span id="deleteCounterDisplay">&mdash;</span></span>
                </div>
                <div class="p-6 text-center">
                    <div id="deleteCounterAlert" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded-lg text-left"><span id="deleteCounterAlertMsg"></span></div>
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="text-5xl text-red-500 mb-4"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <h5 class="font-bold mb-2">Confirm deletion</h5>
                    <p class="text-gray-600 mb-4">Remove <strong id="deleteCounterUsername">&mdash;</strong> from counter <strong id="deleteCounterNumber">&mdash;</strong>?</p>
                    <label class="flex items-center gap-2 justify-center cursor-pointer text-sm text-red-600">
                        <input type="checkbox" name="delete_force" id="delete_force" value="1" class="w-4 h-4 rounded border-gray-300">
                        <span>Force delete — permanently remove (admin only)</span>
                    </label>
                    <p id="deleteModeDesc" class="text-xs text-gray-500 mt-2">Detach will unassign the employee but will not reset today's counter counts.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                    <button type="button" onclick="closeModal('deleteCounterModal')" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" id="btnDeleteCounterSubmit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Detach Counter</button>
                </div>
            </form>
        </div>
    </div>

    <?php include_once './../includes/footer.php'; ?>
    <?php after_js()?>
    <script>
const endpointHost = window.endpointHost;
var counter_search = '';
var counter_page = 1;
var paginate = 25;
var counter_page_modal = 1;
var update_selected_employee = null;
var lastCounters = [];
var counter_filter_availability = 'none';
var counter_filter_priority = 'none';

function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
function escapeHtml(s) { if (s === null || s === undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function textBadge(label, type) {
    const colors = { success:'bg-green-500', danger:'bg-red-500', warning:'bg-yellow-500 text-gray-900' };
    return `<span class="px-2 py-1 text-xs rounded text-white ${colors[type]||'bg-gray-200 text-gray-800'}">${label}</span>`;
}

function showLoading(container) {
    if (container) container.innerHTML = '<div class="flex justify-center py-8"><div class="animate-spin w-8 h-8 border-4 border-[rgb(255,110,55)] border-t-transparent rounded-full"></div></div>';
}

function renderPagination(totalPages, currentPage, containerId = 'countersPagination') {
    const container = document.getElementById(containerId);
    if (!container) return;
    let html = '';
    const btn = (label, p, disabled, active) => `<li><button class="px-3 py-1 rounded ${active?'bg-[rgb(255,110,55)] text-white':'border border-gray-300 hover:bg-gray-100'} ${disabled?'opacity-50 cursor-not-allowed':''}" ${disabled?'disabled':''} data-page="${p}">${label}</button></li>`;
    html += btn('Prev', Math.max(1,currentPage-1), currentPage===1, false);
    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentPage-2 && p <= currentPage+2)) html += btn(p, p, false, p===currentPage);
        else if (p === currentPage-3 || p === currentPage+3) html += '<li><span class="px-2">...</span></li>';
    }
    html += btn('Next', Math.min(totalPages,currentPage+1), currentPage===totalPages, false);
    container.innerHTML = html;
    container.querySelectorAll('button[data-page]').forEach(b => b.addEventListener('click', () => {
        const page = parseInt(b.dataset.page);
        if (containerId === 'countersPagination') { counter_page = page; loadCounters(); }
        else if (containerId === 'addCounterPagination') { counter_page_modal = page; loadAddEmployees(); }
        else if (containerId === 'updateCounterPagination') { counter_page_modal = page; loadUpdateAvailableEmployees(); }
    }));
}

function loadCounters() {
    const container = document.getElementById('cards-counters-registered');
    if (!container) return;
    showLoading(container);
    const params = new URLSearchParams({ counters: true, page: counter_page, paginate, search: counter_search });
    if (counter_filter_availability !== 'none') params.set('availability', counter_filter_availability);
    if (counter_filter_priority !== 'none') params.set('priority', counter_filter_priority);
    $.ajax({
        url: buildApiUrl('/api/counters', params), type: 'GET', timeout: 10000, xhrFields: { withCredentials: true },
        success: function(response) {
            container.innerHTML = '';
            if (response.status === 'success') {
                const counters = response.counters || [];
                lastCounters = counters;
                const total = response.total || counters.length;
                const totalPages = Math.max(1, Math.ceil(total / paginate));
                renderPagination(totalPages, counter_page);
                if (!counters.length) {
                    container.innerHTML = '<div class="text-center py-8 text-gray-500"><div class="text-4xl mb-2"><i class="bi bi-collection"></i></div><div class="font-bold">No counters assigned</div><button class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg" onclick="document.getElementById(\'btn-add-counter\').click()">Add Counter</button></div>';
                    return;
                }
                counters.forEach(c => {
                    const card = document.createElement('div');
                    card.className = 'bg-gray-50 rounded-xl p-4 flex items-center justify-between';
                    card.innerHTML = `
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-[rgb(255,110,55)] text-white rounded-full flex items-center justify-center text-xl font-bold">${escapeHtml(c.counterNumber||'')}</div>
                            <div><div class="font-bold">${escapeHtml(c.username||'')}</div><div class="text-sm text-gray-500">${escapeHtml(c.role_type||'')}</div></div>
                        </div>
                        <div class="flex gap-2">
                            <button class="btn-update-counter p-2 border border-gray-300 rounded hover:bg-gray-100" data-id="${c.idcounter}"><i class="bi bi-pencil-square text-blue-600"></i></button>
                            <button class="btn-delete-counter p-2 border border-red-300 rounded hover:bg-red-50" data-id="${c.idcounter}"><i class="bi bi-trash-fill text-red-600"></i></button>
                        </div>`;
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">No counters found</div>';
            }
        },
        error: function(xhr) {
            if (xhr?.status === 404) container.innerHTML = '<div class="text-center py-8 text-gray-500"><div class="font-bold">No counters assigned</div><button class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg" onclick="document.getElementById(\'btn-add-counter\').click()">Add Counter</button></div>';
            else container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading counters <button class="ml-2 underline" onclick="loadCounters()">Retry</button></div>';
        }
    });
}

function loadAddEmployees() {
    const container = document.getElementById('cards-add-counter-available');
    if (!container) return;
    showLoading(container);
    const params = new URLSearchParams({ counters: true, available: true, search: counter_search, page: counter_page_modal, paginate });
    $.ajax({
        url: buildApiUrl('/api/counters', params), type: 'GET', timeout: 10000, xhrFields: { withCredentials: true },
        success: function(response) {
            container.innerHTML = '';
            if (response.status === 'success') {
                const employees = response.counters || [];
                if (!employees.length) { container.innerHTML = '<div class="text-center font-bold py-4">No employees available</div>'; return; }
                employees.forEach(e => {
                    const card = document.createElement('div');
                    card.className = 'bg-white border border-gray-200 rounded-lg p-3 flex items-center justify-between cursor-pointer hover:border-[rgb(255,110,55)]';
                    card.innerHTML = `
                        <div class="flex items-center gap-3">
                            <input type="radio" name="employee-counter-set" value="${e.id}" class="w-4 h-4">
                            <div><div class="font-bold">${escapeHtml(e.username)}</div><div class="text-sm text-gray-500">${escapeHtml(e.role_type||'')}</div></div>
                        </div>
                        <div>${textBadge(e.availability, e.availability==='Available'?'success':e.availability==='Assigned'?'danger':'warning')}</div>`;
                    card.addEventListener('click', () => card.querySelector('input[type=radio]').checked = true);
                    container.appendChild(card);
                });
                const hasMore = employees.length === paginate;
                renderPagination(hasMore ? counter_page_modal + 1 : counter_page_modal, counter_page_modal, 'addCounterPagination');
            } else { container.innerHTML = '<div class="text-center font-bold py-4">No employees available</div>'; }
        },
        error: function() { container.innerHTML = '<div class="text-center font-bold py-4">No employees available</div>'; }
    });
}

function loadUpdateAvailableEmployees() {
    const container = document.getElementById('cards-update-counter-available');
    if (!container) return;
    showLoading(container);
    const params = new URLSearchParams({ counters: true, available: true, search: counter_search, page: counter_page_modal, paginate });
    $.ajax({
        url: buildApiUrl('/api/counters', params), type: 'GET', timeout: 10000, xhrFields: { withCredentials: true },
        success: function(response) {
            container.innerHTML = '';
            if (response.status === 'success') {
                const employees = response.counters || [];
                if (!employees.length) { container.innerHTML = '<div class="text-center font-bold py-4">No employees available</div>'; return; }
                employees.forEach(e => {
                    const card = document.createElement('div');
                    card.className = 'bg-white border border-gray-200 rounded-lg p-3 flex items-center justify-between cursor-pointer hover:border-[rgb(255,110,55)]';
                    card.innerHTML = `
                        <div class="flex items-center gap-3">
                            <input type="radio" name="employee-counter-set" value="${e.id}" class="w-4 h-4" ${e.id == update_selected_employee ? 'checked' : ''}>
                            <div><div class="font-bold">${escapeHtml(e.username)}</div><div class="text-sm text-gray-500">${escapeHtml(e.role_type||'')}</div></div>
                        </div>
                        <div>${textBadge(e.availability, e.availability==='Available'?'success':e.availability==='Assigned'?'danger':'warning')}</div>`;
                    card.addEventListener('click', () => card.querySelector('input[type=radio]').checked = true);
                    container.appendChild(card);
                });
                const hasMore = employees.length === paginate;
                renderPagination(hasMore ? counter_page_modal + 1 : counter_page_modal, counter_page_modal, 'updateCounterPagination');
            } else { container.innerHTML = '<div class="text-center font-bold py-4">No employees available</div>'; }
        },
        error: function() { container.innerHTML = '<div class="text-center font-bold py-4">No employees available</div>'; }
    });
}

document.getElementById('btn-add-counter')?.addEventListener('click', () => { counter_page_modal = 1; counter_search = ''; document.getElementById('frmAddCounter')?.reset(); loadAddEmployees(); openModal('addCounterModal'); });
document.getElementById('btnRefreshCounters')?.addEventListener('click', () => loadCounters());
document.getElementById('searchCounterRegistered')?.addEventListener('keyup', e => { counter_search = e.target.value; counter_page = 1; loadCounters(); });
document.getElementById('counter-filter-availability')?.addEventListener('change', e => { counter_filter_availability = e.target.value; counter_page = 1; loadCounters(); });
document.getElementById('counter-filter-priority')?.addEventListener('change', e => { counter_filter_priority = e.target.value; counter_page = 1; loadCounters(); });
document.getElementById('countersPerPage')?.addEventListener('change', e => { paginate = parseInt(e.target.value) || 25; counter_page = 1; loadCounters(); });
document.getElementById('addSearchUsername')?.addEventListener('keyup', e => { counter_search = e.target.value; counter_page_modal = 1; loadAddEmployees(); });
document.getElementById('updateSearchUsername')?.addEventListener('keyup', e => { counter_search = e.target.value; counter_page_modal = 1; loadUpdateAvailableEmployees(); });

$(document).on('click', '.btn-update-counter', function() {
    const id = this.dataset.id;
    counter_search = ''; counter_page_modal = 1;
    $.ajax({
        url: buildApiUrl('/api/counters', new URLSearchParams({ counters: true, id })), type: 'GET', dataType: 'json', xhrFields: { withCredentials: true },
        success: function(response) {
            if (response.status === 'success') {
                const c = response.counter;
                document.querySelector('#updateCounterDisplay span').innerText = c.counterNumber;
                document.getElementById('update_id').value = c.idcounter;
                document.getElementById('update_counter_no').value = c.counterNumber;
                document.getElementById('counter_no_update_visible').value = c.counterNumber;
                document.getElementById('transaction-filter-priority-update').value = c.counter_priority === 'Y' ? 'Y' : 'N';
                update_selected_employee = c.idemployee;
                loadUpdateAvailableEmployees();
                openModal('updateCounterModal');
            }
        }
    });
});

$(document).on('click', '.btn-delete-counter', function() {
    const id = this.dataset.id;
    $.ajax({
        url: buildApiUrl('/api/counters', new URLSearchParams({ counters: true, id })), type: 'GET', dataType: 'json', xhrFields: { withCredentials: true },
        success: function(response) {
            if (response.status === 'success') {
                const c = response.counter;
                document.getElementById('deleteCounterDisplay').innerText = c.counterNumber;
                document.getElementById('deleteCounterUsername').innerText = c.username;
                document.getElementById('deleteCounterNumber').innerText = c.counterNumber;
                document.getElementById('delete_id').value = c.idcounter;
                openModal('deleteCounterModal');
            }
        }
    });
});

document.getElementById('frmAddCounter')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const alert = document.getElementById('addCounterAlert'), alertMsg = document.getElementById('addCounterAlertMsg');
    document.getElementById('counter_no_add').value = document.getElementById('counter_no_add_visible').value;
    const formData = new FormData(this);
    const radio = document.querySelector('input[name="employee-counter-set"]:checked');
    if (!radio) { alertMsg.innerText = 'Please select an employee'; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); return; }
    $.ajax({
        url: buildApiUrl('/api/counters'), type: 'POST', contentType: 'application/json', dataType: 'json', xhrFields: { withCredentials: true },
        data: JSON.stringify({ counterNumber: formData.get('counter_no_add'), counter_priority: formData.get('transaction-filter-priority-add') || 'N', user_id: radio.value }),
        success: function(response) {
            if (response.status === 'success') { alert.classList.remove('hidden','bg-red-100','text-red-800'); alert.classList.add('bg-green-100','text-green-800'); alertMsg.innerText = response.message; setTimeout(() => location.reload(), 1000); }
            else { alertMsg.innerText = response.message; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
        },
        error: function(x) { alertMsg.innerText = 'Failed: ' + (x.responseText || 'Error'); alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
    });
});

document.getElementById('frmUpdateCounter')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const alert = document.getElementById('updateCounterAlert'), alertMsg = document.getElementById('updateCounterAlertMsg');
    document.getElementById('update_counter_no').value = document.getElementById('counter_no_update_visible').value;
    const formData = new FormData(this);
    const radio = document.querySelector('#cards-update-counter-available input[name="employee-counter-set"]:checked');
    if (!radio) { alertMsg.innerText = 'Please select an employee'; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); return; }
    $.ajax({
        url: buildApiUrl('/api/counters'), type: 'PUT', contentType: 'application/json', dataType: 'json', xhrFields: { withCredentials: true },
        data: JSON.stringify({ counter_id: formData.get('update_id'), counterNumber: formData.get('update_counter_no'), idemployee: radio.value, counter_priority: formData.get('transaction-filter-priority-update') || 'N' }),
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            if (response.status === 'success') { alert.classList.remove('hidden','bg-red-100','text-red-800'); alert.classList.add('bg-green-100','text-green-800'); alertMsg.innerText = response.message; setTimeout(() => location.reload(), 1000); }
            else { alertMsg.innerText = response.message; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
        },
        error: function(x) { alertMsg.innerText = 'Failed: ' + (x.responseText || 'Error'); alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
    });
});

document.getElementById('delete_force')?.addEventListener('change', function() {
    const btn = document.getElementById('btnDeleteCounterSubmit');
    const desc = document.getElementById('deleteModeDesc');
    if (this.checked) { btn.classList.remove('bg-yellow-500','hover:bg-yellow-600'); btn.classList.add('bg-red-600','hover:bg-red-700'); btn.innerText = 'Delete Counter'; desc.innerText = 'Force delete will permanently remove the counter row.'; }
    else { btn.classList.remove('bg-red-600','hover:bg-red-700'); btn.classList.add('bg-yellow-500','hover:bg-yellow-600'); btn.innerText = 'Detach Counter'; desc.innerText = "Detach will unassign the employee but will not reset today's counter counts."; }
});

document.getElementById('frmDeleteCounter')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const alert = document.getElementById('deleteCounterAlert'), alertMsg = document.getElementById('deleteCounterAlertMsg');
    const formData = new FormData(this);
    $.ajax({
        url: buildApiUrl('/api/counters'), type: 'DELETE', contentType: 'application/json', dataType: 'json', xhrFields: { withCredentials: true },
        data: JSON.stringify({ counter_id: formData.get('delete_id'), force: formData.get('delete_force') === '1' }),
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            if (response.status === 'success') { alert.classList.remove('hidden','bg-red-100','text-red-800'); alert.classList.add('bg-green-100','text-green-800'); alertMsg.innerText = response.message; setTimeout(() => location.reload(), 1000); }
            else { alertMsg.innerText = response.message; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
        },
        error: function(x) { alertMsg.innerText = 'Failed: ' + (x.responseText || 'Error'); alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
    });
});

document.getElementById('btnExportCountersCsv')?.addEventListener('click', function() {
    const data = lastCounters.length ? lastCounters : [];
    if (!data.length) { alert('No counters to export'); return; }
    const cols = ['counterNumber','username','role_type','queue_count','idcounter'];
    const csv = [['Counter','Username','Role','Queue','ID'].join(',')].concat(data.map(c => cols.map(k => '"' + String(c[k]||'').replace(/"/g,'""') + '"').join(','))).join('\n');
    const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([csv], { type:'text/csv' })); a.download = 'counters_export.csv'; a.click();
});

loadCounters();
    </script>
</body>
</html>
