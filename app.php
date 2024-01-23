<!DOCTYPE html>
<html lang="eu" data-theme="light">
    <head>
        <link rel="stylesheet" type="text/css" href="resources/css/basestyle.css">
        <link rel="stylesheet" type="text/css" href="resources/css/appstyle.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
        <link rel="stylesheet" type="text/css" href="/resources/css/loading-bar.css"/>
        <script type="text/javascript" src="/resources/js/loading-bar.js"></script>
        <script src="resources/js/socket-io/socket.io.js"></script>
        <script src="./resources/js/index.js"></script>

        <title>WellBee</title>
    </head>
    <body>
        <div id="jquerychecker"></div>
        <section id="header">
            <div id="logo">
                <img src="/resources/images/wellbee.svg" id="logoImage">    
            </div>
            <div id="pfp">
                <a href="#" id="profilePicture"></a>
            </div>
        </section>
        <section id="content">
            <div class="padding" id="stats">
                <p class="paddingTitle">TODAY'S STATS</p>
                <div class="paddingContent">
                    <div class="progressBar" id="calories">
                        <!-- <img src="resources/images/hexBar.svg"> -->
                        <div class="caloriesBar label-center"></div>
                    </div>
                    <div class="progressBar" id="steps">
                        <!-- <img src="resources/images/hexBar.svg"> -->
                        <div class="stepsBar label-center">
                        </div>
                    </div>
                    <div class="progressBar" id="miles">
                        <!-- <img src="resources/images/hexBar.svg"> -->
                        <div class="milesBar label-center"></div>
                    </div>
                </div>
            </div>
            <div class="padding" id="badges">
                <p class="paddingTitle">RECENT BADGES</p>
                <div class="paddingContent" id="badgesContainer">
                    <div class="badgeObject">
                        <img class="badgeImage" src="resources/images/badge.png">
                    </div>
                    <div class="badgeObject">
                        <img class="badgeImage" src="resources/images/badge.png">
                    </div>
                    <div class="badgeObject">
                        <img class="badgeImage" src="resources/images/badge.png">
                    </div>
                    <div class="badgeObject">
                        <img class="badgeImage" src="resources/images/badge.png">
                    </div>
                    <div class="badgeObject">
                        <img class="badgeImage" src="resources/images/badge.png">
                    </div>
                    <div id="fadeOut"></div>
                </div>
                
            </div>
            <div class="padding" id="routes">
                <p class="paddingTitle">NEARBY ROUTES</p>
                <div class="paddingContent">
                    <div id="map">
                        <a href="quests.html" id="navToMapPage">GO TO ROUTES</a>
                    </div>
                </div>
            </div>
        </section>
        <section id="footer">
            <div id="uiNav">
                <a href="#" id="leaderboards"><img class="navbutton" src="resources/images/ui_leaderboards.png"></a>
                <a href="#" id="walking"><img class="navbutton" src="resources/images/ui_walking.png"></a>
                <a href="quests.php" id="quests"><img class="navbutton" src="resources/images/ui_quests.png"></a>
            </div>
        </section>
    </body>
    
    <script>
        var waitForEl = function (selector, callback) {
            if (jQuery(selector).length) {
                callback();
            } else {
                setTimeout(function () {
                    waitForEl(selector, callback);
                }, 100);
            }
        };
        
        stepsBar = new ldBar('.stepsBar', {
            "stroke": "#f5bb26",
            "stroke-width": "10",
            "value": "20",
            "path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
        });
        // document.querySelector('.stepsBar .ldBar-label').innerHTML = "350/1000<br>STEPS";
        caloriesBar = new ldBar('.caloriesBar', {
            "stroke": "#f5bb26",
            "stroke-width": "10",
            "value": "60",
            "path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
        });
        // document.querySelector('.caloriesBar .ldBar-label').innerHTML = "467/870<br>CALORIES";
        milesBar = new ldBar('.milesBar', {
            "stroke": "#f5bb26",
            "stroke-width": "10",
            "value": "90",
            "path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
        });
        <?php
        $conn = mysqli_connect("localhost", "root", "", "wellbee");
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
        waitForEl('#map', function(){
            stepsBar.set($steps_daily_percent_complete, false);
            document.querySelector('.stepsBar .ldBar-label').innerHTML = '$steps_daily/$steps_target<br>STEPS';
            milesBar.set($miles_daily_percent_complete, false);
            document.querySelector('.milesBar .ldBar-label').innerHTML = '$miles_daily/$miles_target<br>MILES';
            caloriesBar.set($calories_daily_percent_complete, false);
            document.querySelector('.caloriesBar .ldBar-label').innerHTML = '$calories_daily/$calories_target<br>CALORIES';
        })
        "
        ?>
    </script>
</html>