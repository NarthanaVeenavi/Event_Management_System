
<?php

$title = 'User Management | EventFlow';
$pageCss = 'user_management.css';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token if one does not already exist.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Allow only administrators.
if (
    !isset($_SESSION['user_id']) ||
    !in_array($_SESSION['role'] ?? '', ['Admin', 'System Admin'], true)
) {
    header('Location: /EventFlow/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';


require_once __DIR__ . '/../../controllers/user_controller.php';

// handling update user profile information by admin
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'update_user'
) {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)
    ) {
        header(
            'Location: /EventFlow/app/views/admin/users.php?edit=failed'
        );
        exit;
    }

    $userId = filter_var(
        $_POST['user_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$userId || $userId < 1) {
        header(
            'Location: /EventFlow/app/views/admin/users.php?edit=failed'
        );
        exit;
    }

    try {
        $userController = new UserController($conn);
        $userController->updateUser($userId, $_POST);

        header(
            'Location: /EventFlow/app/views/admin/users.php?edit=updated'
        );
        exit;

    } catch (InvalidArgumentException $e) {
        error_log('User update validation: ' . $e->getMessage());

        $_SESSION['edit_user_error'] = $e->getMessage();

        header(
            'Location: /EventFlow/app/views/admin/users.php?edit=failed'
        );
        exit;

    } catch (Throwable $e) {
        error_log('User update failed: ' . $e->getMessage());

        $_SESSION['edit_user_error'] =
            'Unable to update the user account. Please try again.';

        header(
            'Location: /EventFlow/app/views/admin/users.php?edit=failed'
        );
        exit;
    }
}

//handling delete user account by admin
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'delete_user') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)
    ) {
        header('Location: /EventFlow/app/views/admin/users.php?delete=failed');
        exit;
    }

    $userId = filter_var(
        $_POST['user_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        !$userId ||
        $userId === (int) ($_SESSION['user_id'] ?? 0)
    ) {
        header('Location: /EventFlow/app/views/admin/users.php?delete=failed');
        exit;
    }

    try {
        $userController = new UserController($conn);
        $result = $userController->deleteUser($userId);
        header(
            'Location: /EventFlow/app/views/admin/users.php?delete=' .
            ($result ? 'deactivated' : 'failed')
        );
        exit;

    } catch (Throwable $e) {
        error_log('User deletion failed: ' . $e->getMessage());

        header('Location: /EventFlow/app/views/admin/users.php?delete=failed');
        exit;
    }
}


/**
 * Escape output before displaying database values in HTML.
 */
function userManagementEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$dbError = '';
$users = [];

// Default statistics.
$stats = [
    [
        'label' => 'Total Users',
        'value' => 0,
        'icon' => 'bi-people',
        'note' => 'Across all account types'
    ],
    [
        'label' => 'Participants',
        'value' => 0,
        'icon' => 'bi-person',
        'note' => 'Event attendees'
    ],
    [
        'label' => 'Organizers',
        'value' => 0,
        'icon' => 'bi-calendar-event',
        'note' => 'Event management accounts'
    ],
    [
        'label' => 'Administrators',
        'value' => 0,
        'icon' => 'bi-shield-check',
        'note' => 'System administration'
    ]
];

try {
    // Get user statistics from the database.
    $statsQuery = "
        SELECT
            COUNT(*) AS total_users,
            COALESCE(SUM(role = 'Participant'), 0) AS participants,
            COALESCE(SUM(role = 'Organizer'), 0) AS organizers,
            COALESCE(SUM(role = 'Admin'), 0) AS admins
        FROM users
    ";

    $statsResult = $conn->query($statsQuery);

    if (!$statsResult) {
        throw new RuntimeException('Unable to retrieve user statistics.');
    }

    $statsData = $statsResult->fetch_assoc();

    $stats[0]['value'] = (int) $statsData['total_users'];
    $stats[1]['value'] = (int) $statsData['participants'];
    $stats[2]['value'] = (int) $statsData['organizers'];
    $stats[3]['value'] = (int) $statsData['admins'];

    // Retrieve all registered users.
    $usersQuery = "
        SELECT
            user_id,
            fname,
            lname,
            email,
            department,
            student_id,
            role,
            status,
            created_at
        FROM users
        ORDER BY created_at DESC, user_id DESC
    ";

    $usersResult = $conn->query($usersQuery);

    if (!$usersResult) {
        throw new RuntimeException('Unable to retrieve user accounts.');
    }

    
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = [
            'id' => (int) $row['user_id'],
            'display_id' => 'USR-' . str_pad($row['user_id'], 3, '0', STR_PAD_LEFT),

            // Keep the individual names for editing
            'fname' => $row['fname'],
            'lname' => $row['lname'],

            // Full name for displaying in the table
            'name' => $row['fname'] . ' ' . $row['lname'],

            'email' => $row['email'],
            'department' => $row['department'],
            'student_id' => $row['student_id'],
            'role' => $row['role'],
            'status' => $row['status'],
            'joined' => date('d M Y', strtotime($row['created_at'])),
            'initials' => strtoupper(
                substr($row['fname'], 0, 1) .
                substr($row['lname'], 0, 1)
            ),
        ];
    }


} catch (Throwable $e) {
    error_log('EventFlow user management: ' . $e->getMessage());
    $dbError = 'Unable to load user data. Please check the database connection and try again.';
}

