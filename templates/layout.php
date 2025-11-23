<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind via CDN (stable v3) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'nix-bg': '#020617',
                        'nix-sidebar': '#020617',
                        'nix-sidebar-border': '#1f2937',
                        'nix-text': '#e5e7eb',
                        'nix-muted': '#9ca3af',
                        'nix-accent': '#38bdf8',
                        'nix-accent-strong': '#f97316',
                    }
                }
            }
        }
    </script>

    <!-- Initialize theme early to avoid flash -->
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            if (stored === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (stored === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                if (window.matchMedia &&
                    window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        })();
    </script>

    <!-- Prism.js for code highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" />

    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="min-h-screen bg-white text-slate-900 dark:bg-nix-bg dark:text-nix-text">

<div class="min-h-screen flex flex-col">
    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside class="w-72 bg-slate-50 dark:bg-nix-sidebar border-r border-slate-200 dark:border-nix-sidebar-border flex-shrink-0">
            <div class="h-full flex flex-col">
                <!-- Header -->
                <div class="px-4 py-4 border-b border-slate-200 dark:border-nix-sidebar-border flex items-center justify-between gap-2">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">
                            <a href="/" class="text-slate-900 dark:text-white hover:text-nix-accent transition">
                                nixjump
                            </a>
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-nix-muted mt-1">
                            Personal tech notes &amp; lab.
                        </p>
                    </div>
                    <button
                        id="theme-toggle"
                        class="inline-flex items-center justify-center rounded-md border border-slate-300 dark:border-slate-700 px-2 py-1 text-xs text-slate-700 dark:text-slate-200 bg-white/70 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        type="button"
                    >
                        <span class="light-label hidden">☀️</span>
                        <span class="dark-label">🌙</span>
                    </button>
                </div>

                <!-- Sidebar content -->
                <nav class="flex-1 overflow-y-auto px-4 py-4 text-sm space-y-6">
                    <!-- Search -->
                    <div>
                        <form action="/search" method="get" class="relative">
                            <input
                                type="text"
                                name="q"
                                placeholder="Search notes..."
                                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-md py-1.5 pl-8 pr-2 text-xs text-slate-800 dark:text-nix-text placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-nix-accent focus:border-nix-accent"
                            />
                            <span class="absolute left-2 top-1.5 text-slate-400 dark:text-slate-500 text-xs">🔍</span>
                        </form>
                    </div>

                    <!-- Main -->
                    <div>
                        <div class="text-[0.7rem] font-semibold tracking-[0.18em] text-slate-500 dark:text-nix-muted uppercase mb-2">
                            Main
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="/"
                                   class="block px-2 py-1 rounded-md transition
                                   <?php echo $currentCat === null && !$isTagsView ? 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-nix-accent-strong' : 'text-slate-800 dark:text-nix-text hover:bg-slate-200 dark:hover:bg-slate-800'; ?>">
                                    Home
                                </a>
                            </li>
                            <li>
                                <a href="/tags"
                                   class="block px-2 py-1 rounded-md transition
                                   <?php echo $isTagsView ? 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-nix-accent-strong' : 'text-slate-800 dark:text-nix-text hover:bg-slate-200 dark:hover:bg-slate-800'; ?>">
                                    Tags
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Categories -->
                    <div>
                        <div class="text-[0.7rem] font-semibold tracking-[0.18em] text-slate-500 dark:text-nix-muted uppercase mb-2">
                            Categories
                        </div>

                        <div class="space-y-2">
                            <?php foreach ($categories as $catSlug => $catInfo): ?>
                                <?php
                                    $isActiveCat = $currentCat && $currentCat['slug'] === $catSlug;
                                ?>
                                <details class="group border border-transparent hover:border-slate-200 dark:hover:border-slate-700 rounded-md"
                                         <?php echo $isActiveCat ? 'open' : ''; ?>>
                                    <summary class="list-none flex items-center justify-between px-2 py-1 cursor-pointer">
                                        <a href="/<?php echo urlencode($catSlug); ?>"
                                           class="font-semibold text-xs md:text-sm
                                           <?php
                                               echo $isActiveCat && $currentPage === null
                                                   ? 'text-slate-900 dark:text-nix-accent-strong'
                                                   : 'text-slate-800 dark:text-nix-text hover:text-nix-accent';
                                           ?>">
                                            <?php echo htmlspecialchars($catInfo['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                        <span class="text-slate-400 dark:text-slate-500 text-xs">
                                            ▾
                                        </span>
                                    </summary>

                                    <?php if (!empty($catInfo['pages'])): ?>
                                        <ul class="ml-2 mb-2 mt-1 space-y-1 border-l border-slate-200 dark:border-slate-700 pl-2">
                                            <?php foreach ($catInfo['pages'] as $pageSlug => $pageInfo): ?>
                                                <li>
                                                    <a href="/<?php echo urlencode($catSlug); ?>/<?php echo urlencode($pageSlug); ?>"
                                                       class="block px-2 py-1 rounded-md text-xs transition
                                                       <?php
                                                           echo ($currentCat && $currentPage &&
                                                                 $currentCat['slug'] === $catSlug &&
                                                                 $currentPage['slug'] === $pageSlug)
                                                               ? 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-nix-accent-strong'
                                                               : 'text-slate-600 dark:text-nix-muted hover:bg-slate-200 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-nix-text';
                                                       ?>">
                                                        <?php echo htmlspecialchars($pageInfo['title'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tags list (sidebar) -->
                    <?php if (!empty($tags)): ?>
                        <div>
                            <div class="text-[0.7rem] font-semibold tracking-[0.18em] text-slate-500 dark:text-nix-muted uppercase mb-2">
                                Tags
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($tags as $tagSlug => $tagInfo): ?>
                                    <a href="/tags/<?php echo urlencode($tagSlug); ?>"
                                       class="inline-flex items-center px-2 py-0.5 rounded-full border border-slate-300 dark:border-slate-700 text-[0.7rem] text-slate-700 dark:text-slate-200 hover:border-nix-accent hover:text-nix-accent">
                                        <?php echo htmlspecialchars($tagInfo['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 flex justify-center">
            <div class="w-full max-w-3xl px-6 py-8">
                <!-- Breadcrumbs -->
                <nav class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    <a href="/" class="hover:text-nix-accent">Home</a>

                    <?php if ($isTagsView): ?>
                        <span class="mx-1">/</span>
                        <a href="/tags" class="hover:text-nix-accent">Tags</a>
                        <?php if ($currentTag): ?>
                            <span class="mx-1">/</span>
                            <span class="text-slate-700 dark:text-nix-text">
                                <?php echo htmlspecialchars($currentTag['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (!empty($currentCat)): ?>
                            <span class="mx-1">/</span>
                            <a href="/<?php echo urlencode($currentCat['slug']); ?>"
                               class="hover:text-nix-accent">
                                <?php echo htmlspecialchars($currentCat['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($currentPage)): ?>
                            <span class="mx-1">/</span>
                            <span class="text-slate-700 dark:text-nix-text">
                                <?php echo htmlspecialchars($currentPage['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>

                <article class="prose prose-slate max-w-none dark:prose-invert">
                    <?php echo $htmlContent; ?>
                </article>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="border-t border-slate-200 dark:border-nix-sidebar-border py-3 text-xs text-slate-500 dark:text-nix-muted">
        <div class="max-w-3xl mx-auto px-6">
            &copy; <?php echo date('Y'); ?> nixjump
        </div>
    </footer>
</div>

<!-- Theme toggle + Prism -->
<script>
    (function () {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;

        function applyTheme(theme) {
            if (theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
            localStorage.setItem('theme', theme);

            const lightLabel = btn.querySelector('.light-label');
            const darkLabel = btn.querySelector('.dark-label');
            if (lightLabel && darkLabel) {
                if (theme === 'light') {
                    lightLabel.classList.remove('hidden');
                    darkLabel.classList.add('hidden');
                } else {
                    lightLabel.classList.add('hidden');
                    darkLabel.classList.remove('hidden');
                }
            }
        }

        // Initialize button state
        const stored = localStorage.getItem('theme');
        if (stored === 'light') {
            applyTheme('light');
        } else if (stored === 'dark') {
            applyTheme('dark');
        }

        btn.addEventListener('click', function () {
            const isDark = document.documentElement.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        });
    })();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
</body>
</html>
