window.onload = () => {
    // Gestion des boutons "Supprimer" sur les balises qui ont un attribut data-delete
    let links = document.querySelectorAll("[data-delete]")
    
console.log(links)

    // On boucle sur links
    for(link of links){
        // On écoute le clic
        link.addEventListener("click", function(e){
            // On empêche la navigation vers le lien qui est dans href
            e.preventDefault()

            // On demande confirmation
            if(confirm("Voulez-vous supprimer cette image ?")){
                // On envoie une requête Ajax vers le href du lien avec la méthode DELETE
                // this es tle lien sur lequel on a cliqué
                // fetch est une promesse qui envoie à URL du href
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                    // quand la promesse est tenue
                }).then(
                    // On récupère la réponse en JSON
                    response => response.json()
                ).then(data => {
                    if(data.success)
                        this.parentElement.remove()
                    else
                        alert(data.error)
                }).catch(e => alert(e))
            }
        })
    }
}