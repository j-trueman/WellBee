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
<img src="https://github.com/j-trueman/WellBee/assets/82833724/0a7c09ef-2e48-49c1-bb44-25c6bbc7a914" style="width: 250px">

### The Progress Bars

<img src="https://github.com/j-trueman/WellBee/assets/82833724/7fc7f32e-3234-43fa-9ae1-9211eb916d9b" style="width: 250px">

These progress bars break down the users daily stats and show how close they are to reaching their goals for the day (distance walked, steps taken and calories burned) These progress bars were created using the LoadingBar JavaScript library which allows us to use an svg as the basis for the path that the progres bar follows. Below you can see the code that generates these progress bars:
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
      $conn = mysqli_connect("dbhostname", "dbusername", "dbpassword", "dbname");
      $steps_query_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `steps_daily`, `steps_target` FROM `uinfo`"));
      $miles_query_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `miles_daily`, `miles_target` FROM `uinfo`"));
      $calories_query_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `calories_daily`, `calories_target` FROM `uinfo`"));
        
      $steps_daily = $steps_query_result["steps_daily"];
      $steps_target = $steps_query_result["steps_target"];
      $miles_daily = $miles_query_result["miles_daily"];
      $miles_target = $miles_query_result["miles_target"];
      $calories_daily = $calories_query_result["calories_daily"];
      $calories_target = $calories_query_result["calories_target"];
        
      $steps_daily_percent_complete = round(($steps_daily/$steps_target)*100, 2);
      $miles_daily_percent_complete = round(($miles_daily/$miles_target)*100, 2);
      $calories_daily_percent_complete = round(($calories_daily/$calories_target)*100, 2);
        
      echo "
      waitForEl('#dummy_map', function(){
          stepsBar.set($steps_daily_percent_complete, false);
          document.querySelector('.stepsBar .ldBar-label').innerHTML = '$steps_daily/$steps_target<br>STEPS';
          milesBar.set($miles_daily_percent_complete, false);
          document.querySelector('.milesBar .ldBar-label').innerHTML = '$miles_daily/$miles_target<br>MILES';
          caloriesBar.set($calories_daily_percent_complete, false);
          document.querySelector('.caloriesBar .ldBar-label').innerHTML = '$calories_daily/$calories_target<br>CALORIES';
      })
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


[^1]: Step and calorie tracking are rough estimates. Steps being based on the average page length of a human (around 0.75 meters).
[^2]: The gaining of points currently serves no purpose.
[^3]: All modifications were made by us. Read more about FullPageOs [here](https://github.com/guysoft/FullPageOS).
[^4]: Not in a creepy way, obvs.
