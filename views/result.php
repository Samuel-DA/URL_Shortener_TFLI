<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TFLI URL Shortener — Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-8 text-center">
        <h1 class="text-xl font-semibold mb-4">Here's your short URL</h1>

        <div class="flex items-center gap-2">
            <input type="text" readonly id="short-url"
                value="<?= htmlspecialchars($shortUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="flex-1 rounded border-gray-300 shadow-sm px-3 py-2 border text-sm">
            <button id="copy-btn"
                class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700 text-sm">
                Copy
            </button>
        </div>

        <a href="/urls" class="inline-block mt-6 text-sm text-indigo-600 hover:underline">
            Shorten another
        </a>
    </div>

    <script>
        document.getElementById('copy-btn').addEventListener('click', () => {
            const input = document.getElementById('short-url');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                const btn = document.getElementById('copy-btn');
                const original = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = original; }, 1500);
            });
        });
    </script>
</body>
</html>