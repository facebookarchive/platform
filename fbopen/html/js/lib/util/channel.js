/******BEGIN LICENSE BLOCK*******
* 
* Common Public Attribution License Version 1.0.
*
* The contents of this file are subject to the Common Public Attribution 
* License Version 1.0 (the "License") you may not use this file except in 
* compliance with the License. You may obtain a copy of the License at
* http://developers.facebook.com/fbopen/cpal.html. The License is based 
* on the Mozilla Public License Version 1.1 but Sections 14 and 15 have 
* been added to cover use of software over a computer network and provide 
* for limited attribution for the Original Developer. In addition, Exhibit A 
* has been modified to be consistent with Exhibit B.
* Software distributed under the License is distributed on an "AS IS" basis, 
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License 
* for the specific language governing rights and limitations under the License.
* The Original Code is Facebook Open Platform.
* The Original Developer is the Initial Developer.
* The Initial Developer of the Original Code is Facebook, Inc.  All portions 
* of the code written by Facebook, Inc are 
* Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
*
*
********END LICENSE BLOCK*********/

/**
 * PRESENCE CHANNEL MANAGER
 *
 * Initial presence setup.  Starts the iframe that talks to the channel
 * servers (via iframex.js).  The iframe passes messages up to
 * handleChannelMsg, from which we dispatch it to the appropriate handler
 * for that channel.
 *
 * @author  jwiseman
 */

// There should be a matching copy of this in the iframe (iframex.js)
var ChannelRebuildReasons = {
  Unknown                : 0,
  AsyncError             : 1,
  TooLong                : 2,
  Refresh                : 3,
  RefreshDelay           : 4,
  UIRestart              : 5,
  NeedSeq                : 6,
  PrevFailed             : 7,
  IFrameLoadGiveUp       : 8,
  IFrameLoadRetry        : 9,
  IFrameLoadRetryWorked  : 10
};

function fbChannelManager(user) {
  this.user = user;
  this.iframeCheckTime = 16000;  // 16 secs, iframe load time window
  this.iframeCheckRetryTime = 16000; // 16 secs, iframe load time window after
                                     // initial failure.
  this.defaultRetryInterval = 3000; // 3 sec, time between retries
  this.maxRetryInterval = 60000;    // 1 min, max time between retries
  this.iframeLoadMaxRetries = 1;    // number of iframe load retries before giving up


  this._init();
}

// receives messages from the channel iframe
function handleChanneliFrameMessageEvent(ev) {
  // Only accept messages from facebook domain (the iframe should have lowered
  // it's domain).
  // Opera sets the 'domain' and respects the iframe's domain lowering.
  // Webkit sets the 'domain' but ignores the domain lowering.
  // FF3 sets the 'origin' as protocol://fulldomain.
  var domain = (ev.domain || ev.origin);
  if (domain.substring(domain.length-12) != 'facebook.com') {
    return;
  }
  handleChanneliFrameMessage(ev.data);
}

function handleChanneliFrameMessage(iframeMsgStr) {
  channelManager.handleiFrameMessage(eval('('+iframeMsgStr+')'));
}

