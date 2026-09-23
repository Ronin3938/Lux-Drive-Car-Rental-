<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Notification Check</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f4f8;
        }

        .container {
            text-align: center;
            background-color: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            max-width: 400px;
            width: 90%;
        }

        #results {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #e2e8f0;
            border-radius: 0.5rem;
            min-height: 50px;
            text-align: left;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Manual Notification Check</h1>
        <p class="text-gray-600 mb-6">Click the button below to check for and send notifications for all newly available cars.</p>
        <button id="runButton" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Check & Send Notifications
        </button>
        <div id="results" class="text-sm text-gray-700">
            Click the button to see the results.
        </div>
    </div>

    <script>
        document.getElementById('runButton').addEventListener('click', async () => {
            const resultsDiv = document.getElementById('results');
            const button = document.getElementById('runButton');

            button.disabled = true;
            button.textContent = 'Processing...';
            resultsDiv.textContent = 'Running the script... Please wait.';

            try {
                // Send a request to the PHP script
                const response = await fetch('send_availability_notifications.php');
                const text = await response.text();

                // Display the output from the PHP script
                resultsDiv.textContent = text;

            } catch (error) {
                resultsDiv.textContent = 'An error occurred. Please check the server logs.';
                console.error('Error:', error);
            } finally {
                button.disabled = false;
                button.textContent = 'Check & Send Notifications';
            }
        });
    </script>

</body>

</html>