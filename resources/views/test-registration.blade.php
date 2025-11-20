<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Registration Validation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .test-section h3 {
            margin-top: 0;
            color: #333;
        }
        .test-input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .test-button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .test-button:hover {
            background: #0056b3;
        }
        .result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Test Registration Validation</h1>
        <p>This page tests the registration form validation functionality.</p>

        <div class="test-section">
            <h3>1. Test Email Uniqueness Check</h3>
            <p>Test the AJAX email validation endpoint:</p>
            <input type="email" id="testEmail" class="test-input" placeholder="Enter email to test">
            <button onclick="testEmailCheck()" class="test-button">Check Email</button>
            <div id="emailResult" class="result" style="display: none;"></div>
        </div>

        <div class="test-section">
            <h3>2. Test Known Emails</h3>
            <p>Test with emails that should already exist in the database:</p>
            <button onclick="testKnownEmail('admin.bps@gmail.com')" class="test-button">Test admin.bps@gmail.com</button>
            <button onclick="testKnownEmail('adi.darmanto@bps.go.id')" class="test-button">Test adi.darmanto@bps.go.id</button>
            <div id="knownEmailResult" class="result" style="display: none;"></div>
        </div>

        <div class="test-section">
            <h3>3. Test New Email</h3>
            <p>Test with a new email that should be available:</p>
            <button onclick="testKnownEmail('newuser@example.com')" class="test-button">Test newuser@example.com</button>
            <div id="newEmailResult" class="result" style="display: none;"></div>
        </div>

        <div class="test-section">
            <h3>4. Test Invalid Email Format</h3>
            <p>Test with invalid email formats:</p>
            <button onclick="testKnownEmail('invalid-email')" class="test-button">Test invalid-email</button>
            <button onclick="testKnownEmail('test@')" class="test-button">Test test@</button>
            <div id="invalidEmailResult" class="result" style="display: none;"></div>
        </div>

        <div class="test-section">
            <h3>5. Go to Registration Page</h3>
            <p>Test the actual registration form:</p>
            <a href="/register" class="test-button" style="text-decoration: none; display: inline-block;">Open Registration Form</a>
        </div>
    </div>

    <script>
        function testEmailCheck() {
            const email = document.getElementById('testEmail').value;
            const resultDiv = document.getElementById('emailResult');
            
            if (!email) {
                showResult(resultDiv, 'Please enter an email address', 'error');
                return;
            }

            showResult(resultDiv, 'Testing...', 'info');

            fetch('/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                const message = `Email: ${email}\nAvailable: ${data.available}\nMessage: ${data.message}`;
                showResult(resultDiv, message, data.available ? 'success' : 'error');
            })
            .catch(error => {
                console.error('Error:', error);
                showResult(resultDiv, 'Error: ' + error.message, 'error');
            });
        }

        function testKnownEmail(email) {
            const resultDiv = document.getElementById('knownEmailResult');
            
            showResult(resultDiv, 'Testing...', 'info');

            fetch('/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                const message = `Email: ${email}\nAvailable: ${data.available}\nMessage: ${data.message}`;
                showResult(resultDiv, message, data.available ? 'success' : 'error');
            })
            .catch(error => {
                console.error('Error:', error);
                showResult(resultDiv, 'Error: ' + error.message, 'error');
            });
        }

        function showResult(element, message, type) {
            element.textContent = message;
            element.className = 'result ' + type;
            element.style.display = 'block';
        }
    </script>
</body>
</html>
