<?php
// nixjump flat-file site: PHP + Markdown + auto categories + clean URLs

require __DIR__ . '/lib/Parsedown.php';

$CONTENT_DIR = __DIR__ . '/content';

$parsedown = new Parsedown();

// Helper: nice title from slug (linux-basics -> Linux Basics)
function slug_to_title(string $slug): string {
    $slug = str_replace(['_', '-'], ' ', $slug);
    return ucwords($slug);
}

// Helper: read first "# Heading" as title, fallback to filename
function extract_title_from_md(string $filepath, string $fallback): string {
    if (!is_readable($filepath)) {
        return $fallback;
    }
    $contents = file_get_contents($filepath);
    if ($contents === false) {
        return $fallback;
    }
    if (preg_match('/^#\s+(.+)\s*$/m', $contents, $m)) {
        return trim($m[1]);
    }
    return $fallback;
}

// Scan content directory: build categories + pages
function build_site_structure(string $contentDir): array {
    $structure = [
        'main' => [
            'file' => $contentDir . '/_main.md',
            'title' => 'nixjump',
        ],
        'categories' => [], // slug => [title, indexFile?, pages[]]
    ];

    if (!is_dir($contentDir)) {
        return $structure;
    }

    $entries = scandir($contentDir);
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $fullPath = $contentDir . '/' . $entry;
        if (is_dir($fullPath)) {
            $catSlug = $entry;
            $catTitle = slug_to_title($catSlug);

            $cat = [
                'slug' => $catSlug,
                'title' => $catTitle,
                'indexFile' => null,
                'indexTitle' => $catTitle,
                'pages' => [], // pageSlug => [title, file]
            ];

            $files = scandir($fullPath);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') {
                    continue;
                }
                $filePath = $fullPath . '/' . $f;
                if (!is_file($filePath)) {
                    continue;
                }
                if (substr($f, -3) !== '.md') {
                    continue;
                }

                if ($f === '_index.md') {
                    $cat['indexFile'] = $filePath;
                    $cat['indexTitle'] = extract_title_from_md($filePath, $catTitle);
                    continue;
                }

                $pageSlug = substr($f, 0, -3);
                $pageTitle = extract_title_from_md($filePath, slug_to_title($pageSlug));

                $cat['pages'][$pageSlug] = [
                    'slug' => $pageSlug,
                    'title' => $pageTitle,
                    'file' => $filePath,
                ];
            }

            // Sort pages by title
            uasort($cat['pages'], function ($a, $b) {
                return strcasecmp($a['title'], $b['title']);
            });

            $structure['categories'][$catSlug] = $cat;
        }
    }

    // Sort categories by title
    uasort($structure['categories'], function ($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });

    return $structure;
}

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

// Helper: render markdown file or fallback
function render_markdown(Parsedown $parsedown, string $file, string $fallbackMarkdown = ''): string {
    if (is_readable($file)) {
        $markdown = file_get_contents($file);
        if ($markdown === false) {
            $markdown = $fallbackMarkdown;
        }
    } else {
        $markdown = $fallbackMarkdown;
    }
    return $parsedown->text($markdown);
}

// Routing logic
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

// Pass data to the template
$categories = $structure['categories'];

require __DIR__ . '/templates/layout.php';
