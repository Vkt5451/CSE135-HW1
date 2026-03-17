// auth-guard.js
(function() {
    if (localStorage.getItem('isLoggedIn') !== 'true') {
        // Redirect to login if the flag is missing
        window.location.href = "../login/login.html";
    }
})();