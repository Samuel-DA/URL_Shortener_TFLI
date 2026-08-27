<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TFLI URL Shortener</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-800">
    <main class="w-full max-w-md bg-white p-6 sm:p-8 rounded-xl shadow-md border border-slate-100">
        <h1 class="text-2xl font-bold text-slate-900 mb-1">Shorten a URL</h1>
        <p class="text-sm text-slate-500 mb-6">Enter a URL to get a shortened URL.</p>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="shortenForm" action="/urls" method="POST" class="space-y-4" novalidate>
            <div>
                <label for="url" class="block text-sm font-medium text-slate-700 mb-1">Destination URL *</label>
                <input
                    type="url"
                    id="url"
                    name="url"
                    value="<?= htmlspecialchars($longUrl ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="https://example.com/long-path"
                    required
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition"
                >
                <p id="urlError" class="hidden mt-1 text-xs text-red-600"></p>
            </div>

            <div>
                <label for="expires_at" class="block text-sm font-medium text-slate-700 mb-1">Expiry Date & Time (Optional)</label>
                <input
                    type="datetime-local"
                    id="expires_at"
                    name="expires_at"
                    value="<?= htmlspecialchars($expiresAtInput ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition"
                >
            </div>

            <button
                type="submit"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-sm transition"
            >
                Create Short URL
            </button>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('shortenForm');
            const urlInput = document.getElementById('url');
            const urlError = document.getElementById('urlError');

            form.addEventListener('submit', (event) => {
                urlError.classList.add('hidden');
                urlError.textContent = '';

                const value = urlInput.value.trim();
                if (!value) {
                    event.preventDefault();
                    urlError.textContent = 'Please enter a URL.';
                    urlError.classList.remove('hidden');
                    return;
                }

                try {
                    new URL(value);
                } catch (_) {
                    event.preventDefault();
                    urlError.textContent = 'Please enter a valid URL (e.g. https://example.com).';
                    urlError.classList.remove('hidden');
                }
            });
        });
    </script>
</body>
</html>