<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
$raw_token = isset($_COOKIE['token']) ? $_COOKIE['token'] : null;
$token = null;
if ($raw_token) {
    $decoded = decryptToken($raw_token, $master_key);
    if ($decoded) $token = json_decode(json_encode($decoded));
}
$id = isset($token->id) ? $token->id : null;
$username = isset($token->username) ? $token->username : null;
$role_type = isset($token->role_type) ? $token->role_type : (isset($_COOKIE['role_type']) ? $_COOKIE['role_type'] : null);
$email = isset($token->email) ? $token->email : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Employees | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
    <style>
        .table-employees thead th { position:sticky; top:0; background:#fff; z-index:2; }
        .table-employees tbody tr:hover { background:rgba(0,0,0,0.02); }
        .td-truncate { max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>
    <div class="min-h-screen pt-24 pb-32 flex justify-center px-4">
        <div class="w-full max-w-4xl">
            <div id="logOutNotify" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="bg-white shadow rounded-full px-6 py-2 mb-4">
                <nav class="text-sm">
                    <a href="/public/admin" class="text-gray-700 hover:text-[rgb(255,110,55)]">Dashboard</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-500">Employees</span>
                </nav>
            </div>
            <div class="bg-white shadow rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h5 class="text-lg font-semibold">Employees</h5>
                        <div class="text-sm text-gray-500">List of employees, roles and status</div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500">Per page</span>
                        <select id="employeesPerPage" class="border border-gray-300 rounded px-2 py-1 text-sm">
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
                                <input type="text" id="searchEmployee" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" placeholder="Search username or email">
                            </div>
                        </div>
                        <select id="filterRole" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="none">All Roles</option>
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                        <select id="filterStatus" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="none">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="flex gap-2 ml-auto">
                            <button id="btn-add-employee" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><span class="font-bold">+</span> Add</button>
                            <button id="btnExportEmployeesCsv" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Export CSV</button>
                            <button id="btnRefreshEmployees" class="px-4 py-2 bg-[rgb(255,110,55)] text-white rounded-lg text-sm hover:bg-[rgb(230,60,20)]">Refresh</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full table-employees text-sm" id="table-employees">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Username</th>
                                    <th class="px-4 py-3 font-medium hidden md:table-cell">Email</th>
                                    <th class="px-4 py-3 font-medium hidden md:table-cell">Role</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <small class="text-gray-500">Showing <strong id="empShowingCount">0</strong></small>
                    </div>
                    <nav class="mt-4">
                        <ul id="employeesPagination" class="flex justify-center gap-1"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewEmployeeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('viewEmployeeModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-auto">
            <div class="bg-[rgb(255,110,55)] text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                <div>
                    <h5 class="font-bold">View Employee: <span id="viewUsernameDisplay"></span></h5>
                    <div class="text-sm opacity-80">Employee details</div>
                </div>
                <button onclick="closeModal('viewEmployeeModal')" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="text-center md:w-1/3">
                        <div class="text-6xl text-gray-400 mb-2"><i class="bi bi-person-circle"></i></div>
                        <h5 class="font-bold" id="viewEmployeeUsername">N/A</h5>
                        <div class="text-sm text-gray-500" id="viewEmployeeRoleTypeSidebar">&mdash;</div>
                        <div class="mt-2" id="viewEmployeeStatusSidebar">&mdash;</div>
                    </div>
                    <div class="flex-1 text-sm space-y-2">
                        <div class="flex"><span class="w-24 text-gray-500">ID</span><span id="viewEmployeeId">N/A</span></div>
                        <div class="flex"><span class="w-24 text-gray-500">Email</span><a href="#" id="viewEmployeeEmail" class="text-[rgb(255,110,55)]">N/A</a></div>
                        <div class="flex"><span class="w-24 text-gray-500">Role</span><span id="viewEmployeeRoleType">&mdash;</span></div>
                        <div class="flex"><span class="w-24 text-gray-500">Status</span><span id="viewEmployeeStatus">&mdash;</span></div>
                        <div class="flex"><span class="w-24 text-gray-500">Created</span><span id="viewEmployeeCreated">&mdash;</span></div>
                        <div class="flex"><span class="w-24 text-gray-500">Last login</span><span id="viewEmployeeLastLogin">&mdash;</span></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 text-right">
                <button onclick="closeModal('viewEmployeeModal')" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addEmployeeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('addEmployeeModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-auto">
            <form id="frmAddEmployee">
                <div class="bg-[rgb(255,110,55)] text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h5 class="font-bold">Add Employee</h5>
                        <div class="text-sm opacity-80">Create a new employee account and assign role</div>
                    </div>
                    <button type="button" onclick="closeModal('addEmployeeModal')" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>
                <div class="p-6">
                    <div id="addEmployeeAlert" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded-lg"><span id="addEmployeeAlertMsg"></span></div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Username</label>
                            <input type="text" name="add_username" id="add_username" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Email</label>
                            <input type="email" name="add_email" id="add_email" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Password</label>
                            <input type="password" name="add_password" id="add_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Confirm Password</label>
                            <input type="password" name="add_confirm_password" id="add_confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Role</label>
                            <select name="add_role_type" id="add_role_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="add_status" id="add_status" value="1" class="w-5 h-5 rounded border-gray-300 text-[rgb(255,110,55)]">
                                <span>Activate Employee</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('addEmployeeModal')" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Modal -->
    <div id="updateEmployeeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('updateEmployeeModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-auto">
            <form id="frmUpdateEmployee">
                <div class="bg-[rgb(255,110,55)] text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h5 class="font-bold">Update Employee: <span id="updateUsernameDisplay"></span></h5>
                        <div class="text-sm opacity-80">Edit employee details and role</div>
                    </div>
                    <button type="button" onclick="closeModal('updateEmployeeModal')" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>
                <div class="p-6">
                    <div id="updateEmployeeAlert" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded-lg"><span id="updateEmployeeAlertMsg"></span></div>
                    <input type="hidden" name="update_id" id="update_id">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Username</label>
                            <input type="text" name="update_username" id="update_username" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Email</label>
                            <input type="email" name="update_email" id="update_email" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">New Password</label>
                            <input type="password" name="update_password" id="update_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Confirm Password</label>
                            <input type="password" name="update_confirm_password" id="update_confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Role</label>
                            <select name="update_role_type" id="update_role_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="update_active" id="update_active" value="1" class="w-5 h-5 rounded border-gray-300 text-[rgb(255,110,55)]">
                                <span>Activate Employee</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('updateEmployeeModal')" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[rgb(255,110,55)] text-white rounded-lg hover:bg-[rgb(230,60,20)]">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteEmployeeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('deleteEmployeeModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md">
            <form id="frmDeleteEmployee">
                <div class="bg-red-600 text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h5 class="font-bold">Delete Employee</h5>
                        <div class="text-sm opacity-80">This action cannot be undone</div>
                    </div>
                    <span class="font-bold" id="deleteUsernameDisplay">&mdash;</span>
                </div>
                <div class="p-6 text-center">
                    <div id="deleteEmployeeAlert" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded-lg"><span id="deleteEmployeeAlertMsg"></span></div>
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="text-5xl text-red-500 mb-4"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <h5 class="font-bold mb-2">Confirm deletion</h5>
                    <p class="text-gray-600">Are you sure you want to remove <strong id="delete_username"></strong> from the system?</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                    <button type="button" onclick="closeModal('deleteEmployeeModal')" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <?php after_js()?>
    <?php include_once "./../includes/footer.php";?>
    <script src="./../asset/js/message.js"></script>
    <script>
const endpointHost = window.endpointHost;
var employee_search = '';
var page_employees = 1;
var paginate = 25;
var role_type_employee = 'none';
var status_employee = 'none';
const search = document.getElementById('searchEmployee');
const filterRole = document.getElementById('filterRole');
const filterStatus = document.getElementById('filterStatus');
const perPageSelect = document.getElementById('employeesPerPage');

function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }

function textBadge(label, type) {
    const colors = { success:'bg-green-500', danger:'bg-red-500', warning:'bg-yellow-500 text-gray-900', info:'bg-blue-500' };
    return `<span class="px-2 py-1 text-xs rounded text-white ${colors[type]||'bg-gray-200 text-gray-800'}">${label}</span>`;
}

function debounce(fn, wait) { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); }; }
const debouncedSearch = debounce(v => { page_employees = 1; employee_search = v; loadEmployees(); }, 300);
search?.addEventListener('keyup', e => { if (e.key === 'Enter') { page_employees = 1; employee_search = e.target.value; loadEmployees(); } else debouncedSearch(e.target.value); });
filterRole?.addEventListener('change', e => { page_employees = 1; role_type_employee = e.target.value; loadEmployees(); });
filterStatus?.addEventListener('change', e => { page_employees = 1; status_employee = e.target.value; loadEmployees(); });
perPageSelect?.addEventListener('change', e => { paginate = Number(e.target.value) || 25; page_employees = 1; loadEmployees(); });

function showLoading() {
    const tbody = document.querySelector('#table-employees tbody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8"><div class="animate-spin w-8 h-8 border-4 border-[rgb(255,110,55)] border-t-transparent rounded-full mx-auto"></div></td></tr>';
}

function renderPagination(totalPages, currentPage) {
    const container = document.getElementById('employeesPagination');
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
    container.querySelectorAll('button[data-page]').forEach(b => b.addEventListener('click', () => { page_employees = parseInt(b.dataset.page); loadEmployees(); }));
}

function loadEmployees() {
    showLoading();
    const set_limit = paginate;
    const set_offset = (page_employees - 1) * paginate;
    const params = new URLSearchParams();
    if (employee_search?.trim()) params.set('username', employee_search.trim());
    if (role_type_employee && role_type_employee !== 'none') params.set('role_type', role_type_employee);
    if (status_employee && status_employee !== 'none') params.set('status', status_employee);
    params.set('set_limit', set_limit);
    params.set('set_offset', set_offset);
    let apiUrl = buildApiUrl('/api/users', params);
    if (!apiUrl) {
        document.querySelector('#table-employees tbody').innerHTML = '<tr><td colspan="5" class="text-center text-red-500 py-4">Service unavailable</td></tr>';
        return;
    }
    $.ajax({
        url: apiUrl, type: 'GET', timeout: 10000, dataType: 'json', xhrFields: { withCredentials: true },
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            const tbody = document.querySelector('#table-employees tbody');
            tbody.innerHTML = '';
            if (response.status === 'success') {
                const employees = response.data || response.employees || [];
                const total = response.total || employees.length;
                document.getElementById('empShowingCount').innerText = employees.length;
                const totalPages = Math.max(1, Math.ceil(total / paginate));
                renderPagination(totalPages, page_employees);
                employees.forEach(emp => {
                    const statusBadge = emp.active == 1 ? textBadge('Active','success') : textBadge('Inactive','danger');
                    const nameClass = emp.role_type === 'admin' ? 'text-blue-600' : 'text-green-600';
                    const row = `<tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium ${nameClass}">${emp.username}</td>
                        <td class="px-4 py-3 hidden md:table-cell td-truncate"><a href="mailto:${emp.email}" class="text-[rgb(255,110,55)] hover:underline">${emp.email||'—'}</a></td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500">${emp.role_type||'—'}</td>
                        <td class="px-4 py-3">${statusBadge}</td>
                        <td class="px-4 py-3 text-right">
                            <button class="btn-view px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 mr-1" data-id="${emp.id}">View</button>
                            <button class="btn-edit px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 mr-1" data-id="${emp.id}">Edit</button>
                            <button class="btn-delete px-2 py-1 text-xs border border-red-300 text-red-600 rounded hover:bg-red-50" data-id="${emp.id}">Delete</button>
                        </td>
                    </tr>`;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
                if (!employees.length) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-500 py-4">No employees found</td></tr>';
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-500 py-4">No employees found</td></tr>';
            }
        },
        error: function() { document.querySelector('#table-employees tbody').innerHTML = '<tr><td colspan="5" class="text-center text-red-500 py-4">Error loading employees</td></tr>'; }
    });
}

document.getElementById('btn-add-employee')?.addEventListener('click', () => { document.getElementById('frmAddEmployee')?.reset(); openModal('addEmployeeModal'); });
document.getElementById('btnRefreshEmployees')?.addEventListener('click', () => loadEmployees());

$(document).on('click', '.btn-view', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const params = new URLSearchParams({ user_id: id });
    $.ajax({
        url: buildApiUrl('/api/users', params), type: 'GET', dataType: 'json', xhrFields: { withCredentials: true },
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            const emp = response.data || response.employee || {};
            document.getElementById('viewUsernameDisplay').innerText = emp.username || '';
            document.getElementById('viewEmployeeId').innerText = id;
            document.getElementById('viewEmployeeUsername').innerText = emp.username || '';
            document.getElementById('viewEmployeeEmail').innerText = emp.email || '—';
            document.getElementById('viewEmployeeEmail').href = emp.email ? 'mailto:'+emp.email : '#';
            document.getElementById('viewEmployeeRoleType').innerText = emp.role_type || '—';
            document.getElementById('viewEmployeeRoleTypeSidebar').innerText = emp.role_type ? emp.role_type.charAt(0).toUpperCase()+emp.role_type.slice(1) : '—';
            document.getElementById('viewEmployeeStatus').innerHTML = emp.active == 1 ? textBadge('Active','success') : textBadge('Inactive','danger');
            document.getElementById('viewEmployeeStatusSidebar').innerHTML = emp.active == 1 ? textBadge('Active','success') : textBadge('Inactive','danger');
            document.getElementById('viewEmployeeCreated').innerText = emp.created_at ? new Date(emp.created_at).toLocaleString() : '—';
            document.getElementById('viewEmployeeLastLogin').innerText = emp.employee_last_login ? new Date(emp.employee_last_login).toLocaleString() : 'Never';
            openModal('viewEmployeeModal');
        }
    });
});

$(document).on('click', '.btn-edit', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const params = new URLSearchParams({ user_id: id });
    $.ajax({
        url: buildApiUrl('/api/users', params), type: 'GET', dataType: 'json', xhrFields: { withCredentials: true },
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            const emp = response.data || response.employee || {};
            document.getElementById('updateUsernameDisplay').innerText = emp.username || '';
            const form = document.getElementById('frmUpdateEmployee');
            form.reset();
            form.elements['update_id'].value = id;
            form.elements['update_username'].value = emp.username || '';
            form.elements['update_email'].value = emp.email || '';
            form.elements['update_role_type'].value = emp.role_type || '';
            form.elements['update_active'].checked = emp.active == 1;
            openModal('updateEmployeeModal');
        }
    });
});

