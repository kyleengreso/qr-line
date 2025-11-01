<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();

$token = $_COOKIE['token'];
$token = decryptToken($token, $master_key);
$token = json_encode($token);
$token = json_decode($token);

// Safely extract token properties to avoid PHP notices when fields are missing
$id = (is_object($token) && property_exists($token, 'id')) ? $token->id : null;
$username = (is_object($token) && property_exists($token, 'username')) ? $token->username : null;
$role_type = (is_object($token) && property_exists($token, 'role_type')) ? $token->role_type : null;
$email = (is_object($token) && property_exists($token, 'email')) ? $token->email : null;
$counterNumber = (is_object($token) && property_exists($token, 'counterNumber')) ? $token->counterNumber : null;
// Server-side fetch counters to render the table initially (fallback to client-side AJAX)
$counters = [];
$totalCounters = 0;
// Build internal API URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$api_url = $protocol . '://' . $host . '/public/api/api_endpoint.php?counters=true&paginate=1000';
// Use cURL to fetch
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$resp = curl_exec($ch);
curl_close($ch);
if ($resp) {
    $data = json_decode($resp, true);
    if (is_array($data) && isset($data['status']) && $data['status'] === 'success' && isset($data['counters'])) {
        $counters = $data['counters'];
        $totalCounters = count($counters);
    }
}

