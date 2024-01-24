<?php
    session_start();
    $conn = mysqli_connect("localhost", "root", "", "wellbee");
    $dstTraveled = floatval($_COOKIE['dayDistanceTraveled']) ;
    mysqli_query($conn, "UPDATE `uinfo` SET `miles_daily` = $dstTraveled");
?>
<!DOCTYPE html>
<html lang="eu" data-theme="light">
    <head>
        <link rel="stylesheet" type="text/css" href="resources/css/basestyle.css">
        <link rel="stylesheet" type="text/css" href="resources/css/mapstyle.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/progressbar.js/0.6.1/progressbar.min.js" integrity="sha512-7IoDEsIJGxz/gNyJY/0LRtS45wDSvPFXGPuC7Fo4YueWMNOmWKMAllEqo2Im3pgOjeEwsOoieyliRgdkZnY0ow==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
        <script src="./resources/js/map.js"></script>

        <title>WellBee</title>
    </head>
    <body>
        <div id="jquerychecker"></div>
        <section id="content">
            <div id="map">
                </div>
                    <a href='app.php' id='homebtn'></a>
                <a href='#' id="reCenterLocation"></a>
            </section>
        <section id="questSection">
            <?php
                $conn = mysqli_connect("localhost", "root", "", "wellbee");
                
                $get_quests = mysqli_query($conn, "SELECT * FROM `quests` WHERE `completed` = 0");
                while($quest_item = mysqli_fetch_assoc($get_quests)) {
                    $quest_id = $quest_item['quest_id'];
                    $quest_lat = trim($quest_item['coordinates'], ",")[0];
                    $quest_long = trim($quest_item['coordinates'], ",")[1];
                    $quest_name = $quest_item['name'];
                    $quest_description = $quest_item['descript'];
                    $quest_reward = $quest_item['points_reward'];
                    
                    if (isset($_SESSION['questactive'])) {
                        if ($quest_id == $_SESSION['questactive']) {
                            echo"
                            <div class='singleQuestBox' questlat=$quest_lat questlong=$quest_long id='questbox_$quest_id' dstFromUser=''>
                            <div class='questTitleCard'>
                                <p class='questHeader'>$quest_name</p>
                                <p class='questDescription'>$quest_description</p>
                                <p class='questReward'><strong>Reward:</strong> $quest_reward</p>
                                </div>
                                <form class='questButtonBox' method='GET' onsubmit='return submit(this)'>
                                <button type='submit' name='abandonquest' class='abandonquest' value=$quest_id>Abandon Quest</button>
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
                        <div class='singleQuestBox' questlat=$quest_lat questlong=$quest_long id='questbox_$quest_id' dstFromUser=''>
                        <div class='questTitleCard'>
                            <p class='questHeader'>$quest_name</p>
                            <p class='questDescription'>$quest_description</p>
                            <p class='questReward'><strong>Reward:</strong> $quest_reward</p>
                            </div>
                            <form class='questButtonBox' method='GET' onsubmit='return submit(this)'>
                            <button type='submit' name='startquest' class='startquest' value=$quest_id>Start Quest</button>
                            <p class='questDst'></p>
                        </form>
                        </div>
                        ";
                    }
                }
                ?>
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
        mapShouldTrack = true;
        
        const distanceCalc = (inputLat1,inputLng1,inputLat2,inputLng2) => {
            let lat2 = inputLat2/57.29577951;
            let lng2 = inputLng2/57.29577951;
            let lat1 = inputLat1/57.29577951;
            let lng1 = inputLng1/57.29577951;
            let deltaInMiles = 3963.0 * Math.acos((Math.sin(lat2) * Math.sin(lat1)) + Math.cos(lat2) * Math.cos(lat1) * Math.cos(lng1 - lng2));
            return deltaInMiles;
        }
        
        sock.addEventListener("message", async (event) => {
            positionData = event.data.split(',');
            console.log(`Position: ${positionData[0]},${positionData[1]}`);
            console.log(`Accuracy: ${positionData[2]}`);
            console.log(`Speed: ${positionData[3]}`);
            
            if (mapShouldTrack) {
                map.setView([positionData[0], positionData[1]], 16);
                document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-active.svg')";
            } else {
                document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-inactive.svg')";
            }
            
            if (typeof circle != "undefined") {
                circle.removeFrom(map);
            }
            
            circle = L.circle([positionData[0], positionData[1]], {
                color: 'lightblue',
                fillColor: '#42e8f4',
                fillOpacity: 0.5,
                radius: parseInt(positionData[2])
            }).addTo(map);
            
            let dateToExpire = new Date(new Date().getTime() + (24 * 60 * 60 * 1000));
            dateToExpire.setSeconds(0);
            dateToExpire.setMinutes(0);
            dateToExpire.setHours(0);
            
            if(Cookies.get('dayDistanceTraveled')){
                let distanceFromLastKnownPosition = distanceCalc(Cookies.get("latestLat"),Cookies.get("latestLng"),parseFloat(positionData[0]),parseFloat(positionData[1]));
                let distanceToAdd = parseFloat(Cookies.get('dayDistanceTraveled'));
                console.log(distanceFromLastKnownPosition);
                if(distanceFromLastKnownPosition >= 0.01) {
                    Cookies.set('dayDistanceTraveled', (distanceFromLastKnownPosition + distanceToAdd).toFixed(2), {expires: dateToExpire});
                }
                let currentStepsNo = Math.round((parseFloat(Cookies.get('dayDistanceTraveled')) * 1609) / 0.75);
                Cookies.set('dayStepsTaken', currentStepsNo, {expires: dateToExpire});
            } else {
                Cookies.set("dayDistanceTraveled", 0, {expires: dateToExpire});
            }
            
            let lastRecievedLat = parseFloat(positionData[0]);
            let lastRecievedLng = parseFloat(positionData[1]);
            
            Cookies.set("latestLat", lastRecievedLat);
            Cookies.set("latestLng", lastRecievedLng);
            
            
            map.eachLayer(function(layer) {
                if(layer.options.title) {
                    dstToQuest = distanceCalc(layer.getLatLng().lat, layer.getLatLng().lng, positionData[0], positionData[1]);
                    let labelselector = `#questbox_${layer.options.title} .questDst`;
                    let boxselector = `#questbox_${layer.options.title}`;
                    if (!boxselector.includes('undefined')){
                        document.querySelector(boxselector).setAttribute("dstfromuser", dstToQuest.toFixed(2).toString());
                        document.querySelector(labelselector).innerHTML = `${dstToQuest.toFixed(2)}<sub>mi</sub> away`;
                        console.log(`Distance to ${layer.options.title}: ${dstToQuest} miles`);
                    }
                    if(layer.options.title == Cookies.get('activequest') && dstToQuest <= 0.02) {
                        document.querySelector('.completequest').click();
                    }
                }
            })
            
        });
        
        sock.addEventListener("open", () => {
            setInterval(() => {
                sock.send("request");
            }, 5000);
            
        })
        
        waitForEl('#map', function () {
            map = L.map('map').setView(["55.765088665952746", "-4.151738591291236"], 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

                document.getElementById('reCenterLocation').addEventListener("click", () => {
                    if (mapShouldTrack) {
                        mapShouldTrack = false;
                        document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-inactive.svg')"
                    } else {
                        mapShouldTrack = true;
                        document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-active.svg')"
                        if (positionData) {
                            map.setView([positionData[0], positionData[1]], 16);
                        }
                    }
                });
                
                map.addEventListener("drag", function(event) {
                    mapShouldTrack = false;
                    document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-inactive.svg')";
                    
                    document.querySelectorAll('.singleQuestBox').forEach((element) => {
                        element.classList.remove('active'); 
                        element.classList.remove('hidden');
                    });
                });
                
            var myIcon = L.icon({
                iconUrl: '/resources/images/walking_marker.png',
                iconSize: [30,30]
            });
            
                <?php
                $conn = mysqli_connect("localhost", "root", "", "wellbee");
                
                $get_quests = mysqli_query($conn, "SELECT `quest_id`,`coordinates` FROM `quests` WHERE `completed` = 0");
                while($current_quest = mysqli_fetch_assoc($get_quests)) {
                    $quest_id = $current_quest['quest_id'];
                    $coords = explode(",",$current_quest['coordinates']);
                    
                    if (isset($_SESSION['questactive'])) {
                        if ($quest_id == $_SESSION['questactive']) {
                            echo "
                            questmarker_$quest_id = L.marker(['$coords[0]','$coords[1]'], {title: '$quest_id', icon: myIcon, riseOnHover: true}).addTo(map); 
                            
                            questmarker_$quest_id.on('click', function() {
                                map.setView(['$coords[0]','$coords[1]'], 16); 
                                questmarker_$quest_id.getElement().classList.add('activeMarker');
                                document.querySelector('#questbox_$quest_id').classList.add('active');
                                document.querySelector('#questbox_$quest_id').classList.remove('hidden');
                                document.querySelectorAll('.singleQuestBox:not(#questbox_$quest_id)').forEach((element) => {
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
                            questmarker_$quest_id = L.marker(['$coords[0]','$coords[1]'], {title: '$quest_id', icon: myIcon, riseOnHover: true}).addTo(map); 
                            
                            questmarker_$quest_id.on('click', function() {
                                map.setView(['$coords[0]','$coords[1]'], 16); 
                                questmarker_$quest_id.getElement().classList.add('activeMarker');
                                document.querySelector('#questbox_$quest_id').classList.add('active');
                                document.querySelector('#questbox_$quest_id').classList.remove('hidden');
                                document.querySelectorAll('.singleQuestBox:not(#questbox_$quest_id)').forEach((element) => {
                                    element.classList.remove('active'); element.classList.add('hidden')
                                })
                            });
                        ";
                    }
                }
                echo "
                map.addEventListener('click', function() {
                    document.querySelectorAll('.singleQuestBox').forEach((element) => {
                        element.classList.remove('active'); 
                        element.classList.remove('hidden');
                        mapShouldTrack = false;
                        document.getElementById('reCenterLocation').style.backgroundImage = 'url(/resources/images/locator-inactive.svg)';
                    })
                });
                
                document.querySelectorAll('.singleQuestBox').forEach((element) => {
                    element.addEventListener('click', function(e) {
                        document.querySelectorAll('.singleQuestBox').forEach((el) => {
                            el.classList.add('hidden');
                        })
                        this.classList.remove('hidden');
                        map.eachLayer((layer) => {
                            if(layer.options.title == element.id.split('questbox_')[1]) {
                                let markerLat = layer.getLatLng().lat;
                                let markerLng = layer.getLatLng().lng;
                                map.setView([markerLat, markerLng], 16);
                            }
                        })
                    })
                });
                
                let dateToExpire = new Date(new Date().getTime() + (24 * 60 * 60 * 1000));
                dateToExpire.setSeconds(0);
                dateToExpire.setMinutes(0);
                dateToExpire.setHours(0);
                ";
                
                
                if (isset($_GET['startquest'])) {
                    $quest_id = $_GET['startquest'];
                    $_SESSION['questactive'] = $quest_id;
                    mysqli_query($conn, "UPDATE `uinfo` SET `curent_quest_id` = $quest_id");
                    echo "document.querySelector('#questbox_$quest_id').classList.add('active');let arr = document.querySelectorAll('.singleQuestBox:not(#questbox_$quest_id)');arr.forEach((element) => { element.classList.add('hidden');});";
                    echo"window.location = 'quests.php';";
                    echo"Cookies.set('activequest', $quest_id, {expires: dateToExpire});";
                    
                }
                if (isset($_GET['abandonquest'])) {
                    $quest_id = $_GET['abandonquest'];
                    unset($_SESSION['questactive']);
                    mysqli_query($conn, "UPDATE `uinfo` SET `curent_quest_id` = 0");
                    echo"Cookies.set('activequest', 0, {expires: dateToExpire});";            
                    echo"window.location = 'quests.php';";
                }
                if (isset($_GET['completequest'])) {
                    unset($_SESSION['questactive']);
                    mysqli_query($conn, "UPDATE `uinfo` SET `curent_quest_id` = 0");
                    mysqli_query($conn, "UPDATE `quests` SET `completed` = 1 WHERE `quest_id` = $quest_id");
                    echo "alert('You completed the quest! :)');";
                    echo"Cookies.set('activequest', 0, {expires: dateToExpire});";
                    echo"window.location = 'quests.php';";
                }
                
                if(isset($_SESSION['questactive'])) {
                    echo"map.setView(['$coords[0]','$coords[1]'], 16); mapShouldTrack = false;";
                }
                ?>
            
        });
        </script>
</html>