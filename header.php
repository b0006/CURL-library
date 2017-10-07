<?
session_start();
Error_Reporting(E_ALL & ~E_NOTICE);
?>
<!DOCTYPE html>
<html>
<head>

<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<meta name="author" content="ИКИТ">
<title>Active Map Manager</title>

<link rel="shortcut icon" type="image/png" href="http://krasnoyarsk-region.rekod.ru/statics/favicon/faviconRekod.png"/>
<link rel="stylesheet" type="text/css" href="/public/css/demo-examples.css">

<!--jQuery-->
<link rel="stylesheet" type="text/css" href="/public/jquery/jquery-ui.css">
<script src="/public/jquery/jquery-1.10.2.js" type="text/javascript"></script>
<script src="/public/jquery/jquery-ui-1.9.2.js" type="text/javascript"></script>

<!--GEOPORTAL API-->
<script src="http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/public/javascripts/geoportal/geoportal-api.min.js" type="text/javascript"></script>


<!--BOOTSTRAP-->
<link rel="stylesheet" type="text/css" href="/public/bootstrap2/css/bootstrap.css">
<script src="/public/bootstrap2/js/bootstrap.js" type="text/javascript"></script>

<!--ACTIVEMAP MANAGER-->
<script src="/public/js/activemap-manager.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="/public/css/s_activemap_ui.css">

<script type="text/javascript">
var ieStyle = userBrowserDefiner({'ie':'/public/css/ie.css', 'firefox':'/public/css/ie.css'});
document.write('<link rel="stylesheet" type="text/css" href="' + ieStyle + '">');

var zoomControlShift = userBrowserDefiner({'ie':'440px', 'chrome':'420px', 'firefox':'440px'});
</script>

<!--LIGHT TABS-->
<script src="/public/simple-tabs/js/ion.tabs.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="/public/simple-tabs/css/ion.tabs.css">
<link rel="stylesheet" type="text/css" href="/public/simple-tabs/css/ion.tabs.skinBordered.css">

<!--Stickers-->
<script src="/public/stickers/jquery.stickr.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="/public/stickers/stickr.css">

<!--Модальное окно публикации слоя-->
<link rel="stylesheet" href="/public/css/modal_public.css">
<script src="/public/js/modal-window.js" type="text/javascript"></script>

<!--Color Picker-->
<script src="/public/js/jquery.minicolors.js"></script>
<link rel="stylesheet" href="/public/css/jquery.minicolors.css">
    
<script>
    $(document).ready( function() {

      $('.demo').each( function() {
        $(this).minicolors({
          control: $(this).attr('data-control') || 'hue',
          defaultValue: $(this).attr('data-defaultValue') || '',
          format: $(this).attr('data-format') || 'hex',
          keywords: $(this).attr('data-keywords') || '',
          inline: $(this).attr('data-inline') === 'true',
          letterCase: $(this).attr('data-letterCase') || 'lowercase',
          opacity: $(this).attr('data-opacity'),
          position: $(this).attr('data-position') || 'bottom left',
          swatches: $(this).attr('data-swatches') ? $(this).attr('data-swatches').split('|') : [],
          change: function(value, opacity) {
            if( !value ) return;
            if( opacity ) value += ', ' + opacity;
            if( typeof console === 'object' ) {
              console.log(value);
            }
          },
          theme: 'bootstrap'
        });

      });

    });
</script>
    
<?
// Подключаем необходимые библиотеки
include("Modules/Users/Model/UsersModel.php");
include("vendor/Misc.php");
include("Modules/Popup/Model/PopupModel.php");
include("Modules/Styles/Model/StylesModel.php");
include("Modules/Farmers/Model/FarmersModel.php");
include("Modules/Logs/Model/LogsModel.php");

include("curl/groupList_of_layers.php"); //скрипт запроса списка групп слоев

