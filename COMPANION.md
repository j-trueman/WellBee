## The WellBee Companion App

As stated in the main readme of the project, the companion app works by binding an Http Server to the devices network address and listens for clients connecting. When a client does connect, it upgrades that connection to a WebSocket and starts sending location data to it. This location data is obtained using the [Flutter Location](https://pub.dev/packages/location) library and is sent in the form of a list of comma separated values.

### The Code

The first of the app is just setting up permissions for the flutter location library and so I won't include it here, the important part is the http server which is very simple so I can put the whole thing here.
```javascript
//Bind the server to port 8080 of the devices network address
var server = await HttpServer.bind(InternetAddress.anyIPv4, 8080);
//Listen for clients trying to connect
print('Listening on ${InternetAddress.anyIPv4}:${server.port}');
//When an http request is recieved
await for (HttpRequest request in server) {
    //If the client requests the /ws uri
    if (request.uri.path == '/ws') {
        // Upgrade an HttpRequest to a WebSocket connection
        await WebSocketTransformer.upgrade(request).then((websocket) {
            print('Client connected!');
            bool IsFirstMessage = true;
            //When the device sensors send an update
            location.onLocationChanged.listen((LocationData currentLocation) {
                //Send this data to the connected client
                websocket.add('${currentLocation.latitude},${currentLocation.longitude},${currentLocation.accuracy},${currentLocation.speed},${IsFirstMessage}');
                IsFirstMessage = false;
            });
        });
    } else {
        //Deny the connection if the uri is not "/ws"
        request.response.statusCode = HttpStatus.forbidden;
        request.response.close();
    }
}
```
You can download and use the apk from the [releases](https://github.com/j-trueman/WellBee/releases) tab if you want to use it yourself.
