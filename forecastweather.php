<?php
//This is the right code base for GIT
$alert = $location = $table = $card = "";
$card_array = array();
$card_summary_html = "";
$table_icons = array(
  "clear-day"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-12-512.png",
  "rain"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-04-512.png",
  "snow"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-19-512.png",
  "sleet"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-07-512.png",
  "wind"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-27-512.png",
  "fog"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-28-512.png",
  "cloudy"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-01-512.png",
  "partly-cloudy-day"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-02-512.png",
  "clear-night"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-12-512.png",
  "partly-cloudy-night"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-02-512.png"
);
$card_icons = array(
  "Humidity"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-16-512.png",
  "Pressure"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-25-512.png",
  "Wind Speed"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-27-512.png",
  "Visibility"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-30-512.png",
  "CloudCover"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-28-512.png",
  "Ozone"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-24-512.png"
);
$forecast_json = null;
 if($_SERVER["REQUEST_METHOD"] == "POST"){
   if(!empty($_POST["myloc"])){
     $tmp_arr = get_ip_loc();
     $table = $tmp_arr[0];
     $card = $tmp_arr[1];
   }
   else if((!isset($_POST["street_name"]) || empty($_POST["street_name"])) ||
   (!isset($_POST["city_name"]) || empty($_POST["city_name"])) || (!isset($_POST["state_name"]) || $_POST["state_name"] == "State")){
     $alert = "Please check the input address.";
   }
   else{
     $street = $_POST["street_name"];
     $city = $_POST["city_name"];
     $state = $_POST["state_name"];
     $tmp_arr = get_xml($street, $city, $state);
     $table = $tmp_arr[0];
     $card = $tmp_arr[1];
   }
 }

 function get_card_summary($latitude, $longitude, $time){
   $request = "https://api.darksky.net/forecast/".$DARKSKY_API.'/'.$latitude.",".$longitude.",".$time."?exclude=minutely";
   $json_obj = json_decode(file_get_contents($request));
   return $json_obj;
 }

 function get_ip_loc(){
   $json = json_decode(file_get_contents("http://ip-api.com/json/".$_SERVER['REMOTE_ADDR']));
   $forecast_json = get_weather_info($json->lat, $json->lon);
   $tmp_arr = array(create_table($forecast_json), create_card($forecast_json, $json->city));
  return $tmp_arr;
}