$authButton = $_POST['auth'];
$authExitButton = $_POST['auth-exit'];
$z = 0;
if ( $authButton )
{
    $userModelObject = new UsersModel();
    $miscObject = new Misc();
    
    $login = $miscObject->CleanFormData($_POST['login']);
    $password = $miscObject->CleanFormData($_POST['password']);
    
    $encodePassword = md5($password);
    
    // Проверяем существует ли такой пользователь в БД, прежде чем записать его в сессию
    $result = $userModelObject->GetUser($login);
    $row = pg_fetch_array($result, null, PGSQL_ASSOC);
    
    if ( $row['login'] == $login && $row['password'] == $encodePassword )
    {
        $_SESSION['uLogin'] = $login;
        $_SESSION['uPassword'] = $password;
        $_SESSION['idRole'] = trim($row['role_id']);
        $_SESSION['idUser'] = trim($row['id']);
    }
}
if ( $authExitButton )
{
    session_unset();
    session_destroy();
}
if ( isset($_SESSION['uLogin']) && !empty($_SESSION['uLogin']) )
{
    $stylesModelObject = new StylesModel();
    // ПОЛУЧАЕМ ID ХОЗЯЙСТВА ПО ID ПОЛЬЗОВАТЕЛЯ. TODO: переделать на таблицу связи пользователей и хозяйств.
    $resultStyles = $stylesModelObject->GetUserStyles($_SESSION['idUser']);
    if ( isset($resultStyles) && !empty($resultStyles) ) {
        $rowStyles = pg_fetch_array($resultStyles, null, PGSQL_ASSOC);
        
        // ПОЛУЧАЕМ НАЗВАНИЕ ХОЗЯЙСТВА ИЗ СПРАВОЧНИКА ПО ID ХОЗЯЙСТВА.
        if ( isset($rowStyles['id_farmer']) ) {
            // Запоминаем ID хозяйства.
            $_SESSION['idFarmer'] = $rowStyles['id_farmer'];
			// Записываем в лог вход пользователя.
            $logsModelObject = new LogsModel();
            $logsModelObject->SaveLog(1, $rowStyles['id_farmer']);
            $popupModelObject = new PopupModel();
            $farmersModelObject = new FarmersModel();
            $resultFarmers = $popupModelObject->SelectDictionaryData($popupModelObject->FARMERS_TABLE, 'f_name', $rowStyles['id_farmer']);
            if ( isset($resultFarmers) && !empty($resultFarmers) ) {
                $rowFarmers = pg_fetch_array($resultFarmers, null, PGSQL_ASSOC);
                // Запоминаем название хозяйства.
                $_SESSION['farmerName'] = trim($rowFarmers['f_name']);
            }
            // ПОЛУЧАЕМ BBOX ХОЗЯЙСТВА, ЕСЛИ ОН ЕСТЬ.
            $result = $farmersModelObject->GetFarmerBbox($rowStyles['id_farmer']);
            if ( isset($result) && !empty($result) ) {
                $farmerBbox = pg_fetch_array($result, null, PGSQL_ASSOC);
                $_SESSION['minLat'] = $farmerBbox['minlat'];
                $_SESSION['minLng'] = $farmerBbox['minlng'];
                $_SESSION['maxLat'] = $farmerBbox['maxlat'];
                $_SESSION['maxLng'] = $farmerBbox['maxlng'];
                //echo "<br><br><br> ".$_SESSION['minLat']."<br>";
                //echo $_SESSION['minLng']."<br>";
                //echo $_SESSION['maxLat']."<br>";
                //echo $_SESSION['maxLng']."<br>";
            }
        }
    }
}
?>

<script type="text/javascript">
/****************************** ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ ******************************/

var login = "<?=$_SESSION['uLogin']?>";
var password = "<?=$_SESSION['uPassword']?>";
var idFarmer = "<?=$_SESSION['idFarmer']?>";
var farmerName = "<?=$_SESSION['farmerName']?>";
var role = "<?=$_SESSION['idRole']?>";

var _minLat = "<? if ( !empty($_SESSION['minLat']) ) echo $_SESSION['minLat']; else echo $z; ?>";
var _minLng = "<? if ( !empty($_SESSION['minLng']) ) echo $_SESSION['minLng']; else echo $z; ?>";
var _maxLat = "<? if ( !empty($_SESSION['maxLat']) ) echo $_SESSION['maxLat']; else echo $z; ?>";
var _maxLng = "<? if ( !empty($_SESSION['maxLng']) ) echo $_SESSION['maxLng']; else echo $z; ?>";

var hostName = 'krasnoyarsk-geomonitoring.ikit.sfu-kras.ru';

var currentUserName;
var currentUserLogin;

var farmerUniversalStyleName = 'contours_zshn_suchobuzimskoe_2015_universal_style';
var culturesPlanUniversalStyleName = 'cultures_plan_universal_style';
var culturesFactUniversalStyleName = 'cultures_fact_universal_style';

var filterCQLFarmer = new GeoPortal.Filter.CQL([
    {
        field: "owner",
        type: "string",
        value: farmerName
    }
]);

var attribsArray = new Array();
var queryArray = new Array();
var selectorLayer = new Array();

var idsString = '';

var mapObject;

var _currentToken;

var agriculturalLayer;
var layerOfFarmer;
var layerCultures;
var layerCulturesFact;

var randmz = Math.random();

var someString = '';
var groups_count;
var dwnld_usgs_params;
var _compare_bbox = {minLat: 90, maxLat: 0, minLng: 180, maxLng: 0};

/***********************************************************************************/

