<?php

/**
* French Language file for Emaj_web.
*
*/

//
// Common strings
//

	// Language and character set
	$lang['applocale'] = 'fr-FR';
	$lang['applocalearray'] = array('fr-FR','fr_FR','fr','fra','fr_FR@euro','fr_FR.utf8');
	$lang['applangdir'] = 'ltr';
	// Php format for timestamp fields, distinguishing the format for:
	// - the old times, producing something like '23 Jun 2020 12:34:56'
	$lang['stroldtimestampformat'] = 'dd MMM YYYY HH:mm:ss';
	// - the recent times, producing something like 'Mon 23 Jun 12:34:56'
	$lang['strrecenttimestampformat'] = 'EEE dd MMM HH:mm:ss';
	// - the timestamp abbreviated into time with milliseconds
	$lang['strprecisetimeformat'] = 'HH:mm:ss.SSS';
	// Internal format for full interval display
	$lang['strintervalformat'] = 'DD j HH h MM min SS.US s';

	// Basic strings
	$lang['straction'] = 'Action';
	$lang['stractions'] = 'Actions';
	$lang['stractionsonselectedobjects'] = 'Actions sur les objets (%s)';
	$lang['stractivity'] = 'Activité';
	$lang['stractual'] = 'Réel';
	$lang['strall'] = 'Tous';
	$lang['strassign'] = 'Affecter';
	$lang['strassigned'] = 'Affectée';
	$lang['strauditonly'] = 'Audit-seul';
	$lang['strautorefresh'] = 'Rafraîchissement auto';
	$lang['strback'] = 'Retour';
	$lang['strbacktolist'] = 'Retour à la liste';
	$lang['strbegin'] = 'Début';
	$lang['strbrowse'] = 'Parcourir';
	$lang['strcancel'] = 'Annuler';
	$lang['strchanges'] = 'Mises à jour';
	$lang['strclear'] = 'Effacer';
	$lang['strcollapse'] = 'Réduire';
	$lang['strcolumn'] = 'Colonne';
	$lang['strcomment'] = 'Commentaire';
	$lang['strcommentlabel'] = 'Commentaire : ';
	$lang['strconfirm'] = 'Confirmer';
	$lang['strconstraints'] = 'Contraintes';
	$lang['strcontent'] = 'Contenu';
	$lang['strcreate'] = 'Créer';
	$lang['strcumulated'] = 'Cumul';
	$lang['strcurrentvalue'] = 'Valeur courante';
	$lang['strdatetime'] = 'Date-Heure';
	$lang['strdefault'] = 'Défaut';
	$lang['strdelete'] = 'Effacer';
	$lang['strdifferentvalues'] = '(%s valeurs différentes)';
	$lang['strdisplay'] = 'Afficher';
	$lang['strdownload'] = 'Télécharger';
	$lang['strdrop'] = 'Supprimer';
	$lang['stredit'] = 'Éditer';
	$lang['strellipsis'] = '...';
	$lang['stremajproperties'] = 'Propriétés E-Maj';
	$lang['stremajschema'] = 'Schéma E-Maj';
	$lang['stremajtrigger'] = 'Trigger E-Maj';
	$lang['strencoding'] = 'Codage';
	$lang['strend'] = 'Fin';
	$lang['strendmark'] = 'Marque fin';
	$lang['strestimate'] = 'Estimer';
	$lang['strestimatedduration'] = 'Durée estimée';
	$lang['strestimates'] = 'Estimations';
	$lang['strexecute'] = 'Exécuter';
	$lang['strexpand'] = 'Étendre';
	$lang['strexport'] = 'Exporter';
	$lang['strfalse'] = 'FALSE';
	$lang['strfirst'] = '<< Début';
	$lang['strforget'] = 'Oublier';
	$lang['strgotoppage'] = 'Haut de la page';
	$lang['strgroup'] = 'Groupe';
	$lang['strgroups'] = 'Groupes';
	$lang['strgrouptype'] = 'Type de groupe';
	$lang['strhost'] = 'Hôte';
	$lang['stridle'] = 'Arrêté';
	$lang['strimport'] = 'Importer';
	$lang['strintroduction'] = 'Introduction';
	$lang['strinvert'] = 'Inverser';
	$lang['strlast'] = 'Fin >>';
	$lang['strlevel'] = 'Niveau';
	$lang['strlogged'] = 'tracé';
	$lang['strlogging'] = 'Démarré';
	$lang['strlogindexes'] = 'Index log';
	$lang['strlogsession'] = 'Session de log';
	$lang['strlogsize'] = 'Taille log';
	$lang['strlogtables'] = 'Tables log';
	$lang['strmark'] = 'Marque';
	$lang['strmarks'] = 'Marques';
	$lang['strmessage'] = 'Message';
	$lang['strmove'] = 'Déplacer';
	$lang['strname'] = 'Nom';
	$lang['strnewvalue'] = 'Nouvelle valeur';
	$lang['strnext'] = 'Suivant';
	$lang['strno'] = 'Non';
	$lang['strnone'] = 'Aucun';
	$lang['strnotassigned'] = 'Non affectés';
	$lang['strnotnull'] = 'NOT NULL';
	$lang['strnumber'] = 'Nombre';
	$lang['strok'] = 'OK';
	$lang['stropen'] = 'Ouvrir';
	$lang['strowner'] = 'Propriétaire';
	$lang['strpagebottom'] = 'Bas de la page';
	$lang['strpk'] = 'Clé primaire';
	$lang['strport'] = 'Port';
	$lang['strprev'] = 'Précédent';
	$lang['strproperties'] = 'Propriétés';
	$lang['strprotect'] = 'Protéger';
	$lang['strprotected'] = 'Protégé contre les rollbacks E-Maj';
	$lang['strquantity'] = 'Quantité';
	$lang['strqueryresults'] = 'Résultats de la requête';
	$lang['strrecreate'] = 'Recréer';
	$lang['strreestimate'] = 'Ré-estimer';
	$lang['strrefresh'] = 'Rafraîchir';
	$lang['strremove'] = 'Retirer';
	$lang['strremoved'] = 'Retirée';
	$lang['strrename'] = 'Renommer';
	$lang['strrequiredfield'] = 'Champ requis';
	$lang['strreset'] = 'Réinitialiser';
	$lang['strrlbk'] = 'Rollback';
	$lang['strrole'] = 'Rôle';
	$lang['strroles'] = 'Rôles';
	$lang['strrollback'] = 'Rollback E-Maj';
	$lang['strrollbackable'] = 'Rollbackable';
	$lang['strrollbacktype'] = 'Type de rollback';
	$lang['strrows'] = 'ligne(s)';
	$lang['strrowsaff'] = 'ligne(s) affectée(s).';
	$lang['strruntime'] = 'Temps d\'exécution total : %s ms';
	$lang['strselect'] = 'Sélectionner';
	$lang['strselectfile'] = 'Sélectionner un fichier';
	$lang['strsequence'] = 'Séquence';
	$lang['strsequences'] = 'Séquences';
	$lang['strsetcomment'] = 'Commenter';
	$lang['strsince'] = 'Depuis';
	$lang['strsql'] = 'SQL';
	$lang['strstart'] = 'Démarrer';
	$lang['strstartmark'] = 'Marque début';
	$lang['strstate'] = 'État';
	$lang['strstop'] = 'Arrêter';
	$lang['strtable'] = 'Table';
	$lang['strtables'] = 'Tables';
	$lang['strtablesgroup'] = 'Groupe de tables';
	$lang['strtablespace'] = 'Tablespace';
	$lang['strtrue'] = 'TRUE';
	$lang['strtxid'] = 'Id. transaction';
	$lang['strtype'] = 'Type';
	$lang['strunlogged'] = 'non tracé';
	$lang['strunprotect'] = 'Déprotéger';
	$lang['strupdate'] = 'Modifier';
	$lang['struser'] = 'Utilisateur';
	$lang['strvisible'] = 'Visibles';
	$lang['stryes'] = 'Oui';

	// Sizes
	$lang['strnoaccess'] = 'No Access'; 
	$lang['strsize'] = 'Size';
	$lang['strbytes'] = 'bytes';
	$lang['strkb'] = 'kB';
	$lang['strmb'] = 'MB';
	$lang['strgb'] = 'GB';
	$lang['strtb'] = 'TB';

	// Common help messages
	$lang['strmarknamehelp'] = 'Le nom de la marque doit être unique pour le groupe. Un caractère % représente l\'heure courante (format hh.mn.ss.ms).';
	$lang['strmarknamemultihelp'] = 'Le nom de la marque doit être unique pour les groupes concernés. Un caractère % représente l\'heure courante (format hh.mn.ss.ms).';
	$lang['strfiltershelp'] = 'Afficher/cacher les filtres. Les filtres sur le contenu des colonnes peuvent contenir des chaînes de caractères (abc), des nombres (123), des conditions d\'inégalité (>= 1000), des expressions rationnelles (/^ABC\d\d/), des conditions multiples avec les opérateurs \'and\', \'or\' ou \'!\'.';
	$lang['strautorefreshhelp'] = 'Le délai de rafraichissement automatique (actuellement %s sec) est paramétrable dans le fichier de configuration d\'Emaj_web.';

	// Error handling
	$lang['strnotloaded'] = 'Vous n\'avez pas compilé correctement le support de la base de données dans votre installation de PHP.';
	$lang['strmissingIntlDateFormatter'] = 'Le module PHP "IntlDateFormatter" est absent de la configuration du serveur web, gênant l\'affichage des dates et heures.';
	$lang['strpostgresqlversionnotsupported'] = 'Cette version de PostgreSQL n\'est pas supportée. La version minimum supportée est la %s.';
	$lang['strbadschema'] = 'Schéma spécifié invalide.';
	$lang['strsqlerror'] = 'Erreur SQL :';
	$lang['strinstatement'] = 'Dans l\'instruction :';
	$lang['strnodata'] = 'Pas de résultats.';
	$lang['strnoobjects'] = 'Aucun objet trouvé.';
	$lang['strcannotdumponwindows'] = 'La sauvegarde de table complexe et des noms de schémas n\'est pas supporté sur Windows.';
	$lang['strinvalidserverparam'] = 'Tentative de connexion avec un serveur invalide, il est possible que quelqu\'un essai de pirater votre système.'; 
	$lang['strnoserversupplied'] = 'Aucun serveur fournis !';
	$lang['strconnectionfail'] = 'Connexion au serveur échouée.';
	$lang['strimporterror-uploadedfile'] = 'Erreur d\'importation : le fichier n\'a pas pû être récupéré sur le serveur.';
	$lang['strimportfiletoobig'] = 'Erreur d\'importation : le fichier à charger est trop gros.';
	$lang['strnotjsonfile'] = 'Le fichier %s n\'a pas un format JSON valide.';

