$(document).ready(function() {
	var result_shp;
	var result_prj;
	var result_dbf;
	var result_shx;
	
    //ПРИ ВЫБОРЕ ФАЙЛА ПРОИСХОДИТ POST-ЗАПРОС НА ЕГО ЗАГРУЗКУ
	$('#shp').change(function () { 
		var data = new FormData();
		jQuery.each(jQuery('#shp')[0].files, function(name, file) {
			data.append('shp-file', file);
		});
        
		$.ajax({ 
			url: 'curl/upload_shp.php',
			type: 'post',
			data: data,
			async: false, 
			cache: false, 
			contentType: false, 
			processData: false,
			success: function (res) {
				//ЗАПИСЬ В ПЕРЕМЕННУЮ, КОТОРАЯ БУДЕТ ПЕРЕДАНА GET-методом
                result_shp = res;
			}
		}) 
	})

     //ПРИ ВЫБОРЕ ФАЙЛА ПРОИСХОДИТ POST-ЗАПРОС НА ЕГО ЗАГРУЗКУ
	$('#prj').change(function () { 		
		var data = new FormData();
		jQuery.each(jQuery('#prj')[0].files, function(name, file) {
			data.append('prj-file', file);
		});
		
		$.ajax({ 
			url: 'curl/upload_prj.php',
			type: 'post',
			data: data,		
			async: false, 
			cache: false, 
			contentType: false, 
			processData: false, 
			success: function (res) {
                //ЗАПИСЬ В ПЕРЕМЕННУЮ, КОТОРАЯ БУДЕТ ПЕРЕДАНА GET-методом
				result_prj = res;
			}
		}) 
	}) 

     //ПРИ ВЫБОРЕ ФАЙЛА ПРОИСХОДИТ POST-ЗАПРОС НА ЕГО ЗАГРУЗКУ
	$('#dbf').change(function () {
		var data = new FormData();
		jQuery.each(jQuery('#dbf')[0].files, function(name, file) {
			data.append('dbf-file', file);
		});
		
		$.ajax({ 
			url: 'curl/upload_dbf.php',
			type: 'post',
			data: data, 
			async: false, 
			cache: false, 
			contentType: false, 
			processData: false, 
			success: function (res) {
                //ЗАПИСЬ В ПЕРЕМЕННУЮ, КОТОРАЯ БУДЕТ ПЕРЕДАНА GET-методом
				result_dbf = res;
			}
		}) 
	})

     //ПРИ ВЫБОРЕ ФАЙЛА ПРОИСХОДИТ POST-ЗАПРОС НА ЕГО ЗАГРУЗКУ
	$('#shx').change(function () {   
		var data = new FormData();
		jQuery.each(jQuery('#shx')[0].files, function(name, file) {
			data.append('shx-file', file);
		});		
		
		$.ajax({ 
			url: 'curl/upload_shx.php',
			type: 'post',
			data: data, 
			async: false, 
			cache: false, 
			contentType: false, 
			processData: false, 
			success: function (res) {
                //ЗАПИСЬ В ПЕРЕМЕННУЮ, КОТОРАЯ БУДЕТ ПЕРЕДАНА GET-методом
				result_shx = res;
			}
		})
	})	
	 
    //ОТПРАВЛЯЕМ ФОРМУ НА СЕРВЕР
	$("form#pub").submit(function(event){
		event.preventDefault();
        //АВТОМАТИЧЕСКИ ФОРМИРУЕМ ФОРМУ ДЛЯ ОТПРАВКИ НА СЕРВЕР
		//var formData = new FormData($(this)[0]);       
        
        //ВРУЧНУЮ ФОРМИРУЕМ ФОРМУ ДЛЯ ОТПРАВКИ НА СЕРВЕР
        var formData = new FormData();
		
        formData.append('rus_name', $("#rus_name").val());
		formData.append('eng_name', $("#eng_name").val());
        
        formData.append('color_bgr', $("#color_bgr").val());
        formData.append('color_around', $("#color_around").val());
        
        formData.append('group_id', $("#group_list").val());
        
		$.ajax({
			url: 'curl/public_layers.php?result_shp=' + result_shp + '&result_prj=' + result_prj + '&result_dbf=' + result_dbf + '&result_shx=' + result_shx,
			type: 'POST',
			data: formData,
			async: false,
			cache: false,
			contentType: false,
			processData: false,
			success: function (returndata) {
                alert(returndata);
				if(returndata == "Слой добавлен!"){
                    //ОЧИЩАЕМ ФОРМУ
                    $('#upload-shp-info').text('');
                    $('#upload-prj-info').text(''); 
                    $('#upload-dbf-info').text(''); 
                    $('#upload-shx-info').text('');
                    
                    $('#shp').get(0).value = '';
                    $('#shp').get(0).type = '';
                    $('#shp').get(0).type = 'file';
                    
                    $('#prj').get(0).value = '';
                    $('#prj').get(0).type = '';
                    $('#prj').get(0).type = 'file';
                    
                    $('#dbf').get(0).value = '';
                    $('#dbf').get(0).type = '';
                    $('#dbf').get(0).type = 'file';
                    
                    $('#shx').get(0).value = '';
                    $('#shx').get(0).type = '';
                    $('#shx').get(0).type = 'file';
                    
                    $('#rus_name').get(0).value = '';
                    $('#eng_name').get(0).value = '';
                    
                    $('#group_list').get(0).value = '';
                    
                    $('#color_bgr').get(0).value = '';
                    $('#color_around').get(0).value = '';
                    
				}
			}
		})
		return false;
	})
    
    $('input[type="reset"]').click(function() {
        $('#upload-shp-info').text('');
        $('#upload-prj-info').text(''); 
        $('#upload-dbf-info').text(''); 
        $('#upload-shx-info').text('');
        $('#shp').get(0).value = '';
        $('#shp').get(0).type = '';
        $('#shp').get(0).type = 'file';

        $('#prj').get(0).value = '';
        $('#prj').get(0).type = '';
        $('#prj').get(0).type = 'file';

        $('#dbf').get(0).value = '';
        $('#dbf').get(0).type = '';
        $('#dbf').get(0).type = 'file';

        $('#shx').get(0).value = '';
        $('#shx').get(0).type = '';
        $('#shx').get(0).type = 'file';
    });
});