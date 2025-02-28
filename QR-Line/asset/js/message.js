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