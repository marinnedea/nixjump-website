<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script>
        tailwind.config = {
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

    <!-- Optional: your own small overrides if you want -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-nix-bg text-nix-text min-h-screen">

<div class="min-h-screen flex flex-col">
    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside class="w-64 bg-nix-sidebar border-r border-nix-sidebar-border flex-shrink-0">
            <div class="h-full flex flex-col">
                <div class="px-4 py-4 border-b border-nix-sidebar-border">
                    <h1 class="text-xl font-semibold tracking-tight">
                        <a href="/" class="text-white hover:text-nix-accent transition">
                            nixjump
                        </a>
                    </h1>
                </div>

                <nav class="flex-1 overflow-y-auto px-4 py-4 text-sm">
                    <!-- Main -->
                    <div class="mb-6">
                        <div class="text-[0.7rem] font-semibold tracking-[0.18em] text-nix-muted uppercase mb-2">
                            Main
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="/"
                                   class="block px-2 py-1 rounded-md transition
                                   <?php echo $currentCat === null ? 'bg-slate-800 text-nix-accent-strong' : 'text-nix-text hover:bg-slate-800'; ?>">
                                    Home
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Categories -->
                    <div>
                        <div class="text-[0.7rem] font-semibold tracking-[0.18em] text-nix-muted uppercase mb-2">
                            Categories
                        </div>

                        <div class="space-y-3">
                            <?php foreach ($categories as $catSlug => $catInfo): ?>
                                <div>
                                    <a href="/<?php echo urlencode($catSlug); ?>"
                                       class="block px-2 py-1 rounded-md font-semibold mb-1 transition
                                       <?php
                                           echo ($currentCat && $currentCat['slug'] === $catSlug && $currentPage === null)
                                               ? 'bg-slate-800 text-nix-accent-strong'
                                               : 'text-nix-text hover:bg-slate-800';
                                       ?>">
                                        <?php echo htmlspecialchars($catInfo['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>

                                    <?php if (!empty($catInfo['pages'])): ?>
                                        <ul class="ml-3 mt-1 space-y-1 border-l border-slate-700 pl-2">
                                            <?php foreach ($catInfo['pages'] as $pageSlug => $pageInfo): ?>
                                                <li>
                                                    <a href="/<?php echo urlencode($catSlug); ?>/<?php echo urlencode($pageSlug); ?>"
                                                       class="block px-2 py-1 rounded-md text-xs transition
                                                       <?php
                                                           echo ($currentCat && $currentPage &&
                                                                 $currentCat['slug'] === $catSlug &&
                                                                 $currentPage['slug'] === $pageSlug)
                                                               ? 'bg-slate-800 text-nix-accent-strong'
                                                               : 'text-nix-muted hover:bg-slate-800 hover:text-nix-text';
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
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 flex justify-center">
            <div class="w-full max-w-3xl px-6 py-8">
                <article class="prose prose-invert prose-slate max-w-none">
                    <?php echo $htmlContent; ?>
                </article>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="border-t border-nix-sidebar-border py-3 text-xs text-nix-muted">
        <div class="max-w-3xl mx-auto px-6">
            &copy; <?php echo date('Y'); ?> nixjump
        </div>
    </footer>
</div>

</body>
</html>

