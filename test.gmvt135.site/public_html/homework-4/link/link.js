document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.toggle-btn');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            console.log("Menu button clicked!"); // Check your browser console (F12)
            sidebar.classList.toggle('active');
        });
    } else {
        console.error("Toggle button not found in DOM");
    }
});