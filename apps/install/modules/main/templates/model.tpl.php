<h1>Modelisation</h1>
<p>
	Création des schémas sql à partir des entités crées et référencées par Citrus.
</p>
<form action="buildSchema.html" id="shbuild">
	<button>Générer les shémas SQL</button>
</form>
<hr class="cb" />
<p>
	Insertion du schéma sql dans la base de donnée de l'host utilisé
</p>
<form action="execSchema.html" id="shexec">
	<button <?php if (!$shema) echo 'disabled="disabled"'; ?>>Injecter directement dans la base de donnée</button>
</form>
<hr class="cb" />
<p>
	Export du schéma sql
</p>
<form action="dlSchema.html" id="shdl">
	<button <?php if (!$shema) echo 'disabled="disabled"'; ?>>Télécharger les shémas</button>
</form>