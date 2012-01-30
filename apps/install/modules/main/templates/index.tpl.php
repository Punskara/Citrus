<header>
	<div class="msgbox" id="generated">
		<h2>Félicitation</h2>
		<p>La génération de votre fichier de configuration s'est déroulé correctement.</p>
	</div>
	<div class="msgbox" id="error_generated">
		<h2>Erreur lors de la génération du fichier de configuration</h2>
		<p>Vérifiez les données saisies dans les formulaire afin de vous assurer de leur cohérence.</p>
		<p>Supprimez le fichier <b><i>config/config.inc.php</i></b> afin de retouver l'interface d'installation initiale.</p>
	</div>
	<div class="msgbox" id="model_build">
		<h2>Model</h2>
		<p>Le shéma SQL a correctement été créer.</p>
	</div>
	<div class="msgbox" id="model_exec">
		<h2>Model</h2>
		<p>Le shéma SQL a correctement été éxecuté.</p>
	</div>
	<nav>
		<a href="installation.html">installation</a>
		<a href="configuration.html" class="disabled">configuration</a>
		<a href="apps.html" class="disabled">apps</a>
		<a href="model.html" class="disabled">model</a>
	</nav>
</header>
<div class="msgboxLight" id="modified">
	<div>
		<button>Générer le fichier de configuration</button>
		<p>Les modifications que vous avez effectués ne seront prises en compte que lorsque vous aurez de nouveau généré le fichier de configuration</p>
	</div>
</div>
<div class="container"></div>
<footer></footer>
<script type="text/javascript" src="getconfig.json"></script>
<script type="text/javascript">
	var generated = <?php echo $generated ? 'true' : 'false'; ?>;
	var just_generated = <?php echo $just_generated ? 'true' : 'false'; ?>;
</script>
