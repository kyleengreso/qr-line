<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();

$token = $_COOKIE['token'];
$token = decryptToken($token, $master_key);
$token = json_encode($token);
$token = json_decode($token);

$id = $token->id;
$username = $token->username;
$role_type = $token->role_type;
$email = $token->email;
$counterNumber = $token->counterNumber;
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
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer d-flex justify-content-center" style="margin-top:100px;min-height:500px">
        <div class="col-md-6" style="min-width:400px;max-width:900px;transform:scale(0.9)">
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
            <div class="card shadow">
                <div class="card-header">
                    <span>Counters</span>
                </div>
                <div class="card-body">
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-start my-1 mx-2 fw-bold">Counters</h3>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <a href="#" class="btn btn-success text-white px-4" id="btn-add-counter"><span class="fw-bold">+</span> Add New</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col-12">
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="bi bi-search"></i></div>
                                    <div class="form-floating">
                                        <input type="text" name="searchAdd" id="searchCounterRegistered" class="form-control" placeholder="Search username">
                                        <label for="searchAdd">Search Username</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-striped table-members" id="table-counters-registered">
                        <thead>
                            <th>#</th>
                            <!-- <th>Queue Count</th> -->
                            <th>Employee</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <?php if (!empty($counters)): ?>
                                <?php foreach ($counters as $counter): ?>
                                    <tr>
                                        <td style="min-width:20px;max-width:35px"><?php echo htmlspecialchars($counter['counterNumber'] ?? ''); ?></td>
                                        <td class="fw-bold role_type_employee_icon" style="min-width:40px">
                                            <?php echo phpUserStatusIcon($counter['username'] ?? '', $counter['role_type'] ?? '', $counter['active'] ?? 0); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-outline-primary text-primary btn-update-counter" data-id="<?php echo htmlspecialchars($counter['idcounter']); ?>" title="Update"><i class="bi bi-pencil-square"></i></button>
                                                <button type="button" class="btn btn-outline-danger btn-delete-counter" data-id="<?php echo htmlspecialchars($counter['idcounter']); ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="fw-bold text-center">No counters assigned</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <nav aria-label="">
                        <ul class="pagination justify-content-center">
                            <li class="page-item">
                                <a class="page-link disabled" id="pagePrevCounterRegistered">Previous</a>
                            </li>
                            <!-- Page number reserved -->
                            <li class="page-item">
                                <a class="page-link" id="pageNextCounterRegistered">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW COUNTER -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
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
    <div class="modal fade" id="addCounterModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-between text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="addCounterTitle">Add Counter</h5>
                        <div class="small text-white-50">Assign an employee and set the counter number</div>
                    </div>
                    <div class="text-end">
                        <div class="h5 mb-0">#<strong id="addCounterDisplay">&mdash;</strong></div>
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
    <div class="modal fade" id="updateCounterModal" tabindex="-1" role="dialog" aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog modal-lg" role="document">
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
    <div class="modal fade" id="deleteCounterModal" tabindex="-1" role="dialog" aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog modal-md" role="document">
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
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete Counter</button>
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
        var paginate = 10;

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

        function loadCounters() {
            let table_counters_registered = document.getElementById('table-counters-registered');
            if (table_counters_registered) {
                const params = new URLSearchParams({
                    counters: true,
                    page: counter_page,
                    paginate: paginate,
                    search: counter_search,
                });
                $.ajax({
                    url: realHost + '/public/api/api_endpoint.php?' + params,
                    type: 'GET',
                    success: function(response) {
                        while (table_counters_registered.rows.length > 1) {
                            table_counters_registered.deleteRow(-1);
                        }
                        if (response.status === 'success') {
                            const counters = response.counters;
                            if (counters.length < paginate) {
                                pageNextCounterRegistered.classList.add('disabled');
                            } else {
                                pageNextCounterRegistered.classList.remove('disabled');
                            }
                            counters.forEach(counter => {
                                let row = table_counters_registered.insertRow(-1);
                                row.innerHTML = `
                                        <td style="min-width:20px;max-width:35px">${counter.counterNumber}</td>
                                        <td class="fw-bold role_type_employee_icon" style="min-width:40px">
                                            <span>${userStatusIcon(counter.username, counter.role_type, counter.active)}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-outline-primary text-primary btn-update-counter" data-id="${counter.idcounter}" title="Update"><i class="bi bi-pencil-square"></i></button>
                                                <button type="button" class="btn btn-outline-danger btn-delete-counter" data-id="${counter.idcounter}" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                            </div>
                                        </td>
                                `;
                            });
                        } else {
                            let row = table_counters_registered.insertRow(-1);
                            row.innerHTML = `
                                <tr>
                                    <td colspan="3" class="fw-bold text-center">No counters assigned</td>
                                </tr>
                            `;
                        }
                    }
                })
            }
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

            const params = new URLSearchParams({
                counters: true,
                available: true,
                search: counter_search,
                page: counter_page_modal,
                paginate: paginate,
            });

            $.ajax({
                url: realHost + '/public/api/api_endpoint.php?' + params,
                type: 'GET',
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
                    console.error('Error loading employees for add modal:', error);
                }
            });
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
                url: realHost + '/public/api/api_endpoint.php',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    method: "counter-add",
                    idemployee: employee_id,
                    counterNumber: counter_number,
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
                url: realHost + '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
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
                        loadUpdateEmployees();

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
            const container = document.getElementById('cards-update-counter-available');
            if (!container) return;

            const params = new URLSearchParams({
                counters: true,
                available: true,
                search: counter_search,
                page: counter_page_modal,
                paginate: paginate,
            });

            $.ajax({
                url: realHost + '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function (response) {
                    // clear previous cards
                    container.innerHTML = '';

                        if (response.status === 'success') {
                        const employees = response.counters;
                        if (!employees || employees.length === 0) {
                            container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                                // update pagination UI
                                renderUpdatePaginationUnknown(counter_page_modal, false);
                            return;
                        }

                        const renderEmployees = (list) => {
                            list.forEach(employee => {
                                const checked = (update_selected_employee && update_selected_employee == employee.id) ? 'checked' : '';
                                const card = document.createElement('div');
                                card.className = 'col-12 mb-2';
                                card.innerHTML = `
                                    <div class="card">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <input class="form-check-input" type="radio" name="employee-counter-set" id="employee-counter-set-${employee.id}" value="${employee.id}" ${checked}>
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

                            // clear after use so subsequent loads don't accidentally re-check
                            update_selected_employee = null;

                            // update pagination UI (unknown total - use hasMore)
                            try {
                                const hasMore = list.length === paginate;
                                renderUpdatePaginationUnknown(counter_page_modal, hasMore);
                            } catch (ex) { console.error(ex); }
                        };

                        // If the assigned employee isn't present in the available list, fetch and prepend it so it can be auto-selected
                        if (update_selected_employee) {
                            const found = employees.some(e => String(e.id) === String(update_selected_employee));
                            if (!found) {
                                const singleParams = new URLSearchParams({ employees: true, id: update_selected_employee });
                                $.ajax({
                                    url: realHost + '/public/api/api_endpoint.php?' + singleParams,
                                    type: 'GET',
                                    dataType: 'json',
                                    success: function (singleResp) {
                                        if (singleResp && singleResp.status === 'success' && singleResp.employee) {
                                            // mark availability as Assigned to indicate current assignment
                                            singleResp.employee.availability = singleResp.employee.availability || 'Assigned';
                                            // prepend
                                            employees.unshift(singleResp.employee);
                                        }
                                        renderEmployees(employees);
                                    },
                                    error: function () {
                                        // if single fetch fails, just render the original list
                                        renderEmployees(employees);
                                    }
                                });
                            } else {
                                renderEmployees(employees);
                            }
                        } else {
                            renderEmployees(employees);
                        }
                    } else {
                        container.innerHTML = '<div class="text-center fw-bold w-100">No employee available</div>';
                        renderUpdatePaginationUnknown(counter_page_modal, false);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading employees:', error);
                }
            });
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
                url: realHost + '/public/api/api_endpoint.php',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    method: "counters-update",
                    id: idcounter,
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
                url: realHost + '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
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
    if (frmDeleteCounter) frmDeleteCounter.addEventListener('submit', function(e) {
            e.preventDefault();

            let formAlert = document.getElementById('deleteCounterAlert');
            let formAlertMsg = document.getElementById('deleteCounterAlertMsg');

            const formData = new FormData(this);
            const idcounter = formData.get('delete_id');

            $.ajax({
                url: realHost + '/public/api/api_endpoint.php',
                method: 'POST',
                data: JSON.stringify({
                    method: "counters-delete",
                    id: idcounter,
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

    let pagePrevCounterRegistered = document.getElementById('pagePrevCounterRegistered');
    if (pagePrevCounterRegistered) pagePrevCounterRegistered.addEventListener('click', function(e) {
            e.preventDefault();
            console.log(counter_search);
            if (counter_page > 1) {
                counter_page--;
                if (counter_page === 1) {
                    pagePrevCounterRegistered.classList.add('disabled');
                }
                loadCounters();
            }
        });

    let pageNextCounterRegistered = document.getElementById('pageNextCounterRegistered');
    if (pageNextCounterRegistered) pageNextCounterRegistered.addEventListener('click', function(e) {
            pagePrevCounterRegistered.classList.remove('disabled');
            e.preventDefault();
            counter_page++;
            loadCounters();
        });

    let searchCounterRegistered = document.getElementById('searchCounterRegistered');
    if (searchCounterRegistered) searchCounterRegistered.addEventListener('keyup', function(e) {
            console.log(this.value);
            counter_page = 1;
            pagePrevCounterRegistered.classList.add('disabled');
            e.preventDefault();
            counter_search = this.value;
            loadCounters();
        });



    </script>
</body>
</html>
