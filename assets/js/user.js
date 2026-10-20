document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('umSearch');
    const roleFilter = document.getElementById('umRoleFilter');
    const statusFilter = document.getElementById('umStatusFilter');

    const rows = Array.from(
        document.querySelectorAll('#umUsersBody tr[data-search]')
    );

    const emptyState = document.getElementById('umEmptyState');
    const visibleCount = document.getElementById('umVisibleCount');
    const resultCount = document.getElementById('umResultCount');

    function filterUsers() {
        const query = search.value.trim().toLowerCase();
        const role = roleFilter.value;
        const status = statusFilter.value;

        let visible = 0;

        rows.forEach(function (row) {
            const matchesSearch = row.dataset.search.includes(query);
            const matchesRole = !role || row.dataset.role === role;
            const matchesStatus = !status || row.dataset.status === status;

            const show = matchesSearch && matchesRole && matchesStatus;

            row.hidden = !show;

            if (show) {
                visible++;
            }
        });

        emptyState.hidden = visible !== 0;
        visibleCount.textContent = visible;
        resultCount.textContent = visible + ' users';
    }

    search.addEventListener('input', filterUsers);
    roleFilter.addEventListener('change', filterUsers);
    statusFilter.addEventListener('change', filterUsers);
});

function viewUserDetails(button) {
    const fields = {
        Id: 'id',
        Name: 'name',
        Email: 'email',
        StudentId: 'studentId',
        Department: 'department',
        Role: 'role',
        Status: 'status',
        Joined: 'joined'
    };

    Object.entries(fields).forEach(([elementSuffix, dataName]) => {
        const element = document.getElementById('umDetail' + elementSuffix);
        if (element) {
            element.textContent = button.dataset[dataName] || 'Not provided';
        }
    });

    const avatar = document.getElementById('umDetailAvatar');
    avatar.textContent = (button.dataset.name || 'U')
        .trim()
        .split(/\s+/)
        .map(part => part[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();

    const modal = document.getElementById('umUserModal');
    modal.classList.add('is-visible');
    modal.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';
}

function closeUserDetails() {
    const modal = document.getElementById('umUserModal');
    modal.classList.remove('is-visible');
    modal.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';
}

document.getElementById('umUserModal').addEventListener('click', function (event) {
    if (event.target === this) {
        closeUserDetails();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeUserDetails();
    }
});

function editUser(button) {
    document.getElementById('editUserId').value =
        button.dataset.id || '';

    document.getElementById('editFname').value =
        button.dataset.fname || '';

    document.getElementById('editLname').value =
        button.dataset.lname || '';

    document.getElementById('editEmail').value =
        button.dataset.email || '';

    document.getElementById('editDepartment').value =
        button.dataset.department || '';

    document.getElementById('editStudentId').value =
        button.dataset.studentId || '';

    document.getElementById('editRole').value =
        button.dataset.role || 'Participant';

    document.getElementById('editStatus').value =
        button.dataset.status || 'Active';

    const modal = document.getElementById('umEditModal');

    modal.classList.add('is-visible');
    modal.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';

    document.getElementById('editFname').focus();
}

function closeEditModal() {
    const modal = document.getElementById('umEditModal');

    modal.classList.remove('is-visible');
    modal.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';
}

document.getElementById('umEditModal').addEventListener(
    'click',
    function (event) {
        if (event.target === this) {
            closeEditModal();
        }
    }
);

function deleteUser(button) {
    const userId = button.dataset.id;
    const userName = button.dataset.name || 'this user';

    if (!userId) {
        alert('Unable to identify this user.');
        return;
    }

    const confirmed = confirm(
        `Are you sure you want to deactivate ${userName}?\n\n` +
        'The account will be marked as inactive, and its records will be preserved.'
    );

    if (!confirmed) {
        return;
    }

    document.getElementById('umDeleteUserId').value = userId;
    document.getElementById('umDeleteForm').requestSubmit();
}
function closeDeleteAlert() {
    const alertBox = document.getElementById('umDeleteAlert');

    if (alertBox) {
        alertBox.remove();
    }

    // Remove the delete status from the URL.
    const url = new URL(window.location.href);
    url.searchParams.delete('delete');

    window.history.replaceState({}, '', url.pathname + url.search + url.hash);
}