function erreurSelection(){
	document.getElementById('erreur_titulaires').style.visibility = 'visible';
}

function fermerErreurSelection(){
	var x = document.getElementById('erreur_titulaires');
	document.getElementById('erreur_titulaires').style.visibility = 'hidden';
}