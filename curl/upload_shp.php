<?php
	$url_upload = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/dialogs/SWFUpload/upload.php';
    
    $shp_filename = $_FILES['shp-file']['name'];
    $shp_filedata = $_FILES['shp-file']['tmp_name'];
    $shp_filesize = $_FILES['shp-file']['size'];

    if ($shp_filedata != '')
    {
        $headers = array("Content-Type:multipart/form-data");
                
        $postfields_shp = array(
            "Filename" => $shp_filename, 
            "PHPSESSID" => session_id(), 
            "type" => "Shp",
            "Filedata" => "@$shp_filedata",
            //"Filedata" => "@C:\shapefile\shapefile.shp",
            "Upload" => "Submit Query"
        );
                
        $ch_shp = curl_init();
        $options_shp = array(
            CURLOPT_URL => $url_upload,
            CURLOPT_HEADER => false,
            CURLOPT_POST => 1,
            CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $postfields_shp,
            CURLOPT_INFILESIZE => $shp_filesize,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch_shp, $options_shp);
        $result_shp = curl_exec($ch_shp);

        curl_close($ch_shp);
    }
    else
    {
       echo "Выберите shp-файл\n";
    }
	
	echo $result_shp;
	
?>