function get_xml($street, $city, $state){
  $request = "https://maps.googleapis.com/maps/api/geocode/xml?address=".urlencode($street.",".$city.",".$state)."&key=AIzaSyBgYljUf7Goyzt1iRVSqm7QxNcuvraFnjI";
  $xml = simplexml_load_string(file_get_contents($request));
  $forecast_json = get_weather_info($xml->result[0]->geometry->location->lat, $xml->result[0]->geometry->location->lng);
  $tmp_arr = array(create_table($forecast_json), create_card($forecast_json, $city));
  return $tmp_arr;
 }

 function get_weather_info($latitude, $longitude){
   $request = "https://api.forecast.io/forecast/".$DARKSKY_API.'/'.$latitude.",".$longitude."?exclude=minutely,hourly,alerts,flags";
   $json = json_decode(file_get_contents($request));
   return $json;
 }

 function create_card($forecast_json, $city){
   global $card_icons;
   $html = "";
   if($forecast_json != null){
     $html.="<p style='color:#fff; font-size:32px; top:15px; position:relative;'><b>".$city;
     $html.="</b></p>";
     $html.="<p style='color:#fff; position:relative; top:-15px;'>".$forecast_json->timezone."</p><br>";
     $html.="<br>";
     $html.="<ul class='temperature'>";
     $html.="<li style='font-size:110px;font-weight:600;'>".$forecast_json->currently->temperature;
     $html.="<img id='temp_img_card' src='https://cdn3.iconfinder.com/data/icons/virtual-notebook/16/button_shape_oval-512.png'/></li>";
     $html.="<li style='font-size:64px; font-weight:600; margin-left:-5px;'> F </li>";
     $html.="</ul>";
     $html.="<p style='color:#fff; font-size:35px; font-style:bold; position:relative; top: -120px; font-weight:700;'>".$forecast_json->currently->summary."</p>";
     $html.="<ul id='card_icons_ul'>";
     foreach ($card_icons as $key=>$i){
       if($key != "Ozone"){
         $html.="<li style='margin-right:70px;'><img style='width: 30px; height:auto;'src='".$i."' title='".$key."'/></li>";
       }
       else{
         $html.="<li style='margin-right:7px;'><img style='width: 30px; height:auto;'src='".$i."' title='".$key."'/></li>";
       }
     }
     $html .= "<div id='value' style='margin-right: 1px;'>";
     if((isset($forecast_json->currently->humidity) && $forecast_json->currently->humidity != 0) || !empty($forecast_json->currently->humidity) || $forecast_json->currently->humidity != 0){
        $html.="<p style='font-size:22px; color:#fff; font-weight:600;'>".$forecast_json->currently->humidity."</p></div>";
     }else{$html.="<p></p></div>";}
     $html .= "<div  id='value' style='margin-right: 33px;'>";
     if((isset($forecast_json->currently->pressure) && $forecast_json->currently->pressure != 0) || !empty($forecast_json->currently->pressure) || $forecast_json->currently->pressure != 0){
        $html.="<p style='font-size:22px; color:#fff; font-weight:600;'>".$forecast_json->currently->pressure."</p></div>";
     }else{$html.="<p></p></div>";}
     $html .= "<div id='value' style='margin-right:30px;'>";
     if((isset($forecast_json->currently->windSpeed) && $forecast_json->currently->windSpeed != 0) || $forecast_json->currently->windSpeed != 0){
        $html.="<p style='font-size:22px; color:#fff; font-weight:600;'>".$forecast_json->currently->windSpeed."</p></div>";
     }else{$html.="<p></p></div>";}
     $html .= "<div id='value' style='margin-right:12px;'>";
     if((isset($forecast_json->currently->visibility) && $forecast_json->currently->visibility != 0) || !empty($forecast_json->currently->visibility) || $forecast_json->currently->visibility != 0){
        $html.="<p style='font-size:22px; color:#fff; font-weight:600;'>".$forecast_json->currently->visibility."</p></div>";
     }else{$html.="<p></p></div>";}
     $html .= "<div id='value' style='margin-right:8px;'>";
     if((isset($forecast_json->currently->cloudCover) && $forecast_json->currently->cloudCover != 0) || !empty($forecast_json->currently->cloudCover)){
        $html.="<p style='font-size:22px; color:#fff; font-weight:600;'>".$forecast_json->currently->cloudCover."</p></div>";
     }else{$html.="<p></p></div>";}
     $html .= "<div id='value' style='margin-right:0px; width:60px;'>";
     if((isset($forecast_json->currently->ozone) && $forecast_json->currently->ozone != 0) || !empty($forecast_json->currently->ozone) || $forecast_json->currently->ozone != 0){
        $html.="<p style='font-size:22px; color:#fff; font-weight:600; margin-left:13px;'>".round($forecast_json->currently->ozone, 1)."</p></div>";
     }else{$html.="<p></p></div>";}
   }
   return $html;
 }

 function pick_table_icon($case){
   global $table_icons;
   return $table_icons[$case];
 }
 function create_table($forecast_json){
   global $card_array;
   $html = "";
   if($forecast_json != null){
     $html = "<table style='width:1000px;'><tr><th>Date</th><th>Status</th><th>Summary</th><th>TemperatureHigh</th><th>TemperatureLow</th><th>Wind Speed</th></tr>";
     $lat = $forecast_json->latitude;
     $lon = $forecast_json->longitude;
     for($i = 0; $i < count($forecast_json->daily->data); $i++){
       $summary_time = $forecast_json->daily->data[$i]->time;
       $date = new DateTime();
       $date->setTimestamp($forecast_json->daily->data[$i]->time);
       $html.="<tr><td>".$date->format("Y-m-d")."</td>";
       $img_src = pick_table_icon($forecast_json->daily->data[$i]->icon);
       $html.="<td><img src='".$img_src."' style='width:30px;height=auto;'/></td>";
       $daily_content_json = get_card_summary($lat, $lon, $summary_time);
       $card_array[$i] = $daily_content_json;
       $html.="<td style='width:250px; cursor:pointer;' onclick='get_card_summaryJS($i)'>".$forecast_json->daily->data[$i]->summary."</td>";
       $html.="<td style='width:135px'>".$forecast_json->daily->data[$i]->temperatureHigh."</td>";
       $html.="<td style='width:135px'>".$forecast_json->daily->data[$i]->temperatureLow."</td>";
       $html.="<td style='width:90px'>".$forecast_json->daily->data[$i]->windSpeed."</td>";
       $html.="</tr>";
     }
     $html.="</table>";
   }
   return $html;
 }
 ?>

 <!DOCTYPE html>
 <html lang="en" dir="ltr">
   <head>
     <meta charset="utf-8">
     <title></title>
     <style type="text/css">
       #value{
         display: inline-block;
         top:-20px;
         position: relative;
         width:80px;
       }

       #card_icons_val{
         text-decoration: none;
         list-style: none;
         position: relative;
         margin-left:-40px;
       }

       #card_icons_val li{
         display: inline-block;;
         margin-right:50px;
         font-size:22px;
         font-weight:600;
         color:#fff;
       }

       #card_icons_ul{
         text-decoration: none;
         list-style: none;
         margin-left: -40px;
         position: relative;
         top: -140px;
       }

       #card_icons_ul li{
         display: inline-block;
         margin-right:50px;
       }
       #temp_img_card{
         width:11px;
         height: auto;
         position: relative;
         top: -70px;
       }
       h1{
         text-align: center;
       }
       #city{
         margin-left: 13px;
       }
       #user_form{
         margin-left: 75px;
         top:-25px;
         position: relative;
       }
       .form_container{
         width: 840px;
         height: 250px;
         background-color: #009900;
         margin-left: 330px;
         border-radius: 8px;
       }

       h1, label{
         color: #fff;
       }

       label{
         font-size: 18px;
       }

       .vl{
         height: 120px;
         border-left: 6px solid #fff;
         margin-left: 460px;
         border-radius: 1px;
         margin-top: -98px;
         position: absolute;
       }

       #state{
         width: 240px;
       }

       .submit_clear{
         margin-top: 70px;
         margin-left: 250px;
         position: absolute;
       }

       #search, #clear{
         border-radius: 4px;
       }

       #location{
         margin-left: 550px;
         position: absolute;
         margin-top: -65px;
       }

       #street, #city{
         margin-bottom: 5px;
       }

       .hasError{
        border: 2px grey solid;
        width:500px;
        height:auto;
        text-align: center;
        margin-left: 500px;
        margin-top: 30px;
        background-color: #D0D0D0;
       }

       table, th, td{
         border-collapse: collapse;
         border-spacing: 1px;
         border-style: solid;
         border-width: 2px;
       }

       table{
         background-color: blue;
       }

       td, th{
         background-color: #9fc9ee;
         color: #ffff;
         font-style: bold;
         text-align: center;
         border-color: #69acd3;
       }

       #tableContainer{
         margin-left:260px;
       }

       #cardContainer{
         width:550px;
         background-color:#33cfff;
         border-radius: 12px;
         padding-left: 20px;
         margin-left:460px;
         height: 330px;
         margin-bottom:35px;
       }

       ul.temperature{
         text-decoration: none;
         list-style: none;
         margin-left:-40px;
         position: relative;
         top: -80px;
       }

       ul.temperature li{
         display: inline-block;
         color: #fff;
         margin-right:20px;
         font-style: bold;
       }

       #detail_header, #detail_header2{
         margin-left: 550px;
         font-size: 42px;
       }

       #card_summary{
         width:600px;
         background-color: :#a2d9ce;
         border-radius: 4px;
         padding-left:15px;
       }

       #temp_summary{
         text-decoration: none;
         list-style: none;
         top:-220px;
         position: relative;
         margin-left: -38px;
       }

       #temp_summary li{
         display: inline-block;
         color: #fff;
         margin-right:8px;
         font-weight: 600;
       }

      #temp_img_summary{
        width:11px;
        height: auto;
        position: relative;
        top:-53px;
      }

      #card_stats{
        width: 300px;
        height:150px;
        position: relative;
        top: -220px;
        margin-left:280px;
      }

      #card_stats p{
        font-size: 20px;
        margin-bottom:-25px;
        font-weight: 600;
      }

      #arrow_img{
        width: 55px;
        height: auto;
        cursor:pointer;
      }

      #chart_div{
        margin-left:350px;
      }

     </style>
   </head>
   <body id="doc">
     <div class="form_container">
       <h1 style="font-size:40px;"><I>Weather Search</I></h1>
       <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" id="user_form">
         <label for="street"><b>Street</b></label>
         <input type="text" name="street_name" id="street" size="18" value='<?php echo isset($_POST["street_name"]) ? $_POST["street_name"]:""; ?>'><br>
         <label for="city"><b>City</b></label>
         <input type="text" name="city_name" id="city" size="18" value='<?php echo isset($_POST["city_name"]) ? $_POST["city_name"]:""; ?>'><br>
         <label for="State" value="State"><b>State</b></label>
         <select id="state" name="state_name">
           <option <?php echo !isset($_POST["state"]) ? 'selected':'';?>>State</option>
         </select>
         <div class="submit_clear">
           <input type="submit" name="search" value="search" id="search">
           <input type="button" name="clear" value="clear" onclick="clearForm()" id="clear">
         </div>
         <div id="location">
           <input type="checkbox" name="myloc" id="myloc_check">
           <label for="myloc"><b>Current Location</b></label>
         </div>
       </form>
       <div class="vl"></div>
     </div>
     <div class='<?php if($alert != ""){echo "hasError";}?>'><?php echo $alert;?></div>
     <div id='<?php if($card != ""){echo "cardContainer";} ?>'><?php echo $card; ?></div>
     <div id='<?php if($table != ""){echo "tableContainer";}?>'><?php echo $table;?></div>
     <div id="test"></div>
     <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
     <script type="text/javascript">
     var icons_arr = [];
     icons_arr["clear-day"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/sun-512.png";
     icons_arr["rain"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/rain-512.png";
     icons_arr["snow"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/snow-512.png";
     icons_arr["sleet"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/lightning-512.png";
     icons_arr["wind"] = "https://cdn4.iconfinder.com/data/icons/the-weather-is-nice-today/64/weather_10-512.png";
     icons_arr["fog"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/cloudy-512.png";
     icons_arr["cloudy"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/cloud-512.png";
     icons_arr["partly-cloudy-day"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/sunny-512.png";
     icons_arr["clear-night"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/sun-512.png";
     icons_arr["partly-cloudy-night"] = "https://cdn3.iconfinder.com/data/icons/weather-344/142/sunny-512.png";

     var arrow_down = "https://cdn4.iconfinder.com/data/icons/geosm-e-commerce/18/point-down-512.png";
     var arrow_up = "https://cdn0.iconfinder.com/data/icons/navigation-set-arrows-part-one/32/ExpandLess-512.png";
     var explicit = {};
       state = {
        "States":[
       {
       "Abbreviation":"AL",
       "State":"Alabama"
       },
       {
       "Abbreviation":"AK",
       "State":"Alaska"
       },
       {
       "Abbreviation":"AZ",
       "State":"Arizona"
       },
       {
       "Abbreviation":"AR",
       "State":"Arkansas"
       },
       {
       "Abbreviation":"CA",
       "State":"California"
       },
       {
       "Abbreviation":"CO",
       "State":"Colorado"
       },
       {
       "Abbreviation":"CT",
       "State":"Connecticut"
       },
       {
       "Abbreviation":"DE",
       "State":"Delaware"
       },
       {
       "Abbreviation":"DC",
       "State":"District Of Columbia"
       },
       {
       "Abbreviation":"FL",
       "State":"Florida"
       },
       {
       "Abbreviation":"GA",
       "State":"Georgia"
       },
       {
       "Abbreviation":"HI",
       "State":"Hawaii"
       },
       {
       "Abbreviation":"ID",
       "State":"Idaho"
       },
       {
       "Abbreviation":"IL",
       "State":"Illinois"
       },
       {
       "Abbreviation":"IN",
       "State":"Indiana"
       },
       {
       "Abbreviation":"IA",
       "State":"Iowa"
       },
       {
       "Abbreviation":"KS",
       "State":"Kansas"
       },
       {
       "Abbreviation":"KY",
       "State":"Kentucky"
       },
       {
       "Abbreviation":"LA",
       "State":"Louisiana"
       },
       {
       "Abbreviation":"ME",
       "State":"Maine"
       },
       {
       "Abbreviation":"MD",
       "State":"Maryland"
       },
       {
       "Abbreviation":"MA",
       "State":"Massachusetts"
       },
       {
       "Abbreviation":"MI",
       "State":"Michigan"
       },
       {
       "Abbreviation":"MN",
       "State":"Minnesota"
       },{
       "Abbreviation":"MS",
       "State":"Mississippi"
       },{
       "Abbreviation":"MO",
       "State":"Missouri"
       },{
       "Abbreviation":"MT",
       "State":"Montana"
       },{
       "Abbreviation":"NE",
       "State":"Nebraska"
       },{
       "Abbreviation":"NV",
       "State":"Nevada"
       },{
       "Abbreviation":"NH",
       "State":"New Hampshire"
       },{
       "Abbreviation":"NJ",
       "State":"New Jersey"
       },{
       "Abbreviation":"NM",
       "State":"New Mexico"
       },{
       "Abbreviation":"NY",
       "State":"New York"
       },{
       "Abbreviation":"NC",
       "State":"North Carolina"
       },{
       "Abbreviation":"ND",
       "State":"North Dakota"
       },{
       "Abbreviation":"OH",
       "State":"Ohio"
       },{
       "Abbreviation":"OK",
       "State":"Oklahoma"
       },{
       "Abbreviation":"OR",
       "State":"Oregon"
       },{
       "Abbreviation":"PA",
       "State":"Pennsylvania"
       },{
       "Abbreviation":"RI",
       "State":"Rhode Island"
       },{
       "Abbreviation":"SC",
       "State":"South Carolina"
       },{
       "Abbreviation":"SD",
       "State":"South Dakota"
       },{
       "Abbreviation":"TN",
       "State":"Tennessee"
       },{
       "Abbreviation":"TX",
       "State":"Texas"
       },{
       "Abbreviation":"UT",
       "State":"Utah"
       },{
       "Abbreviation":"VT",
       "State":"Vermont"
       },{
       "Abbreviation":"VA",
       "State":"Virginia"
       },{
       "Abbreviation":"WA",
       "State":"Washington"
       },{
       "Abbreviation":"WV",
       "State":"West Virginia"
       },{
       "Abbreviation":"WI",
       "State":"Wisconsin"
       },
       {
       "Abbreviation":"WY",
       "State":"Wyoming"
       }
       ]
     };
     var select = document.getElementById("state");
     var check = '<?php echo (isset($_POST["myloc"]) && !empty($_POST["myloc"])) ? "true" : "false"; ?>';
     console.log(check);
     if(check == "true"){
       document.getElementById("myloc_check").checked = true;
       document.getElementById("street").disabled = true;
       document.getElementById("city").disabled = true;
       document.getElementById("state").disabled = true;
     }else{
       document.getElementById("myloc_check").checked = false;
       document.getElementById("street").disabled = false;
       document.getElementById("city").disabled = false;
       document.getElementById("state").disabled = false;
     }
     var selected_state = '<?php echo isset($_POST["state_name"]) ? $_POST["state_name"] : ""; ?>';
     for(var i = 0; i < state.States.length; i++){
       var optNode = document.createElement("option");
       var textNode = document.createTextNode(state.States[i].State);
       optNode.appendChild(textNode);

       var att_val = document.createAttribute("value");
       att_val.value = state.States[i].Abbreviation;

       var selected = document.createAttribute("selected");
       console.log(selected_state);
       if(selected_state == state.States[i].Abbreviation && selected_state){
         optNode.setAttribute('selected','selected');
       }
       optNode.setAttributeNode(att_val);
       select.appendChild(optNode);
     }

     function clearForm(){
       var user_form = document.getElementById("user_form");
       user_form.reset();
       document.getElementById("street").disabled = false;
       document.getElementById("city").disabled = false;
       document.getElementById("state").disabled = false;
       var card = document.getElementById("cardContainer");
       var table = document.getElementById("tableContainer");
       if(card && table){
        remove_prev_content();
       }
       var street = document.getElementById("street");
       street.value = '';
       var city = document.getElementById("city");
       city.value = '';
       var state = document.getElementById("state");
       var first_state = state.getElementsByTagName("option")[0];
       var selected = state.options[state.selectedIndex];
       selected.removeAttribute("selected");
       first_state.setAttribute('selected','selected');

       //Clear second section
       var header1 = document.getElementById("detail_header");
       if(header1){
         header1.remove();
       }
       var card2 = document.getElementById("card_summary");
       if(card2){
         card2.remove();
       }
       var header2 = document.getElementById("detail_header2");
       if(header2){
         header2.remove();
       }
       var arrow = document.getElementById("arrow_container");
       if(arrow){
         arrow.remove();
       }
       var chart = document.getElementById("chart_div");
       if(chart){
         chart.remove();
       }
     }

     document.getElementById("myloc_check").addEventListener("change", function(){
       if(this.checked){
         var street = document.getElementById("street");
         var city = document.getElementById("city");
         var state = document.getElementById("state");
         street.disabled = true;
         city.disabled = true;
         state.disabled = true;

         street.value = "";
         city.value = "";
         state.value = "State";
       }else{
         document.getElementById("street").disabled = false;
         document.getElementById("city").disabled = false;
         document.getElementById("state").disabled = false;
       }
     })

     function remove_prev_content(){
       var card = document.getElementById("cardContainer");
       card.remove();
       var table = document.getElementById("tableContainer");
       table.remove();
     }

     function get_card_summaryJS(idx){
      json_obj = <?php echo json_encode($card_array); ?>;
      console.log(json_obj[idx]);
      console.log(json_obj);
      console.log(idx);
      remove_prev_content();
      var body = document.getElementById("doc");
      var detail_header = document.createElement("h2");
      var detail_header_id = document.createAttribute("id");
      var header_style = document.createAttribute("style");

      //First Header Section
      header_style.value = "font-style:bold;";
      detail_header.setAttributeNode(header_style);
      detail_header_id.value = "detail_header";
      detail_header.setAttributeNode(detail_header_id);
      detail_header.innerHTML = "Daily Weather Detail";
      body.appendChild(detail_header);

      //Card Section
      var card_summary = document.createElement("div");
      var card_summary_id = document.createAttribute("id");
      var card_summary_style = document.createAttribute("style");
      card_summary_style.value = "width:600px; background-color:#a2d9ce; border-radius:12px; padding-left:25px; margin-left:440px; height:440px;";
      card_summary_id.value = "card_summary";
      card_summary.setAttributeNode(card_summary_id);
      card_summary.setAttributeNode(card_summary_style);
      var card_html = "<h2 style='font-style:bold; color:#fff; font-size:32px; top:85px; position:relative;'>" + json_obj[idx].currently.summary + "</h2>";
      card_html += "<img src= '" + icons_arr[json_obj[idx].currently.icon] + "' style='width:260px; height:auto; margin-left:305px; top:-80px; position:relative;'>";
      card_html += "<ul id='temp_summary'>";
      card_html += "<li style='font-size:85px;' id='temp_list'>" + json_obj[idx].currently.temperature + "<img id='temp_img_summary' src='https://cdn3.iconfinder.com/data/icons/virtual-notebook/16/button_shape_oval-512.png'></li>";
      card_html += "<li style='font-size:70px'> F </li>";
      card_html += "</ul>";

      //list item
      var sunrise = new Date(json_obj[idx].daily.data[0].sunriseTime * 1000);
      var sunrise_hour = sunrise.getHours();
      var sunset = new Date(json_obj[idx].daily.data[0].sunsetTime * 1000);
      var sunset_hour = sunset.getHours();

      if(sunset_hour == 12){   //DEBUG ask Kae
        sunset_hour = 0;
      }
      else if(sunset_hour > 12){
        sunset_hour -= 12;
      }

      if(sunrise_hour == 12){
        sunset_hour = 0;
      }
      else if(sunrise_hour > 12){
        sunrise_hour -= 12;
      }

      card_html += "<div id=card_stats>";
      card_html += "<p style='color:#fff; font-style:bold'>Percipitation: ";
      if(json_obj[idx].currently.precipIntensity <= 0.001){
        card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>N/A</span>";
      }
      else if(json_obj[idx].currently.precipIntensity <= 0.015 && json_obj[idx].currently.precipIntensity > 0.001){
        card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>Very Light</span>";
      }
      else if(json_obj[idx].currently.precipIntensity <= 0.05 && json_obj[idx].currently.precipIntensity > 0.015){
        card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>Light</span>";
      }
      else if(json_obj[idx].currently.precipIntensity <= 0.1 && json_obj[idx].currently.precipIntensity > 0.05){
        card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>Moderate</span>";
      }else{
        card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>heavy</span>";
      }
      card_html += "</p>";
      card_html += "<p style='color:#fff; font-style:bold; margin-left:-18px'; position:relative;>Chance of Rain: ";
      card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>" + (json_obj[idx].currently.precipProbability * 100).toFixed(0) + "<span style='color:#fff; font-size:15px;'> %</span></span>";
      card_html += "</p>";
      card_html += "<p style='color:#fff; font-style:bold; margin-left:8px;'>Wind Speed: ";
      card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>" + json_obj[idx].currently.windSpeed + "<span style='color:#fff; font-size:15px;'> mph</span></span>";
      card_html += "</p>";
      card_html += "<p style='color:#fff; font-style:bold; margin-left:26px;'>Humidity: ";
      card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>" + (json_obj[idx].currently.humidity * 100).toFixed(0) + "<span style='color:#fff; font-size:15px;'> %</span></span>";
      card_html += "</p>";
      card_html += "<p style='color:#fff; font-style:bold; margin-left:30px;'>Visibility: ";
      card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>" + json_obj[idx].currently.visibility + "<span style='color:#fff; font-size:15px;'> mi</span></span>";
      card_html += "</p>";
      card_html += "<p style='color:#fff; font-style:bold; margin-left:-20px;'>Sunrise / Sunset: ";
      card_html += "<span style='color:#fff; font-weight:900; font-size:28px; top:3px; position:relative;'>" + sunrise_hour + "<span style='color:#fff; font-size:16px; font-weight:600;'>AM/ </span>" + sunset_hour + "<span style='color:#fff; font-size:16px; font-weight:600;'> PM</span></span>";
      card_html += "</p>";
      card_html += "</div>";

      //Second Header Section
      var detail_header2 = document.createElement("h2");
      var detail_header_id2 = document.createAttribute("id");
      var header_style2 = document.createAttribute("style");

      //First Header Section
      header_style2.value = "font-style:bold;";
      detail_header2.setAttributeNode(header_style2);
      detail_header_id2.value = "detail_header2";
      detail_header2.setAttributeNode(detail_header_id2);
      detail_header2.innerHTML = "Day's Hourly Weather";
      //Set HTML
      card_summary.innerHTML = card_html;
      body.appendChild(card_summary);
      body.appendChild(detail_header2);

      //Create Image and onclick state
      var img_summary = document.createElement("div");
      var img_summary_id= document.createAttribute("id");
      img_summary_id.value = "arrow_container";
      var img_style = document.createAttribute("style");
      img_style.value = "width:200px; height:100px; margin-left:718px; top:-25px; position:relative;";
      img_summary.setAttributeNode(img_summary_id);
      img_summary.setAttributeNode(img_style);

      console.log(json_obj[idx].hourly.data.length);
      console.log(json_obj[idx].hourly.data);

      explicit = json_obj[idx];
      var img_html = "<img id='arrow_img' src='" + arrow_down + "' onclick='change_arrow()'/>";

      img_summary.innerHTML = img_html;
      body.appendChild(img_summary);
     }

     function change_arrow(){
       var img_src = document.getElementById("arrow_img").src;
       var body = document.getElementById("doc");
       if(img_src == arrow_down){
         document.getElementById("arrow_img").src = arrow_up;

         //Create chart div
         var chart = document.createElement('div');
         var chart_id = document.createAttribute('id');
         chart_id.value = "chart_div";
         chart.setAttributeNode(chart_id);
         var chart_style = document.createAttribute("style");
         chart_style.vlaue = "width:500px;height:200px;";
         chart.setAttributeNode(chart_style);
         body.appendChild(chart);
         //Chart callback
         google.charts.load('current', {packages: ['corechart', 'line']});
         google.charts.setOnLoadCallback(function (){
           create_chart(explicit);
         });
       }
       else{
         document.getElementById("arrow_img").src = arrow_down;
         delete_chart();
       }
     }

     function delete_chart(){
       var chart = document.getElementById("chart_div");
       chart.remove();
     }

     function create_chart(h_json){
       var data = new google.visualization.DataTable();
       data.addColumn('number', 'x');
       data.addColumn('number', 'T');
       var values = new Array();
       for(var i = 0; i < h_json.hourly.data.length; i++){
         var time = new Date(h_json.hourly.data[i].time * 1000);
         var hour = time.getHours();
         values[i] = [hour, h_json.hourly.data[i].temperature];
       }
       data.addRows(values);
       var options = {
         hAxis:{
           title: 'Time'
         },
         vAxis:{
           title: 'Temperature'
         },
         width:850,
       };
       var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
       chart.draw(data, options);
     }
     </script>
   </body>
 </html>
