<?php

	/**
	* French Language file for Emaj_web.
	*/

	// Language and character set
	$lang['applocale'] = 'fr-FR';
	$lang['applangdir'] = 'ltr';

	// Basic strings
	$lang['strintro'] = 'Bienvenue dans Emaj_web.';
	$lang['strpgsqlhome'] = 'Page d\'accueil de PostgreSQL';
	$lang['strpgsqlhome_url'] = 'http://www.postgresql.org/';
	$lang['stremajdoc'] = 'Documentation E-Maj';
	$lang['stremajproject'] = 'E-Maj sur github';

	// Basic strings
	$lang['strlogin'] = 'Connexion';
	$lang['strloginfailed'] = 'Échec de la connexion';
	$lang['strlogindisallowed'] = 'Connexion désactivée pour raison de sécurité';
	$lang['strserver'] = 'Serveur';
	$lang['strservers'] = 'Serveurs';
	$lang['strgroupservers'] = 'Serveurs du groupe "%s"';
	$lang['strallservers'] = 'Tous les serveurs';
	$lang['strintroduction'] = 'Introduction';
	$lang['strhost'] = 'Hôte';
	$lang['strport'] = 'Port';
	$lang['strlogout'] = 'Déconnexion';
	$lang['strowner'] = 'Propriétaire';
	$lang['straction'] = 'Action';
	$lang['stractions'] = 'Actions';
	$lang['strname'] = 'Nom';
	$lang['strproperties'] = 'Propriétés';
	$lang['strbrowse'] = 'Parcourir';
	$lang['strdrop'] = 'Supprimer';
	$lang['strnotnull'] = 'NOT NULL';
	$lang['strprev'] = 'Précédent';
	$lang['strnext'] = 'Suivant';
	$lang['strfirst'] = '<< Début';
	$lang['strlast'] = 'Fin >>';
	$lang['strcreate'] = 'Créer';
	$lang['strcomment'] = 'Commentaire';
	$lang['strdefault'] = 'Défaut';
	$lang['stralter'] = 'Modifier';
	$lang['strok'] = 'OK';
	$lang['strcancel'] = 'Annuler';
	$lang['strreset'] = 'Réinitialiser';
	$lang['strselect'] = 'Sélectionner';
	$lang['strdelete'] = 'Effacer';
	$lang['strupdate'] = 'Modifier';
	$lang['stryes'] = 'Oui';
	$lang['strno'] = 'Non';
	$lang['strtrue'] = 'TRUE';
	$lang['strfalse'] = 'FALSE';
	$lang['strcolumn'] = 'Colonne';
	$lang['strcolumns'] = 'Colonnes';
	$lang['strrows'] = 'ligne(s)';
	$lang['strrowsaff'] = 'ligne(s) affectée(s).';
	$lang['strback'] = 'Retour';
	$lang['strqueryresults'] = 'Résultats de la requête';
	$lang['strshow'] = 'Voir';
	$lang['strlanguage'] = 'Langage';
	$lang['strencoding'] = 'Codage';
	$lang['strvalue'] = 'Valeur';
	$lang['strsql'] = 'SQL';
	$lang['strexecute'] = 'Lancer';
	$lang['strconfirm'] = 'Confirmer';
	$lang['strellipsis'] = '...';
	$lang['strseparator'] = ' :';
	$lang['strexpand'] = 'Étendre';
	$lang['strcollapse'] = 'Réduire';
	$lang['strfind'] = 'Rechercher';
	$lang['strrefresh'] = 'Rafraichir';
	$lang['strdownload'] = 'Télécharger';
	$lang['streditsql'] = 'Éditer SQL';
	$lang['strruntime'] = 'Temps d\'exécution total : %s ms';
	$lang['strpaginate'] = 'Paginer les résultats';
	$lang['struploadscript'] = 'ou importer un script SQL :';
	$lang['strtrycred'] = 'Utilisez ces identifiants pour tous les serveurs';
	$lang['strconfdropcred'] = 'For security reason, disconnecting will destroy your shared login information. Are you sure you want to disconnect ?';
	$lang['strconfdropcred'] = 'Par mesure de sécurité, la déconnexion supprimera le partage de vos identifiants pour tous les serveurs. Êtes-vous certain de vouloir vous déconnecter ?';
	$lang['stractionsonmultiplelines'] = 'Actions sur plusieurs lignes';
	$lang['strselectall'] = 'Sélectionner tout';
	$lang['strunselectall'] = 'Desélectionner tout';
	$lang['strstart'] = 'Démarrer';
	$lang['strstop'] = 'Arrêter';
	$lang['strgotoppage'] = 'Haut de la page';
	$lang['strtheme'] = 'Thème';

	// Admin

	// User-supplied SQL history
	$lang['strhistory'] = 'Historique';
	$lang['strnohistory'] = 'Pas d\'historique.';
	$lang['strclearhistory'] = 'Éffacer l\'historique';
	$lang['strdelhistory'] = 'Supprimer de l\'historique';
	$lang['strconfdelhistory'] = 'Voulez-vous vraiment supprimer cette requête de l\'historique ?';
	$lang['strconfclearhistory'] = 'Voulez-vous vraiment éffacer l\'historique ?';
	$lang['strnodatabaseselected'] = 'Veuillez sélectionner une base de données.';

	// Database Sizes
	$lang['strnoaccess'] = 'Pas d\'Accès'; 
	$lang['strsize'] = 'Taille';
	$lang['strbytes'] = 'octets';
	$lang['strkb'] = ' Ko';
	$lang['strmb'] = ' Mo';
	$lang['strgb'] = ' Go';
	$lang['strtb'] = ' To';

	// Error handling
	$lang['strnoframes'] = 'Cette application fonctionne mieux avec un navigateur pouvant afficher des frames mais peut être utilisée sans frames en suivant les liens ci-dessous.';
	$lang['strnoframeslink'] = 'Utiliser sans frames';
	$lang['strbadconfig'] = 'Le fichier de configuration config.inc.php est obsolète. Vous avez besoin de le regénérer à partir de config.inc.php-dist.';
	$lang['strnotloaded'] = 'Vous n\'avez pas compilé correctement le support de la base de données dans votre installation de PHP.';
	$lang['strpostgresqlversionnotsupported'] = 'Cette version de PostgreSQL n\'est pas supportée. Merci de mettre à jour PHP à la version %s ou ultérieure.';
	$lang['strbadschema'] = 'Schéma spécifié invalide.';
	$lang['strsqlerror'] = 'Erreur SQL :';
	$lang['strinstatement'] = 'Dans l\'instruction :';
	$lang['strinvalidparam'] = 'Paramètres de script invalides.';
	$lang['strnodata'] = 'Pas de résultats.';
	$lang['strnoobjects'] = 'Aucun objet trouvé.';
	$lang['strcannotdumponwindows'] = 'La sauvegarde de table complexe et des noms de schémas n\'est pas supporté sur Windows.';
	$lang['strinvalidserverparam'] = 'Tentative de connexion avec un serveur invalide, il est possible que quelqu\'un essai de pirater votre système.'; 
	$lang['strnoserversupplied'] = 'Aucun serveur fournis !';
	$lang['strconnectionfail'] = 'Connexion au serveur échouée.';

	// Tables
	$lang['strtable'] = 'Table';
	$lang['strtables'] = 'Tables';
	$lang['strnotables'] = 'Aucune table trouvée.';
	$lang['strnofkref'] = 'Aucune valeur correspondate pour la clé étrangère %s.';
	$lang['strselectallfields'] = 'Sélectionner tous les champs';
	$lang['strselectneedscol'] = 'Vous devez sélectionner au moins une colonne.';
	$lang['strselectunary'] = 'Les opérateurs unaires ne peuvent avoir de valeurs.';
	$lang['strestimatedrowcount'] = 'Nombre d\'enregistrements estimés';

	// Columns

	// Users
	$lang['strusername'] = 'Utilisateur';
	$lang['strpassword'] = 'Mot de passe';

	// Groups
	$lang['strgroup'] = 'Groupe';
	$lang['strgroupgroups'] = 'Groupes du groupe "%s"';

	// Roles
	$lang['strrole'] = 'Rôle';
	$lang['strroles'] = 'Rôles';

	// Privileges

	// Databases
	$lang['strdatabase'] = 'Base de données';
	$lang['strdatabases'] = 'Bases de données';
	$lang['strnodatabases'] = 'Aucune base de données trouvée.';
	$lang['strentersql'] = 'Veuillez saisir ci-dessous la requête SQL à exécuter :';
	$lang['strsqlexecuted'] = 'Requête SQL exécutée.';
	$lang['strallobjects'] = 'Tous les objets';

	// Views
	$lang['strviews'] = 'Vues';
	$lang['strcreateview'] = 'Créer une vue';

	// Sequences
	$lang['strsequence'] = 'Séquence';
	$lang['strsequences'] = 'Séquences';
	$lang['strnosequences'] = 'Aucune séquence trouvée.';
	$lang['strlastvalue'] = 'Dernière valeur';
	$lang['strincrementby'] = 'Incrémenter par ';
	$lang['strstartvalue'] = 'Valeur de départ';
	$lang['strmaxvalue'] = 'Valeur maximale';
	$lang['strminvalue'] = 'Valeur minimale';
	$lang['strcachevalue'] = 'Valeur de cache';
	$lang['strlogcount'] = 'Comptage';
	$lang['strcancycle'] = 'Peut boucler?';
	$lang['striscalled'] = 'Incrémentera la dernière valeur avant de retourner la prochaine valeur (is_called) ?';

	// Indexes
	$lang['strindexes'] = 'Index';

	// Rules
	$lang['strrules'] = 'Règles';

	// Constraints
	$lang['strconstraints'] = 'Contraintes';

	// Functions
	$lang['strfunctions'] = 'Fonctions';

	// Triggers
	$lang['strtriggers'] = 'Triggers';

	// Types
	$lang['strtype'] = 'Type';
	$lang['strtypes'] = 'Types';

	// Schemas
	$lang['strschema'] = 'Schéma';
	$lang['strschemas'] = 'Schémas';
	$lang['strnoschemas'] = 'Aucun schéma trouvé.';
	$lang['strsearchpath'] = 'Chemin de recherche du schéma';

	// Reports

	// Domains
	$lang['strdomains'] = 'Domaines';

	// Operators
	$lang['stroperator'] = 'Opérateur';
	$lang['stroperators'] = 'Opérateurs';

	// Casts

	// Conversions
	$lang['strconversions'] = 'Conversions';

	// Languages
	$lang['strlanguages'] = 'Langages';

	// Info

	// Aggregates
	$lang['straggregates'] = 'Agrégats';

	// Operator Classes
	$lang['stropclasses'] = 'Classes d\'opérateur';

	// Stats and performance

	// Tablespaces
	$lang['strtablespace'] = 'Tablespace';

	// Miscellaneous
	$lang['strtopbar'] = '%s lancé sur %s:%s -- Vous êtes connecté avec le profil « %s »';
	$lang['strlogintitle'] = 'Se connecter à %s';
	$lang['strlogoutmsg'] = 'Déconnecté de %s';
	$lang['strloading'] = 'Chargement...';
	$lang['strerrorloading'] = 'Erreur lors du chargement';
	$lang['strclicktoreload'] = 'Cliquer pour recharger';

	//Autovacuum

	//Table-level Locks

	// Prepared transactions

	// Fulltext search

	//Plugins
	$lang['strpluginnotfound'] = 'Error: plugin \'%s\' not found. Check if this plugin exists in the plugins/ directory, or if this plugins has a plugin.php file. Plugin\'s names are case sensitive';
	$lang['stractionnotfound'] = 'Error: action \'%s\' not found in the \'%s\' plugin, or it was not specified as an action.';
	$lang['strhooknotfound'] = 'Error: hook \'%s\' is not avaliable.';
?>
