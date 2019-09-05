<?php
    //create a small image for upload
    function make_small_image($image_path, $output_path) {
        $image        = imagecreatefromstring(file_get_contents($image_path));
        $image_height = imagesy($image);
        $image_width  = imagesx($image);
        if ($image_height > 300 | $image_width > 300) { //make sure at least one side is bigger than 300
            if ($image_height > $image_width) {
                $new_height = 300;
                $new_width  = round((300 * $image_width) / $image_height);
            } else {
                $new_width  = 300;
                $new_height = round((300 * $image_height) / $image_width);
            }
            $small_image = imagescale($image, $new_width, $new_height);
            imagedestroy($image);
            imagejpeg($small_image, $output_path);
            imagedestroy($small_image);
        }
    }



    //upload the image for tagging 
    function upload_to_imagga($image_path, $debug = false) {
        include('credentials.php');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.imagga.com/v2/uploads");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERPWD, $api_credentials['key'] . ':' . $api_credentials['secret']);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = [
            'image' => new \CurlFile($image_path, 'image/jpeg', $image_path)
         ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $response = curl_exec($ch);
        curl_close($ch);
        $json_response = json_decode($response, TRUE);
        if ($debug) {
            print_r($json_response);
        }
        return ($json_response['result']['upload_id']);
    }



    function request_tags($upload_id, $quality_threshold = 30, $debug = false) {
        //request tags 
        include('credentials.php');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imagga.com/v2/tags?threshold=' . $quality_threshold . '&image_upload_id=' . $upload_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_USERPWD, $api_credentials['key'] . ':' . $api_credentials['secret']);
        $response = curl_exec($ch);
        curl_close($ch);
        $json_response = json_decode($response, TRUE);
        if ($debug) {
            print_r($json_response);
        }
        foreach ($json_response['result']['tags'] as $tag) {
            $output_tags[$tag['tag']['en']] = round($tag['confidence']);
        }
        return ($output_tags);
    }



    function write_tags($input_file, $new_tags) {
        include('iptc_library.php');
        $image        = new iptc($input_file);
       // $current_tags = $image->get(IPTC_KEYWORDS);
       // if ((substr(trim($current_tags), -1) != ';') & ($current_tags != '')) {
        //    $current_tags .= '; ';
       // }
       // $updated_tags = $current_tags . $new_tags; //build new tag list 
        $new_tags = str_replace(',', ';', $new_tags); //make sure tags are delimited with a semicolon
        $new_tags = explode(';', $new_tags); //split all the tags apart
        for ($i = 0; $i < count($new_tags); $i++) {
            $new_tags[$i] = trim($new_tags[$i]);
        }
        $new_tags = array_unique($new_tags); //remove all duplicate tags
        $new_tags = implode('; ', $new_tags); //combine the de-duped tags
        $image->set(IPTC_KEYWORDS, $new_tags);
        $image->write();
        return ($new_tags);
    }

    function get_existing_tags($input_file){
        include('iptc_library.php');
        $image        = new iptc($input_file);
        $current_tags = $image->get(IPTC_KEYWORDS);
        $current_tags = str_replace(',', ';', $current_tags); //make sure tags are delimited with a semicolon
        $current_tags = explode(';', $current_tags);
        for ($i = 0; $i < count($current_tags); $i++) {
            $current_tags[$i] = trim($current_tags[$i]);
        }
        $current_tags = array_filter($current_tags);
        return($current_tags);
    }

    function dispay_iptc($image) {
        getimagesize($image, $data);
        $iptc = iptcparse($data['APP13']);
        print_r($iptc);
    }
?>