function phpUserStatusIcon($username, $role_type, $active) {
    if ($role_type === 'admin') {
        if ((int)$active === 1) {
            return "<i class=\"bi bi-person-fill-gear role_type_admin_icon\"></i><span class=\"role_type_admin_icon\">" . htmlspecialchars($username) . "</span>";
        } else {
            return "<i class=\"bi bi-person-fill-slash employeeActive-no-icon\"></i><span class=\"employeeActive-no-icon\">" . htmlspecialchars($username) . "</span>";
        }
    } else {
        if ((int)$active === 1) {
            return "<i class=\"bi bi-person-plus-fill role_type_employee_icon\"></i><span class=\"role_type_employee_icon\">" . htmlspecialchars($username) . "</span>";
        } else {
            return "<i class=\"bi bi-person-fill-slash employeeActive-no-icon\"></i><span class=\"employeeActive-no-icon\">" . htmlspecialchars($username) . "</span>";
        }
    }
}
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
        /* Reuse transactions card/table styles for counters to match sizing and spacing */
        .transactions-toolbar {
            display: flex;
            gap: .75rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .transactions-toolbar .flex-fill { min-width: 220px; }
        .transactions-card { border-radius: 18px; }
        .transactions-table thead th {
            position: sticky;
            top: 0;
            background: var(--bs-white, #fff);
            z-index: 2;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .transactions-table tbody tr {
            border-bottom: 1px solid rgba(0,0,0,0.03);
            transition: background-color .12s ease-in-out;
        }
        .transactions-table tbody tr:hover { background-color: rgba(0,0,0,0.03); }
        .transactions-table td, .transactions-table th { vertical-align: middle; padding: .45rem .6rem; font-size: .92rem; }
        .token-col { width: 110px; white-space: nowrap; }
        .time-col { width: 150px; white-space: nowrap; }
        .txn-col { width: 100px; }
        .actions-col { width: 120px; }
        .actions-col .btn { padding: .25rem .5rem; font-size: .82rem; }
        .dropdown-menu { min-width: 8rem; }
        .transactions-toolbar .form-floating { min-width: 160px; }
        .transactions-toolbar .form-floating.flex-fill { min-width: 120px; }
        .td-truncate { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .small-muted { font-size: .85rem; color: #6c757d; }
        .loader-overlay {
            position: absolute; inset: 0; display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.7);z-index:5;border-radius:inherit;
        }
        .badge-small { font-size:.72rem; padding:.25rem .45rem; }
        @media (max-width: 768px) {
            .transactions-toolbar { gap:.5rem; }
            .td-truncate { max-width: 120px; }
        }
    </style>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer d-flex justify-content-center" style="margin-top:100px;min-height:900px">
        <div class="col-md-10" style="min-width:400px;">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="card shadow px-4 py-2 mb-2" style="border-radius:30px">
                <nav aria-label="breadcrumb mx-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/public/admin" style="text-decoration:none;color:black">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Counters</li>
                    </ol>
                </nav>
            </div>
            <div class="card shadow transactions-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Counters</h5>
                        <div class="small text-muted">Assigned counters and staff</div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="small text-muted">Per page</div>
                        <select id="countersPerPage" class="form-select form-select-sm" style="width:88px">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="card-body position-relative">
                    <div class="mb-3 transactions-toolbar">
                        <div class="input-group flex-fill">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="searchAdd" id="searchCounterRegistered" class="form-control" placeholder="Search username">
                        </div>

                        <div class="d-flex gap-2 flex-wrap flex-fill w-100">
                            <!-- Role filter removed: counters only show registered employees, so role filter unnecessary -->
                            <div class="form-floating flex-fill">
                                <select class="form-select" id="counter-filter-availability">
                                    <option value="none">Any Availability</option>
                                    <option value="Available">Available</option>
                                    <option value="Assigned">Assigned</option>
                                    <option value="Offline">Offline</option>
                                </select>
                                <label for="counter-filter-availability">Availability</label>
                            </div>
                            <div class="form-floating flex-fill">
                                <select class="form-select" id="counter-filter-priority">
                                    <option value="none">All Priority</option>
                                    <option value="Y">Priority</option>
                                    <option value="N">Normal</option>
                                </select>
                                <label for="counter-filter-priority">Priority</label>
                            </div>
                        </div>

                        <div class="ms-auto d-flex gap-2">
                            <a href="#" class="btn btn-success text-white px-3" id="btn-add-counter"><span class="fw-bold">+</span> Add</a>
                            <button id="btnExportCountersCsv" class="btn btn-outline-secondary btn-sm">Export CSV</button>
                            <button id="btnRefreshCounters" class="btn btn-primary btn-sm">Refresh</button>
                        </div>
                    </div>
                    <div id="cards-counters-registered" class="w-100">
                        <!-- Modern table layout for counters (client-side JS will populate tbody) -->
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle" id="table-counters">
                                <thead class="table-light">
                                    <tr>
                                        <th>Counter</th>
                                        <th class="d-none d-md-table-cell">Username</th>
                                        <th class="d-none d-md-table-cell">Role</th>
                                        <th class="d-none d-sm-table-cell">Queue</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="counters-tbody">
                                    <?php if (!empty($counters)): ?>
                                        <?php foreach ($counters as $counter): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($counter['counterNumber'] ?? '&mdash;'); ?></strong></td>
                                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($counter['username'] ?? '&mdash;'); ?></td>
                                                <td class="d-none d-md-table-cell small text-muted"><?php echo htmlspecialchars($counter['role_type'] ?? '&mdash;'); ?></td>
                                                <td class="d-none d-sm-table-cell"><?php echo htmlspecialchars($counter['queue_count'] ?? '&mdash;'); ?></td>
                                                <td class="text-end">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-outline-primary text-primary btn-update-counter" data-id="<?php echo htmlspecialchars($counter['idcounter']); ?>" title="Update"><i class="bi bi-pencil-square"></i></button>
                                                        <button type="button" class="btn btn-outline-danger btn-delete-counter" data-id="<?php echo htmlspecialchars($counter['idcounter']); ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <div class="mb-2"><i class="bi bi-collection display-6 text-muted"></i></div>
                                                <div class="fw-bold">No counters assigned</div>
                                                <div class="small text-muted mb-2">Assign employees to counters so they can start serving customers.</div>
                                                <button type="button" class="btn btn-success btn-sm" onclick="document.getElementById('btn-add-counter').click();">Add Counter</button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="countersOverlay" class="d-none loader-overlay"><div><div class="spinner-border text-primary" role="status" aria-hidden="true"></div><div class="small text-muted mt-2">Loading...</div></div></div>
                    </div>
                    <nav aria-label="">
                        <ul class="pagination justify-content-center" id="countersPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW COUNTER -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Modal title</h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD COUNTER (redesigned to match Update/Delete) -->
    <div class="modal fade" id="addCounterModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-between text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="addCounterTitle">Add Counter</h5>
                        <div class="small text-white-50">Assign an employee and set the counter number</div>
                    </div>
                    <div class="text-end">
                        <div class="h5 mb-0"><strong id="addCounterDisplay"></strong></div>
                    </div>
                </div>
                <div class="modal-body py-3 px-4" id="addCounterBody">
                    <form method="POST" id="frmAddCounter">
                        <div class="alert alert-danger w-100 d-none" id="addCounterAlert">
                            <span id="addCounterAlertMsg"></span>
                        </div>

                        <input type="hidden" name="counter_no_add" id="counter_no_add">

                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small">Search available employees</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="addSearchUsername" placeholder="Search Username">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Priority Lane</label>
                                        <select class="form-select" name="transaction-filter-priority-add" id="transaction-filter-priority-add">
                                            <option value="N">No</option>
                                            <option value="Y">Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-6 d-flex align-items-end">
                                        <label class="form-label small mb-1">Counter Number</label>
                                        <input type="number" name="counter_no_add_visible" id="counter_no_add_visible" class="form-control ms-2" placeholder="#" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Employees Available</label>
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="row" id="cards-add-counter-available"></div>
                                </div>
                                <div class="card-footer bg-white">
                                    <nav class="w-100" aria-label="Page navigation example">
                                        <ul class="mt-4 pagination justify-content-center" id="addCounterPagination">
                                            <li class="page-item disabled"><a href="#" class="page-link" data-page="1">Previous</a></li>
                                            <li class="page-item active"><span class="page-link">1</span></li>
                                            <li class="page-item"><a href="#" class="page-link" data-page="2">Next</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="d-flex justify-content-end w-100">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success" id="btnAddCounterSubmit">Add Counter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>

    <!-- UPDATE COUNTER (redesigned) -->
    <div class="modal fade" id="updateCounterModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-between text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="updateCounterTitle">Update Counter</h5>
                        <div class="small text-white-50">Edit assignment, number and priority</div>
                    </div>
                    <div class="text-end">
                        <div class="h4 mb-0" id="updateCounterDisplay">#<span class="fw-bold"></span></div>
                    </div>
                </div>
                <div class="modal-body py-3 px-4" id="updateCounterBody">
                    <form method="POST" id="frmUpdateCounter">
                        <div class="alert alert-danger w-100 d-none" id="updateCounterAlert">
                            <span id="updateCounterAlertMsg"></span>
                        </div>

                        <input type="hidden" name="update_counter_no" id="update_counter_no">
                        <input type="hidden" name="update_id" id="update_id">

                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small">Search available employees</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="updateSearchUsername" placeholder="Search Username">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Priority Lane</label>
                                        <select class="form-select" name="transaction-filter-priority-update" id="transaction-filter-priority-update">
                                            <option value="N">No</option>
                                            <option value="Y">Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-6 d-flex align-items-end">
                                        <label class="form-label small mb-1">Counter Number</label>
                                        <input type="number" name="counter_no_update_visible" id="counter_no_update_visible" class="form-control ms-2" placeholder="#" min="1">
                                    </div>
                                </div>
                                <!-- keep hidden spans so JS that updates these IDs doesn't break -->
                                <div class="d-none">
                                    <span id="updateCounterUsername">&mdash;</span>
                                    <span id="updateCounterNumber">Counter No: &mdash;</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <!-- Controls moved to the left column to improve layout on small screens -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Employees Available</label>
                            <div class="card">
                                <div class="card-body p-2">
                                    <!-- employees will be rendered as cards here -->
                                    <div class="row" id="cards-update-counter-available"></div>
                                </div>
                                <div class="card-footer bg-white">
                                    <nav class="w-100" aria-label="Page navigation example">
                                        <ul class="mt-4 pagination justify-content-center" id="updateCounterPagination">
                                            <li class="page-item disabled"><a href="#" class="page-link" data-page="1">Previous</a></li>
                                            <li class="page-item active"><span class="page-link">1</span></li>
                                            <li class="page-item"><a href="#" class="page-link" data-page="2">Next</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="d-flex justify-content-end w-100">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="btnUpdateCounterSubmit">Update Counter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE COUNTER (redesigned) -->
    <div class="modal fade" id="deleteCounterModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger d-flex justify-content-between text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="deleteCounterTitle">Delete Counter</h5>
                        <div class="small text-white-50">This action cannot be undone</div>
                    </div>
                    <div class="text-end">
                        <span class="h5 mb-0">#<strong id="deleteCounterDisplay">&mdash;</strong></span>
                    </div>
                </div>
                <form method="POST" id="frmDeleteCounter">
                    <div class="modal-body py-4 px-4" id="deleteEmployeeBody">
                        <div class="mb-3">
                            <div class="alert alert-danger w-100 d-none" id="deleteCounterAlert">
                                <span id="deleteCounterAlertMsg"></span>
                            </div>
                        </div>
                        <input type="hidden" name="delete_id" id="delete_id">
                        <div class="text-center mb-3">
                            <div class="display-4 text-danger mb-2"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <h5 class="fw-bold">Confirm deletion</h5>
                            <p class="mb-0">Are you sure you want to remove <strong><span id="deleteCounterUsername">&mdash;</span></strong> from counter <strong><span id="deleteCounterNumber">&mdash;</span></strong>?</p>
                        </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="delete_force" name="delete_force">
                                <label class="form-check-label small text-danger" for="delete_force">
                                    Force delete — permanently remove this counter entry (admin only)
                                </label>
                            </div>
                            <div id="deleteModeDesc" class="small text-muted mt-2">By default, detaching will unassign the employee but will not reset today's counter counts. Past transactions remain intact.</div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning" id="btnDeleteCounterSubmit">Detach Counter</button>
                            </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php include_once './../includes/footer.php'; ?>
    <?php after_js()?>
    <script>
        var counter_search = '';
    var counter_page = 1;
    var paginate = 25;

        // small helper to escape HTML inserted via JS
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

    var counter_page_modal = 1;
    var update_selected_employee = null;
    // last fetched counters (used for CSV export)
    var lastCounters = [];
    // filter state
    var counter_filter_availability = 'none';
    var counter_filter_priority = 'none';

    // Ensure a placeholder exists so callers won't throw if the real implementation
    // isn't defined yet (helps avoid ReferenceError during incremental loading).
    window.loadUpdateAvailableEmployees = window.loadUpdateAvailableEmployees || function() {
        console.warn('loadUpdateAvailableEmployees placeholder invoked');
    };

        function loadCounters() {
            let container = document.getElementById('cards-counters-registered');
                if (container) {
                const paramsObj = {
                    counters: true,
                    page: counter_page,
                    paginate: paginate,
                    search: counter_search,
                    availability: counter_filter_availability,
                    priority: counter_filter_priority
                };
                // remove unspecified filters to keep query clean
                Object.keys(paramsObj).forEach(k => {
                    if (paramsObj[k] === 'none' || paramsObj[k] === '' || paramsObj[k] === undefined || paramsObj[k] === null) {
                        delete paramsObj[k];
                    }
                });
                const params = new URLSearchParams(paramsObj);
                $.ajax({
                    // Call the Flask counters API (compat layer). Include credentials so cookies are sent when available.
                    url: "http://127.0.0.1:5000/api/counters?" + params,
                    type: 'GET',
                    timeout: 10000,
                    xhrFields: { withCredentials: true },
                    crossDomain: true,
                        success: function(response) {
                            // render as cards
                            try { container.innerHTML = ''; } catch (ex) { console.error(ex); }
                            if (response.status === 'success') {
                                const counters = response.counters || [];
                                // keep last result for client-side export/inspection
                                try { lastCounters = counters.slice(); } catch (ex) { lastCounters = counters; }
                                // Render pagination: prefer total if provided, otherwise show simple prev/current/next
                                if (typeof response.total !== 'undefined') {
                                    const total = parseInt(response.total, 10);
                                    const totalPages = Math.max(1, Math.ceil(total / paginate));
                                    renderPagination(totalPages, counter_page);
                                } else {
                                    const hasMore = counters.length === paginate;
                                    renderPaginationUnknown(counter_page, hasMore);
                                }

                                if (counters.length === 0) {
                                    container.innerHTML = `
                                        <div class="col-12 d-flex justify-content-center">
                                            <div class="card text-center" style="max-width:420px;">
                                                <div class="card-body">
                                                    <div class="display-6 text-muted mb-2"><i class="bi bi-collection"></i></div>
                                                    <h5 class="card-title">No counters assigned</h5>
                                                    <p class="card-text text-muted mb-3">Assign employees to counters so they can start serving customers.</p>
                                                    <button type="button" class="btn btn-success" onclick="document.getElementById('btn-add-counter').click();">Add Counter</button>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    return;
                                }
                                counters.forEach(counter => {
                                    const col = document.createElement('div');
                                    col.className = 'col-12';
                                    col.innerHTML = `
                                        <div class="card">
                                            <div class="card-body d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-size:1.25rem;font-weight:700;">${escapeHtml(String(counter.counterNumber || ''))}</div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">${escapeHtml(counter.username || '')}</div>
                                                            <div class="small text-muted">${escapeHtml(counter.role_type || '')}</div>
                                                        </div>
                                                </div>
                                                <div>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-outline-primary text-primary btn-update-counter" data-id="${counter.idcounter}" title="Update"><i class="bi bi-pencil-square"></i></button>
                                                        <button type="button" class="btn btn-outline-danger btn-delete-counter" data-id="${counter.idcounter}" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    container.appendChild(col);
                                });
                            } else {
                                container.innerHTML = `
                                    <div class="col-12 d-flex justify-content-center">
                                        <div class="card text-center" style="max-width:420px;">
                                            <div class="card-body">
                                                <div class="display-6 text-muted mb-2"><i class="bi bi-collection"></i></div>
                                                <h5 class="card-title">No counters assigned</h5>
                                                <p class="card-text text-muted mb-3">Assign employees to counters so they can start serving customers.</p>
                                                <button type="button" class="btn btn-success" onclick="document.getElementById('btn-add-counter').click();">Add Counter</button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        },
                        error: function(xhr, status, err) {
                            try {
                                // treat 404 as empty result (no counters found)
                                if (xhr && xhr.status === 404) {
                                    container.innerHTML = `
                                        <div class="col-12 d-flex justify-content-center">
                                            <div class="card text-center" style="max-width:420px;">
                                                <div class="card-body">
                                                    <div class="display-6 text-muted mb-2"><i class="bi bi-collection"></i></div>
                                                    <h5 class="card-title">No counters assigned</h5>
                                                    <p class="card-text text-muted mb-3">Assign employees to counters so they can start serving customers.</p>
                                                    <button type="button" class="btn btn-success" onclick="document.getElementById('btn-add-counter').click();">Add Counter</button>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    const pg = document.getElementById('countersPagination'); if (pg) pg.innerHTML = '';
                                    return;
                                }
                            } catch (ex) { console.error(ex); }
                            // generic error UI
                            try { container.innerHTML = `<div class="col-12 d-flex justify-content-center"><div class="text-danger">Error loading counters — <button class="btn btn-sm btn-secondary" onclick="loadCounters();">Retry</button></div></div>`; } catch (ex) { console.error(ex); }
                            console.error('Load counters failed', status, err, xhr && xhr.responseText);
                        }
                })
            }
        }

        // Render numeric pagination when totalPages is known
        function renderPagination(totalPages, currentPage) {
            const container = document.getElementById('countersPagination');
            if (!container) return;
            let items = '';

            const makeItem = (label, page, disabled, active, id) => {
                return `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"><a href="#" class="page-link" ${id ? `id="${id}"` : ''} data-page="${page}">${label}</a></li>`;
            };

            // First
            items += makeItem('First', 1, currentPage === 1, false, 'pageFirstCounters');
            // Prev
            items += makeItem('Previous', Math.max(1, currentPage - 1), currentPage === 1, false, 'pagePrevCounters');

            // Page numbers with collapsing
            const CAP = 5;
            let start = Math.max(2, currentPage - 2);
            let end = Math.min(totalPages - 1, currentPage + 2);
            if (currentPage <= 3) { start = 2; end = Math.min(totalPages - 1, CAP); }
            if (currentPage > totalPages - 3) { start = Math.max(2, totalPages - CAP); end = totalPages - 1; }

            // first page
            items += `<li class="page-item ${currentPage === 1 ? 'active' : ''}"><a href="#" class="page-link" data-page="1">1</a></li>`;
            if (start > 2) {
                items += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
            }

            for (let p = start; p <= end; p++) {
                items += `<li class="page-item ${p === currentPage ? 'active' : ''}"><a href="#" class="page-link" data-page="${p}">${p}</a></li>`;
            }

            if (end < totalPages - 1) {
                items += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
            }

            if (totalPages > 1) {
                items += `<li class="page-item ${currentPage === totalPages ? 'active' : ''}"><a href="#" class="page-link" data-page="${totalPages}">${totalPages}</a></li>`;
            }

            // Next
            items += makeItem('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages, false, 'pageNextCounters');
            // Last
            items += makeItem('Last', totalPages, currentPage === totalPages, false, 'pageLastCounters');

            container.innerHTML = items;
        }

        // Render simple pagination (unknown total) for counters
        function renderPaginationUnknown(currentPage, hasMore) {
            const container = document.getElementById('countersPagination');
            if (!container) return;
            const prevDisabled = currentPage === 1;
            const nextDisabled = !hasMore;
            const items = `
                <li class="page-item ${prevDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${Math.max(1, currentPage - 1)}">Previous</a></li>
                <li class="page-item active"><span class="page-link">${currentPage}</span></li>
                <li class="page-item ${nextDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${currentPage + 1}">Next</a></li>
            `;
            container.innerHTML = items;
        }

        // Delegated pagination handler for counters list
        const countersPagination = document.getElementById('countersPagination');
        if (countersPagination) {
            countersPagination.addEventListener('click', function (e) {
                e.preventDefault();
                const target = e.target.closest('a.page-link');
                if (!target) return;
                const pageAttr = target.getAttribute('data-page');
                if (pageAttr) {
                    const p = parseInt(pageAttr, 10);
                    if (!isNaN(p) && p > 0) {
                        counter_page = p;
                        loadCounters();
                    }
                }
            });
        }

        loadCounters();

        // Add Counter
    let btnAddCounterModal = document.getElementById('btn-add-counter');
    if (btnAddCounterModal) btnAddCounterModal.addEventListener('click', function(e) {
            counter_page_modal = 1;
            e.preventDefault();

            let form = document.getElementById('frmAddCounter');
            form.reset();
            // ensure visible counter field cleared
            try { const v = document.getElementById('counter_no_add_visible'); if (v) v.value = ''; } catch (ex) {}
            loadAddEmployees();
            // show Add modal after preparing form
            const addModalEl = document.getElementById('addCounterModal');
            const addModal = bootstrap.Modal.getOrCreateInstance(addModalEl);
            addModal.show();
        });

        function loadAddEmployees() {
            const container = document.getElementById('cards-add-counter-available');
            if (!container) return;

            const paramsObj = {
                counters: true,
                available: true,
                search: counter_search,
                page: counter_page_modal,
                paginate: paginate,
                availability: counter_filter_availability,
                priority: counter_filter_priority
            };
            // remove unspecified filters
            Object.keys(paramsObj).forEach(k => {
                if (paramsObj[k] === 'none' || paramsObj[k] === '' || paramsObj[k] === undefined || paramsObj[k] === null) {
                    delete paramsObj[k];
                }
            });
            const params = new URLSearchParams(paramsObj);

            $.ajax({
                url: "http://127.0.0.1:5000/api/counters?" + params,
                type: 'GET',
                timeout: 10000,
                xhrFields: { withCredentials: true },
                crossDomain: true,
                success: function (response) {
                    container.innerHTML = '';
                    if (response.status === 'success') {
                        const employees = response.counters || [];
                        if (employees.length === 0) {
                            container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                            renderAddPaginationUnknown(counter_page_modal, false);
                            return;
                        }

                        employees.forEach(employee => {
                            const card = document.createElement('div');
                            card.className = 'col-12 mb-2';
                            card.innerHTML = `
                                <div class="card">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <input class="form-check-input" type="radio" name="employee-counter-set" id="employee-counter-set-${employee.id}" value="${employee.id}">
                                            </div>
                                            <div>
                                                <div class="fw-bold">${escapeHtml(employee.username)}</div>
                                                <div class="small text-muted">${escapeHtml(employee.role_type || '')}</div>
                                            </div>
                                        </div>
                                        <div>
                                            ${textBadge(employee.availability, employee.availability === 'Available' ? 'success' : employee.availability === 'Assigned' ? 'danger' : 'warning')}
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.appendChild(card);
                        });

                        // update pagination UI
                        try {
                            const hasMore = employees.length === paginate;
                            renderAddPaginationUnknown(counter_page_modal, hasMore);
                        } catch (ex) { console.error(ex); }
                    } else {
                        container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                        renderAddPaginationUnknown(counter_page_modal, false);
                    }
                },
                error: function (xhr, status, error) {
                    try {
                        // treat 404 as empty available employees
                        if (xhr && xhr.status === 404) {
                            container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                            renderAddPaginationUnknown(counter_page_modal, false);
                            return;
                        }
                    } catch (ex) { console.error(ex); }
                    console.error('Error loading employees for add modal:', error, xhr && xhr.responseText);
                }
            });
        }

        // Load available employees into the Update Counter modal (pre-checks current assignee)
        // (Duplicated earlier to ensure the function is defined before it's called from the update flow.)
        window.loadUpdateAvailableEmployees = function() {
            console.log('loadUpdateAvailableEmployees() called; page modal:', counter_page_modal, 'selected:', update_selected_employee);
            const container = document.getElementById('cards-update-counter-available');
            if (!container) { console.warn('cards-update-counter-available container not found'); return; }

            const paramsObj = {
                counters: true,
                available: true,
                search: counter_search,
                page: counter_page_modal,
                paginate: paginate,
                availability: counter_filter_availability,
                priority: counter_filter_priority
            };
            Object.keys(paramsObj).forEach(k => {
                if (paramsObj[k] === 'none' || paramsObj[k] === '' || paramsObj[k] === undefined || paramsObj[k] === null) {
                    delete paramsObj[k];
                }
            });
            const params = new URLSearchParams(paramsObj);

            $.ajax({
                url: "http://127.0.0.1:5000/api/counters?" + params,
                type: 'GET',
                timeout: 10000,
                xhrFields: { withCredentials: true },
                crossDomain: true,
                success: function (response) {
                    container.innerHTML = '';
                    console.log('loadUpdateAvailableEmployees: response', response && response.status, response && response.counters && response.counters.length);
                    if (response.status === 'success') {
                        const employees = response.counters || [];
                        if (employees.length === 0) {
                            container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                            renderUpdatePaginationUnknown(counter_page_modal, false);
                            return;
                        }

                        employees.forEach(employee => {
                            const card = document.createElement('div');
                            card.className = 'col-12 mb-2';
                            card.innerHTML = `
                                <div class="card">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <input class="form-check-input" type="radio" name="employee-counter-set" id="employee-counter-set-${employee.id}" value="${employee.id}">
                                            </div>
                                            <div>
                                                <div class="fw-bold">${escapeHtml(employee.username)}</div>
                                                <div class="small text-muted">${escapeHtml(employee.role_type || '')}</div>
                                            </div>
                                        </div>
                                        <div>
                                            ${textBadge(employee.availability, employee.availability === 'Available' ? 'success' : employee.availability === 'Assigned' ? 'danger' : 'warning')}
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.appendChild(card);
                        });

                        // If an employee was previously assigned, pre-check their radio
                        try {
                            if (update_selected_employee) {
                                const r = container.querySelector(`#employee-counter-set-${update_selected_employee}`);
                                if (r) {
                                    r.checked = true;
                                    const ev = new Event('change', { bubbles: true });
                                    r.dispatchEvent(ev);
                                }
                            }
                        } catch (ex) { console.error(ex); }

                        // update pagination UI
                        try {
                            const hasMore = employees.length === paginate;
                            renderUpdatePaginationUnknown(counter_page_modal, hasMore);
                        } catch (ex) { console.error(ex); }
                    } else {
                        container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                        renderUpdatePaginationUnknown(counter_page_modal, false);
                    }
                },
                error: function (xhr, status, error) {
                    try {
                        if (xhr && xhr.status === 404) {
                            container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                            renderUpdatePaginationUnknown(counter_page_modal, false);
                            return;
                        }
                    } catch (ex) { console.error(ex); }
                    console.error('Error loading employees for update modal:', error, xhr && xhr.responseText);
                }
            });
        }

        // Helper: show skeleton rows in counters table while loading
        function showSkeletonRowsCounters(count) {
            const tbody = document.getElementById('counters-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            for (let i = 0; i < count; i++) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><span class="placeholder col-4 placeholder-wave"></span></td>
                    <td class="d-none d-md-table-cell"><span class="placeholder col-6 placeholder-wave"></span></td>
                    <td class="d-none d-md-table-cell"><span class="placeholder col-4 placeholder-wave"></span></td>
                    <td class="d-none d-sm-table-cell"><span class="placeholder col-2 placeholder-wave"></span></td>
                    <td class="text-end"><span class="placeholder col-3 placeholder-wave"></span></td>
                `;
                tbody.appendChild(tr);
            }
        }

    // Delegated pagination handler for add modal
    const addCounterPagination = document.getElementById('addCounterPagination');
    if (addCounterPagination) {
        addCounterPagination.addEventListener('click', function (e) {
            e.preventDefault();
            const target = e.target.closest('a.page-link');
            if (!target) return;
            const pageAttr = target.getAttribute('data-page');
            if (pageAttr) {
                const p = parseInt(pageAttr, 10);
                if (!isNaN(p) && p > 0) {
                    counter_page_modal = p;
                    loadAddEmployees();
                }
            }
        });
    }

    let addSearchUsername = document.getElementById('addSearchUsername');
    if (addSearchUsername) addSearchUsername.addEventListener('keyup', function(e) {
            counter_page_modal = 1;
            e.preventDefault();
            counter_search = this.value;
            loadAddEmployees();
        });

    let frmAddEmployee = document.getElementById('frmAddCounter');
    if (frmAddEmployee) frmAddEmployee.addEventListener('submit', function(e) {
            e.preventDefault();

            let formAlert = document.getElementById('addCounterAlert');
            let formAlertMsg = document.getElementById('addCounterAlertMsg');

            // sync visible counter input into hidden field before building FormData
            try {
                const visible = document.getElementById('counter_no_add_visible');
                if (visible) {
                    const hidden = document.getElementById('counter_no_add');
                    if (hidden) hidden.value = visible.value;
                }
            } catch (ex) { console.error(ex); }

            const formData = new FormData(this);
            const selectedRadio = document.querySelector('input[name="employee-counter-set"]:checked');
            if (!selectedRadio) {
                formAlertMsg.innerText = 'Please select an employee from the list';
                formAlert.classList.remove('d-none');
                formAlert.classList.add('alert-danger');
                setTimeout(()=>{ formAlert.classList.add('d-none'); }, 4000);
                return;
            }
            const employee_id = selectedRadio.value;
            const counter_number = formData.get('counter_no_add');
            // priority select uses id/name transaction-filter-priority-add
            const priority = formData.get('transaction-filter-priority-add') || 'N';

            $.ajax({
                url: 'http://127.0.0.1:5000/api/counters',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                xhrFields: { withCredentials: true },
                crossDomain: true,
                data: JSON.stringify({
                    counterNumber: counter_number,
                    counter_priority: priority,
                    user_id: employee_id
                }),
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(()=>{
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.add('alert-danger');
                        formAlert.classList.remove('d-none', 'alert-success');
                        setTimeout(()=>{
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(xhr, status, err) {
                    try {
                        console.error('Add counter failed:', status, err, 'HTTP', xhr.status);
                        console.error('Response:', xhr.responseText);
                        formAlertMsg.innerText = 'Failed to create counter: ' + (xhr.responseText || status);
                        formAlert.classList.remove('d-none');
                        formAlert.classList.add('alert-danger');
                    } catch (ex) { console.error(ex); }
                }
            });
        });

        // Open Update modal after loading counter details
        $(document).on('click', '.btn-update-counter', function (e) {
            e.preventDefault();
            counter_search = '';
            counter_page_modal = 1;

            const counterId = this.dataset.id;

            const params = new URLSearchParams({
                counters: true,
                id: counterId,
                page: counter_page_modal,
                paginate: paginate,
            });
            let frmUpdateCounter = document.getElementById('frmUpdateCounter');

            $.ajax({
                url: "http://127.0.0.1:5000/api/counters?" + params,
                type: 'GET',
                timeout: 10000,
                xhrFields: { withCredentials: true },
                crossDomain: true,
                // server sometimes emits multiple JSON objects concatenated which breaks jQuery's parser
                // request as text and parse the first JSON object manually to be robust
                dataType: 'text',
                success: function (raw) {
                        let response = null;
                        try {
                            response = JSON.parse(raw);
                        } catch (e) {
                            // try to recover when PHP emitted multiple JSON objects back-to-back like: {...}{...}
                            const idx = raw.indexOf('}{');
                            if (idx !== -1) {
                                const first = raw.substring(0, idx+1);
                                try {
                                    response = JSON.parse(first);
                                } catch (e2) {
                                    console.error('Failed to parse first JSON object from concatenated response', e2);
                                }
                            }
                        }

                        if (!response) {
                            console.error('Failed to parse JSON from update counter response');
                            console.error('Raw response (first 2k chars):\n', raw && raw.substring ? raw.substring(0, 2000) : raw);
                            return;
                        }

                        if (response.status === 'success') {
                        const counter = response.counter;
                        // Update header display: keep the '#' prefix and bold span
                        const displaySpan = document.querySelector('#updateCounterDisplay .fw-bold');
                        if (displaySpan) {
                            displaySpan.innerText = counter.counterNumber;
                        } else {
                            // fallback
                            const updateCounterDisplay = document.getElementById('updateCounterDisplay');
                            if (updateCounterDisplay) updateCounterDisplay.innerText = '#' + counter.counterNumber;
                        }
                        let updateCounterUsername = document.getElementById('updateCounterUsername');
                        updateCounterUsername.innerText = counter.username;
                        let updateCounterNumber = document.getElementById('updateCounterNumber');
                        updateCounterNumber.innerText = 'Counter No: ' + counter.counterNumber;
                        let update_id = document.getElementById('update_id');
                        update_id.value = counter.idcounter;

                        // set the selected employee id so loadUpdateEmployees can pre-check it
                        update_selected_employee = counter.idemployee;

                        frmUpdateCounter.reset();
                        frmUpdateCounter.elements['update_id'].value = counter.idcounter;
                        frmUpdateCounter.elements['update_counter_no'].value = counter.counterNumber;
                        // populate visible counter number input in redesigned modal
                        try {
                            const visibleCounter = document.getElementById('counter_no_update_visible');
                            if (visibleCounter) visibleCounter.value = counter.counterNumber;
                        } catch (ex) { console.error(ex); }

                        // set the priority select value if present
                        try {
                            const prSel = document.getElementById('transaction-filter-priority-update');
                            if (prSel) prSel.value = counter.counter_priority === 'Y' ? 'Y' : 'N';
                        } catch (ex) { console.error(ex); }

                        // load available employees for update (keeps existing behavior)
                        if (typeof loadUpdateAvailableEmployees === 'function') {
                            loadUpdateAvailableEmployees();
                        } else {
                            console.error('loadUpdateAvailableEmployees is not defined at callsite');
                        }

                        // show the modal after fields are populated (loadUpdateEmployees will pre-check the radio)
                        const modalEl = document.getElementById('updateCounterModal');
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                },
                error: function (xhr, status, err) {
                    // Improve error logging: show HTTP status and response body to help debug server-side errors
                    try {
                        console.error('Failed to load counter details:', status, err, 'HTTP', xhr.status);
                        console.error('Response text (first 2k chars):\n', xhr.responseText && xhr.responseText.substring ? xhr.responseText.substring(0, 2000) : xhr.responseText);
                    } catch (ex) {
                        console.error('Failed to log error details', ex);
                    }
                }
            });
        });

    // Delegated pagination handler for update modal (same pattern as Employees page)
    const updateCounterPagination = document.getElementById('updateCounterPagination');
    if (updateCounterPagination) {
        updateCounterPagination.addEventListener('click', function (e) {
            e.preventDefault();
            const target = e.target.closest('a.page-link');
            if (!target) return;
            const pageAttr = target.getAttribute('data-page');
            if (pageAttr) {
                const p = parseInt(pageAttr, 10);
                if (!isNaN(p) && p > 0) {
                    counter_page_modal = p;
                    loadUpdateEmployees();
                }
            }
        });
    }

    let updateSearchUsername = document.getElementById('updateSearchUsername');
    if (updateSearchUsername) updateSearchUsername.addEventListener('keyup', function(e) {
            counter_page_modal = 1;
            e.preventDefault();
            counter_search = this.value;
            loadUpdateEmployees();
        });

        function loadUpdateEmployees() {
            // Use the new table body for rendering counters
            let container = document.getElementById('counters-tbody');
            if (container) {
                const paramsObj = {
                    counters: true,
                    page: counter_page,
                    paginate: paginate,
                    search: counter_search,
                    availability: counter_filter_availability,
                    priority: counter_filter_priority
                };
                Object.keys(paramsObj).forEach(k => {
                    if (paramsObj[k] === 'none' || paramsObj[k] === '' || paramsObj[k] === undefined || paramsObj[k] === null) {
                        delete paramsObj[k];
                    }
                });
                const params = new URLSearchParams(paramsObj);

                // show overlay
                const overlay = document.getElementById('countersOverlay');
                if (overlay) overlay.classList.remove('d-none');

                // show skeleton rows
                showSkeletonRowsCounters(6);

                $.ajax({
                    url: "http://127.0.0.1:5000/api/counters?" + params,
                    type: 'GET',
                    timeout: 10000,
                    xhrFields: { withCredentials: true },
                    crossDomain: true,
                    success: function(response) {
                        try { container.innerHTML = ''; } catch (ex) { console.error(ex); }

                        if (response.status === 'success' && (response.counters && response.counters.length >= 0)) {
                            const counters = response.counters || [];
                            // cache for export/inspection
                            try { lastCounters = counters.slice(); } catch (ex) { lastCounters = counters; }

                            // pagination
                            if (typeof response.total !== 'undefined') {
                                const total = parseInt(response.total, 10);
                                const totalPages = Math.max(1, Math.ceil(total / paginate));
                                renderPagination(totalPages, counter_page);
                            } else if (typeof response.total_counters !== 'undefined') {
                                const total = parseInt(response.total_counters, 10);
                                const totalPages = Math.max(1, Math.ceil(total / paginate));
                                renderPagination(totalPages, counter_page);
                            } else {
                                const hasMore = counters.length === paginate;
                                renderPaginationUnknown(counter_page, hasMore);
                            }

                            if (counters.length === 0) {
                                container.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No counters assigned — <button class=\"btn btn-sm btn-success\" onclick=\"document.getElementById('btn-add-counter').click();\">Add Counter</button></td></tr>`;
                                if (overlay) overlay.classList.add('d-none');
                                return;
                            }

                            counters.forEach(counter => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td><strong>${escapeHtml(String(counter.counterNumber || '—'))}</strong></td>
                                    <td class="d-none d-md-table-cell">${escapeHtml(counter.username || '—')}</td>
                                    <td class="d-none d-md-table-cell small text-muted">${escapeHtml(counter.role_type || '—')}</td>
                                    <td class="d-none d-sm-table-cell">${escapeHtml(String(counter.queue_count || '—'))}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-primary text-primary btn-update-counter" data-id="${counter.idcounter}" title="Update"><i class="bi bi-pencil-square"></i></button>
                                            <button type="button" class="btn btn-outline-danger btn-delete-counter" data-id="${counter.idcounter}" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                        </div>
                                    </td>
                                `;
                                container.appendChild(tr);
                            });

                        } else {
                            container.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No counters assigned — <button class=\"btn btn-sm btn-success\" onclick=\"document.getElementById('btn-add-counter').click();\">Add Counter</button></td></tr>`;
                        }

                        if (overlay) overlay.classList.add('d-none');
                    },
                    error: function(xhr, status, err) {
                        const overlay = document.getElementById('countersOverlay'); if (overlay) overlay.classList.add('d-none');
                        try {
                            // If server returned 404, show friendly 'no counters' row
                            if (xhr && xhr.status === 404) {
                                container.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No counters assigned — <button class="btn btn-sm btn-success" onclick="document.getElementById('btn-add-counter').click();">Add Counter</button></td></tr>`;
                                const pg = document.getElementById('countersPagination'); if (pg) pg.innerHTML = '';
                                return;
                            }
                        } catch (ex) { console.error(ex); }
                        try { container.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Error loading counters — <button class="btn btn-sm btn-secondary" onclick="loadCounters();">Retry</button></td></tr>`; } catch(ex){}
                        console.error('Load counters failed', status, err, xhr && xhr.responseText);
                    }
                });
            }

    // Load available employees into the Update Counter modal (pre-checks current assignee)
    window.loadUpdateAvailableEmployees = function() {
        console.log('loadUpdateAvailableEmployees() called; page modal:', counter_page_modal, 'selected:', update_selected_employee);
        const container = document.getElementById('cards-update-counter-available');
        if (!container) { console.warn('cards-update-counter-available container not found'); return; }

            const paramsObj = {
                counters: true,
                available: true,
                search: counter_search,
                page: counter_page_modal,
                paginate: paginate,
                availability: counter_filter_availability,
                priority: counter_filter_priority
            };
            Object.keys(paramsObj).forEach(k => {
                if (paramsObj[k] === 'none' || paramsObj[k] === '' || paramsObj[k] === undefined || paramsObj[k] === null) {
                    delete paramsObj[k];
                }
            });
            const params = new URLSearchParams(paramsObj);

            $.ajax({
                url: "http://127.0.0.1:5000/api/counters?" + params,
                type: 'GET',
                timeout: 10000,
                xhrFields: { withCredentials: true },
                crossDomain: true,
                success: function (response) {
                    container.innerHTML = '';
                        console.log('loadUpdateAvailableEmployees: response', response && response.status, response && response.counters && response.counters.length);
                        if (response.status === 'success') {
                            const employees = response.counters || [];
                        if (employees.length === 0) {
                            container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                            renderUpdatePaginationUnknown(counter_page_modal, false);
                            return;
                        }

                        employees.forEach(employee => {
                            const card = document.createElement('div');
                            card.className = 'col-12 mb-2';
                            card.innerHTML = `
                                <div class="card">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <input class="form-check-input" type="radio" name="employee-counter-set" id="employee-counter-set-${employee.id}" value="${employee.id}">
                                            </div>
                                            <div>
                                                <div class="fw-bold">${escapeHtml(employee.username)}</div>
                                                <div class="small text-muted">${escapeHtml(employee.role_type || '')}</div>
                                            </div>
                                        </div>
                                        <div>
                                            ${textBadge(employee.availability, employee.availability === 'Available' ? 'success' : employee.availability === 'Assigned' ? 'danger' : 'warning')}
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.appendChild(card);
                        });

                        // If an employee was previously assigned, pre-check their radio
                        try {
                            if (update_selected_employee) {
                                const r = container.querySelector(`#employee-counter-set-${update_selected_employee}`);
                                if (r) {
                                    r.checked = true;
                                    const ev = new Event('change', { bubbles: true });
                                    r.dispatchEvent(ev);
                                }
                            }
                        } catch (ex) { console.error(ex); }

                        // update pagination UI
                        try {
                            const hasMore = employees.length === paginate;
                            renderUpdatePaginationUnknown(counter_page_modal, hasMore);
                        } catch (ex) { console.error(ex); }
                    } else {
                        container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                        renderUpdatePaginationUnknown(counter_page_modal, false);
                    }
                },
                error: function (xhr, status, error) {
                    try {
                        if (xhr && xhr.status === 404) {
                            container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                            renderUpdatePaginationUnknown(counter_page_modal, false);
                            return;
                        }
                    } catch (ex) { console.error(ex); }
                    console.error('Error loading employees for update modal:', error, xhr && xhr.responseText);
                }
            });
        }
        }

        // Render simple pagination (unknown total) for update modal
        function renderUpdatePaginationUnknown(currentPage, hasMore) {
            const container = document.getElementById('updateCounterPagination');
            if (!container) return;
            const prevDisabled = currentPage === 1;
            const nextDisabled = !hasMore;
            const items = `
                <li class="page-item ${prevDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${Math.max(1, currentPage - 1)}">Previous</a></li>
                <li class="page-item active"><span class="page-link">${currentPage}</span></li>
                <li class="page-item ${nextDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${currentPage + 1}">Next</a></li>
            `;
            container.innerHTML = items;
        }

        // Render simple pagination (unknown total) for add modal
        function renderAddPaginationUnknown(currentPage, hasMore) {
            const container = document.getElementById('addCounterPagination');
            if (!container) return;
            const prevDisabled = currentPage === 1;
            const nextDisabled = !hasMore;
            const items = `
                <li class="page-item ${prevDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${Math.max(1, currentPage - 1)}">Previous</a></li>
                <li class="page-item active"><span class="page-link">${currentPage}</span></li>
                <li class="page-item ${nextDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${currentPage + 1}">Next</a></li>
            `;
            container.innerHTML = items;
        }

        // Highlight selected employee card for add modal; allow clicking a card to select
        (function setupAddCardSelection() {
            const container = document.getElementById('cards-add-counter-available');
            if (!container) return;

            container.addEventListener('change', function(e) {
                const target = e.target;
                if (!target || target.name !== 'employee-counter-set') return;
                container.querySelectorAll('.card').forEach(c => c.classList.remove('border','border-primary','bg-light'));
                const card = target.closest('.card');
                if (card) card.classList.add('border','border-primary','bg-light');
            });

            container.addEventListener('click', function(e) {
                const card = e.target.closest('.card');
                if (!card) return;
                const radio = card.querySelector('input[name="employee-counter-set"]');
                if (radio) {
                    if (!radio.checked) {
                        radio.checked = true;
                        const ev = new Event('change', { bubbles: true });
                        radio.dispatchEvent(ev);
                    }
                }
            });

            const observer = new MutationObserver(function() {
                const checked = container.querySelector('input[name="employee-counter-set"]:checked');
                if (checked) {
                    container.querySelectorAll('.card').forEach(c => c.classList.remove('border','border-primary','bg-light'));
                    const card = checked.closest('.card');
                    if (card) card.classList.add('border','border-primary','bg-light');
                }
            });
            observer.observe(container, { childList: true, subtree: true });
        })();

        // Highlight selected employee card when its radio is checked; allow clicking a card to select
        (function setupCardSelection() {
            const container = document.getElementById('cards-update-counter-available');
            if (!container) return;

            // Delegate change events from radios
            container.addEventListener('change', function(e) {
                const target = e.target;
                if (!target || target.name !== 'employee-counter-set') return;
                // remove highlight from all cards
                container.querySelectorAll('.card').forEach(c => c.classList.remove('border','border-primary','bg-light'));
                const card = target.closest('.card');
                if (card) card.classList.add('border','border-primary','bg-light');
            });

            // Clicking a card selects the radio inside it
            container.addEventListener('click', function(e) {
                const card = e.target.closest('.card');
                if (!card) return;
                const radio = card.querySelector('input[name="employee-counter-set"]');
                if (radio) {
                    if (!radio.checked) {
                        radio.checked = true;
                        const ev = new Event('change', { bubbles: true });
                        radio.dispatchEvent(ev);
                    }
                }
            });

            // When cards are rendered, ensure any pre-checked radio highlights its card
            const observer = new MutationObserver(function() {
                const checked = container.querySelector('input[name="employee-counter-set"]:checked');
                if (checked) {
                    container.querySelectorAll('.card').forEach(c => c.classList.remove('border','border-primary','bg-light'));
                    const card = checked.closest('.card');
                    if (card) card.classList.add('border','border-primary','bg-light');
                }
            });
            observer.observe(container, { childList: true, subtree: true });
        })();
    let frmUpdateCounter = document.getElementById('frmUpdateCounter');
    if (frmUpdateCounter) frmUpdateCounter.addEventListener('submit', function(e) {
            e.preventDefault();

            let formAlert = document.getElementById('updateCounterAlert');
            let formAlertMsg = document.getElementById('updateCounterAlertMsg');

            // If the visible counter input exists, sync it into the hidden field so FormData picks it up
            try {
                const visibleCounter = document.getElementById('counter_no_update_visible');
                if (visibleCounter) {
                    const hidden = document.getElementById('update_counter_no');
                    if (hidden) hidden.value = visibleCounter.value;
                }
            } catch (ex) { console.error(ex); }

            const formData = new FormData(this);    // RESERVE :>

            const idcounter = formData.get('update_id');
            const selectedRadio = document.querySelector('input[name="employee-counter-set"]:checked');
            if (!selectedRadio) {
                formAlertMsg.innerText = 'Please select an employee to assign to this counter';
                formAlert.classList.remove('d-none');
                formAlert.classList.add('alert-danger');
                setTimeout(()=>{ formAlert.classList.add('d-none'); }, 4000);
                return;
            }
            const employee_id = selectedRadio.value;
            const counter_number = formData.get('update_counter_no');
            const priority = formData.get('transaction-filter-priority-update') || 'N';

            $.ajax({
                url: 'http://127.0.0.1:5000/api/counters',
                type: 'PUT',
                contentType: 'application/json',
                dataType: 'json',
                xhrFields: { withCredentials: true },
                crossDomain: true,
                data: JSON.stringify({
                    counter_id: idcounter,
                    counterNumber: counter_number,
                    idemployee: employee_id,
                    counter_priority: priority
                }),
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(()=>{
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.add('alert-danger');
                        formAlert.classList.remove('d-none', 'alert-success');
                        setTimeout(()=>{
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(xhr, status, err) {
                    try {
                        console.error('Update counter failed:', status, err, 'HTTP', xhr.status);
                        console.error('Response:', xhr.responseText);
                        formAlertMsg.innerText = 'Failed to update counter: ' + (xhr.responseText || status);
                        formAlert.classList.remove('d-none');
                        formAlert.classList.add('alert-danger');
                    } catch (ex) { console.error(ex); }
                }
            });

        });

        // Open Delete modal after loading counter details
        $(document).on('click', '.btn-delete-counter', function (e) {
            e.preventDefault();
            const counterId = this.dataset.id;

            let frmDeleteCounter = document.getElementById('frmDeleteCounter');
            const params = new URLSearchParams({
                counters: true,
                id: counterId,
            });

            $.ajax({
                url: "http://127.0.0.1:5000/api/counters?" + params,
                type: 'GET',
                timeout: 10000,
                xhrFields: { withCredentials: true },
                crossDomain: true,
                // request as text and parse robustly because server may emit concatenated JSON objects
                dataType: 'text',
                success: function (raw) {
                    let response = null;
                    try {
                        response = JSON.parse(raw);
                    } catch (e) {
                        const idx = raw.indexOf('}{');
                        if (idx !== -1) {
                            const first = raw.substring(0, idx+1);
                            try { response = JSON.parse(first); } catch (e2) { console.error('Failed to parse first JSON from delete response', e2); }
                        }
                    }
                    if (!response) {
                        console.error('Failed to parse JSON from delete counter response');
                        console.error('Raw response (first 2k chars):\n', raw && raw.substring ? raw.substring(0,2000) : raw);
                        return;
                    }
                    if (response.status === 'success') {
                        let deleteCounterDisplay = document.getElementById('deleteCounterDisplay');
                        deleteCounterDisplay.innerText = response.counter.counterNumber;
                        let deleteCounterUsername = document.getElementById('deleteCounterUsername');
                        deleteCounterUsername.innerText = response.counter.username;
                        let deleteCounterNumber = document.getElementById('deleteCounterNumber');
                        deleteCounterNumber.innerText = response.counter.counterNumber;
                        frmDeleteCounter.reset();
                        frmDeleteCounter.elements['delete_id'].value = response.counter.idcounter;

                        // show delete modal
                        const modalEl = document.getElementById('deleteCounterModal');
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                },
                error: function (xhr, status, err) {
                    try {
                        console.error('Failed to load counter details for delete:', status, err, 'HTTP', xhr.status);
                        console.error('Response text (first 2k chars):\n', xhr.responseText && xhr.responseText.substring ? xhr.responseText.substring(0, 2000) : xhr.responseText);
                    } catch (ex) {
                        console.error('Failed to log delete error details', ex);
                    }
                }
            });
        });

    let frmDeleteCounter = document.getElementById('frmDeleteCounter');
    if (frmDeleteCounter) {
        // Wire up UI toggles for the delete modal so the button text and styles
        // reflect whether the user chose a soft detach or a hard force-delete.
        const deleteForceCheckbox = document.getElementById('delete_force');
        const btnDeleteCounterSubmit = document.getElementById('btnDeleteCounterSubmit');
        const deleteModeDesc = document.getElementById('deleteModeDesc');

        function updateDeleteModeUI() {
            if (!btnDeleteCounterSubmit) return;
            if (deleteForceCheckbox && deleteForceCheckbox.checked) {
                btnDeleteCounterSubmit.classList.remove('btn-warning');
                btnDeleteCounterSubmit.classList.add('btn-danger');
                btnDeleteCounterSubmit.innerText = 'Delete Counter';
                if (deleteModeDesc) deleteModeDesc.innerText = 'Force delete will permanently remove the counter row. Past transactions remain intact.';
            } else {
                btnDeleteCounterSubmit.classList.remove('btn-danger');
                btnDeleteCounterSubmit.classList.add('btn-warning');
                btnDeleteCounterSubmit.innerText = 'Detach Counter';
                if (deleteModeDesc) deleteModeDesc.innerText = 'Detach will unassign the employee but will not reset today\'s counter counts. Past transactions remain intact.';
            }
        }

        if (deleteForceCheckbox) deleteForceCheckbox.addEventListener('change', updateDeleteModeUI);
        // initialize UI
        updateDeleteModeUI();

        frmDeleteCounter.addEventListener('submit', function(e) {
            e.preventDefault();

            let formAlert = document.getElementById('deleteCounterAlert');
            let formAlertMsg = document.getElementById('deleteCounterAlertMsg');

            const formData = new FormData(this);
            const idcounter = formData.get('delete_id');
            const forceFlag = (formData.get('delete_force') === '1' || formData.get('delete_force') === 'on');

            $.ajax({
                url: 'http://127.0.0.1:5000/api/counters',
                type: 'DELETE',
                contentType: 'application/json',
                dataType: 'json',
                xhrFields: { withCredentials: true },
                crossDomain: true,
                data: JSON.stringify({
                    counter_id: idcounter,
                    force: !!forceFlag
                }),
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(()=>{
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.add('alert-danger');
                        formAlert.classList.remove('d-none', 'alert-success');
                        setTimeout(()=>{
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                }
            });
        });
    }

    let searchCounterRegistered = document.getElementById('searchCounterRegistered');
    if (searchCounterRegistered) searchCounterRegistered.addEventListener('keyup', function(e) {
            console.log(this.value);
            counter_page = 1;
            e.preventDefault();
            counter_search = this.value;
            loadCounters();
        });

    // Role filter removed from UI; role-based filtering is unnecessary for counters.

    const filterAvailability = document.getElementById('counter-filter-availability');
    if (filterAvailability) filterAvailability.addEventListener('change', function(e) {
        counter_filter_availability = this.value || 'none';
        counter_page = 1;
        loadCounters();
    });

    const filterPriority = document.getElementById('counter-filter-priority');
    if (filterPriority) filterPriority.addEventListener('change', function(e) {
        counter_filter_priority = this.value || 'none';
        counter_page = 1;
        loadCounters();
    });

    // Per-page selector, export and refresh handlers
    const countersPerPageSel = document.getElementById('countersPerPage');
    if (countersPerPageSel) {
        countersPerPageSel.addEventListener('change', function(e) {
            const v = parseInt(this.value, 10) || 25;
            paginate = v;
            counter_page = 1;
            loadCounters();
        });
        // initialize paginate to match select (in case server-side rendered default differs)
        try { paginate = parseInt(countersPerPageSel.value, 10) || paginate; } catch(ex){}
    }

    const btnExportCountersCsv = document.getElementById('btnExportCountersCsv');
    if (btnExportCountersCsv) btnExportCountersCsv.addEventListener('click', function(e) {
        e.preventDefault();
        // attempt to fetch all counters matching current filters for a complete export
        const paramsObj = {
            counters: true,
            page: 1,
            paginate: 10000,
            search: counter_search,
            availability: counter_filter_availability,
            priority: counter_filter_priority
        };
        Object.keys(paramsObj).forEach(k => {
            if (paramsObj[k] === 'none' || paramsObj[k] === '' || paramsObj[k] === undefined || paramsObj[k] === null) delete paramsObj[k];
        });
        const params = new URLSearchParams(paramsObj);

        const doExport = function(dataArray) {
            if (!dataArray || dataArray.length === 0) {
                alert('No counters to export');
                return;
            }
            const cols = ['counterNumber','username','role_type','queue_count','idcounter'];
            const header = ['Counter','Username','Role','Queue','ID'];
            const rows = [header.map(h => '"' + h.replace(/"/g,'""') + '"').join(',')];
            dataArray.forEach(c => {
                const row = cols.map(k => '"' + String((c[k] === null || c[k] === undefined) ? '' : String(c[k])).replace(/"/g,'""') + '"').join(',');
                rows.push(row);
            });
            const csv = rows.join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'counters_export.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        };

        // Fetch full dataset from API; fall back to cached lastCounters on failure
        $.ajax({
            url: 'http://127.0.0.1:5000/api/counters?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            xhrFields: { withCredentials: true },
            crossDomain: true,
            success: function(response) {
                if (response && response.status === 'success' && Array.isArray(response.counters)) {
                    doExport(response.counters);
                } else if (lastCounters && lastCounters.length) {
                    doExport(lastCounters);
                } else {
                    alert('No counters to export');
                }
            },
            error: function() {
                if (lastCounters && lastCounters.length) {
                    doExport(lastCounters);
                } else {
                    alert('Failed to fetch counters for export');
                }
            }
        });
    });

    const btnRefreshCounters = document.getElementById('btnRefreshCounters');
    if (btnRefreshCounters) btnRefreshCounters.addEventListener('click', function(e) {
        e.preventDefault();
        loadCounters();
    });



    </script>
</body>
</html>
