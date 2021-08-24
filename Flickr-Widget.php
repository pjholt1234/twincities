<?php
    
    //REFERENCE: Dan Coulter (<12/06/17>) phpflickr (Version 2) [PHP]. https://github.com/dan-coulter/phpflickr
    
    require_once('phpFlickr.php');
    

    //Loading The config file with db information and API keys
    $xml=simplexml_load_file("config.xml") or die("Error: Cannot create object");

    $server = "localhost";
    $username = "pjholt64_project";
    $pass = "projects";
    $dbname = "pjholt64_twincitiesproject";

    $connect = mysqli_connect($server,$username,$pass,$dbname);
    
        $Flickr_API_key = $xml->data->flikrKEY;
        $Flickr_API_secret = $xml->data->flikrSecretKey;
    
    //Creating a new instance of the PHP Flickr Libray 
    $flickr = new phpFlickr($Flickr_API_key,$Flickr_API_secret,false);

    //enabling cache to database
    $dbstring = 'mysql://'.$user.':'.$password.'@'.$host.'/'.$dbname;
    $flickr->enableCache("db", $dbstring);
        
    //Check if the widget has the city name or not, meaning it can work outside of the main webpage
    if (isset($_POST["poinameinput"])) {
        $tags = $_POST["poinameinput"];
    }else{
        $tags = "Car";
    }

    //Constructing the search arguments                             
    $args = array("tags" => $tags, "safe_search" => 1, "sort" => "interestingness-desc", "format" => "json");
    
    //Pass the args to the phpflickr libaray
    $response = $flickr->photos_search($args);
                                
    $i = 0;
    // process each photo result
    foreach ($response['photo'] as $photo) {
        $url ='http://farm66.staticflickr.com/65535/'.$photo['id'].'_'.$photo['secret'].'.jpg';
        echo '
        <td class="flicker">
        <img class="flicker" src="'.$url.'">
        </td>';
        
        //break the loop after 5 photos                            
        if ($i++ == 3) break;    
    }
    
    echo '<td><p>Powered by</p><img src="flickr.png" style="width: 50px;"></td>'
?>
