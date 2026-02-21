(function() {
    console.log("Collector script is live!");
    const endpoint = "https://collector.gmvt135.site/log.php"; 
    // Use one consistent session ID
    const sessionID = crypto.randomUUID() || Math.random().toString(36).substring(7);

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
        const cssCheck = (function() {
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
            screenDim: `${screen.width}x${screen.height}`,
            jsEnabled: true,
            cssEnabled: cssCheck
        };

        const perf = window.performance.timing;
        const performanceData = {
            type: "performance",
            sessionId: sessionID,
            totalLoadTimeMs: perf.loadEventEnd - perf.navigationStart
        };

        sendData(staticData);
        sendData(performanceData);
    });

    // 3. ACTIVITY DATA
    const pushActivity = (act) => {
        sendData({
            ...act,
            type: "activity",
            sessionId: sessionID,
            timestamp: Date.now(),
            page: window.location.pathname
        });
    };

    // Activity Listeners
    document.addEventListener('click', e => pushActivity({ event: 'click', x: e.clientX, y: e.clientY }));
    
    // Throttled Scroll
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (!scrollTimeout) {
            pushActivity({ event: 'scroll', y: window.scrollY });
            scrollTimeout = setTimeout(() => { scrollTimeout = null; }, 2000);
        }
    });

    // Error Reporting
    window.onerror = (msg, url, line) => pushActivity({ event: 'error', msg: msg, line: line });

    console.log("Collector script is live!222");
})();