fbChannelManager.prototype = {
  _init: function() {
    this.channels = {};
    // NOTE: For now, we use the same iframe for all channels.  Should this
    // be a problem, we'll need to store the host/port/url with each channel
    // structure.
    this.iframeURL = this.iframeHost = this.iframePort = null;

    // whether the next x request should be considered a sign of activity.
    this.isActionRequest = true;

    this.isReady = false;
    this.isRebuilding = false;
    this.iframeIsLoaded = false;
    this.iframeEverLoaded = false;
    this.iframeCheckFailedCount = 0;
    this.permaShutdown = false;
    this.shouldClearSubdomain = false;
    this.retryInterval = 0;

    this.subframe = ge('channel_iframe');

    // The iframe message poster is null until the iframe says it's alive.
    this.postMessage = null;

    // We need each browser window/tab to use a unique subdomain for the
    // channel iframe, or else we'll run into browser persistent connection
    // limits (usually 2).  We store a list of 'used subdomains' in our
    // channel cookie, and, on page load, always choose the lowest available
    // integer.  On page unload we'll clear this page's subdomain from the
    // list.
    var channelData = presenceCookieManager.getSubCookie('ch');
    this.iframeSubdomain = 0;
    if (channelData && channelData.sub) {
      for (var i = 0; i < channelData.sub.length; i++) {
        if (!channelData.sub[i]) {
          this.iframeSubdomain = i;
          break;
        }
      }
      // if we reached the end, grab a new number
      if (i == channelData.sub.length) {
        this.iframeSubdomain = channelData.sub.length;
      }
    }

    // for safari 3.0.4 , we can't access window state from the
    // iframe, so we need to set any messages as instance variables and
    // poll for their existence (see handleChannelMsg and
    // handleChannelMsgCheck).
    var safari = ua.safari();
    this.pollForMessages = (safari > 523 && safari < 525);
    // IE6 and IE7 don't properly kill the x request when loading a new page,
    // and it blocks on the next page when fetching the iframe markup from the
    // same domain.  Force a random subdomain to avoid this issue.
    // Unfortunately, this means the iframe markup can't be cached properly.
    this.useRandomSubdomain = (ua.ie() > 0);

    // Some browsers support cross-document messaging, which we should use
    // instead of direct parent<->iframe javascript access.
    // see http://www.whatwg.org/specs/web-apps/current-work/
    if (document.postMessage || window.postMessage) {
      document.addEventListener('message', handleChanneliFrameMessageEvent, false);
    }

    // register to store our channel info in the presence cookie
    presenceCookieManager.register('ch', this._getCookieInfo.bind(this));

    // on unload we want to clear our subdomain from the cookie, so the next
    // page can claim this subdomain.
    if (ua.firefox()) {
      // firefox doesn't allow setting a cookie onload, so we use
      // onbeforeunload instead.  it's not quite as good, as the unload
      // might get cancelled after we clear our subdomain, potentially
      // leaving more than one page with the same subdomain.  but that should
      // be very rare.
      onbeforeunloadRegister(this._onUnload.bind(this));
    } else {
      onunloadRegister(this._onUnload.bind(this));
    }
  },

  sendiFrameMessage: function(msg) {
    // Ignore if the iframe was never built properly.
    if (!this.postMessage) {
      return;
    }
    var msgStr = JSON.encode(msg);
    try {
      this.postMessage(msgStr);
    } catch(e) {
      presence.error('channel: error sending message "'+msgStr+'" to iframe: '+e.toString());
    }
  },

  handleiFrameMessage: function(iframeMsg) {
    if (iframeMsg.type == 'init') {
      this.iframeLoaded();
    } else if (iframeMsg.type == 'channelMsg') {
      this.handleChannelMsg(iframeMsg.channel, iframeMsg.seq, iframeMsg.msg);
    }
  },

  _onUnload: function() {
    this.shouldClearSubdomain = true;
    // We need a doSync so the other windows know that the subdomain array
    // changed in the cookie.
    presence.doSync();
  },

  // Adds a new handler for messages on the given channel, starting with the
  // message at the given sequence number.  Start handler is called when the
  // channel iframe first loads.  shutdownHandler is called if the channel
  // connection doesn't work.  restartHandler is called if/when it works again
  // after a shutdown.
  addChannel: function(channel, seq, msgHandler, startHandler,
                       shutdownHandler, restartHandler) {
    this.channels[channel] = {
      'currentSeq'     : seq,
      'nextSeq'        : 0,
      'msgHandler'     : msgHandler,
      'startHandler'   : startHandler,
      'shutdownHandler': shutdownHandler,
      'restartHandler' : restartHandler
    };
  },

  isLowestSubdomain: function() {
    var channelData = presenceCookieManager.getSubCookie('ch');
    if (!channelData || !channelData.sub) {
      return true;
    }

    for (var i = 0; i < channelData.sub.length; i++) {
      if (channelData.sub[i]) {
        return (i == this.iframeSubdomain);
      }
    }
  },

  // store each channel's current seq in the cookie
  _getCookieInfo: function() {
    var data = {};
    // only store things if the connection was setup without issue
    if (this.iframeHost && this.iframePort) {
      // host and port
      data.h = this.iframeHost;
      data.p = this.iframePort;

      // array of subdomains
      var channelData = presenceCookieManager.getSubCookie('ch');
      var subdomains = (channelData && channelData.sub)
                       ? channelData.sub : [];
      var oldLength = subdomains.length;
      if (this.shouldClearSubdomain) {
        // this is triggered on page unload.  clear out the subdomain from the
        // array, so future pages can use it.
        subdomains[this.iframeSubdomain] = 0;
      } else {
        // store our subdomain so other pages don't use it.
        subdomains[this.iframeSubdomain] = 1;
        // we may have introduced undefined values if the subdomains array
        // wasn't big enough before.  change them to zeroes so the array is
        // more compact when serialized.
        for (var i = oldLength; i <= this.iframeSubdomain; i++) {
          if (!subdomains[i]) {
            subdomains[i] = 0;
          }
        }
      }
      data.sub = subdomains;

      // seq for each channel we're managing
      for (var channel in this.channels) {
        data[channel] = this.channels[channel].currentSeq;
      }
    }
    return data;
  },

  // Stops all channel actions, from here on out.  Nothing can get things
  // started again.
  stop: function() {
    this.stopped = true;
    this.setReady(false);
  },

  setReady: function(isReady) {
    this.isReady = isReady;
    var msg = {
      'type'            : 'isReady',
      'isReady'         : isReady,
      'isActionRequest' : this.isActionRequest
    };
    if (isReady && this.isActionRequest) {
      this.isActionRequest = false;
    }

    if (isReady) {
      // pass the latest channels if we're going ready
      msg['channels'] = this.channels;
    }
    this.sendiFrameMessage(msg);
  },

  // Set isReady to false if you don't want to start the actual x requests.
  iframeLoad: function(path, host, port, isReady) {
    // we might not want to start the actual connections (isReady==false),
    // for example if the user is invisible.
    this.isReady = isReady;

    this.iframeIsLoaded = false;

    this.iframePath = path;
    this.iframeHost = host;
    this.iframePort = port;

    var subdomain = this.iframeSubdomain;
    if (this.useRandomSubdomain) {
      subdomain += '' + rand32();
    }

    var url = 'http://'+subdomain+'.'+this.iframeHost+
              '.facebook.com:'+this.iframePort+this.iframePath;

    // check the iframe has loaded after a while
    setTimeout(this._iframeCheck.bind(this), this.iframeCheckTime);

    // set the iframe location any way we can
    if (this.subframe.contentDocument) {
      try {
        this.subframe.contentDocument.location.replace(url);
      } catch(e) {
        presence.error('channel: error setting location: '+e.toString());
      }
    } else if (this.subframe.contentWindow) {
      this.subframe.src = url;
    } else if (this.subframe.document) {
      this.subframe.src = url;
    } else {
      presence.error('channel: error setting subframe url');
    }

    presence.debug('channel: done with iframeLoad, subframe sent to ' + url);
  },

  // called once the iframe is loaded.
  iframeLoaded: function() {
    if (!this.iframeIsLoaded) {
      this.iframeIsLoaded = true;
      // for testing
      /*
      if (ge('presence_popout_header')) {
        set_inner_html($('presence_popout_header'), '<span style="color:white">'+this.iframeSubdomain+' loaded!</span>');
      }
      */

      // Setup iframe message sender.
      if (window.postMessage) {
        // Opera 9.5+, FF3, Webkit use window.postMessage.
        this.postMessage = this.subframe.contentWindow.postMessage.bind(this.subframe.contentWindow);
      } else if (document.postMessage) {
        // Older Opera uses document.postMessage.
        this.postMessage = window.parent.document.postMessage.bind(window.parent.document);
        this.postMessage = this.subframe.contentDocument.postMessage.bind(this.subframe.contentDocument);
      } else {
        // Other browsers.
        this.postMessage = this.subframe.contentWindow.handleChannelParentMessage.bind(this.subframe.contentWindow);
      }

      // sync state to iframe
      this.setReady(this.isReady);

      if (this.pollForMessages) {
        this.msgCheckInterval = setInterval(this.handleChannelMsgCheck.bind(this), 100);
      }

      if (this.iframeCheckFailedCount) {
        // call restart handlers if we had shut them down due to an iframe
        // check failure.
        for (var c in this.channels) {
          this.channels[c].restartHandler(false);
        }

        // log this successful iframe loading retry
        this._sendDummyReconnect(ChannelRebuildReasons.IFrameLoadRetryWorked);
      } else {
        // normal iframe load.  call start handlers.
        for (var c in this.channels) {
          this.channels[c].startHandler();
        }
      }

      this.iframeCheckFailedCount = 0;
      this.iframeEverLoaded = true;
    }
  },

  // check if the iframe is loaded yet.  if not, shutdown all handlers.
  _iframeCheck: function() {
    if (!this.iframeIsLoaded) {
      presence.error("channel: uplink iframe never loaded; shutting down");
      this.iframeCheckFailedCount++;

      // clear the channel info from the cookie, so we do a reconnect next page
      this.iframeHost = this.iframePort = 0;
      presenceCookieManager.store();

      if (this.iframeCheckFailedCount <= this.iframeLoadMaxRetries) {
        // try once more to load the iframe. (forcing the path to null will
        // make sure we get through the check in _rebuildResponse).
        this.iframeCheckTime = this.iframeCheckRetryTime;
        this.iframePath = null;
        this.rebuild(ChannelRebuildReasons.IFrameLoadRetry);
      } else {
        // we've failed twice, just give up.  but post a dummy reconnect to
        // log the problem.
        for (var c in this.channels) {
          this.channels[c].shutdownHandler();
        }
        this._sendDummyReconnect(ChannelRebuildReasons.IFrameLoadGiveUp);
      }
    } else {
      presence.debug('channel: uplink iframe loaded fine');
    }
  },

  _sendDummyReconnect: function(reason) {
    AsyncRequest.pingURI('/ajax/presence/reconnect.php',
      { reason: reason, iframe_loaded: this.iframeEverLoaded });
  },

  _rebuildResponse: function(response) {
    var rebuildInfo = response.getPayload();
    var channel = rebuildInfo.user_channel;
    presence.debug('got rebuild response with channel '+channel+', seq '+rebuildInfo.seq+', host '+rebuildInfo.host+', port '+rebuildInfo.port);
    this.channels[channel].currentSeq = rebuildInfo.seq;
    this.channels[channel].nextSeq = 0;

    this.isRebuilding = false;

    if (rebuildInfo.path != this.iframePath ||
        rebuildInfo.host != this.iframeHost ||
        rebuildInfo.port != this.iframePort) {
      // if the iframe url is different, we need to reload it.
      this.iframeLoad(rebuildInfo.path, rebuildInfo.host, rebuildInfo.port, true);
    } else {
      this.setReady(true);
    }

    // update the channel cookie
    presenceCookieManager.store();

    // breaks our handler abstraction. boo!

    presenceUpdater.pauseUpdate();

    if (typeof statusControl != 'undefined') {
      statusControl.setVisibility(rebuildInfo.visibility);
    }

    // call the restart handlers
    for (var c in this.channels) {
      this.channels[c].restartHandler(true);
    }

    // Force a presence update, but ensure only one (since the restart handlers
    // may want to force updates as well).
    presenceUpdater.resumeUpdate();
  },

  _retryRebuild: function(reason) {
    if (this.retryInterval == 0) {
      // next retry after a set chunk of time
      this.retryInterval = this.defaultRetryInterval;
    } else {
      // then, double the time between attempts, up to the max
      this.retryInterval *= 2;
      if (this.retryInterval >= this.maxRetryInterval) {
        this.retryInterval = this.maxRetryInterval;
      }
    }

    presence.warn('manager trying again in '+(this.retryInterval*0.001)+' secs');
    setTimeout(this._rebuildSend.bind(this, reason), this.retryInterval);
  },

  _rebuildError: function(reason, response) {
    presence.error('got rebuild error: '+response.getErrorDescription());

    // breaks our handler abstraction. boo!
    if (presence.checkLoginError(response) ||
        presence.checkMaintenanceError(response)) {
      // if we got a login or maintenance error, don't try again.
      presence.warn('manager not trying again');
    } else {
      // retry, and change the reason to PrevFailed
      this._retryRebuild(ChannelRebuildReasons.PrevFailed);
    }
  },

  _rebuildTransportError: function(reason, response) {
    presence.error('got rebuild transport error: '+response.getErrorDescription());
    this._retryRebuild(reason);
  },

  _rebuildSend: function(reason) {
    // make sure we have a valid reason
    if (typeof reason != 'number') {
      reason = ChannelRebuildReasons.Unknown;
    }

    presence.debug('channel: sending rebuild');
    new AsyncRequest()
      .setHandler(this._rebuildResponse.bind(this))
      .setErrorHandler(this._rebuildError.bind(this, reason))
      .setTransportErrorHandler(this._rebuildTransportError.bind(this, reason))
      .setOption('suppressErrorAlerts', true)
      .setURI('/ajax/presence/reconnect.php')
      .setData({ reason: reason, iframe_loaded: this.iframeEverLoaded })
      .send();
  },

  // called from handleChannelMsg, when the iframe's connection to the
  // channel has busted and we need to rebuild it from scratch.
  rebuild: function(reason) {
    if (this.stopped) {
      return;
    }

    // if we're already restarting, nothing to do
    if (this.isRebuilding) {
      presence.debug('channel: rebuild called, but already rebuilding');
      return;
    }

    this.setReady(false);  // tell the iframe we're not ready
    this.isRebuilding = true;
    presence.debug('channel: rebuilding');

    if (reason == ChannelRebuildReasons.RefreshDelay) {
      // the channel told us to delay before rebuilding
      this.retryInterval = this.maxRetryInterval;
    } else {
      this.retryInterval = 0;
    }

    setTimeout(this._rebuildSend.bind(this, reason), this.retryInterval);
  },

  // called periodically to check if there's a msg to dispatch
  handleChannelMsgCheck: function() {
    if (this.pendingMsg) {
      this._handleChannelMsg(this.pendingMsg.channel, this.pendingMsg.seq,
                             this.pendingMsg.msg);
      this.pendingMsg = null;
    }
  },

  handleChannelMsg: function(channel, seq, msg) {
    if (this.pollForMessages) {
      this.pendingMsg = { channel: channel,
                              seq: seq,
                              msg: msg };
    } else {
      this._handleChannelMsg(channel, seq, msg);
    }
  },

  // handles incoming messages from the channel server, parsing and
  // dispatching to channel-specific handlers. calls special handlers
  // for shutdown or restart.
  _handleChannelMsg: function(channel, seq, msg) {
    if (msg.type == 'shutdown' || msg.type == 'permaShutdown') {
      // if the page has unloaded or we've already perma-shutdown, just ignore.
      if (!window.loaded || this.permaShutdown) {
        return;
      }

      if (msg.type == 'permaShutdown') {
        presence.warn('channel: got permaShutdown for all channels');
        this.permaShutdown = true;
      } else {
        presence.warn('channel: got shutdown for all channels');
        // rebuild the connection with the channel server
        this.rebuild(msg.reason);
      }

      // call the shutdown handlers
      for (var c in this.channels) {
        this.channels[c].shutdownHandler(true);
      }
    } else {
      this.channels[channel].currentSeq++;

      // in firefox we get duplicate messages when the user goes back to
      // a page and the old iframe gets something and then a new iframe
      // gets the same thing
      var nextSeq;
      if ((nextSeq = this.channels[channel].nextSeq) && seq < nextSeq) {
        presence.warn('ignoring a duplicate message ('+seq+')<('+nextSeq+') on '+channel);
        return;
      }
      this.channels[channel].nextSeq = parseInt(seq) + 1;


      // Call the channel-specific handler.  Wrap it in pause/resumeSync so
      // we only do one doSync, which is necessary to store the new sequence
      // number and likely other state updated by this msg.
      presence.pauseSync();
      try {
        this.channels[channel].msgHandler(channel, msg);
      } catch (e) {
        presence.error('error in channel handlers: '+e.toString()+', msg: '+msg);
      }
      presence.resumeSync();
    }
  }
};

