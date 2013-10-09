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
		<span>Nom du site <small>(SiteName)</small></span>
		<input type="text" name="sitename" class="big">
	</label>
	<label>
		<span>Nom du projet <small>(ProjectName)</small></span>
		<input type="text" name="projectname" class="big">
	</label>
	<div class="label">
		<span class="mb10">Utilisation d'une base de données MySQL</span>
		<label><input type="radio" name="bdd" value="0" checked="checked"><span class="fl">Non</span></label>
		<label><input type="radio" name="bdd" value="1"><span class="fl">Oui</span></label>
		<div class="label" id="bloc_doctrine">
			<span>Utiliser Doctrine 2 ?</span>
			<label><input type="radio" name="doctrine" value="0" checked="checked"><span class="fl">Non</span></label>
			<label><input type="radio" name="doctrine" value="1"><span class="fl">Oui</span></label>
		</div>
	</div>
	<button type="submit">Valider</button>
</form>