$(document).on('click', '.btn-delete', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const params = new URLSearchParams({ user_id: id });
    $.ajax({
        url: buildApiUrl('/api/users', params), type: 'GET', dataType: 'json', xhrFields: { withCredentials: true },
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            const emp = response.data || response.employee || {};
            document.getElementById('deleteUsernameDisplay').innerText = emp.username || '';
            document.getElementById('delete_username').innerText = emp.username || '';
            document.getElementById('delete_id').value = id;
            openModal('deleteEmployeeModal');
        }
    });
});

document.getElementById('frmAddEmployee')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const password = formData.get('add_password');
    const confirm = formData.get('add_confirm_password');
    const alert = document.getElementById('addEmployeeAlert');
    const alertMsg = document.getElementById('addEmployeeAlertMsg');
    if (password !== confirm) { alertMsg.innerText = 'Passwords do not match'; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); return; }
    $.ajax({
        url: buildApiUrl('/api/users'), type: 'POST', contentType: 'application/json', dataType: 'json',
        data: JSON.stringify({ username: formData.get('add_username'), password, email: formData.get('add_email'), user_role: formData.get('add_role_type'), active: formData.get('add_status') ? 1 : 0 }),
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            if (response.status === 'success') { alert.classList.remove('hidden','bg-red-100','text-red-800'); alert.classList.add('bg-green-100','text-green-800'); alertMsg.innerText = response.message; setTimeout(() => location.reload(), 1000); }
            else { alertMsg.innerText = response.message; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
        },
        error: function(x) { alertMsg.innerText = 'Error: ' + (x.responseText || 'Failed'); alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
    });
});

