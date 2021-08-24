<!DOCTYPE html>
<!-- Referencing script used for weather api-->
<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous" ></script>
<link rel="stylesheet" href="style.css">
<!-- Referencing the font styles-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">  
<html lang="en">
<head>
  <title>My Google Map</title>
</head>

<!--Post method from that sends the name of the pois whom marker was clicked to ingopage.php -->
<body>
  <form method="POST" id="poinameplc" action="infopage.php" target="infopage.php">
      <input type="hidden" id="poinameinput" name="poinameinput">
  </form>

  <div id="map" class="mapmain"></div> 
  
  <?php
    $xml=simplexml_load_file("config.xml") or die("Error: Cannot create object");
  //checking if the go back button was pressed
    if(!isset($_POST['selectedcity']))
    {
        //hiding one html title tag
            echo "<script type='text/javascript'>$('#nowweatherid').hide()</script>";
            exit();
    }
    $city_id = $_POST['selectedcity'];
    // connecting to db    

    $server = "localhost";
    $username = "pjholt64_project";
    $pass = "projects";
    $dbname = "pjholt64_twincitiesproject";

    $connect = mysqli_connect($server,$username,$pass,$dbname);
    
    if (!$connect)
    {
        die("Unable to connect to the database". mysqli_connect_error());
    }

    // Building SQL Query for the 
    $sql= "SELECT city_id, names,lat, lng, description, currency, county, population FROM city WHERE city_id = '$city_id'";    
    $result = mysqli_query($connect,$sql);

    //Passing the result into an array
    while ($row = mysqli_fetch_array($result)) {
        $citydata[] = $row;
	}    

    //Selecting the values from the row that has a matching city id with the one that was selected in the previous page
    $sql= "SELECT poi_id, name, lat, lng, wiki_url, description FROM poi WHERE city_id_fk = '$city_id'";
    $result = mysqli_query($connect,$sql);
    //passing the result into an array
    while ($row = mysqli_fetch_array($result)) {
        $poidata[] = $row;
    }
    //selecting all the pois in the poi table to create an array used for setting categories for each one
     $sql= "SELECT poi_id, name, lat, lng, wiki_url, description FROM poi";
     $result = mysqli_query($connect,$sql);
     //passing the result into an array
     while ($row = mysqli_fetch_array($result)) {
         $poicategory[] = $row;
     }
    

    //Api  
    //Making a variable that holds a link to the google map api
    $string = "https://maps.googleapis.com/maps/api/js?key=".$xml->data->GoogleKEY."&callback=initMap";
?>
<?php
//creating variable that the length of an array
$length = count($poicategory);
    // Building SQL Query for the 
    for($i = 0;$i <=$length ;$i++){
        // selecting all the categories id of each poi 
        $sql= "SELECT cat_id_fk FROM category_poi WHERE poi_id_fk='$i'";
    $result = mysqli_query($connect,$sql);
    $row = mysqli_fetch_array($result); 
        
    $iconnumber[] = $row;
    }
?>

    
<script>
    //REFERENCE: Traversy Media (<12/06/17>) Google Maps JavaScript API (Version 1) [JavaScript]. https://www.youtube.com/watch?v=Zxf1mnP5zcw
    //This Youtube download was the basis of the map function.
    
    
    function initMap(){
        // Map options
        var options = {
            zoom:12,
            //centers the map based on selected city's longitude and lattitude
            center:{lat:parseFloat(<?php echo $citydata[0]["lat"];?>),lng:parseFloat(<?php echo $citydata[0]["lng"];?>)},
            gestureHandling: 'none',
            zoomControl: false,
            disableDefaultUI: true
        }
        
        // New map
        var map = new google.maps.Map(document.getElementById('map'), options);
	    //array that holds all points of interest places 
		var poiarray = <?php echo json_encode($poidata); ?>;
        //array that holds the categories id's of each poi
        var iconarray = <?php echo json_encode($iconnumber);?>;
        
		//Array or markers
		var markers = [];
        for(var i = 0;i < poiarray.length ;i++){

            //if first city is selected
            if (<?php echo $city_id ?>==1){
            //it starts reading the array depending on what city is selected. If first city is selected this tells to skip the elements that represent the category ids of another city
            var plusone=i+poiarray.length+1;
            }
            if (<?php echo $city_id ?>==2){
            //plus one is added because the id's in the database start from 1
            var plusone=i+1;
            }
            //setting an object that contains properites of icons variable used for markers
            var icons = {
                //points to a image that has the id of the current pois category
		  url: "images/" + iconarray[plusone][0] + ".png", // url
		  scaledSize: new google.maps.Size(30, 30), // scaled size
		};
            
		  //puts in new elements with their properites like coords, iconImage etc. into markers array
		  markers.push({coords:{lat:parseFloat(poiarray[i][2]),lng:parseFloat(poiarray[i][3])},
            iconImage: icons,
		      content:poiarray[i][5],
		      //adding a link of the marker that will later be used when the marker is clicked
            poiname: poiarray[i][1]
            }
          );
        }

		//adding the markers on the map
        for(var i = 0;i < markers.length;i++){
            //gives this object the markers content
            addMarker(markers[i]);
        }

        // Add Marker Function
        function addMarker(props){
            var marker = new google.maps.Marker({
                //setting the markers[] property "coords" as a position of the marker
                position:props.coords,
                map:map,
                title:props.poiname,
            });

            // Check if this property is set
            if(props.iconImage){
                // Set icon image
            marker.setIcon(props.iconImage);
            }

            // Check if this property is set
            if(props.content){
                var infoWindow = new google.maps.InfoWindow({
                    content:props.content
            });
		  
			
                //adding a listener that runs a function when marker is clicked
                marker.addListener('click', function(){
                    //assigns the value of this marker to the html input field
                    document.getElementById("poinameinput").value = this.title;
                    //and submits the form that this field is in
                    document.getElementById("poinameplc").submit();
                });
            
                //adding a listener that runs a function when marker is hover on with mouse
                marker.addListener('mouseover', function(){
                    infoWindow.open(map, marker);
                });
                //adding a listener that runs a function when the mouse is no longer hovering on the marker
                marker.addListener('mouseout', function(){
                    infoWindow.close(map, marker);
                });
            }
        } 
    }
