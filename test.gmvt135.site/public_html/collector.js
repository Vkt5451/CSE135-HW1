(function () {
    console.log("Collector script is live1!");
    const endpoint = "https://collector.gmvt135.site/log.php";
    // Use one consistent session ID
    function getSessionId() {
        let sid = sessionStorage.getItem('_collector_sid');
        if (!sid) {
            sid =
                (crypto.randomUUID && crypto.randomUUID()) ||
                (Math.random().toString(36).substring(2) + Date.now().toString(36));
                sessionStorage.setItem('_collector_sid', sid);
        }
        return sid;
    }

    const sessionID = getSessionId();

    // --- THE MASTER SEND FUNCTION ---
    function sendData(data) {
        fetch(endpoint, {
            method: "POST",
            mode: "cors",
            body: JSON.stringify(data), // Sends the WHOLE object (static, perf, or activity)
            headers: { "Content-Type": "application/json" }
        }).catch(err => console.error("Data transmission failed:", err));
    }

    // 1 & 2. STATIC & PERFORMANCE DATA
    window.addEventListener('load', () => {
        const cssCheck = (function () {
            try {
                const test = document.createElement('div');
                test.style.display = 'none';
                document.body.appendChild(test);
                const style = window.getComputedStyle(test).display;
                document.body.removeChild(test);
                return style === 'none';
            } catch (e) { return false; }
        })();

        const staticData = {
            type: "static",
            sessionId: sessionID,
            userAgent: navigator.userAgent,
            language: navigator.language,
            cookiesEnabled: navigator.cookieEnabled,
            screenDim: `${screen.width}x${screen.height}`,
            windowDim: `${window.innerWidth}x${window.innerHeight}`,
            connectionType: navigator.connection?.effectiveType || "unknown",
            jsEnabled: true,
            cssEnabled: cssCheck,
            imagesEnabled: "TBD" // set after test
        };

        (function detectImagesEnabled() {
            const img = new Image();

            img.onload = () => {
                staticData.imagesEnabled = true;
                sendData(staticData); 
            };

            img.onerror = () => {
                staticData.imagesEnabled = false;
                sendData(staticData); 
            };

            img.src = "https://test.gmvt135.site/assets/nft-coin.png?cacheBust=" + Date.now();
        })();
       
       
        console.group("Static Data Collection");
        console.table(staticData);
        console.groupEnd();

        
        setTimeout(() => {
            const nav = performance.getEntriesByType("navigation")[0];

            const performanceData = {
                type: "performance",
                sessionId: sessionID,

                startLoad: nav.fetchStart,
                endLoad: nav.loadEventEnd,
                totalLoadTimeMs: Math.round(nav.loadEventEnd - nav.fetchStart),

                navTiming: nav.toJSON ? nav.toJSON() : {
                fetchStart: nav.fetchStart,
                loadEventEnd: nav.loadEventEnd,
                domInteractive: nav.domInteractive,
                domComplete: nav.domComplete,
                responseStart: nav.responseStart,
                responseEnd: nav.responseEnd,
                requestStart: nav.requestStart,
                connectStart: nav.connectStart,
                connectEnd: nav.connectEnd,
                domainLookupStart: nav.domainLookupStart,
                domainLookupEnd: nav.domainLookupEnd,
                redirectStart: nav.redirectStart,
                redirectEnd: nav.redirectEnd
                }
            };

            console.group("Performance Data Collection");
            console.table(performanceData);
            console.groupEnd();

            sendData(performanceData);
            }, 50);

        });
    // 3. ACTIVITY DATA
    const pushActivity = (act) => {
        sendData({
        ...act,
        type: "activity",
        sessionId: sessionID,

        //base page fields
        url: window.location.href,
        title: document.title,
        referrer: document.referrer || "direct",

        // timestamps
        timestamp: Date.now(),
        timestampISO: new Date().toISOString(),

        page: window.location.pathname
     });
    };
    // ---- PAGE ENTER / LEAVE ----
    const pagePath = window.location.pathname;
    const pageEnterTs = Date.now();

    pushActivity({
    event: "page_enter",
    pageEnterTs,
    });

    function logPageLeave() {
        const leaveTs = Date.now();
        const durationMs = leaveTs - pageEnterTs;

        const payloadObj = {
            type: "activity",
            sessionId: sessionID,
            event: "page_leave",

            url: window.location.href,
            title: document.title,
            referrer: document.referrer || "direct",

            timestamp: leaveTs,
            timestampISO: new Date().toISOString(),

            page: pagePath,
            pageEnterTs,
            durationMs
            };

    const payload = JSON.stringify(payloadObj);

    if (navigator.sendBeacon) {
        navigator.sendBeacon(endpoint, new Blob([payload], { type: "application/json" }));
    } else {
        fetch(endpoint, {
        method: "POST",
        mode: "cors",
        headers: { "Content-Type": "application/json" },
        body: payload,
        keepalive: true
        });
    }
    }

    window.addEventListener("pagehide", logPageLeave);

        let idleTimer;
        let idleStart = null;
        let isIdle = false;

        function startIdleCountdown() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            // mark the moment we became idle, but don't log yet
            if (!isIdle) {
            isIdle = true;
            idleStart = Date.now();
            }
        }, 2000);
        }

        function userDidSomething() {
        const now = Date.now();

        // if we were idle, log ONE event that contains everything
        if (isIdle && idleStart) {
            pushActivity({
            event: "idle",
            idleStart: idleStart,
            idleEnd: now,
            idleDurationMs: now - idleStart
            });
        }

        // reset and restart countdown
        isIdle = false;
        idleStart = null;
        startIdleCountdown();
        }

        // any activity counts as not idle
        ["click", "scroll", "keypress", "mousemove"].forEach(evt =>
        document.addEventListener(evt, userDidSomething)
        );

        // start timer on page load
        startIdleCountdown();
    // Activity Listeners
    document.addEventListener('click', e => {
        let buttonName = 'other';
        if (e.button === 0) buttonName = 'left';
        else if (e.button === 1) buttonName = 'middle';
        else if (e.button === 2) buttonName = 'right';

        pushActivity({
            event: 'click',
            x: e.clientX,
            y: e.clientY,
            button: buttonName
        });
    });

    // Throttled Scroll
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (!scrollTimeout) {
            pushActivity({ event: 'scroll', y: window.scrollY });
            scrollTimeout = setTimeout(() => { scrollTimeout = null; }, 2000);
        }
    });
    // Mouse move (throttled)
        let lastMouseLogTs = 0;
        let lastMouseX = null;
        let lastMouseY = null;

        document.addEventListener("mousemove", (e) => {
        const now = Date.now();

        // throttle: only log at most once every 250ms
        if (now - lastMouseLogTs < 250) return;

        // optional: skip tiny jitter
        if (lastMouseX !== null && lastMouseY !== null) {
            const dx = Math.abs(e.clientX - lastMouseX);
            const dy = Math.abs(e.clientY - lastMouseY);
            if (dx < 3 && dy < 3) return; // ignore tiny movement
        }

        lastMouseLogTs = now;
        lastMouseX = e.clientX;
        lastMouseY = e.clientY;

        pushActivity({
            event: "mousemove",
            x: e.clientX,
            y: e.clientY
        });
});

    // Keyboard activity (keydown + keyup)
        document.addEventListener('keydown', (e) => {
        if (e.repeat) return;

        pushActivity({
            event: 'key_down',
            key: e.key,
            code: e.code,
            ctrl: e.ctrlKey,
            shift: e.shiftKey
        });
        });

        document.addEventListener('keyup', (e) => {
        pushActivity({
            event: 'key_up',
            key: e.key,
            code: e.code,
            ctrl: e.ctrlKey,
            shift: e.shiftKey
        });
        });

    // Error Reporting
    //window.onerror = (msg, url, line) => pushActivity({ event: 'error', msg: msg, line: line });
    // JS errors + resource load errors
window.addEventListener('error', (event) => {
  // 1) JS runtime error
  if (event instanceof ErrorEvent) {
    pushActivity({
      event: 'js_error',
      message: event.message,
      source: event.filename,
      line: event.lineno,
      column: event.colno,
      stack: event.error ? event.error.stack : ''
    });
    return;
  }

  // 2) Resource load failure (img/script/link)
  const t = event.target;
  if (t && (t.tagName === 'IMG' || t.tagName === 'SCRIPT' || t.tagName === 'LINK')) {
    pushActivity({
      event: 'resource_error',
      tagName: t.tagName,
      src: t.src || t.href || ''
    });
  }
}, true); 

// Unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
  const r = event.reason;
  pushActivity({
    event: 'promise_rejection',
    message: (r instanceof Error) ? r.message : String(r),
    stack: (r instanceof Error && r.stack) ? r.stack : ''
  });
});
})();