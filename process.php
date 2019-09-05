<?php
    include('functions.php');
    $tags = '';
    foreach ($_POST['tags'] as $tag) {
        $tags .= trim($tag) . "; ";
    }
    echo dispay_iptc($image);
    $tags .= $_POST['manual_tags'];
    $tags_written = write_tags($_POST['file_name'], $tags);
    
    rename ($_POST['file_name'],'completed_images/'.$_POST['file_name']);

    echo "tags written: $tags_written<br><br><a href='.'>Tag Another</a>";
?>