<h1>Gestion des applications</h1>
<p>
	Vous pouvez créer les "Apps" qui composent votre projet ainsi que le modules les composants.<br/>
	Lorsque vous ajoutez une "Apps", les fichiers suivants sont crées : <br/>
	- le repertoire {NomApp} dans apps<br/>
	- les repertoires "config", "templates" et "modules" dans {NomApp}<br/>
	Vous pouvez également gérer les modules des apps, les fichiers suivants seront crées dans {NomModule} : <br/>
	- le repertoire {NomModule} dans {NomApp}<br/>
	- les repertoires "config" et "templates" ainsi que le fichier Controller du module<br/>
	
</p>
<button id="bt_addApps">Ajouter une application</button>
<form name="" action="" method="post" id="f_addApps">
	<h2 id="title_host">Ajouter une application</h2>
	<hr />
	<label>
		<span class="label base">Nom</span>
		<input type="text" name="app" />
	</label>
	<button type="submit">Ajouter</button>
	<button type="reset">Annuler</button>
</form>
<table cellspacing="0" class="t_apps">
	<thead>
		<tr>
			<th>Liste des applications</th>
			<th>Nbre modules</th>
			<th></th>
		</tr>
	</thead>
	<tbody id="recept">
		<tr class="vide">
			<td colspan="3">
				<i>La liste des application est vide</i>
			</td>
		</tr>
	</tbody>
</table>

<div class="bloc_module">
	<button id="bt_addModule">Ajouter un module</button>
	<form name="" action="" method="post" id="f_addModule">
		<input type="hidden" name="app" />
		<h2 id="title_host">Ajouter un module</h2>
		<hr />
		<label>
			<span class="label base">Nom</span>
			<input type="text" name="module" />
		</label>
		<button type="submit">Ajouter</button>
		<button type="reset">Annuler</button>
	</form>
	<table cellspacing="0" class="t_modules">
		<thead>
			<tr>
				<th>Liste des modules de l'application <span class="appName"></span></th>
				<th></th>
			</tr>
		</thead>
		<tbody id="recept">
			<tr class="vide">
				<td colspan="2">
					<i>La liste des modules est vide</i>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<form action="generate" method="post" id="f_addAppMod">
	<label>
		<span class="label inline">Application par défaut</span>
		<select name="defaultApp"></select>
	</label>
	<input type="hidden" name="config" />
	<button>Générer</button>
</form>
<script type="text/javascript">
	var appList = <?php echo $appsJson; ?>;
</script>
