<pre>
<?php
    include('credentials.php');
    include('functions.php');
    $minimum_confidence = 50;
    
    $file_name = 'test.jpg';
    
    make_small_image($file_name, 'output-small.jpg');
    
    echo "<img src='$file_name' width='300px'><br>";
    
    $upload_id = upload_to_imagga('output-small.jpg');
    
    $tags = request_tags($upload_id);
    
    echo "<form action='process.php' method='post'>\n";
    echo "<input type='hidden' name='file_name' value='$file_name'>";
    echo "<table><tr><th>Suggested Tags</th><th>Existing Tags</th></tr><tr><td>";
    foreach ($tags as $tag => $confidence) {
        if ($confidence >= $minimum_confidence) {
            $checked = "checked='checked'";
        } else {
            $checked = "";
        }
        echo "<input type='checkbox' name='tags[]' value='$tag' $checked>$tag ($confidence)<br>\n";
    }
    echo "</td><td>";
    
    echo "<input type='checkbox' name='tags[]' value='$tag' checked='checked'>asdf$tag<br>\n";
    
    
    echo "</td><td></tr></table>";
    
    echo "New Tags (Semicolon Delimited):<br><input type='text' name='manual_tags'><br>";
    echo "<br><input type='submit' name='formSubmit' value='Submit' />";
    
    echo "</form>";
    
    
?>