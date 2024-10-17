<?php

/**
 * Mini HTTP Client (MHC)
 *
 * This PHP script provides a minimal HTTP client interface that allows users to make HTTP requests
 * (GET, POST, PUT, DELETE) to a specified URL. The script uses cURL to send the requests and formats
 * the response for display. It also supports saving previously made requests to local storage for easy reuse.
 *
 * Features:
 * - Send HTTP requests with different methods (GET, POST, PUT, DELETE)
 * - Optionally provide a request body for POST and PUT methods
 * - Display the response in a formatted and readable way (JSON pretty-print supported)
 * - Dark mode toggle for user interface customization
 * - Save and reload past requests from local storage (saved in the browser)
 *
 * @author  Kevin Illanas, kevinillanas.dev
 * @license GNU GPL v3
 * @version 0.1.0
 */

define('AUTHOR', 'Kevin Illanas');
define('LICENSE', 'GNU GPL v3');
define('VERSION', '0.1.0');

$name = $_POST['name'] ?? '';
$url = $_POST['url'] ?? '';
$method = $_POST['method'] ?? 'GET';
$body = $_POST['body'] ?? '';
$response = 'No response has yet been received';
$errores = [];

// Check if the form was submitted
if (isset($_POST['sendpetition'])) {
    // Create a new HttpManager instance
    $httpManager = new HttpManager($url, $method, ['Content-Type: application/json'], $body);
    $response = $httpManager->send();
}

/**
 * Class HttpManager
 */
class HttpManager
{
    private $url;
    private $method;
    private $headers;
    private $body;

