
<?php
$title = $title ?? 'EventFlow';
$content = $content ?? '';
$pageCss = $pageCss ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Global CSS -->
    <link rel="stylesheet" href="/EventFlow/assets/css/global.css">

    <?php
    // Pages that use the dashboard layout.
    $dashboardPages = [
        'dashboard.css',
        'profile.css',
        'admin_dashboard.css',
        'user_management.css',
        'add_user.css',
        'organizer_dashboard.css',
        'create_event.css',
        'events.css',
        'event_details.css',
        'event_approvals.css',
        'event_review.css',
    ];

    // Allowed page-specific CSS files.
    $allowedCss = [
        'register.css',
        'login.css',
        'dashboard.css',
        'profile.css',
        'admin_dashboard.css',
        'user_management.css',
        'add_user.css',
        'organizer_dashboard.css',
        'create_event.css',
        'events.css',
        'event_details.css',
        'event_approvals.css',
        'event_review.css',
    ];
    ?>

    <!-- Shared Dashboard CSS -->
    <?php if (in_array($pageCss, $dashboardPages, true)): ?>
        <link rel="stylesheet" href="/EventFlow/assets/css/sidebar.css">
        <link rel="stylesheet" href="/EventFlow/assets/css/topbar.css">
    <?php endif; ?>

    <!-- Shared Authentication CSS -->
    <?php if ($pageCss === 'login.css'): ?>
        <link rel="stylesheet" href="/EventFlow/assets/css/register.css">
    <?php endif; ?>

    <!-- Page-specific CSS -->
    <?php if (in_array($pageCss, $allowedCss, true)): ?>
        <link
            rel="stylesheet"
            href="/EventFlow/assets/css/<?= htmlspecialchars(
                $pageCss,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >
    <?php endif; ?>
</head>

<body>

    <?php if (in_array($pageCss, $dashboardPages, true)): ?>

        <!-- Dashboard Layout -->
        <div class="dashboard-layout">

            <?php require __DIR__ . '/sidebar.php'; ?>

            <main class="dashboard-main">

                <?php require __DIR__ . '/topbar.php'; ?>

                <div class="dashboard-content">
                    <?= $content ?>
                </div>

            </main>

        </div>

    <?php else: ?>

        <!-- Authentication / Other Pages -->
        <?= $content ?>

    <?php endif; ?>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme JavaScript -->
    <script src="/EventFlow/assets/js/theme.js"></script>
    <script src="/EventFlow/assets/js/user.js"></script>

</body>
</html>
