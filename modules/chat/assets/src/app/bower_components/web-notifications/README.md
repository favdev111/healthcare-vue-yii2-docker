# web-notifications (QBNotification)

## Overview

A wrapper for using the [Web Notifications API](https://www.w3.org/TR/notifications/).

See it in action in folder **test**

[Browsers support see on caniuse](http://caniuse.com/#feat=notifications)

## Install

Install the package via CDN
```html
https://cdn.rawgit.com/QuickBlox/web-notifications/master/qbNotification.js
```
or as NPM package
```javascript
npm i web-notifications --save-dev
```
or as Bower component
```javascript
bower i web-notifications --save-dev
```

# Usage
Module is written like Universal Module Definition, so use everywhere.

Before show a notification you need to get [permission](https://developer.mozilla.org/en-US/docs/Web/API/Notification/permission) from a user by
```javascript
QBNotification.requestPermission(function(state) {
    console.log('State is changed: ' + state);
});
```
Then you should create instance of QBNotification.
```javascript
/**
 * @param {[string]} title [Title of notification. Required.]
 * @param {[type]} options [Additional options: standard params + timeout]
 */
var notify = new QBNotification(title, options);
```
Example options
```javascript
var options = {
    body: 'Body of notification',
    tag: 'chatQB',
    icons: 'image/icons.png',
    timeout: 7,
    closeOnClick: true
}
```

Then you can show notification via
```javascript
notify.show()
```
Also you have:
```javascript
/**
 * [isSupported - check whether Notification API available in environment or not]
 * @return {Boolean} [flag]
 */
QBNotification.isSupported()
```

```javascript
/**
 * [needsPermission - check whether a user provided his permission to show notifications or not]
 * @return {Boolean} [flag]
 */
QBNotification.needsPermission()
```

# Contribution
For test we use JSHint, so do
```javascript
npm install -g jshint
npm test
```
before push yours changes.

# Helpful links
[W3](https://www.w3.org/TR/notifications/),
[whatwg group](https://notifications.spec.whatwg.org/),
[MDN](https://developer.mozilla.org/en/docs/Web/API/notification)

# License
BSD
