<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StudyPath SMK</title>

    {{-- GLOBAL CSS --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- CSS PER HALAMAN --}}
    @stack('styles')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="app">

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Main Area --}}
    <div class="main-wrapper">

        {{-- Navbar (FIXED HEADER) --}}
        @include('components.navbar')

        {{-- Content --}}
        <main class="content">
            @yield('content')
        </main>

    </div>

</div>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* HITUNG */
    const inputs = document.querySelectorAll('input[type=number]');
    if(inputs.length){
        inputs.forEach(i => i.addEventListener('input', hitung));
    }

    function hitung(){
        let nilai = [];
        inputs.forEach(i => { if(i.value) nilai.push(parseFloat(i.value)); });

        if(!nilai.length) return;

        let avg = nilai.reduce((a,b)=>a+b,0)/nilai.length;
        let max = Math.max(...nilai);
        let min = Math.min(...nilai);
        let std = Math.sqrt(
            nilai.map(x => Math.pow(x - avg, 2)).reduce((a,b)=>a+b)/nilai.length
        );

        const stat = document.querySelectorAll('.stat b');
        if(stat.length >= 4){
            stat[0].innerText = avg.toFixed(2);
            stat[1].innerText = max;
            stat[2].innerText = min;
            stat[3].innerText = std.toFixed(2);
        }
    }

    /* DARK MODE */
    const toggle = document.getElementById('theme-toggle');

    if(toggle){
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');

            if(document.body.classList.contains('dark')){
                toggle.innerText = '☀️';
                localStorage.setItem('theme','dark');
            } else {
                toggle.innerText = '🌙';
                localStorage.setItem('theme','light');
            }
        });

        if(localStorage.getItem('theme') === 'dark'){
            document.body.classList.add('dark');
            toggle.innerText = '☀️';
        }
    }

});
</script>

</body>
</html>