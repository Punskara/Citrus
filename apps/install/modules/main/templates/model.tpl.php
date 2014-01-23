<h1>Modelisation</h1>
<p>
	Création des schémas sql à partir des entités crées et référencées par Citrus.
</p>
<form action="<?php url_to( 'install/main/buildSchema' ) ?>" id="shbuild">
	<button>Générer les schémas SQL</button>
</form>
<hr class="cb">
<p>
	Insertion du schéma sql dans la base de donnée de l'host utilisé
</p>
<form action="<?php url_to( 'install/main/execSchema' ) ?>" id="shexec">
	<button <?php if (!$shema) echo 'disabled="disabled"'; ?>>Injecter directement dans la base de données</button>
</form>
<hr class="cb">
<p>
	Export du schéma sql
</p>
<form action="<?php url_to( 'install/main/dlSchema' ) ?>" id="shdl">
	<button <?php if (!$shema) echo 'disabled="disabled"'; ?>>Télécharger les schémas</button>
</form>