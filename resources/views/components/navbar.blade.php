<div class="navbar">
    <div class="nav-left"> 
        <button id="sidebar-toggle" class="menu-toggle"> 
            <i class="fas fa-bars"></i> 
        </button> 
    </div>

    <div class="nav-right">
        <button id="theme-toggle">🌙</button>
    </div>
</div>

<script>
const sidebarToggle = document.getElementById('sidebar-toggle');

if(sidebarToggle){
    sidebarToggle.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');

        // simpan state
        if(document.body.classList.contains('sidebar-collapsed')){
            localStorage.setItem('sidebar', 'collapsed');
        } else {
            localStorage.setItem('sidebar', 'open');
        }
    });

    // load state
    if(localStorage.getItem('sidebar') === 'collapsed'){
        document.body.classList.add('sidebar-collapsed');
    }
}
</script>