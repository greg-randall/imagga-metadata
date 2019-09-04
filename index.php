<pre>
<?php 
    $debug = FALSE;
    $quality_threshold = 30; //the confidences are represented by a percentage number from 0 to 100 where 100 means that the API is absolutely sure the tags must be relevant anf confidence < 30 means that there is a higher chance that the tag might not be relevant.
    include('credentials.php');

    //create a small image for upload
    $image = imagecreatefromstring(file_get_contents("test2.jpg"));
    $image_height = imagesy($image);
    $image_width = imagesx($image);

    if($image_height > 300 | $image_width > 300){//make sure at least one side is bigger than 300
        if($image_height > $image_width){
            $new_height = 300;
            $new_width = round( (300*$image_width)/$image_height );
        }else{
            $new_width = 300;
            $new_height = round( (300*$image_height)/$image_width);
        }
        $small_image = imagescale($image, $new_width, $new_height);
        imagedestroy ($image);
        imagejpeg($small_image, "small_image_temporary.jpg");
        imagedestroy ($small_image);
    }

    echo "<img src='small_image_temporary.jpg'><br>";

 
    //upload the image for tagging 
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.imagga.com/v2/uploads");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_USERPWD, $api_credentials['key'].':'.$api_credentials['secret']);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, 1);
    $fields = [
        'image' => new \CurlFile('small_image_temporary.jpg', 'image/jpeg', 'small_image_temporary.jpg')
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    $response = curl_exec($ch);
    curl_close($ch);

    $json_response = json_decode($response, TRUE);
    if($debug){
        print_r($json_response);
    }


    //request tags for the small image uploaded above
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.imagga.com/v2/tags?threshold='.$quality_threshold.'&image_upload_id='.$json_response['result']['upload_id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_USERPWD, $api_credentials['key'].':'.$api_credentials['secret']);

    $response = curl_exec($ch);
    curl_close($ch);

    $json_response = json_decode($response, TRUE);
    if($debug){
        print_r($json_response);
    }

    //print the tags
    foreach($json_response['result']['tags'] as $tag){
        echo $tag['tag']['en'].' ('.round($tag['confidence']).')<br>';
    }

?>