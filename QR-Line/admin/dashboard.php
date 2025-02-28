<?php
session_start();

include "./../includes/db_conn.php";
include "./../base.php";
include "./../asset/php/message.php";
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $message_success = $_SESSION['message-success'] ?? null;
    $message_error = $_SESSION['message-error'] ?? null;

    // GET all list from employees

    $stmt = $conn->prepare("SELECT id, username, created_at FROM employees");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $employees = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container" style="margin-top: 15vh">

        <div class="row justify-content-center mt-5">

            <div class="col-12 text-center p-2">
                <h1>DASHBOARD</h1>
            </div>
            <div class="col col-md-6">
                <div class="col mx-0 my-2">
                    <div class="card shadow-sm p-4">
                        <div class="row w-100 mb-4">
                            <h4 class="text-center">Counters</h4>
                        </div>

                        <table class="table table-striped table-members" id="table-counters">
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Counter No.</th>
                                <th>Queue Count</th>
                            </tr>
                        </table>
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center">
                                <li class="page-item">
                                <a class="page-link" onclick="prevPaginateCounters()" id="pagePrevEmployees">Previous</a>
                                </li>
                                <li class="page-item">
                                <a class="page-link" onclick="nextPaginateCounters();" id="pageNextEmployees" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>


                <div class="col mx-0 my-2">
                    <div class="card shadow-sm p-4">
                        <div class="row w-100 mb-4">
                            <h4 class="text-center">Employees List</h4>
                        </div>

                        <table class="table table-striped table-members" id="table-employees">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Created At</th>
                            </tr>
                        </table>
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center">
                                <li class="page-item">
                                <a class="page-link" onclick="prevPaginateEmployees()" id="pagePrevEmployees">Previous</a>
                                </li>
                                <li class="page-item">
                                <a class="page-link" onclick="nextPaginateEmployees();" id="pageNextEmployees" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>


            <div class="col col-md-6">
                <div class="col mx-0 my-2">
                    <div class="card shadow-sm p-4">
                        <div class="row w-100 mb-4">
                            <h4 class="text-center">Transaction History</h4>
                        </div>
                        <table class="table table-striped table-members" id="table-transaction-history">
                            <tr>
                                <th>Datetime</th>
                                <th>Transaction Type</th>
                            </tr>
                        </table>
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center">
                                <li class="page-item">
                                <a class="page-link" onclick="prevPaginateTransactions()" id="pagePrevEmployees">Previous</a>
                                </li>
                                <li class="page-item">
                                <a class="page-link" onclick="nextPaginateTransactions();" id="pageNextEmployees" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <div class="col mx-0 my-2">
                    <div class="card shadow-sm p-4">
                        <div class="row w-100 mb-4">
                            <h4 class="text-center">Transaction History (Outside)</h4>
                        </div>
                        <table class="table table-striped table-members" id="table-transaction-history">
                            <tr>
                                <th>Datetime</th>
                                <th>Transaction Type</th>
                            </tr>
                        </table>
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center">
                                <li class="page-item">
                                <a class="page-link" onclick="prevPaginateTransactions()" id="pagePrevEmployees">Previous</a>
                                </li>
                                <li class="page-item">
                                <a class="page-link" onclick="nextPaginateTransactions();" id="pageNextEmployees" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

    </div>

    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/dashboard_admin.js"></script>
</body>
</html>
