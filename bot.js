var irc = require('irc');
var https = require('https');
var Bitly = require('bitly');

var bitly = new Bitly('', '');
var lpid = 0;
var ltid = 0;

client = new irc.Client( 'localhost', 'Forum', {
	userName: 'Forum',
	realName: 'Forum',
	debug: false,
	showErrors: false,
	autoRejoin: true,
	autoConnect: true,
	port: 6667,
	channels: [
		'#relaychan',
	],
});

function doProcess( data ) {
	try {
		data = JSON.parse( data );
		// Threads
		for( var x=0; x < data['t'].length; x++ ) {
		(function(x) {
			if( ltid > 0 ) {
				bitly.shorten( data['t'][x]['url'], function(err, response) {
					if( !err && response.data.url ) {
						data['t'][x]['url'] = response.data.url;
					}

					try {
						client.say(
							'#relaychan', 
							'New thread by ' + data['t'][x]['username'] +
							' (' + data['t'][x]['title'] + ')' +
							' - ' + data['t'][x]['url']
						);
					} catch( e ) {}
				});
			}
			if( parseInt( data['t'][x]['id'] ) > ltid ) {
				ltid = parseInt( data['t'][x]['id'] );
			}
		})(x);
		}

		// Posts
		for( var xx=0; xx < data['p'].length; xx++ ) {
		(function(xx) {
			if( lpid > 0 ) {
				bitly.shorten( data['p'][xx]['url'], function(err, response) {
					if( !err && response.data.url ) {
						data['p'][xx]['url'] = response.data.url;
					}

					try {
						client.say(
							'#relaychan', 
							'New post by ' + data['p'][xx]['username'] +
							' in ' + data['p'][xx]['title'] +
							' - ' + data['p'][xx]['url']
						);
					} catch( e ) {}
				});

			}
			if( parseInt( data['p'][xx]['id'] ) > lpid ) {
				lpid = parseInt( data['p'][xx]['id'] );
			}
		})(xx);
		}
	} catch( e ) {
//		console.error( e );
	}
}

function doRun() {
	args = 'key=something';

	if( lpid > 0 ) {
		args += '&lastpid=' + lpid;
	}

	if( ltid > 0 ) {
		args += '&lasttid=' + ltid;
	}

	https.get({
		host: 'www.forum.com',
		path: '/forumbot.php?' + args,
	}, function( response ) {
		response.on('data', function( data ) {
			doProcess( data );
		});
	}).on('error', function(e) {
		console.error(e);
	});
	setTimeout( doRun, 5000 );
}

client.addListener('motd', function(message) {
	doRun();
});

client.addListener('error', function(message) {
	console.error('ERROR: %s: %s', message.command, message.args.join(' '));
});
