<?php header('Content-Type: text/javascript'); ?>
const locationObj = self.location;
importScripts(`${locationObj.origin}/sw.js`);
importScripts('https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js');
