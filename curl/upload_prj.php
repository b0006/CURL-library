<?php
	$url_upload = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/dialogs/SWFUpload/upload.php';
    
    $prj_filename = $_FILES['prj-file']['name'];
    $prj_filedata = $_FILES['prj-file']['tmp_name'];
    $prj_filesize = $_FILES['prj-file']['size'];
	
    if ($prj_filedata != '')
    {
        $headers = array("Content-Type:multipart/form-data");
                
        $postfields_prj = array(
            "Filename" => $prj_filename, 
            "PHPSESSID" => session_id(), 
            "type" => "Prj",
            "Filedata" => "@$prj_filedata",
            //"Filedata" => "@C:\shapefile\shapefile.prj",
            "Upload" => "Submit Query"
        );
                
        $ch_prj = curl_init();
        $options_prj = array(
            CURLOPT_URL => $url_upload,
            CURLOPT_HEADER => false,
            CURLOPT_POST => 1,
            CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $postfields_prj,
            CURLOPT_INFILESIZE => $prj_filesize,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch_prj, $options_prj);
        $result_prj = curl_exec($ch_prj);

        curl_close($ch_prj);
    }
    else
    {
       echo "Выберите prj-файл\n";
    }
	
	echo $result_prj;
	
?>