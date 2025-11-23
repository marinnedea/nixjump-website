<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1><a href="/">nixjump</a></h1>
        </div>

        <nav class="sidebar-nav">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <ul>
                    <li><a href="/" class="<?php echo $currentCat === null ? 'active' : ''; ?>">Home</a></li>
                </ul>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Categories</div>
                <?php foreach ($categories as $catSlug => $catInfo): ?>
                    <div class="menu-category">
                        <a href="/<?php echo urlencode($catSlug); ?>"
                           class="menu-category-title <?php
                               echo ($currentCat && $currentCat['slug'] === $catSlug && $currentPage === null) ? 'active' : '';
                           ?>">
                            <?php echo htmlspecialchars($catInfo['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <?php if (!empty($catInfo['pages'])): ?>
                            <ul class="menu-sublist">
                                <?php foreach ($catInfo['pages'] as $pageSlug => $pageInfo): ?>
                                    <li>
                                        <a href="/<?php echo urlencode($catSlug); ?>/<?php echo urlencode($pageSlug); ?>"
                                           class="<?php
                                               echo ($currentCat && $currentPage &&
                                                     $currentCat['slug'] === $catSlug &&
                                                     $currentPage['slug'] === $pageSlug) ? 'active' : '';
                                           ?>">
                                            <?php echo htmlspecialchars($pageInfo['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </nav>
    </aside>

    <main class="content">
        <?php echo $htmlContent; ?>
    </main>
</div>

<footer class="footer">
    <div class="footer-inner">
        &copy; <?php echo date('Y'); ?> nixjump
    </div>
</footer>
</body>
</html>