$totalUsers = count($users);

ob_start();
?>

<div class="user-management-page">

    <!-- Page header -->
    <div class="um-page-header">
        <div>
            <span class="um-eyebrow">ADMINISTRATION</span>
            <h1>User Management</h1>
            <p>Manage user accounts, roles, and access to EventFlow.</p>
        </div>

        <a href="add_user.php"
        class="um-primary-button">
            <i class="bi bi-plus-lg"></i> Add User
        </a>
    </div>

    <?php if ($dbError !== ''): ?>
        <div class="um-error-message" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <?= userManagementEscape($dbError) ?>
        </div>
    <?php endif; ?>

    <!-- Summary cards -->
    <section class="um-stats-grid">
        <?php foreach ($stats as $stat): ?>
            <article class="um-stat-card">
                <div class="um-stat-top">
                    <span>
                        <?= userManagementEscape($stat['label']) ?>
                    </span>

                    <div class="um-stat-icon">
                        <i class="bi <?= userManagementEscape($stat['icon']) ?>"></i>
                    </div>
                </div>

                <h2><?= number_format((int) $stat['value']) ?></h2>

                <p><?= userManagementEscape($stat['note']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <!-- User list -->
    <section class="um-table-card">

        <div class="um-table-heading">
            <div>
                <h2>All Users</h2>
                <p>View and manage registered EventFlow accounts.</p>
            </div>

            <span class="um-result-count" id="umResultCount">
                <?= $totalUsers ?> users
            </span>
        </div>

        <!-- Search and filters -->
        <div class="um-toolbar">

            <div class="um-search-box">
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    id="umSearch"
                    placeholder="Search name, email or student ID..."
                    aria-label="Search users"
                >
            </div>

            <select id="umRoleFilter" aria-label="Filter by role">
                <option value="">All Roles</option>
                <option value="Participant">Participant</option>
                <option value="Organizer">Organizer</option>
                <option value="Admin">Admin</option>
            </select>

            <select id="umStatusFilter" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

        </div>
 
        <?php if (isset($_GET['delete'])): ?>
            <?php if ($_GET['delete'] === 'deactivated'): ?>
                <div class="um-alert um-alert-success" role="status" id="umDeleteAlert">
                    <i class="bi bi-check-circle-fill um-alert-icon"></i>

                    <span>User account deactivated successfully.</span>

                    <button
                        type="button"
                        class="um-alert-close"
                        onclick="closeDeleteAlert()"
                        aria-label="Dismiss notification"
                        title="Close"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php elseif ($_GET['delete'] === 'failed'): ?>
                <div class="um-alert um-alert-error" role="alert" id="umDeleteAlert">
                    <i class="bi bi-exclamation-circle-fill um-alert-icon"></i>

                    <span>The account could not be deactivated. Please check the account and try again.</span>

                    <button
                        type="button"
                        class="um-alert-close"
                        onclick="closeDeleteAlert()"
                        aria-label="Dismiss notification"
                        title="Close"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Edit success/error messages -->
        <?php if (isset($_GET['edit'])): ?>

            <?php if ($_GET['edit'] === 'updated'): ?>
                <div class="um-alert um-alert-success" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>User updated successfully.</span>
                    <button type="button"
                            class="um-alert-close"
                            onclick="this.parentElement.remove()"
                            aria-label="Dismiss notification">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

            <?php elseif ($_GET['edit'] === 'failed'): ?>
                <div class="um-alert um-alert-error" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>
                        <?= userManagementEscape(
                            $_SESSION['edit_user_error'] ?? 'Unable to update the user.'
                        ) ?>
                    </span>
                    <button type="button"
                            class="um-alert-close"
                            onclick="this.parentElement.remove()"
                            aria-label="Dismiss notification">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <?php unset($_SESSION['edit_user_error']); ?>
            <?php endif; ?>

        <?php endif; ?>

        <!-- Users table -->
        <div class="um-table-wrapper">
            <table class="um-users-table">

                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Date Joined</th>
                        <th class="um-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody id="umUsersBody">

                    <?php foreach ($users as $user): ?>

                        <tr
                            data-search="<?= userManagementEscape(
                                strtolower(
                                    $user['name'] . ' ' .
                                    $user['email'] . ' ' .
                                    $user['display_id'] . ' ' .
                                    $user['student_id'] . ' ' .
                                    $user['department']
                                )
                            ) ?>"
                            data-role="<?= userManagementEscape($user['role']) ?>"
                            data-status="<?= userManagementEscape($user['status']) ?>"
                        >

                            <!-- User details -->
                            <td>
                                <div class="um-user-cell">

                                    <div class="um-avatar">
                                        <?= userManagementEscape($user['initials']) ?>
                                    </div>

                                    <div class="um-user-info">
                                        <strong>
                                            <?= userManagementEscape($user['name']) ?>
                                        </strong>

                                        <span>
                                            <?= userManagementEscape($user['email']) ?>
                                        </span>

                                        <small>
                                            <?= userManagementEscape($user['display_id']) ?>
                                        </small>
                                    </div>

                                </div>
                            </td>

                            <!-- Role -->
                            <td>
                                <span class="um-role um-role-<?= userManagementEscape(
                                    strtolower(str_replace(' ', '-', $user['role']))
                                ) ?>">
                                    <?= userManagementEscape($user['role']) ?>
                                </span>
                            </td>

                            <!-- Department -->
                            <td>
                                <?= userManagementEscape($user['department']) ?>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="um-status um-status-<?= userManagementEscape(
                                    strtolower($user['status'])
                                ) ?>">
                                    <span class="um-status-dot"></span>
                                    <?= userManagementEscape($user['status']) ?>
                                </span>
                            </td>

                            <!-- Date joined -->
                            <td>
                                <?= userManagementEscape($user['joined']) ?>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="um-row-actions">

                                    <button
                                        type="button"
                                        class="um-icon-button"
                                        title="View user details"
                                        aria-label="View user details"
                                        onclick="viewUserDetails(this)"
                                        data-id="<?= userManagementEscape($user['display_id']) ?>"
                                        data-name="<?= userManagementEscape($user['name']) ?>"
                                        data-email="<?= userManagementEscape($user['email']) ?>"
                                        data-department="<?= userManagementEscape($user['department']) ?>"
                                        data-student-id="<?= userManagementEscape($user['student_id']) ?>"
                                        data-role="<?= userManagementEscape($user['role']) ?>"
                                        data-status="<?= userManagementEscape($user['status']) ?>"
                                        data-joined="<?= userManagementEscape($user['joined']) ?>"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    
                                    <button
                                        type="button"
                                        class="um-icon-button"
                                        title="Edit user"
                                        aria-label="Edit user"
                                        onclick="editUser(this)"
                                        data-id="<?= (int) $user['id'] ?>"
                                        data-fname="<?= userManagementEscape($user['fname']) ?>"
                                        data-lname="<?= userManagementEscape($user['lname']) ?>"
                                        data-email="<?= userManagementEscape($user['email']) ?>"
                                        data-department="<?= userManagementEscape($user['department']) ?>"
                                        data-student-id="<?= userManagementEscape($user['student_id']) ?>"
                                        data-role="<?= userManagementEscape($user['role']) ?>"
                                        data-status="<?= userManagementEscape($user['status']) ?>"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <button
                                        type="button"
                                        class="um-icon-button um-delete-button"
                                        title="Delete user"
                                        aria-label="Delete user"
                                        onclick="deleteUser(this)"
                                        data-id="<?= (int) $user['id'] ?>"
                                        data-name="<?= userManagementEscape($user['name']) ?>"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>


                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <!-- Empty state -->
                    <tr id="umEmptyState" hidden>
                        <td colspan="6" class="um-empty-state">
                            <i class="bi bi-search"></i>
                            <strong>No users found</strong>
                            <span>Try changing your search or filters.</span>
                        </td>
                    </tr>

                </tbody>

            </table>
        </div>

        <!-- Table footer -->
        <div class="um-table-footer">
            <span>
                Showing
                <strong id="umVisibleCount"><?= $totalUsers ?></strong>
                of <?= $totalUsers ?> users
            </span>

            <span class="um-demo-note">
                Live database data
            </span>
        </div>

    </section>

    <!-- view user details modal -->
    <div class="um-modal-overlay" id="umUserModal" aria-hidden="true">
        <div
            class="um-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="umModalTitle"
        >
            <div class="um-modal-header">
                <div>
                    <h2 id="umModalTitle">User Details</h2>
                    <p>View account information</p>
                </div>

                <button
                    type="button"
                    class="um-modal-close"
                    onclick="closeUserDetails()"
                    aria-label="Close modal"
                >
                    &times;
                </button>
            </div>

            <div class="um-modal-profile">
                <div class="um-modal-avatar" id="umDetailAvatar">U</div>
                <div>
                    <h3 id="umDetailName"></h3>
                    <p id="umDetailEmail"></p>
                </div>
            </div>

            <div class="um-modal-details">
                <div class="um-detail-item">
                    <span>User ID</span>
                    <strong id="umDetailId"></strong>
                </div>

                <div class="um-detail-item">
                    <span>Student ID</span>
                    <strong id="umDetailStudentId"></strong>
                </div>

                <div class="um-detail-item">
                    <span>Department</span>
                    <strong id="umDetailDepartment"></strong>
                </div>

                <div class="um-detail-item">
                    <span>Role</span>
                    <strong id="umDetailRole"></strong>
                </div>

                <div class="um-detail-item">
                    <span>Account Status</span>
                    <strong id="umDetailStatus"></strong>
                </div>

                <div class="um-detail-item">
                    <span>Joined Date</span>
                    <strong id="umDetailJoined"></strong>
                </div>
            </div>

            <div class="um-modal-footer">
                <button
                    type="button"
                    class="um-modal-done"
                    onclick="closeUserDetails()"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="um-modal-overlay" id="umEditModal" aria-hidden="true">
        <div
            class="um-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="umEditModalTitle"
        >
            <div class="um-modal-header">
                <div>
                    <h2 id="umEditModalTitle">Edit User</h2>
                    <p>Update the user's account information</p>
                </div>

                <button
                    type="button"
                    class="um-modal-close"
                    onclick="closeEditModal()"
                    aria-label="Close modal"
                >
                    &times;
                </button>
            </div>

            <form id="umEditForm" method="POST"
                action="/EventFlow/app/views/admin/users.php">

                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="editUserId">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= userManagementEscape($_SESSION['csrf_token']) ?>"
                >

                <div class="um-edit-form-grid">
                    <div class="um-edit-field">
                        <label for="editFname">First Name</label>
                        <input type="text" id="editFname" name="fname"
                            maxlength="100" required>
                    </div>

                    <div class="um-edit-field">
                        <label for="editLname">Last Name</label>
                        <input type="text" id="editLname" name="lname"
                            maxlength="100" required>
                    </div>

                    <div class="um-edit-field um-edit-full-width">
                        <label for="editEmail">Email Address</label>
                        <input type="email" id="editEmail" name="email"
                            maxlength="150" required>
                    </div>

                    <div class="um-edit-field">
                        <label for="editDepartment">Department</label>
                        <input type="text" id="editDepartment"
                            name="department" maxlength="100" required>
                    </div>

                    <div class="um-edit-field">
                        <label for="editStudentId">Student ID</label>
                        <input type="text" id="editStudentId"
                            name="student_id" maxlength="100" required>
                    </div>

                    <div class="um-edit-field">
                        <label for="editRole">Role</label>
                        <select id="editRole" name="role" required>
                            <option value="Participant">Participant</option>
                            <option value="Organizer">Organizer</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <div class="um-edit-field">
                        <label for="editStatus">Account Status</label>
                        <select id="editStatus" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="um-modal-footer">
                    <button type="button" class="um-edit-cancel"
                            onclick="closeEditModal()">
                        Cancel
                    </button>

                    <button type="submit" class="um-modal-done">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>


<form id="umDeleteForm" method="POST"
      action="/EventFlow/app/views/admin/users.php"
      hidden>
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" id="umDeleteUserId">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= userManagementEscape($_SESSION['csrf_token']) ?>"
    >
</form>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layout/main.php';
?>
