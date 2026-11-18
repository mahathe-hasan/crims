</div>

</div>


    <script>
function updateClock(){

    const now = new Date();

    const options = {
        hour:'2-digit',
        minute:'2-digit',
        second:'2-digit',
        hour12:true
    };

    document.getElementById('liveClock').innerHTML =
        now.toLocaleTimeString('en-US', options);

}

setInterval(updateClock,1000);

updateClock();
</script>
<script>
function toggleTheme(){

    document.body.classList.toggle("dark");

    localStorage.setItem(
        "theme",
        document.body.classList.contains("dark")
            ? "dark"
            : "light"
    );

}

if(localStorage.getItem("theme")==="dark"){

    document.body.classList.add("dark");

}
</script>
<script>

function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("active");
    document.body.classList.toggle("sidebar-open");
}

// Live Clock Script (যদি আগে থেকে না থাকে)
if(document.getElementById("liveClock")) {
    setInterval(() => {
        const d = new Date();
        document.getElementById("liveClock").innerHTML = d.toLocaleTimeString();
    }, 1000);
}

</script>

<script>

const photoInput = document.querySelector('input[name="photo"]');

if(photoInput){

    photoInput.addEventListener("change",function(e){

        const file = e.target.files[0];

        if(file){

            document.getElementById("preview").src =
                URL.createObjectURL(file);

        }

    });

}

</script>

<script>

document.getElementById("searchAttendance").addEventListener("keyup",function(){

let value=this.value.toLowerCase();

document.querySelectorAll("tbody tr").forEach(function(row){

row.style.display=row.innerText.toLowerCase().includes(value)
? ""
: "none";

});

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>