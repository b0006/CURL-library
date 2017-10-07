<?php
	$url_upload = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/dialogs/SWFUpload/upload.php';
    
    $shx_filename = $_FILES['shx-file']['name'];
    $shx_filedata = $_FILES['shx-file']['tmp_name'];
    $shx_filesize = $_FILES['shx-file']['size'];
	
    if ($shx_filedata != '')
    {
        $headers = array("Content-Type:multipart/form-data");
                
        $postfields_shx = array(
            "Filename" => $shx_filename, 
            "PHPSESSID" => session_id(), 
            "type" => "Shx",
            "Filedata" => "@$shx_filedata",
            //"Filedata" => "@C:\shapefile\shapefile.shx",
            "Upload" => "Submit Query"
        );
                
        $ch_shx = curl_init();
        $options_shx = array(
            CURLOPT_URL => $url_upload,
            CURLOPT_HEADER => false,
            CURLOPT_POST => 1,
            CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $postfields_shx,
            CURLOPT_INFILESIZE => $shx_filesize,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch_shx, $options_shx);
        $result_shx = curl_exec($ch_shx);

        curl_close($ch_shx);
    }
    else
    {
       echo "Выберите shx-файл\n";
    }
	
	echo $result_shx;
	
?>