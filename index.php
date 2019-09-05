<pre>
<?php
    include('credentials.php');
    include('functions.php');
    
    make_small_image('test2.jpg', 'output-small.jpg');
    
    echo "<img src='output-small.jpg'><br>";
    
    $upload_id = upload_to_imagga('output-small.jpg');
    
    print_r(request_tags($upload_id));
?>