</script>
<!-- running the script as soon as possible -->
<script async
    src="<?php echo $string; ?>">
</script>
<!--sets the input fields value to the current city name-->
<input type="hidden" id="citydiv" value="<?php echo $citydata[0]["names"]; ?>" />							
<script type="text/javascript">
    //takes the value of the input field and assigns it to js variable
    var city = $("#citydiv").val();
    var key  = "<?php echo $xml->data->OpenweathermapKEY ?>"; //api key
	//$.ajax is an inbuilt jquery method used to make ajax responses
	//this is sort of an object that contains all the necessary configurations Ajax needs to send the request
    $.ajax({
        //the url of api
        url:'http://api.openweathermap.org/data/2.5/weather',
        dataType:'json',
        type:'GET',
        //the parameters of this api that I want to set
        data:{q:city, appid: key, units: 'metric'},
										
        success: function(data){
        var weather = '';
            //for each element there is in data.weather run that function
            //why does it only work with the cycle??????
            $.each(data.weather, function(index, val){
                weather += '<p></b><img src="http://openweathermap.org/img/wn/'+ val.icon + '@2x.png"></p>'+ data.main.temp + '&deg;C ' + ' | ' + val.main + ", " + val.description
            });
            $(".currentweather").html(weather);
        }
										
    })
</script>
<div class="fivedayforecast"></div>
    <script type="text/javascript">
        var url = "https://api.openweathermap.org/data/2.5/forecast";
        $.ajax({
            url: url, //API Call
            dataType: "json",
            type: "GET",
            data: {
                q: city,
                appid: key, // api key
                units: "metric", // units of measurement
                cnt: "7" // number of days forcast
            },
            success: function(data) {
                var wf = "";
                wf += "<table id='weathertable'>"// build table
                wf += "<tr>"
                
                //Creating Dates
                const today = new Date() 
                const date = new Date(today)
                date.setDate(date.getDate() + 1) 
                var finaldate = date.toString().split(' ')[0];
                                                
                $.each(data.list, function(index, val) {
                    wf += "<th class='dayweek'>" // Opening paragraph tag
                    wf +=  finaldate  // Day
                    wf += "</th>" // Closing paragraph tag
                    date.setDate(date.getDate() + 1)
                    finaldate = date.toString().split(' ')[0];
                 });
                    wf += "</tr>"
                    wf += "<tr>"
                    $.each(data.list, function(index, val) {
                        wf += "<th>" // Opening paragraph tag
                        wf += "<img src='https://openweathermap.org/img/w/" + val.weather[0].icon +     ".png'>" // Icon
                        wf += "</th>" // Closing paragraph tag
                    });
                    wf += "</tr>"
                    wf += "<tr>"
                    $.each(data.list, function(index, val) {
                        wf += "<td>" // Opening paragraph tag
                        wf += "" + val.main.temp + "&degC" // Temperature
                        wf += "<span> " + val.weather[0].description + "</span></td>"; // Description
                    });  
                    wf += "</tr>"
                    wf +="</table>"
                    $(".fivedayforecast").html(wf);// insert into div
                }
        });
</script>						
</body>
</html>
