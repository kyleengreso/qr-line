function message_success(form, message) {
    $form = $(form);
    $form.find('.alert').remove();
    $form.prepend('<div class="alert alert-success">' + message + '</div>');
}

function message_error(form, message) {
    $form = $(form);
    $form.find('.alert').remove();
    $form.prepend('<div class="alert alert-danger">' + message + '</div>');
}

// 
function userStatusIcon(username, role_type, active) {
    if (role_type == 'admin') {
        if (active === 1) {
            return `
                <i class="fas fa-user-shield role_type_admin_icon"></i>
                <span class="role_type_admin_icon">${username}</span>
            `;
        } else {
            return `
                <i class="fa-solid fa-user-slash employeeActive-no-icon"></i>
                <span class="employeeActive-no-icon">${username}</span>
            `;
        }
    } else if (role_type == 'employee') {
        if (active === 1) {
            return `
                <i class="fas fa-user role_type_employee_icon"></i>
                <span class="role_type_employee_icon">${username}</span>
            `;
        } else {
            return `
                <i class="fa-solid fa-user-slash employeeActive-no-icon"></i>
                <span class="employeeActive-no-icon">${username}</span>
            `;
        }
    }
}

function textBadge(text, common_color) {
    if (common_color == 'success') {
        return `
            <span class="badge bg-success">
                ${text}
            </span>
        `;
    } else if (common_color == 'danger') {
        return `
            <span class="badge bg-danger">
                ${text}
            </span>
        `;
    } else if (common_color == 'warning') {
        return `
            <span class="badge bg-warning">
                ${text}
            </span>
        `;
    } else if (common_color == 'info') {
        return `
            <span class="badge bg-info">
                ${text}
            </span>
        `;
    } else if (common_color == 'secondary') {
        return `
            <span class="badge bg-secondary">
                ${text}
            </span>
        `;
    } else if (common_color == 'primary') {
        return `
            <span class="badge bg-primary">
                ${text}
            </span>
        `;
    }
}

function paginateGenerator(currentPage, totalData, paginate = 5) {
    var pages = [1,2,3,4,5];
    page = currentPage;
    total_page = Math.floor(totalData / paginate);

    if (currentPage > total_page) {
        currentPage = total_page;
    } else if (page < 1) {
        page = 1;
    } else {
        page = currentPage;
    }

    if (page > 3 && page <= total_page - 2) {
        pages = [
            page - 2,
            page - 1,
            page,
            page + 1,
            page + 2
        ]
    } else if (page == total_page - 1) {
        pages = [
            page - 3,
            page - 2,
            page - 1,
            page,
            page + 1
        ]
    } else if (page == total_page) {
        pages = [
            page - 4,
            page - 3,
            page - 2,
            page - 1,
            page
        ]
    } else if (page < 3) {
        pages = [
            1,
            2,
            3,
            4,
            5
        ]
    } else if (page > total_page) {
        page = total_page
        pages = [
            page - 4,
            page - 3,
            page - 2,
            page - 1,
            page
        ]
    }

    // console.log('Pages:', pages);
}