let links = document.querySelectorAll("[data-delete]");

// console.log(links);

// On boucle sur les liens
for (let link of links){
    // On met un ecouteur d'event
    link.addEventListener("click", function (e){
        // On empehce la nav
        e.preventDefault();

        //On demande confirmation
        if(confirm("Voulez-vous supprimer cette image ?")){
            // On envoie la requete ajax
            fetch(this.getAttribute("href"), {
                method: "DELETE",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "ContentType": "application/json"
                },
                body: JSON.stringify({"_token": this.dataset.token})
            }).then(response => response.json())
                .then(data => {
                    if(data.success){
                        this.parentElement.remove();
                    } else {
                        alert(data.error);
                    }
                })
        }
    })
}