    /**
     * HttpManager constructor
     * @param mixed $url 
     * @param string $method 
     * @param array $headers 
     * @param string $body 
     * @return void 
     */
    public function __construct($url, $method = 'GET', $headers = [], $body = '')
    {
        $this->url = $url;
        $this->method = $method;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Send the HTTP request
     * @return string|bool 
     */
    public function send()
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

            if ($this->method === 'POST' && !empty($this->body)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
            }
            $result = curl_exec($ch);
            curl_close($ch);

            // pretty print JSON
            $decoded = json_decode($result);
            if ($decoded) {
                $result = json_encode($decoded, JSON_PRETTY_PRINT);
            }

            return $result;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini HTTP Client</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📤</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html,
        body {
            font-family: 'Inter', sans-serif;
            height: 100%;
        }

        pre {
            max-width: 100%;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow: auto;
            overflow-wrap: break-word;
            word-break: break-all;
        }
    </style>
</head>

<body>
    <div class="flex min-h-full">
        <aside class="min-h-full min-w-64 bg-white dark:bg-gray-900 p-6 shadow-md">
            <h2 class="text-lg font-semibold mb-4 dark:text-white">History</h2>
            <ul id="savedCalls"></ul>
        </aside>
        <main class="flex flex-col flex-1 p-7 dark:bg-gray-800">
            <div class="flex justify-between mb-4">
                <h1 class="text-2xl font-semibold dark:text-white">📤 Mini Http Client (MHC)</h1>
                <button id="darkModeToggle" class="bg-gray-200 dark:bg-gray-900 text-black dark:text-white px-4 py-2 rounded-md">Modo Oscuro</button>
            </div>
            <form id="httpForm" method="POST" action="" class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md space-y-4">
                <div class="flex space-x-4">
                    <input type="text" name="name" id="name" placeholder="Request name" value="<?= htmlspecialchars($name) ?>" class="flex-1 p-2 border rounded dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex space-x-4">
                    <select name="method" id="method" class="w-24 p-2 border rounded dark:bg-gray-700 dark:text-white" required>
                        <option value="GET" <?= $method === 'GET' ? 'selected' : '' ?>>GET</option>
                        <option value="POST" <?= $method === 'POST' ? 'selected' : '' ?>>POST</option>
                        <option value="PUT" <?= $method === 'PUT' ? 'selected' : '' ?>>PUT</option>
                        <option value="DELETE" <?= $method === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                    </select>
                    <input type="url" name="url" id="url" placeholder="URL" value="<?= htmlspecialchars($url) ?>" class="flex-1 p-2 border rounded dark:bg-gray-700 dark:text-white" required>
                </div>
                <textarea name="body" id="body" placeholder="Request Body (for POST/PUT)" class="w-full h-32 p-2 border rounded dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($body) ?></textarea>
                <button type="submit" name="sendpetition" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Send request</button>
            </form>
            <div class="my-6">
                <h2 class="text-lg font-semibold mb-2 dark:text-white">Response:</h2>
                <pre id="response" class="bg-white dark:bg-gray-900 dark:text-white shadow-md p-4 rounded-md w-full overflow-x-auto"><?= htmlspecialchars($response) ?></pre>
            </div>
            <footer class="text-sm text-gray-600 dark:text-gray-400 mt-auto">
                <p>Author: <?= AUTHOR ?> - <?= LICENSE ?> - v<?= VERSION ?></p>
            </footer>
        </main>
    </div>

    <script>
        tailwind.config = {
            content: ["./*.html"],
            darkMode: "class"
        };
    </script>

    <script>
        const body = document.body;
        const toggleButton = document.getElementById('darkModeToggle');

        const updateDarkMode = (enabled) => {
            if (enabled) {
                body.classList.add('dark');
                toggleButton.textContent = 'Modo Claro';
                localStorage.setItem('darkMode', 'enabled');
            } else {
                body.classList.remove('dark');
                toggleButton.textContent = 'Modo Oscuro';
                localStorage.setItem('darkMode', 'disabled');
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            const darkMode = localStorage.getItem('darkMode') === 'enabled';
            updateDarkMode(darkMode);
        });

        toggleButton.addEventListener('click', () => {
            const darkMode = !body.classList.contains('dark');
            updateDarkMode(darkMode);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('httpForm');
            const nameInput = document.getElementById('name');
            const methodSelect = document.getElementById('method');
            const urlInput = document.getElementById('url');
            const bodyTextarea = document.getElementById('body');
            const responseArea = document.getElementById('response');
            const savedCallsList = document.getElementById('savedCalls');

            let savedCalls = JSON.parse(localStorage.getItem('savedCalls')) || [];

            function updateSavedCallsList() {
                savedCallsList.innerHTML = '';
                savedCallsList.className = 'space-y-2';
                savedCalls.forEach((call, index) => {
                    const li = document.createElement('li');
                    li.className = 'flex justify-between items-center py-1 pl-2 pr-1 bg-white dark:bg-gray-800 dark:text-white rounded-md border dark:border-none rounded hover:bg-gray-50';

                    const button = document.createElement('button');
                    button.textContent = call.name || call.url;
                    button.className = 'text-left flex-grow font-medium truncate';
                    button.onclick = () => loadCall(index);

                    const deleteButton = document.createElement('button');
                    deleteButton.textContent = '✘';
                    deleteButton.className = 'flex items-center justify-center w-6 h-6 text-gray-400 hover:text-red-600 rounded-md hover:bg-red-100';
                    deleteButton.onclick = (e) => {
                        e.stopPropagation();
                        deleteCall(index);
                    };

                    li.appendChild(button);
                    li.appendChild(deleteButton);
                    savedCallsList.appendChild(li);
                });
            }

            function saveCall() {
                const newCall = {
                    name: nameInput.value,
                    method: methodSelect.value,
                    url: urlInput.value,
                    body: bodyTextarea.value
                };
                savedCalls = [newCall, ...savedCalls.filter(call => call.name !== newCall.name)].slice(0, 10);
                localStorage.setItem('savedCalls', JSON.stringify(savedCalls));
                updateSavedCallsList();
            }

            function loadCall(index) {
                const call = savedCalls[index];
                nameInput.value = call.name || '';
                methodSelect.value = call.method;
                urlInput.value = call.url;
                bodyTextarea.value = call.body || '';
            }

            function deleteCall(index) {
                savedCalls.splice(index, 1);
                localStorage.setItem('savedCalls', JSON.stringify(savedCalls));
                updateSavedCallsList();
            }

            form.addEventListener('submit', (e) => {
                saveCall();
            });

            updateSavedCallsList();
        });
    </script>
</body>

</html>