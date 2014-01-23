<h1>bienvenue !</h1>
<ul> Cet interface vous permet :
	<li>• De configurer votre projet (host / bdd )</li>
	<li>• De générer le fichier de config nécessaire au bon fonctionnement de Citrus</li>
	<li>• De gérer les applications qui composeront votre projet</li>
	<li>• De générer le fichier SQL à partir des schémas des entités</li>
</ul>

<hr>
<form name="" action="" method="post" id="f_install">
	<label>
		<span>Nom du site <small>(site_name)</small></span>
		<input type="text" name="site_name" class="big">
	</label>
	<div class="label">
		<span class="mb10">Utilisation d'une base de données MySQL</span>
		<label><input type="radio" name="bdd" value="0" checked="checked"><span class="fl">Non</span></label>
		<label><input type="radio" name="bdd" value="1"><span class="fl">Oui</span></label>
	</div>
	<button type="submit">Valider</button>
</form>
