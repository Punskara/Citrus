<h1>configurer citrus</h1>
<p>
	Dans cet espace, vous pouvez gérer les hosts qui hébergeront votre projet.<br/>
	Citrus distinge 2 types de "host" : <br/>
	- Développement<br/>
	- Production 
</p>
<h2 id="title_host">Créer un host</h2>
<hr />
<form name="" action="" method="post" id="f_addHost">
	<input type="hidden" name="inst" value="new" />
	<div class="gauche">
		<label>
			<span class="label base">type</span>
			<select name="type">
				<option>Développement</option>
				<option>Production</option>
			</select>
		</label>
		<label>
			<span class="label base">Hôte</span>
			<input type="text" name="hostname" class="big" />
		</label>
		<div id="bloc_bdd" class="label">
			<span class="label base">Base</span>
			<input type="text" name="bddhost" placeholder="Host" />
			<input type="text" name="database" placeholder="Database name" /><br />
			<span class="label base"></span>
			<input type="text" name="login" placeholder="login" />
			<input type="text" name="password" placeholder="password" />
		</div>
	</div>
	<div class="droite">
		<div class="label">
			<span class="label base">Débug</span>
			<label><input type="radio" name="debug" value="0" checked="checked"><span class="fl">Non</span></label>
			<label><input type="radio" name="debug" value="1"><span class="fl">Oui</span></label>
		</div>
		<div class="label">
			<span class="label base">Logging</span>
			<label><input type="radio" name="log" value="0" checked="checked"><span class="fl">Non</span></label>
			<label><input type="radio" name="log" value="1"><span class="fl">Oui</span></label>
		</div>
		<label>
			<span class="label base">Chemin</span>
			<input type="text" name="path" class="big" />
		</label>
	</div>
	<button type="submit">Ajouter</button>
	<button type="reset" disabled="disabled">Annuler</button>
</form>
<table cellspacing="0" class="t_host">
	<thead>
		<tr>
			<th>Liste des hosts existants</th>
			<th>type</th>
			<th>Chemin</th>
			<th>Base</th>
			<th>Debug</th>
			<th>Logging</th>
			<th></th>
		</tr>
	</thead>
	<tbody id="recept">
		<tr class="vide">
			<td colspan="7">
				<i>La liste des hosts est vide</i>
			</td>
		</tr>
	</tbody>
</table>
<form name="" action="" method="post" id="f_addHosts">
	<button>Valider</button>
</form>
