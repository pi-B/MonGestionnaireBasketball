function afficherPhotoJoueur(nomImage){
	lien = "photos_joueurs/" + nomImage + ".jpg";
	var x = document.getElementById("joueurSurvole");
	x.setAttribute("src",lien);
	document.getElementById('joueurSurvole').style.border = '5px solid #466d99';
}

function enleverPhotoJoueur(){
	var x = document.getElementById("joueurSurvole");
	x.setAttribute("src","");
	document.getElementById('joueurSurvole').style.border = '';
}

