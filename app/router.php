<?php

// Build site structure
$structure = build_site_structure($CONTENT_DIR);

// ---- Routing: parse /cat[/page] from ?path=... (set by .htaccess) ----

$rawPath = $_GET['path'] ?? '';
$rawPath = trim($rawPath, "/");

$cat = null;
$page = null;

if ($rawPath === '') {
    // Root URL -> main page
    $cat = null;
    $page = null;
} else {
    $segments = explode('/', $rawPath);

    $cat = $segments[0] ?? null;
    $page = $segments[1] ?? null;
}

// Sanitize slugs (only allow a-zA-Z0-9-_)
if ($cat !== null) {
    $cat = preg_replace('/[^a-zA-Z0-9\-_]/', '', $cat);
}
if ($page !== null) {
    $page = preg_replace('/[^a-zA-Z0-9\-_]/', '', $page);
}

$currentCat = null;
$currentPage = null;
$pageTitle = 'nixjump';
$htmlContent = '';

// ---- Search route: /search?q=... ----
if ($cat === 'search') {
    $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $pageTitle = 'Search - nixjump';

    if ($q === '') {
        $htmlContent = $parsedown->text("# Search\n\nType something to search your notes.");
    } else {
        $results = [];

        // Search main page
        $mainFile = $structure['main']['file'];
        if (is_readable($mainFile)) {
            $text = file_get_contents($mainFile) ?: '';
            if (mb_stripos($text, $q) !== false) {
                $results[] = [
                    'title' => $structure['main']['title'],
                    'url' => '/',
                    'snippet' => 'Main page',
                ];
            }
        }

        // Search categories and pages
        foreach ($structure['categories'] as $catSlug => $catInfo) {
            // Category index
            if ($catInfo['indexFile'] && is_readable($catInfo['indexFile'])) {
                $text = file_get_contents($catInfo['indexFile']) ?: '';
                if (mb_stripos($text, $q) !== false) {
                    $results[] = [
                        'title' => $catInfo['indexTitle'],
                        'url' => '/' . $catSlug,
                        'snippet' => 'Category: ' . $catInfo['title'],
                    ];
                }
            }

            // Category pages
            foreach ($catInfo['pages'] as $pageSlug => $pageInfo) {
                if (!is_readable($pageInfo['file'])) {
                    continue;
                }
                $text = file_get_contents($pageInfo['file']) ?: '';
                if (mb_stripos($text, $q) !== false) {
                    $results[] = [
                        'title' => $pageInfo['title'],
                        'url' => '/' . $catSlug . '/' . $pageSlug,
                        'snippet' => $catInfo['title'],
                    ];
                }
            }
        }

        // Build simple Markdown for results
        if (empty($results)) {
            $md = "# Search\n\nNo results for `" . str_replace('`', '\`', $q) . "`.";
        } else {
            $md = "# Search results for `" . str_replace('`', '\`', $q) . "`\n\n";
            foreach ($results as $r) {
                $md .= "- [" . $r['title'] . "](" . $r['url'] . ")  \n  _" . $r['snippet'] . "_\n";
            }
        }

        $htmlContent = $parsedown->text($md);
    }

    $categories = $structure['categories'];
    return;
}

// ---- Normal routing logic (main / categories / pages) ----

if ($cat === null) {
    // Main page
    $mainFile = $structure['main']['file'];
    $pageTitle = $structure['main']['title'];
    $htmlContent = render_markdown(
        $parsedown,
        $mainFile,
        "# nixjump\n\nMain page not found."
    );
} else {
    if (!isset($structure['categories'][$cat])) {
        http_response_code(404);
        $pageTitle = 'Not found - nixjump';
        $htmlContent = $parsedown->text("# Not found\n\nThe requested category does not exist.");
    } else {
        $currentCat = $structure['categories'][$cat];

        if ($page === null || $page === '') {
            // Category index
            if ($currentCat['indexFile']) {
                $pageTitle = $currentCat['indexTitle'] . ' - nixjump';
                $htmlContent = render_markdown(
                    $parsedown,
                    $currentCat['indexFile'],
                    '# ' . $currentCat['title'] . "\n\nNo index content yet."
                );
            } else {
                $pageTitle = $currentCat['title'] . ' - nixjump';
                $htmlContent = $parsedown->text('# ' . $currentCat['title'] . "\n\nNo index content yet.");
            }
        } else {
            // Specific page in category
            if (!isset($currentCat['pages'][$page])) {
                http_response_code(404);
                $pageTitle = 'Not found - nixjump';
                $htmlContent = $parsedown->text("# Not found\n\nThe requested page does not exist.");
            } else {
                $currentPage = $currentCat['pages'][$page];
                $pageTitle = $currentPage['title'] . ' - ' . $currentCat['title'] . ' - nixjump';
                $htmlContent = render_markdown(
                    $parsedown,
                    $currentPage['file'],
                    '# ' . $currentPage['title'] . "\n\nContent not found."
                );
            }
        }
    }
}

// Shared for all normal routes
$categories = $structure['categories'];
