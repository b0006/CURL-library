<?php 

//ЕСЛИ ФАЙЛЫ БЫЛИ ЗАГРУЖЕНЫ
if( (isset($_GET['result_shp'])) AND (isset($_GET['result_prj'])) AND (isset($_GET['result_dbf'])) AND (isset($_GET['result_shx'])))
{
	//-----------АВТОРИЗАЦИЯ
	
	$url_auth = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/authorization/comeIn/';
	  
    $postfields_auth = array( 
        'login_user'=>'depadmin',
        'pass_user'=>'12345',
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
     
    //-----------POST-запрос на uploadfiles (ШАГ 1)
    
	//ПОЛУЧАЕМ АНГЛИЙСКОЕ И РУССКОЕ НАЗВАНИЕ
    $eng_name = $_POST['eng_name'];
    $rus_name = $_POST['rus_name'];
    
	//ПОЛУЧАЕМ ID ГРУППЫ
	$group_id = $_POST['group_id'];

	//ДАННЫЙ ЗАГОЛОВОК ОБЯЗАТЕЛЕН
	$headers = array("Content-Type:multipart/form-data");
	
	$url_uploadfiles = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/layers/action/uploadfiles';
                
	$postfields_uploadfiles = array(
        "layerfromfile_isView" => '',
        "layerfromfile_publishName" => '',
        "layerfromfile_server_id" => '6',
        "layerfromfile_workspace" => 'http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/rest/workspaces/workspace/datastores.json',
        "layerfromfile_datastore" => 'http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/rest/workspaces/workspace/datastores/editor_store.json',
        "layerfromfile_engname" => $eng_name,
        "layerfromfile_rusname" => $rus_name,
        "layerfromfile_filetype" => 'shp',
        "shp_shp_file" => $_GET['result_shp'],
        "shp_dbf_file" => $_GET['result_dbf'],
        "shp_prj_file" => $_GET['result_prj'],
        "shp_shx_file" => $_GET['result_shx'],
        "tab_tab_file" => '',
        "tab_map_file" => '',
        "tab_dat_file" => '',
        "tab_id_file" => '',
        "mid_mid_file" => '',
        "mid_mif_file" => '',
        "projection_srid" => '32646',
        "layerfromfile_style" => '1',
        "layerfromfile_layer_style" => '',
        "layerfromfile_layer_link" => 'empty',
        "layerfromfile_namespace" => 'empty',
        "layerfromfile_style_name" => '',
        "layerfromfile_imagehref" => 'empty',
        "layerfromfile_service" => 'WMS',
        "layerfromfile_group_id" => $group_id,
        "action" => 'add'
    );
                
    $ch_uploadfiles = curl_init();
    $options_uploadfiles = array(
        CURLOPT_URL => $url_uploadfiles,
        CURLOPT_HEADER => false,
        CURLOPT_POST => 1,
        CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postfields_uploadfiles,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch_uploadfiles, $options_uploadfiles);
    curl_exec($ch_uploadfiles);
    curl_close($ch_uploadfiles);

    //-----------POST-запрос на stylecreating (ШАГ 2)
    
	//ПОЛУЧАЕМ ЗНАЧЕНИЯ ЦВЕТОВ ДЛЯ ФОНА И КОНТУРА
	$color_bgr = $_POST['color_bgr'];
	$color_around = $_POST['color_around'];
	
	//УДАЛЯЕМ ПЕРВЫЙ ЗНАК - #
	$color_bgr = substr( $color_bgr, 1);
	$color_around = substr( $color_around, 1);
	
	//НИЖНИЙ РЕГИСТР
    $low_eng_name = strtolower($eng_name);
    
    $url_style = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/layers/action/stylecreating';
                
    $postfields_style = array(
        "layerfromfile_isView" => '1',
        "layerfromfile_publishName" => $low_eng_name . '_vw',
        "layerfromfile_server_id" => '6',
        "layerfromfile_workspace" => 'http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/rest/workspaces/workspace/datastores.json',
        "layerfromfile_datastore" => 'http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/rest/workspaces/workspace/datastores/editor_store.json',
        "layerfromfile_engname" => $eng_name,
        "layerfromfile_rusname" => $rus_name,
        "layerfromfile_filetype" => 'shp',
        "layerfromfile_geomtype" => 'polygon',
        "layerfromfile_layer_style" => '',
        "layerfromfile_style" => '3',
        "layerfromfile_polygon_fill_color" => $color_bgr,
        "layerfromfile_polygon_opacity" => '1',
        "layerfromfile_polygonstroke" => '1',
        "layerfromfile_polygon_stroke_width" => '1',
        "layerfromfile_polygon_stroke_color" => $color_around,
        "layerfromfile_layer_link" => 'empty',
        "layerfromfile_namespace" => 'empty',
        "layerfromfile_style_name" => '',
        "layerfromfile_imagehref" => 'empty',
        "layerfromfile_service" => 'WMS',
        "layerfromfile_group_id" => $group_id,
        "action" => 'add'
    );
                
    $ch_style = curl_init();
    $options_style = array(
        CURLOPT_URL => $url_style,
        CURLOPT_HEADER => false,
        CURLOPT_POST => 1,
        CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postfields_style,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch_style, $options_style);
    curl_exec($ch_style);
	curl_close($ch_style);
    
    //-----------POST-запрос на addlayersfromfile (ШАГ 3)
    
	$url_addlayer = 'http://krasnoyarsk-geomonitoring.ssc.ikit.sfu-kras.ru/layers/action/addlayerfromfile';
                
	$postfields_addlayer = array(
        "layerfromfile_isView" => '1',
        "layerfromfile_publishName" => $low_eng_name . '_vw',
        "layerfromfile_server_id" => '6',
        "layerfromfile_workspace" => 'http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/rest/workspaces/workspace/datastores.json',
        "layerfromfile_datastore" => 'http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/rest/workspaces/workspace/datastores/editor_store.json',
        "layerfromfile_engname" => $eng_name,
        "layerfromfile_rusname" => $rus_name,
        "layerfromfile_filetype" => 'shp',
        "layerfromfile_layer_link" => 'empty',
        "layerfromfile_namespace" => 'workspace',
        "layerfromfile_style_name" => $low_eng_name . '_vw_style',
        "layerfromfile_imagehref" => 'http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/wms?REQUEST=GetLegendGraphic&VERSION=1.1.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=' . $low_eng_name . '_vw',
        "attr_id" => '',
        "attribute_title[id]" => '1',
        "attr_owner" => '',
        "attr_area_giv" => '',
        "attr_area_giv_" => '',
        "attr_area_calc" => '',
        "attr_culture_13" => '',
        "attr_cult14plan" => '',
        "attr_culture_14" => '',
        "attr_culture_15" => '',
        "attr_data_seva" => '',
        "attr_master_id" => '',
        "attr_status" => '',
        "attr_culture_pl" => '',
        "attr_culture_fa" => '',
        "attr_geom" => '',
        "layerfromfile_service" => 'WMS',
        "layerfromfile_group_id" => $group_id,
        "action" => 'add'
	);
                
    $ch_addlayer = curl_init();
    $options_addlayer = array(
        CURLOPT_URL => $url_addlayer,
        CURLOPT_HEADER => false,
        CURLOPT_POST => 1,
        CURLOPT_COOKIEFILE => dirname(__FILE__).'/cookie.txt',
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postfields_addlayer,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch_addlayer, $options_addlayer);
    $result = curl_exec($ch_addlayer);
    curl_close($ch_addlayer);
    
	//РАСКОДИРОВКА ОТВЕТА (ИЗНАЧАЛЬНО ОН ЗАКОДИРОВАН JSON'ом)
	$result = json_decode($result, true);
    
	//ВЫВОД НА ЭКРАН СООБЩЕНИЯ О РЕЗУЛЬТАТЕ
	foreach ($result as $key1 => $value1) {
		if($key1 == "message")
			print_r($result[$key1]);
	}
}
else {
	echo "Не загружен один из файлов";
}
    
?>