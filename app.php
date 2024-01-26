<!DOCTYPE html>
<html lang="eu" data-theme="light">
    <?php
        $conn = mysqli_connect("localhost", "root", "", "wellbee");
        if(isset($_COOKIE['dayDistanceTraveled'])){
            $dstTraveled = round(floatval($_COOKIE['dayDistanceTraveled']), 2);
            if (isset($_COOKIE['dayStepsTaken'])) {
                $stepsTaken = floatval($_COOKIE['dayStepsTaken']);
                mysqli_query($conn, "UPDATE `uinfo` SET `miles_daily` = $dstTraveled, `steps_daily` = $stepsTaken");
            }
        }
    ?>
    <head>
        <link rel="stylesheet" type="text/css" href="resources/css/basestyle.css">
        <link rel="stylesheet" type="text/css" href="resources/css/appstyle.css">
        <script type="text/javascript" src="/resources/js/loading-bar.js"></script>
        <link rel="stylesheet" type="text/css" href="/resources/css/loading-bar.css"/>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
        <script src="./resources/js/map.js"></script>

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
                
            </div>
            <div class="padding" id="routes">
                <p class="paddingTitle">NEARBY QUESTS</p>
                <div class="paddingContent">
                    <div id="dummy_map">
                        <a href="quests.php" id="navToMapPage">GO TO QUESTS</a>
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
        
        const sock = new WebSocket('ws://192.168.1.69:8080/ws');
        
        const distanceCalc = (inputLat1,inputLng1,inputLat2,inputLng2) => {
            let lat2 = inputLat2/57.29577951;
            let lng2 = inputLng2/57.29577951;
            let lat1 = inputLat1/57.29577951;
            let lng1 = inputLng1/57.29577951;
            let deltaInMiles = 3963.0 * Math.acos((Math.sin(lat2) * Math.sin(lat1)) + Math.cos(lat2) * Math.cos(lat1) * Math.cos(lng1 - lng2));
            return deltaInMiles;
        }
        
        sock.addEventListener("message", async (event) => {
            console.clear();
            positionData = event.data.split(',');
            console.log(`Position: ${positionData[0]},${positionData[1]}`);
            console.log(`Accuracy: ${parseFloat(positionData[2]).toFixed(2)} meters`);
            console.log(`Speed: ${(positionData[3] * 2.237).toFixed(4)} mi/h`);
            
            let dateToExpire = new Date(new Date().getTime() + (24 * 60 * 60 * 1000));
            dateToExpire.setSeconds(0);
            dateToExpire.setMinutes(0);
            dateToExpire.setHours(0);
            
            if(Cookies.get('dayDistanceTraveled')){
                let distanceFromLastKnownPosition = distanceCalc(Cookies.get("latestLat"),Cookies.get("latestLng"),parseFloat(positionData[0]),parseFloat(positionData[1]));
                let distanceToAdd = parseFloat(Cookies.get('dayDistanceTraveled'));
                console.log(`Distance moved since last update: ${(distanceFromLastKnownPosition).toFixed(3)} mi`);
                if(distanceFromLastKnownPosition >= 0.001 && parseFloat(positionData[3]) >= 0.44704 && parseFloat(positionData[3]) <= 6.7056) {
                    isMoving += 1
                    if (isMoving > 2) {
                        Cookies.set('dayDistanceTraveled', (distanceFromLastKnownPosition + distanceToAdd).toFixed(3), {expires: dateToExpire});
                        movementStatus = "Moving"
                        window.location = 'app.php';
                    }
                } else {
                    movementStatus = "Idle"
                    isMoving = 0
                }
                console.log(`Status: ${movementStatus}`)
                let currentStepsNo = Math.round((parseFloat(Cookies.get('dayDistanceTraveled')) * 1609) / 0.75);
                Cookies.set('dayStepsTaken', currentStepsNo, {expires: dateToExpire});
            } else {
                Cookies.set("dayDistanceTraveled", 0, {expires: dateToExpire});
                document.location='app.php';
            }
            
            let lastRecievedLat = parseFloat(positionData[0]);
            let lastRecievedLng = parseFloat(positionData[1]);
            
            Cookies.set("latestLat", lastRecievedLat);
            Cookies.set("latestLng", lastRecievedLng);
        });
        
        stepsBar = new ldBar('.stepsBar', {
            "stroke": "#f5bb26",
            "stroke-width": "10",
            "value": "0",
            "path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
        });
        // document.querySelector('.stepsBar .ldBar-label').innerHTML = "350/1000<br>STEPS";
        caloriesBar = new ldBar('.caloriesBar', {
            "stroke": "#f5bb26",
            "stroke-width": "10",
            "value": "0",
            "path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
        });
        // document.querySelector('.caloriesBar .ldBar-label').innerHTML = "467/870<br>CALORIES";
        milesBar = new ldBar('.milesBar', {
            "stroke": "#f5bb26",
            "stroke-width": "10",
            "value": "0",
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
        waitForEl('#dummy_map', function(){
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