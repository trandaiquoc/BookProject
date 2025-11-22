document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");
    const searchDropdown = document.getElementById("searchDropdown");
    const searchForm = document.getElementById("searchForm");

    let timeout = null;

    searchInput.addEventListener("input", function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        if(!query){
            searchDropdown.style.display = "none";
            return;
        }

        timeout = setTimeout(() => {
            fetch(`http://localhost/BookProject/BookProject/Backend/index.php?action=search&q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    searchDropdown.innerHTML = "";
                    if(data.length === 0){
                        searchDropdown.style.display = "none";
                        return;
                    }
                    data.slice(0,5).forEach(item => {
                        const li = document.createElement("li");
                        li.className = "list-group-item";
                        li.textContent = item.name; // tên sách hoặc user
                        li.addEventListener("click", () => {
                            searchInput.value = item.name;
                            searchDropdown.style.display = "none";
                            searchForm.submit();
                        });
                        searchDropdown.appendChild(li);
                    });
                    searchDropdown.style.display = "block";
                });
        }, 300);
    });

    document.addEventListener("click", function(e){
        if(!searchForm.contains(e.target)){
            searchDropdown.style.display = "none";
        }
    });

    // Nhấn Enter sẽ submit form
    searchInput.addEventListener("keydown", function(e){
        if(e.key === "Enter"){
            searchDropdown.style.display = "none";
        }
    });
});