<?php
// List of PHP files in the pages directory
$page_files = [
    'job_details.php',
    'user_info.php',
    'applications.php',
    'profile.php',
    'confirm.php',
    'applicants.php'
    // login.php and register.php are already updated
];

foreach ($page_files as $file) {
    $file_path = __DIR__ . '/pages/' . $file;
    
    if (file_exists($file_path)) {
        // Read file content
        $content = file_get_contents($file_path);
        
        // Check if CSS link already exists
        if (strpos($content, 'deep-theme.css') === false) {
            // Add deep-theme.css link after boxicons CSS link
            $pattern = '/<link rel="stylesheet" href="https:\/\/cdn\.jsdelivr\.net\/npm\/boxicons@latest\/css\/boxicons\.min\.css">/';
            $replacement = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">' . PHP_EOL . '    <link rel="stylesheet" href="../css/deep-theme.css">';
            
            // Replace the pattern
            $content = preg_replace($pattern, $replacement, $content);
            
            // If the first pattern didn't match, try looking for the end of head tag
            if (strpos($content, 'deep-theme.css') === false) {
                $head_pattern = '/<\/head>/';
                $head_replacement = '    <link rel="stylesheet" href="../css/deep-theme.css">' . PHP_EOL . '</head>';
                $content = preg_replace($head_pattern, $head_replacement, $content);
            }
            
            // Remove :root declaration and CSS variables if they exist
            $root_pattern = '/\s*:root\s*{[^}]+}/';
            $content = preg_replace($root_pattern, '', $content);
            
            // Save the modified content back to the file
            file_put_contents($file_path, $content);
            
            echo "Updated file: $file\n";
        } else {
            echo "File already updated: $file\n";
        }
    } else {
        echo "File not found: $file\n";
    }
}

echo "CSS update complete!\n";
?> 