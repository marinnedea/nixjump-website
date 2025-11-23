<?php

require __DIR__ . '/../lib/Parsedown.php';

$CONTENT_DIR = __DIR__ . '/../content';

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

// Parse tags from <!-- tags: ... --> comment
function extract_tags_from_contents(string $contents): array {
    $tags = [];
    if (preg_match('/<!--\s*tags\s*:\s*(.+?)\s*-->/i', $contents, $m)) {
        $parts = explode(',', $m[1]);
        foreach ($parts as $part) {
            $clean = trim($part);
            if ($clean === '') {
                continue;
            }
            $tags[] = $clean;
        }
    }
    return $tags;
}

// Build a URL-safe slug from a tag name
function tag_to_slug(string $tag): string {
    $slug = strtolower($tag);
    $slug = preg_replace('/[^a-z0-9\-]+/i', '-', $slug);
    $slug = trim($slug, '-');
    return $slug === '' ? 'tag' : $slug;
}

// Scan content directory: build categories + pages + tags index
function build_site_structure(string $contentDir): array {
    $structure = [
        'main' => [
            'file' => $contentDir . '/_main.md',
            'title' => 'nixjump',
        ],
        'categories' => [], // slug => [title, indexFile?, pages[]]
        'tags' => [],       // tagSlug => ['name' => ..., 'pages' => [...]]
    ];

    if (!is_dir($contentDir)) {
        return $structure;
    }

    $tagsIndex = [];

    $entries = scandir($contentDir);
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        // Ignore special/hidden dirs like _templates
        if ($entry[0] === '_') {
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
                'pages' => [], // pageSlug => [title, file, tags[]]
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

                $contents = file_get_contents($filePath) ?: '';
                $tags = extract_tags_from_contents($contents);

                if ($f === '_index.md') {
                    $cat['indexFile'] = $filePath;
                    $cat['indexTitle'] = extract_title_from_md($filePath, $catTitle);

                    // Index tags on category index too
                    foreach ($tags as $tagName) {
                        $tagSlug = tag_to_slug($tagName);
                        if (!isset($tagsIndex[$tagSlug])) {
                            $tagsIndex[$tagSlug] = [
                                'name' => $tagName,
                                'pages' => [],
                            ];
                        }
                        $tagsIndex[$tagSlug]['pages'][] = [
                            'title' => $cat['indexTitle'],
                            'url' => '/' . $catSlug,
                            'category' => $cat['title'],
                        ];
                    }

                    continue;
                }

                $pageSlug = substr($f, 0, -3);
                $pageTitle = extract_title_from_md($filePath, slug_to_title($pageSlug));

                $cat['pages'][$pageSlug] = [
                    'slug' => $pageSlug,
                    'title' => $pageTitle,
                    'file' => $filePath,
                    'tags' => $tags,
                ];

                // Index tags
                foreach ($tags as $tagName) {
                    $tagSlug = tag_to_slug($tagName);
                    if (!isset($tagsIndex[$tagSlug])) {
                        $tagsIndex[$tagSlug] = [
                            'name' => $tagName,
                            'pages' => [],
                        ];
                    }
                    $tagsIndex[$tagSlug]['pages'][] = [
                        'title' => $pageTitle,
                        'url' => '/' . $catSlug . '/' . $pageSlug,
                        'category' => $cat['title'],
                    ];
                }
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

    // Sort tags alphabetically by display name
    uasort($tagsIndex, function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    $structure['tags'] = $tagsIndex;

    return $structure;
}

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
