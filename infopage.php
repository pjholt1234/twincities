<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!--viewport element gives the browser instructions on how to control the page's dimensions and scaling.-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <!-- referencing external javascript -->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>

    
  <title>Landing Page</title>
</head>
<body class="infopage">
    <div class="infocontent">        
            <h2 class="content-head"><?php echo $_POST["poinameinput"]; ?></h2>
            <div class="all">
                <div class="informationdiv">
                    <h2 class="content-subhead">Information</h2>
                    <div class="infobox">
                    
                    <?php 
                        $xml=simplexml_load_file("config.xml") or die("Error: Cannot create object");

                        $server = "localhost";
                        $username = "pjholt64_project";
                        $pass = "projects";
                        $dbname = "pjholt64_twincitiesproject";

                        $connect = mysqli_connect($server,$username,$pass,$dbname);
                        
                        //Google API string
                        $string = "https://maps.googleapis.com/maps/api/js?key=".$xml->data->GoogleKEY."&callback=initMap";
                            
                        //SQL query
                        $sql= "SELECT poi_id, wiki_url, description, tags,lat, lng, city_id_fk, name FROM poi WHERE name ="."'".$_POST["poinameinput"]."'";
                        $result = mysqli_query($connect,$sql);
                
                        //passing the result into an array
                        while ($row = mysqli_fetch_array($result)) {
                            $poidata[] = $row;
                        }
                        
                        //Display POI data
                            echo "<p><span class ='bold'>Description: </span>".$poidata[0][2]."</p>";
                            echo "<a href='".$poidata[0][1]."'> Wiki Link </a>";
                            echo"<p><span class ='bold'>Tags: </span>".$poidata[0][3]."</p>";

                    ?>
                    
                    </div>
                </div> 
                    
                <div class="imagebox">
                    <h2 class="content-subhead">Image Gallery</h2>
            
                    <div class="imagetablediv">
                        <table class="imagetable">
                        <?php
                            //SQL selecting image files with the current poi ID
                            $query = "SELECT file,title,description FROM images Where poi_id_fk = "."'".$poidata[0][0]."'"." ORDER BY image_id DESC";
                        
                            //SQL result
                            $result = mysqli_query($connect, $query);  
                            while($row = mysqli_fetch_array($result))  
                            {  
                                    //outputting images as html with values of an sql querry. File and description of current poi
                                    echo "<td><img src='data:image/jpeg;base64,".base64_encode($row['file'] )."'". " title='".$row['description'] ."'"."' class='img-thumnail'  /></td>";
                                    //Display Images
                            
                               
                            }
                        
                        ?>
                        </table>
					</div>
                 </div>
                    <div class="upload_butt">
                        <h2 class="content-subhead">Image Uploader</h2>
                        
                        <!-- This section contains the image uploader, it takes the poi_id and poi name alongside the users image and saves it to the database.
                        it auto - increments the image_id in the database table -->
                        <!--  multipart/form-data enctype property is required when you are using forms that have a file upload control -->
                        <form target="_blank" method="post" enctype="multipart/form-data">
                            <!-- creating labels that replace the buttons because its easier to work the styling-->
                            <label class="custom-file-choose"> Choose image
                                <input type="file" name="file" id="filing">
                            </label>
                            <label class="custom-file-upload"> Upload
                                <input type="submit" name="submit" value="Upload" id="uploading">
                            </label>
                            <!-- hidden input that acts a signal to javascript when to post the from -->
                            <input type="hidden" name="posting" id="posting"> 
                            <input type="text" class= "descriptionin" name="poi_description" maxlength="20" placeholder="Enter description">
                            <!-- putting a input field hold the value that was submited to this page before. This allows to resubmit the form in order for page to change its content wihout loosin the posted value it was using -->
                            <input type="hidden" id="poinameinput" name="poinameinput" value="<?php echo ($_POST['poinameinput'])?>">
                        </form>
                        <form method="post" id="poinameplc">
                            <input type="hidden" id="poinameinput" name="poinameinput" value="<?php echo ($_POST['poinameinput'])?>">
                        </form>
                    </div>
                    <h2 class="content-subhead">Flickr Gallery</h2>
                    <div class="flicker">
                    <!--table for flicker content-->
                        <table class="imagetable">
			             <?php
                            include('Flickr-Widget.php');
                        ?>
                         </table>
                    </div>
            </div>
        <h2 class="content-subhead">Location</h2>
            <div class="mapthing">
                <div id="map" class="mapinfo"></div>
			</div>        
            <script>   
                function initMap(){
                    //Passes $poidata from the php array to a javascript array called poiarray
                    var poiarray = <?php echo json_encode($poidata)?>; 
                    //creating variables from the array
                    var poiLat = parseFloat(poiarray[0][4]);
                    var poiLng = parseFloat(poiarray[0][5]);
                    var poiName = poiarray[0][7];
                    var poiUrl = poiarray[0][1];
            
                    // Maps visual properties
                    var options = {
                        zoom:15,
                        center:{lat:poiLat,lng:poiLng},
						gestureHandling: 'none',
                        zoomControl: false,
                        disableDefaultUI: true
                    };
  
                    // New map
                    //gets the contents of map div
                    var map = new google.maps.Map(document.getElementById('map'), options);
                        
                    //Creates new marker with using the previous variables as its properties
                    var marker = new google.maps.Marker({
                        position: {lat: poiLat,lng: poiLng},
                        map: map,
                        title: poiName, 
                    });
                }
            </script>  
    </div>

    <?php  
    //checks if the field everything is in order to insert the file to the database. Depends on the javascript on the bottom.
    if(isset($_POST["posting"]))  
    {
        $poicityfk= ($poidata[0][6]);
        $poiname= $_POST["poinameinput"];
        $poidesctiption= $_POST["poi_description"];
        $poiid= $poidata[0][0];
	    //gets content that is inserted in the type="file" input field 
        $file = addslashes(file_get_contents($_FILES["file"]["tmp_name"]));  
	    //makes a query to insert the column to the database with the sent values
        $query = "INSERT into images (city_id_fk, title, description, file, poi_id_fk) VALUES ('$poicityfk','$poiname','$poidesctiption','".$file."','$poiid')";  
            if(mysqli_query($connect, $query))  
        {  
			    // echo the javascript to resubmit the form so the page resets and new image appears
            echo "<script type='text/javascript'>";
            echo "document.getElementById('poinameplc').submit();";
		    echo "</script>";
		   
        }  
    }  
    ?> 

    <script>  
    //will run once the entire page (images or iframes), not just the DOM, is ready.
 $(document).ready(function(){  
 //runs the function when the buton is pressed
      $('#uploading').click(function(){
		  
           var image_name = $('#filing').val();
//checks if the input field that hold the file is empty		   
           if(image_name == '')  
           {  
                alert("Please Select Image");  
                return false;  
           }  
           else  
           {  
                //if the file is uploaded to input field with id 'filing' it disects the file name to check get the file extension.
                var extension = $('#filing').val().split('.').pop().toLowerCase();
                var fi = document.getElementById('filing');
                //making variable that hold the size value in bites of the uploded file
                var fsize = fi.files.item(0).size;
					//checks if the file extension is valid
                if(jQuery.inArray(extension, ['gif','png','jpg','jpeg']) == -1)  
                {  
                     alert('Invalid Image File');
                     //resets the file input field value   
                     $('#filing').val('');  
                     return false;  
                }
                //if the file extension is valid
                else
                {
                    //checks if the file size if less that 2MB
                    if(fsize<2000000)
                    {
                        //gives the input field a value which signals that file upload is a go 
                        document.getElementById("posting").value = 1;
                        //closes the current window because next window will be opened after the form resubmits
                        window.close();
                    }
                    //if file size is more that 2mb
                    else
                    {
                     alert('Sorry, file size has to be less than 2Mb');  
                     $('#filing').val('');  
                     return false;

                    }
                    
                    
                }  
           }  
      });  
 });  
 </script> 
<!--defer attribute tells the browser that it should go on working with the page, and load the script “in background”, then run the script when it loads.-->
<script async defer src="<?php echo $string; ?>"></script>  
</body>
</html>