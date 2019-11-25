importScripts('https://storage.googleapis.com/workbox-cdn/releases/4.3.1/workbox-sw.js');

if (workbox) {
	workbox.precaching.precacheAndRoute([
	]);//End precache

	workbox.googleAnalytics.initialize({
		parameterOverrides: {
			cd1: "Offline"
		}
	});

	//Ignore preview and admin files
	workbox.routing.registerRoute(
	  /wp-admin(.*)|(.*)preview=true(.*)|(.*)\/plugins\/(.*)/,
	  new workbox.strategies.NetworkOnly()
	);

	//Stale while revalidate for js and css that are not in precache
	workbox.routing.registerRoute(
	  /\.(?:js|css)$/,
	  new workbox.strategies.StaleWhileRevalidate()
	);

	//Limit to 50 images in cache
	workbox.routing.registerRoute(
	  /\.(?:png|jpg|svg|gif|webp)(.*)$/,
	  new workbox.strategies.CacheFirst({
	    cacheName: 'images-cache',
	    plugins: [
				new workbox.expiration.Plugin({
					maxEntries: 50,
					maxAgeSeconds: 60 * 60 * 24,
					purgeOnQuotaError: true
				})
			]
	  })
	);

	//Cache wp pages
	workbox.routing.registerRoute(
	  new RegExp('/(.*)/'),
	  new workbox.strategies.NetworkFirst({
	    cacheName: 'pages-cache',
	    plugins: [
				new workbox.expiration.Plugin({
					maxEntries: 20,
					maxAgeSeconds: 60 * 60 * 24,
					purgeOnQuotaError: true
				}),
	      new workbox.cacheableResponse.Plugin({
	        statuses: [0, 200]
	      })
	    ]
	  })
	);

}
