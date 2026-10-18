
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
    <!-- Shared authentication page styles -->
    <?php if ($pageCss === 'login.css'): ?>
        <link
            rel="stylesheet"
            href="/EventFlow/assets/css/register.css"
        >
    <?php endif; ?>

    <!-- Page-specific CSS -->
    <?php if (in_array($pageCss, ['register.css', 'login.css'], true)): ?>
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
    <?= $content ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
