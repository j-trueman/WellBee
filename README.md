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

### The App

[^1]: Step and calorie tracking are rough estimates. Steps being based on the average page length of a human (around 0.75 meters).
[^2]: The gaining of points currently serves no purpose.
[^3]: All modifications were made by us. Read more about FullPageOs [here](https://github.com/guysoft/FullPageOS)
