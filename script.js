// Confirm before delete actions
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.confirm-delete').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm('Kya aap sach me delete karna chahte hain?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide alerts after 4s
    document.querySelectorAll('.alert').forEach(function (a) {
        setTimeout(function () { a.style.display = 'none'; }, 4000);
    });
});