$(function() {
    var clickLatLn,
        marker;
    
    // ACCORDION ДЛЯ МЕНЮ СПРАВОЧНИКОВ.    
    $( "#accordion" ).accordion({
        collapsible: false
    });
    
    // АВТОРИЗАЦИЯ.
    GeoPortal.authenticate(login,password,
        function(data){
            GeoPortal.requestGroups(true,
                function(groups){
                    console.log(groups);
                },
                function(status,error){
                    console.log("Error to request groups list. Status = " +
                                status + ". Error text: " + error);
                }
            );
            
            // Получаем текущего пользователя
            GeoPortal.currentUser(
                function(user){
                    if (user != null){
                        currentUserLogin = user.login;
                        currentUserName = user.name;
                        $(".hello-user").html('Добро пожаловать, '+user.name);
                    }
                },
                function(status,error){
                    console.log("Error to request authentication. Status = " +
                                status + ". Error text: " + error);
                }
            );
            // end
            
        },
        function(status,error){
            //console.log("Error to request authentication. Status = " +
            //            status + ". Error text: " + error);
            
        }
    );
    // end
    

    // Отображаем элементы геопортала, когда объект GeoPortal загружен
    GeoPortal.on("ready",function() {

        
        var schemas     = GeoPortal.baseLayers.schemas,
            schemasLen  = schemas.length,
            spaces      = GeoPortal.baseLayers.spaces,
            spacesLen   =  spaces.length,
            currentBaseLayer,
            i=0,
            selected = "",
            baseLayer,
            baseLayerArray = new Array(),
            layersStore = new Array();
        
        _currentToken = GeoPortal._accessToken;
        
        // ЕСЛИ ПОЛЬЗОВАТЕЛЬ АДМИНИСТРАТОР, ТО ОН ДОЛЖЕН УВИДЕТЬ ВСЕ ХОЗЯЙСТВА, ВСЕ КУЛЬТУРЫ ПЛАНИРУЕМЫЕ И ФАКТИЧЕСКИЕ.
        if ( farmerName == '' ) {
            farmerUniversalStyleName = 'all_farmers_color_admin_style';
        }

        // формируем слой хозяйства, чтобы он был доступен везде
        layerOfFarmer = new GeoPortal.Layer.WMS("http://"+hostName+"/service/wms", {
            service: 'WMS',
            request: 'GetMap',
            version: '1.1.1',
            layers: 'workspace:contours_zshn_suchobuzimskoe_2015_vw',
            styles: farmerUniversalStyleName,
            format: 'image/png',
            transparent: true,
            cql_filter: filterCQLFarmer.filterString(),
            token: _currentToken,
            random: randmz
        });
        
        layerCultures = new GeoPortal.Layer.WMS("http://"+hostName+"/service/wms", {
            service: 'WMS',
            request: 'GetMap',
            version: '1.1.1',
            layers: 'workspace:contours_zshn_suchobuzimskoe_2015_vw',
            styles: culturesPlanUniversalStyleName,
            format: 'image/png',
            transparent: true,
            cql_filter: filterCQLFarmer.filterString(),
            token: _currentToken,
            random: randmz
        });
        
        layerCulturesFact = new GeoPortal.Layer.WMS("http://"+hostName+"/service/wms", {
            service: 'WMS',
            request: 'GetMap',
            version: '1.1.1',
            layers: 'workspace:contours_zshn_suchobuzimskoe_2015_vw',
            styles: culturesFactUniversalStyleName,
            format: 'image/png',
            transparent: true,
            cql_filter: filterCQLFarmer.filterString(),
            token: _currentToken,
            random: randmz
        });
        
        mapObject = new GeoPortal.Map('map');
        
        // Добавляем элемент Zoom
        zoom = new GeoPortal.Control.Zoom();
        zoom.on("handClick", function(){
            console.log("icon hand click");
        },this);
        // Позиционируем карту. Это позиционирование по умолчанию.
        mapObject.on("ready",function(){
                mapObject.setView(92.93111444,56.0641667,9);
        },this);

        mapObject.addControl(zoom);
        // end 
        
        // Добавляем элемент Измерение дистанции
        distance = new GeoPortal.Control.Distance();
        distance.on("control:distance:enable", function(data){
            console.log("control:distance:enable");
        },this);
        distance.on("control:distance:disable", function(data){
            console.log("control:distance:disable");
        },this);
        mapObject.addControl(distance);
        $('div#distanceButton img').prop("title", "Измерение расстояния");
        // end
        
        // Добавляем элемент выделения прямоугольником
        /*restangle = new GeoPortal.Control.RectangleDraw();
        restangle.on("control:RectangleDraw:enable", function(data){
            console.log("control:RectangleDraw:enable");
        },this);
        restangle.on("control:RectangleDraw:created", function(data){
            var latLngBounds = new GeoPortal.LatLngBounds(data.latLngs[0],data.latLngs[2]);
            mapObject.fitBounds(latLngBounds);
        },this);
        mapObject.addControl(restangle);*/
        // end
        
        // Добавляем элементы select для выбора карт-подложек
        currentBaseLayer = mapObject.baseLayer();
        
        $("body").find("#map").after('<div id="baseLayerContainer"/>');
            $("#baseLayerContainer").append('<div class="schemasContainer"><select class="selectBaseLayer"><option value="" selected>'+GPMessages("default.select")+'...</option></select></div>');
            $("#baseLayerContainer").append('<div class="spacesContainer"><select class="selectBaseLayer"><option value="" selected>'+GPMessages("default.select")+'...</option></select></div>');
        
        if(schemasLen >0) {
            for(i=0; i<schemasLen; i++) {
                baseLayer = schemas[i];
                baseLayerArray[baseLayer.id()] = baseLayer;

                if(baseLayer.id() == currentBaseLayer.id())
                    selected = "selected";
                else
                    selected = "";

                $("#baseLayerContainer>.schemasContainer>select").append('<option value="'+baseLayer.id()+'" '+selected+'>'+baseLayer.name()+'</option>');
            }
        }
        if(spacesLen >0) {
            for(i=0;i<spacesLen;i++) {
                baseLayer = spaces[i];
                baseLayerArray[baseLayer.id()] = baseLayer;

                if(baseLayer.id() == currentBaseLayer.id())
                    selected = "selected";
                else
                    selected = "";

                $("#baseLayerContainer>.spacesContainer>select").append('<option value="'+baseLayer.id()+'" '+selected+'>'+baseLayer.name()+'</option>');
            }
        }
        // Поключаемся к событию "change" у каждого селектора. Когда новый базовый слой выбран, устанавливаем карте.
        $(".selectBaseLayer").change(function(){
            var id = $(this).val(),
                baseLayer = baseLayerArray[id];

            if(typeof baseLayer != 'undefined') {
                mapObject.setBaseLayer(baseLayer);
                currentBaseLayer = baseLayer;
            }
            if($(this).parent("div").hasClass("schemasContainer")){
                $("#baseLayerContainer>.spacesContainer>select").children("option:first").attr("selected","selected");
            }
            else{
                $("#baseLayerContainer>.schemasContainer>select").children("option:first").attr("selected","selected");
            }

        });
        // end
        
        // ПОЛУЧАЕМ ВСЕ ДОСТУПНЫЕ ГРУППЫ СЛОЕВ. 
        // ПАРАМЕТР true ОЗНАЧАЕТ, ЧТО ВСЕ ГРУППЫ ВЕРНУТЬСЯ СО СЛОЯМИ.
        // callback(groups) – функция выполняется при получении групп слоев с сервера. Принимает на вход один параметр - массив экземпляров класса GeoPortal.LayerGroup.
        // callErrorBack(status,error) – функция, которая будет выполняться при ошибке во время запроса, на вход должна принимать статус и описание ошибки.
        GeoPortal.requestGroups(true,
            function(groups) {
                // Получаем массив слоев, чтобы запомнить в глобальную переменную agriculturalLayer рабочий слой полей.
                var layers = groups[0].layers();
                agriculturalLayer = layers[0];
                
		EnableWorkingLayer(agriculturalLayer);                

                // Если группы слоев существуют, то оформляем графически КОНТЕНТ ПРАВОЙ ПАНЕЛИ с группами.
                if(groups.length >= 1) {
                    var i;
                    groups_count = (groups.length+1) * 30;
                    var groupsHeight = $("#map").height() - 150;
                    
                    for ( i=0; i<groups.length; i++ ) {
                        drawGroup(groups[i]);
                    }
                    
                    // Создаем элемент div[id=groups].
                    
                    
                    $("#groups").height(groupsHeight);
                    // Changing DEFAULT Settings for jQuery accordion
                    $("#groups").accordion();
                    $(".ui-accordion-content").height(groupsHeight - groups_count);
                    $(".ui-corner-all, .ui-corner-bottom, .ui-corner-right, .ui-corner-br").css({"border-bottom-right-radius":"0px", "border-bottom-left-radius":"0px"});
                    $(".ui-widget-content").css({"border":"0px solid #aaaaaa", "color":"#000000"});
                    $(".ui-accordion .ui-accordion-header").css({"display":"block", "cursor":"pointer", "position":"relative", "margin":"0px 0px 1px 0px", "padding":"8px 0px 0px 30px", "height":"26px", "min-height":"26px", "max-height":"26px", "font":"bold 10pt sans-serif", "font-style":"normal", "font-variant":"normal", "font-stretch":"normal"});
                    $(".ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default").css({"color":"#FFFFFF", "background":"#9EB5BF", "border":"0px solid #D3D3D3"});
                    // background for active h3 div header
                    $(".ui-accordion-header-active").css({"color":"#FFFFFF", "background":"#6D8692", "border":"0px solid #6D8692"});
                    $(".ui-accordion-header").click(function(){
                        $(".ui-accordion-header").css({"color":"#FFFFFF", "background":"#9EB5BF", "border":"0px solid #6D8692"});
                        $(".ui-accordion-header-active").css({"color":"#FFFFFF", "background":"#6D8692", "border":"0px solid #6D8692"});
                    });
                    // radius for h3 div header
                    $(".ui-corner-all, .ui-corner-top, .ui-corner-left, .ui-corner-tl").css({"border-top-left-radius":"0px"});
                    $(".ui-corner-all, .ui-corner-top, .ui-corner-right, .ui-corner-tr").css({"border-top-right-radius":"0px"});
                    $(".ui-corner-all, .ui-corner-bottom, .ui-corner-left, .ui-corner-tl").css({"border-bottom-left-radius":"0px"});
                    $(".ui-corner-all, .ui-corner-bottom, .ui-corner-right, .ui-corner-tr").css({"border-bottom-right-radius":"0px"});
                    // accordion content
                    $(".ui-accordion .ui-accordion-content").css({"padding":"0", "color":"#000000", "font":"normal 10pt sans-serif"});
                }
                
                // Оформляем САМУ ПРАВУЮ ПАНЕЛЬ с группами слоев.
                var groupsDiv = $("#groups");
                groupsDiv.before('<div id="roll-up"><p>ОБЩИЕ СЛОИ</p></div>');
                groupsDiv.addClass('groups-hide');
                // Сворачиваем и разворачиваем правую панель со слоями.
                $("#roll-up").click(function(){
                    if ( groupsDiv.hasClass('groups-hide') == true )
                    {
                        groupsDiv.removeClass('groups-hide');
                        groupsDiv.addClass('groups-show');
                        groupsDiv.slideUp(500);
                        $(this).css({"opacity":"0.5", "-webkit-opacity":"0.5", "-moz-opacity":"0.5", "-o-opacity":"0.5"});
                    }
                    else
                    {
                        groupsDiv.removeClass('groups-show');
                        groupsDiv.addClass('groups-hide');
                        groupsDiv.slideDown(500);
                        $(this).css({"opacity":"1", "-webkit-opacity":"1", "-moz-opacity":"1", "-o-opacity":"1"});
                    }
                });
                
                // ОТРАБАТЫВАЕМ СОБЫТИЯ, СВЯЗАННЫЕ С ОБЩИМИ СЛОЯМИ.
                $("#groups").find(".layer").find("input").on("click",function() { 
                    var id = $(this).val(), 
                    layer = layersStore[id]; 
                    // ВКЛЮЧАЕМ КАРТИНКУ ПЕРЕЛЕТА К СЛОЮ И ОТРАБАТЫВАЕМ САМ ПЕРЕЛЕТ.
                    if ( document.getElementById('checkbox-layer-' + id).checked == true ) 
                    { 
                        $("#checkbox-layer-" + id).after('<span class="download-layer" id="download-layer-' + id + '"><img src="public/images/download-layer.png" title="Скачать слой" /></span>');
			$("#checkbox-layer-" + id).after('<span class="fly" id="fly-' + id + '"><img src="public/images/fly-to-layer.png" title="Перелет к слою" /></span>'); 
                        
                        $("#fly-" + id).click(function(){ 
                            layer.requestBbox( 
                                function(bbox){ 
                                    mapObject.fitBounds(bbox.minx, bbox.miny, bbox.maxx, bbox.maxy);
                                }, 
                                function(status, error){ 
                                    console.log(error); 
                                }); 
                        });
			$("#download-layer-" + id).click(function(){
                            var layer_name = layer.name();

                            if ( layer._model._values.info.style == "raster" )
                            {
                                /*var bboxString;
                                layer.requestBbox( 
                                    function(bbox){ 
                                        console.log(bbox.minx+","+bbox.miny+","+bbox.maxx+","+bbox.maxy);
                                        bboxString = bbox.minx+","+bbox.miny+","+bbox.maxx+","+bbox.maxy;
                                    }, 
                                    function(status, error){ 
                                        console.log(error); 
                                });*/
                                var url = "http://"+hostName+"/geoserver/workspace/wms/kml?"+
                                          //"service=WMS&"+
                                          //"version=1.1.0&"+
                                          //"request=GetMap&"+
                                          "layers="+layer_name;//+"&"+
                                          //"styles="+"&"+
                                          //"bbox="+bboxString+"&"+
                                          //"width=auto&"+
                                          //"height=auto&"+
                                          //"srs=EPSG:32646&"+
                                          //"format=image/geotiff";
                                          
                                        //  http://krasnoyarsk-geomonitoring.ikit.sfu-kras.ru/geoserver/workspace/wms?layers=workspace:WV2_20140625_I02_SEG04_GO_NC
                                 
                            }
                            else
                            {
                                var url = "http://"+hostName+"/geoserver/workspace/ows?"+
                                          "service=WFS&"+
                                          "version=1.1.0&"+
                                          "request=GetFeature&"+
                                          "typeName="+layer_name+"&"+
                                          "outputFormat=SHAPE-ZIP&"+
                                          "format_options=charset:cp1251";
                            }
                            
                            window.open(url, '_blank');
                        }); 
                    } 
                    else 
                    { 
                        $("#fly-" + id).remove();
			$("#download-layer-" + id).remove(); 
                    } 

                    if(typeof layer != 'undefined') { 
                        layer.turn(mapObject); 
                    } 
                });
                // ОТОБРАЖАЕМ КАРТИНКУ ИНФОРМАЦИИ О СЛОЕ И ОТРАБАТЫВАЕМ ВКЛЮЧЕНИЕ ЛЕГЕНДЫ СЛОЯ.
                $(".info").click(function(){ 
                    $("#layer-legend").remove(); 

                    var id = this.id, 
                    layer = layersStore[id], 
                    legend, 
                    userPanelDiv = $("#user-panel"), 
                    rollUserPanelDiv = $(".roll-user-panel"); 
                    legend = layer.legend(); 

                    userPanelDiv.slideDown(500); 
                    userPanelDiv.attr('class', 'up-hide'); 
                    rollUserPanelDiv.css({"opacity":"1", "-webkit-opacity":"1", "-moz-opacity":"1", "-o-opacity":"1"});
                    // Делаем активной нужную вкладку таба.
                    $.ionTabs.setTab("Tabs_Group_name", "Tab_4_name");
                    
                    /* Альтернативный способ сделать активной нужную вкладку.
                    $('li[id="Button__Tabs_Group_name__Tab_1_name"]').attr('class', 'ionTabs__tab');
                    $('li[id="Button__Tabs_Group_name__Tab_2_name"]').attr('class', 'ionTabs__tab');
                    $('li[id="Button__Tabs_Group_name__Tab_3_name"]').attr('class', 'ionTabs__tab');
                    
                    $('div[id="Tab__Tabs_Group_name__Tab_1_name"]').attr('class', 'ionTabs__item');
                    $('div[id="Tab__Tabs_Group_name__Tab_2_name"]').attr('class', 'ionTabs__item');
                    $('div[id="Tab__Tabs_Group_name__Tab_3_name"]').attr('class', 'ionTabs__item');
                    // Делаем активной нужную вкладку таба.
                    $('li[id="Button__Tabs_Group_name__Tab_4_name"]').attr('class', 'ionTabs__tab ionTabs__tab_state_active');
                    $('div[id="Tab__Tabs_Group_name__Tab_4_name"]').attr('class', 'ionTabs__item ionTabs__item_state_active');
                    */
                    
                    $(".zoom-control").css({"left":zoomControlShift}); 
                    $("#my-contours").after('<form id="layer-legend"><fieldset><legend>Легенда</legend><div class="legend"><img src="' + legend + '" /></div></fieldset></form>'); 
                });
                
            },
            
            function(status,error) {
                console.log(status);
                console.log(error);
            }
        );
        
        function drawGroup(group) { 

            if(typeof group == "undefined") 
                return; 

            var groupsDiv = $("#groups"), 
            groupDiv, layersDiv, layers, key; 

            groupsDiv.append('<h3>' + group.name() + '</h3>' + 
                    '<div><div class="group"><div class="layers"></div></div></div>'); 
            groupDiv = groupsDiv.last(); 
            layersDiv = groupDiv.find(".layers:last"); 
            layers = group.layers(); 

            for(key in layers) { 

                layersStore[layers[key].id()] = layers[key]; 

                var layerName = layers[key].rusName(); 
                
		shortLayerName = GetShortLayerName(layerName); 

                layersDiv.append('<div class="layer"><input type="checkbox" class="checkbox-layer" id="checkbox-layer-'+ layers[key].id() +'" value="'+ layers[key].id() +'">' + '<span title="' + layerName + '">' + shortLayerName + '</span>' + '<span class="info" id="' + layers[key].id() + '"><img src="public/images/info-icon.png" title="Информация о слое"></span></div>');
	    } 
        };
        
        
        
        // Вывод popup окошка при клике на объект слоя
        mapObject.on("popupclose",
            function(data) {
                if (typeof marker != 'undefined') {
                    mapObject.removeLayer(marker);
                    marker = undefined;
                }
        },this);

        mapObject.on("click",function(e) {
            clickLatLng = e.latlng;
        },this);
        
        
        mapObject.on("featureClicked",function(data) {
            
            if (typeof marker != 'undefined') {
                mapObject.removeLayer(marker);
                marker = undefined;
            }

            if(typeof data.features == 'undefined') {
                console.log("Request features error. Status = " + status + ". Error text: " + error);
                return;
            }

            var features = data.features;

            // закраска объекта
            var objGeometry = JSON.parse(features[0]._model._values.data.geom);
            //console.log(objGeometry);
            geoJson = objGeometry; 
            var layer1 = new GeoPortal.Layer.GeoJSON(
                geoJson,{color: '#32A4D2', editable:true,fillOpacity:0.4} //#32A4D2
            );
            selectorLayer.push(layer1);
            //alert(selectorLayer.length);
            mapObject.addLayer(layer1);
            
            // убираем выделение объекта одинарным щелчком мыши по нему
            layer1.on("click",function(e) {
                // когда делаем деселект контура, то убираем ID этого контура из строки idsString
                mapObject.removeLayer(layer1);
                idsString = idsString.replace(new RegExp(objFID.toString()+',','g'),"");
		if ( idsString == "" ) {
                    dwnld_usgs_params = undefined;
                }
                someString = someString.replace(new RegExp(objFID.toString(),'g'),"");
            },this);
            
           
            var objFID = JSON.parse(features[0]._model._values.data.fid);
            //alert(objFID);
            // строка ID выбранных контуров полей
            idsString += objFID + ',';
            // debug
            //alert(idsString);
            
            var objGeom = JSON.parse(features[0]._model._values.data.geom);
            
            var _object_bbox = GetBbox(objGeom);
            
            someString += "|fid:"+objFID+", minLat:"+_object_bbox['minLat']+", minLng:"+_object_bbox['minLng']+", maxLat:"+_object_bbox['maxLat']+", maxLng:"+_object_bbox['maxLng']+"|";
		
		dwnld_usgs_params = CompareCoordinatesOfContoursBbox(_object_bbox, _compare_bbox);
            
            // При клике по объекту, имеющему координаты, выполняем следующие действия:
            if(clickLatLng instanceof GeoPortal.LatLng) {
                // убираем элемент object-info в левой панеле, если оно было создано,
                $("#object-info-form").remove();
                // разворачиваем левую панель,
                var userPanel = $("#user-panel");
                rollUserPanelDiv = $(".roll-user-panel");
                userPanel.slideDown(500); 
                userPanel.attr('class', 'up-hide'); 
                rollUserPanelDiv.css({"opacity":"1", "-webkit-opacity":"1", "-moz-opacity":"1", "-o-opacity":"1"});
                // Делаем активной нужную вкладку таба.
                $.ionTabs.setTab("Tabs_Group_name", "Tab_2_name");
                // Перемещаем контрол zoom в видимую область.
                $(".zoom-control").css({"left":zoomControlShift});
                // получаем все открытые слои,
                var openedLayers = mapObject.layers();
                // преобразуем слои в массив,
                var layersArray = openedLayers.getArray();
                
                //marker = new GeoPortal.Marker(clickLatLng);
                //mapObject.addLayer(marker);
                //marker.setPopup('ANY HTML');
            
                // выводим информацию о выбранных объектах, создаем элемент object-info в левой панеле.
                if ( layersArray.length > 0 ) {
                    var objectInfoHTML = '';
                    
                    objectInfoHTML += '<div id="object-info-form">';
                    var upperLayer = layersArray.length - 2;
                    for( var i=0; i<layersArray.length; i++ ) 
                    {
                        // ЭТО УСЛОВИЕ НЕОБХОДИМО, ЧТОБЫ ОТОБРАЖАЛАСЬ И ОБНОВЛЯЛАСЬ ИНФОРМАЦИЯ О КАЖДОМ ИЗ ВКЛЮЧЕНЫЫХ КОНТУРОВ ОБЪЕКТОВ, 
                        // Т.К. layersArray СОДЕРЖИТ НЕ ТОЛЬКО ОСНОВНЫЕ ВКЛЮЧЕННЫЕ СЛОИ, НО И КОНТУРЫ ДОБАВЛЕННЫХ ОБЪЕКТОВ ПОНИМАЕТ КАК СЛОИ. НО ЭТИ КОНТУРЫ НЕ ИМЕЮТ СВОЙСТВА _model И У НИХ НЕТ ID, ЧТО МОЖЕТ ПРИВЕСТИ К ОШИБКЕ.
                        if ( "_model" in layersArray[i] )
                        {
                            //lastLayer = layersArray[i];
                            // Получаем ID открытых слоев.
                            var currentLayerID = layersArray[i]._model._pkValue;
                            // Получаем поля БД каждого из открытых слоев.
                            var currentLayerFields = layersStore[currentLayerID].fields();
                            attribsArray['table'] = layersStore[currentLayerID]._model._values.info.typeName;
                            objectInfoHTML += '<p style="text-decoration:underline; color:#2C87D2; font-size:12pt; font-weight:normal; letter-spacing:0.5px; cursor:pointer;"><b>' + layersStore[currentLayerID].rusName() + '</b></p>';
                            for ( var j=0; j<currentLayerFields.length; j++ )
                            {
                                if ( currentLayerFields[j].nameRu != '' )
                                {
                                    // Получаем имя свойства на латинице.
                                    nameLat = currentLayerFields[j].name;
                                    //alert(features[i]._model._values.data[nameLat]);
                                    if ( features[i]._model._values.data[nameLat] != undefined )
                                    {
                                        objectInfoHTML += '<p id="'+nameLat+'" style="font-size:9pt;"><b>' + currentLayerFields[j].nameRu + ':</b> ';
                                        objectInfoHTML += '' + features[i]._model._values.data[nameLat] + '</p>';
                                    }
                                    
                                    //console.log('Подставляемое свойство: '+nameLat);
                                    //console.log(currentLayerFields[j].nameRu+': '+features[0]._model._values.data[nameLat]);
                                }
                            }
                            attribsArray['fid'] = features[i]._model._values.data['fid'];
                            if ( role == 8 )
                            {
                                objectInfoHTML += '<button type="button" class="change" onclick="EditAttribs()" id="attrib-change">Изменить</button>';
                            }
                        }
                    }
                    objectInfoHTML += '</div>';
                    $("div[data-name='Tab_1_name']").prepend(objectInfoHTML);
                }
            }
        },this);
        // end
        
    },this);
    // END Geoportal ready
});
</script>
<script src="/public/js/activemap-index.js" type="text/javascript"></script>