//
// Tabs or actions specific strings
//

	// Miscellaneous
	$lang['strloading'] = 'Chargement...';
	$lang['strerrorloading'] = 'Erreur lors du chargement';
	$lang['strclicktoreload'] = 'Cliquer pour recharger';

	// Welcome
	$lang['strintro'] = 'Bienvenue dans %s %s, le client web pour';
	$lang['strlink'] = 'Quelques liens :';
	$lang['strpgsqlhome'] = 'Page d\'accueil de PostgreSQL';
	$lang['strpgsqlhome_url'] = 'http://www.postgresql.org/';
	$lang['stremajdoc'] = 'Documentation E-Maj';
	$lang['stremajdoc_url'] = 'http://emaj.readthedocs.io/fr/latest/';
	$lang['stremajproject'] = 'E-Maj sur github';
	$lang['stremajwebproject'] = 'Emaj_web sur github';

	// Servers and servers Groups
	$lang['strserver'] = 'Serveur';
	$lang['strservers'] = 'Serveurs';
	$lang['strconfiguredservers'] = 'Serveurs PostgreSQL';
	$lang['strgroupservers'] = 'Serveurs PostgreSQL du groupe "%s"';
	$lang['strallservers'] = 'Tous les serveurs';
	$lang['strgroupgroups'] = 'Groupes du groupe "%s"';
	$lang['strserversgroups'] = 'Groupes de serveurs';

	// Connection and disconnection
	$lang['strlogin'] = 'Connexion';
	$lang['strlogintitle'] = 'Se connecter à %s';
	$lang['strusername'] = 'Nom utilisateur';
	$lang['strpassword'] = 'Mot de passe';
	$lang['strtrycred'] = 'Utilisez ces identifiants pour tous les serveurs';
	$lang['strloginfailed'] = 'Échec de la connexion';
	$lang['strlogout'] = 'Se déconnecter';
	$lang['strlogoutmsg'] = 'Déconnecté de %s';
	$lang['strlogindisallowed'] = 'Connexion désactivée pour raison de sécurité';
	$lang['strconfdropcred'] = 'Par mesure de sécurité, la déconnexion supprimera le partage de vos identifiants pour tous les serveurs. Êtes-vous certain de vouloir vous déconnecter ?';
	$lang['strusersuperuser'] = 'L\'utilisateur a les droits de SUPERUSER.';
	$lang['struseremajadm'] = 'L\'utilisateur a les droits emaj_adm et emaj_viewer.';
	$lang['struseremajviewer'] = 'L\'utilisateur a les droits emaj_viewer.';
	$lang['strusernoright'] = 'L\'utilisateur n\'a aucun droit E-Maj.';

	// User-supplied SQL editing
	$lang['strsqledit'] = 'Édition de requête SQL';
	$lang['strsearchpath'] = 'Chemin de recherche des schémas ';
	$lang['strpaginate'] = 'Paginer les résultats';

	// User-supplied SQL history
	$lang['strhistory'] = 'Historique';
	$lang['strsqlhistory'] = 'Historique des requêtes SQL';
	$lang['strnohistory'] = 'Pas d\'historique.';
	$lang['strclearhistory'] = 'Effacer l\'historique';
	$lang['strdelhistory'] = 'Supprimer de l\'historique';
	$lang['strconfdelhistory'] = 'Voulez-vous vraiment supprimer cette requête de l\'historique ?';
	$lang['strconfclearhistory'] = 'Voulez-vous vraiment effacer l\'historique ?';
	$lang['strnodatabaseselected'] = 'Veuillez sélectionner une base de données.';

	// E-Maj html titles and tabs
	$lang['strgroupsmanagement'] = 'Gestion des groupes E-Maj';
	$lang['strgroupsconfiguration'] = 'Configuration des groupes de tables';
	$lang['strgroupsconf'] = 'Conf.groupes';
	$lang['strrollbacksmanagement'] = 'Gestion des rollbacks E-Maj';
	$lang['strrlbkop'] = 'Rollbacks E-Maj';
	$lang['strenvironment'] = 'Environnement E-Maj';
	$lang['strenvir'] = 'E-Maj';
	$lang['strchangesstat'] = 'Statistiques / Mises à jour';

	// Databases
	$lang['strdatabase'] = 'Base de données';
	$lang['strdatabases'] = 'Bases de données';
	$lang['strdatabaseslist'] = 'Databases du serveur';
	$lang['strnodatabases'] = 'Aucune base de données trouvée.';
	$lang['strsqlexecuted'] = 'Requête SQL exécutée.';

