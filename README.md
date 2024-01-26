<img src="https://github.com/j-trueman/WellBee/assets/82833724/3a63caff-1a75-45d7-9642-6838b9520fce">

---

TODO:
- [ ] automated location host ip resolution
- [ ] image-based quests
- [ ] routes implementation
  + [ ] quest type-filtering
- [ ] rough calorie tracking
- [ ] badge content
- [ ] daily goal adjustment 
- [ ] code cleanup
- [ ] documentation
- [ ] finish the readme lol

# Welcome to WellBee!
WellBee is an open-source gamified walking app that is designed to help improve your WELLBEEing by making excersising fun! It will track your daily distance walked as well as steps and even track calories[^1]. There is also a quest system which rewards you for completing a task, e.g. walking a route or going to a specific place. We also plan to implement a GeoGuessr-style quest system where you will be given an image and have to walk to where you think that image is. Completing quests is rewarded with badges and a points system[^2].

## How Does it Work?
### The OS
WellBee is a webapp hosted on a RaspberryPi 4 which we configured with a 4.3" DSI touchscreen from [WaveShare](https://www.waveshare.com/product/4.3inch-dsi-lcd.htm). This RPi is running a modified version of FullPageOs[^3] to which we added a LAMP package (Linux, Apache, MySql, PHP) as well as PhpMyAdmin to make interacting with backend databases easier.

