
<html>
<head>
<!-- Refrencing the css file-->
<link rel="stylesheet" href="style.css">
<!-- Refrencing fonts library for text fonts-->
<link rel='stylesheet prefetch' href='https://fonts.googleapis.com/css?family=Lato|Quicksand'>
<!-- Refrencing fonts library for the arrow in select list-->
<link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css'>

</head>

<body>
<?php
$xml=simplexml_load_file("config.xml") or die("Error: Cannot create object");

$server = "localhost";
$username = "pjholt64_project";
$pass = "projects";
$dbname = "pjholt64_twincitiesproject";

$connect = mysqli_connect($server,$username,$pass,$dbname);

if (!$connect)
{
    die("Unable to connect to the database". mysqli_connect_error());
}
    
//Making an sql querry
//selecting all city ids from city table
$sql= "SELECT city_id, names FROM city";
//variable that contains results from the querry
$result = mysqli_query($connect,$sql);
//making a cycle that populates $data object with information from the database
while ($rows = mysqli_fetch_array($result)) {
    $data[] = $rows;
}
?>
<div class="selectlist">
    <div class="sel sel--black-panther">
    <select id="cityss" name="selectedcity">
    <option value="" disabled selected>Select your city</option>
<!-- adding a php script inside select list that makes options with names and values based on what cities are in the database  -->
    <?php foreach ($data as $row): ?>
        <option value="<?= $row['city_id'] ?>"><?=$row["names"]?></option>
    <?php endforeach ?>
</select>
</div>
</div>

<!-- hidden div that holds iframe -->
<div id="mapframe" style="display:block; visibility:hidden">
<iframe src="myMap.php" name= "hiddenframe" class="hiddenframe"></iframe>   
</div>



<!-- creating a form that uses post method to send selected city's id to myMap page which will be stored in the "hiddenframe" iframe  -->    
<form method="post" action="myMap.php" target="hiddenframe" id="theform">
<!-- an invisible input field that will change to be the id of the the selected city and will be sent with post method when the script is run-->
<input type="hidden" id="cities" name="selectedcity">
</form>
</body>
</html>


<!--Using an external javascript thats used for the button in primary page-->
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js'></script>

<script>
    //REFERENCE: https://codepen.io/radiantshaw/pen/oLdLRW
    //Some of the code that was used below was made by: Nipun Paradkar 

    /* ===== Logic for creating fake Select Boxes ===== */
    //runing a function for each element that has '.sel' in its class name 
$('.sel').each(function() {
    //selects the children, 'this case <option>' of <select> html element and hides them to display custom made fake select boxes instead
  $(this).children('select').css('display', 'none');
  
  var $current = $(this);
  
  $(this).find('option').each(function(i) {
    if (i == 0) {
        //creates a new div to hold the elemets of the select list
      $current.prepend($('<div>', {
        class: $current.attr('class').replace(/sel/g, 'sel__box')
      }));
      //variable that hold the placeholder value of select list elements
      var placeholder = $(this).text();
      
        //creates the <span> elements that act as a select list options
      $current.prepend($('<span>', {
          //create the properties of the span elements
        class: $current.attr('class').replace(/sel/g, 'sel__placeholder'),
        text: placeholder,
        'data-placeholder': placeholder
        
      }));
      
      return;
    }
        
    $current.children('div').append($('<span>', {
      class: $current.attr('class').replace(/sel/g, 'sel__box__options'),
      text: $(this).text()
    }));
  });
});

// Toggling the `.active` state on the `.sel`. element
$('.sel').click(function() {
  $(this).toggleClass('active');
  var $choice = $(this);
  

});

// Toggling the `.selected` state on the options.
$('.sel__box__options').click(function() {
  var txt = $(this).text();
  var index = $(this).index();
  
  //gets the value of the hidden input field 'cities'
  document.getElementById("cities").value = index+1;
  //submits the POST method form that holds that hidden input field
  document.getElementById("theform").submit();
  //makes the iframe visible 
  document.getElementById("mapframe").style.visibility = "visible";
  
  $(this).siblings('.sel__box__options').removeClass('selected');
  $(this).addClass('selected');
  
  var $currentSel = $(this).closest('.sel');
  $currentSel.children('.sel__placeholder').text(txt);
  $currentSel.children('select').prop('selectedIndex', index + 1);
});

    </script>