</head>

<body>

<div class="container">
    <div class="navbar navbar-fixed-top navbar-inverse">
        <div class="navbar-inner">
            <div class="nav-collapse">
                 <ul class="nav">
                    <li class="active">
                        <a class="brand" style="margin:0px;" href="/">
                           Система агромониторинга
                        </a>
                    </li>
                    <? if ( isset($_SESSION['idRole']) && $_SESSION['idRole'] == 8 ) { ?>
                    <li class="dropdown">
                        <a style="cursor: pointer;" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="/public/images/dictionary.png" alt="Справочники" title="Справочники" style="width:24px; height:18px;">
                            <b class="caret"></b>
                        </a>
                     
                        <ul class="dropdown-menu" style="width: 235px;">
                            <li class="nav-header">Управление справочниками
                            <ul style="list-style: none;">
                                <li><a href="<?=$_SERVER['REMOTE_SERVER']?>/dictionaries/management/index.php">Главная</a></li>
                                <li><a href="<?=$_SERVER['REMOTE_SERVER']?>/dictionaries/management/index.php?render=jqCultures">С/Х Культуры</a></li>
                                <li><a href="<?=$_SERVER['REMOTE_SERVER']?>/dictionaries/management/index.php?render=jqAgriculturalWorks">Виды сельскохозяйственных работ</a></li>
                                <li><a href="<?=$_SERVER['REMOTE_SERVER']?>/dictionaries/management/index.php?render=jqChemcomposition">Химический состав почв</a></li>
                                <li><a href="<?=$_SERVER['REMOTE_SERVER']?>/dictionaries/management/index.php?render=jqOwnership">Формы собственности</a></li>
                                <li><a href="<?=$_SERVER['REMOTE_SERVER']?>/dictionaries/management/index.php?render=jqFarmers">Хозяйства</a></li>
                            </ul>
                            </li>
                        </ul>
                    </li>
					<li class="divider-vertical"></li>
                    <li class="dropdown">
                        <a style="cursor: pointer;" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="/public/images/stat.png" alt="Статистика" title="Статистика" style="height: 24px; width: 24px; margin: -6px 0px -3px 0px;">
                            <b class="caret"></b>
                        </a>
                     
                        <ul class="dropdown-menu" style="width: 235px;">
                            <li class="nav-header">Статистика
                            <ul style="list-style: none;">
                                <li><a href="<?=$_SERVER['REMOTE_SERVER']?>/Modules/Logs/View/index.php">Входы пользователей</a></li>
                            </ul>
                            </li>
                        </ul>
                    </li>
					<li class="divider-vertical"></li>
                    <li>
                        <div class="usgsDwnld" onclick="DownloadImageUSGS()">
                            <img src="/public/images/usgs.png" alt="Загрузить снимок" title="Загрузить снимок" style="margin-left: 12px; margin-right: 12px;">
                        </div>
                    </li>
                    <? } ?>
                    <li class="divider-vertical"></li>
                    <li>
                        <div class="printMap">
                            <img src="/public/images/print.png" alt="Печать карты" title="Печать карты" style="margin-left: 12px; margin-right: 12px;">
                        </div>
                    </li>
                    <li class="divider-vertical"></li>
                    <li>
                         <div class="plus">
                             <label title="Модальное окно публикации" for="modal-2"><img src="/public/images/plus.png" alt="Публикация слоя" title="Публикация слоя" style="margin-left: 12px; margin-right: 12px;"></label>
                         </div>
                    </li>
                    <li class="divider-vertical"></li>
                    <div class="modal2">
				        <input class="modal-open" id="modal-2" type="checkbox" hidden>
                        <div class="modal-wrap" aria-hidden="true" role="dialog">
                            <label class="modal-overlay" for="modal-2"></label>
                            <div class="modal-dialog">
                                <div class="modal-header">
                                    <h2>Публикация слоя</h2>
                                </div>
                                <div id="mod" class="modal-body">
                                    <form id="pub" name="publication" action="curl/public_layers.php" method="post" enctype='multipart/form-data'>
                                        <div>
                                        <div style="position:relative;margin-bottom: 2%; margin-left: 1%;">
                                            <a class='btn btn-primary' href='javascript:;'>
                                                SHP-файл
                                                <input type="file" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' id="shp" name="shp-file" accept=".shp" required size="40"  onchange='$("#upload-shp-info").html($(this).val());'>
                                            </a>
                                            &nbsp;
                                            <span name="shp_span" class='label label-info' id="upload-shp-info"></span>
                                        </div>
                                        
                                        <div style="position:relative;margin-bottom: 2%; margin-left: 1%;">
                                            <a class='btn btn-primary' href='javascript:;'>
                                                PRJ-файл 
                                                <input type="file" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' id="prj" name="prj-file" accept=".prj" required size="40"  onchange='$("#upload-prj-info").html($(this).val());'>
                                            </a>
                                            &nbsp;
                                            <span class='label label-info' id="upload-prj-info"></span>
                                        </div>

                                        <div style="position:relative;margin-bottom: 2%; margin-left: 1%;">
                                            <a class='btn btn-primary' href='javascript:;'>
                                                DBF-файл
                                                <input type="file" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' id="dbf" name="dbf-file" accept=".dbf" required size="40"  onchange='$("#upload-dbf-info").html($(this).val());'>
                                            </a>
                                            &nbsp;
                                            <span class='label label-info' id="upload-dbf-info"></span>
                                        </div>

                                        <div style="position:relative;margin-bottom: 2%; margin-left: 1%;">
                                            <a class='btn btn-primary' href='javascript:;'>
                                                SHX-файл
                                                <input type="file" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' id="shx" name="shx-file" accept=".shx" required size="40"  onchange='$("#upload-shx-info").html($(this).val());'>
                                            </a>
                                            &nbsp;
                                            <span class='label label-info' id="upload-shx-info"></span>
                                        </div>
                                        </div>
                                        <input id="rus_name" name="rus_name" placeholder="Русское название" class="textbox" required pattern="^[-_\dА-Яа-яЁё\s]+$"/>
                                        <input id="eng_name" name="eng_name" placeholder="Английское название" class="textbox" required pattern="^[-_\da-zA-Z\s]+$"/>
                                        
                                        <h4>Цвет фона</h4>
                                        <input type="hidden" name="color_bgr" id="color_bgr" class="demo" >
                                        
                                        <h4>Цвет контура</h4>
                                        <input type="hidden" name="color_around" id="color_around" class="demo" >
                                        
                                        <h4>Группа</h4>
                                        <script>
                                            //groupList_of_layers.php
                                            var LIST = '<?php echo $list;?>';
                                            document.write(LIST);
                                        </script>
                                        <div>
                                            <input class="btn btn-success" type="submit" name="btnSubmit" value="Сохранить">
                                            <input class="btn" type="reset" name="resSubmit" value= "Очистить">
                                         </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                     </div>
                </ul>
                <? if ( isset($_SESSION['uLogin']) && !empty($_SESSION['uLogin']) ) { ?>
                    <div id="hello-box">
                        <p class="hello-user"></p>
                    </div>
                    <div class="menu-authorization-exit">
                        <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
                            <input type="submit" name="auth-exit" value="  Выйти">
                        </form>
                    </div>
                <? } else { ?>
                    <div class="menu-authorization">
                        <div></div>
                    </div>
                <? } ?>
                
            </div>
        </div>
    </div>
    <!--  АВТОРИЗАЦИЯ  -->
    <? if ( empty($_SESSION['uLogin']) ) { ?>
    <div id="authorization-box">
        <p class="authorization-title">Авторизация</p>
        <div class="authorization-form">
            <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
                <table>
                <tr>
                    <td align="right" style="width: 100px;"><p class="authorization-login">Логин:</p></td>
                    <td align="right"><input class="authorization-login-element" type="text" id="login" name="login"></td>
                </tr>
                <tr>
                    <td align="right"><p class="authorization-password">Пароль:</p></td>
                    <td align="right"><input class="authorization-password-element" type="password" id="password" name="password"></td>
                </tr>
                </table>
                <div class="button-block">
                    <input type="submit" id="authorization-enter" name="auth" value="Вход">
                    <button type="button" id="authorization-cancel">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    <? } ?>
    <!--  END  -->
</div>
