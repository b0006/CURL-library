<?php	
	$url_upload = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/dialogs/SWFUpload/upload.php';
    
    $dbf_filename = $_FILES['dbf-file']['name'];
    $dbf_filedata = $_FILES['dbf-file']['tmp_name'];
    $dbf_filesize = $_FILES['dbf-file']['size'];
	
    if ($dbf_filedata != '')
    {
        $headers = array("Content-Type:multipart/form-data");
                
        $postfields_dbf = array(
            "Filename" => $dbf_filename, 
            "PHPSESSID" => session_id(), 
            "type" => "Dbf",
            "Filedata" => "@$dbf_filedata",
            //"Filedata" => "@C:\shapefile\shapefile.dbf",
            "Upload" => "Submit Query"
        );
                
        $ch_dbf = curl_init();
        $options_dbf = array(
            CURLOPT_URL => $url_upload,
            CURLOPT_HEADER => false,
            CURLOPT_POST => 1,
            CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $postfields_dbf,
            CURLOPT_INFILESIZE => $dbf_filesize,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch_dbf, $options_dbf);
        $result_dbf = curl_exec($ch_dbf);

        curl_close($ch_dbf);
    }
    else
    {
       echo "Выберите dbf-файл\n";
    }
	
	echo $result_dbf;
	
?>