document.getElementById('frmUpdateEmployee')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const password = formData.get('update_password');
    const confirm = formData.get('update_confirm_password');
    const alert = document.getElementById('updateEmployeeAlert');
    const alertMsg = document.getElementById('updateEmployeeAlertMsg');
    if (password !== confirm) { alertMsg.innerText = 'Passwords do not match'; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); return; }
    const payload = { id: formData.get('update_id'), username: formData.get('update_username'), email: formData.get('update_email'), role_type: formData.get('update_role_type'), active: formData.get('update_active') ? 1 : 0 };
    if (password?.trim()) payload.password = password;
    $.ajax({
        url: buildApiUrl('/api/users'), type: 'PATCH', contentType: 'application/json', dataType: 'json', data: JSON.stringify(payload), xhrFields: { withCredentials: true },
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            if (response.status === 'success') { alert.classList.remove('hidden','bg-red-100','text-red-800'); alert.classList.add('bg-green-100','text-green-800'); alertMsg.innerText = response.message || 'Updated'; setTimeout(() => location.reload(), 1000); }
            else { alertMsg.innerText = response.message || 'Failed'; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
        },
        error: function(x) { alertMsg.innerText = 'Error: ' + (x.responseText || 'Failed'); alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
    });
});

document.getElementById('frmDeleteEmployee')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const alert = document.getElementById('deleteEmployeeAlert');
    const alertMsg = document.getElementById('deleteEmployeeAlertMsg');
    $.ajax({
        url: buildApiUrl('/api/users'), type: 'DELETE', contentType: 'application/json', dataType: 'json', data: JSON.stringify({ user_id: formData.get('delete_id'), force: true }), xhrFields: { withCredentials: true },
        beforeSend: function(xhr) { <?php if (isset($_COOKIE['token'])): ?>try { xhr.setRequestHeader('Authorization', 'Bearer <?php echo addslashes($_COOKIE['token']); ?>'); } catch(e){}<?php endif; ?> },
        success: function(response) {
            if (response.status === 'success') { alert.classList.remove('hidden','bg-red-100','text-red-800'); alert.classList.add('bg-green-100','text-green-800'); alertMsg.innerText = response.message; setTimeout(() => location.reload(), 1000); }
            else { alertMsg.innerText = response.message || 'Failed'; alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
        },
        error: function(x) { alertMsg.innerText = 'Error: ' + (x.responseText || 'Failed'); alert.classList.remove('hidden'); setTimeout(() => alert.classList.add('hidden'), 3000); }
    });
});

loadEmployees();
    </script>
</body>
</html>
