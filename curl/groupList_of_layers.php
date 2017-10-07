<?php 
	//-----------POST запрос на авторизацию
    $url_auth = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/authorization/comeIn/';
	  
    $postfields_auth = array( 
        'login_user'=>'user',
        'pass_user'=>'password',
    );
                
    $ch_auth = curl_init();
    $options_auth = array(
        CURLOPT_URL => $url_auth,
        CURLOPT_HEADER => true,
        CURLOPT_POST => 1,
		CURLOPT_COOKIEJAR => dirname(__FILE__).'/cookie.txt',
        CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
        CURLOPT_POSTFIELDS => $postfields_auth,
        CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch_auth, $options_auth);
    $html = curl_exec($ch_auth);
    curl_close($ch_auth);
    
    //-----------ПОЛУЧАЕМ СПИСОК ГРУПП (первая страница список групп)
    $url_groups = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/layers/group/1';         
    $postfields_groups = array(  
        "action" => "pages"
    );
    
    $ch_groups = curl_init();
    $options_groups = array(
        CURLOPT_URL => $url_groups,
        CURLOPT_HEADER => false,
        CURLOPT_POST => 1,
        CURLOPT_ENCODING => 'utf-8',
        CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
        CURLOPT_POSTFIELDS => $postfields_groups,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch_groups, $options_groups);
    $result = curl_exec($ch_groups);

    curl_close($ch_groups);
    
    //-----------ПОЛУЧАЕМ СПИСОК ГРУПП (вторая страница список групп)
    $url_groups2 = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/layers/group/2';         
    $postfields_groups2 = array(  
        "action" => "pages"
    );
    
    $ch_groups2 = curl_init();
    $options_groups2 = array(
        CURLOPT_URL => $url_groups2,
        CURLOPT_HEADER => false,
        CURLOPT_POST => 1,
        CURLOPT_ENCODING => 'utf-8',
        CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
        CURLOPT_POSTFIELDS => $postfields_groups2,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch_groups2, $options_groups2);
    $result2 = curl_exec($ch_groups2);

    curl_close($ch_groups2);
    
	//РАСКОДИРОВКА ОТВЕТА (ИЗНАЧАЛЬНО ОН ЗАКОДИРОВАН JSON'ом)
    $result = json_decode($result, true);
   
	//ОТОБРАЖАЕМ СПИСОК ГРУПП
	$list = '<select id="group_list" name="group_id">';
    $list .= '<option></option>';

    foreach ($result as $field => $value) {
        if (is_array($value)){
            for ($i = 0; $i < count($value); $i++) {
                foreach ($value[$i] as $field_ob => $value_ob) {
                    if($field_ob == "id")
                    {
						$list .= '<option value="' . $value_ob . '" >';
                    }
                    if($field_ob == "name")
                    {
						$list .= $value_ob . '</option>';
                    }
                }
            }
        } 
    }
    
	//ТАКЖЕ РАСКОДИРОВКА ОТВЕТА (ИЗНАЧАЛЬНО ОН ЗАКОДИРОВАН JSON'ом), 2 страница
    $result2 = json_decode($result2, true);
    
    foreach ($result2 as $field => $value) {
        if (is_array($value)){
            for ($i = 0; $i < count($value); $i++) {
                foreach ($value[$i] as $field_ob => $value_ob) {
                    if($field_ob == "id")
                    {
						$list .= '<option value="' . $value_ob . '" >';
                    }
                    if($field_ob == "name")
                    {
						$list .= $value_ob . '</option>';
                    }
                }
            }
        } 
    } 
	$list .= '</select></br></br>';
?>