// E-Maj groups

	// E-Maj groups lists
	$lang['stridlegroups'] = 'Groupes de tables en état "arrêté" ';
	$lang['strlogginggroups'] = 'Groupes de tables en état "démarré" ';
	$lang['strconfiguredgroups'] = 'Groupes de tables "configurés" mais non encore "créés" ';
	$lang['strlogginggrouphelp'] = 'Quand un groupe de tables est dans l\'état \'démarré\', les insertions, modifications et suppression de lignes sur ses tables sont enregistrées.';
 	$lang['stridlegrouphelp'] = 'Quand un groupe de tables est dans l\'état \'arrêté\', les insertions, modifications et suppressions de lignes sur ses tables ne sont PAS enregistrées.';
	$lang['strconfiguredgrouphelp'] = 'La configuration d\'un groupe définit les tables et séquences qui vont le constituer. Une fois \'configuré\', le groupe doit être \'créé\', afin de préparer tous les objets nécessaires à son utilisation (tables de log, fonctions,...).';
	$lang['strApplyConfChanges'] = 'Appliquer changements conf';
	$lang['strnoidlegroup'] = 'Il n\'y a actuellement aucun groupe de tables en état "arrêté".';
	$lang['strnologginggroup'] = 'Il n\'y a actuellement aucun groupe de tables en état "démarré".';
	$lang['strnoconfiguredgroups'] = 'Il n\'y a actuellement aucun groupe de tables configuré mais non créé.';
	$lang['strnoschema'] = 'Schéma inexistant (x%s) / ';
	$lang['strinvalidschema'] = 'Schéma invalide (x%s) / ';
	$lang['strnorelation'] = 'Table ou séquence inexistante (x%s) / ';
	$lang['strinvalidtable'] = 'Type de table invalide (x%s) / ';
	$lang['strduplicaterelation'] = 'Table ou séquence déjà affectée à un groupe (x%s) / ';
	$lang['strnoconfiguredgroup'] = 'Pour créer un (autre) groupe de tables, allez d\'abord dans l\'onglet de configuration des groupes.<br>Vous pouvez aussi créer un groupe vide puis y ajouter des tables et séquences puis appliquer le changement de configuration.';
	$lang['strcreateemptygroup'] = 'Créer un groupe vide';
	$lang['strnewgroup'] = 'Nouveau groupe';
	$lang['strdroppedgroupslist'] = 'Anciens groupes de tables supprimés';
	$lang['strnodroppedgroup'] = 'Aucun ancien groupe de tables supprimé.';
	$lang['strnoselectedgroup'] = 'Aucun groupe de tables n\'a été sélectionné !';

	// Group creation
	$lang['strcreateagroup'] = 'E-Maj : Créer un groupe de tables';
	$lang['strconfirmcreategroup'] = 'Êtes-vous sûr de vouloir créer le groupe de tables "%s" ?';
	$lang['strcreategroupok'] = 'Le groupe de tables "%s" a été créé.';
	$lang['strcreategrouperr'] = 'Erreur lors de la création du groupe de tables "%s".';

	// Export groups configuration
	$lang['strexportgroupsconf'] = 'Exporter une configuration de groupes de tables';
	$lang['strexportgroupsconfselect'] = 'Sélectionnez les groupes de tables dont la configuration sera exportée sur un fichier local.';
	$lang['strexportgroupserr'] = 'Erreur lors de l\'exportation des groupes de tables "%s".';

	// Import groups configuration
	$lang['strimportgroupsconf'] = 'Importer une configuration de groupes de tables';
	$lang['strimportgroupsinfile'] = 'Sélectionnez les groupes de tables à importer depuis le fichier "%s" :';
	$lang['strimportgroupsinfileerr'] = 'Des erreurs ont été détectées dans le fichier "%s" :';
	$lang['strcheckjsongroupsconf201'] = 'La structure JSON ne contient pas de tableau "tables_groups".';
 	$lang['strcheckjsongroupsconf202'] = 'La structure JSON référence plusieurs fois le groupe de tables "%s".';
	$lang['strcheckjsongroupsconf210'] = 'Le groupe de tables #%s ne contient pas d\'attribut "group".';
	$lang['strcheckjsongroupsconf211'] = 'Pour le groupe de tables "%s", le mot clé "%s" est inconnu.';
	$lang['strcheckjsongroupsconf212'] = 'Pour le groupe de tables "%s", l\'attribut "is_rollbackable" n\'est pas un booléen.';
	$lang['strcheckjsongroupsconf220'] = 'Dans le groupe de tables "%s", la table #%s n\'a pas d\'attribut "schema".';
	$lang['strcheckjsongroupsconf221'] = 'Dans le groupe de tables "%s", la table #%s n\'a pas d\'attribut "table".';
	$lang['strcheckjsongroupsconf222'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, the mot clé "%s" est inconnu.';
	$lang['strcheckjsongroupsconf223'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, l\'attribut "priority" n\'est pas un nombre.';
	$lang['strcheckjsongroupsconf224'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger #%s n\'a pas d\'attribut "trigger".';
	$lang['strcheckjsongroupsconf225'] = 'Dans le groupe de tables "%s" et pour un trigger de la table %s.%s, le mot clé "%s" est inconnu.';
	$lang['strcheckjsongroupsconf226'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger #%s n\'est pas une chaîne de caractères.';
	$lang['strcheckjsongroupsconf227'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, l\'attribut "ignored_triggers" n\'est pas un tableau.';
	$lang['strcheckjsongroupsconf230'] = 'Dans le groupe de tables "%s", la sequence #%s n\'a pas d\'attribut "schema".';
	$lang['strcheckjsongroupsconf231'] = 'Dans le groupe de tables "%s", la sequence #%s n\'a pas d\'attribut "sequence".';
	$lang['strcheckjsongroupsconf232'] = 'Dans le groupe de tables "%s" et pour la sequence %s.%s, le mot clé "%s" est inconnnu.';
	$lang['strgroupsconfimport250'] = 'Le groupe de tables "%s" à importer n\'est pas référencé dans la structure JSON.';
	$lang['strgroupsconfimport251'] = 'Le groupe de tables "%s" existe déjà.';
	$lang['strgroupsconfimport252'] = 'Changer le type du groupe de tables "%s" n\'est pas permis. Vous pouvez supprimer ce groupe de tables avant d\'importer la configuration.';
	$lang['strgroupsconfimport260'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger %s n\'existe pas.';
	$lang['strgroupsconfimport261'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger %s est un trigger E-Maj.';
	$lang['strgroupsconfimportpreperr'] = 'L\'importation de la configuration des groupes de tables "%s" depuis le fichier "%s" a échoué pour les raisons suivantes :';
	$lang['strgroupsconfimported'] = '%s groupes de tables ont été importés depuis le fichier "%s".';
	$lang['strgroupsconfimporterr'] = 'Erreur à l\'importation de groupes de tables à partir du fichier "%s"';

	// Groups content checks
	$lang['strgroupconfok'] = 'La configuration du groupe de tables "%s" est correcte.';
	$lang['strgroupconfwithdiag'] = 'Les contrôles sur la configuration du groupe de tables "%s" montrent que :';
	$lang['strgroupsconfok'] = 'La configuration des groupes de tables "%s" est correcte.';
	$lang['strgroupsconfwithdiag'] = 'Les contrôles sur la configuration des groupes de tables "%s" montrent que :';
	$lang['strcheckconfgroups01'] = 'Dans le groupe "%s", la table ou séquence "%s.%s" n\'existe pas.';
	$lang['strcheckconfgroups02'] = 'Dans le groupe "%s", la table "%s.%s" est une table partitionnée (seule les partitions élémentaires sont supportées par E-Maj).';
	$lang['strcheckconfgroups03'] = 'Dans le groupe "%s", la table ou séquence "%s.%s" appartient à un schéma E-Maj.';
	$lang['strcheckconfgroups04'] = 'Dans le groupe "%s", la table ou séquence "%s.%s" appartient déjà au groupe "%s".';
	$lang['strcheckconfgroups05'] = 'Dans le groupe "%s", la table "%s.%s" est une table temporaire.';
	$lang['strcheckconfgroups10'] = 'Dans le groupe "%s", la table "%s.%s" générerait un doublon de préfixe de noms E-Maj "%s".';
	$lang['strcheckconfgroups11'] = 'Dans le groupe "%s", la table "%s.%s" a un préfixe de noms E-Maj déjà utilisé ("%s").';
	$lang['strcheckconfgroups12'] = 'Dans le groupe "%s", pour la table "%s.%s", le tablespace de la table de log "%s" n\'existe pas.';
	$lang['strcheckconfgroups13'] = 'Dans le groupe "%s", pour la table "%s.%s", le tablespace de l\'index de log "%s" n\'existe pas.';
	$lang['strcheckconfgroups15'] = 'Dans le groupe "%s", pour la table "%s.%s", le trigger "%s" n\'existe pas.';
	$lang['strcheckconfgroups16'] = 'Dans le groupe "%s", pour la table "%s.%s", le trigger "%s" est un trigger E-Maj.';
	$lang['strcheckconfgroups20'] = 'Dans le groupe "%s", la table "%s.%s" est une table UNLOGGED.';
	$lang['strcheckconfgroups21'] = 'Dans le groupe "%s", la table "%s.%s" est déclarée WITH OIDS.';
	$lang['strcheckconfgroups22'] = 'Dans le groupe "%s", la table "%s.%s" n\'a pas de PRIMARY KEY.';
	$lang['strcheckconfgroups30'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le suffixe de schéma secondaire de log n\'est pas NULL.';
	$lang['strcheckconfgroups31'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le préfixe des noms E-Maj n\'est pas NULL.';
	$lang['strcheckconfgroups32'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le tablespace de la table de log n\'est pas NULL.';
	$lang['strcheckconfgroups33'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le tablespace de l\'index de log n\'est pas NULL.';

	// Group forget
	$lang['strforgetagroup'] = 'E-Maj : Effacer un groupe de tables des historiques';
	$lang['strconfirmforgetgroup'] = 'Êtes-vous sûr de vouloir effacer le groupe de tables "%s" des historiques ?';
	$lang['strforgetgroupok'] = 'Le groupe de tables "%s" a été effacé des historiques.';
	$lang['strforgetgrouperr'] = 'Erreur lors de l\'effacement du groupe de tables "%s" des historiques.';

	// Group's properties and marks
	$lang['strgroupproperties'] = 'Propriétés du groupe de tables "%s"';
	$lang['strgroupmarks'] = 'Marques du groupe de tables "%s"';
	$lang['strlogsessionshelp'] = 'Session de log, représentant l\'intervalle de temps entre le démarrage et l\'arrêt du groupe de tables.';
	$lang['strlogsessionstart'] = 'Session de log démarrée à : %s';
	$lang['strlogsessionstop'] = ' et arrêtée à : %s';
	$lang['strtimestamp'] = 'Date-Heure';
	$lang['strcumchangeshelp'] = 'Le cumul du nombre de mises à jour représente le nombre de mises à jour à annuler en cas de rollback E-Maj à la marque correspondante.';
	$lang['strfirstmark'] = 'Première marque';
	$lang['strnomark'] = 'Le groupe de tables n\'a pas de marque';
	$lang['strgroupcreatedat'] = 'Créé le';
	$lang['strgroupcreateddroppedat'] = 'Créé/supprimé le';
	$lang['strgrouplatesttype'] = 'Dernier type';
	$lang['strgrouplatestdropat'] = 'Dernière suppression à';
	$lang['strgroupstartedat'] = 'Démarré le';
	$lang['strgroupstoppedat'] = 'Arrêté le';
	$lang['strmarksetat'] = 'Posée le';
	$lang['stractivemark'] = 'Marque active, donc utilisable pour un rollback E-Maj.';
	$lang['strdeletedmark'] = 'Un arrêt de l\'enregistrement des mises à jour a rendu la marque inactive, donc inutilisable pour un rollback E-Maj.';
	$lang['strprotectedmark'] = 'La protection mise sur la marque bloque les rollbacks E-Maj sur des marques antérieures.';
	$lang['strsetmark'] = 'Poser une marque';

	// Generic error messages for groups and marks checks
	$lang['strgroupmissing'] = 'Le groupe de tables "%s" n\'existe plus.';
	$lang['strgroupsmissing'] = '%s groupes de tables (%s) n\'existent plus.';
	$lang['strgroupalreadyexists'] = 'Le groupe de table "%s" existe déjà.';
	$lang['strgroupstillexists'] = 'Le groupe de table "%s" existe toujours.';
	$lang['strgroupnotstopped'] = 'Le groupe de tables "%s" n\'est plus arrêté.';
	$lang['strgroupsnotstopped'] = '%s groupes de tables (%s) ne sont plus arrêtés.';
	$lang['strgroupnotstarted'] = 'Le groupe de tables "%s" n\'est plus démarré.';
	$lang['strgroupsnotstarted'] = '%s groupes de tables (%s) ne sont plus démarrés.';
	$lang['strgroupprotected'] = 'Le groupe de table "%s" est protégé.';
	$lang['strgroupsprotected'] = '%s groupes de tables (%s) sont protégés.';
	$lang['strinvalidmark'] = 'La marque saisie (%s) est invalide.';
	$lang['strduplicatemarkgroup'] = 'La marque "%s" existe déjà dans le groupe de tables "%s".';
	$lang['strduplicatemarkgroups'] = 'La marque "%s" existe déjà dans %s groupes de tables (%s).';
	$lang['strmarkmissing'] = 'La marque "%s" n\'existe plus.';
	$lang['strmarksmissing'] = '%s marques (%s) n\'existent plus.';
	$lang['strmissingmarkgroup'] = 'La marque n\'existe plus dans le groupe de tables "%s".';
	$lang['strmissingmarkgroups'] = 'La marque n\'existe plus dans %s groupes de tables (%s).';
	$lang['stradoreturncode'] = 'Code retour de la couche ADO = %s.';

	// Group drop
	$lang['strdropagroup'] = 'E-Maj : Supprimer un groupe de tables';
	$lang['strconfirmdropgroup'] = 'Êtes-vous sûr de vouloir supprimer le groupe de tables "%s" ?';
	$lang['strdropgroupok'] = 'Le groupe de tables "%s" a été supprimé.';
	$lang['strdropgrouperr'] = 'Erreur lors de la suppression du groupe de tables "%s".';

	// Groups drop
	$lang['strdropgroups'] = 'E-Maj : Supprimer les groupes de tables';
	$lang['strconfirmdropgroups'] = 'Êtes-vous sûr de vouloir supprimer les groupes de tables "%s" ?';
	$lang['strdropgroupsok'] = 'Les groupes de tables "%s" ont été supprimés.';
	$lang['strdropgroupserr'] = 'Erreur lors de la suppression des groupes de tables "%s".';

	// Group alter
	$lang['straltergroups'] = 'E-Maj : Appliquer les changements de configuration';
	$lang['stralteraloggingroup'] = 'Le groupe "%s" est actif. Vous pouvez spécifier un nom de marque.';
	$lang['strconfirmaltergroup'] = 'Êtes-vous sûr de vouloir appliquer les changements de configuration pour le groupe de tables "%s" ?';
	$lang['strcantaltergroup'] = 'La modification du groupe "%s" générerait des actions qui ne peuvent être effectuées sur un groupe actif. Arrêtez le groupe avant de le modifier.';
	$lang['straltergroupok'] = 'Les changements de configuration du groupe de tables "%s" ont été appliqués.';
	$lang['straltergrouperr'] = 'Erreur lors de l\'application des changements de configuration pour le groupe de tables "%s" !';

	// Groups alter
	$lang['stralterallloggingroups'] = 'Les groupes "%s" sont actifs. Vous pouvez spécifier un nom de marque.';
	$lang['strconfirmaltergroups'] = 'Êtes-vous sûr de vouloir appliquer les changements de configuration pour les groupes de tables "%s" ?';
	$lang['straltergroupsok'] = 'Les changements de configuration des groupes de tables "%s" ont été appliqués.';
	$lang['straltergroupserr'] = 'Erreur lors de l\'application des changements de configuration pour les groupes de tables "%s" !';

	// Group comment
	$lang['strcommentagroup'] = 'E-Maj : Enregistrer un commentaire pour un groupe de tables ';
	$lang['strcommentgroup'] = 'Entrer, modifier ou supprimer un commentaire pour le groupe de tables "%s"';
	$lang['strcommentgroupok'] = 'Le commentaire a été enregistré pour le groupe de tables "%s".';
	$lang['strcommentgrouperr'] = 'Erreur lors de l\'enregistrement du commentaire pour le groupe de tables "%s".';

	// Group start
	$lang['strstartagroup'] = 'E-Maj : Démarrer un groupe de tables';
	$lang['strconfirmstartgroup'] = 'Démarrage du groupe de tables "%s"';
	$lang['strinitmark'] = 'Marque initiale';
	$lang['stroldlogsdeletion'] = 'Suppression des anciens logs';
	$lang['strstartgroupok'] = 'Le groupe de tables "%s" est démarré avec la marque "%s".';
	$lang['strstartgrouperr'] = 'Erreur lors du démarrage du groupe de tables "%s".';
	$lang['strstartgrouperr2'] = 'Erreur lors du démarrage du groupe de tables "%s" avec la marque "%s".';

	// Groups start
	$lang['strstartgroups'] = 'E-Maj : Démarrer des groupes de tables';
	$lang['strconfirmstartgroups'] = 'Démarrage des groupes de tables "%s"';
	$lang['strstartgroupsok'] = 'Les groupes de tables "%s" ont été démarrés avec la marque "%s".';
	$lang['strstartgroupserr'] = 'Erreur lors du démarrage des groupes de tables "%s".';
	$lang['strstartgroupserr2'] = 'Erreur lors du démarrage des groupes de tables "%s" avec la marque "%s".';

	// Group stop
	$lang['strstopagroup'] = 'E-Maj : Arrêter un groupe de tables ';
	$lang['strconfirmstopgroup'] = 'Arrêt du groupe de tables "%s"';
	$lang['strstopmark'] = 'Marque finale';
	$lang['strforcestop'] = 'Forcer l\'arrêt (en cas de problème seulement)';
	$lang['strstopgroupok'] = 'Le groupe de tables "%s" a été arrêté.';
	$lang['strstopgrouperr'] = 'Erreur lors de l\'arrêt du groupe de tables "%s".';
	$lang['strstopgrouperr2'] = 'Erreur lors de l\'arrêt du groupe de tables "%s" avec la marque "%s".';

	// Groups stop
	$lang['strstopgroups'] = 'E-Maj : Arrêter des groupes de tables';
	$lang['strconfirmstopgroups'] = 'Arrêt des groupes de tables "%s"';
	$lang['strstopgroupsok'] = 'Les groupes de tables "%s" ont été arrêtés.';
	$lang['strstopgroupserr'] = 'Erreur lors de l\'arrêt des groupes de tables "%s".';
	$lang['strstopgroupserr2'] = 'Erreur lors de l\'arrêt des groupes de tables "%s" avec la marque "%s".';

	// Group reset
	$lang['strresetagroup'] = 'E-Maj : Réinitialiser un groupe de tables';
	$lang['strconfirmresetgroup'] = 'Êtes-vous sûr de vouloir réinitialiser le groupe de tables "%s" ?';
	$lang['strresetgroupok'] = 'Le groupe de tables "%s" est réinitialisé.';
	$lang['strresetgrouperr'] = 'Erreur lors de la réinitialisation du groupe de tables "%s".';

	// Groups reset
	$lang['strresetgroups'] = 'E-Maj : Réinitialiser des groupes de tables';
	$lang['strconfirmresetgroups'] = 'Êtes-vous sûr de vouloir réinitialiser les groupe de tables "%s" ?';
	$lang['strresetgroupsok'] = 'Les groupes de tables "%s" ont été réinitialisés.';
	$lang['strresetgroupserr'] = 'Erreur lors de la réinitialisation des groupes de tables "%s".';

	// Group protect
	$lang['strprotectgroupok'] = 'Le groupe de tables "%s" est maintenant protégé contre les rollbacks.';
	$lang['strprotectgrouperr'] = 'Erreur lors de la protection du groupe de tables "%s".';

	// Groups protect
	$lang['strprotectgroupsok'] = 'Les groupes de tables "%s" sont maintenant protégés contre les rollbacks.';
	$lang['strprotectgroupserr'] = 'Erreur lors de la protection des groupes de tables "%s".';

	// Group unprotect
	$lang['strunprotectgroupok'] = 'Le groupe de tables "%s" est maintenant déprotégé.';
	$lang['strunprotectgrouperr'] = 'Erreur lors de la déprotection du groupe de tables "%s".';

	// Groups unprotect
	$lang['strunprotectgroupsok'] = 'Les groupes de tables "%s" sont maintenant déprotégés.';
	$lang['strunprotectgroupserr'] = 'Erreur lors de la déprotection des groupes de tables "%s".';

	// Set Mark for one group
	$lang['strsetamark'] = 'E-Maj : Poser une marque';
	$lang['strconfirmsetmarkgroup'] = 'Pose d\'une marque pour le groupe de tables "%s" :';
	$lang['strsetmarkgroupok'] = 'La marque "%s" est posée pour le groupe de tables "%s".';
	$lang['strsetmarkgrouperr'] = 'Erreur lors de la pose d\'une marque pour le groupe de tables "%s".';
	$lang['strsetmarkgrouperr2'] = 'Erreur lors de la pose de la marque "%s" pour le groupe de tables "%s".';

	// Set Mark for several groups
	$lang['strconfirmsetmarkgroups'] = 'Pose d\'une marque pour les groupes de tables "%s" :';
	$lang['strsetmarkgroupsok'] = 'La marque "%s" est posée pour les groupes de tables "%s".';
	$lang['strsetmarkgroupserr'] = 'Erreur lors de la pose d\'une marque pour les groupes de tables "%s".';
	$lang['strsetmarkgroupserr2'] = 'Erreur lors de la pose de la marque "%s" pour les groupes de tables "%s".';

	// Group rollback
	$lang['strrlbkagroup'] = 'E-Maj : Rollbacker un groupe de tables';
	$lang['strconfirmrlbkgroup'] = 'Rollback du groupe de tables "%s" à la marque "%s"';
	$lang['strunknownestimate'] = 'non connue';
	$lang['strdurationminutesseconds'] = '%s min %s s';
	$lang['strdurationhoursminutes'] = '%s h %s min';
	$lang['strdurationovertendays'] = '> 10 jours';
	$lang['strselectmarkgroup'] = 'Rollback du groupe de tables "%s" à la marque : ';
	$lang['strrlbkthenmonitor'] = 'Rollback et suivi';
	$lang['strcantrlbkinvalidmarkgroup'] = 'La marque "%s" n\'est pas valide.';
	$lang['strreachaltergroup'] = 'Le rollback du groupe de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification du groupe. Veuillez confirmer le rollback.';
	$lang['strautorolledback'] = 'Annulé automatiquement ?';
	$lang['strrlbkgrouperr'] = 'Erreur lors du rollback du groupe de tables "%s".';
	$lang['strrlbkgrouperr2'] = 'Erreur lors du rollback du groupe de tables "%s" à la marque "%s".';
	$lang['strestimrlbkgrouperr'] = 'Erreur lors de l\'estimation de la durée de rollback du groupe de tables "%s" à la marque "%s".';
	$lang['strbadconfparam'] = 'Erreur : le rollback asynchrone n\'est plus possible. Vérifiez l\'existence de l\'extension dblink et la valeur des deux paramètres de configuration du chemin de la commande psql (%s) et du répertoire temporaire (%s).';
	$lang['strasyncrlbkstarted'] = 'Rollback #%s démarré.';
	$lang['strrlbkgroupreport'] = 'Rapport d\'exécution du rollback du groupe de tables "%s" à la marque "%s"';

	// Groups rollback
	$lang['strrlbkgroups'] = 'E-Maj : Rollbacker des groupes de tables';
	$lang['strselectmarkgroups'] = 'Rollback des groupes de tables "%s" à la marque : ';
	$lang['strnomarkgroups'] = 'Aucune marque commune aux groupes de tables "%s" ne peut être utilisée pour un rollback.';
	$lang['strcantrlbkinvalidmarkgroups'] = 'Le rollback des groupes de tables "%s" est impossible. La marque "%s" n\'est pas valide.';
	$lang['strreachaltergroups'] = 'Le rollback des groupes de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification de groupes. Veuillez confirmer le rollback.';
	$lang['strrlbkgroupserr'] = 'Erreur lors du rollback des groupes de tables "%s".';
	$lang['strrlbkgroupserr2'] = 'Erreur lors du rollback des groupes de tables "%s" à la marque "%s".';
	$lang['strestimrlbkgroupserr'] = 'Erreur lors de l\'estimation de la durée de rollback des groupes de tables "%s" à la marque "%s".';
	$lang['strrlbkgroupsreport'] = 'Rapport d\'exécution du rollback des groupes de tables "%s" à la marque "%s"';

	// Elementary alter group actions previously executed, reported at rollback time 
	$lang['stralteredremovetbl'] = 'La table "%s.%s" a été supprimée du groupe de tables "%s"';
	$lang['stralteredremoveseq'] = 'La séquence "%s.%s" a été supprimée du groupe de tables "%s"';
	$lang['stralteredrepairtbl'] = 'Les objets E-Maj pour la table "%s.%s" ont été reparés';
	$lang['stralteredrepairseq'] = 'Les objets E-Maj pour la séquence "%s.%s" ont été reparés';
	$lang['stralteredchangetbllogschema'] = 'Le schéma de log E-Maj pour la table "%s.%s" a été modifié';
	$lang['stralteredchangetblnamesprefix'] = 'Le préfixe des noms E-Maj pour la table "%s.%s" a été modifié';
	$lang['stralteredchangetbllogdatatsp'] = 'Le tablespace pour le log de la table "%s.%s" a été modifié';
	$lang['stralteredchangetbllogindextsp'] = 'Le tablespace pour les index de log de la table "%s.%s" a été modifié';
	$lang['stralteredchangerelpriority'] = 'La priorité E-Maj pour la table "%s.%s" a été modifiée';
	$lang['stralteredchangeignoredtriggers'] = 'Les triggers à ignorer au rollback de la table "%s.%s" ont été modifiés';
	$lang['stralteredmovetbl'] = 'La table "%s.%s" a été déplacée du groupe de tables "%s" vers le groupe de tables "%s"';
	$lang['stralteredmoveseq'] = 'La séquence "%s.%s" a été déplacée du groupe de tables "%s" vers le groupe de tables "%s"';
	$lang['stralteredaddtbl'] = 'La table "%s.%s" a été ajoutée au groupe de tables "%s"';
	$lang['stralteredaddseq'] = 'La séquence "%s.%s" a été ajoutée au groupe de tables "%s"';

	// Protect mark
	$lang['strprotectmarkok'] = 'La marque "%s" du groupe de tables "%s" est maintenant protégée contre les rollbacks.';
	$lang['strprotectmarkerr'] = 'Erreur lors de la protection de la marque "%s" du groupe de tables "%s".';

	// Unprotect mark
	$lang['strunprotectmarkok'] = 'La marque "%s" du groupe de tables "%s" est maintenant déprotégée.';
	$lang['strunprotectmarkerr'] = 'Erreur lors de la déprotection de la marque "%s" du groupe de tables "%s".';

	// Comment mark
	$lang['strcommentamark'] = 'E-Maj : Enregistrer un commentaire pour une marque';
	$lang['strcommentmark'] = 'Entrer, modifier ou supprimer le commentaire pour la marque "%s" du groupe de tables "%s".';
	$lang['strcommentmarkok'] = 'Le commentaire a été enregistré pour la marque "%s" du groupe de tables "%s".';
	$lang['strcommentmarkerr'] = 'Erreur lors de l\'enregistrement du commentaire pour la marque "%s" du groupe de tables "%s".';

	// Mark renaming
	$lang['strrenameamark'] = 'E-Maj : Renommer une marque';
	$lang['strconfirmrenamemark'] = 'Renomage de la marque "%s" du groupe de tables "%s"';
	$lang['strnewnamemark'] = 'Nouveau nom';
	$lang['strrenamemarkok'] = 'La marque "%s" du groupe de tables "%s" a été renommée en "%s".';
	$lang['strrenamemarkerr'] = 'Erreur lors du renommage de la marque "%s" du groupe de tables "%s".';
	$lang['strrenamemarkerr2'] = 'Erreur lors du renommage de la marque "%s" du groupe de tables "%s" en "%s".';

	// Mark deletion
	$lang['strdeleteamark'] = 'E-Maj : Effacer une marque';
	$lang['strconfirmdeletemark'] = 'Êtes-vous sûr de vouloir effacer la marque "%s" pour le groupe de tables "%s" ?';
	$lang['strdeletemarkok'] = 'La marque "%s" a été effacée pour le groupe de tables "%s".';
	$lang['strdeletemarkerr'] = 'Erreur lors de l\'effacement de la marque "%s" pour le groupe de tables "%s".';

	// Marks deletion
	$lang['strdeletemarks'] = 'E-Maj : Effacer des marques';
	$lang['strconfirmdeletemarks'] = 'Êtes-vous sûr de vouloir effacer ces %s marques pour le groupe de tables "%s" ?';
	$lang['strdeletemarksok'] = 'Les %s marques ont été effacées pour le groupe de tables "%s".';
	$lang['strdeletemarkserr'] = 'Erreur lors de l\'effacement des marques "%s" pour le groupe de tables "%s".';

	// Marks before mark deletion
	$lang['strdelmarksprior'] = 'E-Maj : Supprimer des marques';
	$lang['strconfirmdelmarksprior'] = 'Êtes-vous sûr de vouloir supprimer toutes les marques et log antérieurs à la marque "%s" pour le groupe de tables "%s" ?';
	$lang['strdelmarkspriorok'] = 'Les (%s) marques antérieures à la marque "%s" ont été supprimées pour le groupe de tables "%s".';
	$lang['strdelmarkspriorerr'] = 'Erreur lors de la suppression des marques antérieures à la marque "%s" pour le groupe de tables "%s".';

	// Statistics
	$lang['strchangesgroup'] = 'Mises à jour enregistrées pour le groupe de tables "%s"';
	$lang['strcurrentsituation'] = 'Situation courante';
	$lang['strestimatetables'] = 'Estimer tables';
	$lang['strestimatesequences'] = 'Estimer séquences';
	$lang['strdetailtables'] = 'Détailler tables';
	$lang['strdetailedlogstatwarning'] = 'Le parcours des tables de log nécessaire à l\'obtention des statistiques détaillées peut être long. Bien que moins détaillée et moins précise, l\'estimation du nombre de mises à jour est plus rapide car elle n\'utilise que les valeurs des séquences de log enregistrées à chaque marque.';
	$lang['strchangestblbetween'] = 'Mises à jour de table entre les marques "%s" et "%s"';
	$lang['strchangestblsince'] = 'Mises à jour de table depuis la marque "%s"';
	$lang['strtblingroup'] = 'Tables dans le groupe';
	$lang['strtblwithchanges'] = 'Tables mises à jour';
	$lang['strchangesseqbetween'] = 'Mises à jour de séquence entre les marques "%s" et "%s"';
	$lang['strchangesseqsince'] = 'Mises à jour de séquence depuis la marque "%s"';
	$lang['strseqingroup'] = 'Séquences dans le groupe';
	$lang['strseqwithchanges'] = 'Séquences mises à jour';
	$lang['strstatincrements'] = 'Incréments';
	$lang['strstatstructurechanged'] = 'Changement structure ?';
	$lang['strstatverb'] = 'Verbe SQL';
	$lang['strnbinsert'] = 'INSERT';
	$lang['strnbupdate'] = 'UPDATE';
	$lang['strnbdelete'] = 'DELETE';
	$lang['strnbtruncate'] = 'TRUNCATE';
	$lang['strnbrole'] = 'Rôles';
	$lang['strlogsessionwarning'] = 'Cet intervalle de marques couvre plusieurs sessions de log. Des mises à jour de données peuvent ne pas avoir été enregistrées.';
	$lang['strstatrows'] = 'Mises à jour';
	$lang['strbrowsechanges'] = 'Voir les mises à jour';

	// Dump changes SQL generation
	$lang['strsqlgentitle'] = 'Générer la requête SQL d\'extraction des mises à jour';
	$lang['strsqlgenmarksinterval'] = 'Intervalle de marques';
	$lang['strsqlgennopk'] = 'La table n\'a pas de clé primaire. Aucune vision consolidée des mises à jour n\'est possible';
	$lang['strsqlgenconsolidation'] = 'Consolidation';
	$lang['strsqlgenconsonone'] = 'Aucune';
	$lang['strsqlgenconsopartial'] = 'Partielle';
	$lang['strsqlgenconsofull'] = 'Complète';
	$lang['strsqlgenconsohelp'] = 'Sans consolidation, toutes les mises à jour élémentaires enregistrées dans la table de log, pour la tranche de marques sélectionnée, sont restituées. Avec une consolidation (partielle ou totale), ne sont restitués que l\'état initial et/ou l\'état final de chaque clé primaire. Avec une consolidation totale, aucune donnée n\'est restituée lorsque l\'état initial et l\'état final de la ligne sont strictement identiques.';
	$lang['strsqlgenverbs'] = 'Verbes SQL';
	$lang['strsqlgenverbshelp'] = 'Lorsqu\'il n\'y a pas de consolidation, il est possible de filtrer les mises à jour restituées sur les verbes SQL.';
	$lang['strsqlgenknownroles'] = 'Rôles connus :';
	$lang['strsqlgenroleshelp'] = 'Lorsqu\'il n\'y a pas de consolidation, il est possible de filtrer les mises à jour restituées sur les rôles à l\'origine des mises à jour. Si alimenté, le champs doit contenir une liste de rôles séparés par des virgules.';
	$lang['strsqlgentechcols'] = 'Colonnes techniques E-Maj';
	$lang['strsqlgentechcolshelp'] = 'Dans le résultat de la requête, la plupart des colonnes techniques E-Maj peuvent être masquées.';
	$lang['strsqlgencolsorder'] = 'Ordre des colonnes';
	$lang['strsqlgencolsorderlog'] = 'Comme la table de log';
	$lang['strsqlgencolsorderpk'] = 'Clé primaire en tête';
	$lang['strsqlgencolsorderhelp'] = 'Dans le résultat de la requête, soit les colonnes restituées sont dans le même ordre que la table de log, soit les colonnes de la clé primaire (celles de la table applicative + emaj_tuple) sont placées en tête.';
	$lang['strsqlgenroworder'] = 'Ordre des lignes';
	$lang['strsqlgenrowordertime'] = 'Chronologique';
	$lang['strsqlgenroworderhelp'] = 'Dans le résultat de la requête, les lignes peuvent être présentées dans l\'ordre chronologique d\'enregistrement des mises à jour ou dans l\'ordre des clés primaires.';
	$lang['strsqlgenerate'] = 'Générer le SQL';

	// Group's content
	$lang['strgroupcontent'] = 'Contenu actuel du groupe de tables "%s"';
	$lang['stremptygroup'] = 'Le groupe de tables "%s" est actuellement vide.';
	$lang['strpriority'] = 'Priorité';
	$lang['strcurrentlogtable'] = 'Table de log courante';

	// Group's history
	$lang['strgrouphistory'] = 'Historique du groupe de tables "%s"';
	$lang['stremajnohistory'] = 'Il n\'y a aucun historique à afficher pour ce groupe.';
	$lang['strgrouphistoryorder'] = 'Les plus récentes créations de groupe, suppressions de groupe et sessions de log sont placées en début de tableau.';
	$lang['strnblogsessions'] = 'Sessions de log';
	$lang['strgroupcreate'] = 'Création du groupe';
	$lang['strgroupdrop'] = 'Suppression du groupe';
	$lang['strdeletedlogsessions'] = 'Des sessions de log supprimées';

// Old Groups content setup

	// Configure groups
	$lang['strappschemas'] = 'Les schémas applicatifs';
	$lang['strunknownobject'] = 'Cet objet est référencé dans la table emaj_group_def mais n\'est pas créé.';
	$lang['strunsupportedobject'] = 'Ce type d\'objet n\'est pas supporté par E-Maj (unlogged table, table avec OIDS, table partitionnée,...).';
	$lang['strtblseqofschema'] = 'Tables et séquences du schéma "%s"';
	$lang['strlogschemasuffix'] = 'Suffixe schéma log';
	$lang['strnamesprefix'] = 'Préfixe nom objets';
	$lang['strspecifytblseqtoassign'] = 'Spécifiez au moins une table ou séquence à affecter';
	$lang['strtblseqyetgroup'] = 'Erreur, "%s.%s" est déjà affecté à un groupe de tables.';
	$lang['strtblseqbadtype'] = 'Erreur, le type de "%s.%s" n\'est pas supporté par E-Maj.';
	$lang['strassigntblseq'] = 'E-Maj : Affecter des tables / séquences à un groupe de tables';
	$lang['strconfirmassigntblseq'] = 'Affecter :';
	$lang['strfromgroup'] = 'du groupe "%s"';
	$lang['strenterlogschema'] = 'Suffixe du schéma de log';
	$lang['strlogschemahelp'] = 'Un schéma de log contient des tables, séquences et fonctions de log. Le schéma de log par défaut est \'emaj\'. Si un suffixe est défini pour la table, ses objets iront dans le schéma \'emaj\' + suffixe.';
	$lang['strenternameprefix'] = 'Préfixe des noms d\'objets E-Maj';
	$lang['strnameprefixhelp'] = 'Par défaut les noms des objets de log sont préfixés par <schéma>_<table>. Mais on peut définir un autre préfixe pour la table. Il doit être unique dans la base de données.';
	$lang['strspecifytblseqtoupdate'] = 'Spécifiez au moins une table ou séquence à modifier';
	$lang['strupdatetblseq'] = 'E-Maj : Modifier les propriétés d\'une table / séquence dans un groupe de tables';
	$lang['strspecifytblseqtoremove'] = 'Spécifiez au moins une table ou séquence à retirer';
	$lang['strtblseqnogroup'] = 'Erreur, "%s.%s" n\'est actuellement affecté à aucun groupe de tables.';
	$lang['strremovetblseq'] = 'E-Maj : Retirer des tables / séquences de groupes de tables';
	$lang['strconfirmremove1tblseq'] = 'Êtes-vous sûr de vouloir retirer %s du groupe de tables "%s" ?';
	$lang['strconfirmremovetblseq'] = 'Êtes-vous sûr de vouloir retirer :';
	$lang['strmodifygroupok'] = 'Le changement de configuration est enregistré. Il sera effectif après (re)création des groupes de tables concernés ou application des changements de configuration pour ces groupes.';
	$lang['strmodifygrouperr'] = 'Une erreur est survenue lors de l\'enregistrement du changement de configuration des groupes de tables.';

// Schemas

	// Schemas list
	$lang['strschema'] = 'Schéma';
	$lang['strschemas'] = 'Schémas';
	$lang['strallschemas'] = 'Tous les schémas';
	$lang['strnoschemas'] = 'Aucun schéma trouvé.';

	// Tables
	$lang['strtableslist'] = 'Tables du schéma "%s"';
	$lang['strnotables'] = 'Aucune table trouvée.';
	$lang['strestimatedrowcount'] = 'Nb lignes estimé';
	$lang['strtblproperties'] = 'Propriétés de la table "%s.%s"';
	$lang['strtblcontent'] = 'Contenu de la table "%s.%s"';
	$lang['strnograntontable'] = 'Vous n\'avez pas les droits nécessaires pour voir le contenu de la table.';
	$lang['stremajlogtable'] = 'La table est une table de log E-Maj.';
	$lang['strinternaltable'] = 'La table est une table interne E-Maj.';
	$lang['strtblnogroupownership'] = 'La table ne fait actuellement partie d\'aucun groupe de tables.';

	// Sequences
	$lang['strsequenceslist'] = 'Séquences du schéma "%s"';
	$lang['strnosequences'] = 'Aucune séquence trouvée.';
	$lang['strnograntonsequence'] = 'Vous n\'avez pas les droits nécessaires pour voir les propriétés de la séquence.';
	$lang['strseqproperties'] = 'Propriétés de la séquence "%s.%s"';
	$lang['strlastvalue'] = 'Dernière valeur';
	$lang['strincrement'] = 'Incrément';
	$lang['strstartvalue'] = 'Valeur de départ';
	$lang['strmaxvalue'] = 'Valeur maximale';
	$lang['strminvalue'] = 'Valeur minimale';
	$lang['strcachesize'] = 'Taille de cache';
	$lang['strlogcount'] = 'Comptage';
	$lang['strcancycle'] = 'Peut boucler?';
	$lang['striscalled'] = 'Incrémentera la dernière valeur avant de retourner la prochaine valeur (is_called) ?';
	$lang['stremajlogsequence'] = 'La séquence est une séquence de log E-Maj.';
	$lang['strinternalsequence'] = 'La séquence est une séquence interne E-Maj.';
	$lang['strseqnogroupownership'] = 'La séquence ne fait actuellement partie d\'aucun groupe de tables.';

	// Dynamic groups content management: common messages
	$lang['strlogdattsp'] = 'Tablespace tables log';
	$lang['strlogidxtsp'] = 'Tablespace index log';
	$lang['strdefaulttsp'] = '<tablespace par défaut>';
	$lang['strthetable'] = 'la table "%s.%s"';
	$lang['strthesequence'] = 'la séquence "%s.%s"';
	$lang['strthetblseqingroup'] = '%s (groupe %s)';
	$lang['strenterpriority'] = 'Priorité de traitement';
	$lang['strpriorityhelp'] = 'Les tables sont traitées par ordre croissant de priorité, et par ordre alphabétique de nom si aucune priorité n\'est définie.';
	$lang['strenterlogdattsp'] = 'Tablespace des tables de log';
	$lang['strenterlogidxtsp'] = 'Tablespace des index de table de log';
	$lang['strmarkiflogginggroup'] = 'Marque (si un groupe démarré)';

	// Dynamic groups content management: generic error messages
	$lang['strschemamissing'] = 'Le schéma "%s" n\'existe plus.';
	$lang['strtablemissing'] = 'La table "%s.%s" n\'existe plus.';
	$lang['strsequencemissing'] = 'La séquence "%s.%s" n\'existe plus';
	$lang['strtablesmissing'] = '%s tables (%s) n\'existent plus.';
	$lang['strsequencesmissing'] = '%s séquences (%s) n\'existent plus.';

	// Assign tables
	$lang['strassigntable'] = 'E-Maj : Affecter des tables à un groupe de tables';
	$lang['strconfirmassigntable'] = 'Affecter la table "%s.%s"';
	$lang['strconfirmassigntables'] = 'Affecter ces %s tables du schéma "%s" :';
	$lang['strtableshavetriggers'] = 'Cet ensemble de tables porte %s triggers. Ils seront désactivés automatiquement lors d\'un rollback E-Maj. Vous pourrez modifier ce comportement avec l\'onglet "Triggers".';
	$lang['strassigntableok'] = '%s table a été affectée au groupe de tables %s.';
	$lang['strassigntablesok'] = '%s tables ont été affectées au groupe de tables %s.';
	$lang['strassigntableerr'] = 'Erreur lors de l\'affectation de la table "%s.%s".';
	$lang['strassigntableerr2'] = 'Erreur lors de l\'affectation de la table "%s.%s" dans le groupe de tables "%s".';
	$lang['strassigntableserr'] = 'Erreur lors de l\'affectation des %s tables du schéma "%s".';
	$lang['strassigntableserr2'] = 'Erreur lors de l\'affectation des %s tables du schéma "%s" dans le groupe de tables "%s".';

	// Move tables
	$lang['strmovetable'] = 'E-Maj : Déplacer des tables dans un autre groupe de tables';
	$lang['strconfirmmovetable'] = 'Déplacer la table "%s.%s" de son groupe de tables "%s".';
	$lang['strconfirmmovetables'] = 'Déplacer ces %s tables du schéma "%s" :';
	$lang['strmovetableok'] = '%s table a été déplacée dans le groupe de tables %s.';
	$lang['strmovetablesok'] = '%s tables ont été déplacées dans le groupe de tables %s.';
	$lang['strmovetableerr'] = 'Erreur lors du déplacement de la table "%s.%s".';
	$lang['strmovetableerr2'] = 'Erreur lors du déplacement de la table "%s.%s" du groupe de tables "%s" vers le groupe de tables "%s".';
	$lang['strmovetableserr'] = 'Erreur lors du déplacement des %s tables du schéma "%s".';
	$lang['strmovetableserr2'] = 'Erreur lors du déplacement des %s tables du schéma "%s" de leur groupe de tables vers le groupe de tables "%s".';

	// Modify table
	$lang['strmodifytable'] = 'E-Maj : Modifier les propriétés E-Maj de tables';
	$lang['strconfirmmodifytable'] = 'Modifier les propriétés E-Maj de la table "%s.%s".';
	$lang['strconfirmmodifytables'] = 'Modifier les propriétés E-Maj de ces %s tables du schéma "%s" :';
	$lang['strmodifytablesok'] = 'Les propriétés E-Maj de %s tables ont été modifiées.';
	$lang['strmodifytableerr'] = 'Erreur lors de la modification des propiétés E-Maj de la table "%s.%s".';
	$lang['strmodifytableserr'] = 'Erreur lors de la modification des propiétés E-Maj des %s tables du schéma "%s".';

	// Remove tables
	$lang['strremovetable'] = 'E-Maj : Retirer des tables de leur groupe de tables';
	$lang['strconfirmremovetable'] = 'Retirer la table "%s.%s" de son groupe de tables "%s".';
	$lang['strconfirmremovetables'] = 'Retirer ces %s tables du schéma "%s" de leur groupe de tables :';
	$lang['strremovetableok'] = '%s table a été retirée de son groupe de tables.';
	$lang['strremovetablesok'] = '%s tables ont été retirées de leur groupe de tables.';
	$lang['strremovetableerr'] = 'Erreur lors de la sortie de la table "%s.%s" du groupe de tables "%s".';
	$lang['strremovetableserr'] = 'Erreur lors de la sortie des %s tables du schéma "%s" de leur groupe de tables.';

	// Assign sequences
	$lang['strassignsequence'] = 'E-Maj : Affecter des séquences à un groupe de tables';
	$lang['strconfirmassignsequence'] = 'Affecter la séquence "%s.%s"';
	$lang['strconfirmassignsequences'] = 'Affecter ces %s séquences du schéma "%s" :';
	$lang['strassignsequenceok'] = '%s sequence a été affectée au groupe de tables %s.';
	$lang['strassignsequencesok'] = '%s sequences ont été affectées au groupe de tables %s.';
	$lang['strassignsequenceerr'] = 'Erreur lors de l\'affectation de la séquence "%s.%s".';
	$lang['strassignsequenceerr2'] = 'Erreur lors de l\'affectation de la séquence "%s.%s" dans le groupe de tables "%s".';
	$lang['strassignsequenceserr'] = 'Erreur lors de l\'affectation des %s séquences du schéma "%s".';
	$lang['strassignsequenceserr2'] = 'Erreur lors de l\'affectation des %s séquences du schéma "%s" dans le groupe de tables "%s".';

	// Move sequences
	$lang['strmovesequence'] = 'E-Maj : Déplacer des séquences dans un autre groupe de tables';
	$lang['strconfirmmovesequence'] = 'Déplacer la sequence "%s.%s" de son groupe de tables "%s".';
	$lang['strconfirmmovesequences'] = 'Déplacer ces %s séquences du schéma "%s" :';
	$lang['strmovesequenceok'] = '%s séquence a été déplacée dans le groupe de tables %s.';
	$lang['strmovesequencesok'] = '%s séquences ont été déplacées dans le groupe de tables %s.';
	$lang['strmovesequenceerr'] = 'Erreur lors du déplacement de la séquence "%s.%s".';
	$lang['strmovesequenceerr2'] = 'Erreur lors du déplacement de la séquence "%s.%s" du groupe de tables "%s" vers le groupe de tables "%s".';
	$lang['strmovesequenceserr'] = 'Erreur lors du déplacement des %s séquences du schéma "%s".';
	$lang['strmovesequenceserr2'] = 'Erreur lors du déplacement des %s séquences du schéma "%s" de leur groupe de tables vers le groupe de tables "%s".';

	// Remove sequences
	$lang['strremovesequence'] = 'E-Maj : Retirer des séquences de leur groupe de tables';
	$lang['strconfirmremovesequence'] = 'Retirer la séquence "%s.%s" de son groupe de tables "%s".';
	$lang['strconfirmremovesequences'] = 'Retirer ces %s séquences du schéma "%s" :';
	$lang['strremovesequenceok'] = '%s séquence a été retirée de son groupe de tables.';
	$lang['strremovesequencesok'] = '%s séquences ont été retirées de leur groupe de tables.';
	$lang['strremovesequenceerr'] = 'Erreur lors de la sortie de la sequence "%s.%s" du groupe de tables "%s".';
	$lang['strremovesequenceserr'] = 'Erreur lors de la sortie des %s sequences du schéma "%s" de leur groupe de tables.';

// Triggers

	// Triggers list
	$lang['strtrigger'] = 'Trigger';
	$lang['strtriggers'] = 'Triggers';
	$lang['strnotriggerontable'] = 'La table n\'a pas de trigger.';
	$lang['strapptriggers'] = 'Triggers applicatifs';
	$lang['strapptriggershelp'] = 'Liste des triggers de la base de données, hors triggers système et E-Maj.';
	$lang['strnoapptrigger'] = 'Aucun trigger applicatif dans la base de données.';
	$lang['strexecorder'] = 'Ordre Exec';
	$lang['strtriggeringevent'] = 'Événement déclencheur';
	$lang['strcalledfunction'] = 'Fonction appelée';
	$lang['strisemaj'] = 'E-Maj ?';
	$lang['strisautodisable'] = 'Désactivation<br>auto';
	$lang['strisautodisablehelp'] = 'Indique si le trigger est désactivé automatiquement lors des rollbacks E-Maj (défaut = ON = Oui)';
	$lang['strtriggerautook'] = 'Le trigger %s de la table %s.%s sera automatiquement désactivé lors des rollbacks E-Maj.';
	$lang['strtriggernoautook'] = 'Le trigger %s de la table %s.%s ne sera PAS automatiquement désactivé lors des rollbacks E-Maj.';
	$lang['strtriggerprocerr'] = 'Une erreur est survenue dans le traitement du trigger %s de la table %s.%s.';
	$lang['strnoselectedtriggers'] = 'Aucun trigger sélectionné.';
	$lang['strtriggersautook'] = '%s nouveaux triggers seront automatiquement désactivés lors des rollbacks E-Maj.';
	$lang['strtriggersnoautook'] = '%s nouveaux triggers ne seront PAS automatiquement désactivés lors des rollbacks E-Maj.';
	$lang['strorphantriggersexist'] = 'La table qui contient les identifiants de triggers à ne pas désactiver automatiquement lors des rollbacks E-Maj (emaj_ignored_app_trigger) référence des schémas, tables ou triggers qui n\'existent plus.';
	$lang['strtriggersremovedok'] = '%s triggers ont été retirés.';

// E-Maj Rollbacks

	// Rollback activity
	$lang['strrlbkid'] = 'Id. Rlbk';
	$lang['strrlbkstart'] = 'Début rollback';
	$lang['strrlbkend'] = 'Fin rollback';
	$lang['strduration'] = 'Durée';
	$lang['strislogged'] = 'Tracé ?';
	$lang['strnbsession'] = 'Sessions';
	$lang['strcurrentduration'] = 'Durée actuelle';
	$lang['strglobalduration'] = 'Durée globale';
	$lang['strplanningduration'] = 'Durée planification';
	$lang['strlockingduration'] = 'Durée pose verrous';
	$lang['strestimremaining'] = 'Restant estimée';
	$lang['strpctcompleted'] = '% effectué';
	$lang['strinprogressrlbk'] = 'Rollbacks E-Maj en cours';
	$lang['strrlbkmonitornotavailable'] = 'Le suivi des rollbacks en cours n\'est pas disponible.';
	$lang['strcompletedrlbk'] = 'Rollbacks E-Maj terminés';
	$lang['strnbtabletoprocess'] = 'Tables à traiter';
	$lang['strnbseqtoprocess'] = 'Séquences à traiter';
	$lang['strnorlbk'] = 'Aucun rollback.';
	$lang['strconsolidablerlbk'] = 'Rollbacks E-Maj tracés consolidables';
	$lang['strtargetmark'] = 'Marque cible';
	$lang['strendrollbackmark'] = 'Marque de fin de rollback';
	$lang['strnbintermediatemark'] = 'Marques intermédiaires';
	$lang['strconsolidate'] = 'Consolider';

	// Consolidate an E-Maj rollback
	$lang['strconsolidaterlbk'] = 'Consolider un rollback tracé';
	$lang['strconfirmconsolidaterlbk'] = 'Êtes-vous sûr de vouloir consolider le rollback terminé par la marque "%s" du groupe de tables "%s" ?';
	$lang['strconsolidaterlbkok'] = 'Le rollback terminé par la marque "%s" du groupe de tables "%s" a été consolidé.';
	$lang['strconsolidaterlbkerr'] = 'Erreur lors de la consolidation du rollback terminé par la marque "%s" du groupe de tables "%s" !';

	// E-Maj rollback details
	$lang['strrlbkprogress'] = 'Progression du rollback';
	$lang['strrlbksessions'] = 'Sessions';
	$lang['strrlbksession'] = 'Session';
	$lang['strrlbkexecreport'] = 'Rapport d\'exécution';
	$lang['strrlbkelemsteps'] = 'Etapes élémentaires';
	$lang['strrlbkelemstepshelp'] = 'Les principales étapes élémentaires d\'exécution du Rollback E-Maj. Ne sont pas inclus : la planification et la pose des verrous sur les tables en début d\'opération et, pour les versions emaj < 4.2, le traitement des séquences en fin d\'opération.';
	$lang['strrlbkestimmethodhelp'] = 'En phase de planification, la durée de chaque étape est estimée, en utilisant en priorité des statistiques d\'exécutions similaires passées, avec le même ordre de grandeur de quantités à traiter (STAT+), ou des ordres de grandeur différentes (STAT), ou, à défaut, les paramètres de l\'extension (PARAM). La colonne Q évalue la qualité des estimations de durée des étapes de plus de 10ms.';
	$lang['strnorlbkstep'] = 'Pas d\'étape élémentaire pour ce rollback.';
	$lang['strrlbkstep'] = 'Étape';
	$lang['strrlbkstarttime'] = 'Démarrage';
	$lang['strrlbkendtime'] = 'Fin';
	$lang['strabbrquality'] = 'Q';
	$lang['strmethod'] = 'Méthode';
	$lang['strrlbksequences'] = 'Effectuer le rollback des séquences';
	$lang['strrlbkdisapptrg'] = 'Désactiver le trigger %s';
	$lang['strrlbkdislogtrg'] = 'Désactiver le trigger de log';
	$lang['strrlbksetalwaysapptrg'] = 'Passer le trigger %s à ALWAYS';
	$lang['strrlbkdropfk'] = 'Supprimer la clé étrangère %s';
	$lang['strrlbksetfkdef'] = 'Positionner la clé étrangère %s DEFFERED';
	$lang['strrlbkrlbktable'] = 'Effectuer le rollback de la table';
	$lang['strrlbkdeletelog'] = 'Supprimer des log';
	$lang['strrlbksetfkimm'] = 'Positionner la clé étrangère %s IMMEDIATE';
	$lang['strrlbkaddfk'] = 'Recréer la clé étrangère %s';
	$lang['strrlbkenaapptrg'] = 'Réactiver le trigger %s';
	$lang['strrlbksetlocalapptrg'] = 'Passer le trigger %s à LOCAL';
	$lang['strrlbkenalogtrg'] = 'Réactiver le trigger de log';

	// Comment an E-Maj rollback
	$lang['strcommentarollback'] = 'E-Maj : Enregistrer un commentaire pour un rollback';
	$lang['strcommentrollback'] = 'Entrer, modifier ou supprimer un commentaire pour le rollback %s';
	$lang['strcommentrollbackok'] = 'Le commentaire a été enregistré pour le rollback %s.';
	$lang['strcommentrollbackerr'] = 'Erreur lors de l\'enregistrement du commentaire pour le rollback %s !';

// Activity

	$lang['strchangesactivity'] = 'Activité de mises à jour E-Maj';

	// Form
	$lang['strincluderegexp'] = 'Regexp d\'inclusion';
	$lang['strincluderegexphelp'] = 'Expression rationelle permettant de sélectionner les groupes, tables ou séquences à filtrer. Une chaîne vide équivaut à .* et inclut tous les objets. Pour les tables et séquences, le filtrage porte sur les noms préfixés par leur schéma. Voir la documentation PostgreSQL pour les syntaxes d\'expressions régulières possibles.';
	$lang['strexcluderegexp'] = 'Regexp d\'exclusion';
	$lang['strexcluderegexphelp'] = 'Expression rationelle permettant d\'exclure les groupes, tables ou séquences à filtrer. Une chaîne vide équivaut à aucune exclusion. Pour les tables et séquences, le filtrage porte sur les noms préfixés par leur schéma. Voir la documentation PostgreSQL pour les syntaxes d\'expressions régulières possibles.';
	$lang['strmaxrows'] = 'Nb lignes maximum';
	$lang['strmaxrowshelp'] = 'Définit le nombre maximum de lignes à afficher dans chacun des tableaux des groupes, tables et séquences, ces lignes étant triées par ordre décroissant de mises à jour, soit depuis la dernière marque, soit depuis l\'affichage précédent. La valeur 0 supprime le tableau correspondant.';
	$lang['strmainsortcriteria'] = 'Critère de tri principal';
	$lang['strmainsortcriteriahelp'] = 'Définit le critère de tri principal des groupes, tables et séquences affichées. En cas d\'égalité du nombre de mises à jour, les lignes sont triées sur le nom des groupes, tables et séquences, les noms de tables et séquences étant préfixées par leur nom de schéma.';
	$lang['strchangessince'] = 'Nb mises à jour depuis';
	$lang['strlatestmark'] = 'Dernière marque';
	$lang['strpreviousdisplay'] = 'Affichage précédent';

	// Display
	$lang['strerrortrapped'] = 'Une erreur a été interceptée lors de la consultation des séquences.';
	$lang['strglobalactivity'] = 'Activité globale';
	$lang['strlogginggroupstitle'] = 'Groupes démarrés';
	$lang['strnogroupselected'] = 'Aucun groupe de tables n\'est sélectionné.';
	$lang['strtablesinlogginggroups'] = 'Tables des groupes démarrés';
	$lang['strnotableselected'] = 'Aucune table n\'est sélectionnée.';
	$lang['strsequencesinlogginggroups'] = 'Séquences des groupes démarrés';
	$lang['strnosequenceselected'] = 'Aucune séquence n\'est sélectionnée.';
	$lang['strsincelatestmark'] = 'Depuis la dernière marque';
	$lang['strsincepreviousdisplay'] = 'Depuis l\'affichage précédent';
	$lang['strchangespersecond'] = 'MàJ / sec';

// E-Maj environment

	// Versions
	$lang['strextnotavailable'] = 'Le logiciel E-Maj n\'est pas installé sur cette instance PostgreSQL.';
	$lang['strextnotcreated'] = 'L\'extension emaj n\'est pas créée dans cette base de données.';
	$lang['strcontactdba'] = 'Contactez votre administrateur des bases de données.';
	$lang['strnogrant'] = 'Votre rôle de connexion n\'a pas les droits d\'utilisation d\'E-Maj. Utilisez un autre rôle ou contactez votre administrateur des bases de données.';
	$lang['strcharacteristics'] = 'Caractéristiques de l\'environnement E-Maj';
	$lang['strversions'] = 'Versions';
	$lang['strpgversion'] = 'Version PostgreSQL : ';
	$lang['strversion'] = 'Version E-Maj : ';
	$lang['strasextension'] = 'installée comme extension';
	$lang['strasscript'] = 'installée par script';
	$lang['strtooold'] = 'Désolé, cette version d\'E-Maj (%s) est trop ancienne. La version minimum supportée par Emaj_web est %s.';
	$lang['strversionmorerecent'] = 'Il existe une version plus récente de l\'extension "emaj" compatible avec cette version d\'Emaj_web.';
	$lang['strwebversionmorerecent'] = 'Une version plus récente d\'Emaj_web existe probablement.';
	$lang['strwarningdevel'] = 'Accéder à une extension emaj en version <devel> peut poser des problèmes. Il est conseillé d\'obtenir une version stable d\'emaj depuis pgxn.org.';
	$lang['stremajwebversion'] = 'Version Emaj_web : ';

	// Extension management
	$lang['strextensionmngt'] = 'Gestion de l\'extension "emaj"';
	$lang['strcreateextension'] = 'Créer l\'extension';
	$lang['strcreateemajextension'] = 'Créer l\'extension "emaj"';
	$lang['strnocompatibleemajversion'] = 'Aucune version d\'extension emaj installée n\'est compatible avec la version de PostgreSQL.';
	$lang['strcreateextensionok'] = 'L\'extension "emaj" a été créée.';
	$lang['strcreateextensionerr'] = 'Erreur lors de la création de l\'extension "emaj".';
	$lang['strupdateextension'] = 'Mettre à jour l\'extension';
	$lang['strupdateemajextension'] = 'Mettre à jour l\'extension "emaj"';
	$lang['strmissingeventtriggers'] = 'Il manque des triggers sur événement. Cela bloque les mises à jour de version >= 4.2.0. Exécutez le script sql/emaj_upgrade_after_postgres_upgrade.sql ou supprimez et réinstallez l\'extension.';
	$lang['strnocompatibleemajupdate'] = 'Aucune mise à jour d\'extension emaj installée n\'est compatible avec la version de PostgreSQL.';
	$lang['strupdateextensionok'] = 'L\'extension "emaj" a été mise à jour.';
	$lang['strupdateextensionerr'] = 'Erreur lors de la mise à jour de l\'extension "emaj".';
	$lang['strdropextension'] = 'Supprimer l\'extension';
	$lang['strdropextensiongroupsexist'] = 'Des (%s) groupes de tables existent actuellement. Supprimer l\'extension supprimera automatiquement ces groupes.';
	$lang['strdropemajextension'] = 'Supprimer l\'extension "emaj"';
	$lang['strconfirmdropextension'] = 'Confirmer la suppression de l\'extension "emaj"';
	$lang['strdropextensionok'] = 'L\'extension "emaj" a été supprimée.';
	$lang['strdropextensionerr'] = 'Erreur lors de la suppression de l\'extension "emaj".';

	// Characteristics and consistency checks
	$lang['strdiskspace'] = 'Place disque occupée par l\'environnement E-Maj : %s de la base de données courante.';
	$lang['strchecking'] = 'Intégrité de l\'environnement E-Maj';
	$lang['strdiagnostics'] = 'Diagnostics';

	// Parameters
	$lang['strextparams'] = 'Paramètres de l\'extension';
	$lang['strpargeneral'] = 'Paramètres généraux';
	$lang['strparcostmodel'] = 'Paramètres du modèle de coûts des rollbacks E-Maj';
	$lang['strparhistret'] = 'Délai de rétention des historiques';
	$lang['strparhistretinfo'] = 'Le paramètre \'history_retention\' de la table emaj_param détermine le délai de rétention du contenu des différentes tables internes d\'historiques des opérations E-Maj. La valeur par défaut est de 1 an. Le paramètre est de type INTERVAL.';
	$lang['strpardblinkcon'] = 'Chaine de connexion dblink';
	$lang['strpardblinkconinfo'] = 'Le paramètre \'dblink_user_password\' de la table emaj_param définit la chaîne de connexion utilisée par dblink pour permettre le suivi des opérations de rollback E-Maj en cours d\'exécution. Le format du paramètre correspond aux chaînes de connexion habituelles pour PostgreSQL, par exemple \'user=<user> password=<password>\'. Par défaut le paramètre est vide, empếchant le suivi des opérations de rollback E-Maj.';
	$lang['strparalterlog'] = 'Modification de la structure des tables de log';
	$lang['strparalterloginfo'] = 'Le paramètre \'alter_log_table\' de la table emaj_param définit la modification de la structure des tables de log à leur création. Il prend la forme d\'une directive de type ALTER TABLE, par exemple \'ADD COLUMN emaj_appname TEXT DEFAULT current_setting(\'\'application_name\'\')\'. Le paramètre est vide par défaut.';
	$lang['strparfixedstep'] = 'Coût fixe d\'une étape de rollback';
	$lang['strparfixedstepinfo'] = 'Le paramètre \'fixed_step_rollback_duration\' de la table emaj_param détermine un coût fixe de traitement d\'une étape élémentaire de rollback E-Maj. Le paramètre est de type INTERVAL. La valeur par défaut est de 2,5 ms.';
	$lang['strparfixeddblink'] = 'Surcoût dblink d\'une étape de rollback';
	$lang['strparfixeddblinkinfo'] = 'Le paramètre \'fixed_dblink_rollback_duration\' de la table emaj_param détermine un surcoût pour chaque étape élémentaire de rollback E-Maj lorsqu\'une connexion dblink est utilisée. Le paramètre est de type INTERVAL. La valeur par défaut est de 4 ms.';
	$lang['strparfixedrlbktbl'] = 'Coût fixe de rollback d\'une table';
	$lang['strparfixedrlbktblinfo'] = 'Le paramètre \'fixed_table_rollback_duration\' de la table emaj_param détermine un coût fixe de rollback d\'une table ou séquence. Le paramètre est de type INTERVAL. La valeur par défaut est de 1 ms.';
	$lang['strparavgrowrlbk'] = 'Coût moyen de rollback d\'une mise à jour';
	$lang['strparavgrowrlbkinfo'] = 'Le paramètre \'avg_row_rollback_duration\' de la table emaj_param détermine le coût moyen de rollback d\'une mise à jour élémentaire. Le paramètre est de type INTERVAL. La valeur par défaut est de 100 µs.';
	$lang['strparavgrowdel'] = 'Coût moyen de suppression d\'une mise à jour des logs';
	$lang['strparavgrowdelinfo'] = 'Le paramètre \'avg_row_delete_log_duration\' de la table emaj_param détermine le coût moyen de suppression d\'une mise à jour élémentaire dans le log E-Maj. Le paramètre est de type INTERVAL. La valeur par défaut est de 10 µs.';
	$lang['strparavgfkcheck'] = 'Coût moyen de vérification d\'une clé étrangère';
	$lang['strparavgfkcheckinfo'] = 'Le paramètre \'avg_fkey_check_duration\' de la table emaj_param détermine le coût moyen de vérification d\'une clé étrangère. Le paramètre est de type INTERVAL. La valeur par défaut est de 20 µs.';

	// Import parameters
	$lang['strimportparamconf'] = 'Importer une configuration de paramètres';
	$lang['strdeletecurrentparam'] = 'Supprimer tous les paramètres existants';
	$lang['strdeletecurrentparaminfo'] = 'Si la case est cochée, tous les paramètres présents dans l\'extension emaj sont supprimés avant le chargement du fichier.';
	$lang['strcheckjsonparamconf101'] = 'La structure JSON ne contient pas de tableau "parameters".';
	$lang['strcheckjsonparamconf102'] = 'Le paramètre #%s n\' pas d\'attribut  "key" ou a un attribut "key" à null.';
	$lang['strcheckjsonparamconf103'] = 'Pour le paramètre "%s", l\'attribut "%s" est inconnu.';
	$lang['strcheckjsonparamconf104'] = '"%s" n\'est pas un paramètre E-Maj connu.';
	$lang['strcheckjsonparamconf105'] = 'La structure JSON référence plusieurs fois le paramètre "%s".';
	$lang['strparamconfimported'] = '%s : %s paramètres importés depuis le fichier %s.';
	$lang['strnewconf'] = 'Nouvelle configuration';
	$lang['strnewmodifiedconf'] = 'Configuration modifiée';
	$lang['strparamconfigimporterr'] = 'Erreur à l\'importation de paramètres à partir du fichier %s';

?>