### The Libraries
A small set of JavaScript libraries were used to make development easier and to help in the development of new features. These are:
- [JQuery](https://www.npmjs.com/package/jquery)
- [LeafletJS](https://leafletjs.com/)
- [JSCookie](https://www.npmjs.com/package/js-cookie)
- [LoadingBar](https://loading.io/progress/)

### The Companion App
We developed a small android application with Flutter in order to stream location data to the RPi and make it easier to track the user.[^4] You can download this app from the [releases](https://github.com/j-trueman/WellBee/releases) tab along with the source code for it (there's not much of it).

## Webapp Breakdown
Here's some screenshots of the webapp. Let's go through it bit by bit and explain how it all works.

<img src="https://github.com/j-trueman/WellBee/assets/82833724/5e0524fa-8bb3-4148-b5dc-c3cb4dee87f2" style="width: 250px">
<img src="https://github.com/j-trueman/WellBee/assets/82833724/e95f0bcb-40c7-43b4-b43b-5334e61f353e" style="width: 250px">

### The Progress Bars

<img src="https://github.com/j-trueman/WellBee/assets/82833724/7fc7f32e-3234-43fa-9ae1-9211eb916d9b" style="width: 250px">

These progress bars break down the users daily stats and show how close they are to reaching their goals for the day (distance walked, steps taken and calories burned) These progress bars were created using the LoadingBar JavaScript library which allows us to use an svg path value as the basis for the path that the progres bar follows. Below you can see the code that generates these progress bars:
```javascript,php
//Create the progress bar object
progressBarNameHere = new ldBar('.progressBarCSSSelectorHere', {
      "stroke": "#f5bb26",
      "stroke-width": "10",
      "value": "0",
      "path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
});
```
This PHP code then pulls the users data from the backend database and calculates the percentage of the goal that has been completed before setting the text in the bars to match this.
```php
<?php
      //Connect to the datdabase and collect goal data (this could be turned into an object. will do that in update)
      $conn = mysqli_connect("dbhostname", "dbusername", "dbpassword", "dbname");
      $steps_query_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `steps_daily`, `steps_target` FROM `uinfo`"));
      $miles_query_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `miles_daily`, `miles_target` FROM `uinfo`"));
      $calories_query_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `calories_daily`, `calories_target` FROM `uinfo`"));

      //Store values to variables
      $steps_daily = $steps_query_result["steps_daily"];
      $steps_target = $steps_query_result["steps_target"];
      $miles_daily = $miles_query_result["miles_daily"];
      $miles_target = $miles_query_result["miles_target"];
      $calories_daily = $calories_query_result["calories_daily"];
      $calories_target = $calories_query_result["calories_target"];

      //Calculate percentage of goal completed
      $steps_daily_percent_complete = round(($steps_daily/$steps_target)*100, 2);
      $miles_daily_percent_complete = round(($miles_daily/$miles_target)*100, 2);
      $calories_daily_percent_complete = round(($calories_daily/$calories_target)*100, 2);

      //Add some javascript to change the labels on the progress bars and set their values.
      echo "
            waitForEl('#dummy_map', function(){
                  stepsBar.set($steps_daily_percent_complete, false);
                  document.querySelector('.stepsBar .ldBar-label').innerHTML = '$steps_daily/$steps_target<br>STEPS';
                  milesBar.set($miles_daily_percent_complete, false);
                  document.querySelector('.milesBar .ldBar-label').innerHTML = '$miles_daily/$miles_target<br>MILES';
                  caloriesBar.set($calories_daily_percent_complete, false);
                  document.querySelector('.caloriesBar .ldBar-label').innerHTML = '$calories_daily/$calories_target<br>CALORIES';
            });
      ";
?>
```
And the actual DOM elements look like this
```html
<div class="progressBar" id="calories">
      <div class="caloriesBar label-center"></div>
</div>
<div class="progressBar" id="steps">
      <div class="stepsBar label-center"></div>
</div>
<div class="progressBar" id="miles">
      <div class="milesBar label-center"></div>
</div>
```
### The Badges

<img src="https://github.com/j-trueman/WellBee/assets/82833724/0bbef451-3c5a-45a0-aef0-0fcbd51c2abe" style="width: 250px">

The badges that the user currently posses are stored in the `badge_acquired_ids` field of the user info database along with a URL to the image that it uses in the `img_url_reference` field and is formatted as a list of comma separated values. e.g.
`"1,4,3,7"`. Badges are are created via this PHP code which pulls that field from the database and then explodes the field on every comma. It then loops through the returned array and if the value is not null then it displays that badge in the badge section of the app.
```php
<div class="paddingContent" id="badgesContainer">
      <?php
            $conn = mysqli_connect("localhost", "root", "", "wellbee");
            $badges_acquired = explode(",",mysqli_fetch_assoc(mysqli_query($conn, "SELECT `badge_acquired_ids` FROM `uinfo`"))['badge_acquired_ids']);
            foreach($badges_acquired as $current_badge_id) {
                  if($current_badge_id != "") {
                        $badge_image_reference = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `image_url_reference` FROM `badges` WHERE `badge_id` = '$current_badge_id'"))['image_url_reference'];
                        echo "
                              <div class='badgeObject'>
                                    <img class='badgeImage' src='resources/images/$badge_image_reference'>
                              </div>
                        ";
                  }
            }
      ?>
</div>
```

### The Map

![image](https://github.com/j-trueman/WellBee/assets/82833724/53bcd404-f385-45af-bce1-5d3b74d7eb65)

The map is created with the LeafletJS library. To create a new map with LeafletJS you simply have to import the script and stylesheet as a header tag:
```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
```
and then set the start position of the map and load the tiles in JavaScript:
```javascript
map = L.map('map').setView(["55.765088665952746", "-4.151738591291236"], 13);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
```

### The Quests

The quests are broken up into two parts. The description boxes and the markers. The boxes store information about the quests such as name, description, reward and how far away from the user it is as well as the button for starting the quest. These are created through some PHP code which gets the information of the quest from the database and then loops through this data, generating a box for each. The generation code is there twice so that if a quest is active then it will only generate the description box for that quest.
```php
<?php
    //Get all information on a quest if it hasn't been completed
    $questData = mysqli_query($database_connection, "SELECT * FROM `quests` WHERE `completed` = 0");
    while($questItem = mysqli_fetch_assoc($questData)) {
        $questId = $questItem['quest_id'];
        $questName = $questItem['name'];
        $questDescription = $questItem['descript'];
        $questReward = $questItem['points_reward'];
        
        if (isset($_SESSION['questactive'])) {
            if ($questId == $_SESSION['questactive']) {
                echo"
                <div class='singleQuestBox' id='questbox_$questId' dstFromUser=''>
                    <div class='questTitleCard'>
                        <p class='questHeader'>$questName</p>
                        <p class='questDescription'>$questDescription</p>
                        <p class='questReward'><strong>Reward:</strong> $questReward</p>
                    </div>
                    <form class='questButtonBox' method='GET' onsubmit='return submit(this)'>
                        <button type='submit' name='abandonquest' class='abandonquest' value=$questId>Abandon Quest</button>
                        <button type='submit' name='completequest' class='completequest'></button>
                        <p class='questDst'></p>
                    </form>
                </div>
                ";
                break;
            } else {
                continue;
            }
        } else {
            echo"
            <div class='singleQuestBox' id='questbox_$questId' dstFromUser=''>
            <div class='questTitleCard'>
                <p class='questHeader'>$questName</p>
                <p class='questDescription'>$questDescription</p>
                <p class='questReward'><strong>Reward:</strong> $questReward</p>
                </div>
                <form class='questButtonBox' method='GET' onsubmit='return submit(this)'>
                <button type='submit' name='startquest' class='startquest' value=$questId>Start Quest</button>
                <p class='questDst'></p>
            </form>
            </div>
            ";
        }
    }
?>
```
The markers work very similarly except they are generated with JavaScript instead of a DOM element. Thankfully the LeafletJS library has a framework for creating and positioning markers given a latlng point[^5]. Again the code is there twice so that it only generates the marker for the active quest if there is one.
```php
<?php
    $get_quests = mysqli_query($database_connection, "SELECT `quest_id`,`coordinates` FROM `quests` WHERE `completed` = 0");
    while($current_quest = mysqli_fetch_assoc($get_quests)) {
        $questId = $current_quest['quest_id'];
        $coords = explode(",",$current_quest['coordinates']);
        
        if (isset($_SESSION['questactive'])) {
            if ($questId == $_SESSION['questactive']) {
                echo "
                questmarker_$questId = L.marker(['$coords[0]','$coords[1]'], {title: '$questId', icon: myIcon, riseOnHover: true}).addTo(map); 
                
                questmarker_$questId.on('click', function() {
                    map.setView(['$coords[0]','$coords[1]'], 16); 
                    questmarker_$questId.getElement().classList.add('activeMarker');
                    document.querySelector('#questbox_$questId').classList.add('active');
                    document.querySelector('#questbox_$questId').classList.remove('hidden');
                    document.querySelectorAll('.singleQuestBox:not(#questbox_$questId)').forEach((element) => {
                        element.classList.remove('active'); element.classList.add('hidden')
                    })
                });
                
                ";
                break;
            }
            else {
                continue;
            }
        } else {
            echo "
                questmarker_$questId = L.marker(['$coords[0]','$coords[1]'], {title: '$questId', icon: myIcon, riseOnHover: true}).addTo(map); 
                
                questmarker_$questId.on('click', function() {
                    map.setView(['$coords[0]','$coords[1]'], 16); 
                    questmarker_$questId.getElement().classList.add('activeMarker');
                    document.querySelector('#questbox_$questId').classList.add('active');
                    document.querySelector('#questbox_$questId').classList.remove('hidden');
                    document.querySelectorAll('.singleQuestBox:not(#questbox_$questId)').forEach((element) => {
                        element.classList.remove('active'); element.classList.add('hidden')
                    })
                });
            ";
        }
    }
?>
```

## Lets Talk About Location Tracking

### Companion App

Location tracking for the app is done via a mobile device that communicates with the WellBee unit through a WebSocket. The device has on it a Flutter app which uses the [Flutter Location](https://pub.dev/packages/location) library to pull the location data from the devices gps. It then advertises a websocket which will send the location data to whichever client connects to it[^6]. You can read more about the companion app and how it works in the [COMPANION.md](https://github.com/j-trueman/WellBee/blob/main/COMPANION.md) readme.

### Client Side

On the client side we use JavaScript's built-in WebSocket API to communicate with the one being advertised by the companion app. To start, we create a new socket object.
```javascript
const sock = new WebSocket('ws://192.168.1.69:8080/ws');
```
As soon as this connection is opened, we can setup an event listener that listens for the "message" event and then we should start recieving location updates from the companion app. These updates come in the form of a list of comma separated values[^7] which we can split and assign each value to a variable.
```javascript
mapShouldTrack = true;

sock.addEventListener("message", async (event) => {
    console.clear();
    positionData = event.data.split(',');
    let userLat = parseFloat(positionData[0]);
    let userLng = parseFloat(positionData[1]);
    let positionAccuracy = parseFloat(positionData[2]);
    let userSpeed = parseFloat(positionData[3]);
...
```
You may also have noticed this `mapShouldTrack` variable which is used to determine whether the map should be centered on the user. This is on by default but turns off when the user clicks or moves the map to a different location[^8].
We then use the position data to draw a circle around where the user is. The circle has a radius of whatever the accuracy value of the location data is (this is in meters).
```javascript
//This if statement is used to remove the circle from the previous location update.
if (typeof circle != "undefined") {
    circle.removeFrom(map);
}

circle = L.circle([positionData[0], positionData[1]], {
    color: 'lightblue',
    fillColor: '#42e8f4',
    fillOpacity: 0.5,
    radius: parseInt(positionData[2])
}).addTo(map);
```
Every update, we update how far the user has travelled that day by calculating the distance between the latitude and longitude points of the last known position and the users current position. Here is the function that is used to calculate this delta:
```javascript
const distanceCalc = (inputLat1,inputLng1,inputLat2,inputLng2) => {
    let lat2 = inputLat2/57.29577951;
    let lng2 = inputLng2/57.29577951;
    let lat1 = inputLat1/57.29577951;
    let lng1 = inputLng1/57.29577951;
    let deltaInMiles = 3963.0 * Math.acos((Math.sin(lat2) * Math.sin(lat1)) + Math.cos(lat2) * Math.cos(lat1) * Math.cos(lng1 - lng2));
    return deltaInMiles;
}
```

[^1]: Step and calorie tracking are rough estimates. Steps being based on the average page length of a human (around 0.75 meters).
[^2]: The gaining of points currently serves no purpose.
[^3]: All modifications were made by us. Read more about FullPageOs [here](https://github.com/guysoft/FullPageOS).
[^4]: Not in a creepy way, obvs.
[^5]: Which we store in the 'coordinates' field of the quests database.
[^6]: While we realise that this is a security risk, this is a small project and we are not overly concerned about making it secure until we need to.
[^7]: This may be updated to a json model in future for ease of data parsing.
[^8]: Or when they click on the "recent location" button.
